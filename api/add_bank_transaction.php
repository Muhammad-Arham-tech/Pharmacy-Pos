<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Add Bank Transaction
 */

header('Content-Type: application/json');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    http_response_code(401); // Unauthorized
    echo json_encode(['error' => 'User not logged in.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// --- Basic Validation ---
if (empty($data['t_date']) || empty($data['t_type']) || !isset($data['t_amount']) || empty($data['t_desc'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'All fields are required.']);
    exit;
}

$db_file = '../data/db.json';
$response = [];

try {
    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);

    // Get the latest transaction to calculate the running balance
    $last_balance = 0;
    if (!empty($db_data['bank_transactions'])) {
        $last_transaction = end($db_data['bank_transactions']);
        $last_balance = $last_transaction['balance'];
    }

    $amount = floatval($data['t_amount']);
    $new_balance = 0;

    if ($data['t_type'] === 'credit') {
        $new_balance = $last_balance + $amount;
    } else { // debit
        $new_balance = $last_balance - $amount;
    }

    $new_transaction = [
        'id' => count($db_data['bank_transactions'] ?? []) + 1,
        'user_id' => $_SESSION['user_id'],
        'user_name' => $_SESSION['username'],
        'transaction_time' => $data['t_date'],
        'type' => $data['t_type'],
        'description' => $data['t_desc'],
        'amount' => $amount,
        'balance' => $new_balance
    ];

    $db_data['bank_transactions'][] = $new_transaction;
    
    // Save the updated data back to the file
    if (file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT))) {
        http_response_code(201); // Created
        $response = ['success' => true, 'message' => 'Transaction added successfully.', 'transaction' => $new_transaction];
    } else {
        throw new Exception("Could not write to the database file.");
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => $e->getMessage()]);
}

exit;
