<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Search Medicine
 *
 * This endpoint now reads from the db.json file to find medicines.
 */

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$db_file = '../data/db.json';
$results = [];

try {
    // 1. Read the database file.
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found.");
    }
    $db_content = file_get_contents($db_file);
    $db_data = json_decode($db_content, true);

    $medicines = $db_data['medicines'] ?? [];
    $stock_batches = $db_data['stock_batches'] ?? [];

    // Create a quick lookup map for medicines by their ID
    $medicine_map = [];
    foreach ($medicines as $med) {
        $medicine_map[$med['id']] = $med;
    }

    // 2. Search through stock batches that have quantity > 0
    foreach ($stock_batches as $batch) {
        if ($batch['quantity'] <= 0) {
            continue; // Skip out-of-stock items
        }

        // Find the corresponding medicine details
        if (isset($medicine_map[$batch['medicine_id']])) {
            $medicine = $medicine_map[$batch['medicine_id']];

            // Check if the query matches the name or barcode
            $is_match = (
                stripos($medicine['name'], $query) !== false ||
                (isset($medicine['barcode']) && $medicine['barcode'] === $query)
            );

            if ($is_match) {
                // 3. Combine medicine and batch info for the result
                $results[] = [
                    "id" => $medicine['id'],
                    "name" => $medicine['name'],
                    "barcode" => $medicine['barcode'],
                    "strength" => $medicine['strength'],
                    "tax_rate" => $medicine['tax_rate'],
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
    error_log('Search Medicine Error: ' . $e->getMessage());
    // Return an empty array on error
    echo json_encode([]);
    exit;
}

// Return the final results as JSON
echo json_encode($results);

exit;

