<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Sales
 *
 * This endpoint reads from db.json and returns all sales records.
 */

header('Content-Type: application/json');

$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) throw new Exception("Database file not found.");

    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Error decoding database file.");

    $sales = $db_data['sales'] ?? [];

    // Sort sales by date, most recent first
    usort($sales, function ($a, $b) {
        return strtotime($b['created_at']) - strtotime($a['created_at']);
    });

    echo json_encode($sales);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Get Sales Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


