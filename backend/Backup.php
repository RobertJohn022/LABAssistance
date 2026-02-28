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

        // Get time from database
        function initTimer(loadId) {
            clearInterval(timerInterval); // Reset timer
            fetch(`backend/timer_action.php?action=get&load_id=${loadId}`) // Remaining time from db
                .then(res => res.json())
                .then(data => {
                    timeLeft = data.remaining;
                    startCountdown();
                });
        }

        // Start timer 
        function startCountdown() {
            const loadId = document.getElementById('modalLoadId').value; // Get current ID
            updateTimerDisplay();

            timerInterval = setInterval(() => {
                // Decrease each second
                if (!isPaused && timeLeft > 0) {
                    timeLeft--;
                    updateTimerDisplay();

                    // TRIGGER: When timer hits exactly 0
                    if (timeLeft === 0) {
                        clearInterval(timerInterval);
                        autoCycleStatus(loadId);
                    }
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
                        // 1. Update the dropdown to the NEW status
                        document.getElementById('modalStatusSelect').value = data.next_status;

                        // 2. Visual Alert
                        console.log("Status advanced to: " + data.next_status);

                        // 3. OPTIONAL: Automatically start a new 1-minute timer for the next phase
                        if (!data.is_final) {
                            updateTimerDB('reset');
                        } else {
                            document.getElementById('modalTimerDisplay').innerText = "DONE";
                        }
                    }
                });
        }

        // Display
        function updateTimerDisplay() {
            const el = document.getElementById('modalTimerDisplay');
            if (timeLeft <= 0) {
                el.innerText = "00:00";
                return;
            }
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