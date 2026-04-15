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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    <a href="orders_history.php" class="btn btn-dark py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                        <i class="bi bi-receipt me-2"></i>View Orders History
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
                            <label class="form-label small text-muted fw-bold text-uppercase">Email Address</label>
                            <input type="email" class="form-control" name="email" value="<?php echo isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; ?>" required>
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
    <script>
        function fetchNotifications() {
            fetch('backend/check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const badge = document.getElementById('notifBadge');
                        const list = document.getElementById('notifList');

                        if (!badge || !list) return;

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

        fetchNotifications();
        setInterval(fetchNotifications, 5000);
    </script>
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
        });
    </script>
</body>

</html>