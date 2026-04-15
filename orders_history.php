<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: employee_login.php");
    exit();
}
require 'backend/db_conn.php';
$result = $conn->query("SELECT * FROM `Order` ORDER BY created_at DESC");

// Group orders per month
$grouped_orders = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $month_year = date('F Y', strtotime($row['created_at']));
        $grouped_orders[$month_year][] = $row;
    }
}

// Delete orders from month
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_month'])) {
    $month_year = $_POST['delete_month'];
    $date = DateTime::createFromFormat('F Y', $month_year);

    if ($date) {
        $year  = $date->format('Y');
        $month = $date->format('m');

        $stmt = $conn->prepare("DELETE FROM `Order` WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?");
        $stmt->bind_param("ss", $year, $month);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: orders_history.php");
    exit();
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
            <a href="manager_dashboard.php" class="navbar-brand fw-bold d-flex align-items-center gap-2 text-decoration-none text-white">
                <img src="assets/labaratory_logo_white.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                <span>LAB<span class="text-primary">Assistance</span></span>
            </a>

            <div class="d-flex align-items-center gap-2">
                <span class="btn btn-sm btn-light border shadow-sm rounded-pill d-none d-sm-inline pe-none">
                    <i class="bi bi-person-circle text-primary me-1"></i> Hi, <?php echo htmlspecialchars($_SESSION['first_name']); ?>
                </span>

                <a href="manager_dashboard.php" class="btn btn-sm btn-outline-info rounded-pill" title="Return to Dashboard">
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
                <div class="mb-5 d-none d-md-block">
                    <!-- Month-Year Title -->
                    <div class="py-3 month-title d-flex justify-content-between align-items-center">
                        <div>
                            <i class="me-2"></i><?php echo $month_year; ?>
                            <span class="badge bg-secondary ms-2"><?php echo count($orders); ?> order(s)</span>
                        </div>
                        <button
                            class="btn btn-danger rounded-pill shadow-sm fw-bold px-3"
                            onclick="confirmClear('<?php echo $month_year; ?>')">
                            <i class="bi me-1"></i> Clear Month
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="overflow-hidden shadow-sm" style="border-radius: 0 0 12px 12px; border: 1px solid #dee2e6; border-top: none; background: #fff;">
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
                                            <button
                                                class="btn btn-sm btn-outline-secondary rounded-pill"
                                                onclick="toggleDetails('details-<?php echo $row['order_id']; ?>', this)">
                                                <i class="bi bi-chevron-down me-1"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Hidden Detail Row -->
                                    <tr id="details-<?php echo $row['order_id']; ?>" style="display: none; background-color: #f8f9fa;">
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
    <div class="modal fade" id="clearModal" tabindex="-1" aria-labelledby="clearModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-content">
            <div class="modal-body text-muted">
                Are you sure you want to delete all orders from <strong id="modalMonthLabel"></strong>?
                This action cannot be undone.
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">
                    Cancel
                </button>
                <form method="POST" action="orders_history.php" id="clearForm">
                    <input type="hidden" name="delete_month" id="deleteMonthInput">
                    <button type="submit" class="btn btn-danger rounded-pill">
                        Yes, Delete
                    </button>
                </form>
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

    function confirmClear(monthYear) {
        document.getElementById('modalMonthLabel').textContent = monthYear;
        document.getElementById('deleteMonthInput').value = monthYear;
        new bootstrap.Modal(document.getElementById('clearModal')).show();
    }
</script>