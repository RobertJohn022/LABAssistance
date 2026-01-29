<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laundry_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['signin'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role, full_name FROM `User` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['full_name'] = $row['full_name'];
            $_SESSION['email'] = $email;

            // SUCCESS: Go up one level to root to find dashboard.php
            header("Location: ../dashboard.php");
            exit();
        } else {
            // ERROR: Go up one level to root to find login.php
            echo "<script>
                    alert('Invalid Password!'); 
                    window.location.href='../login.php';
                  </script>";
        }
    } else {
        // ERROR: Go up one level to root to find register.php
        echo "<script>
                alert('Email not found! Please register first.'); 
                window.location.href='../register.php';
              </script>";
    }
    $stmt->close();
}
$conn->close();
