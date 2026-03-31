<?php
session_start();
require 'db_conn.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../customer_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Fetch dynamic prices from the database
    $prices = [];
    $priceQuery = $conn->query("SELECT * FROM service_prices");
    if ($priceQuery) {
        while ($row = $priceQuery->fetch_assoc()) {
            $prices[$row['service_name']] = $row['price'];
        }
    } else {
        // Fallback or error handling if table doesn't exist yet
        die("Error loading service prices. Please ensure the service_prices table is set up.");
    }

    // Get form data
    $customer_id = $_SESSION['user_id'];
    $customer_name = trim($_SESSION['first_name'] . ' ' . $_SESSION['last_name']);

    $note = isset($_POST['note']) ? trim($_POST['note']) : "";

    // Service types
    $services = [];
    if (isset($_POST['checkWash'])) $services[] = "Wash";
    if (isset($_POST['checkDry'])) $services[] = "Dry";
    if (isset($_POST['checkFold'])) $services[] = "Fold";
    $services_str = implode(", ", $services);

    // Add-ons
    $supplies = [];
    if (isset($_POST['supplyDetergent'])) $supplies[] = "Detergent";
    if (isset($_POST['supplySoftener'])) $supplies[] = "Softener"; // Adjusted to match DB value 'Softener'
    $supplies_str = implode(", ", $supplies);

    // Load counts
    $qtyColored = isset($_POST['qtyColored']) ? intval($_POST['qtyColored']) : 0;
    $qtyWhite = isset($_POST['qtyWhite']) ? intval($_POST['qtyWhite']) : 0;
    $qtyFold = isset($_POST['qtyFold']) ? intval($_POST['qtyFold']) : 0;

    // Calculate costs
    $isWash = in_array("Wash", $services);
    $isDry = in_array("Dry", $services);
    $isFold = in_array("Fold", $services);
    $isFoldOnly = ($isFold && !$isWash && !$isDry);

    $totalLoads = $isFoldOnly ? $qtyFold : ($qtyColored + $qtyWhite);

    if ($totalLoads <= 0) {
        echo "<script>alert('Invalid load quantity.'); window.history.back();</script>";
        exit();
    }

    // Dynamic Pricing logic
    $costPerLoad = 0;
    if ($isWash) $costPerLoad += $prices['Wash'];
    if ($isDry) $costPerLoad += $prices['Dry'];
    if ($isFold) $costPerLoad += $prices['Fold'];

    if ($isWash) {
        if (in_array("Detergent", $supplies)) $costPerLoad += $prices['Detergent'];
        if (in_array("Softener", $supplies)) $costPerLoad += $prices['Softener'];
    }

    $estimated_price = $totalLoads * $costPerLoad;
    $final_price = $estimated_price;
    $bag_counts = $isFoldOnly ? "Fold Only: $qtyFold" : "Colored: $qtyColored, White: $qtyWhite";

    // Create Order ID
    $datePart = date("mdY");
    $query = "SELECT COUNT(*) as count FROM `order` WHERE order_id LIKE '$datePart%'";
    $result = $conn->query($query);
    $row = $result->fetch_assoc();
    $count = $row['count'] + 1;
    $sequencePart = str_pad($count, 3, "0", STR_PAD_LEFT);
    $order_id = $datePart . $sequencePart;

    // Create tracking code
    $prefix = 5;
    if ($isWash && $isDry && $isFold) $prefix = 1;
    elseif ($isWash && $isDry && !$isFold) $prefix = 2;
    elseif ($isFoldOnly) $prefix = 3;
    elseif (!$isWash && $isDry && $isFold) $prefix = 4;

    $suffix = rand(100, 999);
    $tracking_code = intval($prefix . $suffix);

    // Save order
    // Set initial status
    $stmt = $conn->prepare("INSERT INTO `order` 
        (order_id, customer_id, customer_name, tracking_code, services_requested, supplies_requested, bag_counts, customer_note, estimated_price, final_price) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $stmt->bind_param("sissssssdd", $order_id, $customer_id, $customer_name, $tracking_code, $services_str, $supplies_str, $bag_counts, $note, $estimated_price, $final_price);

    if ($stmt->execute()) {
        $stmt->close();

        // Save bag details
        // Init bag status
        $loadInsert = $conn->prepare("INSERT INTO `process_load` (order_id, load_category, bag_label) VALUES (?, ?, ?)");

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

        // Store success info in session to be picked up by SweetAlert or UI
        $_SESSION['order_success'] = true;
        $_SESSION['new_order_id'] = $order_id;
        $_SESSION['new_tracking'] = $tracking_code;

        // Redirect cleanly
        header("Location: ../dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
}
$conn->close();
