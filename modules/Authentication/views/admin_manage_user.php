<?php
require_once '../../../helpers/JWTHandler.php'; // Include your JWT handler

// Start the session or read the cookie
if (!isset($_COOKIE['jwtToken'])) {
    // If the cookie is not set, redirect the user to the login page
    // echo "<h2>If the cookie is not set, redirect the user to the login page</h2>";
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// Retrieve the token from the cookie
$jwtToken = $_COOKIE['jwtToken'];

// Initialize JWT handler
$jwtHandler = new JWTHandler();

// Validate the token
$decodedToken = $jwtHandler->validateToken($jwtToken);

if ($decodedToken === false) {
    // If the token is invalid, redirect to login page
    header('Location: ../../Authentication/views/login.php');
    exit;
}

// if ($decodedToken->role != 1) {
//     echo "<h2>Access Denied</h2>";
//     exit;
// }
?>



<!DOCTYPE html>
<html lang="en">

<?php include('../../../includes/admin_header.php'); ?>

<body>

    <!-- ======= Header ======= -->
    <?php include('../../../includes/admin_navbar.php'); ?>
    <!-- ======= END Header ======= -->


    <!-- ======= SIDE MENU (MODULES) ======= -->
    <?php include('../../../includes/admin_sidebar.php'); ?>
    <!-- ======= SIDE MENU (MODULES) ======= -->



    <main id="main" class="main">

        <div class="pagetitle">
            <h1>MANAGE USERS</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.html">Home</a></li>
                    <li class="breadcrumb-item active">USERS</li>
                </ol>
            </nav>
        </div><!-- End Page Title -->

        <section class="section profile users">
            <div class="row">
                <div class="col-xl-12">

                    <div class="card">
                        <div class="card-body pt-3 ">
                        <div class="table-responsive table-sm users-table">                            
                            <table class="table datatable">
                                <thead>
                                  <tr>
                                    <th><span class="badge text-dark">PICTURE</span></th>
                                    <th><span class="badge text-dark">PROFILE NAMES</th>
                                    <th><span class="badge text-dark">ROLE</span></th>
                                    <th><span class="badge text-dark">PHONE</span></th>
                                    <th><span class="badge text-dark">EMAIL</span></th>
                                    <th><span class="badge text-dark">USERNAME</span></th>
                                    <th><span class="badge text-dark">ACCOUNT STATUS</span></th>
                                    <th><span class="badge text-dark">ACTION</span></th>
                                  </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                              <!-- End Table with stripped rows -->
                        </div>
                            
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main><!-- End #main -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- ADDED MODELS Files -->
    <?php include('../static/view_user_model.php'); ?>

    <!-- Include Dashboard Scripts -->
    <?php include('../../../includes/admin_scripts.php'); ?>

    <script>
        $(document).ready(function () {
            $("#daterangepicker").kendoDateRangePicker({
                calendarButton: true,
                clearButton: false
            });
            $('#daterangepicker input').addClass('filter-control'); 

            $('#start').mobiscroll().datepicker({
                select: 'range',
                startInput: '#start',
                endInput: '#end'
            });

        });

    </script>
    <script>
        $(document).ready(function () {
           
            function fetchUsers() {
            fetch('../api/authApi.php?action=fetchUsers') // Call the API
            .then(response => response.json())
            .then(data => {
                if (Array.isArray(data)) {
                    // console.log("Fetched users:", data);
                    populateTable(data);
                }
            })
            .catch(error => console.error('Error fetching Users:', error));
            }

            function populateTable(data) {
                var tbody = $('.users-table tbody');
                tbody.empty();
                data.forEach(function (user) {
                    const photo = user.photo ? `../../../assets/img/${user.photo}` : '../../../assets/img/profile.png';
                    const status = user.status;
                    var buttonsDiv = '';
                    var badge = '';
                    var role = '';
                        if (user.roleid == 1) {
                            role = 'MANAGER';
                        }else if (user.roleid == 2){
                            role =  'MILK COLLECTOR';
                        }else if (user.roleid == 3){
                            role =  'FARMER';
                        }else if (user.roleid == 4){
                            role =  'CUSTOMER';
                        }

                    if (status == 'Waiting'){
                        badge = "bg-warning";
                        buttonsDiv = `
                            <div class="btn-group btn-group-sm" role="group" aria-label="Button group with nested dropdown">
                                <button type="button" class="btn btn-info view" data-viewid="${user.userid}">View</button>
                                <button type="button" class="btn btn-success activate" data-activeid="${user.userid}">Activate</button>
                                <button type="button" class="btn btn-warning cancel" data-cancelid="${user.userid}">Cancel</button>
                                <button type="button" class="btn btn-danger delete" data-deleteid="${user.userid}">Delete</button>
                            </div>`;
                    }
                    else if(status == 'Pending'){
                        badge = "bg-info";
                        buttonsDiv = `
                            <div class="btn-group btn-group-sm" role="group" aria-label="Button group with nested dropdown">
                                <button type="button" class="btn btn-info view" data-viewid="${user.userid}">View</button>
                                <button type="button" class="btn btn-danger delete" data-deleteid="${user.userid}">Delete</button>
                            </div>`;
                    }
                    else if (status == 'Active'){
                        badge = "bg-warning";
                        buttonsDiv = `
                            <div class="btn-group btn-group-sm" role="group" aria-label="Button group with nested dropdown">
                                <button type="button" class="btn btn-info view" data-viewid="${user.userid}">View</button>
                                <button type="button" class="btn btn-dark deactivate" data-deactiveid="${user.userid}">Deactivate</button>
                            </div>`;
                    }
                    else if (status == 'Deactivated'){
                        badge = "bg-danger";
                        buttonsDiv = `
                            <div class="btn-group btn-group-sm" role="group" aria-label="Button group with nested dropdown">
                                <button type="button" class="btn btn-info view" data-viewid="${user.userid}">View</button>
                                <button type="button" class="btn btn-success activate" data-activeid="${user.userid}">Activate</button>
                            </div>`;
                    }
                    else if(status == 'Canceled'){
                        badge = "bg-secondary";
                        buttonsDiv = `
                            <div class="btn-group btn-group-sm" role="group" aria-label="Button group with nested dropdown">
                                <button type="button" class="btn btn-info view" data-viewid="${user.userid}">View</button>
                                <button type="button" class="btn btn-danger delete" data-deleteid="${user.userid}">Delete</button>
                            </div>`;
                    }
                    var row = `<tr>
                        <td><span class="badge text-dark"><img class="rounded-circle img-responsive mt-2" src='${photo}' height='60px' width='60px'></span></td>
                        <td><span class="badge text-black">${user.firstname} ${user.lastname}</span></td> <!-- Fixed customer name -->
                        <td><span class="badge text-black">${role}</span></td>
                        <td><span class="badge text-black">${user.phone || 'N/A'}</span></td> <!-- Packages formatted -->
                        <td><span class="badge text-black">${user.email}</span></td> <!-- Formatted total amount -->
                        <td><span class="badge text-black">${user.username}</td>
                        <td><span class="badge ${badge} text-black">${user.status}</span></td> <!-- Amount Paid -->
                        <td>${buttonsDiv}</td>
                    </tr>`;
                    tbody.append(row);
                });
            }

            // Check user role and fetch data accordingly
            var userRole = <?php echo $decodedToken->role; ?>; // Get role from PHP
            console.log("Role: ",userRole);
            if (userRole == 1) { // MANAGER
                fetchUsers();
            } 

        // });
        // $(document).ready(function () {
            // Event delegation for dynamic buttons
            $('.users-table').on('click', '.activate', function () {
                const userId = this.getAttribute('data-activeid');
                updateUserStatus(userId, 'Active');
            });

            $('.users-table').on('click', '.deactivate', function () {
                const userId = this.getAttribute('data-deactiveid');
                console.log("userid deactivated: ",userId);
                updateUserStatus(userId, 'Deactivated');
            });

            $('.users-table').on('click', '.cancel', function () {
                const userId = this.getAttribute('data-cancelid');
                updateUserStatus(userId, 'Canceled');
            });

            $('.users-table').on('click', '.delete', function () {
                const userId = this.getAttribute('data-deleteid');
                deleteUser(userId);
            });

            $('.users-table').on('click', '.view', function () {
                const userId = this.getAttribute('data-viewid');
                viewUserDetails(userId);
                // console.log("User ID on view ",userId);
            });

            // Function to update user status
            function updateUserStatus(userId, status) {
                let formData = {
                    userId: userId,
                    status: status
                    };

                $.ajax({
                    type: 'POST',
                    url: '../api/authApi.php?action=updateUserStatus',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Use SweetAlert for success message
                            Swal.fire({
                                icon: 'success',
                                title: 'UPDATES!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                fetchUsers(); // Redirect after success
                            });
                        } else {
                            // Use SweetAlert for error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong. Please try again. '+status+' '+error
                        });
                    }
                });
            }

            // Function to delete a user
            function deleteUser(userId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this action!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        let formData = { userId: userId };

                        $.ajax({
                            type: 'POST',
                            url: '../api/authApi.php?action=deleteUser',
                            data: formData,
                            dataType: 'json',
                            success: function (response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'User Deleted!',
                                        text: response.message,
                                        showConfirmButton: false,
                                        timer: 1500
                                    }).then(() => {
                                        fetchUsers(); // Refresh user list after success
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops...',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: 'Something went wrong. Please try again. ' + status + ' ' + error
                                });
                            }
                        });
                    }
                });
            }


            function formatDate(dateString) {
                const date = new Date(dateString);
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return date.toLocaleDateString(undefined, options);
            }

            // Function to view user details
            function viewUserDetails(userId) {

                let formData = {
                    userId: userId
                    };
                console.log("view User Details: ",formData);

                $.ajax({
                    type: 'POST',
                    url: '../api/authApi.php?action=viewUserDetails',
                    data: formData,
                    dataType: 'json',
                    success: function (data) {
                        if (data.success) {
                            // Display user details in a modal or another section
                            // console.log(data.user);
                            
                const formattedDate = formatDate(data.user.startingDate);
                    var role = '';
                        if (data.user.roleid == 1) {
                            role = 'MANAGER';
                        }else if (data.user.roleid == 2){
                            role =  'MILK COLLECTOR';
                        }else if (data.user.roleid == 3){
                            role =  'FARMER';
                        }else if (data.user.roleid == 4){
                            role =  'CUSTOMER';
                        }
                            // Example: Open a modal with user details
                            $('#userDetailsModal').modal('show');
                            $('#userDetailsModal .modal-body').html(`
                                <p>Name: ${data.user.firstname} ${data.user.lastname}</p>
                                <p>Email: ${data.user.email}</p>
                                <p>Phone: ${data.user.phone}</p>
                                <p>Username: ${data.user.username}</p>
                                <p>Role: ${role}</p>
                                <p>Address: ${data.user.address}</p>
                                <p>Status: Account ${data.user.status}</p>
                                <p>Account Created On: ${formattedDate}</p>
                            `);
                        } else {
                            // Use SweetAlert for error message
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: data.message
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong. Please try again. '+status+' '+error
                        });
                    }
                });
            }
        });
    </script>


</body>

</html>