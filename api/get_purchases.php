<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Purchases
 *
 * This endpoint reads from db.json and returns all purchase records.
 */

header('Content-Type: application/json');

$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) throw new Exception("Database file not found.");

    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Error decoding database file.");

    $purchases = $db_data['purchases'] ?? [];

    // Sort purchases by date, most recent first
    usort($purchases, function ($a, $b) {
        return strtotime($b['purchase_date']) - strtotime($a['purchase_date']);
    });

    echo json_encode($purchases);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Get Purchases Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


