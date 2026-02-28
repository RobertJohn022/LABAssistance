<?php
session_start();
require 'db_conn.php'; // Uses central connection

if (!isset($_SESSION['user_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // --- A. RETRIEVE INPUTS ---
    $customer_id = $_SESSION['user_id'];
    $customer_name = $_SESSION['full_name'];
    $note = isset($_POST['note']) ? trim($_POST['note']) : "";

    // Services
    $services = [];
    if (isset($_POST['checkWash'])) $services[] = "Wash";
    if (isset($_POST['checkDry'])) $services[] = "Dry";
    if (isset($_POST['checkFold'])) $services[] = "Fold";
    $services_str = implode(", ", $services);

    // Supplies
    $supplies = [];
    if (isset($_POST['supplyDetergent'])) $supplies[] = "Detergent";
    if (isset($_POST['supplySoftener'])) $supplies[] = "Fabric Softener";
    $supplies_str = implode(", ", $supplies);

    // Quantities
    $qtyColored = isset($_POST['qtyColored']) ? intval($_POST['qtyColored']) : 0;
    $qtyWhite = isset($_POST['qtyWhite']) ? intval($_POST['qtyWhite']) : 0;
    $qtyFold = isset($_POST['qtyFold']) ? intval($_POST['qtyFold']) : 0;

    // --- B. CALCULATIONS ---
    $isWash = in_array("Wash", $services);
    $isDry = in_array("Dry", $services);
    $isFold = in_array("Fold", $services);
    $isFoldOnly = ($isFold && !$isWash && !$isDry);

    $totalLoads = $isFoldOnly ? $qtyFold : ($qtyColored + $qtyWhite);

    if ($totalLoads <= 0) {
        echo "<script>alert('Invalid load quantity.'); window.history.back();</script>";
        exit();
    }

    // Cost Calculation
    $costPerLoad = 0;
    if ($isWash) $costPerLoad += 50;
    if ($isDry) $costPerLoad += 60;
    if ($isFold) $costPerLoad += 35;
    if ($isWash) {
        if (in_array("Detergent", $supplies)) $costPerLoad += 20;
        if (in_array("Fabric Softener", $supplies)) $costPerLoad += 10;
    }

    $estimated_price = $totalLoads * $costPerLoad;
    $final_price = $estimated_price;
    $bag_counts = $isFoldOnly ? "Fold Only: $qtyFold" : "Colored: $qtyColored, White: $qtyWhite";

    // --- C. GENERATE ORDER ID ---
    $datePart = date("mdY");
    $query = "SELECT COUNT(*) as count FROM `Order` WHERE order_id LIKE '$datePart%'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    $sequencePart = str_pad($count, 3, "0", STR_PAD_LEFT);
    $order_id = $datePart . $sequencePart;

    // --- D. GENERATE TRACKING CODE ---
    $prefix = 5;
    if ($isWash && $isDry && $isFold) $prefix = 1;
    elseif ($isWash && $isDry && !$isFold) $prefix = 2;
    elseif ($isFoldOnly) $prefix = 3;
    elseif (!$isWash && $isDry && $isFold) $prefix = 4;

    $suffix = rand(100, 999);
    $tracking_code = intval($prefix . $suffix);

    // --- E. INSERT MAIN ORDER ---
    // Note: Main Order status acts as a summary. It starts at 'Pending Dropoff'
    $stmt = $conn->prepare("INSERT INTO `Order` 
        (order_id, customer_id, customer_name, tracking_code, services_requested, supplies_requested, bag_counts, customer_note, estimated_price, final_price) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sissssssdd", $order_id, $customer_id, $customer_name, $tracking_code, $services_str, $supplies_str, $bag_counts, $note, $estimated_price, $final_price);

    if ($stmt->execute()) {
        $stmt->close();

        // TIMER WORK HERE:
        // --- F. INSERT INDIVIDUAL LOADS (PROCESS_LOAD) ---
        // Each bag gets the specific granular status 'Pending Dropoff'
        $loadInsert = $conn->prepare("INSERT INTO `Process_Load` (order_id, load_category, bag_label, status) VALUES (?, ?, ?, 'Pending Dropoff')");

        if ($isFoldOnly) {
            for ($i = 1; $i <= $qtyFold; $i++) {
                $category = "Fold Only";
                $label = "Fold Only #$i";
                $loadInsert->bind_param("sss", $order_id, $category, $label);
                $loadInsert->execute();
            }
        } else {
            for ($i = 1; $i <= $qtyColored; $i++) {
                $category = "Colored";
                $label = "Colored #$i";
                $loadInsert->bind_param("sss", $order_id, $category, $label);
                $loadInsert->execute();
            }
            for ($i = 1; $i <= $qtyWhite; $i++) {
                $category = "White";
                $label = "White #$i";
                $loadInsert->bind_param("sss", $order_id, $category, $label);
                $loadInsert->execute();
            }
        }
        $loadInsert->close();

        echo "<script>
                alert('Order Placed! ID: $order_id. Tracking: $tracking_code'); 
                window.location.href='../dashboard.php';
              </script>";
    } else {
        echo "Error: " . $stmt->error;
    }
}
$conn->close();
