<?php
// process.php - Handles installation logic
error_reporting(E_ALL);
ini_set('display_errors', 0); // Do not display errors to the user, handle them in JSON response
ini_set('log_errors', 1);

header('Content-Type: application/json');

// --- Helper function to send JSON response and exit ---
function send_response($success, $message, $data = []) {
    echo json_encode(['success' => $success, 'message' => $message, 'data' => $data]);
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
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
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
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sql_schema = file_get_contents('../schema.sql');
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
        // This query is now aligned with the provided schema.sql
        $stmt = $pdo->prepare(
            "INSERT INTO users (username, password_hash, full_name_encrypted, email_encrypted, role) VALUES (?, ?, ?, ?, ?)"
        );
        // Using admin_user for full_name_encrypted as a placeholder during installation.
        $stmt->execute([$admin_user, $hashed_password, $admin_user, $admin_email, 'admin']);

    } catch (Exception $e) {
        // Cleanup config file on failure
        unlink('../config.php');
        send_response(false, "Failed to create admin user: " . $e->getMessage() . ". Please check your 'users' table structure in schema.sql.");
    }

    // --- Success ---
    send_response(true, 'Installation successful!');
}
?>
