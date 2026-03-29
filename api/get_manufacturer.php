<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Single Manufacturer
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

$id = $_GET['id'] ?? null;
if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Manufacturer ID is required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $manufacturers = $db_data['manufacturers'] ?? [];
    
    $found_manufacturer = null;
    foreach ($manufacturers as $manufacturer) {
        if ($manufacturer['id'] == $id) {
            $found_manufacturer = $manufacturer;
            break;
        }
    }

    if ($found_manufacturer) {
        echo json_encode($found_manufacturer);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Manufacturer not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


