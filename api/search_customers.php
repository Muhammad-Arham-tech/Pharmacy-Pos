<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Search Customers
 */

header('Content-Type: application/json');

$query = $_GET['q'] ?? '';
$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);
    $customers = $db_data['customers'] ?? [];

    $results = [];
    if (strlen($query) < 2) {
        // Return latest 10 customers if query is short
        $results = array_slice(array_reverse($customers), 0, 10);
    } else {
        foreach ($customers as $customer) {
            if (stripos($customer['name'], $query) !== false || stripos($customer['phone'], $query) !== false) {
                $results[] = $customer;
            }
        }
    }

    echo json_encode($results);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
