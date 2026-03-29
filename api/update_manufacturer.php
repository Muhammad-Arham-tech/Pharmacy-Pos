<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Update Manufacturer
 */

header('Content-Type: application/json');
require_once '../php/SecurityHelper.php';
require_once '../php/config.php';
$db_file = '../data/db.json';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (empty($id) || empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Manufacturer ID and name are required.']);
    exit;
}

try {
    // In a real app, you would load your encryption key here.
    // define('ENCRYPTION_KEY', 'your-secret-key');

    $db_data = json_decode(file_get_contents($db_file), true);
    $manufacturers = &$db_data['manufacturers']; // Use reference

    $found = false;
    foreach ($manufacturers as &$manufacturer) { // Use reference
        if ($manufacturer['id'] == $id) {
            $manufacturer['name'] = htmlspecialchars($data['name']);
            // Only update encrypted fields if they are provided, to avoid re-encrypting nulls
            if (isset($data['contact_person'])) {
                 $manufacturer['contact_person_encrypted'] = SecurityHelper::encrypt($data['contact_person']);
            }
            if (isset($data['phone'])) {
                 $manufacturer['phone_encrypted'] = SecurityHelper::encrypt($data['phone']);
            }
             if (isset($data['email'])) {
                 $manufacturer['email_encrypted'] = SecurityHelper::encrypt($data['email']);
            }
            $found = true;
            break;
        }
    }
    unset($manufacturer);

    if ($found) {
        file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Manufacturer updated successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Manufacturer not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;


