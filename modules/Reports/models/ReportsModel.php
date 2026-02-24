<?php
/**
 * Reports Model
 * File: modules/Reports/models/ReportsModel.php
 */

class ReportsModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    // ── MEMBER REPORTS ─────────────────────────────────────────

    public function getMemberSummary($session = null)
    {
        try {
            $where  = $session ? "WHERE cep_session=:session" : "";
            $params = $session ? [':session' => $session] : [];
            $stmt   = $this->db->prepare(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='active'    THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status='inactive'  THEN 1 ELSE 0 END) AS inactive,
                    SUM(CASE WHEN gender='Male'      THEN 1 ELSE 0 END) AS male,
                    SUM(CASE WHEN gender='Female'    THEN 1 ELSE 0 END) AS female,
                    SUM(CASE WHEN family_id IS NOT NULL THEN 1 ELSE 0 END) AS in_families
                 FROM members $where"
            );
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getMembersByFaculty($session = null)
    {
        try {
            $where  = $session ? "WHERE cep_session=:session AND status='active'" : "WHERE status='active'";
            $params = $session ? [':session' => $session] : [];
            $stmt   = $this->db->prepare(
                "SELECT faculty, COUNT(*) AS total FROM members $where GROUP BY faculty ORDER BY total DESC"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getMembersJoinedByYear($session = null)
    {
        try {
            $where  = $session ? "WHERE cep_session=:session" : "";
            $params = $session ? [':session' => $session] : [];
            $stmt   = $this->db->prepare(
                "SELECT year_joined_cep AS year, COUNT(*) AS total FROM members $where GROUP BY year_joined_cep ORDER BY year"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getMembersByChurch($session = null, $limit = 10)
    {
        try {
            $where  = $session ? "WHERE cep_session=:session AND status='active'" : "WHERE status='active'";
            $params = $session ? [':session' => $session] : [];
            $stmt   = $this->db->prepare(
                "SELECT church_name, COUNT(*) AS total FROM members $where
                 GROUP BY church_name ORDER BY total DESC LIMIT :lim"
            );
            $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getMembersByFamily($session = null)
    {
        try {
            $where  = $session ? "WHERE (f.cep_session=:session OR f.cep_session='both')" : "";
            $params = $session ? [':session' => $session] : [];
            $stmt   = $this->db->prepare(
                "SELECT f.family_name, f.color_code, COUNT(m.id) AS total,
                        SUM(CASE WHEN m.gender='Male' THEN 1 ELSE 0 END) AS male,
                        SUM(CASE WHEN m.gender='Female' THEN 1 ELSE 0 END) AS female
                 FROM cep_families f
                 LEFT JOIN members m ON m.family_id=f.id AND m.status='active'
                 $where
                 GROUP BY f.id ORDER BY total DESC"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getMembersList($filters = [], $page = 1, $perPage = 50)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['session'])) { $where .= " AND m.cep_session=:session"; $params[':session'] = $filters['session']; }
            if (!empty($filters['status']))  { $where .= " AND m.status=:status";       $params[':status']  = $filters['status']; }
            if (!empty($filters['faculty'])) { $where .= " AND m.faculty=:faculty";     $params[':faculty'] = $filters['faculty']; }
            if (!empty($filters['family_id'])) { $where .= " AND m.family_id=:fam";    $params[':fam']     = $filters['family_id']; }
            if (!empty($filters['gender']))  { $where .= " AND m.gender=:gender";       $params[':gender']  = $filters['gender']; }

            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare(
                "SELECT m.id, m.membership_number, m.firstname, m.lastname, m.email, m.phone, m.gender,
                        m.status, m.cep_session, m.faculty, m.program, m.church_name, m.year_joined_cep,
                        f.family_name, m.profile_photo, m.created_at
                 FROM members m
                 LEFT JOIN cep_families f ON f.id = m.family_id
                 $where ORDER BY m.firstname
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = $this->db->prepare("SELECT COUNT(*) FROM members m $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    // ── FINANCE REPORTS ─────────────────────────────────────────

    public function getFinanceSummary($session = null, $year = null)
    {
        try {
            $year   = $year ?: date('Y');
            $where  = "WHERE YEAR(revenue_date)=:year";
            $params = [':year' => $year];
            if ($session) { $where .= " AND cep_session=:session"; $params[':session'] = $session; }

            $rev = $this->db->prepare("SELECT COALESCE(SUM(amount),0) AS total FROM finance_revenue $where");
            $rev->execute($params);

            $diswhere  = $session ? "WHERE YEAR(d.disbursed_at)=:year AND fr.cep_session=:session" : "WHERE YEAR(d.disbursed_at)=:year";
            $dis = $this->db->prepare(
                "SELECT COALESCE(SUM(d.amount),0) AS total FROM disbursements d JOIN fund_requests fr ON fr.id=d.request_id $diswhere"
            );
            $dis->execute($params);

            return [
                'year'         => $year,
                'total_revenue'=> (float)$rev->fetchColumn(),
                'total_expenses'=> (float)$dis->fetchColumn(),
            ];
        } catch (Exception $e) { return []; }
    }

    public function getMonthlyFinanceReport($session = null, $year = null)
    {
        try {
            $year   = $year ?: date('Y');
            $where  = "WHERE YEAR(revenue_date)=:year";
            $params = [':year' => $year];
            if ($session) { $where .= " AND cep_session=:session"; $params[':session'] = $session; }
            $stmt = $this->db->prepare(
                "SELECT MONTH(revenue_date) AS month,
                        SUM(amount) AS revenue,
                        SUM(CASE WHEN revenue_type='offering' THEN amount ELSE 0 END) AS offerings,
                        SUM(CASE WHEN revenue_type='tithe'    THEN amount ELSE 0 END) AS tithes,
                        SUM(CASE WHEN revenue_type='donation' THEN amount ELSE 0 END) AS donations
                 FROM finance_revenue $where GROUP BY MONTH(revenue_date) ORDER BY month"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getFundRequestReport($session = null, $year = null)
    {
        try {
            $year   = $year ?: date('Y');
            $where  = "WHERE YEAR(created_at)=:year";
            $params = [':year' => $year];
            if ($session) { $where .= " AND cep_session=:session"; $params[':session'] = $session; }
            $stmt = $this->db->prepare(
                "SELECT stage, COUNT(*) AS count, COALESCE(SUM(amount_requested),0) AS requested, COALESCE(SUM(amount_approved),0) AS approved
                 FROM fund_requests $where GROUP BY stage"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function exportMembersCSV($filters = [])
    {
        try {
            $result = $this->getMembersList($filters, 1, 10000);
            return $result['data'];
        } catch (Exception $e) { return []; }
    }
}