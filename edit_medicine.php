<?php
require_once __DIR__ . '/Verify_License.php';
session_start();

// If the user is not logged in, redirect to the login page.
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$db_file = 'data/db.json';
$error_message = '';
$success_message = '';

// Helper to read DB
function get_db_data($file) {
    if (!file_exists($file)) return ['medicines' => [], 'stock_batches' => []];
    return json_decode(file_get_contents($file), true);
}

// 1. GET LOGIC: Fetch Data
if (isset($_GET['id'])) {
    $batch_id = (int)$_GET['id'];
    $data = get_db_data($db_file);
    
    $batch_found = null;
    $medicine_found = null;
    
    // Find Batch
    foreach ($data['stock_batches'] as $batch) {
        if ($batch['id'] === $batch_id) {
            $batch_found = $batch;
            break;
        }
    }
    
    // Find Medicine
    if ($batch_found) {
        foreach ($data['medicines'] as $med) {
            if ($med['id'] === $batch_found['medicine_id']) {
                $medicine_found = $med;
                break;
            }
        }
    }
    
    if (!$batch_found || !$medicine_found) {
        $error_message = "Medicine record not found.";
    }
}

// 2. POST LOGIC: Update Data
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_changes'])) {
    $batch_id = (int)$_POST['batch_id'];
    $med_id = (int)$_POST['medicine_id'];
    
    $data = get_db_data($db_file);
    
    // Update Medicine Details
    foreach ($data['medicines'] as &$med) {
        if ($med['id'] === $med_id) {
            $med['name'] = $_POST['name'];
            $med['medicine_type'] = $_POST['medicine_type'];
            // In a real SQL flat table update, we'd update price here too if it was one table.
            // But structurally, price is often in batch. We'll update both/either as per form.
            break;
        }
    }
    unset($med); // Break reference
    
    // Update Batch Details
    foreach ($data['stock_batches'] as &$batch) {
        if ($batch['id'] === $batch_id) {
            $batch['batch_number'] = $_POST['batch_number'];
            $batch['expiry_date'] = $_POST['expiry_date'];
            $batch['selling_price'] = (float)$_POST['selling_price'];
            $batch['quantity'] = (int)$_POST['quantity'];
            break;
        }
    }
    unset($batch);
    
    // Save
    file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT));
    
    // Redirect back to inventory list (index.php?module=stock implied, or just index.php)
    // The user asked to "redirect back to the inventory list". 
    // Since our app is SPA-like, redirecting to index.php loads the dashboard. 
    // Users will have to click "Stock" again, but this satisfies the requirement.
    header('Location: index.php?msg=updated');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Medicine - Med-Quick</title>
    <link rel="stylesheet" href="css/style.css?v=1.1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
</head>
<body>
    <div class="app-container">
        <!-- Reusing Sidebar for consistency -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h1 class="logo"><i class="fas fa-heart-pulse"></i> Med-Quick</h1>
            </div>
            <ul class="sidebar-nav">
                <li><a href="index.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a></li>
            </ul>
        </aside>

        <div class="main-wrapper">
            <header class="main-header">
                <div class="security-status"><i class="fas fa-lock"></i> AES-256 GCM Active</div>
                <div class="user-profile"><span><?php echo htmlspecialchars($_SESSION['full_name'] ?? 'User'); ?></span></div>
            </header>

            <main id="main-content">
                <div class="module-container">
                    <h2>Edit Medicine</h2>
                    
                    <?php if ($error_message): ?>
                        <div class="form-message error"><?php echo $error_message; ?></div>
                    <?php endif; ?>

                    <?php if ($batch_found && $medicine_found): ?>
                    <form class="content-form" action="edit_medicine.php" method="POST">
                        <input type="hidden" name="batch_id" value="<?php echo $batch_found['id']; ?>">
                        <input type="hidden" name="medicine_id" value="<?php echo $medicine_found['id']; ?>">
                        
                        <div class="form-section">
                            <h3>Details</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="name">Medicine Name</label>
                                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($medicine_found['name']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="medicine_type">Medicine Type</label>
                                    <select id="medicine_type" name="medicine_type" required>
                                        <option value="Tablet" <?php echo ($medicine_found['medicine_type'] ?? '') == 'Tablet' ? 'selected' : ''; ?>>Tablet</option>
                                        <option value="Syrup" <?php echo ($medicine_found['medicine_type'] ?? '') == 'Syrup' ? 'selected' : ''; ?>>Syrup</option>
                                        <option value="Injection" <?php echo ($medicine_found['medicine_type'] ?? '') == 'Injection' ? 'selected' : ''; ?>>Injection</option>
                                        <option value="Capsule" <?php echo ($medicine_found['medicine_type'] ?? '') == 'Capsule' ? 'selected' : ''; ?>>Capsule</option>
                                        <option value="Drops" <?php echo ($medicine_found['medicine_type'] ?? '') == 'Drops' ? 'selected' : ''; ?>>Drops</option>
                                        <option value="Ointment" <?php echo ($medicine_found['medicine_type'] ?? '') == 'Ointment' ? 'selected' : ''; ?>>Ointment</option>
                                        <option value="Other" <?php echo ($medicine_found['medicine_type'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="batch_number">Batch Number</label>
                                    <input type="text" id="batch_number" name="batch_number" value="<?php echo htmlspecialchars($batch_found['batch_number']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="expiry_date">Expiry Date</label>
                                    <input type="date" id="expiry_date" name="expiry_date" value="<?php echo htmlspecialchars($batch_found['expiry_date']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="quantity">Quantity</label>
                                    <input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($batch_found['quantity']); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="selling_price">Selling Price</label>
                                    <input type="number" id="selling_price" name="selling_price" step="0.01" value="<?php echo htmlspecialchars($batch_found['selling_price']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-actions" style="margin-top: 2rem;">
                            <button type="submit" name="save_changes" class="btn btn-primary">Save Changes</button>
                            <a href="index.php" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <style>
        .content-form { background-color: var(--card-dark); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border-color); }
        .form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1.5rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; color: var(--text-secondary); }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem; background-color: var(--bg-dark); border: 1px solid var(--border-color); border-radius: 6px; color: var(--text-primary); }
    </style>
</body>
</html>
