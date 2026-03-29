<?php
// Installer Disabled Check
if (file_exists('../config.php')) {
    die("
        <div style='text-align: center; padding: 40px; font-family: sans-serif; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; margin: 20px;'>
            <h2>Installer Disabled</h2>
            <p>The configuration file (<code>config.php</code>) already exists.</p>
            <p>For security reasons, the installer has been disabled. To re-run the installation, you must first delete <code>config.php</code> from the root directory.</p>
        </div>
    ");
}

// System Compatibility Checks
$php_version_required = '7.4.0';
$php_version_ok = version_compare(PHP_VERSION, $php_version_required, '>=');
$pdo_ok = extension_loaded('pdo_mysql');
$config_writable_ok = is_writable('../') || @mkdir('../config_test_dir', 0755);
if (isset($config_writable_ok) && is_dir('../config_test_dir')) {
    rmdir('../config_test_dir');
}

$all_checks_ok = $php_version_ok && $pdo_ok && $config_writable_ok;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Setup Wizard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="installer-container">
        <div class="installer-header">
            <img src="logo.svg" alt="Logo" style="width: 60px; height: 60px; margin-bottom: 1rem;">
            <h1>Application Setup Wizard</h1>
            <p>Welcome! Let's get your application ready.</p>
        </div>
        <div class="installer-content">
            <!-- Step 1: System Compatibility -->
            <div id="step1" class="step-content active">
                <h3 class="mb-4">Step 1: System Requirements</h3>
                <ul class="list-group">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PHP Version (>= <?php echo $php_version_required; ?>)
                        <span class="badge bg-<?php echo $php_version_ok ? 'success' : 'danger'; ?>">
                            <?php echo $php_version_ok ? 'OK (' . PHP_VERSION . ')' : 'Failed'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        PDO MySQL Extension
                        <span class="badge bg-<?php echo $pdo_ok ? 'success' : 'danger'; ?>">
                            <?php echo $pdo_ok ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Root Directory Writable
                         <span class="badge bg-<?php echo $config_writable_ok ? 'success' : 'danger'; ?>">
                            <?php echo $config_writable_ok ? 'Writable' : 'Not Writable'; ?>
                        </span>
                    </li>
                </ul>
                <?php if ($all_checks_ok): ?>
                    <p class="text-success mt-3">Great! Your system is ready for installation.</p>
                    <button id="goToStep2" class="btn btn-primary w-100 mt-3">Continue to Database Setup</button>
                <?php else: ?>
                    <p class="text-danger mt-3">Your system does not meet the minimum requirements. Please fix the issues above before proceeding.</p>
                <?php endif; ?>
            </div>

            <!-- Step 2: Database Configuration -->
            <div id="step2" class="step-content">
                <h3 class="mb-4">Step 2: Database Configuration</h3>
                <form id="dbForm">
                    <div class="mb-3">
                        <label for="db_host" class="form-label">Database Host</label>
                        <input type="text" class="form-control" id="db_host" name="db_host" value="localhost" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_name" class="form-label">Database Name</label>
                        <input type="text" class="form-control" id="db_name" name="db_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_user" class="form-label">Database User</label>
                        <input type="text" class="form-control" id="db_user" name="db_user" required>
                    </div>
                    <div class="mb-3">
                        <label for="db_pass" class="form-label">Database Password</label>
                        <input type="password" class="form-control" id="db_pass" name="db_pass">
                    </div>
                    <div id="db-alert" class="alert d-none"></div>
                    <button type="submit" id="testDbBtn" class="btn btn-primary w-100">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Test Connection & Proceed
                    </button>
                </form>
            </div>

            <!-- Step 3: Admin Setup -->
            <div id="step3" class="step-content">
                <h3 class="mb-4">Step 3: Create Admin Account</h3>
                <form id="adminForm">
                    <div class="mb-3">
                        <label for="admin_username" class="form-label">Admin Username</label>
                        <input type="text" class="form-control" id="admin_username" name="admin_username" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_email" class="form-label">Admin Email</label>
                        <input type="email" class="form-control" id="admin_email" name="admin_email" required>
                    </div>
                    <div class="mb-3">
                        <label for="admin_password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="admin_password" name="admin_password" required>
                    </div>
                    <div id="final-alert" class="alert d-none"></div>
                     <button type="submit" id="installBtn" class="btn btn-primary w-100">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Complete Installation
                    </button>
                </form>
            </div>

            <!-- Step 4: Finish -->
            <div id="step4" class="step-content text-center">
                 <h3 class="mb-4 text-success">Installation Complete!</h3>
                 <p>Your application has been installed successfully.</p>
                 <div class="alert alert-warning">
                     <strong>Security Warning:</strong> For your protection, the <code>install/</code> directory should now be deleted.
                 </div>
                 <a href="../index.php" class="btn btn-primary">Go to Login Page</a>
            </div>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const goToStep2 = document.getElementById('goToStep2');
    const dbForm = document.getElementById('dbForm');
    const adminForm = document.getElementById('adminForm');

    const steps = {
        step1: document.getElementById('step1'),
        step2: document.getElementById('step2'),
        step3: document.getElementById('step3'),
        step4: document.getElementById('step4')
    };

    function showStep(stepName) {
        Object.values(steps).forEach(s => s.classList.remove('active'));
        steps[stepName].classList.add('active');
    }

    if (goToStep2) {
        goToStep2.addEventListener('click', () => showStep('step2'));
    }

    dbForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleDbTest(new FormData(this));
    });

    adminForm.addEventListener('submit', function(e) {
        e.preventDefault();
        handleFinalInstall(new FormData(dbForm), new FormData(this));
    });

    function showAlert(alertEl, message, isSuccess) {
        const el = document.getElementById(alertEl);
        el.textContent = message;
        el.className = `alert ${isSuccess ? 'alert-success' : 'alert-danger'}`;
        el.classList.remove('d-none');
    }
    
    function toggleSpinner(btnId, show) {
        const btn = document.getElementById(btnId);
        const spinner = btn.querySelector('.spinner-border');
        if (show) {
            spinner.classList.remove('d-none');
            btn.disabled = true;
        } else {
            spinner.classList.add('d-none');
            btn.disabled = false;
        }
    }

    async function handleDbTest(formData) {
        toggleSpinner('testDbBtn', true);
        formData.append('action', 'test_db');

        try {
            const response = await fetch('process.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                showAlert('db-alert', result.message, true);
                setTimeout(() => showStep('step3'), 1000);
            } else {
                showAlert('db-alert', result.message, false);
            }
        } catch (error) {
            showAlert('db-alert', 'An unexpected error occurred. Check the console.', false);
        } finally {
            toggleSpinner('testDbBtn', false);
        }
    }
    
    async function handleFinalInstall(dbFormData, adminFormData) {
        toggleSpinner('installBtn', true);
        
        const finalData = new FormData();
        dbFormData.forEach((value, key) => finalData.append(key, value));
        adminFormData.forEach((value, key) => finalData.append(key, value));
        finalData.append('action', 'install');

        try {
            const response = await fetch('process.php', {
                method: 'POST',
                body: finalData
            });
            const result = await response.json();

            if (result.success) {
                showAlert('final-alert', result.message, true);
                setTimeout(() => showStep('step4'), 1000);
            } else {
                showAlert('final-alert', result.message, false);
            }
        } catch (error) {
            showAlert('final-alert', 'An unexpected error occurred. Check the console and server logs.', false);
        } finally {
            toggleSpinner('installBtn', false);
        }
    }
});
</script>
</body>
</html>
