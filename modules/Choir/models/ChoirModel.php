<?php
/**
 * Choir Model
 * File: modules/Choir/models/ChoirModel.php
 */

class ChoirModel
{
    private $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: Database::getInstance();
    }

    // ── CHOIR MEMBERS ──────────────────────────────────────────

    public function getAllChoirMembers($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['session']))    { $where .= " AND cm.cep_session=:session OR cm.cep_session='both'"; $params[':session'] = $filters['session']; }
            if (!empty($filters['voice_part'])) { $where .= " AND cm.voice_part=:vp"; $params[':vp'] = $filters['voice_part']; }
            if (!empty($filters['status']))     { $where .= " AND cm.status=:status"; $params[':status'] = $filters['status']; }
            if (!empty($filters['search']))     {
                $where .= " AND (cm.full_name LIKE :search OR m.email LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare(
                "SELECT cm.*, m.profile_photo, m.email, m.phone, m.faculty, m.program
                 FROM choir_members cm
                 LEFT JOIN members m ON m.id = cm.member_id
                 $where ORDER BY cm.voice_part, cm.full_name
                 LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $count = $this->db->prepare("SELECT COUNT(*) FROM choir_members cm LEFT JOIN members m ON m.id=cm.member_id $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function getChoirStats()
    {
        try {
            $stmt = $this->db->query(
                "SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN voice_part='soprano' THEN 1 ELSE 0 END) AS soprano,
                    SUM(CASE WHEN voice_part='alto'    THEN 1 ELSE 0 END) AS alto,
                    SUM(CASE WHEN voice_part='tenor'   THEN 1 ELSE 0 END) AS tenor,
                    SUM(CASE WHEN voice_part='bass'    THEN 1 ELSE 0 END) AS bass
                 FROM choir_members WHERE status='active'"
            );
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function createChoirMember($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO choir_members (member_id,full_name,voice_part,instrument,cep_session,role,joined_date,status,notes)
                 VALUES (:mid,:name,:vp,:instr,:session,:role,:joined,:status,:notes)"
            );
            $stmt->execute([
                ':mid'     => $data['member_id'] ?? null,
                ':name'    => $data['full_name'],
                ':vp'      => $data['voice_part'] ?? 'soprano',
                ':instr'   => $data['instrument'] ?? null,
                ':session' => $data['cep_session'] ?? 'both',
                ':role'    => $data['role'] ?? 'member',
                ':joined'  => $data['joined_date'] ?? date('Y-m-d'),
                ':status'  => $data['status'] ?? 'active',
                ':notes'   => $data['notes'] ?? null,
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function updateChoirMember($id, $data)
    {
        try {
            $fields = []; $params = [':id' => $id];
            $allowed = ['full_name','voice_part','instrument','cep_session','role','joined_date','status','notes'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) { $fields[] = "$f=:$f"; $params[":$f"] = $data[$f]; }
            }
            if (!$fields) return ['success' => false, 'message' => 'Nothing to update'];
            $stmt = $this->db->prepare("UPDATE choir_members SET " . implode(',', $fields) . " WHERE id=:id");
            $stmt->execute($params);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function deleteChoirMember($id)
    {
        try {
            $this->db->prepare("DELETE FROM choir_attendance WHERE choir_member_id=:id")->execute([':id' => $id]);
            $this->db->prepare("DELETE FROM choir_members WHERE id=:id")->execute([':id' => $id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    // ── SONGS ──────────────────────────────────────────────────

    public function getAllSongs($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['category'])) { $where .= " AND category=:cat";    $params[':cat']    = $filters['category']; }
            if (!empty($filters['status']))   { $where .= " AND status=:status";   $params[':status'] = $filters['status']; }
            if (!empty($filters['language'])) { $where .= " AND language=:lang";   $params[':lang']   = $filters['language']; }
            if (!empty($filters['search']))   { $where .= " AND (title LIKE :s OR composer LIKE :s)"; $params[':s'] = '%'.$filters['search'].'%'; }
            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare("SELECT * FROM choir_songs $where ORDER BY title LIMIT :lim OFFSET :off");
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = $this->db->prepare("SELECT COUNT(*) FROM choir_songs $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function createSong($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO choir_songs (title,composer,arranger,language,category,key_signature,tempo,youtube_url,sheet_music_path,status,notes)
                 VALUES (:title,:comp,:arr,:lang,:cat,:key,:tempo,:yt,:sheet,:status,:notes)"
            );
            $stmt->execute([
                ':title'  => $data['title'],
                ':comp'   => $data['composer'] ?? null,
                ':arr'    => $data['arranger'] ?? null,
                ':lang'   => $data['language'] ?? 'Kinyarwanda',
                ':cat'    => $data['category'] ?? 'worship',
                ':key'    => $data['key_signature'] ?? null,
                ':tempo'  => $data['tempo'] ?? null,
                ':yt'     => $data['youtube_url'] ?? null,
                ':sheet'  => $data['sheet_music_path'] ?? null,
                ':status' => $data['status'] ?? 'active',
                ':notes'  => $data['notes'] ?? null,
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function updateSong($id, $data)
    {
        try {
            $fields = []; $params = [':id' => $id];
            $allowed = ['title','composer','arranger','language','category','key_signature','tempo','youtube_url','status','notes'];
            foreach ($allowed as $f) {
                if (array_key_exists($f, $data)) { $fields[] = "$f=:$f"; $params[":$f"] = $data[$f]; }
            }
            if (!$fields) return ['success' => false, 'message' => 'Nothing to update'];
            $stmt = $this->db->prepare("UPDATE choir_songs SET " . implode(',', $fields) . " WHERE id=:id");
            $stmt->execute($params);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function deleteSong($id)
    {
        try {
            $this->db->prepare("DELETE FROM choir_songs WHERE id=:id")->execute([':id' => $id]);
            return ['success' => true];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function getSongStats()
    {
        try {
            $stmt = $this->db->query(
                "SELECT COUNT(*) AS total,
                    SUM(CASE WHEN status='active' THEN 1 ELSE 0 END) AS active,
                    SUM(CASE WHEN status='learning' THEN 1 ELSE 0 END) AS learning,
                    SUM(CASE WHEN category='worship' THEN 1 ELSE 0 END) AS worship,
                    SUM(CASE WHEN category='praise' THEN 1 ELSE 0 END) AS praise
                 FROM choir_songs"
            );
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    // ── REHEARSALS & ATTENDANCE ─────────────────────────────────

    public function getAllRehearsals($filters = [], $page = 1, $perPage = 20)
    {
        try {
            $where = "WHERE 1=1"; $params = [];
            if (!empty($filters['session'])) { $where .= " AND r.cep_session=:session"; $params[':session'] = $filters['session']; }
            $offset = ($page - 1) * $perPage;
            $stmt = $this->db->prepare(
                "SELECT r.*,
                        CONCAT(u.firstname,' ',u.lastname) AS conductor_name,
                        (SELECT COUNT(*) FROM choir_attendance ca WHERE ca.rehearsal_id=r.id AND ca.status='present') AS present_count,
                        (SELECT COUNT(*) FROM choir_members WHERE status='active')  AS total_active
                 FROM choir_rehearsals r
                 LEFT JOIN users u ON u.id = r.conductor_id
                 $where ORDER BY r.rehearsal_date DESC LIMIT :lim OFFSET :off"
            );
            $stmt->bindValue(':lim', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':off', (int)$offset,  PDO::PARAM_INT);
            foreach ($params as $k => $v) $stmt->bindValue($k, $v);
            $stmt->execute();
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $count = $this->db->prepare("SELECT COUNT(*) FROM choir_rehearsals r $where");
            foreach ($params as $k => $v) $count->bindValue($k, $v);
            $count->execute();
            $total = (int)$count->fetchColumn();
            return ['data' => $rows, 'total' => $total, 'pages' => ceil($total / $perPage)];
        } catch (Exception $e) { return ['data' => [], 'total' => 0, 'pages' => 0]; }
    }

    public function createRehearsal($data)
    {
        try {
            $stmt = $this->db->prepare(
                "INSERT INTO choir_rehearsals (rehearsal_date,cep_session,location,start_time,end_time,conductor_id,songs_practiced,notes)
                 VALUES (:date,:session,:loc,:start,:end,:cond,:songs,:notes)"
            );
            $stmt->execute([
                ':date'    => $data['rehearsal_date'],
                ':session' => $data['cep_session'] ?? 'both',
                ':loc'     => $data['location'] ?? null,
                ':start'   => $data['start_time'] ?? null,
                ':end'     => $data['end_time'] ?? null,
                ':cond'    => $data['conductor_id'] ?? null,
                ':songs'   => $data['songs_practiced'] ?? null,
                ':notes'   => $data['notes'] ?? null,
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId()];
        } catch (Exception $e) { return ['success' => false, 'message' => $e->getMessage()]; }
    }

    public function saveAttendance($rehearsalId, array $attendance)
    {
        try {
            $this->db->beginTransaction();
            $this->db->prepare("DELETE FROM choir_attendance WHERE rehearsal_id=:id")->execute([':id' => $rehearsalId]);
            $stmt = $this->db->prepare(
                "INSERT INTO choir_attendance (rehearsal_id,choir_member_id,status,notes) VALUES (:rid,:mid,:status,:notes)"
            );
            foreach ($attendance as $row) {
                $stmt->execute([':rid' => $rehearsalId, ':mid' => $row['member_id'], ':status' => $row['status'] ?? 'present', ':notes' => $row['notes'] ?? null]);
            }
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function getRehearsalAttendance($rehearsalId)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT cm.id AS choir_member_id, cm.full_name, cm.voice_part,
                        COALESCE(ca.status,'absent') AS status, ca.notes
                 FROM choir_members cm
                 LEFT JOIN choir_attendance ca ON ca.choir_member_id=cm.id AND ca.rehearsal_id=:rid
                 WHERE cm.status='active' ORDER BY cm.voice_part, cm.full_name"
            );
            $stmt->execute([':rid' => $rehearsalId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) { return []; }
    }

    public function getMemberAttendanceRate($memberId)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT COUNT(*) AS total,
                        SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS present_count
                 FROM choir_attendance WHERE choir_member_id=:id"
            );
            $stmt->execute([':id' => $memberId]);
            $r = $stmt->fetch(PDO::FETCH_ASSOC);
            return $r['total'] > 0 ? round($r['present_count'] / $r['total'] * 100, 1) : 0;
        } catch (Exception $e) { return 0; }
    }
}