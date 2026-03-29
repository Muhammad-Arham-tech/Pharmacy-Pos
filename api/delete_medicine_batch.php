<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Delete Medicine Batch (Soft Delete)
 */

header('Content-Type: application/json');

// --- Get Request Data ---
$data = json_decode(file_get_contents('php://input'), true);
$batch_id = $data['batch_id'] ?? null;

if (empty($batch_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Batch ID is required.']);
    exit;
}

$db_file = '../data/db.json';

try {
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found.");
    }
    
    $db_data = json_decode(file_get_contents($db_file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding database file.");
    }

    $stock_batches = $db_data['stock_batches'] ?? [];
    $batch_found = false;
    
    // Find the batch and update its quantity
    foreach ($stock_batches as $key => $batch) {
        if ($batch['id'] == $batch_id) {
            // Soft delete by setting quantity to 0
            $stock_batches[$key]['quantity'] = 0;
            // Optionally, add a status field for clarity
            $stock_batches[$key]['status'] = 'deleted'; 
            $batch_found = true;
            break;
        }
    }

    if (!$batch_found) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Batch not found.']);
        exit;
    }

    // Update the main data array and save back to the file
    $db_data['stock_batches'] = $stock_batches;
    if (file_put_contents($db_file, json_encode($db_data, JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true, 'message' => 'Batch has been deleted successfully.']);
    } else {
        throw new Exception("Could not write to the database file. Check permissions.");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

exit;
