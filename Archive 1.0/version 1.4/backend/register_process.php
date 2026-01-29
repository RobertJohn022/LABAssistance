<?php
// 1. Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laundry_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['register'])) {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validations: Redirects need "../" to go back to root
    if ($password !== $confirm_password) {
        echo "<script>alert('Passwords do not match!'); window.location.href='../register.php';</script>";
        exit();
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $role = 'Customer';

    $checkEmail = $conn->prepare("SELECT email FROM `User` WHERE email = ?");
    $checkEmail->bind_param("s", $email);
    $checkEmail->execute();
    $result = $checkEmail->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already exists!'); window.location.href='../register.php';</script>";
    } else {
        $sql = "INSERT INTO `User` (email, password, role, full_name, created_at) VALUES (?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $email, $hashed_password, $role, $full_name);

        if ($stmt->execute()) {
            // Success: Redirect back up to login.php
            echo "<script>
                    alert('Registration Successful! Please login.');
                    window.location.href='../login.php';
                  </script>";
        } else {
            echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    $checkEmail->close();
}
$conn->close();
