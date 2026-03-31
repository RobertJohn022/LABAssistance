<?php
session_start();
require 'db_conn.php';

// Kick out if not an employee or manager
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: ../staff_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    $employee_name = (isset($_SESSION['first_name']) && isset($_SESSION['last_name']))
        ? $_SESSION['first_name'] . ' ' . $_SESSION['last_name']
        : 'Staff';

    // Add Bag
    if ($action === 'add_bag') {
        $order_id = $_POST['order_id'] ?? '';
        $bag_qty = intval($_POST['bag_quantity'] ?? 1);
        $bag_category = $_POST['bag_category'] ?? 'Colored'; // Captures the dropdown value

        if (!empty($order_id) && $bag_qty > 0) {
            $orderQuery = $conn->query("SELECT services_requested, supplies_requested, final_price FROM `Order` WHERE order_id = '$order_id'");
            if ($orderRow = $orderQuery->fetch_assoc()) {

                $services = $orderRow['services_requested'];
                $supplies = $orderRow['supplies_requested'];
                $current_price = floatval($orderRow['final_price']);

                // Calculate price per load
                $costPerLoad = 0;
                $isWash = stripos($services, 'Wash') !== false;
                if ($isWash) $costPerLoad += 55;
                if (stripos($services, 'Dry') !== false) $costPerLoad += 60;
                if (stripos($services, 'Fold') !== false) $costPerLoad += 30;
                if ($isWash) {
                    if (stripos($supplies, 'Detergent') !== false) $costPerLoad += 20;
                    if (stripos($supplies, 'Fabric Softener') !== false) $costPerLoad += 10;
                }

                $total_added_cost = $costPerLoad * $bag_qty;
                $new_price = $current_price + $total_added_cost;

                $status = "In Queue";
                $bags_added_names = [];

                // Get the current highest bag number for THIS SPECIFIC category to name them sequentially
                $loadCountQuery = $conn->query("SELECT COUNT(*) as c FROM `Process_Load` WHERE order_id = '$order_id' AND load_category = '$bag_category'");
                $loadCountData = $loadCountQuery->fetch_assoc();
                $starting_bag_number = $loadCountData['c'] + 1;

                $stmt = $conn->prepare("INSERT INTO `Process_Load` (order_id, load_category, bag_label, status) VALUES (?, ?, ?, ?)");

                for ($i = 0; $i < $bag_qty; $i++) {
                    // Correct Output format: "White #2 (Extra)"
                    $bag_label = "$bag_category #" . ($starting_bag_number + $i) . " (Extra)";
                    $bags_added_names[] = $bag_label;
                    $stmt->bind_param("ssss", $order_id, $bag_category, $bag_label, $status);
                    $stmt->execute();
                }
                $stmt->close();

                // Recalculate formatted bag_counts string dynamically
                $loadCounts = $conn->query("SELECT load_category, COUNT(*) as count FROM `Process_Load` WHERE order_id = '$order_id' GROUP BY load_category");
                $c_colored = 0;
                $c_white = 0;
                $c_fold = 0;
                $c_other = 0;
                while ($lr = $loadCounts->fetch_assoc()) {
                    if ($lr['load_category'] == 'Colored') $c_colored = $lr['count'];
                    elseif ($lr['load_category'] == 'White') $c_white = $lr['count'];
                    elseif ($lr['load_category'] == 'Fold Only') $c_fold = $lr['count'];
                    else $c_other += $lr['count'];
                }

                $isFoldOnly = (stripos($services, 'Fold') !== false && stripos($services, 'Wash') === false && stripos($services, 'Dry') === false);
                $new_bag_counts_str = $isFoldOnly ? "Fold Only: $c_fold" : "Colored: $c_colored, White: $c_white";
                if ($c_other > 0) $new_bag_counts_str .= ", Extra Types: $c_other";

                // Update Order Status, Price, and Bag Counts String
                $conn->query("UPDATE `Order` SET final_price = $new_price, bag_counts = '$new_bag_counts_str', status = 'In Progress' WHERE order_id = '$order_id'");

                // Insert Log
                $bag_names_str = implode(", ", $bags_added_names);
                $log_msg = "$employee_name added $bag_qty extra bag(s) ($bag_names_str). Price auto-adjusted (+₱$total_added_cost).";
                $log_stmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
                $log_stmt->bind_param("ss", $order_id, $log_msg);
                $log_stmt->execute();
            }
        }
    }

    // Delete Bag
    elseif ($action === 'delete_bag') {
        $load_id = intval($_POST['load_id'] ?? 0);
        $order_id = $_POST['order_id'] ?? '';

        if ($load_id > 0 && !empty($order_id)) {
            $stmt = $conn->prepare("SELECT pl.bag_label, o.services_requested, o.supplies_requested, o.final_price FROM `Process_Load` pl JOIN `Order` o ON pl.order_id = o.order_id WHERE pl.load_id = ?");
            $stmt->bind_param("i", $load_id);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $bag_label = $row['bag_label'];
                $services = $row['services_requested'];
                $supplies = $row['supplies_requested'];
                $current_price = floatval($row['final_price']);

                // Calculate price per load
                $costPerLoad = 0;
                $isWash = stripos($services, 'Wash') !== false;
                if ($isWash) $costPerLoad += 55;
                if (stripos($services, 'Dry') !== false) $costPerLoad += 60;
                if (stripos($services, 'Fold') !== false) $costPerLoad += 30;
                if ($isWash) {
                    if (stripos($supplies, 'Detergent') !== false) $costPerLoad += 20;
                    if (stripos($supplies, 'Fabric Softener') !== false) $costPerLoad += 10;
                }

                $new_price = max(0, $current_price - $costPerLoad);

                // Delete the bag
                $conn->query("DELETE FROM `Process_Load` WHERE load_id = $load_id");

                // Recalculate formatted bag_counts string dynamically
                $loadCounts = $conn->query("SELECT load_category, COUNT(*) as count FROM `Process_Load` WHERE order_id = '$order_id' GROUP BY load_category");
                $c_colored = 0;
                $c_white = 0;
                $c_fold = 0;
                $c_other = 0;
                while ($lr = $loadCounts->fetch_assoc()) {
                    if ($lr['load_category'] == 'Colored') $c_colored = $lr['count'];
                    elseif ($lr['load_category'] == 'White') $c_white = $lr['count'];
                    elseif ($lr['load_category'] == 'Fold Only') $c_fold = $lr['count'];
                    else $c_other += $lr['count'];
                }

                $isFoldOnly = (stripos($services, 'Fold') !== false && stripos($services, 'Wash') === false && stripos($services, 'Dry') === false);
                $new_bag_counts_str = $isFoldOnly ? "Fold Only: $c_fold" : "Colored: $c_colored, White: $c_white";
                if ($c_other > 0) $new_bag_counts_str .= ", Extra Types: $c_other";

                // Re-evaluate order status
                $rem = $conn->query("SELECT COUNT(*) as c FROM `Process_Load` WHERE order_id = '$order_id' AND status != 'Completed'")->fetch_assoc();
                $masterStatus = ($rem['c'] == 0) ? 'Completed' : 'In Progress';

                // Update Order Database
                $conn->query("UPDATE `Order` SET final_price = $new_price, bag_counts = '$new_bag_counts_str', status = '$masterStatus' WHERE order_id = '$order_id'");

                // Log the action
                $log_msg = "$employee_name deleted bag: $bag_label. Price auto-adjusted (-₱$costPerLoad).";
                $log_stmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
                $log_stmt->bind_param("ss", $order_id, $log_msg);
                $log_stmt->execute();
            }
            $stmt->close();
        }
    } elseif ($action === 'cancel_order') {
        $order_id = $_POST['order_id'] ?? '';

        if (!empty($order_id)) {
            $conn->query("UPDATE `Order` SET status = 'Cancelled' WHERE order_id = '$order_id'");
            $conn->query("UPDATE `Process_Load` SET status = 'Cancelled' WHERE order_id = '$order_id'");

            $log_msg = "$employee_name cancelled the order by removing the final bag.";
            $log_stmt = $conn->prepare("INSERT INTO `Order_Logs` (order_id, log_message) VALUES (?, ?)");
            $log_stmt->bind_param("ss", $order_id, $log_msg);
            $log_stmt->execute();
        }
    }
}

// Redirect back
header("Location: ../employee_dashboard.php");
exit();
