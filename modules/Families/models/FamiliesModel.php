<?php
/**
 * Families Model
 * File: modules/Families/models/FamiliesModel.php
 */

class FamiliesModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    public function getAllFamilies($session = null)
    {
        try {
            $where  = $session ? "WHERE f.cep_session = :session OR f.cep_session = 'both'" : "";
            $params = $session ? [':session' => $session] : [];

            $stmt = $this->db->prepare(
                "SELECT f.*,
                        COUNT(m.id)                                          AS member_count,
                        SUM(CASE WHEN m.gender='Male'   THEN 1 ELSE 0 END)  AS male_count,
                        SUM(CASE WHEN m.gender='Female' THEN 1 ELSE 0 END)  AS female_count,
                        SUM(CASE WHEN m.status='active' THEN 1 ELSE 0 END)  AS active_count,
                        CONCAT(u1.firstname,' ',u1.lastname)                 AS parent_name,
                        CONCAT(u2.firstname,' ',u2.lastname)                 AS co_parent_name,
                        u1.photo AS parent_photo, u2.photo AS co_parent_photo
                 FROM cep_families f
                 LEFT JOIN members m      ON m.family_id = f.id AND m.status != 'inactive'
                 LEFT JOIN users u1       ON u1.id = f.parent_user_id
                 LEFT JOIN users u2       ON u2.id = f.co_parent_user_id
                 $where
                 GROUP BY f.id ORDER BY f.cep_session, f.family_name"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("FamiliesModel::getAllFamilies - " . $e->getMessage());
            return [];
        }
    }

    public function getFamilyById($id)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT f.*,
                        CONCAT(u1.firstname,' ',u1.lastname) AS parent_name,
                        CONCAT(u2.firstname,' ',u2.lastname) AS co_parent_name
                 FROM cep_families f
                 LEFT JOIN users u1 ON u1.id = f.parent_user_id
                 LEFT JOIN users u2 ON u2.id = f.co_parent_user_id
                 WHERE f.id = :id"
            );
            $stmt->execute([':id' => $id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return null; }
    }

    public function getFamilyMembers($familyId, $filters = [])
    {
        try {
            $where = "WHERE m.family_id = :fid";
            $params = [':fid' => $familyId];
            if (!empty($filters['status'])) { $where .= " AND m.status=:status"; $params[':status'] = $filters['status']; }
            if (!empty($filters['search'])) { $where .= " AND (m.firstname LIKE :s OR m.lastname LIKE :s)"; $params[':s'] = '%'.$filters['search'].'%'; }

            $stmt = $this->db->prepare(
                "SELECT m.id, m.firstname, m.lastname, m.email, m.phone, m.gender, m.profile_photo,
                        m.status, m.cep_session, m.faculty, m.program, m.year_joined_cep
                 FROM members m $where ORDER BY m.firstname"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function createFamily($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO cep_families (family_name,family_code,icon_class,motto,cep_session,description,
                                           parent_user_id,co_parent_user_id,color_code,status)
                 VALUES (:name,:code,:icon,:motto,:session,:desc,:parent,:coparent,:color,:status)"
            );
            $stmt->execute([
                ':name'     => $data['family_name'],
                ':code'     => $data['family_code'] ?? null,
                ':icon'     => $data['icon_class'] ?? 'bi bi-people',
                ':motto'    => $data['motto'] ?? null,
                ':session'  => $data['cep_session'],
                ':desc'     => $data['description'] ?? null,
                ':parent'   => $data['parent_user_id'] ?? null,
                ':coparent' => $data['co_parent_user_id'] ?? null,
                ':color'    => $data['color_code'] ?? '#007bff',
                ':status'   => $data['status'] ?? 'active',
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function updateFamily($id, $data)
    {
        try {
            $fields = [];
            $params = [':id' => $id];
            $allowed = ['family_name','family_code','icon_class','motto','cep_session','description','parent_user_id','co_parent_user_id','color_code','status'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) {
                    $fields[] = "$f = :$f";
                    $params[":$f"] = $data[$f];
                }
            }
            if (!$fields) return ['success' => false, 'message' => 'Nothing to update'];
            $stmt = $this->db->prepare("UPDATE cep_families SET " . implode(',', $fields) . " WHERE id=:id");
            $stmt->execute($params);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteFamily($id)
    {
        try {
            // Unassign members first
            $this->db->prepare("UPDATE members SET family_id=NULL WHERE family_id=:id")->execute([':id' => $id]);
            $stmt = $this->db->prepare("DELETE FROM cep_families WHERE id=:id");
            $stmt->execute([':id' => $id]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function assignMembers($familyId, array $memberIds)
    {
        try {
            if (empty($memberIds)) return ['success' => false, 'message' => 'No members provided'];
            $placeholders = implode(',', array_fill(0, count($memberIds), '?'));
            $stmt = $this->db->prepare("UPDATE members SET family_id=? WHERE id IN ($placeholders)");
            $stmt->execute(array_merge([$familyId], $memberIds));
            return ['success' => true, 'assigned' => $stmt->rowCount()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function removeMemberFromFamily($memberId)
    {
        try {
            $stmt = $this->db->prepare("UPDATE members SET family_id=NULL WHERE id=:id");
            $stmt->execute([':id' => $memberId]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function getUnassignedMembers($session = null, $search = '')
    {
        try {
            $where = "WHERE m.family_id IS NULL";
            $params = [];
            if ($session) { $where .= " AND m.cep_session=:session"; $params[':session'] = $session; }
            if ($search)  { $where .= " AND (m.firstname LIKE :s OR m.lastname LIKE :s OR m.email LIKE :s)"; $params[':s'] = "%$search%"; }
            $stmt = $this->db->prepare(
                "SELECT m.id, m.firstname, m.lastname, m.email, m.gender, m.cep_session, m.profile_photo, m.faculty
                 FROM members m $where AND m.status='active' ORDER BY m.firstname LIMIT 50"
            );
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getFamilyStats()
    {
        try {
            $stmt = $this->db->query(
                "SELECT cep_session,
                        COUNT(*) AS total_families,
                        (SELECT COUNT(*) FROM members WHERE family_id IS NOT NULL AND status='active') AS assigned_members,
                        (SELECT COUNT(*) FROM members WHERE family_id IS NULL AND status='active') AS unassigned_members
                 FROM cep_families WHERE status='active' GROUP BY cep_session"
            );
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }
}