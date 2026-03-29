<?php
// api/delete_category.php
header('Content-Type: application/json');

// Function to read the database
function read_db() {
    $db_file = '../data/db.json';
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found.");
    }
    $json_data = file_get_contents($db_file);
    $data = json_decode($json_data, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding database file: " . json_last_error_msg());
    }
    return $data;
}

// Function to write to the database
function write_db($data) {
    $db_file = '../data/db.json';
    if (file_put_contents($db_file, json_encode($data, JSON_PRETTY_PRINT)) === false) {
        throw new Exception("Error writing to database file.");
    }
}

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $category_id = $input['id'] ?? null;

    if (!$category_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Category ID is required.']);
        exit;
    }

    $db = read_db();
    $categories = $db['categories'];
    $original_count = count($categories);

    // Filter out the category to be deleted
    $db['categories'] = array_values(array_filter($categories, function($cat) use ($category_id) {
        return $cat['id'] != $category_id;
    }));

    if (count($db['categories']) === $original_count) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Category not found.']);
        exit;
    }

    write_db($db);

    echo json_encode(['success' => true, 'message' => 'Category deleted successfully.']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
