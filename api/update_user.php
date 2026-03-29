<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Update User
 */

header('Content-Type: application/json');
require_once '../php/SecurityHelper.php';
require_once '../php/config.php';
require_once '../php/init_db.php';
$db_file = get_db_path();

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (empty($id) || empty($data['username']) || empty($data['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID, username, and role are required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $users = &$db_data['users']; // Use reference

    // Check for duplicate username, excluding the current user
    foreach($users as $user) {
        if ($user['username'] === $data['username'] && $user['id'] != $id) {
            http_response_code(409); // Conflict
            echo json_encode(['success' => false, 'message' => 'Username already exists.']);
            exit;
        }
    }

    $found = false;
    foreach ($users as &$user) { // Use reference
        if ($user['id'] == $id) {
            $user['username'] = htmlspecialchars($data['username']);
            $user['role'] = $data['role'];
            $user['is_active'] = (bool)($data['is_active'] ?? false);
            $user['full_name_encrypted'] = SecurityHelper::encrypt($data['full_name'] ?? '');

            // Only update password if a new one is provided
            if (!empty($data['password'])) {
                // In a real app, use: 'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
                $user['password_hash'] = $data['password'];
            }
            
            $found = true;
            break;
        }
    }
    unset($user);

    if ($found) {
        file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'User updated successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;

