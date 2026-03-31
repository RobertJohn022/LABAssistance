<?php
session_start();
require 'backend/db_conn.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: staff_login.php");
    exit();
}

// Fetch Shop Status
$statusResult = $conn->query("SELECT is_shop_open FROM Shop_Status WHERE status_id = 1");
$shopData = $statusResult->fetch_assoc();
$isOpen = ($shopData && $shopData['is_shop_open'] == 1);

// Fetch Service Prices
$prices = [];
$priceQuery = $conn->query("SELECT * FROM service_prices");
if ($priceQuery) {
    while ($row = $priceQuery->fetch_assoc()) {
        $prices[$row['service_name']] = $row['price'];
    }
}
// Fallback array in case the database is empty initially
$prices = array_merge([
    'Wash' => 55.00,
    'Dry' => 60.00,
    'Fold' => 30.00,
    'Detergent' => 20.00,
    'Softener' => 10.00
], $prices);

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Manager Dashboard - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
        <div class="container">
            <span class="navbar-brand fw-bold text-white d-flex align-items-center gap-2">
                <img src="assets/labaratory_logo_white.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                <span>LAB<span class="text-primary">Assistance</span></span>
            </span>
            <div class="d-flex align-items-center gap-2">
                <span class="small text-muted d-none d-sm-inline">Hi, <?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></span>
                <a href="staff_login.php" class="btn btn-sm btn-outline-danger rounded-pill"><i class="bi bi-box-arrow-right"></i></a>
            </div>
        </div>
    </nav>

    <div class="container page-container py-5">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="app-card p-4 text-center mb-4 shadow-sm bg-white">
                    <h5 class="fw-bold text-uppercase mb-0 text-dark" style="letter-spacing: 1px;">Shop Status</h5>
                    <?php if ($isOpen): ?>
                        <h1 class="display-2 mb-0" style="color: #198754; font-weight: 800;">OPEN</h1>
                    <?php else: ?>
                        <h1 class="display-2 mb-0" style="color: #dc3545; font-weight: 800;">CLOSED</h1>
                    <?php endif; ?>
                    <p class="text-muted mb-0 fs-5 mt-1"><?php echo date("F j, Y"); ?></p>
                </div>

                <div class="d-grid gap-3 mb-4">
                    <a href="shop_status.php" class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-power me-2"></i>Open/Close Shop
                    </a>
                    <a href="employee_dashboard.php" class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-receipt me-2"></i>View Orders
                    </a>
                    <a href="manager_employee_table.php" class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-people-fill me-2"></i>View Employee Table
                    </a>
                </div>

                <div class="app-card p-4 shadow-sm bg-white rounded-3 border">
                    <h5 class="fw-bold text-uppercase mb-3 text-dark border-bottom pb-2" style="letter-spacing: 1px;">Service Rates</h5>

                    <?php if (isset($_SESSION['settings_success'])): ?>
                        <div class="alert alert-success small py-2">
                            <i class="bi bi-check-circle-fill me-1"></i> <?php echo $_SESSION['settings_success'];
                                                                            unset($_SESSION['settings_success']); ?>
                        </div>
                    <?php endif; ?>

                    <form action="backend/update_rates.php" method="POST">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-sm-4">
                                <label class="small fw-bold text-muted mb-1">Wash (₱)</label>
                                <input type="number" step="0.01" name="price_wash" class="form-control text-center fw-bold" value="<?php echo htmlspecialchars($prices['Wash']); ?>" required>
                            </div>
                            <div class="col-12 col-sm-4">
                                <label class="small fw-bold text-muted mb-1">Dry (₱)</label>
                                <input type="number" step="0.01" name="price_dry" class="form-control text-center fw-bold" value="<?php echo htmlspecialchars($prices['Dry']); ?>" required>
                            </div>
                            <div class="col-12 col-sm-4">
                                <label class="small fw-bold text-muted mb-1">Fold (₱)</label>
                                <input type="number" step="0.01" name="price_fold" class="form-control text-center fw-bold" value="<?php echo htmlspecialchars($prices['Fold']); ?>" required>
                            </div>
                        </div>

                        <h6 class="small fw-bold text-muted mt-3 mb-2 text-uppercase border-top pt-3">Add-ons</h6>
                        <div class="row g-3 mb-4">
                            <div class="col-6">
                                <label class="small fw-bold text-muted mb-1">Detergent (₱)</label>
                                <input type="number" step="0.01" name="price_detergent" class="form-control text-center fw-bold" value="<?php echo htmlspecialchars($prices['Detergent']); ?>" required>
                            </div>
                            <div class="col-6">
                                <label class="small fw-bold text-muted mb-1">Softener (₱)</label>
                                <input type="number" step="0.01" name="price_softener" class="form-control text-center fw-bold" value="<?php echo htmlspecialchars($prices['Softener']); ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold text-uppercase shadow-sm">
                            <i class="bi bi-save me-1"></i> Update Prices
                        </button>
                    </form>
                </div>

                <div style="height: 50px;"></div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>