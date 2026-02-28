<?php
require 'db_conn.php';
header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
$load_id = intval($_GET['load_id'] ?? 0);

$durations = [
    'Pending Dropoff'  => 0,
    'In Queue'         => 0,
    'Washing'          => 10,
    'Wash Complete'    => 0,
    'Drying'           => 20,
    'Drying Complete'  => 0,
    'Folding'          => 5,
    'Folding Complete' => 0,
    'Awaiting Pickup'  => 0,
    'Completed'        => 0
];

if ($action == 'reset') {
    // Get current status
    $res = $conn->query("SELECT status FROM process_load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    $current_status = $row['status'] ?? 'In Queue';

    // Get duration from array
    $new_seconds = isset($durations[$current_status]) ? $durations[$current_status] : 60;

    // Update database
    $conn->query("UPDATE process_load SET timer_paused = $new_seconds, end_time = NULL WHERE load_id = $load_id");
} elseif ($action == 'pause') {
    $res = $conn->query("SELECT TIMESTAMPDIFF(SECOND, NOW(), end_time) as rem FROM Process_Load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    $rem = max(0, intval($row['rem']));
    $conn->query("UPDATE Process_Load SET timer_paused = $rem, end_time = NULL WHERE load_id = $load_id");
} elseif ($action == 'resume') {
    $res = $conn->query("SELECT timer_paused FROM Process_Load WHERE load_id = $load_id");
    $row = $res->fetch_assoc();
    // $paused_sec = intval($row['timer_paused']);
    $paused_sec = intval($row['timer_paused'] ?? 60);
    $conn->query("UPDATE Process_Load SET end_time = DATE_ADD(NOW(), INTERVAL $paused_sec SECOND), timer_paused = NULL WHERE load_id = $load_id");
}

// FETCH CURRENT STATE
$query = "SELECT timer_paused, end_time, TIMESTAMPDIFF(SECOND, NOW(), end_time) AS live_rem 
          FROM Process_Load WHERE load_id = $load_id";
$res = $conn->query($query);
$row = $res->fetch_assoc();

$remaining = 0;
$is_paused = true;

if ($row) {
    // If the record is brand new (both columns NULL), initialize it to 120
    if ($row['timer_paused'] === null && $row['end_time'] === null) {
        $conn->query("UPDATE Process_Load SET timer_paused = 64 WHERE load_id = $load_id");
        $remaining = 64;
        $is_paused = true;
    } else {
        $is_paused = ($row['timer_paused'] !== null);
        $remaining = $is_paused ? intval($row['timer_paused']) : intval($row['live_rem']);
    }
}

echo json_encode([
    'remaining' => max(0, $remaining),
    'is_paused' => $is_paused
]);
