<?php
session_start();
require 'db_conn.php';

if (isset($_POST['signin'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, password, role, first_name, last_name FROM `User` WHERE email = ? AND role = 'Customer'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];

            // Remember Me Implementation
            if (isset($_POST['remember'])) {
                // Set cookies for 30 days
                setcookie("customer_email", $email, time() + (30 * 24 * 60 * 60), "/");
                setcookie("customer_password", $password, time() + (30 * 24 * 60 * 60), "/");
            } else {
                // Destroy existing cookies if checkbox is unchecked
                if (isset($_COOKIE['customer_email'])) {
                    setcookie("customer_email", "", time() - 3600, "/");
                }
                if (isset($_COOKIE['customer_password'])) {
                    setcookie("customer_password", "", time() - 3600, "/");
                }
            }

            header("Location: ../dashboard.php");
            exit();
        }
    }

    $_SESSION['login_error'] = "Invalid email or password.";
    header("Location: ../customer_login.php");
    exit();
}
