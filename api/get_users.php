<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get All Users
 */

header('Content-Type: application/json');
require_once '../php/SecurityHelper.php';
require_once '../php/config.php';



$db_file = '../data/db.json';

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $users = $db_data['users'] ?? [];

    // Decrypt full name for display and remove password hash
    foreach($users as &$user) {
        $user['full_name'] = SecurityHelper::decrypt($user['full_name_encrypted']);
        unset($user['password_hash']);
        unset($user['full_name_encrypted']);
    }
    unset($user);

    echo json_encode($users);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


