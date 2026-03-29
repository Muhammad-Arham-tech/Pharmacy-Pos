<?php
    require_once __DIR__ . '/Verify_License.php';
    session_start();
    // If the user is not logged in, redirect to the login page.
    if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
        header('Location: login.php');
        exit;
}

$db_file = 'data/db.json';
$store_name = 'Med-Quick'; // Default

if (file_exists($db_file)) {
    $db_data = json_decode(file_get_contents($db_file), true);
    if (!empty($db_data['settings']['store_name'])) {
        $store_name = $db_data['settings']['store_name'];
    }
}
// Optionally fallback to session if DB read fails but session exists, 
// though DB read is preferred for consistency on page load.
if (isset($_SESSION['store_name']) && !empty($_SESSION['store_name'])) {
    $store_name = $_SESSION['store_name'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($store_name); ?> - Secure POS</title>
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="app-container">
        <!-- SIDEBAR -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo"><i class="fas fa-heart-pulse"></i> <?php echo htmlspecialchars($store_name); ?></h1>
            </div>
            <ul class="sidebar-nav">
                <li><a href="#" class="nav-link active" data-module="pos"><i class="fas fa-cash-register"></i> POS Terminal</a></li>
                <li><a href="#" class="nav-link" data-module="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="#" class="nav-link" data-module="sales"><i class="fas fa-chart-line"></i> Sales</a></li>
                <li><a href="#" class="nav-link" data-module="stock"><i class="fas fa-boxes-stacked"></i> Stock / Inventory</a></li>
                <li><a href="#" class="nav-link" data-module="out_of_stock"><i class="fas fa-box-open"></i> Out of Stock</a></li>
                <li><a href="#" class="nav-link" data-module="purchases"><i class="fas fa-shopping-cart"></i> Purchases</a></li>
                <li><a href="#" class="nav-link" data-module="medicines"><i class="fas fa-pills"></i> Medicines</a></li>
                <li><a href="#" class="nav-link" data-module="categories"><i class="fas fa-sitemap"></i> Categories</a></li>
                <li><a href="#" class="nav-link" data-module="manufacturers"><i class="fas fa-industry"></i> Manufacturers</a></li>
                <li><a href="#" class="nav-link" data-module="suppliers"><i class="fas fa-truck"></i> Suppliers</a></li>
                <li><a href="#" class="nav-link" data-module="users"><i class="fas fa-users"></i> User Management</a></li>
                <li><a href="#" class="nav-link" data-module="reports"><i class="fas fa-file-alt"></i> Reports</a></li>
                <li><a href="#" class="nav-link" data-module="bank"><i class="fas fa-university"></i> Bank Transactions</a></li>
                <li><a href="#" class="nav-link" data-module="logs"><i class="fas fa-shield-halved"></i> Security Logs</a></li>
                <li><a href="#" class="nav-link" data-module="settings"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                <li style="margin-top: auto; border-top: 1px solid var(--border-color);"><a href="#" id="support-link"><i class="fas fa-life-ring"></i> Contact Support</a></li>
            </ul>
        </aside>
        <!-- =================================================================
             MAIN CONTENT WRAPPER
             ================================================================= -->
        <div class="main-wrapper">
            <!-- Header -->
            <header class="main-header">
                <div class="security-status">
                    <i class="fas fa-lock"></i> AES-256 GCM Active
                </div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button id="theme-toggle" class="btn" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-primary); padding: 5px 10px;">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="user-profile">
                        <a href="#">
                            <img src="https://i.pravatar.cc/40?u=<?php echo urlencode($_SESSION['username']); ?>" alt="User Avatar">
                            <span><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                        </a>
                    </div>
                </div>
            </header>
            <!-- Main Content Area (for AJAX injection) -->
            <main id="main-content">
                <!-- Content will be loaded here by app.js -->
                <!-- Initial POS Grid for demonstration -->
                <div class="pos-grid">
                    <div class="pos-left">
                        <div class="search-bar">
                            <input type="text" id="medicine-search" placeholder="Scan barcode or type medicine name...">
                        </div>
                        <div class="item-grid" id="item-grid-display">
                            <!-- Medicine items will be populated here via JS -->
                        </div>
                    </div>
                    <div class="pos-right">
                        <div class="billing-summary">
                            <h3>Billing Summary</h3>
                            <div class="cart-items" id="cart-items-container">
                                <!-- Cart items will be populated here -->
                            </div>
                            <div class="totals-section">
                                <div>
                                    <span>Subtotal</span>
                                    <span id="subtotal">0.00</span>
                                </div>
                                <div>
                                    <span>Tax</span>
                                    <span id="total-tax">0.00</span>
                                </div>
                                <div class="grand-total">
                                    <span>Grand Total</span>
                                    <span id="grand-total">0.00</span>
                                </div>
                            </div>
                            <div class="payment-section">
                                <button id="process-checkout" class="btn btn-primary btn-block">Process Checkout</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Support Modal -->
    <div id="support-modal" class="modal-backdrop" style="display: none;">
        <div class="modal-content" style="max-width: 450px; text-align: center; border: 1px solid var(--accent-green);">
            <div class="modal-header" style="justify-content: center; position: relative; border-bottom: none;">
                <h3 style="color: var(--accent-green); font-size: 1.4rem;">Technical Support & Development</h3>
                <button class="modal-close" id="close-support-modal" style="position: absolute; right: 1rem; top: 1rem;">&times;</button>
            </div>
            <div class="modal-body" style="padding-top: 0;">
                <p style="margin-bottom: 1.5rem; color: var(--text-primary); font-size: 1.1rem;">
                    Developer: <strong style="color: var(--accent-green);">Arham Ali</strong>
                </p>
                
                <a href="https://wa.me/923278047689?text=Assalam-o-Alaikum%20Arham,%20I%20need%20help%20with%20Med-Quick%20POS" target="_blank" class="btn btn-support btn-block" style="margin-bottom: 1rem;">
                    <i class="fab fa-whatsapp"></i> Chat on WhatsApp
                </a>
                
                <a href="mailto:arhamrehmani048@gmail.com?subject=Med-Quick%20Support%20Request" class="btn btn-support btn-block" style="margin-bottom: 2rem;">
                    <i class="fas fa-envelope"></i> Send Email
                </a>
                
                <div style="border-top: 1px solid var(--border-color); padding-top: 1rem; font-size: 0.9rem; color: var(--text-secondary);">
                    Designed & Developed by <strong style="color: var(--text-primary);">Arham Ali</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="js/app.js"></script>
</body>
</html>
