<?php
session_start();
require 'db_conn.php';

header('Content-Type: application/json');

// Check if required data and session variables are present
if (!isset($_SESSION['user_id']) || !isset($_POST['order_id']) || !isset($_POST['message'])) {
    echo json_encode(['success' => false, 'error' => 'Missing data or session expired']);
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$order_id = $_POST['order_id'];
$message_text = trim($_POST['message']);

if (empty($message_text)) {
    echo json_encode(['success' => false, 'error' => 'Message is empty']);
    exit();
}

// --- PROFANITY FILTER ---
// Load the array of restricted words from the external dictionary file
$dictionary_path = __DIR__ . '/bad_words.php';

if (file_exists($dictionary_path)) {
    $bad_words = require $dictionary_path;

    if (is_array($bad_words)) {
        foreach ($bad_words as $word) {
            // \b ensures we only match whole words
            // 'i' modifier makes it case-insensitive
            $pattern = '/\b' . preg_quote($word, '/') . '\b/i';

            // Replace matched words with asterisks of the exact same length
            $message_text = preg_replace_callback($pattern, function ($matches) {
                return str_repeat('*', strlen($matches[0]));
            }, $message_text);
        }
    }
} else {
    // Optional: Log an error if the dictionary file is missing, but allow message to send
    error_log("Profanity dictionary not found at: " . $dictionary_path);
}
// ------------------------

// Security Check: If the user is a customer, ensure they actually own the order
if ($role === 'Customer') {
    $checkQuery = $conn->prepare("SELECT customer_id FROM `order` WHERE order_id = ?");
    $checkQuery->bind_param("s", $order_id);
    $checkQuery->execute();
    $result = $checkQuery->get_result();
    $order = $result->fetch_assoc();

    if (!$order || $order['customer_id'] != $user_id) {
        echo json_encode(['success' => false, 'error' => 'Unauthorized']);
        exit();
    }
    $checkQuery->close();
}

// Insert the sanitized message into the database
$stmt = $conn->prepare("INSERT INTO `order_messages` (order_id, sender_id, message_text) VALUES (?, ?, ?)");
$stmt->bind_param("sis", $order_id, $user_id, $message_text);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    error_log("Failed to insert message: " . $stmt->error);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}

$stmt->close();
$conn->close();
