<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Sales Analytics for Chart
 */

header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config.php';

$type = $_GET['type'] ?? 'monthly';
$db_file = dirname(__DIR__) . '/data/db.json';

if (!file_exists($db_file)) {
    echo json_encode(['success' => false, 'message' => 'Database not found.']);
    exit;
}

$db_data = json_decode(file_get_contents($db_file), true);
$sales = $db_data['sales'] ?? [];

$labels = [];
$values = [];

// Helper to sort by date keys (YYYY-MM-DD or YYYY-MM or YYYY)
function ksort_dates(&$array) {
    ksort($array);
}

try {
    $aggregated = [];

    switch ($type) {
        case 'daily':
            // Last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $aggregated[$date] = 0;
            }
            
            foreach ($sales as $sale) {
                $saleDate = date('Y-m-d', strtotime($sale['created_at']));
                if (isset($aggregated[$saleDate])) {
                    $aggregated[$saleDate] += (float)$sale['grand_total'];
                }
            }
            
            // Format labels for UI (e.g., "Mon", "Tue")
            foreach ($aggregated as $date => $total) {
                $labels[] = date('D', strtotime($date));
                $values[] = $total;
            }
            break;

        case 'monthly':
            // All months of current year
            $currentYear = date('Y');
            for ($m = 1; $m <= 12; $m++) {
                $monthKey = sprintf('%s-%02d', $currentYear, $m);
                $aggregated[$monthKey] = 0;
            }

            foreach ($sales as $sale) {
                $saleYear = date('Y', strtotime($sale['created_at']));
                if ($saleYear === $currentYear) {
                    $monthKey = date('Y-m', strtotime($sale['created_at']));
                    if (isset($aggregated[$monthKey])) {
                        $aggregated[$monthKey] += (float)$sale['grand_total'];
                    }
                }
            }

            foreach ($aggregated as $monthKey => $total) {
                $labels[] = date('M', strtotime($monthKey . '-01'));
                $values[] = $total;
            }
            break;

        case 'yearly':
            // Last 5 years
            $currentYear = date('Y');
            for ($i = 4; $i >= 0; $i--) {
                $year = (string)($currentYear - $i);
                $aggregated[$year] = 0;
            }

            foreach ($sales as $sale) {
                $saleYear = date('Y', strtotime($sale['created_at']));
                if (isset($aggregated[$saleYear])) {
                    $aggregated[$saleYear] += (float)$sale['grand_total'];
                }
            }

            foreach ($aggregated as $year => $total) {
                $labels[] = $year;
                $values[] = $total;
            }
            break;
            
        default:
            // Fallback to monthly logic if invalid type
            // (Same as monthly case)
             $currentYear = date('Y');
            for ($m = 1; $m <= 12; $m++) {
                $monthKey = sprintf('%s-%02d', $currentYear, $m);
                $aggregated[$monthKey] = 0;
            }

            foreach ($sales as $sale) {
                $saleYear = date('Y', strtotime($sale['created_at']));
                if ($saleYear === $currentYear) {
                    $monthKey = date('Y-m', strtotime($sale['created_at']));
                    if (isset($aggregated[$monthKey])) {
                        $aggregated[$monthKey] += (float)$sale['grand_total'];
                    }
                }
            }

            foreach ($aggregated as $monthKey => $total) {
                $labels[] = date('M', strtotime($monthKey . '-01'));
                $values[] = $total;
            }
            break;
    }

    echo json_encode(['success' => true, 'labels' => $labels, 'values' => $values]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
exit;
