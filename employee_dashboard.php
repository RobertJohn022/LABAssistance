<?php
session_start();
require 'backend/db_conn.php';

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: staff_login.php");
    exit();
}

$statusResult = $conn->query("SELECT is_shop_open FROM Shop_Status WHERE status_id = 1");
$shopData = $statusResult->fetch_assoc();
$isOpen = ($shopData && $shopData['is_shop_open'] == 1);

$query = "SELECT o.order_id, o.tracking_code, o.customer_name, o.services_requested, o.supplies_requested, o.customer_note, o.status as order_status, o.final_price,
                 pl.load_id, pl.bag_label, pl.status, pl.timer_end, pl.timer_duration, pl.timer_remaining
          FROM `Order` o
          LEFT JOIN `Process_Load` pl ON o.order_id = pl.order_id
          ORDER BY o.order_id DESC, pl.bag_label ASC";
$result = $conn->query($query);

$groupedOrders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $order_id = $row['order_id'];
        if (!isset($groupedOrders[$order_id])) {
            $groupedOrders[$order_id] = [
                'tracking_code' => $row['tracking_code'],
                'customer_name' => $row['customer_name'],
                'services_requested' => $row['services_requested'],
                'supplies_requested' => $row['supplies_requested'],
                'customer_note' => $row['customer_note'],
                'order_status' => $row['order_status'],
                'final_price' => $row['final_price'],
                'loads' => []
            ];
        }
        if (!empty($row['load_id'])) {
            $groupedOrders[$order_id]['loads'][] = $row;
        }
    }
}

$pendingOrders = [];
$inProgressOrders = [];
$completedOrders = [];
$cancelledOrders = [];

foreach ($groupedOrders as $order_id => $order) {
    $status = $order['order_status'];
    if ($status === 'Pending Dropoff' || $status === 'Pending') {
        $pendingOrders[$order_id] = $order;
    } elseif ($status === 'Completed') {
        $completedOrders[$order_id] = $order;
    } elseif ($status === 'Cancelled') {
        $cancelledOrders[$order_id] = $order;
    } else {
        $inProgressOrders[$order_id] = $order;
    }
}

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
    <title>Staff Task Queue</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .order-group-card {
            border: 1px solid #dee2e6;
            border-radius: 12px;
            overflow: hidden;
            background: #fff;
        }

        .order-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            padding: 12px 15px;
        }

        .bag-item {
            background-color: #fafafa;
        }

        .delete-bag-btn {
            background: none;
            border: none;
            color: #dc3545;
            padding: 0 5px;
            font-size: 1rem;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .delete-bag-btn:hover {
            opacity: 1;
        }

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

        .tracking-card {
            border-left: 4px solid #0d6efd;
            background-color: #f8f9fa;
        }

        .order-summary-card {
            background-color: #ffffff;
            border: 1px solid #e9ecef;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold text-white d-flex align-items-center gap-2">
                <img src="assets/labaratory_logo_white.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                <span>LAB<span class="text-primary">Assistance</span></span>
            </span>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-light border shadow-sm rounded-pill d-none d-sm-inline" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-person-circle text-primary me-1"></i> Hi, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                </button>

                <?php if ($_SESSION['role'] === 'Manager'): ?>
                    <a href="manager_dashboard.php" class="btn btn-sm btn-outline-info rounded-pill" title="Return to Dashboard">
                        <i class="bi bi-house-door"></i>
                    </a>
                <?php endif; ?>

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

                <a href="staff_login.php" class="btn btn-sm btn-outline-danger rounded-pill">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container mt-4">

        <div class="row justify-content-center mb-4">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="app-card p-4 text-center shadow-sm bg-white rounded-3">
                    <h5 class="fw-bold text-uppercase mb-0 text-dark" style="letter-spacing: 1px;">Shop Status</h5>
                    <?php if ($isOpen): ?>
                        <h1 class="display-2 mb-0" style="color: #198754; font-weight: 800;">OPEN</h1>
                    <?php else: ?>
                        <h1 class="display-2 mb-0" style="color: #dc3545; font-weight: 800;">CLOSED</h1>
                    <?php endif; ?>
                    <p class="text-muted mb-0 fs-5 mt-1"><?php echo date("F j, Y"); ?></p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mb-3">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="input-group shadow-sm">
                    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="searchTracking" class="form-control border-start-0 ps-0 form-control-lg" placeholder="Search Tracking #, Customer, or Order ID..." onkeyup="filterOrders()">
                    <button class="btn btn-primary px-4 fw-bold" type="button" onclick="trackOrder()">Track Order</button>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-center pb-3">
            <h3 class="fw-bold">Active Task Queue</h3>
        </div>

        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">

                <?php if (!empty($groupedOrders)): ?>
                    <div class="accordion accordion-ribbon" id="ordersAccordion">
                        <?php foreach ($orderGroups as $index => $group): ?>

                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?php echo $group['id']; ?>">
                                    <button class="accordion-button <?php echo $index === 1 && count($group['orders']) > 0 ? '' : 'collapsed'; ?>" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?php echo $group['id']; ?>" aria-expanded="<?php echo $index === 1 && count($group['orders']) > 0 ? 'true' : 'false'; ?>" aria-controls="collapse<?php echo $group['id']; ?>" style="border-left: 5px solid <?php echo $group['color']; ?>;">
                                        <span class="<?php echo $group['text_color']; ?> me-2"><i class="bi bi-folder2-open"></i></span>
                                        <?php echo $group['title']; ?>
                                        <span class="badge bg-secondary ms-2 rounded-pill"><?php echo count($group['orders']); ?></span>
                                    </button>
                                </h2>
                                <div id="collapse<?php echo $group['id']; ?>" class="accordion-collapse collapse <?php echo $index === 1 && count($group['orders']) > 0 ? 'show' : ''; ?>" aria-labelledby="heading<?php echo $group['id']; ?>" data-bs-parent="#ordersAccordion">
                                    <div class="accordion-body px-1 py-2">

                                        <?php if (count($group['orders']) > 0): ?>
                                            <?php foreach ($group['orders'] as $order_id => $order):
                                                $totalBags = count($order['loads']);
                                                $pendingCount = 0;
                                                $awaitingCount = 0;
                                                foreach ($order['loads'] as $l) {
                                                    if ($l['status'] === 'Pending Dropoff') $pendingCount++;
                                                    if ($l['status'] === 'Awaiting Pickup') $awaitingCount++;
                                                }
                                            ?>

                                                <div class="order-group-card mb-4 shadow-sm order-card-item" data-tracking="<?php echo htmlspecialchars($order['tracking_code'] ?? ''); ?>" data-orderid="<?php echo htmlspecialchars($order_id); ?>">
                                                    <div class="order-header">
                                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                                            <h6 class="fw-bold mb-0 text-dark">
                                                                <i class="bi bi-person-fill me-1"></i>
                                                                <?php echo htmlspecialchars($order['customer_name']); ?>
                                                            </h6>
                                                            <div>
                                                                <span class="badge bg-dark">Order #<?php echo $order_id; ?></span>
                                                                <span class="badge bg-secondary ms-1 border border-secondary text-white" title="Tracking Number">TRK: <?php echo htmlspecialchars($order['tracking_code'] ?? 'N/A'); ?></span>
                                                            </div>
                                                        </div>

                                                        <div class="d-flex justify-content-between align-items-start mt-2">
                                                            <div>
                                                                <small class="text-muted d-block mb-1">
                                                                    <i class="bi bi-gear-fill me-1"></i>
                                                                    <?php echo htmlspecialchars($order['services_requested']); ?>
                                                                </small>

                                                                <div class="mb-2">
                                                                    <?php if (!empty($order['supplies_requested'])): ?>
                                                                        <span class="badge bg-info text-dark border border-info" style="font-size: 0.7rem;">
                                                                            <i class="bi bi-droplet-fill me-1"></i> <?php echo htmlspecialchars($order['supplies_requested']); ?>
                                                                        </span>
                                                                    <?php else: ?>
                                                                        <span class="badge bg-light text-muted border" style="font-size: 0.7rem;">
                                                                            <i class="bi bi-x-circle me-1"></i> No Add-ons
                                                                        </span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </div>

                                                            <div>
                                                                <div id="price-view-<?php echo $order_id; ?>" class="d-flex align-items-center gap-2">
                                                                    <span class="fw-bold text-success" style="font-size: 1.1rem;">₱<?php echo number_format($order['final_price'], 2); ?></span>
                                                                    <?php if ((!isset($_SESSION['role']) || $_SESSION['role'] == 'Manager') && $order['order_status'] !== 'Completed'): ?>
                                                                        <button type="button" class="btn btn-sm btn-outline-secondary py-0 px-2" onclick="togglePriceEdit('<?php echo $order_id; ?>')" title="Edit Price">
                                                                            <i class="bi bi-pencil-square"></i> Edit
                                                                        </button>
                                                                    <?php endif; ?>
                                                                </div>

                                                                <form id="price-form-<?php echo $order_id; ?>" action="backend/update_final_price.php" method="POST" class="d-none align-items-center" style="max-width: 200px;">
                                                                    <input type="hidden" name="order_id" value="<?php echo htmlspecialchars($order_id); ?>">
                                                                    <div class="input-group input-group-sm">
                                                                        <span class="input-group-text fw-bold">₱</span>
                                                                        <input type="number" step="0.01" min="0" name="final_price" class="form-control text-center fw-bold text-success" value="<?php echo htmlspecialchars($order['final_price']); ?>" required>
                                                                        <button class="btn btn-success" type="submit" title="Save Price"><i class="bi bi-check-lg"></i></button>
                                                                        <button class="btn btn-secondary" type="button" onclick="togglePriceEdit('<?php echo $order_id; ?>')" title="Cancel"><i class="bi bi-x-lg"></i></button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        <div class="row g-2 mt-2">
                                                            <div class="col-6">
                                                                <button class="btn btn-sm btn-light w-100 border shadow-sm fw-bold text-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#orderCollapse<?php echo $order_id; ?>">
                                                                    <i class="bi bi-chevron-expand"></i> Manage Bags
                                                                </button>
                                                            </div>
                                                            <div class="col-6">
                                                                <a href="chat.php?order_id=<?php echo $order_id; ?>" class="btn btn-sm btn-primary w-100 shadow-sm fw-bold">
                                                                    <i class="bi bi-chat-dots-fill"></i> Chat Customer
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="collapse" id="orderCollapse<?php echo $order_id; ?>">
                                                        <div class="order-body p-2 bg-white">
                                                            <?php if (empty($order['loads'])): ?>
                                                                <div class="text-muted small text-center py-2">No bags registered for this order yet.</div>
                                                            <?php else: ?>
                                                                <?php foreach ($order['loads'] as $load): ?>
                                                                    <div class="bag-item border rounded p-3 mb-2 shadow-sm">
                                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                                            <div class="fw-bold text-primary">
                                                                                <i class="bi bi-bag-fill me-1"></i>
                                                                                <?php echo htmlspecialchars($load['bag_label']); ?>
                                                                            </div>

                                                                            <div class="d-flex align-items-center">
                                                                                <?php
                                                                                $s = $load['status'];
                                                                                $badgeClass = 'bg-secondary';
                                                                                if ($s == 'In Queue') $badgeClass = 'bg-dark';
                                                                                elseif (strpos($s, 'Washing') !== false) $badgeClass = 'bg-primary';
                                                                                elseif (strpos($s, 'Drying') !== false) $badgeClass = 'bg-warning text-dark';
                                                                                elseif ($s == 'Awaiting Pickup') $badgeClass = 'bg-success';
                                                                                elseif ($s == 'Completed') $badgeClass = 'bg-success bg-opacity-75';
                                                                                elseif ($s == 'Cancelled') $badgeClass = 'bg-danger';
                                                                                ?>
                                                                                <span class="badge rounded-pill <?php echo $badgeClass; ?> me-2"><?php echo $s; ?></span>

                                                                                <?php if ($isOpen && $order['order_status'] !== 'Completed' && $order['order_status'] !== 'Cancelled'): ?>
                                                                                    <form action="backend/manage_bag.php" method="POST" class="m-0 p-0" data-total-bags="<?php echo $totalBags; ?>">
                                                                                        <input type="hidden" name="action" value="delete_bag">
                                                                                        <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                                        <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                                                        <button type="button" class="delete-bag-btn" title="Delete Bag" onclick="handleDeleteBag(this.closest('form'))"><i class="bi bi-trash-fill"></i></button>
                                                                                    </form>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                        </div>

                                                                        <?php
                                                                        $hasTimer = !empty($load['timer_end']);
                                                                        $isPaused = ($load['timer_remaining'] !== null);

                                                                        if ($hasTimer || $isPaused):
                                                                            $duration = intval($load['timer_duration'] ?? 1);
                                                                        ?>
                                                                            <div class="border rounded p-2 mb-3 bg-white shadow-sm border-warning">
                                                                                <div class="d-flex justify-content-between align-items-center mb-1 small">
                                                                                    <span class="fw-bold text-dark"><i class="bi bi-stopwatch me-1"></i> <?php echo strtoupper($s); ?> TIMER</span>
                                                                                    <?php if ($isPaused): ?>
                                                                                        <span class="badge bg-warning text-dark px-2"><i class="bi bi-pause-fill"></i> PAUSED</span>
                                                                                    <?php else: ?>
                                                                                        <span class="live-timer text-danger fw-bold" data-end="<?php echo date('c', strtotime($load['timer_end'])); ?>" data-duration="<?php echo $duration; ?>">--:--</span>
                                                                                    <?php endif; ?>
                                                                                </div>

                                                                                <div class="progress" style="height: 6px; background-color: #e9ecef;">
                                                                                    <?php if ($isPaused):
                                                                                        $rem = intval($load['timer_remaining']);
                                                                                        $progressPct = max(0, min(100, (1 - ($rem / $duration)) * 100));
                                                                                    ?>
                                                                                        <div class="progress-bar bg-warning progress-bar-striped" role="progressbar" style="width: <?php echo $progressPct; ?>%;"></div>
                                                                                    <?php else: ?>
                                                                                        <div class="progress-bar bg-primary progress-bar-striped progress-bar-animated live-progress" role="progressbar" style="width: 0%;" data-end="<?php echo date('c', strtotime($load['timer_end'])); ?>" data-duration="<?php echo $duration; ?>"></div>
                                                                                    <?php endif; ?>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>

                                                                        <?php if ($isOpen && $s !== 'Completed' && $s !== 'Pending Dropoff' && $s !== 'Awaiting Pickup' && $order['order_status'] !== 'Cancelled'): ?>

                                                                            <div class="mt-2">
                                                                                <div class="bg-light p-2 rounded border">
                                                                                    <?php
                                                                                    $hasW = (stripos($order['services_requested'], 'Wash') !== false);
                                                                                    $hasD = (stripos($order['services_requested'], 'Dry') !== false);
                                                                                    $hasF = (stripos($order['services_requested'], 'Fold') !== false);
                                                                                    $needsMachine = ($hasW || $hasD);

                                                                                    $nextPhase = 'Awaiting Pickup';
                                                                                    if ($s === 'In Queue') {
                                                                                        $nextPhase = $hasW ? 'Washing' : ($hasD ? 'Drying' : 'Folding');
                                                                                    } elseif ($s === 'Washing') {
                                                                                        $nextPhase = $hasD ? 'Drying' : ($hasF ? 'Folding' : 'Awaiting Pickup');
                                                                                    } elseif ($s === 'Drying') {
                                                                                        $nextPhase = $hasF ? 'Folding' : 'Awaiting Pickup';
                                                                                    } elseif ($s === 'Folding') {
                                                                                        $nextPhase = 'Awaiting Pickup';
                                                                                    }

                                                                                    // DESCRIPTIVE TIMER TEXT LOGIC
                                                                                    $timerLabel = 'Set New Timer:';
                                                                                    if ($s === 'In Queue') {
                                                                                        if ($hasW) $timerLabel = 'Set Washing Machine Timer:';
                                                                                        elseif ($hasD) $timerLabel = 'Set Dryer Timer:';
                                                                                    } elseif ($s === 'Washing') {
                                                                                        $timerLabel = 'Set Addt\'l Wash Time:';
                                                                                    } elseif ($s === 'Drying') {
                                                                                        $timerLabel = 'Set Addt\'l Dry Time:';
                                                                                    }
                                                                                    ?>

                                                                                    <div class="d-flex flex-wrap align-items-end justify-content-between gap-2">

                                                                                        <?php if (($s === 'In Queue' && $needsMachine) || $s === 'Washing' || $s === 'Drying'): ?>
                                                                                            <div class="flex-grow-1" style="max-width: 220px;">
                                                                                                <?php if ($hasTimer): ?>
                                                                                                    <div class="d-flex gap-2">
                                                                                                        <form action="backend/update_status.php" method="POST" class="flex-grow-1 m-0">
                                                                                                            <input type="hidden" name="action" value="pause_timer">
                                                                                                            <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                                                            <button type="submit" class="btn btn-warning btn-sm w-100 fw-bold px-1"><i class="bi bi-pause-fill"></i> Pause</button>
                                                                                                        </form>
                                                                                                        <form action="backend/update_status.php" method="POST" class="flex-grow-1 m-0">
                                                                                                            <input type="hidden" name="action" value="reset_timer">
                                                                                                            <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                                                            <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold px-1"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                                                                                                        </form>
                                                                                                    </div>
                                                                                                <?php elseif ($isPaused): ?>
                                                                                                    <div class="d-flex gap-2">
                                                                                                        <form action="backend/update_status.php" method="POST" class="flex-grow-1 m-0">
                                                                                                            <input type="hidden" name="action" value="resume_timer">
                                                                                                            <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                                                            <button type="submit" class="btn btn-success btn-sm w-100 fw-bold px-1"><i class="bi bi-play-fill"></i> Resume</button>
                                                                                                        </form>
                                                                                                        <form action="backend/update_status.php" method="POST" class="flex-grow-1 m-0">
                                                                                                            <input type="hidden" name="action" value="reset_timer">
                                                                                                            <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                                                            <button type="submit" class="btn btn-danger btn-sm w-100 fw-bold px-1"><i class="bi bi-arrow-counterclockwise"></i> Reset</button>
                                                                                                        </form>
                                                                                                    </div>
                                                                                                <?php else: ?>
                                                                                                    <form action="backend/update_status.php" method="POST" class="m-0">
                                                                                                        <input type="hidden" name="action" value="start_timer">
                                                                                                        <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                                                        <label class="small fw-bold text-muted mb-1 text-truncate d-block" style="max-width: 100%;">
                                                                                                            <?php echo $timerLabel; ?>
                                                                                                        </label>
                                                                                                        <div class="input-group input-group-sm">
                                                                                                            <input type="number" name="minutes" class="form-control text-center fw-bold" placeholder="Min" required min="1">
                                                                                                            <button type="submit" class="btn btn-primary fw-bold px-2"><i class="bi bi-play-fill"></i> Start</button>
                                                                                                        </div>
                                                                                                    </form>
                                                                                                <?php endif; ?>
                                                                                            </div>
                                                                                        <?php endif; ?>

                                                                                        <?php
                                                                                        // BYPASS LOGIC: Always allow moving to the next phase unless the bag is in a final state
                                                                                        $showNextPhase = true;
                                                                                        if (in_array($s, ['Pending Dropoff', 'Awaiting Pickup', 'Completed', 'Cancelled'])) {
                                                                                            $showNextPhase = false;
                                                                                        }

                                                                                        if ($showNextPhase):
                                                                                        ?>
                                                                                            <div class="ms-auto">
                                                                                                <form action="backend/update_status.php" method="POST" onsubmit="submitNextPhase(event, this)" class="m-0">
                                                                                                    <input type="hidden" name="is_ajax" value="1">
                                                                                                    <input type="hidden" name="action" value="next_phase">
                                                                                                    <input type="hidden" name="load_id" value="<?php echo $load['load_id']; ?>">
                                                                                                    <input type="hidden" name="next_phase_name" value="<?php echo $nextPhase; ?>">
                                                                                                    <button type="submit" class="btn btn-info text-dark btn-sm fw-bold shadow-sm border border-info" title="Move to <?php echo $nextPhase; ?>">
                                                                                                        Move to <?php echo $nextPhase; ?> <i class="bi bi-arrow-right-short"></i>
                                                                                                    </button>
                                                                                                </form>
                                                                                            </div>
                                                                                        <?php endif; ?>

                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                <?php endforeach; ?>
                                                            <?php endif; ?>
                                                        </div>

                                                        <div class="p-3 bg-white border-top d-flex flex-column gap-2">

                                                            <?php if ($isOpen && $order['order_status'] !== 'Completed' && $order['order_status'] !== 'Cancelled'): ?>
                                                                <form action="backend/manage_bag.php" method="POST" class="m-0 p-0" data-total-bags="<?php echo $totalBags; ?>">
                                                                    <button type="button" class="btn btn-outline-primary w-100 fw-bold py-2 shadow-sm" onclick="openAddBagModal('<?php echo htmlspecialchars($order_id); ?>')">
                                                                        <i class="bi bi-plus-circle me-1"></i> Add Bag
                                                                    </button>
                                                                </form> <?php endif; ?>

                                                            <?php if ($pendingCount > 0 && $isOpen): ?>
                                                                <form action="backend/update_status.php" method="POST" class="m-0">
                                                                    <input type="hidden" name="action" value="receive_order">
                                                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                                    <button type="submit" class="btn btn-primary w-100 fw-bold py-2 shadow-sm">
                                                                        <i class="bi bi-box-seam me-1"></i> Mark Order as Received
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>

                                                            <?php if ($awaitingCount === $totalBags && $totalBags > 0 && $isOpen && $order['order_status'] !== 'Completed' && $order['order_status'] !== 'Cancelled'): ?>
                                                                <form action="backend/update_status.php" method="POST" class="m-0" onsubmit="submitCompleteOrder(event, this)">
                                                                    <input type="hidden" name="is_ajax" value="1">
                                                                    <input type="hidden" name="action" value="complete_order">
                                                                    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>">
                                                                    <button type="submit" class="btn btn-success w-100 fw-bold py-2 shadow-sm">
                                                                        <i class="bi bi-check2-circle me-1"></i> Complete Order
                                                                    </button>
                                                                </form>
                                                            <?php endif; ?>

                                                            <button class="btn btn-sm btn-link text-decoration-none w-100 mt-1" onclick="viewLogs('<?php echo $order_id; ?>')">
                                                                <i class="bi bi-journal-text"></i> View Order History Logs
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div> <?php endforeach; ?>
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
                        <i class="bi bi-check-circle-fill display-1 text-success opacity-50"></i>
                        <h4 class="mt-3 fw-bold">All Clear!</h4>
                        <p>No active laundry tasks at the moment.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header bg-light border-bottom-0 pb-0 pt-4 px-4 rounded-top-4">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-person-circle me-2 text-primary"></i>Profile Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 pt-4">

                    <form action="backend/update_profile.php" method="POST" class="mb-4">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Personal Information</h6>
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label small text-muted fw-bold text-uppercase">First Name</label>
                                <input type="text" class="form-control" name="first_name" value="<?php echo isset($_SESSION['first_name']) ? htmlspecialchars($_SESSION['first_name']) : ''; ?>" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label small text-muted fw-bold text-uppercase">Last Name</label>
                                <input type="text" class="form-control" name="last_name" value="<?php echo isset($_SESSION['last_name']) ? htmlspecialchars($_SESSION['last_name']) : ''; ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted"><i class="bi bi-info-circle me-1"></i>Please contact the manager to change your email.</small>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_profile" class="btn btn-primary fw-bold px-4">Save Profile</button>
                        </div>
                    </form>

                    <hr class="border-secondary opacity-25 my-4">

                    <form action="backend/update_profile.php" method="POST">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3"><i class="bi bi-shield-lock me-2"></i>Security</h6>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold text-uppercase">Current Password</label>
                            <input type="password" class="form-control" name="current_password" placeholder="Required to authorize change" required>
                            <div class="form-text small mt-2">
                                <i class="bi bi-envelope-check me-1"></i> A secure link to reset your password will be sent to your email.
                            </div>
                        </div>
                        <div class="text-end">
                            <button type="submit" name="update_password" class="btn btn-warning fw-bold px-4">Request Password Change</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <?php include 'employee_modals.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');

            if (status === 'profile_updated') {
                Swal.fire({
                    title: 'Success!',
                    text: 'Your profile has been updated.',
                    icon: 'success'
                });
            } else if (status === 'shop_closed') {
                Swal.fire({
                    title: 'Action Denied',
                    text: 'You cannot manage or modify bags while the shop is closed.',
                    icon: 'error'
                });
                // --------------------------
            } else if (status === 'password_mismatch') {
                Swal.fire({
                    title: 'Update Failed',
                    text: 'Your new passwords do not match.',
                    icon: 'warning'
                });
            } else if (status === 'error') {
                Swal.fire({
                    title: 'Error',
                    text: 'Something went wrong. Please try again.',
                    icon: 'error'
                });
            }

            if (status) {
                window.history.replaceState(null, null, window.location.pathname);
            }

            // --- DROPDOWN STATE PERSISTENCE LOGIC ---
            // Restore any open bag collapsibles properly using Bootstrap's JS API
            let openCollapses = JSON.parse(localStorage.getItem('openOrderCollapses')) || [];
            openCollapses.forEach(id => {
                let el = document.getElementById(id);
                if (el) {
                    // Remove the 'collapse' class temporarily to avoid animation, add 'show'
                    el.classList.add('show');

                    // Find the toggle button and update its ARIA state
                    let toggleBtn = document.querySelector(`[data-bs-target="#${id}"]`);
                    if (toggleBtn) {
                        toggleBtn.setAttribute('aria-expanded', 'true');
                        toggleBtn.classList.remove('collapsed');
                    }

                    // Initialize the Bootstrap instance so its internal state recognizes it's open
                    bootstrap.Collapse.getOrCreateInstance(el, {
                        toggle: false
                    });
                }
            });

            // Targeted listener: Only triggers on the collapsible itself to avoid bubbling bugs
            const bagCollapses = document.querySelectorAll('.collapse');
            bagCollapses.forEach(c => {
                c.addEventListener('shown.bs.collapse', function(e) {
                    if (e.target === this && this.id.startsWith('orderCollapse')) {
                        let open = JSON.parse(localStorage.getItem('openOrderCollapses')) || [];
                        if (!open.includes(this.id)) open.push(this.id);
                        localStorage.setItem('openOrderCollapses', JSON.stringify(open));
                    }
                });
                c.addEventListener('hidden.bs.collapse', function(e) {
                    if (e.target === this && this.id.startsWith('orderCollapse')) {
                        let open = JSON.parse(localStorage.getItem('openOrderCollapses')) || [];
                        open = open.filter(id => id !== this.id);
                        localStorage.setItem('openOrderCollapses', JSON.stringify(open));
                    }
                });
            });
        });
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
</body>

</html>