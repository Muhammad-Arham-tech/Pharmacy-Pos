<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * API Endpoint: Add Medicine
 *
 * This endpoint now saves the new medicine data to a JSON file (db.json).
 */

header('Content-Type: application/json');
error_log('Add Medicine: Script started.'); // LOG: Script start

// --- BASIC VALIDATION ---
$json_data = file_get_contents('php://input');
error_log('Add Medicine: Received raw JSON input: ' . $json_data); // LOG: Raw input
$data = json_decode($json_data, true);

if (!$data) {
    http_response_code(400);
    $error_message = 'Invalid JSON data received.';
    error_log('Add Medicine: Error - ' . $error_message); // LOG: Error
    echo json_encode(['success' => false, 'message' => $error_message]);
    exit;
}
error_log('Add Medicine: Decoded input data: ' . print_r($data, true)); // LOG: Decoded data

$required_fields = [
    'name', 'medicine_type', 'batch_number', 'expiry_date', 'quantity', 
    'mrp', 'cost_price', 'selling_price', 'tax_rate'
];
foreach ($required_fields as $field) {
    if (empty($data[$field]) && !($field === 'tax_rate' && isset($data[$field]) && $data[$field] === 0.0)) { // Allow 0 for tax_rate
        http_response_code(400);
        $error_message = "Field '{$field}' is required and cannot be empty.";
        error_log('Add Medicine: Error - ' . $error_message); // LOG: Error
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
}
error_log('Add Medicine: Required fields validated.'); // LOG: Validation success

// --- FILE-BASED DATABASE LOGIC ---
$db_file = '../data/db.json';

try {
    // 1. Read the existing database file.
    if (!file_exists($db_file)) {
        throw new Exception("Database file not found at " . realpath($db_file));
    }
    error_log('Add Medicine: Database file found: ' . realpath($db_file)); // LOG: File found

    $db_content = file_get_contents($db_file);
    if ($db_content === false) {
        throw new Exception("Failed to read database file content.");
    }
    error_log('Add Medicine: Database file content read successfully. Size: ' . strlen($db_content) . ' bytes.'); // LOG: Content read

    $db_data = json_decode($db_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error decoding JSON from database file: " . json_last_error_msg());
    }
    error_log('Add Medicine: Database content decoded successfully. Keys: ' . implode(', ', array_keys($db_data))); // LOG: Decoded DB

    // Initialize arrays if they don't exist
    $db_data['medicines'] = $db_data['medicines'] ?? [];
    $db_data['stock_batches'] = $db_data['stock_batches'] ?? [];
    error_log('Add Medicine: DB arrays initialized.'); // LOG: Arrays init

    // 2. Generate new IDs.
    $new_medicine_id = count($db_data['medicines']) > 0 ? max(array_column($db_data['medicines'], 'id')) + 1 : 1;
    $new_batch_id = count($db_data['stock_batches']) > 0 ? max(array_column($db_data['stock_batches'], 'id')) + 1 : 1;
    error_log("Add Medicine: Generated IDs - Medicine: {$new_medicine_id}, Batch: {$new_batch_id}"); // LOG: IDs generated

    // 3. Create the new medicine record.
    $new_medicine = [
        "id" => $new_medicine_id,
        "name" => htmlspecialchars($data['name']),
        "medicine_type" => htmlspecialchars($data['medicine_type']),
        "barcode" => htmlspecialchars($data['barcode'] ?? ''),
        "strength" => htmlspecialchars($data['strength'] ?? ''),
        "category_id" => (int)($data['category_id'] ?? null),
        "manufacturer_id" => (int)($data['manufacturer_id'] ?? null),
        "generic_salt_id" => (int)($data['generic_salt_id'] ?? null),
        "mrp" => (float)$data['mrp'],
        "tax_rate" => (float)$data['tax_rate'],
        "requires_prescription" => (bool)($data['requires_prescription'] ?? false)
    ];
    error_log('Add Medicine: New medicine record created: ' . print_r($new_medicine, true)); // LOG: New medicine

    // 4. Create the new stock batch record.
    $new_batch = [
        "id" => $new_batch_id,
        "medicine_id" => $new_medicine_id,
        "batch_number" => htmlspecialchars($data['batch_number']),
        "expiry_date" => $data['expiry_date'],
        "quantity" => (int)$data['quantity'],
        "cost_price" => (float)$data['cost_price'],
        "selling_price" => (float)$data['selling_price']
    ];
    error_log('Add Medicine: New batch record created: ' . print_r($new_batch, true)); // LOG: New batch

    // 5. Add the new records to the data arrays.
    $db_data['medicines'][] = $new_medicine;
    $db_data['stock_batches'][] = $new_batch;
    error_log('Add Medicine: Records added to DB data in memory.'); // LOG: Records added to array

    // 6. Write the updated data back to the file.
    $updated_db_content = json_encode($db_data, JSON_PRETTY_PRINT);
    if ($updated_db_content === false) {
        throw new Exception("Failed to encode updated database content to JSON: " . json_last_error_msg());
    }
    error_log('Add Medicine: Updated DB content encoded. Size: ' . strlen($updated_db_content) . ' bytes.'); // LOG: Encoded DB

    $bytes_written = file_put_contents($db_file, $updated_db_content);

    if ($bytes_written === false) {
        http_response_code(500);
        $error_message = 'Critical Error: Could not write to the database file. Please check server file permissions for the data directory.';
        error_log('Add Medicine: Error - ' . $error_message . ' (realpath: ' . realpath($db_file) . ')'); // LOG: Write error
        echo json_encode(['success' => false, 'message' => $error_message]);
        exit;
    }
    error_log('Add Medicine: Database file written successfully. Bytes: ' . $bytes_written); // LOG: Write success

    // --- SUCCESS RESPONSE ---
    $success_message = "Successfully added '" . $new_medicine['name'] . "' (Batch: " . $new_batch['batch_number'] . ") to the stock.";
    error_log('Add Medicine: Success - ' . $success_message); // LOG: Success
    echo json_encode([
        'success' => true,
        'message' => $success_message,
        'medicine_id' => $new_medicine_id
    ]);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    $error_message = 'An internal server error occurred while adding the medicine: ' . $e->getMessage();
    error_log('Add Medicine: Caught Exception - ' . $e->getMessage() . ' Trace: ' . $e->getTraceAsString()); // LOG: Caught exception
    
    echo json_encode([
        'success' => false,
        'message' => $error_message
    ]);
}

exit;

