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

// If the token is valid, proceed to display the dashboard
// echo "<h2>Welcome to the Dashboard, {$decodedToken->username}</h2>";
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



  <main>

    <div class="container">

      <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10 d-flex flex-column align-items-center justify-content-center">

              <div class="card mb-3 mt-5">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 style="color:green" class="card-title text-center pb-0 fs-4">Create Account</h5>
                    <p class="text-center small">User May Be Manager or Collector</p>
                  </div>

                  <!-- <form class="row g-3 needs-validation" novalidate> -->
                  <form id="registerForm" class="row g-3">
                    <div class="col-6">
                      <label for="your First Name" class="form-label">First Name</label>
                      <input type="text" name="firstname" class="form-control" id="firstname" required>

                      <label for="your Last Name" class="form-label">Last Name</label>
                      <input type="text" name="lastname" class="form-control" id="lastname" required>

                      <div class="invalid-feedback">Please, enter name!</div>
                    </div>

                    <div class="col-6">
                        <label for="phone" class="form-label"> Phone number</label>
                        <input type="number" name="phone" class="form-control" id="phone" required>

                        <label for="yourEmail" class="form-label"> Email</label>
                        <input type="email" name="email" class="form-control" id="email" required>


                        <div class="invalid-feedbacks" id="emailError"></div>
                    </div>

                    <div class="col-6">
                        <label for="role" class="form-label"> SELECT YOUR ROLE</label>
                        <select name="role" id="roleid" class="form-control">
                            <option value="">--------Select your ROLE---------</option>
                            <option value="1">Manager</option>
                            <option value="2">Milk collector</option>
                        </select>
                    </div>
                    <div class="col-6">
                      <label for="yourUsername" class="form-label">Username</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text" name="username" class="form-control" id="username" required>
                        <div class="invalid-feedback">Please choose a username.</div>
                      </div>
                    </div>

                    <div class="col-6">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" id="password" required>
                        <div class="invalid-feedback" id='passwordMismatch'>Passwords do not match!</div>
                    </div>

                    <div class="col-6">
                        <label for="repassword" class="form-label">Re-Password</label>
                        <input type="password" class="form-control" id="repassword" required>
                    </div>
                    <div class="col-6">
                        <label for="your address" class="form-label">address</label>
                        <input type="text" name="address" class="form-control" id="address" required>
                    </div>
                    <div class="col-6">
                        <label for="your startingDate" class="form-label">startingDate</label>
                        <input type="text" name="startingDate" class="form-control" id="startingDate" required>
                    </div>

                  
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Create Account</button>
                    </div>
                    <div class="col-12">
                      <p class="small mb-0">Already have an account? <a href="login.php">Log in</a></p>
                    </div>
                  </form>

                </div>
              </div>

              <div class="credits">
                <!-- All the links in the footer should remain intact. -->
                <!-- You can delete the links only if you purchased the pro version. -->
                <!-- Licensing information: https://bootstrapmade.com/license/ -->
                <!-- Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/ -->
                Designed by <a href="https://bootstrapmade.com/">NSENGIMANA Emmanuel</a>
              </div>

            </div>
          </div>
        </div>

      </section>

    </div>
  </main><!-- End #main -->

  <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  
  <!-- Include Dashboard Scripts -->
  <?php include('../../../includes/admin_scripts.php'); ?>

  <script>
    $(document).ready(function () {
        // Real-time email validation
        $('#email').on('keyup', function () {
            let email = $(this).val();
            let phone = $('#phone').val();
            // console.log("email is "+email +"Phone is "+phone);
            if (email) {
                $.ajax({
                    type: 'POST',
                    url: '../api/authApi.php?action=checkEmail',
                    data: {email: email},
                    dataType: 'json',
                    success: function (response) {
                        if (response.exists) {
                            $('#emailError').text('Email is already taken. Please choose another.');
                        } else {
                            $('#emailError').text(''); // Clear the message if email is available
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        console.log('Response:', xhr.responseText);
                    }
                });
            }
        });

        // Form submission logic
        $('#registerForm').on('submit', function (e) {
            e.preventDefault();

            // Check if there's any error before submitting
            if ($('#emailError').text() === '') {
                let formData = {
                    firstname: $('#firstname').val(),
                    lastname: $('#lastname').val(),
                    phone: $('#phone').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    username: $('#username').val(),
                    address: $('#address').val(),
                   
                    startingDate: $('#startingDate').val(),
                    roleid: $('#roleid').val(),
                };
                console.log('password:'+formData.password+', phone:'+formData.phone+', email:'+formData.email+', lastname:'+formData.lastname+', username:'+formData.username+' address:'+formData.address+' startingDate:'+formData.startingDate)+' roleid:'+formData.roleid;
                $.ajax({
                    type: 'POST',
                    url: '../api/authApi.php?action=adminRegister',
                    data: formData,
                    dataType: 'json',
                    success: function (response) {
                        if (response.success) {
                            // Use SweetAlert for success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Registered!',
                                text: response.message,
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                window.location.href = 'login.php'; // Redirect after success
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
            } else {
                // Notify the user about the error using SweetAlert
                Swal.fire({
                    icon: 'warning',
                    title: 'Email in Use',
                    text: 'Please use a different email.'
                });
            }
        });
    });
</script>
<script>
    $(document).ready(function () {
        function validatePasswords() {
            let password = $("#password").val();
            let repassword = $("#repassword").val();
            let mismatchDiv = $("#passwordMismatch");

            if (password !== repassword) {
                mismatchDiv.show();
                $("#submitBtn").prop("disabled", true);
            } else {
                mismatchDiv.hide();
                $("#submitBtn").prop("disabled", false);
            }
        }

        // Validate on keyup
        $("#password, #repassword").on("keyup", validatePasswords);
    });
</script>

</body>

</html>