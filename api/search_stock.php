<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Search Stock
 *
 * This endpoint searches through all stock batches (including out-of-stock items)
 * based on a query for medicine name or batch number.
 */

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';

// Unlike POS search, we can allow empty query to return all items
if (empty($query)) {
    // An empty query could return all items, but for a dedicated search script,
    // it's better to return an empty set to avoid accidentally loading a huge dataset.
    // The frontend will be responsible for calling a different "get all" endpoint.
    echo json_encode([]);
    exit;
}

$db_file = '../data/db.json';
$results = [];

try {
    // 1. Read the database file.
    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Error decoding database file.");

    $medicines = $db_data['medicines'] ?? [];
    $stock_batches = $db_data['stock_batches'] ?? [];

    $medicine_map = [];
    foreach ($medicines as $med) {
        $medicine_map[$med['id']] = $med;
    }

    // 2. Search through ALL stock batches
    foreach ($stock_batches as $batch) {
        if (isset($medicine_map[$batch['medicine_id']])) {
            $medicine = $medicine_map[$batch['medicine_id']];

            // Check if the query matches the name or batch number
            $is_match = (
                stripos($medicine['name'], $query) !== false ||
                stripos($batch['batch_number'], $query) !== false
            );

            if ($is_match) {
                // Combine info for the result, similar to get_all_medicines.php
                $results[] = [
                    "id" => $medicine['id'],
                    "name" => $medicine['name'],
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
    error_log('Search Stock Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Sort results by name
usort($results, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

echo json_encode($results);

exit;


