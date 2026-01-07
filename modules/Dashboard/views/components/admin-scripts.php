<?php
// modules/Dashboard/views/components/admin-scripts.php
?>
<script>
    // Toggle sidebar on mobile
    function toggleSidebar() {
        document.querySelector('.sidebar').classList.toggle('active');
    }

    // Logout function
    function logout() {
        Swal.fire({
            title: 'Logout?',
            text: 'Are you sure you want to logout?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Clear auth cookie
                document.cookie = "auth_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                window.location.href = "<?= url('logout') ?>";
            }
        });
    }

    // Change password function
    function changePassword() {
        Swal.fire({
            title: 'Change Password',
            html: `
                <div class="text-start">
                    <div class="mb-3">
                        <label class="form-label">Current Password</label>
                        <input type="password" id="currentPassword" class="form-control" placeholder="Enter current password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" id="newPassword" class="form-control" placeholder="Enter new password">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" id="confirmPassword" class="form-control" placeholder="Confirm new password">
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#667eea',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Update Password',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const current = document.getElementById('currentPassword').value;
                const newPass = document.getElementById('newPassword').value;
                const confirm = document.getElementById('confirmPassword').value;

                if (!current || !newPass || !confirm) {
                    Swal.showValidationMessage('Please fill all fields');
                    return false;
                }

                if (newPass !== confirm) {
                    Swal.showValidationMessage('New passwords do not match');
                    return false;
                }

                if (newPass.length < 6) {
                    Swal.showValidationMessage('Password must be at least 6 characters');
                    return false;
                }

                return { current, newPass };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Make API call to change password
                $.ajax({
                    url: '<?= url('api/auth') ?>',
                    method: 'POST',
                    data: {
                        action: 'change_password',
                        current_password: result.value.current,
                        new_password: result.value.newPass
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: 'Password changed successfully!'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Failed to change password'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Failed to change password'
                        });
                    }
                });
            }
        });
    }

    // Load content function
    function loadContent(page) {
        // This function should be implemented based on your SPA needs
        console.log('Loading content:', page);
    }

    // Initialize DataTables
    $(document).ready(function() {
        $('.datatable').DataTable({
            "pageLength": 25,
            "responsive": true,
            "order": [[0, 'desc']],
            "language": {
                "search": "Search:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    });

    // Confirm delete function
    function confirmDelete(item, id, callback) {
        Swal.fire({
            title: 'Delete ' + item + '?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed && typeof callback === 'function') {
                callback(id);
            }
        });
    }

    // Show notification
    function showNotification(message, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }

    // Preview image
    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        const file = input.files[0];
        const reader = new FileReader();

        reader.onloadend = function() {
            preview.src = reader.result;
            preview.style.display = 'block';
        }

        if (file) {
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    }

    // Toggle status
    function toggleStatus(id, currentStatus, callback) {
        const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
        if (typeof callback === 'function') {
            callback(id, newStatus);
        }
    }
</script>