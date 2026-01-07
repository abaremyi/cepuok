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
    if ($_POST['action'] === 'update_subscriber') {
        // Handle update subscriber status
        $subscriberId = $_POST['subscriber_id'];
        $status = $_POST['status'];
        
        try {
            if ($status === 'active') {
                $stmt = $db->prepare("UPDATE newsletter_subscribers SET is_active = 1, unsubscribed_at = NULL WHERE id = ?");
            } else {
                $stmt = $db->prepare("UPDATE newsletter_subscribers SET is_active = 0, unsubscribed_at = NOW() WHERE id = ?");
            }
            $stmt->execute([$subscriberId]);
            
            $message = "Subscriber updated successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error updating subscriber: " . $e->getMessage();
            $messageType = "danger";
        }
    } elseif ($_POST['action'] === 'delete_subscriber') {
        // Handle delete subscriber
        $subscriberId = $_POST['subscriber_id'];
        
        try {
            $stmt = $db->prepare("DELETE FROM newsletter_subscribers WHERE id = ?");
            $stmt->execute([$subscriberId]);
            
            $message = "Subscriber deleted successfully!";
            $messageType = "success";
        } catch (PDOException $e) {
            $message = "Error deleting subscriber: " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// Fetch all subscribers
$subscribers = $db->query("SELECT * FROM newsletter_subscribers ORDER BY subscribed_at DESC")->fetchAll(PDO::FETCH_ASSOC);
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
                        <h1 class="h3 mb-0 text-gray-800">Newsletter Subscribers</h1>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm" id="exportSubscribers">
                            <i class="fas fa-download fa-sm text-white-50"></i> Export Subscribers
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
                            <h6 class="m-0 font-weight-bold text-primary">All Subscribers</h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="subscribersTable" width="100%" cellspacing="0">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Status</th>
                                            <th>Subscribed</th>
                                            <th>Unsubscribed</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($subscribers as $subscriber): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($subscriber['email']); ?></td>
                                            <td>
                                                <span class="badge badge-<?php echo $subscriber['is_active'] ? 'success' : 'secondary'; ?>">
                                                    <?php echo $subscriber['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($subscriber['subscribed_at'])); ?></td>
                                            <td>
                                                <?php echo $subscriber['unsubscribed_at'] ? date('M d, Y', strtotime($subscriber['unsubscribed_at'])) : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info edit-subscriber" 
                                                        data-id="<?php echo $subscriber['id']; ?>"
                                                        data-email="<?php echo htmlspecialchars($subscriber['email']); ?>"
                                                        data-status="<?php echo $subscriber['is_active'] ? 'active' : 'inactive'; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger delete-subscriber" 
                                                        data-id="<?php echo $subscriber['id']; ?>"
                                                        data-email="<?php echo htmlspecialchars($subscriber['email']); ?>">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
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

    <!-- Edit Subscriber Modal -->
    <div class="modal fade" id="editSubscriberModal" tabindex="-1" role="dialog" aria-labelledby="editSubscriberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="editSubscriberForm" method="post">
                    <input type="hidden" name="action" value="update_subscriber">
                    <input type="hidden" name="subscriber_id" id="edit_subscriber_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editSubscriberModalLabel">Edit Subscriber Status</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Email</label>
                            <p class="form-control-static" id="edit_subscriber_email"></p>
                        </div>
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select class="form-control" id="edit_status" name="status" required>
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
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

    <!-- Delete Subscriber Modal -->
    <div class="modal fade" id="deleteSubscriberModal" tabindex="-1" role="dialog" aria-labelledby="deleteSubscriberModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="deleteSubscriberForm" method="post">
                    <input type="hidden" name="action" value="delete_subscriber">
                    <input type="hidden" name="subscriber_id" id="delete_subscriber_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteSubscriberModalLabel">Confirm Delete</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to delete subscriber with email "<span id="delete_subscriber_email"></span>"?</p>
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
            $('#subscribersTable').DataTable({
                responsive: true,
                order: [[2, 'desc']], // Default sort by subscribed date
                columnDefs: [
                    { orderable: false, targets: [4] },
                    { searchable: false, targets: [4] }
                ]
            });

            // Edit subscriber modal
            $('.edit-subscriber').click(function() {
                var subscriberId = $(this).data('id');
                var email = $(this).data('email');
                var status = $(this).data('status');

                $('#edit_subscriber_id').val(subscriberId);
                $('#edit_subscriber_email').text(email);
                $('#edit_status').val(status);

                $('#editSubscriberModal').modal('show');
            });

            // Delete subscriber modal
            $('.delete-subscriber').click(function() {
                var subscriberId = $(this).data('id');
                var email = $(this).data('email');

                $('#delete_subscriber_id').val(subscriberId);
                $('#delete_subscriber_email').text(email);

                $('#deleteSubscriberModal').modal('show');
            });

            // Export subscribers
            $('#exportSubscribers').click(function() {
                window.location.href = 'export_subscribers.php';
            });
        });
    </script>
</body>
</html>