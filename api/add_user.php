<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Add User
 */

header('Content-Type: application/json');
require_once '../php/SecurityHelper.php';
require_once '../php/config.php';



$db_file = '../data/db.json';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['username']) || empty($data['password']) || empty($data['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Username, password, and role are required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $users = $db_data['users'] ?? [];

    // Check for duplicate username
    foreach($users as $user) {
        if ($user['username'] === $data['username']) {
            http_response_code(409); // Conflict
            echo json_encode(['success' => false, 'message' => 'Username already exists.']);
            exit;
        }
    }

    $new_id = count($users) > 0 ? max(array_column($users, 'id')) + 1 : 1;
    
    $new_user = [
        'id' => $new_id,
        'username' => htmlspecialchars($data['username']),
        'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
        'full_name_encrypted' => SecurityHelper::encrypt($data['full_name'] ?? ''),
        'role' => $data['role'],
        'is_active' => (bool)($data['is_active'] ?? false)
    ];

    $db_data['users'][] = $new_user;
    file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true, 'message' => 'User added successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;


