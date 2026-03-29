<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Process Checkout
 *
 * This script now performs the following:
 * 1. Validates the incoming cart data.
 * 2. Reads the db.json file.
 * 3. Updates the quantity for each item in the `stock_batches` array.
 * 4. Creates a new record in the `sales` array.
 * 5. Creates new records in the `sale_items` array.
 * 6. Writes the updated data back to the db.json file.
 */

header('Content-Type: application/json');
session_start(); // To get user info

// --- RECEIVE AND DECODE POST DATA ---
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['cart']) || empty($data['cart'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid or empty cart data provided.']);
    exit;
}

$db_file = '../data/db.json';

try {
    // --- FILE I/O ---
    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Error decoding database file.");

    // --- VALIDATION AND STOCK UPDATE ---
    foreach ($data['cart'] as $cart_item) {
        $batch_found = false;
        foreach ($db_data['stock_batches'] as &$batch) { // Use reference to modify directly
            if ($batch['id'] === $cart_item['batch_id']) {
                if ($batch['quantity'] < $cart_item['quantity']) {
                    throw new Exception("Insufficient stock for item: " . $cart_item['name']);
                }
                // Reduce the stock quantity
                $batch['quantity'] -= $cart_item['quantity'];
                $batch_found = true;
                break;
            }
        }
        if (!$batch_found) throw new Exception("Stock batch not found for item: " . $cart_item['name']);
    }
    unset($batch); // Unset reference

    // --- CREATE SALE RECORD ---
    $new_sale_id = count($db_data['sales']) > 0 ? max(array_column($db_data['sales'], 'id')) + 1 : 1;
    
    $new_sale = [
        "id" => $new_sale_id,
        "transaction_token" => bin2hex(random_bytes(16)),
        "customer_id" => (int)($data['customer_id'] ?? 0), // 0 for Walk-in
        "subtotal" => (float)$data['subtotal'],
        "discount_percentage" => (float)($data['discount_percentage'] ?? 0),
        "discount_amount" => (float)($data['discount_amount'] ?? 0),
        "total_tax" => (float)$data['tax'],
        "grand_total" => (float)$data['total'], // This is now the final total after discount
        "user_id" => $_SESSION['user_id'] ?? 0,
        "user_name" => $_SESSION['full_name'] ?? 'Unknown',
        "created_at" => date('Y-m-d H:i:s')
    ];
    $db_data['sales'][] = $new_sale;

    // --- CREATE SALE ITEMS RECORDS ---
    $last_sale_item_id = count($db_data['sale_items']) > 0 ? max(array_column($db_data['sale_items'], 'id')) + 1 : 0;
    
    foreach ($data['cart'] as $cart_item) {
        $db_data['sale_items'][] = [
            "id" => ++$last_sale_item_id,
            "sale_id" => $new_sale_id,
            "stock_batch_id" => $cart_item['batch_id'],
            "medicine_name" => $cart_item['name'], // Denormalized for reporting
            "quantity" => $cart_item['quantity'],
            "unit_price" => $cart_item['selling_price'],
            "tax" => ($cart_item['selling_price'] * $cart_item['quantity']) * ($cart_item['tax_rate'] / 100),
            "total" => $cart_item['selling_price'] * $cart_item['quantity']
        ];
    }
    
    // --- WRITE DATA BACK TO FILE ---
    if (file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT)) === false) {
        throw new Exception("Could not write to database file.");
    }

    // --- SUCCESS RESPONSE ---
    echo json_encode([
        'success' => true,
        'message' => 'Checkout processed successfully.',
        'sale_id' => $new_sale_id,
        'transaction_token' => $new_sale['transaction_token']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Checkout Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;

