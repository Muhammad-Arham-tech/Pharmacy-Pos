<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Add Customer
 */

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$db_file = '../data/db.json';

try {
    if (!isset($data['name']) || empty($data['name'])) {
        throw new Exception("Customer name is required.");
    }

    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);
    
    // Initialize customers array if not present
    if (!isset($db_data['customers'])) {
        $db_data['customers'] = [];
    }

    $new_id = count($db_data['customers']) > 0 ? max(array_column($db_data['customers'], 'id')) + 1 : 1;

    $new_customer = [
        'id' => $new_id,
        'name' => htmlspecialchars($data['name']),
        'phone' => htmlspecialchars($data['phone'] ?? ''),
        'email' => htmlspecialchars($data['email'] ?? ''),
        'address' => htmlspecialchars($data['address'] ?? ''),
        'created_at' => date('Y-m-d H:i:s')
    ];

    $db_data['customers'][] = $new_customer;

    if (file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT)) === false) {
        throw new Exception("Could not write to database.");
    }

    echo json_encode(['success' => true, 'message' => 'Customer added successfully.', 'customer' => $new_customer]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
