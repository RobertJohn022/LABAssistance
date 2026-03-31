<?php
session_start();
require 'db_conn.php';

// Ensure user is authorized (Manager role check)
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'Manager') {
    header("Location: ../staff_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("UPDATE service_prices SET price = ? WHERE service_name = ?");

    $updates = [
        'Wash' => $_POST['price_wash'],
        'Dry' => $_POST['price_dry'],
        'Fold' => $_POST['price_fold'],
        'Detergent' => $_POST['price_detergent'],
        'Softener' => $_POST['price_softener']
    ];

    foreach ($updates as $service_name => $price) {
        $price_val = floatval($price);
        $stmt->bind_param("ds", $price_val, $service_name);
        $stmt->execute();
    }

    $stmt->close();

    // Redirect back with success message
    $_SESSION['settings_success'] = "Prices updated successfully.";
    header("Location: ../manager_dashboard.php");
    exit();
}
$conn->close();
