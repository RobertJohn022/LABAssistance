<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
</head>

<body>

    <div id="registerform" class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="w-75" id="register">
            <div class="text-center mb-4">
                <h1><b>LABAssistance</b></h1>
                <label>Laundry Management System</label>
                <h3 class="mt-2"><b>Register</b></h3>
            </div>

            <form action="backend/register_process.php" method="POST">

                <div class="row">
                    <div class="mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name" name="full_name" placeholder="Full Name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="pwd" name="password" placeholder="Password" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confpwd" name="confirm_password" placeholder="Confirm Password" required>
                    </div>

                    <div class="mb-3 agreecont">
                        <label class="smooth-checkbox d-flex align-items-center">
                            <input type="checkbox" name="terms" required>
                            <span class="checkmark me-2"></span>
                            I Agree to the Terms and Conditions
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <input type="submit" id="regbutton" class="btn btn-dark" name="register" value="Register">
                    </div>

                    <div class="text-center mt-3">
                        <a href="login.php" class="btn btn-link text-dark text-decoration-none">Already have an account? Login</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>
</body>

</html>