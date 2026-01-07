<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container">
    <h2 class="mt-5">Register</h2>
    <form id="registerForm">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
            <small id="emailError" class="text-danger"></small> <!-- Display email error here -->
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Register</button>
    </form>

    <div id="registerResponse" class="mt-3"></div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- SweetAlert from your assets folder -->
<script src="../../../assets/js/sweetalert2.js"></script>

<script>
    $(document).ready(function () {
        // Real-time email validation
        $('#email').on('keyup', function () {
            let email = $(this).val();
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
                    email: $('#email').val(),
                    password: $('#password').val()
                };

                $.ajax({
                    type: 'POST',
                    url: '../api/authApi.php?action=register',
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
                            text: 'Something went wrong. Please try again.'
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

</body>
</html>
