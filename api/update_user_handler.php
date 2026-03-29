<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Update User Handler
 */

error_reporting(0); // Prevent any PHP warnings from leaking into the output
ob_start();         // Start buffering to capture any unwanted output

header('Content-Type: application/json');

// Use absolute paths to ensure files are found regardless of where the script is executed from
require_once dirname(__DIR__) . '/php/SecurityHelper.php';
require_once dirname(__DIR__) . '/config.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    $db_file = dirname(__DIR__) . '/data/db.json';
    
    // Read the input JSON
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input');
    }

    // Required fields: user_id, full_name, role, status
    $userId = $data['user_id'] ?? null;
    $fullName = $data['full_name'] ?? '';
    $role = $data['role'] ?? '';
    $status = $data['status'] ?? null;
    $password = $data['password'] ?? '';

    if (empty($userId)) {
        throw new Exception('User ID is required.');
    }

    if (empty($role)) {
        throw new Exception('Role is required.');
    }

    // Load database
    if (!file_exists($db_file)) {
        throw new Exception('Database file not found.');
    }
    
    $db_data = json_decode(file_get_contents($db_file), true);
    if (!isset($db_data['users'])) {
        throw new Exception('Users table not found in database.');
    }
    
    $users = &$db_data['users'];
    $userFound = false;

    foreach ($users as &$user) {
        if ($user['id'] == $userId) {
            // Check for username conflict if username is provided
            if (isset($data['username']) && $user['username'] !== $data['username']) {
                foreach ($users as $u) {
                    if ($u['username'] === $data['username'] && $u['id'] != $userId) {
                        throw new Exception('Username already exists.');
                    }
                }
                $user['username'] = htmlspecialchars($data['username']);
            }

            // Update standard fields
            $user['role'] = $role;
            // Map status to boolean is_active
            $user['is_active'] = ($status == '1' || $status === true || $status === 'true');
            
            // Encrypt full name using SecurityHelper
            $user['full_name_encrypted'] = SecurityHelper::encrypt($fullName);

            // Update password only if a new one is provided
            if (!empty($password)) {
                 $user['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
            }

            $userFound = true;
            break;
        }
    }
    unset($user);

    if (!$userFound) {
        throw new Exception('User not found.');
    }

    // Save changes to database
    if (file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT)) === false) {
        throw new Exception('Failed to save changes to database.');
    }

    $response = ['status' => 'success', 'message' => 'User updated successfully.'];

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

ob_clean(); // Clear any accidental whitespace or warnings from the buffer
echo json_encode($response);
exit;