<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Delete User Handler
 */

error_reporting(0); // Prevent PHP warnings
ob_start();         // Buffer output

header('Content-Type: application/json');

// Use absolute paths for config
require_once dirname(__DIR__) . '/config.php';

$response = ['status' => 'error', 'message' => 'An unknown error occurred.'];

try {
    $db_file = dirname(__DIR__) . '/data/db.json';
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON input.');
    }

    $userId = $data['id'] ?? null;

    if (empty($userId)) {
        throw new Exception('User ID is required.');
    }

    // SAFETY CHECK: Prevent deletion of Admin (ID 1)
    if ($userId == 1) {
        throw new Exception('Cannot delete the main Administrator account.');
    }

    if (!file_exists($db_file)) {
        throw new Exception('Database file not found.');
    }

    $db_data = json_decode(file_get_contents($db_file), true);
    
    if (!isset($db_data['users'])) {
        throw new Exception('Users table not found.');
    }

    $users = $db_data['users'];
    $initialCount = count($users);
    
    // Filter out the user to be deleted
    $db_data['users'] = array_values(array_filter($users, function($user) use ($userId) {
        return $user['id'] != $userId;
    }));

    if (count($db_data['users']) == $initialCount) {
        throw new Exception('User not found.');
    }

    if (file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT)) === false) {
        throw new Exception('Failed to save changes to database.');
    }

    $response = ['status' => 'success', 'message' => 'User deleted successfully.'];

} catch (Exception $e) {
    $response = ['status' => 'error', 'message' => $e->getMessage()];
}

ob_clean(); // Clean buffer
echo json_encode($response);
exit;
