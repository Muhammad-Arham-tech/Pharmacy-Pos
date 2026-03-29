<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Out of Stock Medicines
 *
 * This endpoint reads from db.json and returns all medicines that are out of stock (quantity <= 0).
 */

header('Content-Type: application/json');

$db_file = '../data/db.json';
$results = [];

try {
    // 1. Read the database file.
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found.");
    }
    $db_content = file_get_contents($db_file);
    $db_data = json_decode($db_content, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON from database file.");
    }

    $medicines = $db_data['medicines'] ?? [];
    $stock_batches = $db_data['stock_batches'] ?? [];

    // Create a quick lookup map for medicines by their ID
    $medicine_map = [];
    foreach ($medicines as $med) {
        $medicine_map[$med['id']] = $med;
    }

    // 2. Go through stock batches and filter for out-of-stock items (quantity <= 0)
    foreach ($stock_batches as $batch) {
        if (isset($batch['quantity']) && $batch['quantity'] <= 0) {
            // Find the corresponding medicine details
            if (isset($medicine_map[$batch['medicine_id']])) {
                $medicine = $medicine_map[$batch['medicine_id']];

                // 3. Combine medicine and batch info for the result
                $results[] = [
                    "id" => $medicine['id'],
                    "name" => $medicine['name'],
                    "barcode" => $medicine['barcode'] ?? '',
                    "strength" => $medicine['strength'] ?? '',
                    "tax_rate" => $medicine['tax_rate'] ?? 0,
                    "batch_id" => $batch['id'], // Unique ID for the batch
                    "batch_number" => $batch['batch_number'],
                    "expiry_date" => $batch['expiry_date'],
                    "quantity" => $batch['quantity'],
                    "selling_price" => $batch['selling_price']
                ];
            }
        }
    }

} catch (Exception $e) {
    http_response_code(500);
    error_log('Get Out of Stock Medicines Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Return the final results as JSON
echo json_encode($results);

exit;

