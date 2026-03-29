<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Single Supplier
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

$id = $_GET['id'] ?? null;
if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Supplier ID is required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $suppliers = $db_data['suppliers'] ?? [];
    
    $found_supplier = null;
    foreach ($suppliers as $supplier) {
        if ($supplier['id'] == $id) {
            $found_supplier = $supplier;
            break;
        }
    }

    if ($found_supplier) {
        echo json_encode($found_supplier);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Supplier not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


