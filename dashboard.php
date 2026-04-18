<?php
session_start();
require 'backend/db_conn.php';

// Check auth
if (!isset($_SESSION['user_id'])) {
    header("Location: customer_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Capture session alerts
$success_msg = $_SESSION['success_msg'] ?? '';
$error_msg = $_SESSION['error_msg'] ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);

// Check if shop is open or closed
$statusResult = $conn->query("SELECT is_shop_open FROM `shop_status` WHERE status_id = 1");
$shopData = $statusResult->fetch_assoc();
$isOpen = ($shopData && $shopData['is_shop_open'] == 1);

// Fetch user orders
$orderQuery = "SELECT * FROM `order` WHERE customer_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($orderQuery);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$ordersResult = $stmt->get_result();

// Group orders into arrays based on status
$pendingOrders = [];
$inProgressOrders = [];
$completedOrders = [];
$cancelledOrders = [];

while ($order = $ordersResult->fetch_assoc()) {
    $status = $order['status'];
    if ($status === 'Pending Dropoff' || $status === 'Pending') {
        $pendingOrders[] = $order;
    } elseif ($status === 'Completed') {
        $completedOrders[] = $order;
    } elseif ($status === 'Cancelled') {
        $cancelledOrders[] = $order;
    } else {
        $inProgressOrders[] = $order;
    }
}

// Prepare arrays for iteration
$orderGroups = [
    ['title' => 'Pending Dropoff', 'id' => 'Pending', 'orders' => $pendingOrders, 'color' => '#ffc107', 'text_color' => 'text-dark'],
    ['title' => 'In Progress', 'id' => 'InProgress', 'orders' => $inProgressOrders, 'color' => '#0d6efd', 'text_color' => 'text-primary'],
    ['title' => 'Completed', 'id' => 'Completed', 'orders' => $completedOrders, 'color' => '#198754', 'text_color' => 'text-success'],
    ['title' => 'Cancelled', 'id' => 'Cancelled', 'orders' => $cancelledOrders, 'color' => '#dc3545', 'text_color' => 'text-danger']
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
    <style>
        .tracking-card {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
        }

        .order-summary-card {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
        }

        /* Accordion Ribbon Styling */
        .accordion-ribbon .accordion-item {
            border: none;
            background: transparent;
            margin-bottom: 0.75rem;
        }

        .accordion-ribbon .accordion-button {
            border-radius: 8px !important;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            font-weight: 700;
        }

        .accordion-ribbon .accordion-button:not(.collapsed) {
            background-color: #f8f9fa;
            box-shadow: inset 0 -1px 0 rgba(0, 0, 0, .125);
        }

        .accordion-ribbon .accordion-button::after {
            filter: grayscale(1);
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold d-flex align-items-center gap-2">
                <img src="assets/labaratory_logo.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                <span>LAB<span class="text-primary">Assistance</span></span>
            </span>

            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>

                <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#profileModal" title="Profile Settings">
                    <i class="bi bi-person-gear"></i>
                </button>

                <div class="dropdown">
                    <button class="btn btn-light position-relative rounded-circle border shadow-sm p-1" type="button" id="notifDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="width: 36px; height: 36px;">
                        <i class="bi bi-bell-fill text-secondary"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" id="notifBadge" style="font-size: 0.65rem;">
                            0
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="notifDropdown" id="notifList" style="width: 320px; max-height: 400px; overflow-y: auto;">
                        <li class="dropdown-item text-center text-muted small py-3">Loading...</li>
                    </ul>
                </div>

                <a href="customer_login.php" class="btn btn-sm btn-outline-danger rounded-pill" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container mt-4">

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="app-card p-4 text-center shadow-sm bg-white rounded-3">
                    <h5 class="fw-bold text-uppercase mb-0 text-dark" style="letter-spacing: 1px;">Shop Status</h5>
                    <?php if ($isOpen): ?>
                        <h1 class="display-2 mb-0" style="color: #198754; font-weight: 800;">OPEN</h1>
                    <?php else: ?>
                        <h1 class="display-2 mb-0" style="color: #dc3545; font-weight: 800;">CLOSED</h1>
                    <?php endif; ?>
                    <p class="text-muted mb-0 fs-5 mt-1">
                        <?php echo date("F j, Y"); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="app-card p-4 text-center bg-dark text-white rounded-3 shadow-sm">
                    <h4 class="fw-bold mb-1">Need Laundry Service?</h4>
                    <p class="text-white-50 small mb-3">We wash, dry and fold!</p>
                    <a href="order.php" class="btn btn-light text-dark fw-bold w-100 rounded-pill py-3 shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i>Place New Order
                    </a>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">

                <div class="d-flex justify-content-between align-items-center mb-2 ps-2">
                    <h6 class="text-muted fw-bold text-uppercase small mb-0">Your Orders</h6>
                </div>

                <?php if ($ordersResult->num_rows > 0): ?>
                    <div class="input-group shadow-sm mb-4">
                        <span class="input-group-text bg-white border-end-0 text-primary"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchTracking" class="form-control border-start-0 ps-0" placeholder="4-Digit Tracking Number">
                        <button class="btn btn-primary px-3 fw-bold" type="button" onclick="trackOrder()">Track</button>
                    </div>

                    <div class="accordion accordion-ribbon" id="ordersAccordion">
                        <?php foreach ($orderGroups as $index => $group): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $group['id']; ?>">
                                    <button class="accordion-button <?php echo $index === 0 && count($group['orders']) > 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $group['id']; ?>" aria-expanded="<?php echo $index === 0 && count($group['orders']) > 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $group['id']; ?>" style="border-left: 5px solid <?php echo $group['color']; ?>;">
                                        <span class="<?php echo $group['text_color']; ?> me-2"><i class="bi bi-folder2-open"></i></span>
                                        <?php echo $group['title']; ?>
                                        <span class="badge bg-secondary ms-2 rounded-pill group-count"><?php echo count($group['orders']); ?></span>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $group['id']; ?>" class="accordion-collapse collapse <?php echo $index === 0 && count($group['orders']) > 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $group['id']; ?>" data-bs-parent="#ordersAccordion">
                                    <div class="accordion-body px-1 py-2">

                                        <?php if (count($group['orders']) > 0): ?>
                                            <?php foreach ($group['orders'] as $order): ?>

                                                <?php
                                                // Check loads FIRST to calculate the true Display Status
                                                $loadQuery = "SELECT * FROM `process_load` WHERE order_id = '" . $order['order_id'] . "'";
                                                $loadsResult = $conn->query($loadQuery);
                                                $loadArray = [];

                                                $canCancel = ($order['status'] !== 'Cancelled' && $order['status'] !== 'Completed');
                                                $allReadyForPickup = true;
                                                $hasLoads = false;

                                                while ($load = $loadsResult->fetch_assoc()) {
                                                    $loadArray[] = $load;
                                                    $hasLoads = true;
                                                    if (!in_array($load['status'], ['Pending Dropoff', 'In Queue', 'Pending'])) {
                                                        $canCancel = false;
                                                    }
                                                    if ($load['status'] !== 'Ready for Pickup') {
                                                        $allReadyForPickup = false;
                                                    }
                                                }

                                                // Dynamic Status Evaluation
                                                $displayStatus = $order['status'];
                                                if ($hasLoads && $allReadyForPickup && $order['status'] !== 'Completed' && $order['status'] !== 'Cancelled') {
                                                    $displayStatus = 'Ready for Pickup';
                                                }

                                                // Colors based on Status
                                                $statusTextColor = 'text-primary';
                                                $masterBadgeClass = 'bg-primary';

                                                if ($displayStatus === 'Completed') {
                                                    $statusTextColor = 'text-success';
                                                    $masterBadgeClass = 'bg-success';
                                                } elseif ($displayStatus === 'Cancelled') {
                                                    $statusTextColor = 'text-danger';
                                                    $masterBadgeClass = 'bg-danger';
                                                } elseif ($displayStatus === 'Ready for Pickup') {
                                                    $statusTextColor = 'text-success';
                                                    $masterBadgeClass = 'bg-success';
                                                }
                                                ?>

                                                <div class="app-card mb-3 p-3 bg-white rounded-3 shadow-sm border order-card-item" data-tracking="<?php echo htmlspecialchars($order['tracking_code'] ?? ''); ?>" data-orderid="<?php echo htmlspecialchars($order['order_id']); ?>">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div class="d-flex align-items-center gap-2">
                                                            <span class="badge bg-light text-dark border">#<?php echo $order['order_id']; ?></span>
                                                            <span class="small fw-bold <?php echo $statusTextColor; ?>"><?php echo $displayStatus; ?></span>
                                                        </div>
                                                        <h5 class="fw-bold mb-0 text-dark">₱<?php echo number_format($order['final_price'], 2); ?></h5>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center text-muted small mb-3">
                                                        <div>
                                                            <i class="bi bi-basket-fill me-1"></i> <?php echo $order['bag_counts']; ?> • <?php echo $order['services_requested']; ?>
                                                        </div>
                                                        <div class="fw-bold text-secondary">
                                                            TRK: <?php echo htmlspecialchars($order['tracking_code'] ?? ''); ?>
                                                        </div>
                                                    </div>

                                                    <button type="button" class="btn btn-outline-primary btn-sm w-100 fw-bold rounded-pill" data-bs-toggle="modal" data-bs-target="#modal<?php echo $order['order_id']; ?>">
                                                        <i class="bi bi-eye-fill me-1"></i> View Order Details
                                                    </button>
                                                </div>

                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="text-muted small text-center bg-transparent py-2">
                                                No <?php echo strtolower($group['title']); ?> found.
                                            </div>
                                        <?php endif; ?>

                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center py-5 text-muted">
                        <div class="mb-4" style="padding-bottom: 6px;">
                            <i class="bi bi-basket display-1 text-light bg-secondary p-4 rounded-circle bg-opacity-10"></i>
                        </div>
                        <h5 class="fw-bold mt-1">No orders yet</h5>
                        <p class="text-muted small">Your active and past orders will appear here.</p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>

    <?php
    if ($ordersResult->num_rows > 0):
        foreach ($orderGroups as $group):
            foreach ($group['orders'] as $order):

                // Fetch loads for this specific order
                $loadQuery = "SELECT * FROM `process_load` WHERE order_id = '" . $order['order_id'] . "'";
                $loadsResult = $conn->query($loadQuery);
                $loadArray = [];
                $canCancel = ($order['status'] !== 'Cancelled' && $order['status'] !== 'Completed');
                $allReadyForPickup = true;
                $hasLoads = false;

                while ($load = $loadsResult->fetch_assoc()) {
                    $loadArray[] = $load;
                    $hasLoads = true;
                    if (!in_array($load['status'], ['Pending Dropoff', 'In Queue', 'Pending'])) {
                        $canCancel = false;
                    }
                    if ($load['status'] !== 'Ready for Pickup') {
                        $allReadyForPickup = false;
                    }
                }

                // Recalculate status for the modal
                $displayStatus = $order['status'];
                if ($hasLoads && $allReadyForPickup && $order['status'] !== 'Completed' && $order['status'] !== 'Cancelled') {
                    $displayStatus = 'Ready for Pickup';
                }

                $masterBadgeClass = 'bg-primary';
                if ($displayStatus === 'Completed' || $displayStatus === 'Ready for Pickup') {
                    $masterBadgeClass = 'bg-success';
                } elseif ($displayStatus === 'Cancelled') {
                    $masterBadgeClass = 'bg-danger';
                }
    ?>
                <div class="modal fade" id="modal<?php echo $order['order_id']; ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                        <div class="modal-content border-0 rounded-4">
                            <div class="modal-header border-0 pb-0 pt-4 px-4">
                                <h5 class="modal-title fw-bold">Order Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            <div class="modal-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <div>
                                        <span class="text-muted small d-block text-uppercase fw-bold">Tracking Number</span>
                                        <span class="fw-bold fs-5 text-dark"><?php echo $order['tracking_code']; ?></span>
                                    </div>
                                    <span class="badge <?php echo $masterBadgeClass; ?> fs-6 px-3 py-2 rounded-pill"><?php echo $displayStatus; ?></span>
                                </div>

                                <h6 class="fw-bold text-uppercase small text-muted mb-3">Track Order Progress</h6>
                                <div class="mb-4">
                                    <?php if (count($loadArray) > 0): ?>
                                        <?php foreach ($loadArray as $load): ?>
                                            <div class="tracking-card rounded-3 p-3 mb-2 shadow-sm">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="fw-bold text-dark">
                                                        <i class="bi bi-bag-check text-primary me-1"></i> <?php echo $load['bag_label']; ?>
                                                    </div>
                                                    <?php
                                                    $s = $load['status'];
                                                    $badgeClass = 'bg-secondary';
                                                    if ($s == 'In Queue') $badgeClass = 'bg-dark';
                                                    elseif ($s == 'Washing') $badgeClass = 'bg-primary';
                                                    elseif ($s == 'Drying') $badgeClass = 'bg-warning text-dark';
                                                    elseif ($s == 'Folding') $badgeClass = 'bg-info text-dark';
                                                    elseif ($s == 'Ready for Pickup') $badgeClass = 'bg-success';
                                                    elseif ($s == 'Completed') $badgeClass = 'bg-success bg-opacity-75';
                                                    elseif ($s == 'Cancelled') $badgeClass = 'bg-danger';
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?>"><?php echo $s; ?></span>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="text-muted small text-center bg-light p-3 rounded">Bags are being processed.</div>
                                    <?php endif; ?>
                                </div>

                                <a href="chat.php?order_id=<?php echo $order['order_id']; ?>" class="btn btn-primary w-100 fw-bold shadow-sm mb-3">
                                    <i class="bi bi-chat-dots-fill me-2"></i> Message Staff
                                </a>

                                <button class="btn btn-sm btn-link text-decoration-none w-100 mb-3" onclick="viewCustomerLogs('<?php echo $order['order_id']; ?>')">
                                    <i class="bi bi-clock-history me-1"></i> View Full Activity History
                                </button>

                                <h6 class="fw-bold text-uppercase small text-muted mb-3">Order Information</h6>
                                <div class="order-summary-card rounded-3 p-3 shadow-sm mb-2">
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span class="text-muted">Services</span>
                                        <span class="fw-bold text-end"><?php echo $order['services_requested']; ?></span>
                                    </div>
                                    <?php if (!empty($order['supplies_requested'])): ?>
                                        <div class="d-flex justify-content-between mb-2 small">
                                            <span class="text-muted">Add-ons</span>
                                            <span class="fw-bold text-end"><?php echo $order['supplies_requested']; ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span class="text-muted">Bags</span>
                                        <span class="fw-bold text-end"><?php echo $order['bag_counts']; ?></span>
                                    </div>
                                    <?php if (!empty($order['customer_note'])): ?>
                                        <div class="d-flex justify-content-between mb-2 small">
                                            <span class="text-muted">Instructions</span>
                                            <span class="fw-bold text-end text-truncate" style="max-width: 60%;" title="<?php echo htmlspecialchars($order['customer_note']); ?>">
                                                <?php echo htmlspecialchars($order['customer_note']); ?>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    <hr class="my-2 border-secondary opacity-25">

                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-dark">Total</span>
                                        <span class="fw-bold fs-5 text-primary">₱<?php echo number_format($order['final_price'], 2); ?></span>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer border-0 px-4 pb-4 flex-column">
                                <?php if ($canCancel): ?>
                                    <form action="backend/cancel_order.php" method="POST" class="w-100 mb-2">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <button type="submit" class="btn btn-outline-danger w-100 fw-bold border" onclick="return confirm('Are you sure you want to cancel this order? This action cannot be undone.');">
                                            Cancel Order
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <button type="button" class="btn btn-light w-100 fw-bold border" data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

    <?php
            endforeach;
        endforeach;
    endif;
    ?>

    <div class="modal fade" id="customerLogsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">Activity History</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 pt-2">
                    <div id="customerLogContainer" class="bg-light p-3 rounded-3 small">
                        <span class="text-muted">Loading history...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="profileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">

                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <h5 class="modal-title fw-bold">Profile Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form method="POST" action="backend/update_profile.php" class="mb-4">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">First Name</label>
                                <input type="text" name="first_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['first_name']); ?>" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Last Name</label>
                                <input type="text" name="last_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['last_name']); ?>" required>
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary fw-bold px-4">Save Profile</button>
                        </div>
                    </form>

                    <hr class="border-secondary opacity-25 my-4">

                    <form method="POST" action="backend/update_profile.php">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Security</h6>
                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Current Password</label>
                            <input type="password" name="current_password" class="form-control" placeholder="Required to authorize change" required>
                            <div class="form-text small mt-2">
                                <i class="bi bi-envelope-check me-1"></i> A secure link to reset your password will be sent to your email.
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_password" class="btn btn-warning fw-bold px-4">Request Password Change</button>
                        </div>
                    </form>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 d-flex justify-content-between w-100 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-outline-danger fw-bold" onclick="confirmDeleteAccount()">Delete Account</button>
                    <button type="button" class="btn btn-secondary fw-bold border" data-bs-dismiss="modal">Close</button>
                </div>

                <form id="deleteAccountForm" action="backend/delete_account.php" method="POST" style="display: none;">
                    <input type="hidden" name="action" value="delete_account">
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // --- Fetch Customer Logs Logic ---
        function viewCustomerLogs(orderId) {
            const container = document.getElementById('customerLogContainer');
            container.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-primary" role="status"></div> Loading...</div>';

            const logsModal = new bootstrap.Modal(document.getElementById('customerLogsModal'));
            logsModal.show();

            fetch('backend/fetch_customer_logs.php?order_id=' + orderId)
                .then(response => response.text())
                .then(data => container.innerHTML = data)
                .catch(err => container.innerHTML = '<div class="text-danger text-center py-3">Error loading history.</div>');
        }

        // --- Popup Trigger Logic for "Track" Button ---
        function trackOrder() {
            const input = document.getElementById('searchTracking').value.toLowerCase().trim();
            if (input === '') return;

            const cards = document.querySelectorAll('.order-card-item');
            let found = false;

            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const tracking = card.getAttribute('data-tracking').toLowerCase();
                const orderId = card.getAttribute('data-orderid').toLowerCase();

                if (tracking === input || orderId === input) {
                    found = true;

                    // Automatically open the accordion folder if it's closed
                    const accordionCollapse = card.closest('.accordion-collapse');
                    if (accordionCollapse && !accordionCollapse.classList.contains('show')) {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(accordionCollapse);
                        bsCollapse.show();
                    }

                    // Open the modal
                    const rawOrderId = card.getAttribute('data-orderid');
                    const modalEl = document.getElementById('modal' + rawOrderId);

                    if (modalEl) {
                        const modalObj = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalObj.show();
                    }
                    break;
                }
            }

            if (!found) {
                Swal.fire({
                    icon: 'error',
                    title: 'Order Not Found',
                    text: 'We could not find an active or past order with that ID or tracking number.',
                    confirmButtonColor: '#0d6efd'
                });
            }
        }

        // Trigger the search button if the user hits the "Enter" key
        document.getElementById('searchTracking').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                trackOrder();
            }
        });

        // --- Delete Account Confirm ---
        function confirmDeleteAccount() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone. All your orders and personal data will be permanently deleted.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete my account'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('deleteAccountForm').submit();
                }
            });
        }
    </script>
    <script>
        function fetchNotifications() {
            fetch('backend/check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById('notifBadge');
                        const list = document.getElementById('notifList');

                        if (!badge || !list) return; // Safeguard

                        if (data.unread_count > 0) {
                            badge.innerText = data.unread_count;
                            badge.classList.remove('d-none');
                        } else {
                            badge.classList.add('d-none');
                        }

                        if (data.notifications.length === 0) {
                            list.innerHTML = '<li class="dropdown-item text-center text-muted small py-3"><i class="bi bi-check-circle text-success fs-4 d-block mb-2"></i>You are all caught up!</li>';
                        } else {
                            let html = '<li class="dropdown-header fw-bold text-dark bg-light border-bottom">Unread Messages</li>';
                            data.notifications.forEach(notif => {
                                html += `
                            <li>
                                <a class="dropdown-item border-bottom py-2 text-wrap" href="chat.php?order_id=${notif.order_id}">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="small text-primary"><i class="bi bi-person-circle me-1"></i>${escapeNotifHtml(notif.sender_name)}</strong>
                                        <span class="badge bg-secondary" style="font-size: 0.65rem;">TRK: ${notif.tracking_code}</span>
                                    </div>
                                    <div class="small text-dark" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">${escapeNotifHtml(notif.message_text)}</div>
                                </a>
                            </li>`;
                            });
                            list.innerHTML = html;
                        }
                    }
                })
                .catch(err => console.error("Notification Sync Error:", err));
        }

        function escapeNotifHtml(unsafe) {
            if (!unsafe) return '';
            return unsafe.toString().replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
        }

        // Start Real-time sync
        fetchNotifications();
        setInterval(fetchNotifications, 5000); // Polls every 5 seconds
    </script>

    <?php
    // --- SweetAlert for successful order placement ---
    if (isset($_SESSION['order_success']) && $_SESSION['order_success'] === true):
    ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    title: 'Order Placed!',
                    html: `Your laundry has been added to the queue.<br><br>
                           <strong>Order ID:</strong> <?php echo $_SESSION['new_order_id']; ?><br>
                           <strong>Tracking Code:</strong> <span class="text-primary fs-5"><?php echo $_SESSION['new_tracking']; ?></span>`,
                    icon: 'success',
                    confirmButtonText: 'Got it!',
                    confirmButtonColor: '#198754'
                });
            });
        </script>
    <?php
        unset($_SESSION['order_success']);
        unset($_SESSION['new_order_id']);
        unset($_SESSION['new_tracking']);
    endif;
    ?>
</body>

</html>