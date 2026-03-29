<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Sale Details
 *
 * This endpoint returns a specific sale record along with its associated line items.
 */

header('Content-Type: application/json');

// 1. Get the Sale ID from the query parameter
$sale_id = $_GET['id'] ?? null;

if (empty($sale_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Sale ID is required.']);
    exit;
}

$sale_id = (int)$sale_id;
$db_file = '../data/db.json';
$response_data = null;

try {
    // 2. Read the database file
    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Error decoding database file.");

    $sales = $db_data['sales'] ?? [];
    $sale_items = $db_data['sale_items'] ?? [];

    // 3. Find the specific sale record
    $sale_record = null;
    foreach ($sales as $sale) {
        if ($sale['id'] === $sale_id) {
            $sale_record = $sale;
            break;
        }
    }

    if (!$sale_record) {
        http_response_code(404);
        echo json_encode(['error' => 'Sale not found.']);
        exit;
    }

    // 4. Find all associated sale items
    $items_for_sale = [];
    foreach ($sale_items as $item) {
        if ($item['sale_id'] === $sale_id) {
            $items_for_sale[] = $item;
        }
    }
    
    // 5. Combine and prepare the response
    $response_data = $sale_record;
    $response_data['items'] = $items_for_sale;
    
    echo json_encode($response_data);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Get Sale Details Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


