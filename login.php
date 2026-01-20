<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body>
    <!-- HEADER -->

    <!-- MAIN -->
    <!-- REGISTER -->
    <div class="d-flex flex-column col w-75">
        <div class="row border border-black">
            <label>REGISTER</label>
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
                <label class=" mt-3">Password</label>
                <input type="text" name="email">
            </div>

            <div class="row border border-black">
                <label class=" mt-3">Confirm Password</label>
                <input type="password" name="password">
            </div>

            <div class="row border border-black">
                <input type="submit" class=" mt-3 btn btn-dark" name="register" value="Register">
            </div>
            <div class="row border border-black">
                <input type="submit" class=" mt-3 btn btn-dark" name="toLogin" value="(TO LOG IN)">
            </div>
        </div>
    </div>


    <!-- REGISTER -->
    <div class="d-flex flex-column col w-75 mt-5">
        <div class="row border border-black">
            <label>LOGIN</label>
        </div>

        <div class="row border border-black">

            <div class="row border border-black">
                <label class="mt-3">Email</label>
                <input type="text" name="username">
            </div>

            <div class="row border border-black">
                <label class=" mt-3">Password</label>
                <input type="text" name="email">
            </div>

            <div class="row border border-black">
                <a href="dashboard.php">
                    <input type="submit" class=" mt-3 btn btn-dark" name="signin" value="Sign in">
                </a>
            </div>
            <div class="row border border-black">
                <input type="submit" class=" mt-3 btn btn-dark" name="toRegister" value="(TO REGISTER)">
            </div>
        </div>
    </div>

    <!-- FOOTER -->
</body>

</html>