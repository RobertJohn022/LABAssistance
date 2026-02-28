<?php
session_start();
require 'backend/db_conn.php';

// Auth Check
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch User's Orders (Newest First)
$orderQuery = "SELECT * FROM `Order` WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/design.css">
    <link rel="stylesheet" href="./css/dashboard.css">
</head>

<body class="bg-light">

    <div class="container d-flex flex-column" style="min-height: 100dvh;">

        <div class="row pt-3 flex-shrink-0">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="fw-bold fs-5">Hello, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</div>
                <a href="customer_login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Log Out</a>
            </div>
        </div>

        <div class="flex-grow-1 d-flex flex-column justify-content-start align-items-center pt-4">

            <div class="text-center mb-4">
                <h1 class="fw-bold display-5">LABAssistance</h1>
                <p class="text-muted fs-6">Laundry Management System</p>
            </div>

            <div class="w-100 text-center px-4 mb-5">
                <a href="order.php" class="btn btn-dark btn-lg w-100 py-3 rounded-3 shadow-sm" style="max-width: 400px;">
                    Place New Order
                </a>
            </div>

            <div class="w-100 px-3" style="max-width: 600px;">
                <h4 class="fw-bold mb-3 border-bottom pb-2">Your Orders</h4>

                <?php if ($ordersResult->num_rows > 0): ?>
                    <?php while ($order = $ordersResult->fetch_assoc()): ?>

                        <div class="card mb-3 shadow-sm border-0 rounded-3">
                            <div class="card-body border border-danger">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <h5 class="fw-bold mb-0">Order #<?php echo $order['order_id']; ?></h5>
                                        <small class="text-muted">Tracking: <span class="fw-bold text-dark"><?php echo $order['tracking_code']; ?></span></small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill"><?php echo $order['status']; ?></span>
                                </div>

                                <!-- ORDERS LIST -->
                                <div class="mb-3 d-flex justify-content-between">
                                    <div class="border border-danger">
                                        <p class="mb-1 small text-muted"><i class="bi bi-basket-fill me-1"></i> <?php echo $order['bag_counts']; ?></p>
                                        <p class="mb-0 small text-muted"><i class="bi bi-tag-fill me-1"></i> <?php echo $order['services_requested']; ?></p>
                                    </div>

                                    <div class="border border-warning">
                                        <p>Timer Setup?</p>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                    <h5 class="fw-bold mb-0">₱<?php echo number_format($order['final_price'], 2); ?></h5>

                                    <button class="btn btn-sm btn-outline-dark rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modal<?php echo $order['order_id']; ?>">
                                        View More
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modal<?php echo $order['order_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title fw-bold">Order Details (#<?php echo $order['order_id']; ?>)</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">

                                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Bag Status</h6>
                                        <?php
                                        // Fetch Bags for this order
                                        $loadQuery = "SELECT * FROM `Process_Load` WHERE order_id = '" . $order['order_id'] . "'";
                                        $loads = $conn->query($loadQuery);
                                        ?>
                                        <ul class="list-group mb-4">
                                            <?php while ($load = $loads->fetch_assoc()): ?>
                                                <li class="list-group-item d-flex justify-content-between align-items-center border border-danger">
                                                    <div>
                                                        <strong><?php echo $load['bag_label']; ?></strong>
                                                        <div class="small text-muted"><?php echo $load['load_category']; ?></div>
                                                    </div>
                                                    <div class="d-flex flex-column">
                                                        <span class="badge bg-secondary"><?php echo $load['status']; ?></span>
                                                        <div class="border border-primary text-end">
                                                            <?php
                                                            $duration = "00:10";
                                                            $_SESSION["start_time"] = date("i:s");
                                                            ?>
                                                        </div>
                                                    </div>
                                                </li>
                                            <?php endwhile; ?>
                                        </ul>

                                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Order Logs</h6>
                                        <div class="bg-light p-3 rounded-3" style="max-height: 200px; overflow-y: auto;">
                                            <?php
                                            // Fetch Logs for this order via JOIN
                                            $logQuery = "SELECT sl.*, pl.bag_label 
                                                             FROM `System_Log` sl
                                                             JOIN `Process_Load` pl ON sl.load_id = pl.load_id
                                                             WHERE pl.order_id = '" . $order['order_id'] . "'
                                                             ORDER BY sl.timestamp DESC";
                                            $logs = $conn->query($logQuery);
                                            ?>

                                            <?php if ($logs->num_rows > 0): ?>
                                                <?php while ($log = $logs->fetch_assoc()): ?>
                                                    <div class="mb-2 pb-2 border-bottom border-light">
                                                        <div class="d-flex justify-content-between">
                                                            <strong class="small"><?php echo $log['status_event']; ?></strong>
                                                            <small class="text-muted" style="font-size: 0.7rem;"><?php echo date("M d, h:i A", strtotime($log['timestamp'])); ?></small>
                                                        </div>
                                                        <div class="small text-muted fst-italic">
                                                            <?php echo $log['bag_label']; ?> - updated by <?php echo $log['employee_name']; ?>
                                                        </div>
                                                    </div>
                                                <?php endwhile; ?>
                                            <?php else: ?>
                                                <div class="text-center text-muted small">No logs available yet.</div>
                                            <?php endif; ?>
                                        </div>

                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                        No active orders found.
                    </div>
                <?php endif; ?>

            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>