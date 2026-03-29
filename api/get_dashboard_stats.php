<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Dashboard Stats
 */

header('Content-Type: application/json');

$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) throw new Exception("Database file not found.");

    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) throw new Exception("Error decoding database file.");

    $sales = $db_data['sales'] ?? [];
    $sale_items = $db_data['sale_items'] ?? [];
    $stock_batches = $db_data['stock_batches'] ?? [];

    // --- CALCULATE STATS ---
    $todays_sales_total = 0;
    $items_sold_today = 0;
    $expiring_soon_count = 0;
    $out_of_stock_count = 0;
    
    $today_date = date('Y-m-d');
    $expiring_soon_threshold = date('Y-m-d', strtotime('+1 day'));

    // Calculate Today's Sales & Items Sold
    foreach ($sales as $sale) {
        $sale_date = date('Y-m-d', strtotime($sale['created_at']));
        if ($sale_date === $today_date) {
            $todays_sales_total += $sale['grand_total'];
            
            // Find related sale items
            foreach($sale_items as $item) {
                if ($item['sale_id'] === $sale['id']) {
                    $items_sold_today += $item['quantity'];
                }
            }
        }
    }

    // Calculate Stock-based stats
    foreach ($stock_batches as $batch) {
        if ($batch['quantity'] <= 0) {
            $out_of_stock_count++;
        }
        if ($batch['expiry_date'] < $expiring_soon_threshold && $batch['expiry_date'] > $today_date) {
            $expiring_soon_count++;
        }
    }

    // --- PREPARE RESPONSE ---
    $stats = [
        'todays_sales' => number_format($todays_sales_total, 2),
        'items_sold_today' => $items_sold_today,
        'expiring_soon' => $expiring_soon_count,
        'out_of_stock' => $out_of_stock_count
    ];

    echo json_encode($stats);

} catch (Exception $e) {
    http_response_code(500);
    error_log('Get Dashboard Stats Error: ' . $e->getMessage());
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


