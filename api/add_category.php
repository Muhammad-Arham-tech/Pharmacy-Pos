<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Add Category
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

$data = json_decode(file_get_contents('php://input'), true);

if (empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category name is required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $categories = $db_data['categories'] ?? [];

    $new_id = count($categories) > 0 ? max(array_column($categories, 'id')) + 1 : 1;
    
    $new_category = [
        'id' => $new_id,
        'name' => htmlspecialchars($data['name']),
        'parent_id' => !empty($data['parent_id']) ? (int)$data['parent_id'] : null
    ];

    $db_data['categories'][] = $new_category;
    file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true, 'message' => 'Category added successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;


