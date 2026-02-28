<?php
require 'db_conn.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$load_id = intval($_GET['load_id'] ?? 0);
$remaining = 0;

if ($action == 'reset') {
    // Reset: Clear pause state and set new 60s timer
    $conn->query("UPDATE Process_Load SET end_time = DATE_ADD(NOW(), INTERVAL 60 SECOND), timer_paused_seconds = NULL WHERE load_id = $load_id");
} elseif ($action == 'pause') {
    // Pause: Calculate current remaining time and save it into the new column
    $res = $conn->query("SELECT TIMESTAMPDIFF(SECOND, NOW(), end_time) as rem FROM Process_Load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    $rem = max(0, intval($row['rem']));
    $conn->query("UPDATE Process_Load SET timer_paused_seconds = $rem, end_time = NULL WHERE load_id = $load_id");
} elseif ($action == 'resume') {
    // Resume: Take the paused seconds and create a new end_time starting from NOW
    $res = $conn->query("SELECT timer_paused_seconds FROM Process_Load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    $paused_sec = intval($row['timer_paused_seconds']);
    $conn->query("UPDATE Process_Load SET end_time = DATE_ADD(NOW(), INTERVAL $paused_sec SECOND), timer_paused_seconds = NULL WHERE load_id = $load_id");
}

// GET LOGIC: If timer_paused_seconds has a value, return that. Otherwise, calculate diff.
$query = "SELECT timer_paused_seconds, TIMESTAMPDIFF(SECOND, NOW(), end_time) AS live_rem 
          FROM Process_Load WHERE load_id = $load_id";
$res = $conn->query($query);
if ($row = $res->fetch_assoc()) {
    $remaining = ($row['timer_paused_seconds'] !== null) ? $row['timer_paused_seconds'] : $row['live_rem'];
}

echo json_encode(['remaining' => max(0, intval($remaining)), 'is_paused' => ($row['timer_paused_seconds'] !== null)]);
