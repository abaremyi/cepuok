<?php
/**
 * Supporters Model
 * File: modules/Supporters/models/SupportersModel.php
 */

class SupportersModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    public function getAllSupporters($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['type']))    { $where .= " AND s.supporter_type=:type";    $params[':type']    = $filters['type']; }
            if (!empty($filters['tier']))    { $where .= " AND s.tier=:tier";              $params[':tier']    = $filters['tier']; }
            if (!empty($filters['session'])) { $where .= " AND s.cep_session=:session OR s.cep_session='both'"; $params[':session'] = $filters['session']; }
            if (!empty($filters['status']))  { $where .= " AND s.status=:status";          $params[':status']  = $filters['status']; }
            if (!empty($filters['search']))  {
                $where .= " AND (s.firstname LIKE :search OR s.lastname LIKE :search OR s.organization_name LIKE :search OR s.email LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare(
                "SELECT s.*,
                        COALESCE(SUM(c.amount),0)     AS total_contributed,
                        COUNT(c.id)                   AS contribution_count,
                        MAX(c.contribution_date)      AS last_contribution_date
                 FROM cep_supporters s
                 LEFT JOIN supporter_contributions c ON c.supporter_id = s.id
                 $where
                 GROUP BY s.id ORDER BY s.created_at DESC
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = $this->db->prepare("SELECT COUNT(*) FROM cep_supporters s $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function getSupporterById($id)
    {
        try {
            $stmt = $this->db->prepare("SELECT * FROM cep_supporters WHERE id=:id");
            $stmt->execute([':id' => $id]);
            $s = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($s) {
                $c = $this->db->prepare(
                    "SELECT c.*, CONCAT(u.firstname,' ',u.lastname) AS recorded_by_name
                     FROM supporter_contributions c LEFT JOIN users u ON u.id=c.recorded_by
                     WHERE c.supporter_id=:id ORDER BY c.contribution_date DESC"
                );
                $c->execute([':id' => $id]);
                $s['contributions'] = $c->fetchAll(PDO::FETCH_ASSOC);
                $s['total_contributed'] = array_sum(array_column($s['contributions'], 'amount'));
            }
            return $s;
        } catch (Exception $e) { return null; }
    }

    public function createSupporter($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO cep_supporters (supporter_type,firstname,lastname,organization_name,email,phone,address,
                                             cep_session,support_area,tier,is_alumni,graduation_year,notes,status)
                 VALUES (:type,:fn,:ln,:org,:email,:phone,:addr,:session,:area,:tier,:alumni,:grad,:notes,:status)"
            );
            $stmt->execute([
                ':type'    => $data['supporter_type'] ?? 'external',
                ':fn'      => $data['firstname'],
                ':ln'      => $data['lastname'],
                ':org'     => $data['organization_name'] ?? null,
                ':email'   => $data['email'] ?? null,
                ':phone'   => $data['phone'] ?? null,
                ':addr'    => $data['address'] ?? null,
                ':session' => $data['cep_session'] ?? 'both',
                ':area'    => $data['support_area'] ?? 'general',
                ':tier'    => $data['tier'] ?? 'bronze',
                ':alumni'  => $data['is_alumni'] ?? 0,
                ':grad'    => $data['graduation_year'] ?? null,
                ':notes'   => $data['notes'] ?? null,
                ':status'  => $data['status'] ?? 'active',
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateSupporter($id, $data)
    {
        try {
            $fields = []; $params = [':id' => $id];
            $allowed = ['supporter_type','firstname','lastname','organization_name','email','phone','address',
                        'cep_session','support_area','tier','is_alumni','graduation_year','notes','status'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) { $fields[] = "$f=:$f"; $params[":$f"] = $data[$f]; }
            }
            if (!$fields) return ['success' => false, 'message' => 'Nothing to update'];
            $stmt = $this->db->prepare("UPDATE cep_supporters SET " . implode(',', $fields) . " WHERE id=:id");
            $stmt->execute($params);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function deleteSupporter($id)
    {
        try {
            $this->db->prepare("DELETE FROM supporter_contributions WHERE supporter_id=:id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM cep_supporters WHERE id=:id")->execute([':id' => $id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function addContribution($supporterId, $data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO supporter_contributions (supporter_id,cep_session,contribution_type,amount,description,contribution_date,recorded_by)
                 VALUES (:sid,:session,:type,:amount,:desc,:date,:by)"
            );
            $stmt->execute([
                ':sid'     => $supporterId,
                ':session' => $data['cep_session'] ?? 'both',
                ':type'    => $data['contribution_type'] ?? 'financial',
                ':amount'  => $data['amount'] ?? null,
                ':desc'    => $data['description'] ?? null,
                ':date'    => $data['contribution_date'],
                ':by'      => $data['recorded_by'] ?? null,
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function getStats()
    {
        try {
            $stmt = $this->db->query(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN supporter_type='alumni' THEN 1 ELSE 0 END) AS alumni,
                    SUM(CASE WHEN supporter_type='external' THEN 1 ELSE 0 END) AS external,
                    SUM(CASE WHEN supporter_type='choir' THEN 1 ELSE 0 END) AS choir,
                    SUM(CASE WHEN supporter_type='organization' THEN 1 ELSE 0 END) AS organization,
                    SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) AS active_count
                 FROM cep_supporters"
            );
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            $contrib = $this->db->query(
                "SELECT COALESCE(SUM(amount),0) AS total_contributions FROM supporter_contributions WHERE contribution_type='financial'"
            )->fetchColumn();
            $stats['total_contributions'] = (float)$contrib;
            return $stats;
        } catch (Exception $e) { return []; }
    }
}