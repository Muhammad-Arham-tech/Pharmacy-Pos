<?php
require_once __DIR__ . '/Verify_License.php';
session_start();

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db_file = 'data/db.json';
$settings = [];

// Load existing settings
if (file_exists($db_file)) {
    $db_data = json_decode(file_get_contents($db_file), true);
    $settings = $db_data['settings'] ?? [];
}

// 2. PHP Processing Logic
if (isset($_POST['save_settings'])) {
    // Update settings array from POST data
    $settings['store_name'] = $_POST['store_name'] ?? '';
    $settings['store_address'] = $_POST['store_address'] ?? '';
    $settings['store_phone'] = $_POST['store_phone'] ?? '';
    $settings['store_email'] = $_POST['store_email'] ?? '';
    $settings['currency_symbol'] = $_POST['currency_symbol'] ?? '$';
    $settings['default_tax_rate'] = $_POST['default_tax_rate'] ?? '5.00';
    $settings['session_timeout'] = $_POST['session_timeout'] ?? '30';

    // Save back to DB
    if (file_exists($db_file)) {
        $db_data = json_decode(file_get_contents($db_file), true);
        $db_data['settings'] = $settings;
        file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));
    }
    
    // Update Session Immediately
    $_SESSION['store_name'] = $settings['store_name'];

    // Redirect to settings.php?status=success
    header('Location: settings.php?status=success');
    exit;
}

$store_name = $settings['store_name'] ?? 'Med-Quick';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - <?php echo htmlspecialchars($store_name); ?></title>
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>
    <div class="app-container">
        <!-- SIDEBAR (Copied from index.php) -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo"><i class="fas fa-heart-pulse"></i> <?php echo htmlspecialchars($store_name); ?></h1>
            </div>
            <ul class="sidebar-nav">
                <li><a href="index.php" class="nav-link"><i class="fas fa-cash-register"></i> POS Terminal</a></li>
                <li><a href="index.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <!-- For Settings page, we link back to index for other modules, but Settings is active here -->
                <li><a href="settings.php" class="nav-link active"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <li style="margin-top: auto; border-top: 1px solid var(--border-color);"><a href="index.php" onclick="alert('Please go to the dashboard to access support.'); return false;"><i class="fas fa-life-ring"></i> Contact Support</a></li>
            </ul>
        </aside>

        <div class="main-wrapper">
            <header class="main-header">
                <div class="security-status"><i class="fas fa-lock"></i> AES-256 GCM Active</div>
                <div class="user-profile">
                    <span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span>
                </div>
            </header>

            <main id="main-content">
                <?php include 'modules/settings.php'; ?>
            </main>
        </div>
    </div>
</body>
</html>
