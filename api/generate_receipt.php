<?php
// api/generate_receipt.php

// --- Enhanced Error Reporting & Simplified Logging ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$log_file = '../logs/receipt_debug.log';
// Clear the log file for each new request to keep it clean.
if (file_exists($log_file)) {
    unlink($log_file);
}

function write_log($message) {
    global $log_file;
    // Use a simple string format, ensuring complex types are converted.
    $log_entry = date("Y-m-d H:i:s") . " - " . (is_array($message) || is_object($message) ? print_r($message, true) : $message) . "\n";
    // The `error_log` function is often more reliable than `file_put_contents` for logging.
    error_log($log_entry, 3, $log_file);
}

write_log("--- New Receipt Generation Request ---");

// Function to read the database
function read_db() {
    $db_file = '../data/db.json';
    if (!file_exists($db_file)) {
        $error_msg = "ERROR: Database file not found at {" . $db_file . "}";
        write_log($error_msg);
        die($error_msg);
    }
    
    $json_data = file_get_contents($db_file);
    $decoded = json_decode($json_data, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $error_msg = "FATAL: JSON decode error: " . json_last_error_msg();
        write_log($error_msg);
        die($error_msg);
    }

    return $decoded;
}

// Get sale_id from the query string
$sale_id = isset($_GET['sale_id']) ? intval($_GET['sale_id']) : 0;
write_log("Received sale_id: " . $sale_id);

if ($sale_id === 0) {
    $error_msg = "ERROR: No sale ID provided. Terminating.";
    write_log($error_msg);
    die($error_msg);
}

// Read the database
$db = read_db();
$sales = $db['sales'] ?? [];
$sale_items = $db['sale_items'] ?? [];
$medicines = $db['medicines'] ?? [];
$customers = $db['customers'] ?? [];

// Find the specific sale
$sale_data = null;
foreach ($sales as $s) {
    if (isset($s['id']) && $s['id'] == $sale_id) {
        $sale_data = $s;
        break;
    }
}

if ($sale_data === null) {
    $error_msg = "ERROR: Sale with ID {" . $sale_id . "} not found.";
    write_log($error_msg);
    die($error_msg);
}

write_log("Found Sale Data: " . print_r($sale_data, true));

// Fetch Customer Name (Simplified Join)
$customer_name = "Walk-in";
if (isset($sale_data['customer_id']) && $sale_data['customer_id'] > 0) {
    foreach ($customers as $c) {
        if ($c['id'] == $sale_data['customer_id']) {
            $customer_name = $c['name'];
            break;
        }
    }
}

// Find sale items for this sale
$items_for_this_sale = [];
foreach ($sale_items as $item) {
    if (isset($item['sale_id']) && $item['sale_id'] == $sale_id) {
        $items_for_this_sale[] = $item;
    }
}

write_log("Found " . count($items_for_this_sale) . " items for this sale.");

if (empty($items_for_this_sale)) {
    write_log("WARNING: No items found for sale ID {" . $sale_id . "}. Receipt will be empty.");
}

// Prepare medicine details for quick lookup (Simulated Inner Join Table)
$medicine_details = [];
foreach ($medicines as $med) {
    if(isset($med['id'])) {
        $medicine_details[$med['id']] = $med;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Receipt</title>
    <style>
        /* Styles remain the same */
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Mono&display=swap');
        body { font-family: 'Roboto Mono', monospace; width: 300px; margin: auto; padding: 20px; font-size: 12px; line-height: 1.6; }
        .receipt-header { text-align: center; margin-bottom: 20px; }
        h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        p { margin: 2px 0; }
        .item-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th, td { border-bottom: 1px dashed #000; padding: 5px 0; text-align: left; }
        th:last-child, td:last-child { text-align: right; }
        .totals-table { width: 100%; margin-top: 10px; }
        .totals-table td:last-child { text-align: right; }
        .footer { text-align: center; margin-top: 20px; }
        .print-button { width: 100%; padding: 10px; background-color: #333; color: #fff; border: none; cursor: pointer; font-size: 14px; margin-top: 20px; }
        @media print { .print-button { display: none; } body { width: 100%; font-size: 10px; } }
    </style>
    <!-- Auto Print Script -->
    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</head>
<body>

    <div class="receipt-header">
        <h1>Your Pharmacy</h1>
        <p>123 Health St, Wellness City</p>
        <p>Contact: (123) 456-7890</p>
        <p>Date: <?php echo htmlspecialchars(date("Y-m-d H:i:s", strtotime($sale_data['created_at']))); ?></p>
        <p>Receipt #: <?php echo htmlspecialchars($sale_data['id']); ?></p>
        <p>Customer: <?php echo htmlspecialchars($customer_name); ?></p>
    </div>

    <table class="item-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($items_for_this_sale)): ?>
                <?php foreach ($items_for_this_sale as $item):
                    // Logic: JOIN sale_items.medicine_id = medicines.id
                    // Prefer denormalized name if available, otherwise join lookup
                    $med_name = $item['medicine_name'] ?? 'Unknown';
                    
                    if ($med_name === 'Unknown' && isset($item['medicine_id']) && isset($medicine_details[$item['medicine_id']])) {
                        $med_name = $medicine_details[$item['medicine_id']]['name'];
                    }
                ?>
                <tr>
                    <td><?php echo htmlspecialchars($med_name); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
                    <td><?php echo htmlspecialchars(number_format($item['quantity'] * $item['unit_price'], 2)); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" style="text-align:center;">No items in this sale.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Subtotal</td>
            <td><?php echo htmlspecialchars(number_format($sale_data['subtotal'], 2)); ?></td>
        </tr>
        <tr>
            <td>Tax</td>
            <td><?php echo htmlspecialchars(number_format($sale_data['total_tax'], 2)); ?></td>
        </tr>
        <tr>
            <td><strong>Total</strong></td>
            <td><strong><?php echo htmlspecialchars(number_format($sale_data['grand_total'], 2)); ?></strong></td>
        </tr>
    </table>

    <div class="footer">
        <p>Thank you for your business!</p>
        <p>Please consult your doctor.</p>
    </div>

    <button class="print-button" onclick="window.print()">Print Receipt</button>
</body>
</html>