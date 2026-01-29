<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
     <link rel="stylesheet" href="./css/design.css">   
</head>

<body>
    <!-- HEADER -->

    <!-- MAIN -->
    <!-- REGISTER -->
    <div id = "registerform" class="d-flex justify-content-center">
        <div class="w-75" id="register">
            <center><div class="row border border-white">
                <h1><b>LABAssistance</b></h1>
                <label>Laundry Management System</label>
                <h3><b>Register</b></h3>
            </div></center>

            <div class="row border border-white">
                <div class="row border border-white">
                    <label class="mt-3">Full Name</label>
                    <input type="text" id="name" name="full_name" placeholder="Full Name">
                </div>

                <div class="row border border-white">
                    <label class="mt-3">Email</label>
                    <input type="text" id="email" name="username" placeholder="Email">
                </div>

                <div class="row border border-white">
                    <label class="mt-3">Password</label>
                    <input type="password" id="pwd" name="email" placeholder="Password">
                </div>

                <div class="row border border-white">
                    <label class="mt-3">Confirm Password</label>
                    <input type="password" id="confpwd" name="password" placeholder="Confirm Password">
                </div>

                <!-- <div class = "agreecont">
                    <input class = "cbagree" type="checkbox" name="terms" value="terms">
                    <label for="vehicle1">I Agree to the Terms and Conditions</label><br>
                </div> -->

                <div class="agreecont">
                <label class="smooth-checkbox">
                    <input type="checkbox">
                    <span class="checkmark"></span>
                    I Agree to the Terms and Conditions
                    </label>
                </div>

                <div id = "reg" class="row border border-white">
                    <input type="submit" id="regbutton" class="mt-3 btn btn-dark w-50" name="register" value="Register">
                </div>
                <div id = "ihave" class="row border border-white">
                    <button class="mt-3 btn btn-link text-dark p-0 w-100 " onclick="showLogin()">Already have an account? Login</button>
                </div>
            </div>
        </div>


       <!-- LOGIN -->
        <div class="w-75" id="login" style="display: none">
            <div class="row border border-white">
                <center><h1><b>LABAssistance</b></h1>
                <label>Laundry Management System</label>
                <h3><b>Log in</b></h3></center>
            </div>

            <div class="row border border-white">

                <div class="row border border-white">
                    <label class="mt-3">Email</label>
                    <input id = "email" type="text" name="username" placeholder="Email">
                </div>

                <div class="row border border-white">
                    <label class="mt-3">Password</label>
                    <input id="pwd" type="text" name="email" placeholder="Password">
                </div>

                <div id = "forgotpass">
                    <button id="forgotpassbtn" class="mt-3 btn btn-link text-dark p-0  " onclick="showPassRecovery()">Forgot Password?</button>
                </div>

                <div id="login" class="row border border-white">
                    <a class="mt-3 btn btn-dark w-50" href="dashboard.php">
                        <input class=" btn btn-dark" type="submit" name="signin" value="Sign in">
                    </a>
                </div>
            
                <div id="donthaveacc" class="row border border-white">
                    <button class="mt-3 btn btn-link text-dark p-0 w-100" onclick="showRegister()">Don't have an account? Register</button>
                </div>
            </div>
        </div>


        <!-- Password Recovery -->
        <div class="w-75" id="passRecovery" style="display: none">
            <div class="row border border-white">
                <label>Reset Password</label>
            </div>

            <div class="row border border-white">
                <div class="row border border-white">
                    <label class="mt-3">Email</label>
                    <input type="text" name="username">
                </div>

                <div class="row border border-white">
                    <button class="mt-3 btn btn-dark w-50">Send Recovery Link</button>
                </div>
                <div class="row border border-white">
                    <button class="mt-3 btn btn-dark w-50" onclick="showLogin()">Back to Login</button>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="js/scripts.js"></script>
</body>

</html>