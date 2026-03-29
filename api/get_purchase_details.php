<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Purchase Details
 */

header('Content-Type: application/json');

$purchase_id = $_GET['id'] ?? null;

if (empty($purchase_id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Purchase ID is required.']);
    exit;
}

$purchase_id = (int)$purchase_id;
$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Error decoding database file.");

    $purchases = $db_data['purchases'] ?? [];
    $purchase_items = $db_data['purchase_items'] ?? [];

    // Find the specific purchase record
    $purchase_record = null;
    foreach ($purchases as $p) {
        if ($p['id'] === $purchase_id) {
            $purchase_record = $p;
            break;
        }
    }

    if (!$purchase_record) {
        http_response_code(404);
        echo json_encode(['error' => 'Purchase not found.']);
        exit;
    }

    // Find all associated purchase items
    $items_for_purchase = [];
    foreach ($purchase_items as $item) {
        if ($item['purchase_id'] === $purchase_id) {
            $items_for_purchase[] = $item;
        }
    }
    
    // Combine and prepare the response
    $response_data = $purchase_record;
    $response_data['items'] = $items_for_purchase;
    
    echo json_encode($response_data);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Get Purchase Details Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


