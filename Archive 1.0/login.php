<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
<<<<<<< Updated upstream
    <title>LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
=======
    <title>Login - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/design.css">
>>>>>>> Stashed changes
</head>

<body>

<<<<<<< Updated upstream
    <!-- MAIN -->
    <!-- REGISTER -->
    <div class="d-flex justify-content-center">
        <div class="w-50" id="register">
            <div class="row border border-black">
                <label>Register</label>
            </div>

            <div class="row border border-black">
                <div class="row border border-black">
                    <label class="mt-3">Full Name</label>
                    <input type="text" name="full_name">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Email</label>
                    <input type="text" name="username">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Password</label>
                    <input type="password" name="email">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Confirm Password</label>
                    <input type="password" name="password">
                </div>

                <div class="row border border-black">
                    <input type="submit" class="mt-3 btn btn-dark w-50" name="register" value="Register">
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showLogin()">Already have an account? Login</button>
                </div>
            </div>
        </div>


        <!-- LOGIN -->
        <div class="w-50" id="login" style="display: none">
            <div class="row border border-black">
                <label>Log in</label>
            </div>

            <div class="row border border-black">

                <div class="row border border-black">
                    <label class="mt-3">Email</label>
                    <input type="text" name="username">
                </div>

                <div class="row border border-black">
                    <label class="mt-3">Password</label>
                    <input type="text" name="email">
                </div>

                <div class="row border border-black">
                    <a class="mt-3 btn btn-dark w-50" href="dashboard.php">
                        <input class=" btn btn-dark" type="submit" name="signin" value="Sign in">
                    </a>
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showPassRecovery()">Forgot Password?</button>
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showRegister()">Don't have an account? Register</button>
                </div>
            </div>
        </div>

        <!-- Password Recovery -->
        <div class="w-50" id="passRecovery" style="display: none">
            <div class="row border border-black">
                <label>Reset Password</label>
            </div>

            <div class="row border border-black">
                <div class="row border border-black">
                    <label class="mt-3">Email</label>
                    <input type="text" name="username">
                </div>

                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50">Send Recovery Link</button>
                </div>
                <div class="row border border-black">
                    <button class="mt-3 btn btn-dark w-50" onclick="showLogin()">Back to Login</button>
                </div>
            </div>
=======
    <div id="loginform" class="d-flex justify-content-center align-items-center" style="min-height: 100vh;">
        <div class="w-75" id="login">
            <div class="text-center mb-4">
                <h1><b>LABAssistance</b></h1>
                <label>Laundry Management System</label>
                <h3 class="mt-2"><b>Log in</b></h3>
            </div>

            <form action="backend/login_process.php" method="POST">

                <div class="row">
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control" id="pwd" name="password" placeholder="Password" required>
                    </div>

                    <div class="mb-3 text-end">
                        <button type="button" class="btn btn-link text-dark p-0 text-decoration-none" onclick="Swal.fire('Please contact your administrator to reset your password.')">Forgot Password?</button>
                    </div>

                    <div class="d-grid gap-2">
                        <input type="submit" class="btn btn-dark" name="signin" value="Sign in">
                    </div>

                    <div class="text-center mt-3">
                        <a href="register.php" class="btn btn-link text-dark text-decoration-none">Don't have an account? Register</a>
                    </div>
                </div>
            </form>
>>>>>>> Stashed changes
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>
</body>

</html>