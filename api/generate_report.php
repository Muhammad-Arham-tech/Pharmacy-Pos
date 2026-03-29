<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Generate Report
 */

header('Content-Type: application/json');

// --- Get Request Data ---
$data = json_decode(file_get_contents('php://input'), true);
$report_type = $data['report_type'] ?? '';

if (empty($report_type)) {
    http_response_code(400);
    echo json_encode(['error' => 'Report type is required.']);
    exit;
}

$db_file = '../data/db.json';
$response = [];

try {
    if (!file_exists($db_file)) throw new Exception("Database file not found.");
    $db_data = json_decode(file_get_contents($db_file), true);
    
    // --- Report Generation Logic ---
    switch ($report_type) {
        case 'daily_sales':
            $sales = $db_data['sales'] ?? [];
            $input_date = $data['date'] ?? date('Y-m-d');
            $target_date = date('Y-m-d', strtotime($input_date));
            
            $report_data = array_filter($sales, function($sale) use ($target_date) {
                // Extract just the date part (Y-m-d) from created_at datetime string
                $sale_date = date('Y-m-d', strtotime($sale['created_at']));
                return $sale_date === $target_date;
            });

            // Convert filtered data to values array to reset keys, ensuring proper JSON array
            $report_data = array_values($report_data);

            $response = [
                'title' => 'Daily Sales Report for ' . $target_date,
                'headers' => ['Transaction ID', 'Time', 'Cashier', 'Total'],
                'rows' => array_map(function($row) {
                    return [
                        substr($row['transaction_token'], 0, 12),
                        date('h:i:s A', strtotime($row['created_at'])),
                        $row['user_name'],
                        number_format($row['grand_total'], 2)
                    ];
                }, $report_data)
            ];
            break;

        case 'stock_levels':
            $stock = $db_data['stock_batches'] ?? [];
            $medicines = array_column($db_data['medicines'] ?? [], 'name', 'id');

            $response = [
                'title' => 'Current Stock Levels Report',
                'headers' => ['Medicine', 'Batch #', 'Quantity', 'Expiry Date'],
                'rows' => array_map(function($row) use ($medicines) {
                    return [
                        $medicines[$row['medicine_id']] ?? 'Unknown',
                        $row['batch_number'],
                        $row['quantity'],
                        $row['expiry_date']
                    ];
                }, $stock)
            ];
            break;

        case 'expiring_soon':
            $stock = $db_data['stock_batches'] ?? [];
            $medicines = array_column($db_data['medicines'] ?? [], 'name', 'id');
            $threshold_days = $data['threshold_days'] ?? 30;
            $threshold_date = date('Y-m-d', strtotime("+$threshold_days days"));

             $report_data = array_filter($stock, function($batch) use ($threshold_date) {
                return $batch['expiry_date'] < $threshold_date && $batch['quantity'] > 0;
            });
            
            usort($report_data, function($a, $b){
                return strtotime($a['expiry_date']) - strtotime($b['expiry_date']);
            });

            $response = [
                'title' => "Items Expiring Within {$threshold_days} Days",
                'headers' => ['Medicine', 'Batch #', 'Expiry Date', 'Qty Left'],
                'rows' => array_map(function($row) use ($medicines) {
                    return [
                        $medicines[$row['medicine_id']] ?? 'Unknown',
                        $row['batch_number'],
                        $row['expiry_date'],
                        $row['quantity']
                    ];
                }, $report_data)
            ];
            break;

        default:
            http_response_code(400);
            $response = ['error' => 'Invalid report type requested.'];
            break;
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;

