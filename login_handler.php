<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * Login Handler
 *
 * Author: Gemini CLI
 * Version: 1.0.0
 *
 * This script processes the login form data.
 * For this skeleton, it uses hardcoded credentials.
 * In a real application, this would query the `users` table and
 * use password_verify() against the stored password hash.
 */

session_start();
require_once 'php/SecurityHelper.php';
require_once 'php/config.php';

$db_file = 'data/db.json';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    error_log("Login attempt for username: " . $username);

    try {
        $db_data = json_decode(file_get_contents($db_file), true);
        $users = $db_data['users'] ?? [];
        error_log("Loaded " . count($users) . " users from db.json.");

        $authenticated_user = null;
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                error_log("Found user: " . $username);
                if (password_verify($password, $user['password_hash'])) {
                    error_log("Password verified successfully for user: " . $username);
                    $authenticated_user = $user;
                    break;
                } else {
                    error_log("Password verification FAILED for user: " . $username);
                }
            }
        }

        if ($authenticated_user) {
            // --- LOGIN SUCCESS ---
            error_log("Login SUCCESS for user: " . $username);
            
            // Regenerate session ID to prevent session fixation attacks.
            session_regenerate_id(true);

            // Store user information in the session.
            $_SESSION['user_id'] = $authenticated_user['id'];
            $_SESSION['username'] = $authenticated_user['username'];
            // Decrypt full name before storing in session
            $_SESSION['full_name'] = SecurityHelper::decrypt($authenticated_user['full_name_encrypted']);
            $_SESSION['role'] = $authenticated_user['role'];
            $_SESSION['is_logged_in'] = true;
            error_log("Session variables set for user: " . $_SESSION['username'] . ", Role: " . $_SESSION['role']);

            // Redirect to the main application.
            header('Location: index.php');
            exit;

        } else {
            // --- LOGIN FAILURE ---
            error_log("Login FAILED for username: " . $username . ". Invalid credentials.");
            
            // Set an error message and redirect back to the login page.
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: login.php');
            exit;
        }

    } catch (Exception $e) {
        // Log the error for debugging (e.g., file not found, JSON decode error)
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['login_error'] = 'An unexpected error occurred. Please try again.';
        header('Location: login.php');
        exit;
    }

} else {
    // If the script is accessed directly without a POST request, redirect to login.
    error_log("login_handler.php accessed directly without POST request.");
    header('Location: login.php');
    exit;
}

