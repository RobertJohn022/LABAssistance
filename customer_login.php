<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="d-flex align-items-center justify-content-center min-h-100dvh">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-6 col-lg-4">

                <div class="text-center mb-4">
                    <img src="assets/labaratory_logo.png" alt="LABAssistance Logo" class="img-fluid mb-3" style="max-width: 110px;">
                    <h1 class="fw-bold display-6"><span>LAB<span class="text-primary text-blue">Assistance</span></span></h1>
                    <p class="text-muted">Laundry Management System</p>
                </div>

                <div id="loginSection">
                    <div class="app-card p-4">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Log In</h3>
                        </div>

                        <?php
                        if (isset($_SESSION['login_error'])) {
                            echo '<div class="alert alert-danger text-center small py-2 mb-3" role="alert">' . $_SESSION['login_error'] . '</div>';
                            unset($_SESSION['login_error']);
                        }
                        ?>

                        <form action="backend/login_process.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                                <input type="email" class="form-control" name="email" placeholder="Email" value="<?php echo isset($_COOKIE['customer_email']) ? $_COOKIE['customer_email'] : ''; ?>" required>
                            </div>

                            <div class="mb-2">
                                <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="login_pwd" placeholder="Password" value="<?php echo isset($_COOKIE['customer_password']) ? $_COOKIE['customer_password'] : ''; ?>" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('login_pwd', 'eyeLogin')">
                                        <i class="bi bi-eye" id="eyeLogin"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" <?php echo isset($_COOKIE['customer_email']) ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-muted small" for="remember">
                                        Remember me
                                    </label>
                                </div>
                                <button type="button" class="btn btn-link text-muted small p-0 text-decoration-none" onclick="toggleRecover()">Forgot Password?</button>
                            </div>

                            <button type="submit" name="signin" class="btn-primary-app w-100">Sign In</button>
                        </form>
                    </div>

                    <div class="text-center mt-4">
                        <span class="text-muted">Don't have an account?</span>
                        <a href="register.php" class="text-dark fw-bold text-decoration-none">Register</a>
                    </div>
                </div>

                <div id="passRecovery" style="display: none;">
                    <div class="app-card p-4">
                        <div class="text-center mb-4">
                            <h3 class="fw-bold">Reset Password</h3>
                            <p class="text-muted small">Enter your email address. We'll send you a link to reset your password.</p>
                        </div>

                        <form action="backend/send_reset_link.php" method="POST">
                            <div class="mb-4">
                                <label class="form-label text-muted small fw-bold text-uppercase">Email Address</label>
                                <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                            </div>

                            <button type="submit" name="send_link" class="btn-primary-app w-100">Send Reset Link</button>
                        </form>

                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-link text-muted small p-0 text-decoration-none" onclick="toggleRecover()">Back to Login</button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function toggleRecover() {
            var loginDiv = document.getElementById("loginSection");
            var recoverDiv = document.getElementById("passRecovery");
            if (loginDiv.style.display === "none") {
                loginDiv.style.display = "block";
                recoverDiv.style.display = "none";
            } else {
                loginDiv.style.display = "none";
                recoverDiv.style.display = "block";
            }
        }

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("bi-eye", "bi-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("bi-eye-slash", "bi-eye");
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'link_sent') {
                Swal.fire({
                    title: 'Check your Inbox',
                    text: 'If an account exists for that email, we have sent a password reset link.',
                    icon: 'success'
                });
            } else if (status === 'password_updated') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Your password has been reset. You can now login.',
                    icon: 'success'
                });
            } else if (status === 'invalid_token') {
                Swal.fire({
                    title: 'Error',
                    text: 'The reset link is invalid or has expired.',
                    icon: 'error'
                });
            } else if (status === 'error') {
                Swal.fire({
                    title: 'Error',
                    text: 'Something went wrong. Please try again.',
                    icon: 'error'
                });
            }
        });
    </script>
</body>

</html>