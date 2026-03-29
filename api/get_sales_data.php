<?php
session_start();
require_once '../php/SecurityHelper.php';
require_once '../php/config.php';

SecurityHelper::require_logged_in();

header('Content-Type: application/json');

$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found.");
    }
    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding database file.");
    }

    $sales = $db_data['sales'] ?? [];
    error_log('get_sales_data.php: Number of sales records found: ' . count($sales));

    $period = $_GET['period'] ?? 'monthly'; // default to monthly
    error_log('get_sales_data.php: Requested period: ' . $period);

    $aggregated_sales = [];

    foreach ($sales as $sale) {
        if (!isset($sale['created_at']) || empty($sale['created_at'])) {
            error_log('get_sales_data.php: Skipping sale record with missing or empty created_at. Sale data: ' . print_r($sale, true));
            continue; // Skip records with invalid date
        }
        
        $sale_date = new DateTime($sale['created_at']);
        $total_amount = (float)($sale['grand_total'] ?? 0);

        $key = '';
        switch ($period) {
            case 'daily':
                $key = $sale_date->format('Y-m-d'); // e.g., "2023-12-23"
                break;
            case 'weekly':
                $key = $sale_date->format('o-W'); // ISO year and week number, e.g., "2023-51"
                break;
            case 'monthly':
                $key = $sale_date->format('Y-M'); // e.g., "2023-Dec"
                break;
            case 'yearly':
                $key = $sale_date->format('Y'); // e.g., "2023"
                break;
            default:
                $key = $sale_date->format('Y-M'); // default to monthly
                break;
        }

        if (!isset($aggregated_sales[$key])) {
            $aggregated_sales[$key] = 0;
        }
        $aggregated_sales[$key] += $total_amount;
    }

    // Sort the aggregated sales by key (date/period)
    ksort($aggregated_sales);
    error_log('get_sales_data.php: Aggregated sales data: ' . print_r($aggregated_sales, true));

    echo json_encode(['success' => true, 'labels' => array_keys($aggregated_sales), 'data' => array_values($aggregated_sales)]);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Sales Data Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;
