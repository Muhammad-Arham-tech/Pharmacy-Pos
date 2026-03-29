<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get All Suppliers
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $suppliers = $db_data['suppliers'] ?? [];
    echo json_encode($suppliers);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


