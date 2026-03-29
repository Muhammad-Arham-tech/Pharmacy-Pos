<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Update Category
 */

header('Content-Type: application/json');
$db_file = '../data/db.json';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? null;

if (empty($id) || empty($data['name'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Category ID and name are required.']);
    exit;
}

try {
    $db_data = json_decode(file_get_contents($db_file), true);
    $categories = &$db_data['categories']; // Use reference

    $found = false;
    foreach ($categories as &$category) { // Use reference
        if ($category['id'] == $id) {
            // Cannot be its own parent
            $parent_id = !empty($data['parent_id']) ? (int)$data['parent_id'] : null;
            if ($parent_id === (int)$id) {
                 http_response_code(400);
                 echo json_encode(['success' => false, 'message' => 'A category cannot be its own parent.']);
                 exit;
            }

            $category['name'] = htmlspecialchars($data['name']);
            $category['parent_id'] = $parent_id;
            $found = true;
            break;
        }
    }
    unset($category);

    if ($found) {
        file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT));
        echo json_encode(['success' => true, 'message' => 'Category updated successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Category not found.']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;


