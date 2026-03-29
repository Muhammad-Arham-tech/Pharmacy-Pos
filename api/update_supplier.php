<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Update Supplier
 */

header('Content-Type: application/json');
require_once '../php/SecurityHelper.php';
require_once '../php/config.php';
$db_file = '../data/db.json';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (empty($id) || empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Supplier ID and name are required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $suppliers = &$db_data['suppliers']; // Use reference

    $found = false;
    foreach ($suppliers as &$supplier) { // Use reference
        if ($supplier['id'] == $id) {
            $supplier['name'] = htmlspecialchars($data['name']);
            if (isset($data['contact_person'])) {
                 $supplier['contact_person_encrypted'] = SecurityHelper::encrypt($data['contact_person']);
            }
            if (isset($data['phone'])) {
                 $supplier['phone_encrypted'] = SecurityHelper::encrypt($data['phone']);
            }
             if (isset($data['email'])) {
                 $supplier['email_encrypted'] = SecurityHelper::encrypt($data['email']);
            }
            $found = true;
            break;
        }
    }
    unset($supplier);

    if ($found) {
        file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Supplier updated successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Supplier not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;


