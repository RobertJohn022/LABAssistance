<?php
session_start();
require 'db_conn.php';

// Kick out if not an employee or manager
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: ../staff_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    $final_price = $_POST['final_price'] ?? 0;

    if (!empty($order_id)) {
        // Construct the employee name from the new separated session variables
        $employee_name = isset($_SESSION['first_name']) ? trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']) : 'Staff';

        // Update the final_price column in the Order table
        $stmt = $conn->prepare("UPDATE `Order` SET final_price = ? WHERE order_id = ?");
        $stmt->bind_param("ds", $final_price, $order_id);

        if ($stmt->execute()) {
            // Log this change in Order_Logs
            $log_msg = "Order price updated to ₱" . number_format($final_price, 2) . " by " . $employee_name;
            $log_stmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
            $log_stmt->bind_param("ss", $order_id, $log_msg);
            $log_stmt->execute();
        }

        $stmt->close();
    }
}

// Redirect back to the dashboard
header("Location: ../employee_dashboard.php");
exit();
