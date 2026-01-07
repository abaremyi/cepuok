<!DOCTYPE html>
<html lang="en">
<?php
// Include paths configuration
$root_path = dirname(dirname(dirname(dirname(__FILE__))));
require_once $root_path . "/config/paths.php";
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login & Register - Mount Carmel School</title>
    <link rel="shortcut icon" href="<?= img_url('logo-only.png') ?>" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Montserrat', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background-color: #fff;
            border-radius: 30px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.35);
            position: relative;
            overflow: hidden;
            width: 768px;
            max-width: 100%;
            min-height: 480px;
        }

        .container p {
            font-size: 14px;
            line-height: 20px;
            letter-spacing: 0.3px;
            margin: 20px 0;
        }

        .container span {
            font-size: 12px;
        }

        .container a {
            color: #333;
            font-size: 13px;
            text-decoration: none;
            margin: 15px 0 10px;
            cursor: pointer;
        }

        .container a:hover {
            color: #764ba2;
        }

        .container button {
            background-color: #764ba2;
            color: #fff;
            font-size: 12px;
            padding: 10px 45px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            margin-top: 10px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .container button:hover {
            background-color: #667eea;
            transform: translateY(-2px);
        }

        .container button.hidden {
            background-color: transparent;
            border-color: #fff;
        }

        .container form {
            background-color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 40px;
            height: 100%;
        }

        .container input,
        .container select {
            background-color: #eee;
            border: none;
            margin: 8px 0;
            padding: 10px 15px;
            font-size: 13px;
            border-radius: 8px;
            width: 100%;
            outline: none;
        }

        .container input:focus,
        .container select:focus {
            background-color: #f8f9fa;
            border: 1px solid #764ba2;
        }

        .form-container {
            position: absolute;
            top: 0;
            height: 100%;
            transition: all 0.6s ease-in-out;
        }

        .sign-in {
            left: 0;
            width: 50%;
            z-index: 2;
        }
        .form-h1 {
         text-transform: uppercase;
         font-size: 34px;
         letter-spacing: 2px;
         font-weight: 300;
         font-family: Impact, Haettenschweiler, 'Arial Narrow Bold', sans-serif;
         color: rgba(6, 74, 151, 0.8);
         /* text-shadow: 0 0 10px rgba(2, 212, 212, 1), 0 0 20px rgba(255, 255, 255, 1), 0 0 30px rgba(238, 255, 0, 1); */
        }

        .container.active .sign-in {
            transform: translateX(100%);
        }

        .sign-up {
            left: 0;
            width: 50%;
            opacity: 0;
            z-index: 1;
        }

        .container.active .sign-up {
            transform: translateX(100%);
            opacity: 1;
            z-index: 5;
            animation: move 0.6s;
        }

        @keyframes move {
            0%, 49.99% {
                opacity: 0;
                z-index: 1;
            }
            50%, 100% {
                opacity: 1;
                z-index: 5;
            }
        }

        .social-icons {
            margin: 20px 0;
        }

        .social-icons a {
            border: 1px solid #ccc;
            border-radius: 20%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            margin: 0 3px;
            width: 40px;
            height: 40px;
            transition: all 0.3s;
        }

        .social-icons a:hover {
            border-color: #764ba2;
            color: #764ba2;
        }

        .toggle-container {
            position: absolute;
            top: 0;
            left: 50%;
            width: 50%;
            height: 100%;
            overflow: hidden;
            transition: all 0.6s ease-in-out;
            border-radius: 150px 0 0 100px;
            z-index: 1000;
        }

        .container.active .toggle-container {
            transform: translateX(-100%);
            border-radius: 0 150px 100px 0;
        }

        .toggle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100%;
            color: #fff;
            position: relative;
            left: -100%;
            height: 100%;
            width: 200%;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .container.active .toggle {
            transform: translateX(50%);
        }

        .toggle-panel {
            position: absolute;
            width: 50%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding: 0 30px;
            text-align: center;
            top: 0;
            transform: translateX(0);
            transition: all 0.6s ease-in-out;
        }

        .toggle-left {
            transform: translateX(-200%);
        }

        .container.active .toggle-left {
            transform: translateX(0);
        }

        .toggle-right {
            right: 0;
            transform: translateX(0);
        }

        .container.active .toggle-right {
            transform: translateX(200%);
        }

        .school-logo {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .school-logo i {
            font-size: 30px;
            color: #764ba2;
        }

        .otp-inputs {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            gap: 10px;
        }

        .otp-inputs input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
        }

        .otp-inputs input:focus {
            border-color: #764ba2;
            background-color: #fff;
        }

        .timer {
            font-size: 12px;
            color: #666;
            text-align: center;
            margin-top: 10px;
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 20px;
            border: none;
        }

        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            border: none;
        }

        .form-text {
            font-size: 11px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="container" id="mainContainer">
        <!-- Sign In Form -->
        <div class="form-container sign-in">
            <form id="loginForm">
                <div class="school-logo">
                    <!-- <i class="fas fa-school"></i> -->
                    <img src="<?= img_url('logo-only.png') ?>" alt="MCS Logo" style="width: 100%; height: 100%; object-fit: contain;">
                </div>
                <h1 class="form-h1">Sign In</h1>
                <span>Use your Email, Username or Phone & Password</span>
                <input type="text" id="loginIdentifier" placeholder="Email or Phone or Username" required />
                <input type="password" id="loginPassword" placeholder="Password" required />
                <a id="forgotPasswordLink">Forgot Your Password?</a>
                <a href="<?= url() ?>">Back Home</a>
                <button type="submit">Sign In</button>
            </form>
        </div>

        <!-- Sign Up Form -->
        <div class="form-container sign-up">
            <form id="registerForm">
                <!-- <div class="school-logo">
                    <i class="fas fa-user-plus"></i>
                </div> -->
                <h1 class="form-h1">Create Account</h1>
                <span>Use your email for registration</span>
                <input type="text" id="regFirstname" placeholder="First Name" required />
                <input type="text" id="regLastname" placeholder="Last Name" required />
                <input type="email" id="regEmail" placeholder="Email" required />
                <select id="regRole" class="form-select" required>
                    <option value="5">Student</option>
                    <option value="4">Parent</option>
                    <option value="3">Teacher</option>
                </select>
                <input type="tel" id="regPhone" placeholder="Phone Number" required />
                <input type="password" id="regPassword" placeholder="Password (min 8 chars)" required />
                <button type="submit">Sign Up</button>
            </form>
        </div>

        <!-- Toggle Panel -->
        <div class="toggle-container">
            <div class="toggle">
                <div class="toggle-panel toggle-left">
                    <h1>Welcome Back!</h1>
                    <p>Enter your personal details to use all site features</p>
                    <button class="hidden" id="loginBtn">Sign In</button>
                </div>
                <div class="toggle-panel toggle-right">
                    <h1>Hello, Friend!</h1>
                    <p>Register with your personal details to use all site features</p>
                    <button class="hidden" id="registerBtn">Sign Up</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-key me-2"></i>Reset Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="forgotPasswordForm">
                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="forgotEmail" placeholder="Enter your email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Send OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- OTP Verification Modal -->
    <div class="modal fade" id="otpModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-shield-alt me-2"></i>Verify OTP</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-center">Enter the 6-digit code sent to <strong id="otpEmail"></strong></p>
                    <form id="otpForm">
                        <div class="otp-inputs">
                            <input type="text" maxlength="1" class="otp-input" data-index="0">
                            <input type="text" maxlength="1" class="otp-input" data-index="1">
                            <input type="text" maxlength="1" class="otp-input" data-index="2">
                            <input type="text" maxlength="1" class="otp-input" data-index="3">
                            <input type="text" maxlength="1" class="otp-input" data-index="4">
                            <input type="text" maxlength="1" class="otp-input" data-index="5">
                        </div>
                        <div class="timer">OTP valid for: <span id="otpCountdown">05:00</span></div>
                        <button type="submit" class="btn btn-primary w-100 mt-3">Verify OTP</button>
                        <div class="text-center mt-2">
                            <a href="#" id="resendOtpBtn">Resend OTP</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-lock me-2"></i>Set New Password</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="resetPasswordForm">
                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <input type="password" class="form-control" id="newPassword" placeholder="Enter new password" required>
                            <div class="form-text">Min 8 characters with uppercase, lowercase, and number</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm new password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const container = document.getElementById("mainContainer");
        const registerBtn = document.getElementById("registerBtn");
        const loginBtn = document.getElementById("loginBtn");
        const BASE_URL = '<?= url() ?>';
        const API_URL = BASE_URL + '/api/auth';

        // Global variables for password reset flow
        let resetEmail = '';
        let verifiedOtp = '';
        let otpTimer;
        let otpSeconds = 300;

        // Toggle between login and register
        registerBtn.addEventListener("click", (e) => {
            e.preventDefault();
            container.classList.add("active");
        });

        loginBtn.addEventListener("click", (e) => {
            e.preventDefault();
            container.classList.remove("active");
        });

        // Login Form Handler
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const identifier = document.getElementById('loginIdentifier').value.trim();
            const password = document.getElementById('loginPassword').value;

            if (!identifier || !password) {
                Swal.fire('Error', 'Please fill in all fields', 'error');
                return;
            }

            Swal.fire({
                title: 'Logging in...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(`${API_URL}?action=login`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ identifier, password })
                });

                const result = await response.json();

                if (result.success) {
                    localStorage.setItem('auth_token', result.token);
                    localStorage.setItem('user', JSON.stringify(result.user));
                    //  Set cookie too
                    document.cookie = `auth_token=${result.token}; path=/; max-age=${24*60*60}`;
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: result.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        redirectBasedOnRole(result.user);
                    });
                } else {
                    Swal.fire('Login Failed', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Cannot connect to server', 'error');
            }
        });

        // Register Form Handler
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const data = {
                firstname: document.getElementById('regFirstname').value.trim(),
                lastname: document.getElementById('regLastname').value.trim(),
                email: document.getElementById('regEmail').value.trim(),
                phone: document.getElementById('regPhone').value.trim(),
                password: document.getElementById('regPassword').value,
                role_id: document.getElementById('regRole').value,
                username: document.getElementById('regEmail').value.trim()
            };

            if (data.password.length < 8) {
                Swal.fire('Error', 'Password must be at least 8 characters', 'error');
                return;
            }

            Swal.fire({
                title: 'Registering...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(`${API_URL}?action=register`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Registration Successful!',
                        text: 'Please wait for admin approval',
                        confirmButtonText: 'Go to Login'
                    }).then(() => {
                        container.classList.remove("active");
                        document.getElementById('registerForm').reset();
                    });
                } else {
                    Swal.fire('Registration Failed', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Cannot connect to server', 'error');
            }
        });

        // Forgot Password Flow
        document.getElementById('forgotPasswordLink').addEventListener('click', (e) => {
            e.preventDefault();
            const modal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));
            modal.show();
        });

        document.getElementById('forgotPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            resetEmail = document.getElementById('forgotEmail').value.trim();

            Swal.fire({
                title: 'Sending OTP...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(`${API_URL}?action=forgot-password`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: resetEmail })
                });

                const result = await response.json();

                if (result.success) {
                    Swal.close();
                    bootstrap.Modal.getInstance(document.getElementById('forgotPasswordModal')).hide();
                    
                    document.getElementById('otpEmail').textContent = resetEmail;
                    const otpModal = new bootstrap.Modal(document.getElementById('otpModal'));
                    otpModal.show();
                    
                    startOtpTimer();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Cannot connect to server', 'error');
            }
        });

        // OTP Input Handler
        document.querySelectorAll('.otp-input').forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < 5) {
                    document.querySelector(`.otp-input[data-index="${index + 1}"]`).focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    document.querySelector(`.otp-input[data-index="${index - 1}"]`).focus();
                }
            });
        });

        // OTP Verification
        document.getElementById('otpForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const otpInputs = document.querySelectorAll('.otp-input');
            const otp = Array.from(otpInputs).map(input => input.value).join('');

            if (otp.length !== 6) {
                Swal.fire('Error', 'Please enter all 6 digits', 'error');
                return;
            }

            Swal.fire({
                title: 'Verifying OTP...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(`${API_URL}?action=verify-otp`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: resetEmail, otp })
                });

                const result = await response.json();

                if (result.success) {
                    verifiedOtp = otp;
                    Swal.close();
                    bootstrap.Modal.getInstance(document.getElementById('otpModal')).hide();
                    
                    const resetModal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
                    resetModal.show();
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Cannot connect to server', 'error');
            }
        });

        // Reset Password
        document.getElementById('resetPasswordForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                Swal.fire('Error', 'Passwords do not match', 'error');
                return;
            }

            if (newPassword.length < 8) {
                Swal.fire('Error', 'Password must be at least 8 characters', 'error');
                return;
            }

            Swal.fire({
                title: 'Resetting password...',
                allowOutsideClick: false,
                showConfirmButton: false,
                willOpen: () => Swal.showLoading()
            });

            try {
                const response = await fetch(`${API_URL}?action=reset-password`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ 
                        email: resetEmail, 
                        otp: verifiedOtp, 
                        password: newPassword 
                    })
                });

                const result = await response.json();

                if (result.success) {
                    bootstrap.Modal.getInstance(document.getElementById('resetPasswordModal')).hide();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Reset Successful!',
                        text: 'You can now login with your new password',
                        confirmButtonText: 'Go to Login'
                    }).then(() => {
                        document.getElementById('forgotPasswordForm').reset();
                        document.getElementById('resetPasswordForm').reset();
                        document.querySelectorAll('.otp-input').forEach(input => input.value = '');
                    });
                } else {
                    Swal.fire('Error', result.message, 'error');
                }
            } catch (error) {
                Swal.fire('Error', 'Cannot connect to server', 'error');
            }
        });

        // Resend OTP
        document.getElementById('resendOtpBtn').addEventListener('click', async (e) => {
            e.preventDefault();
            
            const response = await fetch(`${API_URL}?action=forgot-password`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email: resetEmail })
            });

            const result = await response.json();
            
            if (result.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'OTP Resent!',
                    timer: 2000,
                    showConfirmButton: false
                });
                startOtpTimer();
            }
        });

        // OTP Timer
        function startOtpTimer() {
            clearInterval(otpTimer);
            otpSeconds = 300;
            
            otpTimer = setInterval(() => {
                otpSeconds--;
                const minutes = Math.floor(otpSeconds / 60);
                const secs = otpSeconds % 60;
                document.getElementById('otpCountdown').textContent = 
                    `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                
                if (otpSeconds <= 0) {
                    clearInterval(otpTimer);
                    Swal.fire('OTP Expired', 'Please request a new OTP', 'warning');
                }
            }, 1000);
        }

        // Redirect based on role
        function redirectBasedOnRole(user) {
            if (user.is_super_admin || user.role_id === 1) {
                window.location.href = `${BASE_URL}/admin`;
            } else if (user.role_id === 2) {
                window.location.href = `${BASE_URL}/admin`;
            } else {
                window.location.href = `${BASE_URL}/admin`;
            }
        }

        // Check for existing token
        const token = localStorage.getItem('auth_token');
        if (token) {
            fetch(`${API_URL}?action=validate`, {
                headers: { 'Authorization': `Bearer ${token}` }
            })
            .then(res => res.json())
            .then(result => {
                if (result.success && result.user) {
                    redirectBasedOnRole(result.user);
                }
            })
            .catch(() => {
                localStorage.removeItem('auth_token');
                localStorage.removeItem('user');
            });
        }
    </script>
</body>
</html>