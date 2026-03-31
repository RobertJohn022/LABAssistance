<?php
session_start();
require 'backend/db_conn.php';

// Fetch dynamic prices
$prices = [];
$priceQuery = $conn->query("SELECT * FROM service_prices");
while ($row = $priceQuery->fetch_assoc()) {
    $prices[$row['service_name']] = $row['price'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Place Order - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/main.css">
    <style>
        .service-select-card {
            border: 2px solid transparent;
            transition: all 0.2s;
        }

        .service-select-card:active {
            transform: scale(0.98);
        }

        input:checked+div,
        input:checked+div+span,
        label:has(input:checked) {
            border-color: var(--accent-color) !important;
            background-color: #f0f7ff;
        }
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a href="dashboard.php" class="btn btn-sm btn-light rounded-circle"><i class="bi bi-arrow-left"></i></a>
            <span class="navbar-brand mb-0 h1 fw-bold fs-5 mx-auto">New Order</span>
            <div style="width: 32px;"></div>
        </div>
    </nav>

    <div class="container page-container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-6">

                <form action="backend/process_order.php" method="POST" id="orderForm">

                    <div class="mb-4">
                        <h6 class="text-muted fw-bold text-uppercase small mb-3 ps-1">Select Services</h6>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card cursor-pointer w-100">
                            <input class="form-check-input me-3" type="checkbox" id="checkWash" name="checkWash" value="Wash" style="transform: scale(1.3);">
                            <div class="flex-grow-1 fw-bold">Wash</div>
                            <span class="fw-bold text-dark">₱<?= number_format($prices['Wash'] ?? 0, 2) ?><small class="text-muted fw-normal">/load</small></span>
                        </label>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card cursor-pointer w-100">
                            <input class="form-check-input me-3" type="checkbox" id="checkDry" name="checkDry" value="Dry" style="transform: scale(1.3);">
                            <div class="flex-grow-1 fw-bold">Dry</div>
                            <span class="fw-bold text-dark">₱<?= number_format($prices['Dry'] ?? 0, 2) ?><small class="text-muted fw-normal">/load</small></span>
                        </label>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card cursor-pointer w-100" id="foldContainer">
                            <input class="form-check-input me-3" type="checkbox" id="checkFold" name="checkFold" value="Fold" style="transform: scale(1.3);">
                            <div class="flex-grow-1 fw-bold">Fold</div>
                            <span class="fw-bold text-dark">₱<?= number_format($prices['Fold'] ?? 0, 2) ?><small class="text-muted fw-normal">/load</small></span>
                        </label>
                    </div>

                    <div id="suppliesSection" class="mb-4 opacity-50" style="pointer-events: none;">
                        <h6 id="suppliesHeader" class="text-muted fw-bold text-uppercase small mb-3 ps-1">Add-ons (Wash only)</h6>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card w-100">
                            <input class="form-check-input me-3" type="checkbox" id="supplyDetergent" name="supplyDetergent" value="Detergent" disabled>
                            <div class="flex-grow-1">Detergent</div>
                            <span class="text-muted small">+₱<?= number_format($prices['Detergent'] ?? 0, 2) ?></span>
                        </label>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card w-100">
                            <input class="form-check-input me-3" type="checkbox" id="supplySoftener" name="supplySoftener" value="Softener" disabled>
                            <div class="flex-grow-1">Softener</div>
                            <span class="text-muted small">+₱<?= number_format($prices['Softener'] ?? 0, 2) ?></span>
                        </label>
                    </div>

                    <div class="mb-4">
                        <h6 class="text-muted fw-bold text-uppercase small mb-3 ps-1">Number of Bags</h6>
                        <div class="app-card p-4">
                            <div class="row g-3" id="standardInputs">
                                <div class="col-6 text-center border-end">
                                    <label class="small text-muted fw-bold mb-2">COLORED/MIXED</label>
                                    <input type="number" id="qtyColored" name="qtyColored" class="form-control text-center fs-4 fw-bold border-0 bg-light" value="0" min="0">
                                </div>
                                <div class="col-6 text-center">
                                    <label class="small text-muted fw-bold mb-2">WHITE</label>
                                    <input type="number" id="qtyWhite" name="qtyWhite" class="form-control text-center fs-4 fw-bold border-0 bg-light" value="0" min="0">
                                </div>
                            </div>
                            <div id="foldOnlyInput" class="mt-3 border-top pt-3 d-none">
                                <label class="small text-muted fw-bold mb-2 d-block text-center">FOLD ONLY BAGS</label>
                                <input type="number" id="qtyFold" name="qtyFold" class="form-control text-center fs-4 fw-bold border-0 bg-light" value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="mb-5">
                        <h6 class="text-muted fw-bold text-uppercase small mb-3 ps-1">Instructions</h6>
                        <textarea class="form-control" name="note" rows="3" placeholder="e.g. Separate delicate items..."></textarea>
                    </div>

                    <div class="alert alert-warning mb-4" role="alert" style="background-color: #fff7cc; border-color: #ffe6a8; color: #6b5600;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Note:</strong> When weight exceeds the maximum for each load (8kg), the excess laundry will be counted towards a new load and final payment will be adjusted accordingly.
                    </div>

                    <div class="fixed-bottom bg-white border-top shadow-lg p-3">
                        <div class="container" style="max-width: 600px;">

                            <div class="d-flex align-items-start p-2 rounded-3 mb-3 border"
                                style="background-color: #fff8e1; border-color: #ffe082;">
                                <i class="bi bi-exclamation-triangle-fill text-warning me-2 mt-1" style="font-size: 0.9rem;"></i>
                                <div class="text-dark" style="font-size: 0.75rem; line-height: 1.4;">
                                    <strong>Note:</strong> This is only an initial estimate based on the bags you provided. The final price may change once our staff weighs and verifies your laundry.
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Total Estimate</span>
                                <span class="fs-4 fw-bold text-dark" id="totalPrice">₱0.00</span>
                            </div>

                            <button type="submit" id="btnPlaceOrder" class="btn btn-primary-app py-3 fw-bold w-100" disabled>
                                Confirm Order
                            </button>
                        </div>
                    </div>

                    <div style="height: 120px;"></div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const SERVICE_PRICES = {
            wash: <?= $prices['Wash'] ?? 0 ?>,
            dry: <?= $prices['Dry'] ?? 0 ?>,
            fold: <?= $prices['Fold'] ?? 0 ?>,
            detergent: <?= $prices['Detergent'] ?? 0 ?>,
            softener: <?= $prices['Softener'] ?? 0 ?>
        };
    </script>
    <script src="js/order.js"></script>
</body>

</html>