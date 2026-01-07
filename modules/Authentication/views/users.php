<?php
require_once '../../../helpers/JWTHandler.php';
require_once '../../../config/database.php';

// Authentication check
if (!isset($_COOKIE['jwtToken'])) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

$jwtToken = $_COOKIE['jwtToken'];
$jwtHandler = new JWTHandler();
$decodedToken = $jwtHandler->validateToken($jwtToken);

if ($decodedToken === false) {
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// Process form submissions
$message = '';
$messageType = '';
$db = Database::getInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_user') {
        // Handle add user
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $roleId = $_POST['role_id'];
        $status = $_POST['status'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        // Handle photo upload
        $photoPath = null;
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = '../../../uploads/users/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . basename($_FILES['photo']['name']);
            $targetFilePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                $photoPath = $fileName;
            }
        }
        
        try {
            $stmt = $db->prepare("INSERT INTO users (firstname, lastname, email, username, phone, address, password, status, roleid, photo) 
                                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$firstName, $lastName, $email, $username, $phone, $address, $password, $status, $roleId, $photoPath]);
            
            $message = "User added successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error adding user: " . $e->getMessage();
            $messageType = "danger";
        }
    } elseif ($_POST['action'] === 'update_user') {
        // Handle update user
        $userId = $_POST['user_id'];
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $email = $_POST['email'];
        $username = $_POST['username'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $roleId = $_POST['role_id'];
        $status = $_POST['status'];
        
        // Check if password is being updated
        $passwordUpdate = !empty($_POST['password']) ? ", password = ?" : "";
        $passwordValue = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;
        
        // Handle photo upload
        $photoUpdate = "";
        $photoPath = null;
        
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
            $uploadDir = '../../../uploads/users/';
            $fileName = time() . '_' . basename($_FILES['photo']['name']);
            $targetFilePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $targetFilePath)) {
                // Get old photo path to delete it
                $stmt = $db->prepare("SELECT photo FROM users WHERE userid = ?");
                $stmt->execute([$userId]);
                $oldPhoto = $stmt->fetchColumn();
                
                if ($oldPhoto && file_exists($uploadDir . $oldPhoto)) {
                    unlink($uploadDir . $oldPhoto);
                }
                
                $photoPath = $fileName;
                $photoUpdate = ", photo = ?";
            }
        }
        
        try {
            if ($passwordUpdate && $photoUpdate) {
                $stmt = $db->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, username = ?, 
                                      phone = ?, address = ?, status = ?, roleid = ? $passwordUpdate $photoUpdate 
                                      WHERE userid = ?");
                $params = [$firstName, $lastName, $email, $username, $phone, $address, $status, $roleId];
                if ($passwordUpdate) $params[] = $passwordValue;
                if ($photoUpdate) $params[] = $photoPath;
                $params[] = $userId;
            } elseif ($passwordUpdate) {
                $stmt = $db->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, username = ?, 
                                      phone = ?, address = ?, status = ?, roleid = ? $passwordUpdate 
                                      WHERE userid = ?");
                $params = [$firstName, $lastName, $email, $username, $phone, $address, $status, $roleId, $passwordValue, $userId];
            } elseif ($photoUpdate) {
                $stmt = $db->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, username = ?, 
                                      phone = ?, address = ?, status = ?, roleid = ? $photoUpdate 
                                      WHERE userid = ?");
                $params = [$firstName, $lastName, $email, $username, $phone, $address, $status, $roleId, $photoPath, $userId];
            } else {
                $stmt = $db->prepare("UPDATE users SET firstname = ?, lastname = ?, email = ?, username = ?, 
                                      phone = ?, address = ?, status = ?, roleid = ? 
                                      WHERE userid = ?");
                $params = [$firstName, $lastName, $email, $username, $phone, $address, $status, $roleId, $userId];
            }
            
            $stmt->execute($params);
            
            $message = "User updated successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error updating user: " . $e->getMessage();
            $messageType = "danger";
        }
    } elseif ($_POST['action'] === 'delete_user') {
        // Handle delete user
        $userId = $_POST['user_id'];
        
        try {
            // First get photo path to delete the file
            $stmt = $db->prepare("SELECT photo FROM users WHERE userid = ?");
            $stmt->execute([$userId]);
            $photoPath = $stmt->fetchColumn();
            
            // Delete the user
            $stmt = $db->prepare("DELETE FROM users WHERE userid = ?");
            $stmt->execute([$userId]);
            
            // Delete the photo file if it exists
            if ($photoPath) {
                $filePath = '../../../uploads/users/' . $photoPath;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            $message = "User deleted successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error deleting user: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Fetch all users
$users = $db->query("SELECT u.*, r.role_name FROM users u LEFT JOIN user_roles r ON u.roleid = r.roleid ORDER BY u.created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch all roles
$roles = $db->query("SELECT * FROM user_roles ORDER BY role_name")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<?php include('../../../layouts/admin_header.php'); ?>

<body id="page-top">
    <div id="wrapper">
        <?php include('../../../layouts/admin_sidebar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('../../../layouts/admin_navbar.php'); ?>

                <div class="container-fluid">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Users Management</h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" data-toggle="modal" data-target="#addUserModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i> Add New User
                        </button>
                    </div>

                    <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <?php endif; ?>

                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">All Users</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Photo</th>
                                            <th>Name</th>
                                            <th>Username</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td class="text-center">
                                                <?php if ($user['photo']): ?>
                                                    <img src="../../../uploads/users/<?php echo htmlspecialchars($user['photo']); ?>" 
                                                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                                                <?php else: ?>
                                                    <i class="fas fa-user-circle fa-2x text-muted"></i>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']); ?></td>
                                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                            <td><?php echo htmlspecialchars($user['role_name'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $user['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                                                    <button class="btn btn-sm btn-info edit-user" 
                                                            data-id="<?php echo $user['userid']; ?>"
                                                            data-firstname="<?php echo htmlspecialchars($user['firstname']); ?>"
                                                            data-lastname="<?php echo htmlspecialchars($user['lastname']); ?>"
                                                            data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                            data-username="<?php echo htmlspecialchars($user['username']); ?>"
                                                            data-phone="<?php echo htmlspecialchars($user['phone']); ?>"
                                                            data-address="<?php echo htmlspecialchars($user['address']); ?>"
                                                            data-status="<?php echo htmlspecialchars($user['status']); ?>"
                                                            data-roleid="<?php echo $user['roleid']; ?>"
                                                            data-photo="<?php echo $user['photo'] ? '../../../uploads/users/' . htmlspecialchars($user['photo']) : ''; ?>">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger delete-user" 
                                                            data-id="<?php echo $user['userid']; ?>"
                                                            data-name="<?php echo htmlspecialchars($user['firstname']) . ' ' . htmlspecialchars($user['lastname']); ?>">
                                                        <i class="fas fa-trash"></i> 
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; EVERRETREAT <?php echo date('Y'); ?></span>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="addUserForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_user">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addUserModalLabel">Add New User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="first_name">First Name</label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="last_name">Last Name</label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">Phone</label>
                                    <input type="tel" class="form-control" id="phone" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_id">Role</label>
                                    <select class="form-control" id="role_id" name="role_id" required>
                                        <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['roleid']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="status">Status</label>
                                    <select class="form-control" id="status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="photo">Profile Photo</label>
                            <input type="file" class="form-control-file" id="photo" name="photo">
                            <small class="form-text text-muted">Max size: 2MB. Formats: JPG, PNG, GIF.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add User</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <form id="editUserForm" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="update_user">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_first_name">First Name</label>
                                    <input type="text" class="form-control" id="edit_first_name" name="first_name" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_last_name">Last Name</label>
                                    <input type="text" class="form-control" id="edit_last_name" name="last_name" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_email">Email</label>
                                    <input type="email" class="form-control" id="edit_email" name="email" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_username">Username</label>
                                    <input type="text" class="form-control" id="edit_username" name="username" required>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_password">New Password (Leave blank to keep current)</label>
                                    <input type="password" class="form-control" id="edit_password" name="password">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_phone">Phone</label>
                                    <input type="tel" class="form-control" id="edit_phone" name="phone">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_address">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_role_id">Role</label>
                                    <select class="form-control" id="edit_role_id" name="role_id" required>
                                        <?php foreach ($roles as $role): ?>
                                        <option value="<?php echo $role['roleid']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="edit_status">Status</label>
                                    <select class="form-control" id="edit_status" name="status" required>
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Current Photo</label>
                            <div class="mb-2">
                                <img id="current_user_photo" src="" class="img-thumbnail" style="max-height: 150px;">
                            </div>
                            <label for="edit_photo">Change Photo</label>
                            <input type="file" class="form-control-file" id="edit_photo" name="photo">
                            <small class="form-text text-muted">Leave empty to keep current photo. Max size: 2MB. Formats: JPG, PNG, GIF.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete User Modal -->
    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="deleteUserForm" method="post">
                    <input type="hidden" name="action" value="delete_user">
                    <input type="hidden" name="user_id" id="delete_user_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteUserModalLabel">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete the user "<span id="delete_user_name"></span>"?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include('../../../layouts/admin_scripts.php'); ?>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#usersTable').DataTable({
                responsive: true,
                columnDefs: [
                    { orderable: false, targets: [0, 8] },
                    { searchable: false, targets: [0, 8] }
                ]
            });

            // Edit user modal
            $('.edit-user').click(function() {
                var userId = $(this).data('id');
                var firstName = $(this).data('firstname');
                var lastName = $(this).data('lastname');
                var email = $(this).data('email');
                var username = $(this).data('username');
                var phone = $(this).data('phone');
                var address = $(this).data('address');
                var status = $(this).data('status');
                var roleId = $(this).data('roleid');
                var photo = $(this).data('photo');
                console.log("Button clicked "+userId+" first: "+firstName+" last: "+lastName+" email: "+email+" username: "+username+" emaphoneil: "+phone+" address: "+address+" status: "+status+" roleId: "+roleId+" photo: "+photo)

                $('#edit_user_id').val(userId);
                $('#edit_first_name').val(firstName);
                $('#edit_last_name').val(lastName);
                $('#edit_email').val(email);
                $('#edit_username').val(username);
                $('#edit_phone').val(phone);
                $('#edit_address').val(address);
                $('#edit_status').val(status);
                $('#edit_role_id').val(roleId);
                
                if (photo) {
                    $('#current_user_photo').attr('src', photo);
                } else {
                    $('#current_user_photo').attr('src', '');
                }

                $('#editUserModal').modal('show');
            });

            // Delete user modal
            $('.delete-user').click(function() {
                var userId = $(this).data('id');
                var userName = $(this).data('name');

                $('#delete_user_id').val(userId);
                $('#delete_user_name').text(userName);

                $('#deleteUserModal').modal('show');
            });

            // Preview for new photo in edit modal
            $('#edit_photo').on('change', function() {
                var file = this.files[0];
                if (file) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        $('#current_user_photo').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>
</html>