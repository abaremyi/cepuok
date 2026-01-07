<?php
/**
 * Dashboard API Endpoint
 * File: modules/Dashboard/api/dashboardApi.php
 * Handles dashboard-related API requests
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Calculate the root path - go up 4 levels from this file's location
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
require_once $root_path . "/config/database.php";
require_once $root_path . "/modules/Authentication/models/UserModel.php";
require_once $root_path . "/modules/Authentication/models/RoleModel.php";

// Get action from query parameter
$action = $_GET['action'] ?? '';

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $pdo = Database::getConnection();

    switch ($action) {
        case 'get_user_details':
            $userId = $_GET['id'] ?? 0;
            
            if (!$userId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'User ID is required'
                ]);
                exit;
            }
            
            try {
                $userModel = new UserModel($pdo);
                $roleModel = new RoleModel($pdo);
                
                $user = $userModel->getUserById($userId);
                
                if (!$user) {
                    echo json_encode([
                        'success' => false,
                        'message' => 'User not found'
                    ]);
                    exit;
                }
                
                // Get user's role name and super admin status
                $role = $roleModel->getRoleById($user['role_id']);
                $user['role_name'] = $role ? $role['name'] : 'Unknown';
                $user['is_super_admin'] = $role ? (bool)$role['is_super_admin'] : false;
                
                // Get user permissions (convert string to array)
                $permissionsString = $user['permissions'] ?? ''; // Get from user array
                $user['permissions'] = !empty($permissionsString) ? explode(',', $permissionsString) : [];
                
                // Format dates
                $last_login = $user['last_login'] ? date('M d, Y H:i', strtotime($user['last_login'])) : 'Never';
                $created_at = date('M d, Y', strtotime($user['created_at']));
                $updated_at = date('M d, Y H:i', strtotime($user['updated_at']));
                
                // Get creator name if available
                $creator_name = 'System';
                if ($user['created_by']) {
                    $creator = $userModel->getUserById($user['created_by']);
                    if ($creator) {
                        $creator_name = htmlspecialchars($creator['firstname'] . ' ' . $creator['lastname']);
                    }
                }
                
                // HTML response
                $html = '
                <div class="user-details">
                    <div class="text-center mb-4">
                        <img src="' . img_url($user['photo']) . '" 
                             class="user-avatar-lg mb-3"
                             alt="' . htmlspecialchars($user['firstname']) . '"
                             onerror="this.src=\'https://ui-avatars.com/api/?name=' . urlencode($user['firstname'] . '+' . $user['lastname']) . '&background=667eea&color=fff\'">
                        <h4>' . htmlspecialchars($user['firstname'] . ' ' . $user['lastname']) . '</h4>
                        <p class="text-muted">@' . htmlspecialchars($user['username']) . '</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-user-circle me-2 text-primary"></i>Basic Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="140" class="text-muted">First Name:</td>
                                            <td><strong>' . htmlspecialchars($user['firstname']) . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Last Name:</td>
                                            <td><strong>' . htmlspecialchars($user['lastname']) . '</strong></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Email:</td>
                                            <td><a href="mailto:' . htmlspecialchars($user['email']) . '">' . htmlspecialchars($user['email']) . '</a></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Phone:</td>
                                            <td><a href="tel:' . htmlspecialchars($user['phone']) . '">' . htmlspecialchars($user['phone']) . '</a></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card mb-3">
                                <div class="card-body">
                                    <h6 class="card-title"><i class="fas fa-shield-alt me-2 text-info"></i>Account Information</h6>
                                    <table class="table table-sm table-borderless">
                                        <tr>
                                            <td width="140" class="text-muted">Role:</td>
                                            <td>
                                                <span class="badge-role">' . htmlspecialchars($user['role_name']) . '</span>
                                                ' . ($user['is_super_admin'] ? '<span class="badge bg-warning ms-1">Super Admin</span>' : '') . '
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Status:</td>
                                            <td>
                                                <span class="badge status-badge status-' . $user['status'] . '">
                                                    ' . ucfirst($user['status']) . '
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Last Login:</td>
                                            <td>' . $last_login . '</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Created:</td>
                                            <td>' . $created_at . '</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-history me-2 text-success"></i>Account History</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="140" class="text-muted">Created By:</td>
                                    <td>' . $creator_name . '</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Created At:</td>
                                    <td>' . date('M d, Y H:i', strtotime($user['created_at'])) . '</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Last Updated:</td>
                                    <td>' . $updated_at . '</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">User ID:</td>
                                    <td><code>' . $user['id'] . '</code></td>
                                </tr>
                            </table>
                        </div>
                    </div>';
                
                if (!empty($user['permissions'])) {
                    $html .= '
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-key me-2 text-warning"></i>Assigned Permissions</h6>
                            <div class="permissions-list mt-3">';
                    
                    // Group permissions by module
                    $groupedPerms = [];
                    foreach ($user['permissions'] as $permission) {
                        $parts = explode('.', $permission);
                        if (count($parts) == 2) {
                            $module = $parts[0];
                            if (!isset($groupedPerms[$module])) {
                                $groupedPerms[$module] = [];
                            }
                            $groupedPerms[$module][] = $permission;
                        }
                    }
                    
                    foreach ($groupedPerms as $module => $perms) {
                        $html .= '<div class="mb-3">';
                        $html .= '<h6 class="text-uppercase text-muted small mb-2">' . ucfirst(str_replace('_', ' ', $module)) . '</h6>';
                        foreach ($perms as $perm) {
                            $html .= '<span class="badge bg-light text-dark me-1 mb-1">' . htmlspecialchars($perm) . '</span>';
                        }
                        $html .= '</div>';
                    }
                    
                    $html .= '
                            </div>
                            <div class="mt-3 text-muted small">
                                <i class="fas fa-info-circle me-1"></i> Total: ' . count($user['permissions']) . ' permission(s)
                            </div>
                        </div>
                    </div>';
                } else {
                    $html .= '
                    <div class="card">
                        <div class="card-body text-center py-4">
                            <i class="fas fa-key fa-2x text-muted mb-3"></i>
                            <h6 class="text-muted">No Specific Permissions</h6>
                            <p class="text-muted small mb-0">This user uses default permissions from their role.</p>
                        </div>
                    </div>';
                }
                
                $html .= '</div>';
                
                echo json_encode([
                    'success' => true,
                    'html' => $html
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error loading user details: ' . $e->getMessage()
                ]);
            }
            break;

        case 'get_dashboard_stats':
            // This endpoint returns dashboard statistics
            try {
                // Count total users
                $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
                $totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
                
                // Count active users
                $stmt = $pdo->query("SELECT COUNT(*) as active_users FROM users WHERE status = 'active'");
                $activeUsers = $stmt->fetch(PDO::FETCH_ASSOC)['active_users'];
                
                // Count pending users
                $stmt = $pdo->query("SELECT COUNT(*) as pending_users FROM users WHERE status = 'pending'");
                $pendingUsers = $stmt->fetch(PDO::FETCH_ASSOC)['pending_users'];
                
                // Count total roles
                $stmt = $pdo->query("SELECT COUNT(*) as total_roles FROM roles");
                $totalRoles = $stmt->fetch(PDO::FETCH_ASSOC)['total_roles'];
                
                echo json_encode([
                    'success' => true,
                    'data' => [
                        'total_users' => $totalUsers,
                        'active_users' => $activeUsers,
                        'pending_users' => $pendingUsers,
                        'total_roles' => $totalRoles
                    ]
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error loading dashboard stats: ' . $e->getMessage()
                ]);
            }
            break;

        case 'get_recent_users':
            // Get recent users for dashboard
            try {
                $limit = min(intval($_GET['limit'] ?? 10), 50);
                
                $query = "SELECT u.id, u.firstname, u.lastname, u.email, u.photo, u.status, u.last_login, 
                                 r.name as role_name, r.is_super_admin
                          FROM users u
                          JOIN roles r ON u.role_id = r.id
                          ORDER BY u.created_at DESC
                          LIMIT ?";
                
                $stmt = $pdo->prepare($query);
                $stmt->execute([$limit]);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'data' => $users
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error loading recent users: ' . $e->getMessage()
                ]);
            }
            break;

        case 'check_email_availability':
            // Check if email is available
            $email = $_GET['email'] ?? '';
            
            if (!$email) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Email is required'
                ]);
                exit;
            }
            
            try {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM users WHERE email = ?");
                $stmt->execute([$email]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'success' => true,
                    'available' => $result['count'] == 0
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error checking email: ' . $e->getMessage()
                ]);
            }
            break;

        case 'get_role_permissions':
            // Get permissions for a specific role
            $roleId = $_GET['role_id'] ?? 0;
            
            if (!$roleId) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Role ID is required'
                ]);
                exit;
            }
            
            try {
                $roleModel = new RoleModel($pdo);
                $permissions = $roleModel->getRolePermissions($roleId);
                
                echo json_encode([
                    'success' => true,
                    'permissions' => $permissions
                ]);
                
            } catch (Exception $e) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Error loading role permissions: ' . $e->getMessage()
                ]);
            }
            break;

        default:
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action',
                'available_actions' => [
                    'get_user_details',
                    'get_dashboard_stats',
                    'get_recent_users',
                    'check_email_availability',
                    'get_role_permissions'
                ]
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Dashboard API Exception: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred.',
        'error' => $e->getMessage()
    ]);
}
?>