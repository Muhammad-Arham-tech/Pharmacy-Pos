<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Single User
 */

ini_set('display_errors', 0); // Do not display errors to the browser
error_reporting(E_ALL); // Report all errors

header('Content-Type: application/json');
require_once '../php/SecurityHelper.php';
require_once '../php/config.php'; // Ensure ENCRYPTION_KEY is defined

$db_file = '../data/db.json';

$id = $_GET['id'] ?? null;
// error_log('get_user.php: Received ID: ' . $id); // Keep this log for backend debugging

if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'User ID is required.']);
    exit;
}

try {
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found: " . $db_file);
    }
    $file_content = file_get_contents($db_file);
    // error_log('get_user.php: db.json content length: ' . strlen($file_content)); // Keep for backend debugging
    
    $db_data = json_decode($file_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding database file: " . json_last_error_msg());
    }
    // error_log('get_user.php: Decoded db_data keys: ' . implode(', ', array_keys($db_data))); // Keep for backend debugging

    $users = $db_data['users'] ?? [];
    // error_log('get_user.php: Number of users in db.json: ' . count($users)); // Keep for backend debugging
    
    $found_user = null;
    foreach ($users as $user) {
        if ($user['id'] == $id) {
            // error_log('get_user.php: Found user with ID: ' . $id); // Keep for backend debugging
            
            // --- Robust Decryption Handling ---
            if (isset($user['full_name_encrypted']) && is_string($user['full_name_encrypted']) && !empty($user['full_name_encrypted'])) {
                try {
                    // error_log('get_user.php: Attempting to decrypt full_name_encrypted for user ID ' . $id); // Keep for backend debugging
                    $decrypted_name = SecurityHelper::decrypt($user['full_name_encrypted']);
                    if ($decrypted_name === false) {
                        error_log('get_user.php: Decryption failed for user ID ' . $id . ' with encrypted data: ' . $user['full_name_encrypted']);
                        $user['full_name'] = 'Decryption Failed'; // Provide a clear message for the frontend
                    } else {
                        $user['full_name'] = $decrypted_name;
                        // error_log('get_user.php: Decrypted full_name: ' . $user['full_name']); // Keep for backend debugging
                    }
                } catch (Exception $decrypt_e) {
                    error_log('get_user.php: Exception during decryption for user ID ' . $id . ': ' . $decrypt_e->getMessage());
                    $user['full_name'] = 'Decryption Error'; // Provide a clear message for the frontend
                }
            } else {
                $user['full_name'] = 'N/A (No encrypted name or invalid format)';
                // error_log('get_user.php: No encrypted full_name found, or it was not a string/empty for user ID ' . $id); // Keep for backend debugging
            }
            
            unset($user['password_hash']);
            unset($user['full_name_encrypted']); // Remove encrypted field from response
            $found_user = $user;
            break;
        }
    }

    if ($found_user) {
        // error_log('get_user.php: Returning found user: ' . print_r($found_user, true)); // Keep for backend debugging
        echo json_encode($found_user);
    } else {
        http_response_code(404);
        error_log('get_user.php: User with ID ' . $id . ' not found.');
        echo json_encode(['error' => 'User not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    $error_message = $e->getMessage();
    error_log('get_user.php: Caught top-level Exception: ' . $error_message);
    echo json_encode(['error' => 'Server error: ' . $error_message]); // Return full error message to frontend
}

exit;



