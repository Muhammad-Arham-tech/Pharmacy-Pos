<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get Single Category
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

$id = $_GET['id'] ?? null;
if (empty($id)) {
    http_response_code(400);
    echo json_encode(['error' => 'Category ID is required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $categories = $db_data['categories'] ?? [];
    
    $found_category = null;
    foreach ($categories as $category) {
        if ($category['id'] == $id) {
            $found_category = $category;
            break;
        }
    }

    if ($found_category) {
        echo json_encode($found_category);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Category not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


