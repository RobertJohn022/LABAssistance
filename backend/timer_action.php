<?php
require 'db_conn.php';
header('Content-Type: application/json');

// Force PHP to use the same timezone as your location (Optional but helpful)
// date_default_timezone_set('Asia/Manila'); 

$action = $_GET['action'] ?? '';
$load_id = intval($_GET['load_id'] ?? 0);
$remaining = 0;

if ($action == 'reset') {
    // We update the end time to be 60 seconds from the database's current time
    $conn->query("UPDATE Process_Load SET timer_end = DATE_ADD(NOW(), INTERVAL 60 SECOND) WHERE load_id = $load_id");
} elseif ($action == 'finish') {
    $conn->query("UPDATE Process_Load SET timer_end = NOW() WHERE load_id = $load_id");
}

// THE FIX: Calculate the difference entirely inside the SQL query
// This compares the DB's 'timer_end' against the DB's 'NOW()'
$query = "SELECT TIMESTAMPDIFF(SECOND, NOW(), timer_end) AS remaining_seconds 
          FROM Process_Load 
          WHERE load_id = $load_id";

$res = $conn->query($query);
if ($row = $res->fetch_assoc()) {
    $remaining = intval($row['remaining_seconds']);
}

// Ensure we don't return a negative number
echo json_encode(['remaining' => max(0, $remaining)]);
