<?php
require 'db_conn.php'; // Check if this path is correct!
header('Content-Type: application/json');

$load_id = isset($_POST['load_id']) ? intval($_POST['load_id']) : 0;

$status_cycle = [
    'Pending Dropoff',
    'In Queue',
    'Washing',
    'Wash Complete',
    'Drying',
    'Drying Complete',
    'Folding',
    'Folding Complete',
    'Awaiting Pickup',
    'Completed'
];

if ($load_id > 0) {
    $res = $conn->query("SELECT status FROM Process_Load WHERE load_id = $load_id");
    if ($res && $row = $res->fetch_assoc()) {
        $current_status = $row['status'];
        $current_index = array_search($current_status, $status_cycle);
        $next_index = $current_index + 1;

        if (isset($status_cycle[$next_index])) {
            $next_status = $status_cycle[$next_index];
            $stmt = $conn->prepare("UPDATE Process_Load SET status = ? WHERE load_id = ?");
            $stmt->bind_param("si", $next_status, $load_id);
            $stmt->execute();

            echo json_encode([
                "success" => true,
                "next_status" => $next_status,
                "is_final" => ($next_status == 'Completed')
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "End of cycle"]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "ID not found"]);
    }
} else {
    echo json_encode(["success" => false, "message" => "No ID provided"]);
}
