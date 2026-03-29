<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Get All Categories
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) {
        // If the db file doesn't exist, let's assume we need to create it with a categories key
        $initial_data = ['categories' => []];
        file_put_contents($db_file, json_encode($initial_data, JSON_PRETTY_PRINT));
        echo json_encode([]);
        exit;
    }

    $db_data = json_decode(file_get_contents($db_file), true);
    $categories = $db_data['categories'] ?? [];
    
    // Create a lookup for parent names
    $category_map = [];
    foreach($categories as $cat) {
        $category_map[$cat['id']] = $cat['name'];
    }

    // Add parent name to each category for display purposes
    foreach($categories as &$cat) {
        if (isset($cat['parent_id']) && isset($category_map[$cat['parent_id']])) {
            $cat['parent_name'] = $category_map[$cat['parent_id']];
        } else {
            $cat['parent_name'] = '-';
        }
    }
    unset($cat);

    echo json_encode($categories);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

exit;


