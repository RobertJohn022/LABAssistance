<?php
if (!empty($groupedOrders)):
    foreach ($orderGroups as $group):
        foreach ($group['orders'] as $order_id => $order):
            $displayStatus = $order['order_status'];

            $allAwaiting = true;
            $hasLoads = (count($order['loads']) > 0);
            foreach ($order['loads'] as $l) {
                if ($l['status'] !== 'Awaiting Pickup') {
                    $allAwaiting = false;
                    break;
                }
            }
            if ($hasLoads && $allAwaiting && $displayStatus !== 'Completed' && $displayStatus !== 'Cancelled') {
                $displayStatus = 'Awaiting Pickup';
            }

            $masterBadgeClass = 'bg-primary';
            if ($displayStatus === 'Completed' || $displayStatus === 'Awaiting Pickup') {
                $masterBadgeClass = 'bg-success';
            } elseif ($displayStatus === 'Cancelled') {
                $masterBadgeClass = 'bg-danger';
            }
?>
            <div class="modal fade" id="modal<?php echo $order_id; ?>" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content border-0 rounded-4">
                        <div class="modal-header border-0 pb-0 pt-4 px-4">
                            <h5 class="modal-title fw-bold">Track Order Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <span class="text-muted small d-block text-uppercase fw-bold">Tracking Number</span>
                                    <span class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($order['tracking_code'] ?? 'N/A'); ?></span>
                                </div>
                                <span class="badge <?php echo $masterBadgeClass; ?> fs-6 px-3 py-2 rounded-pill"><?php echo $displayStatus; ?></span>
                            </div>

                            <div class="mb-3">
                                <span class="text-muted small d-block fw-bold">Customer Name</span>
                                <span class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($order['customer_name']); ?></span>
                            </div>

                            <h6 class="fw-bold text-uppercase small text-muted mb-3">Track Bag Progress</h6>
                            <div class="mb-4">
                                <?php if ($hasLoads): ?>
                                    <?php foreach ($order['loads'] as $load): ?>
                                        <div class="tracking-card rounded-3 p-3 mb-2 shadow-sm border">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div class="fw-bold text-dark">
                                                    <i class="bi bi-bag-check text-primary me-1"></i> <?php echo htmlspecialchars($load['bag_label']); ?>
                                                </div>
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
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $s; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="text-muted small text-center bg-light p-3 rounded">No bags added yet.</div>
                                <?php endif; ?>
                            </div>

                            <h6 class="fw-bold text-uppercase small text-muted mb-3">Order Information</h6>
                            <div class="order-summary-card rounded-3 p-3 shadow-sm mb-2">
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Services</span>
                                    <span class="fw-bold text-end"><?php echo htmlspecialchars($order['services_requested']); ?></span>
                                </div>
                                <?php if (!empty($order['supplies_requested'])): ?>
                                    <div class="d-flex justify-content-between mb-2 small">
                                        <span class="text-muted">Add-ons</span>
                                        <span class="fw-bold text-end"><?php echo htmlspecialchars($order['supplies_requested']); ?></span>
                                    </div>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between mb-2 small">
                                    <span class="text-muted">Bags</span>
                                    <span class="fw-bold text-end"><?php echo count($order['loads']); ?></span>
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
                            <button type="button" class="btn btn-light w-100 fw-bold border" data-bs-dismiss="modal">Close Details</button>
                        </div>
                    </div>
                </div>
            </div>
<?php
        endforeach;
    endforeach;
endif;
?>

<div class="modal fade" id="logsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold">Order History Logs</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-3">
                <div id="logContainer" class="bg-light p-3 rounded-3 small">
                    <span class="text-muted">Fetching logs...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addBagModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Add Extra Bags</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-4">
                <form action="backend/manage_bag.php" method="POST">
                    <input type="hidden" name="action" value="add_bag">
                    <input type="hidden" name="order_id" id="add_bag_order_id">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold text-uppercase d-block text-center">Bag Category</label>
                        <select class="form-select text-center fw-bold bg-light border-0 shadow-sm" name="bag_category" id="add_bag_category" required>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold text-uppercase d-block text-center">Number of Bags</label>
                        <div class="d-flex align-items-center gap-3 justify-content-center">
                            <button type="button" class="btn btn-outline-secondary rounded-circle shadow-sm" onclick="updateBagCount(-1)" style="width: 40px; height: 40px;"><i class="bi bi-dash-lg"></i></button>
                            <input type="number" class="form-control text-center fw-bold fs-5 border-0 bg-light shadow-sm" name="bag_quantity" id="add_bag_qty" value="1" min="1" max="10" readonly style="width: 70px;">
                            <button type="button" class="btn btn-outline-secondary rounded-circle shadow-sm" onclick="updateBagCount(1)" style="width: 40px; height: 40px;"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary-app w-100 py-2 fw-bold">Add to Queue</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Create a dictionary of order services to check if an order is Fold Only
    const orderServices = {};
    <?php
    if (!empty($groupedOrders)):
        foreach ($orderGroups as $group):
            foreach ($group['orders'] as $order_id => $order):
                $safeServices = addslashes($order['services_requested']);
                echo "orderServices['$order_id'] = '$safeServices';\n";
            endforeach;
        endforeach;
    endif;
    ?>

    function updateBagCount(change) {
        const input = document.getElementById('add_bag_qty');
        let current = parseInt(input.value) || 1;
        let newValue = current + change;
        if (newValue >= 1 && newValue <= 10) {
            input.value = newValue;
        }
    }

    var addBagModalObj = new bootstrap.Modal(document.getElementById('addBagModal'));

    function openAddBagModal(orderId) {
        document.getElementById('add_bag_qty').value = 1;
        document.getElementById('add_bag_order_id').value = orderId;

        const services = orderServices[orderId] || '';
        const isWash = services.toLowerCase().includes('wash');
        const isDry = services.toLowerCase().includes('dry');
        const isFold = services.toLowerCase().includes('fold');
        const isFoldOnly = isFold && !isWash && !isDry;

        const categorySelect = document.getElementById('add_bag_category');
        categorySelect.innerHTML = ''; // Clear previous options

        // Populate dropdown based on order requirements
        if (isFoldOnly) {
            categorySelect.innerHTML = '<option value="Fold Only">Fold Only</option>';
        } else {
            categorySelect.innerHTML = '<option value="Colored">Colored</option><option value="White">White</option>';
        }

        addBagModalObj.show();
    }

    // Track Order UI Function
    // Track Order UI Function
    function trackOrder() {
        let input = document.getElementById('searchTracking').value.toLowerCase().trim();
        if (input === '') return;

        if (input.startsWith('#')) {
            input = input.substring(1);
        }

        const cards = document.querySelectorAll('.order-card-item');
        let found = false;

        for (let i = 0; i < cards.length; i++) {
            const card = cards[i];
            const tracking = card.getAttribute('data-tracking').toLowerCase();
            const rawOrderId = card.getAttribute('data-orderid');
            const orderId = rawOrderId.toLowerCase();
            const textContent = card.innerText.toLowerCase();

            if (tracking === input || orderId === input || textContent.includes(input)) {
                found = true;

                // 1. Open the Status Folder (e.g. In Progress)
                const accordionCollapse = card.closest('.accordion-collapse');
                if (accordionCollapse && !accordionCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getOrCreateInstance(accordionCollapse);
                    bsCollapse.show();
                }

                // 2. Open the individual Order Dropdown to show the bags!
                const orderCollapse = document.getElementById('orderCollapse' + rawOrderId);
                if (orderCollapse && !orderCollapse.classList.contains('show')) {
                    const bsOrderCollapse = bootstrap.Collapse.getOrCreateInstance(orderCollapse);
                    bsOrderCollapse.show();
                }

                // 3. Open the main modal tracker
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
                text: 'We could not find an active or past order with that ID, tracking number, or name.',
                confirmButtonColor: '#0d6efd'
            });
        }
    }

    document.getElementById('searchTracking').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            trackOrder();
        }
    });

    function filterOrders() {
        const input = document.getElementById('searchTracking').value.toLowerCase();
        const cards = document.querySelectorAll('.order-group-card');
        cards.forEach(card => {
            const text = card.innerText.toLowerCase();
            const tracking = card.getAttribute('data-tracking').toLowerCase();
            if (text.includes(input) || tracking.includes(input)) {
                card.style.display = "";
            } else {
                card.style.display = "none";
            }
        });
    }

    function togglePriceEdit(orderId) {
        const viewDiv = document.getElementById('price-view-' + orderId);
        const formDiv = document.getElementById('price-form-' + orderId);

        if (viewDiv.classList.contains('d-none')) {
            viewDiv.classList.remove('d-none');
            viewDiv.classList.add('d-flex');
            formDiv.classList.remove('d-flex');
            formDiv.classList.add('d-none');
        } else {
            viewDiv.classList.remove('d-flex');
            viewDiv.classList.add('d-none');
            formDiv.classList.remove('d-none');
            formDiv.classList.add('d-flex');
        }
    }

    function handleDeleteBag(form) {
        const totalBags = parseInt(form.getAttribute('data-total-bags'));
        if (totalBags <= 1) {
            Swal.fire({
                title: 'Cancel Order?',
                text: 'This is the last bag in the order. Deleting it will cancel the entire order regardless of its current state. Are you sure you want to cancel the order?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel order!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.querySelector('input[name="action"]').value = 'cancel_order';
                    form.submit();
                }
            });
        } else {
            Swal.fire({
                title: 'Delete Bag?',
                text: 'Are you sure you want to delete this bag? The price will automatically decrease.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    }

    function submitNextPhase(event, form) {
        event.preventDefault();

        // Check what the button text was to customize the popup
        const buttonText = event.submitter ? event.submitter.innerText : '';
        let alertText = 'Moving to the next phase...';

        if (buttonText.includes('Awaiting Pickup')) {
            alertText = 'Finishing up...<br><i>Sending completion report to customer...</i>';
        }

        Swal.fire({
            title: 'Updating Status',
            html: alertText,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);
        const fetchUrl = form.getAttribute('action');

        fetch(fetchUrl, {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const rawText = await response.text();
                try {
                    return JSON.parse(rawText);
                } catch (err) {
                    console.error("Server Error Response:", rawText);
                    throw new Error("Server did not return valid JSON.");
                }
            })
            .then(data => {
                if (data.email_sent) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Ready & Email Sent!',
                        text: 'Completion report successfully sent to the customer.',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    location.reload();
                }
            })
            .catch(err => {
                console.error('AJAX Catch Error:', err);
                Swal.fire('Error', 'Failed to update status. Please check the console logs.', 'error')
                    .then(() => {
                        location.reload();
                    });
            });
    }

    function submitCompleteOrder(event, form) {
        event.preventDefault();

        Swal.fire({
            title: 'Completing Order',
            html: 'Marking order as picked up...<br><i>Sending thank you email to customer...</i>',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);
        const fetchUrl = form.getAttribute('action');

        fetch(fetchUrl, {
                method: 'POST',
                body: formData
            })
            .then(async response => {
                const rawText = await response.text();
                try {
                    return JSON.parse(rawText);
                } catch (err) {
                    console.error("Server Error Response:", rawText);
                    throw new Error("Server did not return valid JSON.");
                }
            })
            .then(data => {
                if (data.email_sent) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Order Completed!',
                        text: 'Customer picked up the laundry. Thank you email sent!',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    location.reload();
                }
            })
            .catch(err => {
                console.error('AJAX Catch Error:', err);
                Swal.fire('Warning', 'Order completed, but the email encountered an error. Reloading...', 'info')
                    .then(() => {
                        location.reload();
                    });
            });
    }

    document.addEventListener('DOMContentLoaded', function() {
        function updateTimers() {
            const timers = document.querySelectorAll('.live-timer');
            const bars = document.querySelectorAll('.live-progress');
            const now = new Date().getTime();

            timers.forEach((timer, index) => {
                const endTimeStr = timer.getAttribute('data-end');
                const durationSecs = parseInt(timer.getAttribute('data-duration'));
                if (!endTimeStr) return;

                const endTime = new Date(endTimeStr).getTime();
                const distance = endTime - now;
                const bar = bars[index];

                if (distance <= 0) {
                    timer.innerText = "00:00 (FINISHED)";
                    timer.classList.remove('text-danger');
                    timer.classList.add('text-success');

                    if (bar) {
                        bar.style.width = "100%";
                        bar.classList.remove('bg-primary', 'progress-bar-animated', 'progress-bar-striped');
                        bar.classList.add('bg-success');
                    }
                } else {
                    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                    timer.innerText =
                        (minutes < 10 ? "0" : "") + minutes + ":" +
                        (seconds < 10 ? "0" : "") + seconds;

                    if (bar && durationSecs > 0) {
                        const distanceSecs = distance / 1000;
                        const progressPct = Math.max(0, Math.min(100, (1 - (distanceSecs / durationSecs)) * 100));
                        bar.style.width = progressPct + "%";
                    }
                }
            });
        }
        setInterval(updateTimers, 1000);
        updateTimers();
    });

    var logsModal = new bootstrap.Modal(document.getElementById('logsModal'));

    function viewLogs(orderId) {
        const container = document.getElementById('logContainer');
        container.innerHTML = '<div class="text-center text-muted spinner-border spinner-border-sm" role="status"></div> Loading...';
        logsModal.show();

        fetch('backend/fetch_logs.php?order_id=' + orderId)
            .then(response => response.text())
            .then(data => container.innerHTML = data)
            .catch(err => container.innerHTML = '<span class="text-danger">Error loading logs.</span>');
    }
</script>