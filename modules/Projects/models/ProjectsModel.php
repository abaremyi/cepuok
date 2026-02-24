<?php
/**
 * Projects Model
 * File: modules/Projects/models/ProjectsModel.php
 */

class ProjectsModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    public function getAllProjects($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['session']))  { $where .= " AND p.cep_session=:session OR p.cep_session='both'"; $params[':session'] = $filters['session']; }
            if (!empty($filters['status']))   { $where .= " AND p.status=:status";    $params[':status']   = $filters['status']; }
            if (!empty($filters['category'])) { $where .= " AND p.category=:cat";     $params[':cat']      = $filters['category']; }
            if (!empty($filters['search']))   { $where .= " AND p.title LIKE :s";     $params[':s']        = '%'.$filters['search'].'%'; }

            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare(
                "SELECT p.*,
                        CONCAT(u.firstname,' ',u.lastname)  AS lead_name,
                        u.photo                             AS lead_photo,
                        COUNT(t.id)                         AS task_total,
                        SUM(CASE WHEN t.status='done' THEN 1 ELSE 0 END) AS tasks_done
                 FROM projects p
                 LEFT JOIN users u ON u.id = p.lead_user_id
                 LEFT JOIN project_tasks t ON t.project_id = p.id
                 $where
                 GROUP BY p.id ORDER BY p.created_at DESC
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = $this->db->prepare("SELECT COUNT(*) FROM projects p $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function getProjectById($id)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT p.*, CONCAT(u.firstname,' ',u.lastname) AS lead_name
                 FROM projects p LEFT JOIN users u ON u.id=p.lead_user_id WHERE p.id=:id"
            );
            $stmt->execute([':id' => $id]);
            $p = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($p) {
                $ts = $this->db->prepare(
                    "SELECT t.*, CONCAT(u.firstname,' ',u.lastname) AS assignee_name
                     FROM project_tasks t LEFT JOIN users u ON u.id=t.assigned_to WHERE t.project_id=:id ORDER BY t.due_date"
                );
                $ts->execute([':id' => $id]);
                $p['tasks'] = $ts->fetchAll(PDO::FETCH_ASSOC);

                $us = $this->db->prepare(
                    "SELECT pu.*, CONCAT(u.firstname,' ',u.lastname) AS posted_by_name
                     FROM project_updates pu LEFT JOIN users u ON u.id=pu.posted_by WHERE pu.project_id=:id ORDER BY pu.created_at DESC"
                );
                $us->execute([':id' => $id]);
                $p['updates'] = $us->fetchAll(PDO::FETCH_ASSOC);
            }
            return $p;
        } catch (Exception $e) { return null; }
    }

    public function createProject($data)
    {
        try {
            $year = date('Y');
            $cntStmt = $this->db->prepare("SELECT COUNT(*)+1 FROM projects WHERE YEAR(created_at)=:yr");
            $cntStmt->execute([':yr' => $year]);
            $seq  = str_pad((int)$cntStmt->fetchColumn(), 3, '0', STR_PAD_LEFT);
            $code = "PROJ-$year-$seq";

            $stmt = $this->db->prepare(
                "INSERT INTO projects (cep_session,project_code,title,description,category,start_date,end_date,budget,status,lead_user_id,created_by)
                 VALUES (:session,:code,:title,:desc,:cat,:start,:end,:budget,:status,:lead,:by)"
            );
            $stmt->execute([
                ':session' => $data['cep_session'] ?? 'both',
                ':code'    => $code,
                ':title'   => $data['title'],
                ':desc'    => $data['description'] ?? null,
                ':cat'     => $data['category'] ?? 'other',
                ':start'   => $data['start_date'] ?? null,
                ':end'     => $data['end_date'] ?? null,
                ':budget'  => $data['budget'] ?? 0,
                ':status'  => $data['status'] ?? 'planning',
                ':lead'    => $data['lead_user_id'] ?? null,
                ':by'      => $data['created_by'] ?? null,
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId(), 'code' => $code];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function updateProject($id, $data)
    {
        try {
            $fields = []; $params = [':id' => $id];
            $allowed = ['cep_session','title','description','category','start_date','end_date','budget','spent','status','progress','lead_user_id'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) { $fields[] = "$f=:$f"; $params[":$f"] = $data[$f]; }
            }
            if (!$fields) return ['success' => false, 'message' => 'Nothing to update'];
            $stmt = $this->db->prepare("UPDATE projects SET " . implode(',', $fields) . " WHERE id=:id");
            $stmt->execute($params);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function deleteProject($id)
    {
        try {
            $this->db->prepare("DELETE FROM project_updates WHERE project_id=:id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM project_tasks WHERE project_id=:id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM projects WHERE id=:id")->execute([':id' => $id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function createTask($projectId, $data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO project_tasks (project_id,title,description,assigned_to,due_date,priority,status)
                 VALUES (:pid,:title,:desc,:assign,:due,:pri,:status)"
            );
            $stmt->execute([
                ':pid'    => $projectId,
                ':title'  => $data['title'],
                ':desc'   => $data['description'] ?? null,
                ':assign' => $data['assigned_to'] ?? null,
                ':due'    => $data['due_date'] ?? null,
                ':pri'    => $data['priority'] ?? 'medium',
                ':status' => $data['status'] ?? 'todo',
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function updateTaskStatus($taskId, $status)
    {
        try {
            $stmt = $this->db->prepare("UPDATE project_tasks SET status=:status WHERE id=:id");
            $stmt->execute([':status' => $status, ':id' => $taskId]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function addUpdate($projectId, $text, $progress, $userId)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO project_updates (project_id,update_text,progress,posted_by) VALUES (:pid,:text,:prog,:by)"
            );
            $stmt->execute([':pid' => $projectId, ':text' => $text, ':prog' => $progress, ':by' => $userId]);
            if ($progress !== null) {
                $this->db->prepare("UPDATE projects SET progress=:prog WHERE id=:id")->execute([':prog' => $progress, ':id' => $projectId]);
            }
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function getStats($session = null)
    {
        try {
            $where = $session ? "WHERE cep_session=:session OR cep_session='both'" : "";
            $params = $session ? [':session' => $session] : [];
            $stmt = $this->db->prepare(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='active'    THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status='planning'  THEN 1 ELSE 0 END) AS planning,
                    SUM(CASE WHEN status='completed' THEN 1 ELSE 0 END) AS completed,
                    SUM(CASE WHEN status='on_hold'   THEN 1 ELSE 0 END) AS on_hold,
                    COALESCE(SUM(budget),0) AS total_budget,
                    COALESCE(SUM(spent),0)  AS total_spent
                 FROM projects $where"
            );
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }
}