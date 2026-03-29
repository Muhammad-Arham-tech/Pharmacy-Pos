<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get All Manufacturers
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $manufacturers = $db_data['manufacturers'] ?? [];
    echo json_encode($manufacturers);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


