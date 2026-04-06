<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Register - LABAssistance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">

                <div class="text-center mb-4">
                    <h1 class="fw-bold display-6">Create Account</h1>
                    <p class="text-muted">Join LABAssistance today</p>
                </div>

                <div class="app-card p-4 shadow-sm rounded bg-white">
                    <form action="backend/register_process.php" method="POST">
                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">First Name</label>
                                <input type="text" class="form-control" name="first_name" placeholder="First Name" required>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Last Name</label>
                                <input type="text" class="form-control" name="last_name" placeholder="Last Name" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label text-muted small fw-bold text-uppercase">Email</label>
                            <input type="email" class="form-control" name="email" placeholder="name@example.com" required>
                        </div>

                        <div class="row">
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Password</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" name="password" id="reg_pwd" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_pwd', 'eyeReg')">
                                        <i class="bi bi-eye" id="eyeReg"></i>
                                    </button>
                                </div>
                                <ul id="reg_criteria" class="list-unstyled small mt-2 mb-0" style="font-size: 0.8rem;">
                                    <li id="reg_crit_len" class="text-danger"><i class="bi bi-x-circle me-1"></i>8+ characters</li>
                                    <li id="reg_crit_up" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 uppercase letter</li>
                                    <li id="reg_crit_low" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 lowercase letter</li>
                                    <li id="reg_crit_num" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 number</li>
                                    <li id="reg_crit_spec" class="text-danger"><i class="bi bi-x-circle me-1"></i>1 special character</li>
                                </ul>
                            </div>
                            <div class="col-12 col-md-6 mb-3">
                                <label class="form-label text-muted small fw-bold text-uppercase">Confirm Password</label>
                                <div class="input-group mb-1">
                                    <input type="password" class="form-control" name="confirm_password" id="reg_conf_pwd" placeholder="••••••••" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_conf_pwd', 'eyeConf')">
                                        <i class="bi bi-eye" id="eyeConf"></i>
                                    </button>
                                </div>
                                <small id="passwordMatchText" class="text-danger d-none fw-bold"><i class="bi bi-exclamation-circle me-1"></i>Passwords do not match</small>
                            </div>
                        </div>

                        <div class="form-check mb-4 mt-2">
                            <input class="form-check-input" type="checkbox" value="" id="agreeTerms" required>
                            <label class="form-check-label text-muted small" for="agreeTerms">
                                I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal" class="text-primary fw-bold text-decoration-underline">Terms and Conditions</a>
                                <br class="d-md-none">
                                <span class="text-secondary fst-italic" style="font-size: 0.85em;">(Click to read)</span>
                            </label>
                        </div>

                        <button type="submit" name="register" id="submitBtn" class="btn btn-primary w-100 py-2 fw-bold">Register Account</button>
                    </form>
                </div>

                <div class="text-center mt-4">
                    <span class="text-muted">Already have an account?</span>
                    <a href="customer_login.php" class="text-dark fw-bold text-decoration-none">Log In</a>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="termsModalLabel">Terms and Conditions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-muted">
                    <p><strong>Effective Date:</strong> March 20, 2026</p>
                    <p>By creating an account on LABAssistance, you agree to these Terms and Conditions. If you do not agree with any part of these terms, please do not use the platform.</p>

                    <h2 class="h5 mt-4">1. Account Registration</h2>
                    <p>You are responsible for providing accurate and complete information during registration. You are also responsible for maintaining the confidentiality of your account credentials.</p>

                    <h2 class="h5 mt-4">2. Acceptable Use</h2>
                    <p>You agree to use LABAssistance only for lawful and legitimate purposes. Misuse of the platform, unauthorized access attempts, and activities that disrupt normal operations are strictly prohibited.</p>

                    <h2 class="h5 mt-4">3. Data Privacy Act of 2012 (Republic Act No. 10173)</h2>
                    <p>LABAssistance collects and processes personal data such as full name and email address for account creation, identity verification, and system communication. Data is handled in compliance with the Philippine Data Privacy Act of 2012.</p>

                    <h2 class="h5 mt-4">4. Data Protection and Security</h2>
                    <p>Reasonable administrative, technical, and physical security measures are implemented to protect personal information against unauthorized access, disclosure, alteration, or loss.</p>

                    <h2 class="h5 mt-4">5. Non-Disclosure of Personal Data</h2>
                    <p>LABAssistance does not sell or rent personal information to third parties. Data may only be disclosed when required by law or with your explicit consent.</p>

                    <h2 class="h5 mt-4">6. User Rights</h2>
                    <p>You have the right to request access, correction, or deletion of your personal data in accordance with applicable privacy laws.</p>

                    <h2 class="h5 mt-4">7. Service Changes</h2>
                    <p>LABAssistance may update or modify features and policies at any time to improve service quality, security, or legal compliance.</p>

                    <h2 class="h5 mt-4">8. Updates to These Terms</h2>
                    <p>These terms may be revised from time to time. Continued use of LABAssistance after updates means you accept the revised terms.</p>

                    <p class="mt-4 mb-0">If you have questions about these Terms and Conditions, please contact the LABAssistance support team.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-primary" data-bs-dismiss="modal">Return to Register</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Toggle password visibility
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }

        const pwd = document.getElementById('reg_pwd');
        const confPwd = document.getElementById('reg_conf_pwd');
        const matchText = document.getElementById('passwordMatchText');
        const criteriaList = document.getElementById('reg_criteria');
        const submitBtn = document.getElementById('submitBtn');

        function updateCriterion(id, isMet) {
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

        function validatePassword() {
            const p = pwd.value;
            const c = confPwd.value;

            const hasLen = p.length >= 8;
            const hasUp = /[A-Z]/.test(p);
            const hasLow = /[a-z]/.test(p);
            const hasNum = /[0-9]/.test(p);
            const hasSpec = /[^A-Za-z0-9]/.test(p);

            updateCriterion('reg_crit_len', hasLen);
            updateCriterion('reg_crit_up', hasUp);
            updateCriterion('reg_crit_low', hasLow);
            updateCriterion('reg_crit_num', hasNum);
            updateCriterion('reg_crit_spec', hasSpec);

            const isStrong = hasLen && hasUp && hasLow && hasNum && hasSpec;

            // Check match
            const isMatch = (c.length > 0 && p === c);
            if (c.length > 0 && !isMatch) {
                matchText.classList.remove('d-none');
            } else {
                matchText.classList.add('d-none');
            }

            submitBtn.disabled = !(isStrong && isMatch);
        }

        pwd.addEventListener('input', validatePassword);
        confPwd.addEventListener('input', validatePassword);
    </script>
</body>

</html>