<?php
/**
 * Credential Wallet Model
 * File: modules/CredentialWallet/models/CredentialWalletModel.php
 *
 * All passwords are stored AES-256-CBC encrypted.
 * Encryption key = first 32 bytes of SHA-256(JWT_SECRET_KEY).
 */

class CredentialWalletModel
{
    private $db;
    private $encKey; // 32-byte binary key

    public function __construct($db = null)
    {
        $this->db     = $db ?: Database::getInstance();
        // Derive a 32-byte key from the JWT secret (never stored, derived at runtime)
        $this->encKey = substr(hash('sha256', JWT_SECRET_KEY, true), 0, 32);
    }

    // ─────────────────────────────────────────────────────────────
    // ENCRYPTION / DECRYPTION
    // ─────────────────────────────────────────────────────────────

    /** Encrypt plaintext → "base64(iv):base64(cipher)" */
    public function encrypt(string $plaintext): string
    {
        $iv     = random_bytes(16); // 128-bit IV
        $cipher = openssl_encrypt($plaintext, 'AES-256-CBC', $this->encKey, OPENSSL_RAW_DATA, $iv);
        return base64_encode($iv) . ':' . base64_encode($cipher);
    }

    /** Decrypt stored string back to plaintext (returns '' on failure) */
    public function decrypt(string $stored): string
    {
        if (empty($stored) || strpos($stored, ':') === false) return '';
        [$ivB64, $cipherB64] = explode(':', $stored, 2);
        $iv     = base64_decode($ivB64);
        $cipher = base64_decode($cipherB64);
        $plain  = openssl_decrypt($cipher, 'AES-256-CBC', $this->encKey, OPENSSL_RAW_DATA, $iv);
        return $plain !== false ? $plain : '';
    }

    // ─────────────────────────────────────────────────────────────
    // CATEGORIES
    // ─────────────────────────────────────────────────────────────

    public static function categories(): array
    {
        return [
            'social_media' => ['label' => 'Social Media',  'icon' => 'bi-share',           'color' => 'primary'],
            'email'        => ['label' => 'Email',          'icon' => 'bi-envelope',         'color' => 'info'],
            'api_key'      => ['label' => 'API Key',        'icon' => 'bi-code-slash',       'color' => 'warning'],
            'hosting'      => ['label' => 'Hosting',        'icon' => 'bi-server',           'color' => 'success'],
            'domain'       => ['label' => 'Domain',         'icon' => 'bi-globe',            'color' => 'secondary'],
            'analytics'    => ['label' => 'Analytics',      'icon' => 'bi-bar-chart',        'color' => 'danger'],
            'payment'      => ['label' => 'Payment',        'icon' => 'bi-credit-card',      'color' => 'success'],
            'other'        => ['label' => 'Other',          'icon' => 'bi-key',              'color' => 'dark'],
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // READ
    // ─────────────────────────────────────────────────────────────

    /** Return all credentials (passwords NOT decrypted — call getById for that) */
    public function getAll(array $filters = []): array
    {
        try {
            $where  = ['1=1'];
            $params = [];

            if (!empty($filters['category'])) {
                $where[] = 'w.category = :category';
                $params[':category'] = $filters['category'];
            }
            if (!empty($filters['search'])) {
                $where[] = '(w.platform LIKE :s OR w.account_label LIKE :s OR w.username LIKE :s OR w.creator_name LIKE :s)';
                $params[':s'] = '%' . $filters['search'] . '%';
            }
            if (isset($filters['is_active'])) {
                $where[] = 'w.is_active = :ia';
                $params[':ia'] = (int)$filters['is_active'];
            }

            $sql = "SELECT
                        w.id, w.category, w.platform, w.account_label, w.username,
                        w.account_url, w.verification_phone, w.verification_email,
                        w.creator_name, w.creator_phone, w.creator_email,
                        w.purpose, w.notes, w.expiry_date, w.is_active,
                        w.added_by, w.updated_by, w.created_at, w.updated_at,
                        CONCAT(ua.firstname,' ',ua.lastname) AS added_by_name,
                        CONCAT(ub.firstname,' ',ub.lastname) AS updated_by_name
                    FROM credential_wallet w
                    LEFT JOIN users ua ON ua.id = w.added_by
                    LEFT JOIN users ub ON ub.id = w.updated_by
                    WHERE " . implode(' AND ', $where) . "
                    ORDER BY w.category, w.platform, w.account_label";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("CredentialWalletModel::getAll - " . $e->getMessage());
            return [];
        }
    }

    /** Return one credential WITH decrypted password */
    public function getById(int $id): ?array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT w.*,
                        CONCAT(ua.firstname,' ',ua.lastname) AS added_by_name,
                        CONCAT(ub.firstname,' ',ub.lastname) AS updated_by_name
                 FROM credential_wallet w
                 LEFT JOIN users ua ON ua.id = w.added_by
                 LEFT JOIN users ub ON ub.id = w.updated_by
                 WHERE w.id = :id LIMIT 1"
            );
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return null;
            // Decrypt password for the response
            $row['password'] = $this->decrypt($row['password_encrypted'] ?? '');
            unset($row['password_encrypted']);
            return $row;
        } catch (Exception $e) {
            error_log("CredentialWalletModel::getById - " . $e->getMessage());
            return null;
        }
    }

    /** Summary stats grouped by category */
    public function getStats(): array
    {
        try {
            $stmt = $this->db->query(
                "SELECT category,
                        COUNT(*) AS total,
                        SUM(is_active) AS active,
                        SUM(CASE WHEN expiry_date IS NOT NULL AND expiry_date <= DATE_ADD(NOW(), INTERVAL 30 DAY) AND expiry_date >= CURDATE() THEN 1 ELSE 0 END) AS expiring_soon,
                        SUM(CASE WHEN expiry_date IS NOT NULL AND expiry_date < CURDATE() THEN 1 ELSE 0 END) AS expired
                 FROM credential_wallet
                 GROUP BY category"
            );
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Index by category
            $stats = [];
            foreach ($rows as $r) $stats[$r['category']] = $r;

            // Overall totals
            $totStmt = $this->db->query("SELECT COUNT(*) AS total, SUM(is_active) AS active FROM credential_wallet");
            $totals  = $totStmt->fetch(PDO::FETCH_ASSOC);

            return ['by_category' => $stats, 'totals' => $totals];
        } catch (Exception $e) {
            return ['by_category' => [], 'totals' => ['total'=>0,'active'=>0]];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // WRITE
    // ─────────────────────────────────────────────────────────────

    public function create(array $data, int $addedBy): array
    {
        try {
            $required = ['category', 'platform', 'account_label'];
            foreach ($required as $f) {
                if (empty($data[$f])) return ['success'=>false,'message'=>"Field '$f' is required"];
            }

            $pwEnc = !empty($data['password']) ? $this->encrypt($data['password']) : null;

            $stmt = $this->db->prepare(
                "INSERT INTO credential_wallet
                    (category, platform, account_label, username, password_encrypted,
                     account_url, verification_phone, verification_email,
                     creator_name, creator_phone, creator_email,
                     purpose, notes, expiry_date, is_active, added_by)
                 VALUES
                    (:category, :platform, :account_label, :username, :pw,
                     :account_url, :verification_phone, :verification_email,
                     :creator_name, :creator_phone, :creator_email,
                     :purpose, :notes, :expiry_date, 1, :added_by)"
            );
            $stmt->execute([
                ':category'           => $data['category'],
                ':platform'           => trim($data['platform']),
                ':account_label'      => trim($data['account_label']),
                ':username'           => !empty($data['username'])           ? trim($data['username'])           : null,
                ':pw'                 => $pwEnc,
                ':account_url'        => !empty($data['account_url'])        ? trim($data['account_url'])        : null,
                ':verification_phone' => !empty($data['verification_phone']) ? trim($data['verification_phone']) : null,
                ':verification_email' => !empty($data['verification_email']) ? trim($data['verification_email']) : null,
                ':creator_name'       => !empty($data['creator_name'])       ? trim($data['creator_name'])       : null,
                ':creator_phone'      => !empty($data['creator_phone'])      ? trim($data['creator_phone'])      : null,
                ':creator_email'      => !empty($data['creator_email'])      ? trim($data['creator_email'])      : null,
                ':purpose'            => !empty($data['purpose'])            ? trim($data['purpose'])            : null,
                ':notes'              => !empty($data['notes'])              ? trim($data['notes'])              : null,
                ':expiry_date'        => !empty($data['expiry_date'])        ? $data['expiry_date']              : null,
                ':added_by'           => $addedBy,
            ]);
            $id = (int)$this->db->lastInsertId();
            $this->audit($id, $addedBy, 'created');
            return ['success'=>true, 'id'=>$id];
        } catch (Exception $e) {
            error_log("CredentialWalletModel::create - " . $e->getMessage());
            return ['success'=>false,'message'=>'Database error: '.$e->getMessage()];
        }
    }

    public function update(int $id, array $data, int $updatedBy): array
    {
        try {
            // Fetch existing to check if password changed
            $existing = $this->db->prepare("SELECT password_encrypted FROM credential_wallet WHERE id=:id");
            $existing->execute([':id'=>$id]);
            $row = $existing->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['success'=>false,'message'=>'Credential not found'];

            // Only re-encrypt if a new password was provided
            if (isset($data['password']) && $data['password'] !== '') {
                $pwEnc = $this->encrypt($data['password']);
            } else {
                $pwEnc = $row['password_encrypted']; // keep existing
            }

            $stmt = $this->db->prepare(
                "UPDATE credential_wallet SET
                    category=:category, platform=:platform, account_label=:account_label,
                    username=:username, password_encrypted=:pw,
                    account_url=:account_url,
                    verification_phone=:verification_phone, verification_email=:verification_email,
                    creator_name=:creator_name, creator_phone=:creator_phone, creator_email=:creator_email,
                    purpose=:purpose, notes=:notes, expiry_date=:expiry_date,
                    updated_by=:updated_by
                 WHERE id=:id"
            );
            $stmt->execute([
                ':category'           => $data['category'],
                ':platform'           => trim($data['platform']),
                ':account_label'      => trim($data['account_label']),
                ':username'           => !empty($data['username'])           ? trim($data['username'])           : null,
                ':pw'                 => $pwEnc,
                ':account_url'        => !empty($data['account_url'])        ? trim($data['account_url'])        : null,
                ':verification_phone' => !empty($data['verification_phone']) ? trim($data['verification_phone']) : null,
                ':verification_email' => !empty($data['verification_email']) ? trim($data['verification_email']) : null,
                ':creator_name'       => !empty($data['creator_name'])       ? trim($data['creator_name'])       : null,
                ':creator_phone'      => !empty($data['creator_phone'])      ? trim($data['creator_phone'])      : null,
                ':creator_email'      => !empty($data['creator_email'])      ? trim($data['creator_email'])      : null,
                ':purpose'            => !empty($data['purpose'])            ? trim($data['purpose'])            : null,
                ':notes'              => !empty($data['notes'])              ? trim($data['notes'])              : null,
                ':expiry_date'        => !empty($data['expiry_date'])        ? $data['expiry_date']              : null,
                ':updated_by'         => $updatedBy,
                ':id'                 => $id,
            ]);
            $this->audit($id, $updatedBy, 'updated');
            return ['success'=>true];
        } catch (Exception $e) {
            error_log("CredentialWalletModel::update - " . $e->getMessage());
            return ['success'=>false,'message'=>'Database error: '.$e->getMessage()];
        }
    }

    public function toggleStatus(int $id, int $userId): array
    {
        try {
            $this->db->prepare("UPDATE credential_wallet SET is_active = !is_active WHERE id=:id")
                     ->execute([':id'=>$id]);
            $this->audit($id, $userId, 'toggled_status');
            return ['success'=>true];
        } catch (Exception $e) {
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }

    public function delete(int $id, int $userId): array
    {
        try {
            $this->audit($id, $userId, 'deleted');
            $this->db->prepare("DELETE FROM credential_wallet WHERE id=:id")->execute([':id'=>$id]);
            return ['success'=>true];
        } catch (Exception $e) {
            error_log("CredentialWalletModel::delete - " . $e->getMessage());
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // PASSWORD REVEAL (returns decrypted password and logs audit)
    // ─────────────────────────────────────────────────────────────

    public function revealPassword(int $id, int $userId): array
    {
        try {
            $stmt = $this->db->prepare("SELECT password_encrypted FROM credential_wallet WHERE id=:id");
            $stmt->execute([':id'=>$id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return ['success'=>false,'message'=>'Not found'];
            $this->audit($id, $userId, 'copied_password');
            return ['success'=>true, 'password'=>$this->decrypt($row['password_encrypted']??'')];
        } catch (Exception $e) {
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }

    // ─────────────────────────────────────────────────────────────
    // AUDIT LOG
    // ─────────────────────────────────────────────────────────────

    private function audit(int $credId, int $userId, string $action): void
    {
        try {
            $this->db->prepare(
                "INSERT INTO credential_wallet_audit (credential_id, user_id, action, ip_address, user_agent)
                 VALUES (:cid, :uid, :action, :ip, :ua)"
            )->execute([
                ':cid'    => $credId,
                ':uid'    => $userId,
                ':action' => $action,
                ':ip'     => $_SERVER['REMOTE_ADDR']     ?? null,
                ':ua'     => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
            ]);
        } catch (Exception $e) {
            error_log("CredentialWalletModel::audit - " . $e->getMessage());
        }
    }

    public function getAuditLog(int $credId, int $limit = 20): array
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT a.*, CONCAT(u.firstname,' ',u.lastname) AS user_name
                 FROM credential_wallet_audit a
                 LEFT JOIN users u ON u.id = a.user_id
                 WHERE a.credential_id = :cid
                 ORDER BY a.created_at DESC LIMIT :lim"
            );
            $stmt->bindValue(':cid', $credId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit,  PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function logView(int $id, int $userId): void
    {
        $this->audit($id, $userId, 'viewed');
    }
}