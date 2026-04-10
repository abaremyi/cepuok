<?php
/**
 * Finance Model — CEP UOK
 * File: modules/Finance/models/FinanceModel.php
 */
class FinanceModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    // ═══════════════════════════════════════════════════════════
    // DASHBOARD
    // ═══════════════════════════════════════════════════════════

    public function getDashboardStats($session = null)
    {
        try {
            $sw = $session ? 'WHERE cep_session=:s' : '';
            $p  = $session ? [':s' => $session]     : [];

            $rev = $this->db->prepare("SELECT
                COALESCE(SUM(amount),0) AS total_revenue,
                COALESCE(SUM(CASE WHEN MONTH(revenue_date)=MONTH(NOW()) AND YEAR(revenue_date)=YEAR(NOW()) THEN amount END),0) AS this_month
                FROM finance_revenue $sw");
            $rev->execute($p);
            $r = $rev->fetch(PDO::FETCH_ASSOC);

            $fw = $session ? 'AND cep_session=:s' : '';
            $fr = $this->db->prepare("SELECT
                COALESCE(SUM(CASE WHEN stage='completed' THEN amount_approved END),0) AS total_disbursed,
                COALESCE(SUM(CASE WHEN stage='to_president' THEN 1 END),0) AS pending_requests,
                COALESCE(SUM(CASE WHEN stage='to_finance'   THEN 1 END),0) AS to_finance_count
                FROM fund_requests WHERE 1=1 $fw");
            $fr->execute($p);
            $f = $fr->fetch(PDO::FETCH_ASSOC);

            $balance = floatval($r['total_revenue']) - floatval($f['total_disbursed']);
            return [
                'total_revenue'    => (float)$r['total_revenue'],
                'this_month'       => (float)$r['this_month'],
                'total_expenses'   => (float)$f['total_disbursed'],
                'balance'          => $balance,
                'reserve_pool'     => $balance,
                'pending_requests' => (int)$f['pending_requests'],
                'to_finance_count' => (int)$f['to_finance_count'],
            ];
        } catch (Exception $e) {
            error_log("FinanceModel::getDashboardStats - " . $e->getMessage());
            return [];
        }
    }

    public function getRevenueByType($session = null, $year = null)
    {
        try {
            $year = $year ?: date('Y');
            $w = "WHERE YEAR(revenue_date)=:y"; $p = [':y' => $year];
            if ($session) { $w .= " AND cep_session=:s"; $p[':s'] = $session; }
            $s = $this->db->prepare("SELECT revenue_type, COALESCE(SUM(amount),0) AS total FROM finance_revenue $w GROUP BY revenue_type ORDER BY total DESC");
            $s->execute($p);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getMonthlyTrend($session = null, $year = null)
    {
        try {
            $year = $year ?: date('Y');
            $w = "WHERE YEAR(revenue_date)=:y"; $p = [':y' => $year];
            if ($session) { $w .= " AND cep_session=:s"; $p[':s'] = $session; }
            $s = $this->db->prepare("SELECT MONTH(revenue_date) AS month,
                COALESCE(SUM(amount),0) AS total,
                COALESCE(SUM(CASE WHEN revenue_type='offering'    THEN amount END),0) AS offerings,
                COALESCE(SUM(CASE WHEN revenue_type='tithe'       THEN amount END),0) AS tithes,
                COALESCE(SUM(CASE WHEN revenue_type='donation'    THEN amount END),0) AS donations
                FROM finance_revenue $w GROUP BY MONTH(revenue_date) ORDER BY month");
            $s->execute($p);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getSessionSplit($year = null)
    {
        try {
            $year = $year ?: date('Y');
            $s = $this->db->prepare("SELECT cep_session, COALESCE(SUM(amount),0) AS total FROM finance_revenue WHERE YEAR(revenue_date)=:y GROUP BY cep_session");
            $s->execute([':y' => $year]);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getBudgetUtilisation($session = null)
    {
        try {
            $w = "WHERE bq.status='approved'"; $p = [];
            if ($session) { $w .= " AND bq.cep_session=:s"; $p[':s'] = $session; }
            $s = $this->db->prepare("SELECT bq.id, bq.budget_name, bq.quarter, bq.cep_session, bq.total_allocated,
                COALESCE(SUM(ba.allocated_amount),0) AS allocated,
                COALESCE(SUM(ba.spent_amount),0)     AS spent
                FROM budget_quarters bq
                LEFT JOIN budget_activities ba ON ba.quarter_id = bq.id
                $w GROUP BY bq.id ORDER BY bq.created_at DESC LIMIT 6");
            $s->execute($p);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getRecentTransactions($session = null, $limit = 10)
    {
        try {
            $w = $session ? "WHERE r.cep_session=:s" : ""; $p = $session ? [':s' => $session] : [];
            $s = $this->db->prepare("SELECT r.*, CONCAT(u.firstname,' ',u.lastname) AS recorded_by_name
                FROM finance_revenue r LEFT JOIN users u ON u.id=r.recorded_by
                $w ORDER BY r.created_at DESC LIMIT :lim");
            $s->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            foreach ($p as $k => $v) $s->bindValue($k, $v);
            $s->execute();
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    // ═══════════════════════════════════════════════════════════
    // REVENUE CRUD
    // ═══════════════════════════════════════════════════════════

    public function getAllRevenue($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $w = "WHERE 1=1"; $p = [];
            if (!empty($filters['session'])) { $w .= " AND r.cep_session=:s";   $p[':s'] = $filters['session']; }
            if (!empty($filters['type']))    { $w .= " AND r.revenue_type=:t";  $p[':t'] = $filters['type']; }
            if (!empty($filters['month']))   {
                $w .= " AND MONTH(r.revenue_date)=:mo AND YEAR(r.revenue_date)=:yr";
                [$yr, $mo]   = explode('-', $filters['month']);
                $p[':mo'] = $mo; $p[':yr'] = $yr;
            }
            $offset = ($page - 1) * $perPage;
            $s = $this->db->prepare("SELECT r.*, CONCAT(u.firstname,' ',u.lastname) AS recorded_by_name
                FROM finance_revenue r LEFT JOIN users u ON u.id=r.recorded_by
                $w ORDER BY r.revenue_date DESC, r.id DESC LIMIT :lim OFFSET :off");
            $s->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $s->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($p as $k => $v) $s->bindValue($k, $v);
            $s->execute();
            $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            $cnt  = $this->db->prepare("SELECT COUNT(*) FROM finance_revenue r $w");
            foreach ($p as $k => $v) $cnt->bindValue($k, $v);
            $cnt->execute();
            $total = (int)$cnt->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => max(1, ceil($total / $perPage))];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function createRevenue($data)
    {
        try {
            $s = $this->db->prepare("INSERT INTO finance_revenue (cep_session,revenue_type,amount,description,revenue_date,reference_no,recorded_by)
                VALUES (:session,:type,:amount,:desc,:date,:ref,:by)");
            $s->execute([':session' => $data['cep_session'], ':type' => $data['revenue_type'],
                ':amount' => $data['amount'], ':desc' => $data['description'] ?? null,
                ':date' => $data['revenue_date'], ':ref' => $data['reference_no'] ?? null,
                ':by' => $data['recorded_by'] ?? null]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function getRevenueById($id)
    {
        try {
            $s = $this->db->prepare("SELECT * FROM finance_revenue WHERE id=:id");
            $s->execute([':id' => $id]);
            return $s->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return null; }
    }

    public function updateRevenue($data)
    {
        try {
            $s = $this->db->prepare("UPDATE finance_revenue SET cep_session=:s,revenue_type=:t,amount=:a,revenue_date=:d,reference_no=:r,description=:desc WHERE id=:id");
            $s->execute([':s'=>$data['cep_session'],':t'=>$data['revenue_type'],':a'=>$data['amount'],
                ':d'=>$data['revenue_date'],':r'=>$data['reference_no']??null,
                ':desc'=>$data['description']??null,':id'=>$data['id']]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function deleteRevenue($id)
    {
        try {
            $this->db->prepare("DELETE FROM finance_revenue WHERE id=:id")->execute([':id' => $id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function getDailyTotal($session, $date = null)
    {
        try {
            $date = $date ?: date('Y-m-d');
            $s = $this->db->prepare("SELECT COALESCE(SUM(amount),0) FROM finance_revenue WHERE cep_session=:s AND revenue_date=:d");
            $s->execute([':s' => $session, ':d' => $date]);
            return (float)$s->fetchColumn();
        } catch (Exception $e) { return 0; }
    }

    // ═══════════════════════════════════════════════════════════
    // BUDGET INDICATORS
    // ═══════════════════════════════════════════════════════════

    public function getIndicator($session, $year)
    {
        try {
            $s = $this->db->prepare("SELECT bi.*, CONCAT(u.firstname,' ',u.lastname) AS created_by_name
                FROM budget_indicators bi LEFT JOIN users u ON u.id=bi.created_by
                WHERE bi.cep_session=:s AND bi.academic_year=:y LIMIT 1");
            $s->execute([':s' => $session, ':y' => $year]);
            $ind = $s->fetch(PDO::FETCH_ASSOC);
            if ($ind) {
                $ps = $this->db->prepare("SELECT * FROM indicator_pools WHERE indicator_id=:id ORDER BY display_order");
                $ps->execute([':id' => $ind['id']]);
                $ind['pools'] = $ps->fetchAll(PDO::FETCH_ASSOC);
            }
            return $ind;
        } catch (Exception $e) { error_log("getIndicator - " . $e->getMessage()); return null; }
    }

    public function getIndicatorById($id)
    {
        try {
            $s = $this->db->prepare("SELECT bi.*, CONCAT(u.firstname,' ',u.lastname) AS created_by_name
                FROM budget_indicators bi LEFT JOIN users u ON u.id=bi.created_by WHERE bi.id=:id LIMIT 1");
            $s->execute([':id' => $id]);
            $ind = $s->fetch(PDO::FETCH_ASSOC);
            if ($ind) {
                $ps = $this->db->prepare("SELECT * FROM indicator_pools WHERE indicator_id=:id ORDER BY display_order");
                $ps->execute([':id' => $id]);
                $ind['pools'] = $ps->fetchAll(PDO::FETCH_ASSOC);
            }
            return $ind;
        } catch (Exception $e) { return null; }
    }

    public function createIndicator($data, $pools, $createdBy)
    {
        try {
            // Validate total %
            $total = array_sum(array_column($pools, 'percentage'));
            if (abs($total - 100) > 0.01) return ['success' => false, 'message' => "Pool percentages must sum to 100%. Current total: {$total}%"];

            // Check if already exists
            $check = $this->db->prepare("SELECT id FROM budget_indicators WHERE cep_session=:s AND academic_year=:y");
            $check->execute([':s' => $data['cep_session'], ':y' => $data['academic_year']]);
            if ($check->fetch()) return ['success' => false, 'message' => 'Budget indicators for this session and year already exist.'];

            $this->db->beginTransaction();
            $s = $this->db->prepare("INSERT INTO budget_indicators (cep_session,academic_year,base_balance,lock_date,status,created_by)
                VALUES (:s,:y,:b,:ld,'draft',:by)");
            $s->execute([':s'=>$data['cep_session'],':y'=>$data['academic_year'],':b'=>$data['base_balance'],
                ':ld'=>$data['lock_date']??null,':by'=>$createdBy]);
            $indId = $this->db->lastInsertId();

            $base = floatval($data['base_balance']);
            foreach ($pools as $i => $pool) {
                $alloc = round($base * floatval($pool['percentage']) / 100, 2);
                $ps    = $this->db->prepare("INSERT INTO indicator_pools (indicator_id,pool_name,pool_slug,pool_type,percentage,allocated_amount,color,display_order)
                    VALUES (:ind,:name,:slug,:type,:pct,:alloc,:color,:ord)");
                $ps->execute([':ind'=>$indId,':name'=>$pool['pool_name'],':slug'=>$pool['pool_slug']??strtolower(str_replace(' ','_',$pool['pool_name'])),
                    ':type'=>$pool['pool_type']??'department',':pct'=>$pool['percentage'],
                    ':alloc'=>$alloc,':color'=>$pool['color']??'#377dff',':ord'=>$i]);
            }
            $this->db->commit();
            return ['success' => true, 'id' => $indId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateIndicator($id, $data, $pools, $userId)
    {
        try {
            // Validate
            $total = array_sum(array_column($pools, 'percentage'));
            if (abs($total - 100) > 0.01) return ['success' => false, 'message' => "Pools must sum to 100%. Got: {$total}%"];

            // Permission: check lock_date
            $ind = $this->getIndicatorById($id);
            if (!$ind) return ['success' => false, 'message' => 'Indicator not found'];
            if ($ind['status'] === 'locked') return ['success' => false, 'message' => 'This indicator is locked and cannot be edited.'];

            $this->db->beginTransaction();
            $s = $this->db->prepare("UPDATE budget_indicators SET base_balance=:b,lock_date=:ld WHERE id=:id");
            $s->execute([':b'=>$data['base_balance'],':ld'=>$data['lock_date']??null,':id'=>$id]);

            // Delete and re-insert pools
            $this->db->prepare("DELETE FROM indicator_pools WHERE indicator_id=:id")->execute([':id'=>$id]);
            $base = floatval($data['base_balance']);
            foreach ($pools as $i => $pool) {
                $alloc = round($base * floatval($pool['percentage']) / 100, 2);
                $ps    = $this->db->prepare("INSERT INTO indicator_pools (indicator_id,pool_name,pool_slug,pool_type,percentage,allocated_amount,color,display_order)
                    VALUES (:ind,:name,:slug,:type,:pct,:alloc,:color,:ord)");
                $ps->execute([':ind'=>$id,':name'=>$pool['pool_name'],':slug'=>$pool['pool_slug']??strtolower(str_replace(' ','_',$pool['pool_name'])),
                    ':type'=>$pool['pool_type']??'department',':pct'=>$pool['percentage'],
                    ':alloc'=>$alloc,':color'=>$pool['color']??'#377dff',':ord'=>$i]);
            }
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function confirmIndicator($id, $userId)
    {
        try {
            $s = $this->db->prepare("UPDATE budget_indicators SET status='confirmed',confirmed_by=:by,confirmed_at=NOW() WHERE id=:id");
            $s->execute([':by'=>$userId,':id'=>$id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function deleteIndicator($id, $userId = null, $isSuperAdmin = false)
    {
        try {
            // Check if indicator exists
            $ind = $this->getIndicatorById($id);
            if (!$ind) {
                return ['success' => false, 'message' => 'Indicator not found'];
            }
            
            // Check for approved quarters - cannot delete if there are approved quarterly budgets
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM budget_quarters 
                WHERE indicator_id = :id AND status = 'approved'
            ");
            $stmt->execute([':id' => $id]);
            $approvedCount = (int)$stmt->fetchColumn();
            
            if ($approvedCount > 0) {
                return [
                    'success' => false, 
                    'message' => 'Cannot delete: This indicator has approved quarterly budgets. You must delete the quarterly budgets first.'
                ];
            }
            
            // Check for any pending/suspended quarters
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM budget_quarters 
                WHERE indicator_id = :id AND status IN ('draft', 'suspended')
            ");
            $stmt->execute([':id' => $id]);
            $otherQuartersCount = (int)$stmt->fetchColumn();
            
            // Begin transaction
            $this->db->beginTransaction();
            
            // First, delete all associated activities (through quarters)
            // This handles foreign key constraints properly
            if ($otherQuartersCount > 0) {
                // Get all quarter IDs for this indicator
                $stmt = $this->db->prepare("SELECT id FROM budget_quarters WHERE indicator_id = :id");
                $stmt->execute([':id' => $id]);
                $quarters = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (!empty($quarters)) {
                    $placeholders = implode(',', array_fill(0, count($quarters), '?'));
                    
                    // Delete activities for these quarters
                    $stmt = $this->db->prepare("DELETE FROM budget_activities WHERE quarter_id IN ($placeholders)");
                    $stmt->execute($quarters);
                }
            }
            
            // Delete all quarters for this indicator
            $stmt = $this->db->prepare("DELETE FROM budget_quarters WHERE indicator_id = :id");
            $stmt->execute([':id' => $id]);
            $deletedQuarters = $stmt->rowCount();
            
            // Delete all pools for this indicator
            $stmt = $this->db->prepare("DELETE FROM indicator_pools WHERE indicator_id = :id");
            $stmt->execute([':id' => $id]);
            $deletedPools = $stmt->rowCount();
            
            // Finally, delete the indicator itself
            $stmt = $this->db->prepare("DELETE FROM budget_indicators WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $deletedIndicator = $stmt->rowCount();
            
            if ($deletedIndicator === 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Failed to delete indicator'];
            }
            
            $this->db->commit();
            
            // Log the action if user ID is provided
            if ($userId) {
                try {
                    $logStmt = $this->db->prepare("
                        INSERT INTO user_activity_log 
                        (user_id, action, module, description, ip_address, created_at) 
                        VALUES (?, 'delete', 'finance', ?, ?, NOW())
                    ");
                    
                    $description = sprintf(
                        "Deleted budget indicator ID: %d - Session: %s, Year: %s, Base Balance: %.2f (Deleted %d quarters, %d pools)",
                        $id,
                        $ind['cep_session'] ?? 'unknown',
                        $ind['academic_year'] ?? 'unknown',
                        $ind['base_balance'] ?? 0,
                        $deletedQuarters,
                        $deletedPools
                    );
                    
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                    
                    $logStmt->execute([$userId, $description, $ipAddress]);
                } catch (Exception $logError) {
                    // Logging failed but main operation succeeded - just log error
                    error_log("Failed to log indicator deletion: " . $logError->getMessage());
                }
            }
            
            return [
                'success' => true,
                'message' => 'Indicator deleted successfully',
                'details' => [
                    'deleted_indicator' => true,
                    'deleted_quarters' => $deletedQuarters,
                    'deleted_pools' => $deletedPools
                ]
            ];
            
        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Database error in deleteIndicator: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Database error occurred: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("General error in deleteIndicator: " . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    public function lockExpiredIndicators()
    {
        try {
            $this->db->exec("UPDATE budget_indicators SET status='locked' WHERE status='confirmed' AND lock_date IS NOT NULL AND lock_date < CURDATE()");
        } catch (Exception $e) { error_log("lockExpiredIndicators: " . $e->getMessage()); }
    }

    // ═══════════════════════════════════════════════════════════
    // BUDGET QUARTERS
    // ═══════════════════════════════════════════════════════════

    public function getQuartersByIndicator($indicatorId)
    {
        try {
            $s = $this->db->prepare("SELECT bq.*,
                COALESCE(SUM(ba.allocated_amount),0) AS line_allocated,
                COALESCE(SUM(ba.spent_amount),0)     AS line_spent,
                CONCAT(u.firstname,' ',u.lastname)   AS created_by_name,
                CONCAT(ua.firstname,' ',ua.lastname) AS approved_by_name,
                DATEDIFF(NOW(), bq.draft_created_at) AS days_in_draft
                FROM budget_quarters bq
                LEFT JOIN budget_activities ba ON ba.quarter_id = bq.id
                LEFT JOIN users u  ON u.id  = bq.created_by
                LEFT JOIN users ua ON ua.id = bq.approved_by
                WHERE bq.indicator_id=:ind
                GROUP BY bq.id ORDER BY FIELD(bq.quarter,'Q1','Q2','Q3')");
            $s->execute([':ind' => $indicatorId]);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getQuarterById($id)
    {
        try {
            $s = $this->db->prepare("SELECT bq.*, bi.base_balance,
                CONCAT(u.firstname,' ',u.lastname)   AS created_by_name,
                CONCAT(ua.firstname,' ',ua.lastname) AS approved_by_name,
                DATEDIFF(NOW(), bq.draft_created_at) AS days_in_draft
                FROM budget_quarters bq
                JOIN budget_indicators bi ON bi.id = bq.indicator_id
                LEFT JOIN users u  ON u.id  = bq.created_by
                LEFT JOIN users ua ON ua.id = bq.approved_by
                WHERE bq.id=:id LIMIT 1");
            $s->execute([':id' => $id]);
            $q = $s->fetch(PDO::FETCH_ASSOC);
            if ($q) {
                // Get activities grouped by pool
                $as = $this->db->prepare("SELECT ba.*, ip.pool_name, ip.pool_slug, ip.color, ip.pool_type
                    FROM budget_activities ba
                    JOIN indicator_pools ip ON ip.id = ba.pool_id
                    WHERE ba.quarter_id=:id ORDER BY ip.display_order, ba.id");
                $as->execute([':id' => $id]);
                $q['activities'] = $as->fetchAll(PDO::FETCH_ASSOC);
            }
            return $q;
        } catch (Exception $e) { return null; }
    }

    public function createQuarter($data, $activities, $createdBy)
    {
        try {
            // Check indicator exists and is confirmed
            $ind = $this->getIndicatorById($data['indicator_id']);
            if (!$ind) return ['success'=>false,'message'=>'Budget indicators not found'];
            if ($ind['status'] === 'draft') return ['success'=>false,'message'=>'Budget indicators must be confirmed before creating quarterly budgets'];

            // Check quarter not already created
            $chk = $this->db->prepare("SELECT id FROM budget_quarters WHERE indicator_id=:ind AND quarter=:q");
            $chk->execute([':ind'=>$data['indicator_id'],':q'=>$data['quarter']]);
            if ($chk->fetch()) return ['success'=>false,'message'=>"A {$data['quarter']} budget already exists for this indicator"];

            $this->db->beginTransaction();
            $s = $this->db->prepare("INSERT INTO budget_quarters (indicator_id,cep_session,academic_year,quarter,budget_name,status,draft_created_at,notes,created_by)
                VALUES (:ind,:sess,:year,:q,:name,'draft',NOW(),:notes,:by)");
            $s->execute([':ind'=>$data['indicator_id'],':sess'=>$data['cep_session'],
                ':year'=>$data['academic_year'],':q'=>$data['quarter'],
                ':name'=>$data['budget_name'],':notes'=>$data['notes']??null,':by'=>$createdBy]);
            $qid = $this->db->lastInsertId();

            $totalAlloc = 0;
            foreach ($activities as $act) {
                $as = $this->db->prepare("INSERT INTO budget_activities (quarter_id,pool_id,activity_name,allocated_amount,is_external,notes)
                    VALUES (:qid,:pid,:name,:amt,:ext,:notes)");
                $amt = floatval($act['allocated_amount'] ?? 0);
                $as->execute([':qid'=>$qid,':pid'=>$act['pool_id'],':name'=>$act['activity_name'],
                    ':amt'=>$amt,':ext'=>$act['is_external']??0,':notes'=>$act['notes']??null]);
                if (!($act['is_external']??false)) $totalAlloc += $amt;
            }
            $this->db->prepare("UPDATE budget_quarters SET total_allocated=:t WHERE id=:id")->execute([':t'=>$totalAlloc,':id'=>$qid]);
            $this->db->commit();
            return ['success' => true, 'id' => $qid];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateQuarter($id, $data, $activities)
    {
        try {
            $q = $this->getQuarterById($id);
            if (!$q) return ['success'=>false,'message'=>'Budget not found'];
            if ($q['status'] === 'approved') return ['success'=>false,'message'=>'Approved budgets cannot be edited. Only Super Admin can delete.'];
            if ($q['status'] === 'suspended') return ['success'=>false,'message'=>'Suspended budget. Contact Super Admin to reactivate.'];

            $this->db->beginTransaction();
            $s = $this->db->prepare("UPDATE budget_quarters SET budget_name=:name,notes=:notes WHERE id=:id");
            $s->execute([':name'=>$data['budget_name'],':notes'=>$data['notes']??null,':id'=>$id]);

            $this->db->prepare("DELETE FROM budget_activities WHERE quarter_id=:id")->execute([':id'=>$id]);
            $totalAlloc = 0;
            foreach ($activities as $act) {
                $as = $this->db->prepare("INSERT INTO budget_activities (quarter_id,pool_id,activity_name,allocated_amount,is_external,notes)
                    VALUES (:qid,:pid,:name,:amt,:ext,:notes)");
                $amt = floatval($act['allocated_amount'] ?? 0);
                $as->execute([':qid'=>$id,':pid'=>$act['pool_id'],':name'=>$act['activity_name'],
                    ':amt'=>$amt,':ext'=>$act['is_external']??0,':notes'=>$act['notes']??null]);
                if (!($act['is_external']??false)) $totalAlloc += $amt;
            }
            $this->db->prepare("UPDATE budget_quarters SET total_allocated=:t WHERE id=:id")->execute([':t'=>$totalAlloc,':id'=>$id]);
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function approveQuarter($id, $userId)
    {
        try {
            $q = $this->getQuarterById($id);
            if (!$q) return ['success'=>false,'message'=>'Budget not found'];
            if ($q['status'] !== 'draft') return ['success'=>false,'message'=>"Cannot approve a {$q['status']} budget"];
            $s = $this->db->prepare("UPDATE budget_quarters SET status='approved',approved_by=:by,approved_at=NOW() WHERE id=:id");
            $s->execute([':by'=>$userId,':id'=>$id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    public function reactivateQuarter($id)
    {
        try {
            $this->db->prepare("UPDATE budget_quarters SET status='draft',draft_created_at=NOW() WHERE id=:id AND status='suspended'")
                     ->execute([':id'=>$id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    public function deleteQuarter($id)
    {
        try {
            $this->db->prepare("DELETE FROM budget_quarters WHERE id=:id")->execute([':id'=>$id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    // Helper: get active approved quarter for a session
    public function getActiveQuarter($session, $year = null)
    {
        try {
            $year = $year ?: date('Y');
            $s = $this->db->prepare("SELECT bq.*, bi.id AS indicator_id FROM budget_quarters bq
                JOIN budget_indicators bi ON bi.id = bq.indicator_id
                WHERE bq.cep_session=:s AND bq.academic_year LIKE :y AND bq.status='approved'
                ORDER BY FIELD(bq.quarter,'Q1','Q2','Q3') DESC LIMIT 1");
            $s->execute([':s'=>$session,':y'=>"$year%"]);
            return $s->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return null; }
    }

    // Pool remaining budget (allocated - spent in that quarter's activities)
    public function getPoolRemaining($poolId, $quarterId)
    {
        try {
            $s = $this->db->prepare("SELECT COALESCE(SUM(allocated_amount),0) - COALESCE(SUM(spent_amount),0) AS remaining
                FROM budget_activities WHERE pool_id=:p AND quarter_id=:q AND is_external=0");
            $s->execute([':p'=>$poolId,':q'=>$quarterId]);
            return (float)$s->fetchColumn();
        } catch (Exception $e) { return 0; }
    }

    // ═══════════════════════════════════════════════════════════
    // FUND REQUESTS — new workflow
    // ═══════════════════════════════════════════════════════════

    public function getFundRequests($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $w = "WHERE 1=1"; $p = [];
            if (!empty($filters['session']))      { $w .= " AND fr.cep_session=:s";    $p[':s'] = $filters['session']; }
            if (!empty($filters['stage']))        { $w .= " AND fr.stage=:st";         $p[':st'] = $filters['stage']; }
            if (!empty($filters['requested_by'])) { $w .= " AND fr.requested_by=:rb";  $p[':rb'] = $filters['requested_by']; }
            if (!empty($filters['indicator_id'])) { $w .= " AND fr.indicator_id=:ind"; $p[':ind'] = $filters['indicator_id']; }
            if (!empty($filters['search']))       { $w .= " AND (fr.title LIKE :sq OR fr.request_number LIKE :sq)"; $p[':sq'] = '%'.$filters['search'].'%'; }
            // Visibility rules handled in controller — here we just filter
            $off = ($page-1)*$perPage;
            $s   = $this->db->prepare("SELECT fr.*,
                ip.pool_name AS indicator_name, ip.color AS indicator_color,
                bq.quarter, bq.budget_name AS quarter_name,
                CONCAT(req.firstname,' ',req.lastname)  AS requested_by_name,
                CONCAT(app.firstname,' ',app.lastname)  AS approved_by_name,
                CONCAT(dis.firstname,' ',dis.lastname)  AS disbursed_by_name
                FROM fund_requests fr
                LEFT JOIN indicator_pools ip   ON ip.id  = fr.indicator_id
                LEFT JOIN budget_quarters bq   ON bq.id  = fr.budget_quarter_id
                LEFT JOIN users req ON req.id = fr.requested_by
                LEFT JOIN users app ON app.id = fr.approved_by
                LEFT JOIN disbursements d ON d.request_id = fr.id
                LEFT JOIN users dis ON dis.id = d.disbursed_by
                $w ORDER BY fr.created_at DESC LIMIT :lim OFFSET :off");
            $s->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $s->bindValue(':off', (int)$off,     PDO::PARAM_INT);
            foreach ($p as $k => $v) $s->bindValue($k, $v);
            $s->execute();
            $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            $cnt  = $this->db->prepare("SELECT COUNT(*) FROM fund_requests fr $w");
            foreach ($p as $k => $v) $cnt->bindValue($k, $v);
            $cnt->execute();
            $total = (int)$cnt->fetchColumn();
            return ['data'=>$rows,'total'=>$total,'pages'=>max(1,ceil($total/$perPage))];
        } catch (Exception $e) { return ['data'=>[],'total'=>0,'pages'=>0]; }
    }

    public function getFundRequestById($id)
    {
        try {
            $s = $this->db->prepare("SELECT fr.*,
                ip.pool_name AS indicator_name, ip.color AS indicator_color, ip.pool_slug,
                bq.quarter, bq.budget_name AS quarter_name,
                ba.activity_name AS budget_activity_name,
                CONCAT(req.firstname,' ',req.lastname) AS requested_by_name, req.email AS requester_email,
                CONCAT(app.firstname,' ',app.lastname) AS approved_by_name
                FROM fund_requests fr
                LEFT JOIN indicator_pools ip  ON ip.id  = fr.indicator_id
                LEFT JOIN budget_quarters bq  ON bq.id  = fr.budget_quarter_id
                LEFT JOIN budget_activities ba ON ba.id = fr.activity_id
                LEFT JOIN users req ON req.id = fr.requested_by
                LEFT JOIN users app ON app.id = fr.approved_by
                WHERE fr.id=:id LIMIT 1");
            $s->execute([':id'=>$id]);
            $req = $s->fetch(PDO::FETCH_ASSOC);
            if ($req) {
                $cs = $this->db->prepare("SELECT c.*, CONCAT(u.firstname,' ',u.lastname) AS user_name, u.photo
                    FROM fund_request_comments c JOIN users u ON u.id=c.user_id
                    WHERE c.request_id=:id ORDER BY c.created_at ASC");
                $cs->execute([':id'=>$id]);
                $req['comments'] = $cs->fetchAll(PDO::FETCH_ASSOC);
                // Disbursement if completed
                $ds = $this->db->prepare("SELECT d.*, CONCAT(u.firstname,' ',u.lastname) AS disbursed_by_name
                    FROM disbursements d LEFT JOIN users u ON u.id=d.disbursed_by WHERE d.request_id=:id LIMIT 1");
                $ds->execute([':id'=>$id]);
                $req['disbursement'] = $ds->fetch(PDO::FETCH_ASSOC) ?: null;
            }
            return $req;
        } catch (Exception $e) { return null; }
    }

    public function createFundRequest($data, $userId)
    {
        try {
            $year   = date('Y');
            $cntS   = $this->db->prepare("SELECT COUNT(*)+1 FROM fund_requests WHERE YEAR(created_at)=:y");
            $cntS->execute([':y'=>$year]);
            $seq    = str_pad((int)$cntS->fetchColumn(), 3, '0', STR_PAD_LEFT);
            $reqNum = "FR-$year-$seq";

            $s = $this->db->prepare("INSERT INTO fund_requests
                (cep_session,request_number,title,description,indicator_id,budget_quarter_id,activity_id,
                 amount_requested,stage,requested_by,priority,needed_by_date)
                VALUES (:sess,:num,:title,:desc,:ind,:bq,:act,:amt,'draft',:by,:pri,:need)");
            $s->execute([':sess'=>$data['cep_session'],':num'=>$reqNum,':title'=>$data['title'],
                ':desc'=>$data['description'],':ind'=>$data['indicator_id']??null,
                ':bq'=>$data['budget_quarter_id']??null,':act'=>$data['activity_id']??null,
                ':amt'=>$data['amount_requested'],':by'=>$userId,
                ':pri'=>$data['priority']??'medium',':need'=>$data['needed_by_date']??null]);
            return ['success'=>true,'id'=>$this->db->lastInsertId(),'request_number'=>$reqNum];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    public function updateFundRequest($id, $data, $userId)
    {
        try {
            $existing = $this->getFundRequestById($id);
            if (!$existing) return ['success'=>false,'message'=>'Request not found'];
            if ($existing['requested_by'] != $userId) return ['success'=>false,'message'=>'Not authorized'];
            if (!in_array($existing['stage'], ['draft','rejected_by_president'])) return ['success'=>false,'message'=>'Cannot edit a submitted request'];

            $s = $this->db->prepare("UPDATE fund_requests SET title=:t,description=:d,indicator_id=:ind,
                budget_quarter_id=:bq,activity_id=:act,amount_requested=:amt,priority=:pri,needed_by_date=:need
                WHERE id=:id");
            $s->execute([':t'=>$data['title'],':d'=>$data['description'],':ind'=>$data['indicator_id']??null,
                ':bq'=>$data['budget_quarter_id']??null,':act'=>$data['activity_id']??null,
                ':amt'=>$data['amount_requested'],':pri'=>$data['priority']??'medium',
                ':need'=>$data['needed_by_date']??null,':id'=>$id]);
            return ['success'=>true];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    public function submitFundRequest($id, $userId)
    {
        try {
            $fr = $this->getFundRequestById($id);
            if (!$fr) return ['success'=>false,'message'=>'Request not found'];
            if ($fr['requested_by'] != $userId) return ['success'=>false,'message'=>'Not authorized'];
            if (!in_array($fr['stage'], ['draft','rejected_by_president'])) return ['success'=>false,'message'=>'Cannot submit a request in this state'];

            // Check budget: if indicator/quarter set, verify amount fits
            if ($fr['indicator_id'] && $fr['budget_quarter_id']) {
                $rem = $this->getPoolRemaining($fr['indicator_id'], $fr['budget_quarter_id']);
                if (floatval($fr['amount_requested']) > $rem + 1) { // +1 for rounding
                    return ['success'=>false,'message'=>"Amount exceeds available pool budget. Remaining: RWF ".number_format($rem)];
                }
            }

            $s = $this->db->prepare("UPDATE fund_requests SET stage='to_president',submitted_at=NOW() WHERE id=:id");
            $s->execute([':id'=>$id]);
            return ['success'=>true];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    public function deleteFundRequest($id, $userId, $isSuperAdmin = false)
    {
        try {
            $fr = $this->getFundRequestById($id);
            if (!$fr) return ['success'=>false,'message'=>'Not found'];
            if (!$isSuperAdmin && $fr['requested_by'] != $userId) return ['success'=>false,'message'=>'Not authorized'];
            if (in_array($fr['stage'], ['to_finance','completed'])) return ['success'=>false,'message'=>'Cannot delete an approved or completed request'];
            $this->db->prepare("DELETE FROM fund_requests WHERE id=:id")->execute([':id'=>$id]);
            return ['success'=>true];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    public function addFundRequestComment($requestId, $userId, $comment)
    {
        try {
            $s = $this->db->prepare("INSERT INTO fund_request_comments (request_id,user_id,comment) VALUES (:rid,:uid,:c)");
            $s->execute([':rid'=>$requestId,':uid'=>$userId,':c'=>$comment]);
            return ['success'=>true,'id'=>$this->db->lastInsertId()];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    /** President: approve or reject */
    public function presidentAction($id, $action, $userId, $data = [])
    {
        try {
            $fr = $this->getFundRequestById($id);
            if (!$fr) return ['success'=>false,'message'=>'Not found'];
            if ($fr['stage'] !== 'to_president') return ['success'=>false,'message'=>'Request is not pending president review'];

            if ($action === 'approve') {
                $s = $this->db->prepare("UPDATE fund_requests SET stage='to_finance',approved_by=:by,approved_at=NOW(),amount_approved=:amt WHERE id=:id");
                $s->execute([':by'=>$userId,':amt'=>$data['amount_approved']??$fr['amount_requested'],':id'=>$id]);
                if (!empty($data['comment'])) $this->addFundRequestComment($id,$userId,$data['comment']);
            } elseif ($action === 'reject') {
                $reason = $data['rejection_reason'] ?? $data['comment'] ?? 'Rejected by President';
                $s = $this->db->prepare("UPDATE fund_requests SET stage='rejected_by_president',rejection_reason=:r WHERE id=:id");
                $s->execute([':r'=>$reason,':id'=>$id]);
                $this->addFundRequestComment($id,$userId,$reason);
            } else {
                return ['success'=>false,'message'=>'Invalid action'];
            }
            return ['success'=>true];
        } catch (Exception $e) { return ['success'=>false,'message'=>$e->getMessage()]; }
    }

    /** Finance/Accountant: disburse */
    public function disburse($id, $userId, $data = [])
    {
        try {
            $fr = $this->getFundRequestById($id);
            if (!$fr) return ['success' => false, 'message' => 'Request not found'];
            
            if ($fr['stage'] !== 'to_finance') {
                return ['success' => false, 'message' => 'Request must be approved by president before disbursement'];
            }

            $this->db->beginTransaction();
            
            // Update request status
            $s = $this->db->prepare("UPDATE fund_requests SET stage='completed' WHERE id=:id");
            $s->execute([':id' => $id]);

            // Insert disbursement record
            $ds = $this->db->prepare("INSERT INTO disbursements (request_id, cep_session, amount, payment_method, reference_no, recipient_name, notes, disbursed_by, disbursed_at) 
                VALUES (:rid, :sess, :amt, :method, :ref, :recip, :notes, :by, NOW())");
            $ds->execute([
                ':rid' => $id,
                ':sess' => $fr['cep_session'],
                ':amt' => $data['amount'] ?? $fr['amount_approved'],
                ':method' => $data['payment_method'] ?? 'cash',
                ':ref' => $data['reference_no'] ?? null,
                ':recip' => $data['recipient_name'] ?? null,
                ':notes' => $data['notes'] ?? null,
                ':by' => $userId
            ]);

            // Update activity spent amount if linked
            if ($fr['activity_id']) {
                $ua = $this->db->prepare("UPDATE budget_activities SET spent_amount = spent_amount + :amt WHERE id=:id");
                $ua->execute([
                    ':amt' => $data['amount'] ?? $fr['amount_approved'],
                    ':id' => $fr['activity_id']
                ]);
            }
            
            $this->db->commit();
            
            // Log the action
            $logStmt = $this->db->prepare("
                INSERT INTO user_activity_log (user_id, action, module, description, created_at)
                VALUES (?, 'disburse', 'finance', ?, NOW())
            ");
            $logStmt->execute([$userId, "Disbursed funds for request ID: $id - Amount: " . ($data['amount'] ?? $fr['amount_approved'])]);
            
            return ['success' => true];
            
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("disburse error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getFundRequestPipelineCounts($session = null)
    {
        try {
            $w = $session ? "WHERE cep_session=:s" : ""; $p = $session ? [':s'=>$session] : [];
            $s = $this->db->prepare("SELECT stage, COUNT(*) AS cnt, COALESCE(SUM(amount_requested),0) AS total_amt FROM fund_requests $w GROUP BY stage");
            $s->execute($p);
            $map = []; foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $r) $map[$r['stage']] = $r;
            return $map;
        } catch (Exception $e) { return []; }
    }

    // ═══════════════════════════════════════════════════════════
    // DISBURSEMENTS
    // ═══════════════════════════════════════════════════════════

    public function getDisbursements($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $w = "WHERE 1=1"; $p = [];
            if (!empty($filters['session'])) { $w .= " AND d.cep_session=:s";  $p[':s'] = $filters['session']; }
            if (!empty($filters['method']))  { $w .= " AND d.payment_method=:m"; $p[':m'] = $filters['method']; }
            if (!empty($filters['month']))   {
                $w .= " AND MONTH(d.disbursed_at)=:mo AND YEAR(d.disbursed_at)=:yr";
                [$yr,$mo] = explode('-',$filters['month']); $p[':mo']=$mo; $p[':yr']=$yr;
            }
            $off = ($page-1)*$perPage;
            $s = $this->db->prepare("SELECT d.*, fr.title AS request_title, fr.request_number, fr.cep_session,
                ip.pool_name AS indicator_name,
                CONCAT(u.firstname,' ',u.lastname) AS disbursed_by_name,
                CONCAT(req.firstname,' ',req.lastname) AS requested_by_name
                FROM disbursements d
                JOIN fund_requests fr ON fr.id = d.request_id
                LEFT JOIN indicator_pools ip ON ip.id = fr.indicator_id
                LEFT JOIN users u   ON u.id   = d.disbursed_by
                LEFT JOIN users req ON req.id = fr.requested_by
                $w ORDER BY d.disbursed_at DESC LIMIT :lim OFFSET :off");
            $s->bindValue(':lim',(int)$perPage,PDO::PARAM_INT);
            $s->bindValue(':off',(int)$off,    PDO::PARAM_INT);
            foreach ($p as $k=>$v) $s->bindValue($k,$v);
            $s->execute();
            $rows = $s->fetchAll(PDO::FETCH_ASSOC);
            $cnt  = $this->db->prepare("SELECT COUNT(*) FROM disbursements d JOIN fund_requests fr ON fr.id=d.request_id $w");
            foreach ($p as $k=>$v) $cnt->bindValue($k,$v);
            $cnt->execute();
            $total = (int)$cnt->fetchColumn();
            return ['data'=>$rows,'total'=>$total,'pages'=>max(1,ceil($total/$perPage))];
        } catch (Exception $e) { return ['data'=>[],'total'=>0,'pages'=>0]; }
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTS
    // ═══════════════════════════════════════════════════════════

    public function getFinanceOverview($session = null, $year = null)
    {
        try {
            $year = $year ?: date('Y');
            $w = "WHERE YEAR(revenue_date)=:y"; $p = [':y'=>$year];
            if ($session) { $w .= " AND cep_session=:s"; $p[':s']=$session; }
            $rs = $this->db->prepare("SELECT COALESCE(SUM(amount),0) AS total_revenue FROM finance_revenue $w");
            $rs->execute($p);

            $fw = "WHERE YEAR(created_at)=:y AND stage='completed'"; $fp = [':y'=>$year];
            if ($session) { $fw .= " AND cep_session=:s"; $fp[':s']=$session; }
            $fs = $this->db->prepare("SELECT COALESCE(SUM(amount_approved),0) AS total_expenses FROM fund_requests $fw");
            $fs->execute($fp);

            $frSt = $this->db->prepare("SELECT stage, COUNT(*) AS count, COALESCE(SUM(amount_requested),0) AS requested
                FROM fund_requests WHERE YEAR(created_at)=:y".($session?" AND cep_session=:s":"")." GROUP BY stage");
            $frStP = [':y'=>$year]; if ($session) $frStP[':s']=$session;
            $frSt->execute($frStP);

            $rev = floatval($rs->fetchColumn());
            $exp = floatval($fs->fetchColumn());
            return ['summary'=>['total_revenue'=>$rev,'total_expenses'=>$exp,'net_balance'=>$rev-$exp],
                'monthly'=>$this->getMonthlyTrend($session,$year),
                'fund_requests'=>$frSt->fetchAll(PDO::FETCH_ASSOC)];
        } catch (Exception $e) { return []; }
    }

    /** Budget Distribution Report: pool-level breakdown */
    public function getBudgetDistributionReport($session, $year, $quarterId = null)
    {
        try {
            $w = "WHERE bq.status='approved' AND bq.cep_session=:s AND bq.academic_year LIKE :y";
            $p = [':s'=>$session,':y'=>"$year%"];
            if ($quarterId) { $w .= " AND bq.id=:q"; $p[':q']=$quarterId; }
            $s = $this->db->prepare("SELECT bq.quarter, bq.budget_name,
                ip.pool_name, ip.pool_slug, ip.percentage, ip.allocated_amount AS pool_total,
                COALESCE(SUM(ba.allocated_amount),0) AS activity_allocated,
                COALESCE(SUM(ba.spent_amount),0)     AS activity_spent,
                COALESCE(SUM(CASE WHEN ba.is_external=1 THEN ba.allocated_amount END),0) AS external_tracked
                FROM budget_quarters bq
                JOIN budget_indicators bi ON bi.id = bq.indicator_id
                JOIN indicator_pools   ip ON ip.indicator_id = bi.id
                LEFT JOIN budget_activities ba ON ba.quarter_id=bq.id AND ba.pool_id=ip.id
                $w GROUP BY bq.id, ip.id ORDER BY FIELD(bq.quarter,'Q1','Q2','Q3'), ip.display_order");
            $s->execute($p);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    /** Disbursement Report */
    public function getDisbursementReport($session, $year, $filters = [])
    {
        try {
            $w = "WHERE YEAR(d.disbursed_at)=:y AND d.cep_session=:s"; $p = [':y'=>$year,':s'=>$session];
            if (!empty($filters['pool_id'])) { $w .= " AND fr.indicator_id=:ind"; $p[':ind']=$filters['pool_id']; }
            if (!empty($filters['method']))  { $w .= " AND d.payment_method=:m";  $p[':m']=$filters['method']; }
            $s = $this->db->prepare("SELECT d.id, d.disbursed_at, d.amount, d.payment_method, d.reference_no, d.recipient_name,
                fr.request_number, fr.title, fr.cep_session,
                ip.pool_name AS indicator_name,
                bq.quarter,
                CONCAT(ub.firstname,' ',ub.lastname) AS disbursed_by_name,
                CONCAT(ur.firstname,' ',ur.lastname) AS requested_by_name
                FROM disbursements d
                JOIN fund_requests fr ON fr.id = d.request_id
                LEFT JOIN indicator_pools ip ON ip.id = fr.indicator_id
                LEFT JOIN budget_quarters bq ON bq.id = fr.budget_quarter_id
                LEFT JOIN users ub ON ub.id = d.disbursed_by
                LEFT JOIN users ur ON ur.id = fr.requested_by
                $w ORDER BY d.disbursed_at DESC");
            $s->execute($p);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    /** Fund Request Report (submitted and above) */
    public function getFundRequestReport($session, $year, $filters = [])
    {
        try {
            $w = "WHERE YEAR(fr.created_at)=:y AND fr.cep_session=:s AND fr.stage != 'draft'";
            $p = [':y'=>$year,':s'=>$session];
            if (!empty($filters['stage']))    { $w .= " AND fr.stage=:st";        $p[':st']=$filters['stage']; }
            if (!empty($filters['pool_id']))  { $w .= " AND fr.indicator_id=:ind"; $p[':ind']=$filters['pool_id']; }
            $s = $this->db->prepare("SELECT fr.id, fr.request_number, fr.title, fr.stage, fr.amount_requested, fr.amount_approved,
                fr.cep_session, fr.submitted_at, fr.approved_at, fr.created_at,
                ip.pool_name AS indicator_name, bq.quarter,
                CONCAT(ur.firstname,' ',ur.lastname) AS requested_by_name,
                CONCAT(ua.firstname,' ',ua.lastname) AS approved_by_name,
                fr.rejection_reason
                FROM fund_requests fr
                LEFT JOIN indicator_pools ip ON ip.id = fr.indicator_id
                LEFT JOIN budget_quarters bq ON bq.id = fr.budget_quarter_id
                LEFT JOIN users ur ON ur.id = fr.requested_by
                LEFT JOIN users ua ON ua.id = fr.approved_by
                $w ORDER BY fr.created_at DESC");
            $s->execute($p);
            return $s->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }
}