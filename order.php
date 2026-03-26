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
                            <input class="form-check-input me-3" type="checkbox" id="checkWash" name="checkWash" value="55" style="transform: scale(1.3);">
                            <div class="flex-grow-1 fw-bold">Wash</div>
                            <span class="fw-bold text-dark">₱55<small class="text-muted fw-normal">/load</small></span>
                        </label>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card cursor-pointer w-100">
                            <input class="form-check-input me-3" type="checkbox" id="checkDry" name="checkDry" value="60" style="transform: scale(1.3);">
                            <div class="flex-grow-1 fw-bold">Dry</div>
                            <span class="fw-bold text-dark">₱60<small class="text-muted fw-normal">/load</small></span>
                        </label>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card cursor-pointer w-100">
                            <input class="form-check-input me-3" type="checkbox" id="checkFold" name="checkFold" value="30" style="transform: scale(1.3);">
                            <div class="flex-grow-1 fw-bold">Fold</div>
                            <span class="fw-bold text-dark">₱30<small class="text-muted fw-normal">/load</small></span>
                        </label>

                        <div id="errorWetClothes" class="alert alert-danger mt-2 d-none small">
                            <i class="bi bi-exclamation-circle-fill me-1"></i> Wet clothes cannot be folded!
                        </div>
                    </div>

                    <div id="suppliesSection" class="mb-4 opacity-50" style="pointer-events: none;">
                        <h6 id="suppliesHeader" class="text-muted fw-bold text-uppercase small mb-3 ps-1">Add-ons (Wash only)</h6>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card w-100">
                            <input class="form-check-input me-3" type="checkbox" id="supplyDetergent" name="supplyDetergent" value="20" disabled>
                            <div class="flex-grow-1">Detergent</div>
                            <span class="text-muted small">+₱20</span>
                        </label>

                        <label class="app-card p-3 mb-2 d-flex align-items-center service-select-card w-100">
                            <input class="form-check-input me-3" type="checkbox" id="supplySoftener" name="supplySoftener" value="10" disabled>
                            <div class="flex-grow-1">Softener</div>
                            <span class="text-muted small">+₱10</span>
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
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Total Estimate</span>
                                <span class="fs-4 fw-bold text-dark" id="totalPrice">₱0.00</span>
                            </div>
                            <button type="submit" id="btnPlaceOrder" class="btn btn-primary-app py-3 fw-bold" disabled>
                                Confirm Order
                            </button>
                        </div>
                    </div>

                    <div style="height: 100px;"></div>

                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/order.js"></script>
</body>

</html>