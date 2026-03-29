<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Add Supplier
 */

header('Content-Type: application/json');
require_once '../php/SecurityHelper.php';
require_once '../php/config.php';



$db_file = '../data/db.json';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Supplier name is required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $suppliers = $db_data['suppliers'] ?? [];

    $new_id = count($suppliers) > 0 ? max(array_column($suppliers, 'id')) + 1 : 1;
    
    $new_supplier = [
        'id' => $new_id,
        'name' => htmlspecialchars($data['name']),
        'contact_person_encrypted' => SecurityHelper::encrypt($data['contact_person'] ?? ''),
        'phone_encrypted' => SecurityHelper::encrypt($data['phone'] ?? ''),
        'email_encrypted' => SecurityHelper::encrypt($data['email'] ?? '')
    ];

    $db_data['suppliers'][] = $new_supplier;
    file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true, 'message' => 'Supplier added successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;


