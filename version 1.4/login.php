<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
</head>

<body>

    <div id="loginform" class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">

        <div class="col-11 col-md-6 col-lg-5 p-4" id="login">
            <div class="text-center mb-5">
                <h1><b>LABAssistance</b></h1>
                <label class="text-muted">Laundry Management System</label>
                <h3 class="mt-3"><b>Log in</b></h3>
            </div>

            <form action="backend/login_process.php" method="POST">
                <div class="row">
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control p-3" id="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control p-3" id="pwd" name="password" placeholder="Password" required>
                    </div>

                    <div class="mb-4 text-end">
                        <button type="button" class="btn btn-link text-dark p-0 text-decoration-none" onclick="toggleRecover()">Forgot Password?</button>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <input type="submit" class="btn btn-dark p-3" name="signin" value="Sign in">
                    </div>

                    <div class="text-center">
                        <a href="register.php" class="btn btn-link text-dark text-decoration-none">Don't have an account? Register</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-11 col-md-6 col-lg-5 p-4" id="passRecovery" style="display: none;">
            <div class="text-center mb-5">
                <h1><b>LABAssistance</b></h1>
                <label class="text-muted">Laundry Management System</label>
                <h3 class="mt-3"><b>Reset Password</b></h3>
            </div>

            <form action="backend/recovery_process.php" method="POST">
                <div class="row">
                    <div class="mb-4">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control p-3" id="rec_email" name="email" placeholder="Enter your email" required>
                    </div>

                    <div class="d-grid gap-2 mb-4">
                        <button type="submit" class="btn btn-dark p-3">Send Recovery Link</button>
                    </div>

                    <div class="text-center">
                        <button type="button" class="btn btn-link text-dark p-0 text-decoration-none" onclick="toggleRecover()">Back to Login</button>
                    </div>
                </div>
            </form>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>

    <script>
        function toggleRecover() {
            var loginDiv = document.getElementById("login");
            var recoverDiv = document.getElementById("passRecovery");

            if (loginDiv.style.display === "none") {
                loginDiv.style.display = "block";
                recoverDiv.style.display = "none";
            } else {
                loginDiv.style.display = "none";
                recoverDiv.style.display = "block";
            }
        }
    </script>
</body>

</html>