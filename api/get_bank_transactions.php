<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Bank Transactions
 */

header('Content-Type: application/json');

$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) {
        // If the file doesn't exist, return an empty array, which is a valid state.
        echo json_encode([]);
        exit;
    }

    $db_data = json_decode(file_get_contents($db_file), true);
    
    $transactions = $db_data['bank_transactions'] ?? [];

    // Return transactions in reverse chronological order (newest first)
    echo json_encode(array_reverse($transactions));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to read transaction data.']);
}

exit;
