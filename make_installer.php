<?php
// make_installer.php

// Strict error reporting for development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- Configuration ---
$installerDir = 'install';
$schemaFile = 'schema.sql'; // Corrected path to be relative to the project root for make_installer.php
$configFile = 'config.php'; // Corrected path to be relative to the project root for make_installer.php
$htAccessFile = $installerDir . '/.htaccess';
$cssFile = $installerDir . '/style.css';
$logoFile = $installerDir . '/logo.svg';

// --- Helper Functions ---
function createFile($path, $content) {
    if (file_put_contents($path, $content) === false) {
        echo "Error: Could not write to file '$path'. Check permissions.\n";
        exit(1);
    }
    echo "Successfully created: $path\n";
}

// --- Main Generator Logic ---
if (is_dir($installerDir)) {
    echo "Warning: '$installerDir' directory already exists. Overwriting files...\n";
} else {
    if (!mkdir($installerDir, 0755, true)) {
        echo "Error: Could not create directory '$installerDir'. Check permissions.\n";
        exit(1);
    }
    echo "Successfully created directory: $installerDir\n";
}

// --- File Content Definitions ---

// .htaccess for security
$htAccessContent = <<<EOT
# Disable directory listing
Options -Indexes

# Deny access to all files except index.php and style.css
<Files "*">
    Require all denied
</Files>
<FilesMatch "^(index\.php|style\.css|logo\.svg)$">
    Require all granted
</FilesMatch>
EOT;

// Professional CSS for the installer
$cssContent = <<<EOT
body {
    background-color: #f0f2f5;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
.installer-container {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 600px;
    overflow: hidden;
}
.installer-header {
    background-color: #4A90E2; /* A modern blue */
    color: white;
    padding: 2rem;
    text-align: center;
}
.installer-header h1 {
    margin: 0;
    font-size: 1.8rem;
    font-weight: 600;
}
.installer-header p {
    margin: 0.5rem 0 0;
    opacity: 0.9;
}
.installer-content {
    padding: 2.5rem;
}
.step-content {
    display: none;
}
.step-content.active {
    display: block;
}
.form-label {
    font-weight: 600;
}
.btn-primary {
    background-color: #4A90E2;
    border-color: #4A90E2;
    padding: 0.75rem 1.5rem;
    font-size: 1rem;
    font-weight: 600;
    transition: background-color 0.2s, border-color 0.2s;
}
.btn-primary:hover {
    background-color: #357ABD;
    border-color: #357ABD;
}
.list-group-item {
    border-color: #e9ecef;
}
.badge {
    font-size: 0.9rem;
    padding: 0.5em 0.8em;
}
.is-valid {
    border-color: #28a745 !important;
}
.is-invalid {
    border-color: #dc3545 !important;
}
.alert {
    margin-top: 1rem;
}
.spinner-border {
    width: 1.2rem;
    height: 1.2rem;
}
.text-success {
    color: #28a745 !important;
}
.text-danger {
    color: #dc3545 !important;
}
EOT;

// SVG Logo for a professional touch
$logoContent = <<<EOT
<svg width="60" height="60" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" fill="white">
  <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
</svg>
EOT;


// install/index.php content
$indexContent = <<<'EOT'
<?php
// Installer Disabled Check
if (file_exists('../config.php')) {
    die("\n        <div style='text-align: center; padding: 40px; font-family: sans-serif; background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; margin: 20px;'>\n            <h2>Installer Disabled</h2>\n            <p>The configuration file (<code>config.php</code>) already exists.</p>\n            <p>For security reasons, the installer has been disabled. To re-run the installation, you must first delete <code>config.php</code> from the root directory.</p>\n        </div>\n    ");
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
EOT;

// install/process.php content
$processContent = <<<EOT
<?php
// process.php - Handles installation logic
error_reporting(E_ALL);
ini_set('display_errors', 0); // Do not display errors to the user, handle them in JSON response
ini_set('log_errors', 1);

header('Content-Type: application/json');

// --- Helper function to send JSON response and exit ---
function send_response(\$success, \$message, \$data = []) {
    echo json_encode(['success' => \$success, 'message' => \$message, 'data' => \$data]);
    exit;
}

// --- Security Check: Abort if config.php already exists ---
if (file_exists('../config.php')) {
    send_response(false, 'Installation is already complete. config.php exists.');
}

// --- Router for actions ---
$action = $_POST['action'] ?? '';

if ($action === 'test_db') {
    handle_db_test();
} elseif ($action === 'install') {
    handle_install();
} else {
    send_response(false, 'Invalid action specified.');
}

// --- Action Handlers ---

function handle_db_test() {
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';

    if (empty($db_host) || empty($db_name) || empty($db_user)) {
        send_response(false, 'Database details cannot be empty.');
    }

    try {
        $dsn = "mysql:host={"$db_host"};dbname={"$db_name"};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        new PDO($dsn, $db_user, $db_pass, $options);
        send_response(true, 'Database connection successful!');
    } catch (PDOException $e) {
        // Provide a more user-friendly error
        if ($e->getCode() == 1049) { // Unknown database
            send_response(false, "Database '{$db_name}' does not exist. Please create it first.");
        } elseif ($e->getCode() == 1045) { // Access denied
            send_response(false, "Access denied for user '{$db_user}'. Check username/password.");
        } else {
            send_response(false, "Connection failed: " . $e->getMessage());
        }
    }
}

function handle_install() {
    // --- 1. Get POST data ---
    $db_host = $_POST['db_host'] ?? '';
    $db_name = $_POST['db_name'] ?? '';
    $db_user = $_POST['db_user'] ?? '';
    $db_pass = $_POST['db_pass'] ?? '';
    $admin_user = $_POST['admin_username'] ?? '';
    $admin_email = $_POST['admin_email'] ?? '';
    $admin_pass = $_POST['admin_password'] ?? '';

    // --- 2. Validate input ---
    if (empty($db_host) || empty($db_name) || empty($db_user) || empty($admin_user) || empty($admin_email) || empty($admin_pass)) {
        send_response(false, 'All fields are required for installation.');
    }
    if (!filter_var($admin_email, FILTER_VALIDATE_EMAIL)) {
        send_response(false, 'Invalid admin email format.');
    }

    // --- 3. Create config.php file ---
    $config_content = "<?php
// Database Configuration
define('DB_HOST', '{$db_host}');
define('DB_NAME', '{$db_name}');
define('DB_USER', '{$db_user}');
define('DB_PASS', '{$db_pass}');

// Other application settings can go here
define('APP_NAME', 'My POS Application');
define('APP_VERSION', '1.0.0');
?>";

    if (file_put_contents('../config.php', $config_content) === false) {
        send_response(false, 'Failed to create config.php. Check directory permissions.');
    }
    
    // --- 4. Connect to DB and Import Schema ---
    try {
        $dsn = "mysql:host={"$db_host"};dbname={"$db_name"};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql_schema = file_get_contents('../../schema.sql'); // Corrected path to schema.sql
        if ($sql_schema === false) {
            throw new Exception("Could not read the schema.sql file.");
        }
        $pdo->exec($sql_schema);

    } catch (Exception $e) {
        // Cleanup config file on failure
        unlink('../config.php');
        send_response(false, "Database setup failed: " . $e->getMessage());
    }
    
    // --- 5. Insert Admin User ---
    try {
        // Check if a 'users' table exists before proceeding.
        $stmt = $pdo->query("SHOW TABLES LIKE 'users'");
        if ($stmt->rowCount() == 0) {
            throw new Exception("The 'users' table was not found after running schema.sql. Please check your schema file.");
        }
        
        $hashed_password = password_hash($admin_pass, PASSWORD_DEFAULT);
        
        // IMPORTANT: Adjust the SQL query to match your 'users' table structure
        // Assuming 'username', 'password', 'email', 'role' fields
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, password, email, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$admin_user, $hashed_password, $admin_email, 'admin']);

    } catch (Exception $e) {
        // Cleanup config file on failure
        unlink('../config.php');
        send_response(false, "Failed to create admin user: " . $e->getMessage() . ". Please check your 'users' table structure in schema.sql.");
    }

    // --- Success ---
    send_response(true, 'Installation successful!');
}
?>

EOT;

// --- File Creation ---
createFile("$installerDir/index.php", $indexContent);
createFile("$installerDir/process.php", $processContent);
createFile($htAccessFile, $htAccessContent);
createFile($cssFile, $cssContent);
createFile($logoFile, $logoContent);

echo "\n--- Generator Finished ---\n";
echo "The 'install' directory has been created.\n";
echo "To begin installation, navigate to '{$installerDir}/index.php' in your browser.\n";

?>