<?php
$db_file = './data/db.json';
if (file_exists($db_file)) {
    echo "db.json exists.\n";
    $content = file_get_contents($db_file);
    if ($content === false) {
        echo "Failed to read db.json.\n";
    } else {
        echo "Successfully read db.json. File size: " . strlen($content) . " bytes.\n";
        $data = json_decode($content, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo "Failed to decode db.json: " . json_last_error_msg() . "\n";
        } else {
            echo "Successfully decoded db.json. Contains " . count($data) . " top-level keys.\n";
        }
    }
} else {
    echo "db.json does NOT exist at " . realpath($db_file) . ".\n";
}
?>
