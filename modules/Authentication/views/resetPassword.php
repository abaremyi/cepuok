<?php
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Mount Carmel School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .reset-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 100%;
            max-width: 450px;
        }
        .reset-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .reset-body {
            padding: 30px;
        }
        .school-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .school-logo i {
            font-size: 40px;
            color: #764ba2;
        }
        .btn-reset {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3);
        }
        .btn-reset:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        .otp-inputs {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .otp-inputs input {
            width: 50px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        .otp-inputs input:focus {
            border-color: #764ba2;
            box-shadow: 0 0 0 0.2rem rgba(118, 75, 162, 0.25);
        }
        .timer {
            font-size: 14px;
            color: #666;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <div class="school-logo">
                <i class="fas fa-lock"></i>
            </div>
            <h3>Set New Password</h3>
            <?php if ($email): ?>
                <p class="mb-0">for <?php echo htmlspecialchars($email); ?></p>
            <?php endif; ?>
        </div>
        <div class="reset-body">
            <form id="resetForm">
                <!-- OTP Section (can be shown/hidden based on verification method) -->
                <div id="otpSection" class="mb-3">
                    <label class="form-label">
                        <i class="fas fa-shield-alt me-2"></i>Enter OTP (Optional for demo)
                    </label>
                    <div class="otp-inputs">
                        <input type="text" maxlength="1" class="form-control" oninput="moveToNext(this, 1)">
                        <input type="text" maxlength="1" class="form-control" oninput="moveToNext(this, 2)">
                        <input type="text" maxlength="1" class="form-control" oninput="moveToNext(this, 3)">
                        <input type="text" maxlength="1" class="form-control" oninput="moveToNext(this, 4)">
                        <input type="text" maxlength="1" class="form-control" oninput="moveToNext(this, 5)">
                        <input type="text" maxlength="1" class="form-control" oninput="moveToNext(this, 6)">
                    </div>
                    <input type="hidden" id="otp" name="otp">
                    <div class="timer" id="otpTimer">
                        OTP valid for: <span id="countdown">05:00</span>
                    </div>
                    <div class="text-center mt-2">
                        <a href="#" id="resendOtp" class="text-decoration-none">
                            <i class="fas fa-redo me-1"></i>Resend OTP
                        </a>
                    </div>
                </div>
                
                <!-- Email (hidden) -->
                <input type="hidden" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                
                <!-- Password Fields -->
                <div class="mb-3">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock me-2"></i>New Password
                    </label>
                    <input type="password" class="form-control" id="password" required 
                           placeholder="Enter new password">
                    <div class="form-text">
                        Password must be at least 8 characters with uppercase, lowercase, and number
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="confirmPassword" class="form-label">
                        <i class="fas fa-lock me-2"></i>Confirm Password
                    </label>
                    <input type="password" class="form-control" id="confirmPassword" required 
                           placeholder="Confirm new password">
                    <div class="invalid-feedback" id="passwordError"></div>
                </div>
                
                <button type="submit" class="btn btn-reset w-100 mb-3" id="submitBtn">
                    <i class="fas fa-save me-2"></i>Reset Password
                </button>
                
                <div class="text-center">
                    <a href="/login" class="text-decoration-none">
                        <i class="fas fa-arrow-left me-1"></i>Back to Login
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const resetForm = document.getElementById('resetForm');
            const passwordField = document.getElementById('password');
            const confirmPasswordField = document.getElementById('confirmPassword');
            const passwordError = document.getElementById('passwordError');
            const submitBtn = document.getElementById('submitBtn');
            const otpInputs = document.querySelectorAll('.otp-inputs input');
            const otpHidden = document.getElementById('otp');
            const resendOtpBtn = document.getElementById('resendOtp');
            
            let otpTimer;
            let seconds = 300; // 5 minutes
            
            // Start OTP timer
            startOtpTimer();
            
            // Function to move to next OTP input
            window.moveToNext = function(input, nextIndex) {
                if (input.value.length === 1) {
                    if (nextIndex <= 6) {
                        const nextInput = document.querySelector(`.otp-inputs input:nth-child(${nextIndex})`);
                        if (nextInput) nextInput.focus();
                    }
                    updateOtpValue();
                }
            };
            
            // Update hidden OTP value
            function updateOtpValue() {
                let otp = '';
                otpInputs.forEach(input => {
                    otp += input.value;
                });
                otpHidden.value = otp;
            }
            
            // Start OTP timer
            function startOtpTimer() {
                clearInterval(otpTimer);
                seconds = 300;
                
                otpTimer = setInterval(function() {
                    seconds--;
                    const minutes = Math.floor(seconds / 60);
                    const secs = seconds % 60;
                    document.getElementById('countdown').textContent = 
                        `${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
                    
                    if (seconds <= 0) {
                        clearInterval(otpTimer);
                        document.getElementById('otpSection').classList.add('text-muted');
                        document.querySelectorAll('.otp-inputs input').forEach(input => {
                            input.disabled = true;
                        });
                    }
                }, 1000);
            }
            
            // Resend OTP
            resendOtpBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const email = document.getElementById('email').value;
                
                if (!email) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Email is required'
                    });
                    return;
                }
                
                Swal.fire({
                    title: 'Resending OTP...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Simulate OTP resend
                setTimeout(() => {
                    Swal.close();
                    startOtpTimer();
                    document.getElementById('otpSection').classList.remove('text-muted');
                    document.querySelectorAll('.otp-inputs input').forEach(input => {
                        input.disabled = false;
                        input.value = '';
                    });
                    otpHidden.value = '';
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'OTP Resent!',
                        text: 'New OTP sent to your email',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }, 1000);
            });
            
            // Validate password on keyup
            [passwordField, confirmPasswordField].forEach(field => {
                field.addEventListener('keyup', validatePasswords);
            });
            
            function validatePasswords() {
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                
                // Password strength validation
                const hasMinLength = password.length >= 8;
                const hasUpperCase = /[A-Z]/.test(password);
                const hasLowerCase = /[a-z]/.test(password);
                const hasNumbers = /\d/.test(password);
                
                if (!hasMinLength || !hasUpperCase || !hasLowerCase || !hasNumbers) {
                    passwordError.textContent = 
                        'Password must be at least 8 characters with uppercase, lowercase, and number';
                    passwordError.classList.add('d-block');
                    submitBtn.disabled = true;
                    return;
                }
                
                if (password !== confirmPassword) {
                    passwordError.textContent = 'Passwords do not match';
                    passwordError.classList.add('d-block');
                    submitBtn.disabled = true;
                } else {
                    passwordError.textContent = '';
                    passwordError.classList.remove('d-block');
                    submitBtn.disabled = false;
                }
            }
            
            // Form submission
            resetForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const email = document.getElementById('email').value;
                const password = passwordField.value;
                const confirmPassword = confirmPasswordField.value;
                const otp = otpHidden.value;
                
                if (!email) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Email is required'
                    });
                    return;
                }
                
                if (password !== confirmPassword) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Passwords do not match'
                    });
                    return;
                }
                
                // For demo, we'll accept any OTP or none
                // In production, validate OTP here
                
                Swal.fire({
                    title: 'Resetting password...',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                try {
                    const response = await fetch('/api/auth?action=reset-password', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            email: email,
                            password: password,
                            token: otp // Using OTP as token for demo
                        })
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            html: `
                                <p>Your password has been reset successfully!</p>
                                <p>You can now login with your new password.</p>
                            `,
                            confirmButtonText: 'Go to Login'
                        }).then(() => {
                            window.location.href = '/login';
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: result.message || 'Failed to reset password'
                        });
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Network error. Please try again.'
                    });
                }
            });
        });
    </script>
</body>
</html>