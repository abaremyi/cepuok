

<!DOCTYPE html>
<html lang="en">

<?php include('../../../includes/admin_header.php'); ?>

<body>

    <main>
        <div class="container">
            <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
                <div class="container">
                    <div class="row justify-content-center">
                        <div class="col-lg-6 col-md-8 align-items-center justify-content-center">

                            <div class="d-flex justify-content-center py-4">
                                <a href="index.php" class="logo d-flex align-items-center w-auto">
                                    <img src="../../../assets/dashboard_assets/img/logo.png" alt="">
                                    <span class="d-none d-lg-block">UMCD</span>
                                </a>
                            </div><!-- End Logo -->

                            <div class="card mb-3">
                                <div class="card-body">
                                    <div class="pt-4 pb-2">
                                        <h5 class="card-title text-center pb-0 fs-4">Verify Your Email</h5>
                                    </div>

                                    <form id="verifyEmailForm">
                                        <div class="col-12">
                                            <label for="email" class="form-label">Enter Your Email</label>
                                            <input type="email" name="email" class="form-control" id="email" required>
                                        </div>

                                        <div class="col-12" style="margin-top: 10px;">
                                            <button class="btn btn-success w-100" type="submit" id="submitBtn">Check Email</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="credits text-center">
                                Designed by <a href="#">Emmanuel NSENGIMANA</a>
                            </div>

                        </div>
                    </div>
                </div>
            </section>
        </div>
    </main><!-- End #main -->

    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- Include Dashboard Scripts -->
    <?php include('../../../includes/admin_scripts.php'); ?>

    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include SweetAlert for alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function () {

            $("#verifyEmailForm").on("submit", function (e) {
                e.preventDefault(); 

                var email = $("#email").val();

                if (email == '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Please Enter Your Email first.'
                    });
                    return;
                }

                var formData = {
                    email: email
                };

                $.ajax({
                    type: "POST",
                    url: "../api/authApi.php?action=verifyEmail", // Adjust your API URL
                    data: formData,
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Your Email has been verified successfully You may continue with Password reset.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                window.location.href = "resetPassword.php?email="+email; // Redirect to login page
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops!',
                                text: response.message
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Something went wrong. Please try again. ' + status + ' ' + error
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>
