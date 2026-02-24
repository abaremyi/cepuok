<?php
/**
 * Finance Model
 * File: modules/Finance/models/FinanceModel.php
 */

class FinanceModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    // ─────────────────────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────────────────────

    public function getDashboardStats($session = null)
    {
        try {
            $where  = $session ? "WHERE cep_session = :session" : "";
            $params = $session ? [':session' => $session] : [];

            // Revenue totals
            $rev = $this->db->prepare("SELECT
                    COALESCE(SUM(amount),0)                               AS total_revenue,
                    COALESCE(SUM(CASE WHEN MONTH(revenue_date)=MONTH(NOW()) AND YEAR(revenue_date)=YEAR(NOW()) THEN amount END),0) AS this_month,
                    COALESCE(SUM(CASE WHEN WEEK(revenue_date)=WEEK(NOW())  AND YEAR(revenue_date)=YEAR(NOW())  THEN amount END),0) AS this_week
                FROM finance_revenue $where");
            $rev->execute($params);
            $revData = $rev->fetch(PDO::FETCH_ASSOC);

            // Fund requests totals
            $wFR = $session ? "AND cep_session = :session" : "";
            $fr = $this->db->prepare("SELECT
                    COUNT(*) AS total_requests,
                    COALESCE(SUM(CASE WHEN stage='pending'   THEN 1 END),0) AS pending,
                    COALESCE(SUM(CASE WHEN stage='approved'  THEN 1 END),0) AS approved,
                    COALESCE(SUM(CASE WHEN stage='disbursed' THEN amount_approved END),0) AS total_disbursed,
                    COALESCE(SUM(CASE WHEN stage='pending' OR stage='reviewing' THEN amount_requested END),0) AS pending_amount
                FROM fund_requests WHERE 1=1 $wFR");
            $fr->execute($params);
            $frData = $fr->fetch(PDO::FETCH_ASSOC);

            // Budget utilisation
            $bq = $this->db->prepare("SELECT
                    COALESCE(SUM(total_amount),0)                   AS total_budget,
                    COALESCE(SUM(bl.line_spent),0)                  AS total_spent
                FROM finance_budgets fb
                LEFT JOIN (SELECT budget_id, SUM(spent_amount) AS line_spent FROM budget_lines GROUP BY budget_id) bl
                       ON bl.budget_id = fb.id
                WHERE fb.status = 'approved'" . ($session ? " AND fb.cep_session = :session" : ""));
            $bq->execute($params);
            $budData = $bq->fetch(PDO::FETCH_ASSOC);

            // Reserve pool = total revenue – total disbursed
            $reserve = $revData['total_revenue'] - ($frData['total_disbursed'] ?? 0);

            return [
                'total_revenue'   => (float)$revData['total_revenue'],
                'this_month'      => (float)$revData['this_month'],
                'this_week'       => (float)$revData['this_week'],
                'total_expenses'  => (float)($frData['total_disbursed'] ?? 0),
                'balance'         => $reserve,
                'reserve_pool'    => $reserve,
                'pending_requests'=> (int)$frData['pending'],
                'pending_amount'  => (float)$frData['pending_amount'],
                'total_budget'    => (float)($budData['total_budget'] ?? 0),
                'budget_spent'    => (float)($budData['total_spent']  ?? 0),
            ];
        } catch (Exception $e) {
            error_log("FinanceModel::getDashboardStats - " . $e->getMessage());
            return [];
        }
    }

    public function getRevenueByType($session = null, $year = null)
    {
        try {
            $year   = $year ?: date('Y');
            $where  = "WHERE YEAR(revenue_date) = :year";
            $params = [':year' => $year];
            if ($session) { $where .= " AND cep_session = :session"; $params[':session'] = $session; }

            $stmt = $this->db->prepare(
                "SELECT revenue_type, COALESCE(SUM(amount),0) AS total
                 FROM finance_revenue $where
                 GROUP BY revenue_type ORDER BY total DESC"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FinanceModel::getRevenueByType - " . $e->getMessage());
            return [];
        }
    }

    public function getMonthlyTrend($session = null, $year = null)
    {
        try {
            $year   = $year ?: date('Y');
            $where  = "WHERE YEAR(revenue_date) = :year";
            $params = [':year' => $year];
            if ($session) { $where .= " AND cep_session = :session"; $params[':session'] = $session; }

            $stmt = $this->db->prepare(
                "SELECT MONTH(revenue_date) AS month, COALESCE(SUM(amount),0) AS total
                 FROM finance_revenue $where
                 GROUP BY MONTH(revenue_date) ORDER BY month"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FinanceModel::getMonthlyTrend - " . $e->getMessage());
            return [];
        }
    }

    public function getSessionSplit($year = null)
    {
        try {
            $year   = $year ?: date('Y');
            $stmt   = $this->db->prepare(
                "SELECT cep_session, COALESCE(SUM(amount),0) AS total
                 FROM finance_revenue WHERE YEAR(revenue_date) = :year
                 GROUP BY cep_session"
            );
            $stmt->execute([':year' => $year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getBudgetUtilisation($session = null)
    {
        try {
            $where  = "WHERE fb.status = 'approved'";
            $params = [];
            if ($session) { $where .= " AND fb.cep_session = :session"; $params[':session'] = $session; }

            $stmt = $this->db->prepare(
                "SELECT fb.id, fb.budget_name, fb.cep_session, fb.total_amount,
                        COALESCE(SUM(bl.spent_amount),0)      AS spent,
                        COALESCE(SUM(bl.allocated_amount),0)  AS allocated
                 FROM finance_budgets fb
                 LEFT JOIN budget_lines bl ON bl.budget_id = fb.id
                 $where
                 GROUP BY fb.id ORDER BY fb.created_at DESC LIMIT 6"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getRecentTransactions($session = null, $limit = 10)
    {
        try {
            $where  = $session ? "WHERE r.cep_session = :session" : "";
            $params = $session ? [':session' => $session] : [];
            $stmt = $this->db->prepare(
                "SELECT r.*, CONCAT(u.firstname,' ',u.lastname) AS recorded_by_name
                 FROM finance_revenue r
                 LEFT JOIN users u ON u.id = r.recorded_by
                 $where
                 ORDER BY r.created_at DESC LIMIT :lim"
            );
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    // ─────────────────────────────────────────────────────────
    // REVENUE CRUD
    // ─────────────────────────────────────────────────────────

    public function getAllRevenue($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where  = "WHERE 1=1";
            $params = [];
            if (!empty($filters['session']))  { $where .= " AND r.cep_session = :session";  $params[':session']  = $filters['session']; }
            if (!empty($filters['type']))     { $where .= " AND r.revenue_type = :type";    $params[':type']     = $filters['type']; }
            if (!empty($filters['month']))    { $where .= " AND MONTH(r.revenue_date)=:mo AND YEAR(r.revenue_date)=:yr";
                [$yr,$mo] = explode('-', $filters['month']);
                $params[':mo'] = $mo; $params[':yr'] = $yr; }

            $offset = ($page - 1) * $perPage;
            $stmt   = $this->db->prepare(
                "SELECT r.*, CONCAT(u.firstname,' ',u.lastname) AS recorded_by_name
                 FROM finance_revenue r
                 LEFT JOIN users u ON u.id = r.recorded_by
                 $where ORDER BY r.revenue_date DESC, r.id DESC
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = $this->db->prepare("SELECT COUNT(*) FROM finance_revenue r $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();

            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function createRevenue($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO finance_revenue (cep_session,revenue_type,amount,description,revenue_date,reference_no,recorded_by)
                 VALUES (:session,:type,:amount,:desc,:date,:ref,:by)"
            );
            $stmt->execute([
                ':session' => $data['cep_session'],
                ':type'    => $data['revenue_type'],
                ':amount'  => $data['amount'],
                ':desc'    => $data['description'] ?? null,
                ':date'    => $data['revenue_date'],
                ':ref'     => $data['reference_no'] ?? null,
                ':by'      => $data['recorded_by'] ?? null,
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getDailyTotal($session, $date = null)
    {
        try {
            $date = $date ?: date('Y-m-d');
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(amount),0) AS total FROM finance_revenue
                 WHERE cep_session = :session AND revenue_date = :date"
            );
            $stmt->execute([':session' => $session, ':date' => $date]);
            return (float)$stmt->fetchColumn();
        } catch (Exception $e) { return 0; }
    }

    // ─────────────────────────────────────────────────────────
    // BUDGETS
    // ─────────────────────────────────────────────────────────

    public function getAllBudgets($filters = [])
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['session'])) { $where .= " AND fb.cep_session=:session"; $params[':session'] = $filters['session']; }
            if (!empty($filters['status']))  { $where .= " AND fb.status=:status";       $params[':status']  = $filters['status'];  }

            $stmt = $this->db->prepare(
                "SELECT fb.*,
                        COALESCE(SUM(bl.allocated_amount),0) AS line_allocated,
                        COALESCE(SUM(bl.spent_amount),0)     AS line_spent,
                        CONCAT(u.firstname,' ',u.lastname)   AS created_by_name
                 FROM finance_budgets fb
                 LEFT JOIN budget_lines bl ON bl.budget_id = fb.id
                 LEFT JOIN users u ON u.id = fb.created_by
                 $where
                 GROUP BY fb.id ORDER BY fb.created_at DESC"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function createBudget($data, $lines = [])
    {
        try {
            $this->db->beginTransaction();
            $stmt = $this->db->prepare(
                "INSERT INTO finance_budgets (cep_session,budget_name,academic_year,total_amount,status,created_by,notes)
                 VALUES (:session,:name,:year,:amount,:status,:by,:notes)"
            );
            $stmt->execute([
                ':session' => $data['cep_session'],
                ':name'    => $data['budget_name'],
                ':year'    => $data['academic_year'],
                ':amount'  => $data['total_amount'],
                ':status'  => $data['status'] ?? 'draft',
                ':by'      => $data['created_by'] ?? null,
                ':notes'   => $data['notes'] ?? null,
            ]);
            $budgetId = $this->db->lastInsertId();

            foreach ($lines as $line) {
                $ls = $this->db->prepare(
                    "INSERT INTO budget_lines (budget_id,department,line_item,allocated_amount)
                     VALUES (:bid,:dept,:item,:amt)"
                );
                $ls->execute([':bid' => $budgetId, ':dept' => $line['department'], ':item' => $line['line_item'], ':amt' => $line['allocated_amount'] ?? 0]);
            }
            $this->db->commit();
            return ['success' => true, 'id' => $budgetId];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getBudgetById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM finance_budgets WHERE id=:id");
            $stmt->execute([':id' => $id]);
            $budget = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($budget) {
                $ls = $this->db->prepare("SELECT * FROM budget_lines WHERE budget_id=:id ORDER BY id");
                $ls->execute([':id' => $id]);
                $budget['lines'] = $ls->fetchAll(PDO::FETCH_ASSOC);
            }
            return $budget;
        } catch (Exception $e) { return null; }
    }

    public function updateBudgetStatus($id, $status, $approverId = null)
    {
        try {
            $stmt = $this->db->prepare(
                "UPDATE finance_budgets SET status=:status, approved_by=:by, approved_at=IF(:status2='approved',NOW(),NULL) WHERE id=:id"
            );
            return $stmt->execute([':status' => $status, ':status2' => $status, ':by' => $approverId, ':id' => $id]);
        } catch (Exception $e) { return false; }
    }

    // ─────────────────────────────────────────────────────────
    // FUND REQUESTS
    // ─────────────────────────────────────────────────────────

    public function getFundRequests($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['session'])) { $where .= " AND fr.cep_session=:session"; $params[':session'] = $filters['session']; }
            if (!empty($filters['stage']))   { $where .= " AND fr.stage=:stage";         $params[':stage']   = $filters['stage'];   }
            if (!empty($filters['search']))  { $where .= " AND fr.title LIKE :search";   $params[':search']  = '%'.$filters['search'].'%'; }

            $offset = ($page - 1) * $perPage;
            $stmt   = $this->db->prepare(
                "SELECT fr.*,
                        CONCAT(req.firstname,' ',req.lastname) AS requested_by_name,
                        CONCAT(rev.firstname,' ',rev.lastname) AS reviewed_by_name,
                        CONCAT(app.firstname,' ',app.lastname) AS approved_by_name
                 FROM fund_requests fr
                 LEFT JOIN users req ON req.id = fr.requested_by
                 LEFT JOIN users rev ON rev.id = fr.reviewed_by
                 LEFT JOIN users app ON app.id = fr.approved_by
                 $where ORDER BY fr.created_at DESC
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = $this->db->prepare("SELECT COUNT(*) FROM fund_requests fr $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function getFundRequestPipelineCounts($session = null)
    {
        try {
            $where  = $session ? "WHERE cep_session=:session" : "";
            $params = $session ? [':session' => $session] : [];
            $stmt   = $this->db->prepare(
                "SELECT stage, COUNT(*) AS cnt, COALESCE(SUM(amount_requested),0) AS total_amt
                 FROM fund_requests $where GROUP BY stage"
            );
            $stmt->execute($params);
            $rows  = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map   = [];
            foreach ($rows as $r) $map[$r['stage']] = $r;
            return $map;
        } catch (Exception $e) { return []; }
    }

    public function createFundRequest($data)
    {
        try {
            // Generate request number
            $year = date('Y');
            $cntStmt = $this->db->prepare("SELECT COUNT(*)+1 FROM fund_requests WHERE YEAR(created_at)=:yr");
            $cntStmt->execute([':yr' => $year]);
            $seq = str_pad((int)$cntStmt->fetchColumn(), 3, '0', STR_PAD_LEFT);
            $reqNum = "FR-$year-$seq";

            $stmt = $this->db->prepare(
                "INSERT INTO fund_requests (cep_session,request_number,title,description,department,amount_requested,stage,requested_by,priority,needed_by_date)
                 VALUES (:session,:num,:title,:desc,:dept,:amt,:stage,:by,:priority,:needed)"
            );
            $stmt->execute([
                ':session'  => $data['cep_session'],
                ':num'      => $reqNum,
                ':title'    => $data['title'],
                ':desc'     => $data['description'],
                ':dept'     => $data['department'] ?? null,
                ':amt'      => $data['amount_requested'],
                ':stage'    => 'pending',
                ':by'       => $data['requested_by'] ?? null,
                ':priority' => $data['priority'] ?? 'medium',
                ':needed'   => $data['needed_by_date'] ?? null,
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId(), 'request_number' => $reqNum];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function advanceFundRequest($id, $action, $userId, $data = [])
    {
        try {
            $this->db->beginTransaction();

            $stageMap = [
                'mark_review' => ['pending',   'reviewing', 'reviewed_by', 'reviewed_at'],
                'approve'     => ['reviewing', 'approved',  'approved_by', 'approved_at'],
                'reject'      => ['reviewing', 'rejected',  'reviewed_by', 'reviewed_at'],
                'disburse'    => ['approved',  'disbursed', null, null],
            ];
            if (!isset($stageMap[$action])) throw new Exception("Unknown action $action");
            [$from, $to, $field, $timeField] = $stageMap[$action];

            $extra = $field ? ", $field=:uid, $timeField=NOW()" : "";
            if ($action === 'reject' && !empty($data['rejection_reason'])) {
                $extra .= ", rejection_reason=:reason";
            }
            if ($action === 'approve' && !empty($data['amount_approved'])) {
                $extra .= ", amount_approved=:approved_amt";
            }

            $stmt = $this->db->prepare("UPDATE fund_requests SET stage=:to $extra WHERE id=:id AND stage=:from");
            $params = [':to' => $to, ':id' => $id, ':from' => $from];
            if ($field)           $params[':uid'] = $userId;
            if ($action === 'reject' && !empty($data['rejection_reason'])) $params[':reason'] = $data['rejection_reason'];
            if ($action === 'approve' && !empty($data['amount_approved']))  $params[':approved_amt'] = $data['amount_approved'];
            $stmt->execute($params);

            if ($action === 'disburse') {
                $ds = $this->db->prepare(
                    "INSERT INTO disbursements (request_id,amount,payment_method,reference_no,recipient_name,notes,disbursed_by)
                     VALUES (:rid,:amt,:method,:ref,:recip,:notes,:by)"
                );
                $ds->execute([
                    ':rid'    => $id,
                    ':amt'    => $data['amount'] ?? 0,
                    ':method' => $data['payment_method'] ?? 'cash',
                    ':ref'    => $data['reference_no'] ?? null,
                    ':recip'  => $data['recipient_name'] ?? null,
                    ':notes'  => $data['notes'] ?? null,
                    ':by'     => $userId,
                ]);
            }
            $this->db->commit();
            return ['success' => true, 'new_stage' => $to];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────
    // DISBURSEMENTS
    // ─────────────────────────────────────────────────────────

    public function getDisbursements($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['session'])) {
                $where .= " AND fr.cep_session=:session"; $params[':session'] = $filters['session'];
            }
            if (!empty($filters['method'])) {
                $where .= " AND d.payment_method=:method"; $params[':method'] = $filters['method'];
            }
            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare(
                "SELECT d.*, fr.title AS request_title, fr.request_number, fr.cep_session,
                        CONCAT(u.firstname,' ',u.lastname) AS disbursed_by_name
                 FROM disbursements d
                 JOIN fund_requests fr ON fr.id = d.request_id
                 LEFT JOIN users u ON u.id = d.disbursed_by
                 $where ORDER BY d.disbursed_at DESC LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = $this->db->prepare("SELECT COUNT(*) FROM disbursements d JOIN fund_requests fr ON fr.id=d.request_id $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            return ['data' => $rows, 'total' => (int)$count->fetchColumn(), 'pages' => ceil((int)$count->fetchColumn() / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }
}