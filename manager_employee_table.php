<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Manager') {
    header("Location: employee_login.php");
    exit();
}
require 'backend/db_conn.php';
$result = $conn->query("SELECT * FROM `User` WHERE role = 'Employee'");
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
    </style>
</head>

<body class="bg-light">

    <nav class="navbar navbar-light bg-dark shadow-sm sticky-top">
        <div class="container d-flex justify-content-between align-items-center">
            <span class="navbar-brand fw-bold text-white mb-0 d-flex align-items-center gap-2">
                <img src="assets/labaratory_logo_white.png" alt="LABAssistance Logo" style="height: 28px; width: auto;">
                <span>LAB<span class="text-primary">Assistance</span></span>
            </span>
            <a href="manager_dashboard.php" class="btn btn-sm btn-outline-light rounded-pill">
                <i class="bi bi-arrow-left me-1"></i> <span class="d-none d-sm-inline">Dashboard</span>
            </a>
        </div>
    </nav>

    <div class="container page-container mt-4">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0 text-dark">Employee Roster</h3>
            <button class="btn btn-primary rounded-pill shadow-sm fw-bold px-3" data-bs-toggle="modal" data-bs-target="#employeeModal" onclick="resetForm()">
                <i class="bi bi-person-plus-fill me-1"></i> Add <span class="d-none d-sm-inline">Employee</span>
            </button>
        </div>

        <div class="app-card p-0 overflow-hidden shadow-sm d-none d-md-block mb-4" style="border-radius: 12px; border: 1px solid #dee2e6; background: #fff;">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-custom-header">
                    <tr class="small text-uppercase text-muted">
                        <th class="ps-4 py-3">ID</th>
                        <th class="py-3">Name</th>
                        <th class="py-3">Email</th>
                        <th class="text-end pe-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result->data_seek(0);
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                            <tr>
                                <td class="ps-4 text-muted fw-bold">#<?php echo $row['user_id']; ?></td>
                                <td class="fw-bold text-dark">
                                    <i class="bi bi-person-circle text-primary me-2 fs-5 align-middle"></i>
                                    <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($row['email']); ?></td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 me-2"
                                        onclick="editEmployee(<?php echo $row['user_id']; ?>, '<?php echo htmlspecialchars($row['first_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['last_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </button>

                                    <a href="backend/employee_crud.php?delete=<?php echo $row['user_id']; ?>"
                                        class="btn btn-sm btn-outline-danger rounded-pill px-3"
                                        onclick="return confirm('Are you sure you want to delete this employee?');"
                                        title="Delete Employee">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile;
                    else: ?>
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="bi bi-people display-4 d-block mb-3 opacity-50"></i>
                                No employees found.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-md-none">
            <?php
            $result->data_seek(0);
            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
            ?>
                    <div class="app-card mb-3 p-3 shadow-sm" style="border-radius: 12px; border: 1px solid #dee2e6; background: #fff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold text-dark fs-6">
                                <i class="bi bi-person-circle text-primary me-1"></i>
                                <?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?>
                            </span>
                            <span class="badge bg-dark rounded-pill">ID: #<?php echo $row['user_id']; ?></span>
                        </div>
                        <p class="text-muted small mb-3 ms-4"><i class="bi bi-envelope me-1"></i> <?php echo htmlspecialchars($row['email']); ?></p>

                        <div class="d-flex gap-2 mt-2 pt-2 border-top">
                            <button class="btn btn-sm btn-outline-secondary flex-grow-1 rounded-pill fw-bold" onclick="editEmployee(<?php echo $row['user_id']; ?>, '<?php echo htmlspecialchars($row['first_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['last_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($row['email'], ENT_QUOTES); ?>')"
                                <i class="bi bi-pencil-square me-1"></i> Edit
                            </button>
                            <a href="backend/employee_crud.php?delete=<?php echo $row['user_id']; ?>" class="btn btn-sm btn-outline-danger flex-grow-1 rounded-pill fw-bold" onclick="return confirm('Delete this employee?');">
                                <i class="bi bi-trash me-1"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php
                endwhile;
            else:
                ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people display-4 d-block mb-3 opacity-50"></i>
                    No employees found.
                </div>
            <?php endif; ?>
        </div>

    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalTitle">Add Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body pt-4">
                    <form action="backend/employee_crud.php" method="POST" id="employeeForm" novalidate>
                        <input type="hidden" name="user_id" id="emp_id">

                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label small text-muted fw-bold text-uppercase">First Name</label>
                                <input type="text" class="form-control" name="first_name" id="emp_first_name" placeholder="Juan" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label small text-muted fw-bold text-uppercase">Last Name</label>
                                <input type="text" class="form-control" name="last_name" id="emp_last_name" placeholder="Dela Cruz" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold text-uppercase">Email</label>
                            <input type="email" class="form-control" name="email" id="emp_email" placeholder="name@example.com" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold text-uppercase">Password</label>
                            <input type="password" class="form-control" name="password" id="emp_password" autocomplete="new-password" placeholder="Password">
                            <small class="text-muted" id="passHelp">Required for new employees.</small>

                            <ul id="emp_criteria" class="list-unstyled small mt-2 mb-0" style="font-size: 0.8rem;">
                                <li id="emp_crit_len" class="text-danger"><i class="bi bi-x-circle me-1"></i>8+ characters</li>
                                <li id="emp_crit_up" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 uppercase letter</li>
                                <li id="emp_crit_low" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 lowercase letter</li>
                                <li id="emp_crit_num" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 number</li>
                                <li id="emp_crit_spec" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 special character</li>
                            </ul>
                        </div>

                        <div class="mb-4" id="confirm_password_div">
                            <label class="form-label small text-muted fw-bold text-uppercase">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" id="emp_confirm_password" placeholder="Confirm Password">
                            <small id="emp_passwordMatchText" class="text-danger d-none fw-bold mt-1"><i class="bi bi-exclamation-circle me-1"></i>Passwords do not match</small>
                        </div>

                        <div id="formError" class="alert alert-danger py-2 small d-none" role="alert">
                            <i class="bi bi-exclamation-circle me-1"></i> Please fill in all required fields.
                        </div>

                        <button type="submit" name="save_employee" class="btn-primary-app w-100 py-2 fw-bold">Save Employee</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editEmployee(id, firstName, lastName, email) {
            document.getElementById('modalTitle').innerText = "Edit Employee";
            document.getElementById('emp_id').value = id;
            // The arguments are now firstName and lastName to match these variables
            document.getElementById('emp_first_name').value = firstName;
            document.getElementById('emp_last_name').value = lastName;
            document.getElementById('emp_email').value = email;

            // Password logic for editing
            document.getElementById('emp_password').required = false;
            document.getElementById('emp_password').placeholder = "Leave blank to keep current";
            document.getElementById('passHelp').innerText = "Leave blank to keep current password.";

            // Hide error message if it was previously triggered
            document.getElementById('formError').classList.add('d-none');

            var myModal = new bootstrap.Modal(document.getElementById('employeeModal'));
            myModal.show();
        }

        function editEmployee(id, firstName, lastName, email) {
            document.getElementById('modalTitle').innerText = "Edit Employee";
            document.getElementById('emp_id').value = id;
            // The arguments are now firstName and lastName to match these variables
            document.getElementById('emp_first_name').value = firstName;
            document.getElementById('emp_last_name').value = lastName;
            document.getElementById('emp_email').value = email;

            // Password logic for editing
            document.getElementById('emp_password').required = false;
            document.getElementById('emp_password').placeholder = "Leave blank to keep current";
            document.getElementById('passHelp').innerText = "Leave blank to keep current password.";

            // Hide error message if it was previously triggered
            document.getElementById('formError').classList.add('d-none');

            var myModal = new bootstrap.Modal(document.getElementById('employeeModal'));
            myModal.show();
        }

        function resetForm() {
            document.getElementById('modalTitle').innerText = "Add Employee";
            document.getElementById('emp_id').value = "";
            document.getElementById('emp_first_name').value = "";
            document.getElementById('emp_last_name').value = "";
            document.getElementById('emp_email').value = "";
            document.getElementById('emp_password').value = "";
            document.getElementById('emp_confirm_password').value = "";

            // Password logic for new entry
            document.getElementById('emp_password').required = true;
            document.getElementById('emp_password').placeholder = "Password";
            document.getElementById('passHelp').innerText = "Required for new employees.";

            // Reset criteria styling
            validateEmpPassword();

            // Hide error message if it was previously triggered
            document.getElementById('formError').classList.add('d-none');
        }

        // Intercept form submission to show red text instead of alert
        document.getElementById('employeeForm').addEventListener('submit', function(event) {
            if (!this.checkValidity()) {
                event.preventDefault(); // Stop form from submitting
                event.stopPropagation();

                // Show the red error box
                document.getElementById('formError').classList.remove('d-none');
                document.getElementById('formError').innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Please fill in all required fields.';

                // Optional: Adds Bootstrap's native red borders to empty fields
                this.classList.add('was-validated');
            }
        });

        // Hide the error message as soon as the user starts typing again
        document.getElementById('employeeForm').addEventListener('input', function() {
            document.getElementById('formError').classList.add('d-none');
        });
        const empPwd = document.getElementById('emp_password');
        const empConfPwd = document.getElementById('emp_confirm_password');
        const empMatchText = document.getElementById('emp_passwordMatchText');
        const empCriteria = document.getElementById('emp_criteria');
        const isEditing = document.getElementById('emp_id');
        const passHelp = document.getElementById('passHelp');

        function updateEmpCriterion(id, isMet) {
            const el = document.getElementById(id);
            const icon = el.querySelector('i');
            if (isMet) {
                el.classList.replace('text-danger', 'text-success');
                icon.classList.replace('bi-x-circle', 'bi-check-circle');
            } else {
                el.classList.replace('text-success', 'text-danger');
                icon.classList.replace('bi-check-circle', 'bi-x-circle');
            }
        }

        function validateEmpPassword() {
            const p = empPwd.value;
            const c = empConfPwd.value;

            const hasLen = p.length >= 8;
            const hasUp = /[A-Z]/.test(p);
            const hasLow = /[a-z]/.test(p);
            const hasNum = /[0-9]/.test(p);
            const hasSpec = /[^A-Za-z0-9]/.test(p);

            updateEmpCriterion('emp_crit_len', hasLen);
            updateEmpCriterion('emp_crit_up', hasUp);
            updateEmpCriterion('emp_crit_low', hasLow);
            updateEmpCriterion('emp_crit_num', hasNum);
            updateEmpCriterion('emp_crit_spec', hasSpec);

            const isStrong = hasLen && hasUp && hasLow && hasNum && hasSpec;

            // Check match
            if (c.length > 0 && p !== c) {
                empMatchText.classList.remove('d-none');
            } else {
                empMatchText.classList.add('d-none');
            }

            // If editing and blank, hide the help text and consider valid
            if (isEditing.value !== "" && p === "") {
                passHelp.classList.remove('d-none');
                return true;
            }

            passHelp.classList.add('d-none');

            if (p === "" && isEditing.value === "") return false; // Block new employee empty password

            return isStrong && (p === c);
        }

        empPwd.addEventListener('input', validateEmpPassword);
        empConfPwd.addEventListener('input', validateEmpPassword);

        document.getElementById('employeeForm').addEventListener('submit', function(event) {
            if (!validateEmpPassword()) {
                event.preventDefault();
                document.getElementById('formError').classList.remove('d-none');
                document.getElementById('formError').innerHTML = '<i class="bi bi-exclamation-circle me-1"></i> Please ensure passwords match and meet requirements.';
            }
        });
    </script>
</body>

</html>