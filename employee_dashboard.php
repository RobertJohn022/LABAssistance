<?php
session_start();
require 'backend/db_conn.php';

// 1. Auth Check
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'Employee' && $_SESSION['role'] !== 'Manager')) {
    header("Location: employee_login.php");
    exit();
}

// 2. Fetch Active Loads
$query = "SELECT pl.*, o.customer_name, o.services_requested 
          FROM `Process_Load` pl
          JOIN `Order` o ON pl.order_id = o.order_id
          WHERE pl.status != 'Completed' AND pl.status != 'Order Completed'
          ORDER BY pl.order_id DESC, pl.bag_label ASC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Staff Workspace - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="./css/design.css">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: white !important;
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.2rem;
        }

        /* Mobile Optimization */
        @media (max-width: 768px) {
            .task-row {
                background: white;
                border: 1px solid #dee2e6;
                border-radius: 12px;
                margin-bottom: 15px;
                padding: 15px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .task-header {
                display: none !important;
            }

            .mobile-label {
                display: block;
                font-size: 0.75rem;
                color: #6c757d;
                text-transform: uppercase;
                margin-bottom: 2px;
            }

            .btn-action {
                width: 100%;
                margin-top: 10px;
            }
        }

        /* Desktop Optimization */
        @media (min-width: 769px) {
            .task-row {
                border-bottom: 1px solid #dee2e6;
                padding: 15px 10px;
                transition: background-color 0.2s;
            }

            .task-row:hover {
                background-color: #fff;
            }

            .mobile-label {
                display: none;
            }

            .card-container {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                overflow: hidden;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg sticky-top shadow-sm px-3">
        <div class="container-fluid">
            <span class="navbar-brand">LABAssistance <span class="badge bg-dark text-white text-uppercase" style="font-size: 0.6em; vertical-align: middle;">Staff</span></span>
            <a href="employee_login.php" class="btn btn-outline-dark btn-sm rounded-pill px-3">Log Out</a>
        </div>
    </nav>

    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> Status updated successfully!
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container py-4">

        <div class="row mb-4 align-items-center">
            <div class="col-8">
                <h2 class="fw-bold display-6 mb-0">Task Queue</h2>
                <p class="text-muted small mb-0">Manage active laundry bags</p>
            </div>
            <div class="col-4 text-end">
                <button class="btn btn-dark shadow-sm btn-sm px-3 py-2" onclick="location.reload();">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
            </div>
        </div>

        <div class="card-container border-0">

            <div class="task-header bg-light fw-bold text-uppercase small text-muted border-bottom py-3 px-2">
                <div class="row g-0">
                    <div class="col-md-3 ps-3">Order / Bag</div>
                    <div class="col-md-2">Customer</div>
                    <div class="col-md-3">Service Info</div>
                    <div class="col-md-2">Status</div>
                    <div class="col-md-2 text-end pe-3">Action</div>
                </div>
            </div>

            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="task-row">
                        <div class="row g-0 align-items-center">

                            <div class="col-md-3 col-12 mb-2 mb-md-0 ps-md-3">
                                <span class="mobile-label">Bag ID</span>
                                <div class="d-flex align-items-center">
                                    <div class="fw-bold text-dark me-2">#<?php echo $row['order_id']; ?></div>
                                    <span class="badge bg-light text-dark border border-secondary rounded-pill">
                                        <i class="bi bi-bag-fill me-1"></i> <?php echo $row['bag_label']; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-2 col-6 mb-2 mb-md-0">
                                <span class="mobile-label">Customer</span>
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($row['customer_name']); ?></div>
                            </div>

                            <div class="col-md-2 col-6 mb-2 mb-md-0">
                                <span class="mobile-label">Status</span>
                                <?php
                                $s = $row['status'];
                                $badgeClass = 'bg-secondary';
                                if ($s == 'In Queue') $badgeClass = 'bg-dark';
                                elseif (strpos($s, 'Washing') !== false) $badgeClass = 'bg-primary';
                                elseif (strpos($s, 'Drying') !== false) $badgeClass = 'bg-warning text-dark';
                                elseif (strpos($s, 'Folding') !== false) $badgeClass = 'bg-info text-dark';
                                elseif ($s == 'Awaiting Pickup') $badgeClass = 'bg-success';
                                ?>
                                <span class="badge rounded-pill <?php echo $badgeClass; ?> px-2 py-1">
                                    <?php echo $s; ?>
                                </span>
                            </div>

                            <div class="col-md-3 col-12 mb-3 mb-md-0">
                                <span class="mobile-label">Service</span>
                                <small class="text-muted d-block text-truncate">
                                    <?php echo $row['services_requested']; ?>
                                </small>
                            </div>

                            <div class="col-md-2 col-12 text-md-end pe-md-3">
                                <button class="btn btn-dark btn-sm rounded-pill px-4 fw-bold btn-action"
                                    onclick="openUpdateModal('<?php echo $row['load_id']; ?>', '<?php echo $row['bag_label']; ?>', '<?php echo $row['status']; ?>')">
                                    Update
                                </button>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-check-circle fs-1 d-block mb-3 text-success"></i>
                    <span class="fs-5">All caught up! No active tasks.</span>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <div class="modal fade" id="statusModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold fs-4">Update Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body pt-2">
                    <p class="text-muted mb-4">Bag: <span id="modalBagLabel" class="fw-bold text-dark"></span></p>

                    <form action="backend/update_status.php" method="POST">
                        <input type="hidden" name="load_id" id="modalLoadId">

                        <div class="mb-4">
                            <label class="form-label text-uppercase small fw-bold text-muted ls-1">New Status</label>
                            <select class="form-select form-select-lg border-2" name="new_status" id="modalStatusSelect">
                                <option value="Pending Dropoff">Pending Dropoff</option>
                                <option value="In Queue">In Queue</option>
                                <option value="Washing">Washing</option>
                                <option value="Wash Complete">Wash Complete</option>
                                <option value="Drying">Drying</option>
                                <option value="Drying Complete">Drying Complete</option>
                                <option value="Folding">Folding</option>
                                <option value="Folding Complete">Folding Complete</option>
                                <option value="Awaiting Pickup">Awaiting Pickup</option>
                                <option value="Completed">Completed</option>
                            </select>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-dark btn-lg">Confirm</button>
                        </div>
                    </form>

                    <!-- TIMER -->
                    <div id="timerContainer" class="border rounded bg-white text-center">
                        <div id="modalTimerDisplay" class="display-5 mb-2">00:00</div>
                        <div class="btn-group w-100">
                            <button type="button" class="btn btn-outline-dark btn-sm" onclick="updateTimerDB('reset')">Start / Reset</button>
                            <button type="button" id="modalPauseBtn" class="btn btn-outline-dark btn-sm" onclick="togglePause()">Pause</button>
                            <button type="button" class="btn btn-outline-dark btn-sm" onclick="updateTimerDB('finish')">Finish now</button>
                        </div>
                    </div>


                    <hr class="my-4">
                    <h6 class="fw-bold small text-muted text-uppercase mb-3">Log</h6>
                    <div id="logContainer" class="bg-light p-3 rounded-3 small border" style="max-height: 150px; overflow-y: auto;">
                        <div class="text-center text-muted">Loading logs...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));

        function openUpdateModal(loadId, bagLabel, currentStatus) {
            document.getElementById('modalLoadId').value = loadId;
            document.getElementById('modalBagLabel').innerText = bagLabel;
            document.getElementById('modalStatusSelect').value = currentStatus;
            fetchLogs(loadId);
            statusModal.show();
        }

        function fetchLogs(loadId) {
            const container = document.getElementById('logContainer');
            container.innerHTML = '<div class="text-center text-muted mt-2">Loading...</div>';
            fetch('backend/fetch_logs.php?load_id=' + loadId)
                .then(response => response.text())
                .then(data => container.innerHTML = data)
                .catch(err => container.innerHTML = '<div class="text-danger text-center">Error loading logs.</div>');
        }

        // TIMER SCRIPT
        var statusModal = new bootstrap.Modal(document.getElementById('statusModal'));
        document.getElementById('statusModal').addEventListener('hidden.bs.modal', function() {
            console.log("Modal closed. Stopping background timer.");
            clearInterval(timerInterval);
        });

        let timerInterval;
        let timeLeft = 0;
        let isPaused = false;

        // Run timer
        function openUpdateModal(loadId, bagLabel, currentStatus) {
            document.getElementById('modalLoadId').value = loadId;
            document.getElementById('modalBagLabel').innerText = bagLabel;
            document.getElementById('modalStatusSelect').value = currentStatus;

            fetchLogs(loadId);
            initTimer(loadId);
            statusModal.show();
        }

        // TOGGLE PAUSE
        function togglePause() {
            const loadId = document.getElementById('modalLoadId').value;
            const action = isPaused ? 'resume' : 'pause';

            fetch(`backend/timer_action.php?action=${action}&load_id=${loadId}`)
                .then(res => res.json())
                .then(data => {
                    isPaused = !isPaused;
                    document.getElementById('modalPauseBtn').innerText = isPaused ? "Unpause" : "Pause";
                    timeLeft = data.remaining;

                    if (!isPaused) {
                        startCountdown(); // Restart the interval if resuming
                    } else {
                        clearInterval(timerInterval); // Stop interval locally if pausing
                    }
                });
        }

        // Get time from database
        function initTimer(loadId) {
            clearInterval(timerInterval); // Reset timer
            fetch(`backend/timer_action.php?action=get&load_id=${loadId}`) // Remaining time from db
                .then(res => res.json())
                .then(data => {
                    timeLeft = data.remaining;
                    isPaused = data.is_paused;
                    document.getElementById('modalPauseBtn').innerText = isPaused ? "Unpause" : "Pause";
                    updateTimerDisplay();
                    if (!isPaused && timeLeft > 0) {
                        startCountdown();
                    }
                });
        }

        // Start timer 
        function startCountdown() {
            const loadId = document.getElementById('modalLoadId').value;
            updateTimerDisplay();

            // Clear any existing interval to prevent "double speed" timers
            clearInterval(timerInterval);

            timerInterval = setInterval(() => {
                if (isPaused) return;

                if (timeLeft > 0) {
                    timeLeft--;
                    updateTimerDisplay();
                }

                // Check for zero OUTSIDE the timeLeft > 0 block
                if (timeLeft <= 0) {
                    clearInterval(timerInterval);
                    console.log("Timer hit zero. Triggering autoCycleStatus for ID:", loadId);
                    autoCycleStatus(loadId);
                }
            }, 1000);
        }

        // Update database
        function autoCycleStatus(loadId) {
            const formData = new URLSearchParams();
            formData.append('load_id', loadId);

            fetch('backend/auto_cycle_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: formData.toString()
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        console.log("DB Update Success:", data.next_status);
                        document.getElementById('modalStatusSelect').value = data.next_status;

                        // Refresh timer for the next stage if not finished
                        if (!data.is_final) {
                            updateTimerDB('reset');
                        } else {
                            document.getElementById('modalTimerDisplay').innerText = "DONE";
                        }
                    } else {
                        console.error("DB Update Failed:", data.message);
                        alert("Error: " + data.message);
                    }
                })
                .catch(err => {
                    console.error("Network/Server Error:", err);
                });
        }

        // Display
        function updateTimerDisplay() {
            const el = document.getElementById('modalTimerDisplay');
            if (timeLeft <= 0) {
                el.innerText = "00:00";
                return;
            }
            // FIX: Changed 20 to 60 for standard minutes/seconds
            let mins = Math.floor(timeLeft / 60);
            let secs = timeLeft % 60;
            el.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
        }

        // Pause
        function togglePause() {
            isPaused = !isPaused;
            document.getElementById('modalPauseBtn').innerText = isPaused ? "Unpause" : "Pause";
        }

        // Timer button functions
        function updateTimerDB(action) {
            const loadId = document.getElementById('modalLoadId').value;
            fetch(`backend/timer_action.php?action=${action}&load_id=${loadId}`)
                .then(res => res.json())
                .then(data => {
                    timeLeft = data.remaining;
                    if (action === 'reset') isPaused = false;
                    updateTimerDisplay();
                });
        }
    </script>
</body>

</html>