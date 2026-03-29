<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Add New Purchase
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';
$response = ['success' => false, 'message' => 'An unknown error occurred.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Invalid JSON input.';
        echo json_encode($response);
        exit;
    }

    // Basic validation
    if (empty($input['supplier_id']) || empty($input['purchase_date']) || empty($input['items'])) {
        $response['message'] = 'Missing required fields: supplier_id, purchase_date, or items.';
        echo json_encode($response);
        exit;
    }

    try {
        if (!file_exists($db_file)) {
            file_put_contents($db_file, json_encode(['purchases' => [], 'purchase_items' => [], 'stock_batches' => []], JSON_PRETTY_PRINT));
        }

        $db_data = json_decode(file_get_contents($db_file), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Error decoding database file.");
        }

        // Initialize arrays if they don't exist
        $db_data['purchases'] = $db_data['purchases'] ?? [];
        $db_data['purchase_items'] = $db_data['purchase_items'] ?? [];
        $db_data['stock_batches'] = $db_data['stock_batches'] ?? [];
        $db_data['medicines'] = $db_data['medicines'] ?? []; // Ensure medicines array exists

        // Generate new purchase ID
        $new_purchase_id = 1;
        if (!empty($db_data['purchases'])) {
            $new_purchase_id = max(array_column($db_data['purchases'], 'id')) + 1;
        }

        // Fetch supplier name for the purchase record
        $supplier_name = 'Unknown Supplier';
        $suppliers = $db_data['suppliers'] ?? [];
        foreach ($suppliers as $s) {
            if ($s['id'] == $input['supplier_id']) {
                $supplier_name = $s['name'];
                break;
            }
        }

        $total_amount = 0;
        $purchase_items_to_add = [];

        // Process each item in the purchase
        foreach ($input['items'] as $item_data) {
            // Basic item validation
            if (empty($item_data['medicine_name']) || empty($item_data['batch_number']) || empty($item_data['expiry_date']) || empty($item_data['quantity']) || empty($item_data['cost_price']) || empty($item_data['selling_price'])) {
                throw new Exception('Missing required item fields.');
            }

            $item_data['quantity'] = (int)$item_data['quantity'];
            $item_data['cost_price'] = (float)$item_data['cost_price'];
            $item_data['selling_price'] = (float)$item_data['selling_price'];

            if ($item_data['quantity'] <= 0 || $item_data['cost_price'] < 0 || $item_data['selling_price'] < 0) {
                throw new Exception('Invalid quantity or price for an item.');
            }

            // Find or create medicine entry
            $medicine_id = null;
            foreach ($db_data['medicines'] as $med) {
                if (strtolower($med['name']) === strtolower($item_data['medicine_name'])) {
                    $medicine_id = $med['id'];
                    break;
                }
            }
            if ($medicine_id === null) {
                // If medicine not found, create a new one (minimal data)
                $medicine_id = 1;
                if (!empty($db_data['medicines'])) {
                    $medicine_id = max(array_column($db_data['medicines'], 'id')) + 1;
                }
                $db_data['medicines'][] = [
                    'id' => $medicine_id,
                    'name' => $item_data['medicine_name'],
                    'barcode' => '', // Placeholder
                    'strength' => '', // Placeholder
                    'category_id' => null, // Placeholder
                    'manufacturer_id' => null, // Placeholder
                    'generic_salt_id' => null, // Placeholder
                    'mrp' => $item_data['selling_price'], // Use selling price as MRP if no other info
                    'tax_rate' => 0, // Placeholder
                    'requires_prescription' => false // Placeholder
                ];
            }


            // Find or update stock batch
            $batch_found = false;
            foreach ($db_data['stock_batches'] as &$batch) {
                if ($batch['medicine_id'] === $medicine_id && $batch['batch_number'] === $item_data['batch_number']) {
                    // Update existing batch
                    $batch['quantity'] += $item_data['quantity'];
                    $batch['cost_price'] = $item_data['cost_price']; // Update cost price to latest
                    $batch['selling_price'] = $item_data['selling_price']; // Update selling price to latest
                    $batch['expiry_date'] = $item_data['expiry_date']; // Update expiry date to latest
                    $batch_found = true;
                    break;
                }
            }
            unset($batch); // Break the reference

            if (!$batch_found) {
                // Create new batch
                $new_batch_id = 1;
                if (!empty($db_data['stock_batches'])) {
                    $new_batch_id = max(array_column($db_data['stock_batches'], 'id')) + 1;
                }
                $db_data['stock_batches'][] = [
                    'id' => $new_batch_id,
                    'medicine_id' => $medicine_id,
                    'batch_number' => $item_data['batch_number'],
                    'expiry_date' => $item_data['expiry_date'],
                    'quantity' => $item_data['quantity'],
                    'cost_price' => $item_data['cost_price'],
                    'selling_price' => $item_data['selling_price']
                ];
            }

            // Generate new purchase item ID
            $new_purchase_item_id = 1;
            if (!empty($db_data['purchase_items'])) {
                $new_purchase_item_id = max(array_column($db_data['purchase_items'], 'id')) + 1;
            }

            $item_total = $item_data['quantity'] * $item_data['cost_price'];
            $total_amount += $item_total;

            $purchase_items_to_add[] = [
                'id' => $new_purchase_item_id,
                'purchase_id' => $new_purchase_id,
                'medicine_name' => $item_data['medicine_name'],
                'batch_number' => $item_data['batch_number'],
                'quantity' => $item_data['quantity'],
                'cost_price' => $item_data['cost_price'],
                'total' => $item_total
            ];
        }

        // Add the main purchase record
        $db_data['purchases'][] = [
            'id' => $new_purchase_id,
            'supplier_id' => (int)$input['supplier_id'],
            'supplier_name' => $supplier_name,
            'purchase_date' => $input['purchase_date'],
            'total_amount' => $total_amount,
            'user_id' => 1, // Assuming admin user for now or getting from session
            'user_name' => 'Admin User', // Assuming admin user for now or getting from session
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Add the purchase items
        foreach($purchase_items_to_add as $item) {
            $db_data['purchase_items'][] = $item;
        }

        // Save updated data
        if (file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT)) === false) {
            throw new Exception("Failed to write to database file.");
        }

        $response['success'] = true;
        $response['message'] = 'Purchase recorded successfully!';

    } catch (Exception $e) {
        http_response_code(500);
        $response['message'] = $e->getMessage();
        error_log('Add Purchase Error: ' . $e->getMessage());
    }
} else {
    http_response_code(405);
    $response['message'] = 'Method Not Allowed';
}

echo json_encode($response);
exit;

