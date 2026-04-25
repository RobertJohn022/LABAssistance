<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: employee_login.php");
    exit();
}
require 'backend/db_conn.php';

// Generate report each month
$lastMonth = new DateTime('first day of last month');
$m = (int) $lastMonth->format('m');
$y = (int) $lastMonth->format('Y');

$check = $conn->query("SELECT 1 FROM `sales_report` WHERE report_month = $m AND report_year = $y");
if ($check->num_rows === 0) {
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_POST['generate_month_year'] = $lastMonth->format('F Y');
}

// Generate Report
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['generate_month_year'])) {
    $date = DateTime::createFromFormat('F Y', $_POST['generate_month_year']);
    $month = (int) $date->format('m');
    $year = (int) $date->format('Y');

    $stmt = $conn->prepare("
        SELECT supplies_requested, final_price 
        FROM `Order` 
        WHERE payment_status = 'Paid'
          AND MONTH(created_at) = ? 
          AND YEAR(created_at)  = ?
    ");
    $stmt->bind_param("ii", $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $total_orders = $total_revenue = $detergent_count = $softener_count = 0;
    while ($row = $result->fetch_assoc()) {
        $total_orders++;
        $total_revenue += (float) $row['final_price'];
        $supplies = strtolower($row['supplies_requested'] ?? '');
        $detergent_count += substr_count($supplies, 'detergent');
        $softener_count += substr_count($supplies, 'softener');
    }
    $stmt->close();

    $stmt = $conn->prepare("
        INSERT INTO `sales_report` 
            (report_month, report_year, total_orders, total_revenue, detergent_count, softener_count)
        VALUES (?, ?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            total_orders    = VALUES(total_orders),
            total_revenue   = VALUES(total_revenue),
            detergent_count = VALUES(detergent_count),
            softener_count  = VALUES(softener_count)
    ");
    $stmt->bind_param("iiidii", $month, $year, $total_orders, $total_revenue, $detergent_count, $softener_count);
    $stmt->execute();
    $stmt->close();

    header("Location: orders_history.php");
    exit();
}

// Purge database month
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_month'])) {
    $date = DateTime::createFromFormat('F Y', $_POST['delete_month']);
    $year = $date->format('Y');
    $month = $date->format('m');

    $stmt = $conn->prepare("DELETE FROM `Order` WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?");
    $stmt->bind_param("ss", $year, $month);
    $stmt->execute();
    $stmt->close();

    header("Location: orders_history.php");
    exit();
}

// fetch orders by month

$result = $conn->query("SELECT * FROM `Order` ORDER BY created_at DESC");
$grouped_orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $month_year = date('F Y', strtotime($row['created_at']));
        $grouped_orders[$month_year][] = $row;
    }
}

// Fetcha ll existing reports
$report_lookup = [];
$reports = $conn->query("SELECT * FROM `sales_report`");
while ($r = $reports->fetch_assoc()) {
    $key = date('F Y', mktime(0, 0, 0, $r['report_month'], 1, $r['report_year']));
    $report_lookup[$key] = $r;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manage Employees - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
    <style>
        .table-custom-header th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            color: #6c757d;
            font-weight: 700;
        }

        .month-title {
            font-size: 1rem;
            font-weight: 700;
            color: #fff;
            background-color: #212529;
            padding: 8px 16px;
            border-radius: 8px 8px 0 0;
            margin-bottom: 0;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-dark bg-dark shadow-sm sticky-top">
        <div class="container">
            <a href="manager_dashboard.php"
                class="navbar-brand fw-bold d-flex align-items-center gap-2 text-decoration-none text-white">
                <img src="assets/labaratory_logo_white.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                <span>LAB<span class="text-primary">Assistance</span></span>
            </a>

            <div class="d-flex align-items-center gap-2">
                <span class="btn btn-sm btn-light border shadow-sm rounded-pill d-sm-inline pe-none">
                    <i class="bi bi-person-circle text-primary me-1"></i> Hi,
                    <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                </span>

                <a href="manager_dashboard.php" class="btn btn-sm btn-outline-info rounded-pill"
                    title="Return to Dashboard">
                    <i class="bi bi-house-door"></i>
                </a>

                <a href="staff_login.php" class="btn btn-sm btn-outline-danger rounded-pill" title="Logout">
                    <i class="bi bi-box-arrow-right"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="container page-container mt-4">
        <h3 class="fw-bold mb-0 text-dark">Orders History</h3><br>

        <?php if (!empty($grouped_orders)): ?>
            <?php foreach ($grouped_orders as $month_year => $orders): ?>
                <?php $report = $report_lookup[$month_year] ?? null; ?>
                <div class="mb-5 d-md-block">

                    <!-- Month Header -->
                    <div class="py-3 px-3 month-title d-flex justify-content-between align-items-center">
                        <div>
                            </i><?php echo $month_year; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <!-- Generate Report button -->
                            <button class="btn btn-sm btn-success rounded-pill fw-bold"
                                onclick="confirmGenerate('<?php echo $month_year; ?>')">
                                <?php echo $report ? 'Regenerate Report' : 'Generate Report'; ?>
                            </button>

                            <!-- show Clear button if report exists -->
                            <?php if ($report): ?>
                                <button class="btn btn-sm btn-danger rounded-pill fw-bold"
                                    onclick="confirmClear('<?php echo $month_year; ?>')">
                                    </i> Clear Month
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sales Report -->
                    <?php if ($report): ?>
                        <div class="d-flex flex-wrap gap-3 px-3 py-3"
                            style="border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                            <div class="stat-box text-center">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Total Orders</div>
                                <div class="fs-5 fw-bold"><?php echo $report['total_orders']; ?></div>
                            </div>
                            <div class="stat-box text-center">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Total Revenue</div>
                                <div class="fs-5 fw-bold">₱<?php echo number_format($report['total_revenue'], 2); ?></div>
                            </div>
                            <div class="stat-box text-center">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Detergents</div>
                                <div class="fs-5 fw-bold"><?php echo $report['detergent_count']; ?></div>
                            </div>
                            <div class="stat-box text-center">
                                <div class="text-muted small text-uppercase fw-bold mb-1">Softeners</div>
                                <div class="fs-5 fw-bold"><?php echo $report['softener_count']; ?></div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Table -->
                    <div class="overflow-hidden shadow-sm"
                        style="border-radius: 0 0 12px 12px; border: 1px solid #dee2e6; border-top: none; background: #fff;">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-custom-header">
                                <tr class="small text-uppercase text-muted">
                                    <th>Date Ordered</th>
                                    <th>Order ID</th>
                                    <th>Customer</th>
                                    <th>Services Requested</th>
                                    <th>Service Fee</th>
                                    <th>Payment Status</th>
                                    <th>Laundry Status</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($orders as $row): ?>
                                    <!-- Main Row -->
                                    <tr>
                                        <td><?php echo date('M d, g:i A', strtotime($row['created_at'])); ?></td>
                                        <td><?php echo $row['order_id']; ?></td>
                                        <td><?php echo $row['customer_name']; ?></td>
                                        <td><?php echo $row['services_requested']; ?></td>
                                        <td>₱<?php echo $row['final_price']; ?></td>
                                        <td><?php echo $row['payment_status']; ?></td>
                                        <td><?php echo $row['status']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-secondary rounded-pill"
                                                onclick="toggleDetails('details-<?php echo $row['order_id']; ?>', this)">
                                                <i class="bi bi-chevron-down me-1"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Hidden Detail Row -->
                                    <tr id="details-<?php echo $row['order_id']; ?>"
                                        style="display: none; background-color: #f8f9fa;">
                                        <td colspan="9">
                                            <div class="px-3 py-2 d-flex flex-wrap gap-4 small">
                                                <div>
                                                    <span class="text-muted text-uppercase fw-bold">Customer</span><br>
                                                    ID: <?php echo $row['customer_id']; ?><br>
                                                    Name: <?php echo $row['customer_name']; ?>
                                                </div>
                                                <div>
                                                    <span class="text-muted text-uppercase fw-bold">Services</span><br>
                                                    Services: <?php echo $row['services_requested']; ?><br>
                                                    Supplies: <?php echo $row['supplies_requested']; ?><br>
                                                    Bags: <?php echo $row['bag_counts']; ?><br>
                                                    Note: <?php echo $row['customer_note']; ?>
                                                </div>
                                                <div>
                                                    <span class="text-muted text-uppercase fw-bold">Pricing</span><br>
                                                    Estimate: ₱<?php echo $row['estimated_price']; ?><br>
                                                    Additional: ₱<?php echo $row['additional_fees']; ?><br>
                                                    <strong>Final: ₱<?php echo $row['final_price']; ?></strong>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                No orders found.
            </div>
        <?php endif; ?>

    </div>

    <!-- Deletion pop-up -->
    <div class="modal fade" id="clearModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        Confirm Deletion
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-muted">
                    Delete all orders from <strong id="clearMonthLabel"></strong>?
                    This action cannot be undone.
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <form method="POST" action="orders_history.php">
                        <input type="hidden" name="delete_month" id="clearMonthInput">
                        <button type="submit" class="btn btn-danger rounded-pill fw-bold">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Modal -->
    <div class="modal fade" id="generateModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold">
                        Generate Sales Report
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-muted">
                    Generate a sales report for <strong id="generateMonthLabel"></strong>?
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <form method="POST" action="orders_history.php">
                        <input type="hidden" name="generate_month_year" id="generateMonthInput">
                        <button type="submit" class="btn btn-success rounded-pill fw-bold">
                            Generate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

</html>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function toggleDetails(id, btn) {
        const row = document.getElementById(id);
        const isHidden = row.style.display === 'none';

        row.style.display = isHidden ? 'table-row' : 'none';
        btn.innerHTML = isHidden ?
            '<i class="bi bi-chevron-up me-1"></i>' :
            '<i class="bi bi-chevron-down me-1"></i>';
    }

    function confirmGenerate(monthYear) {
        document.getElementById('generateMonthLabel').textContent = monthYear;
        document.getElementById('generateMonthInput').value = monthYear;
        new bootstrap.Modal(document.getElementById('generateModal')).show();
    }

    function confirmClear(monthYear) {
        document.getElementById('clearMonthLabel').textContent = monthYear;
        document.getElementById('clearMonthInput').value = monthYear;
        new bootstrap.Modal(document.getElementById('clearModal')).show();
    }
</script>