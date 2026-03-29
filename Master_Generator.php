<?php
// Include the Core System
require_once __DIR__ . '/php/LicenseSystem.php';

$message = '';
$generatedKey = '';

// Default configuration
$defaultDays = 365;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $clientName = $_POST['client_name'] ?? '';
    $hwid = $_POST['hwid'] ?? '';
    $days = intval($_POST['days'] ?? $defaultDays);

    if ($clientName && $hwid && $days > 0) {
        $expiryDate = date('Y-m-d H:i:s', strtotime("+$days days"));
        $generatedKey = LicenseSystem::generateLicenseKey($clientName, $hwid, $expiryDate);
        $message = "License Generated Successfully!";
    } else {
        $message = "Error: Please fill all fields correctly.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master License Generator</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h2 { margin-top: 0; color: #333; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"], input[type="number"] { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
        button { background: #007bff; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-size: 16px; width: 100%; }
        button:hover { background: #0056b3; }
        .result { margin-top: 20px; background: #e9ecef; padding: 15px; border-radius: 4px; word-break: break-all; }
        .result h3 { margin-top: 0; font-size: 16px; }
        .copy-btn { margin-top: 10px; background: #28a745; width: auto; display: inline-block; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 20px; background: #d4edda; color: #155724; }
        .alert.error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>

<div class="container">
    <h2>🔐 POS License Generator</h2>
    
    <?php if ($message): ?>
        <div class="alert <?php echo strpos($message, 'Error') !== false ? 'error' : ''; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-group">
            <label>Client Name / Pharmacy Name</label>
            <input type="text" name="client_name" required placeholder="e.g. City Pharmacy">
        </div>

        <div class="form-group">
            <label>Client Hardware ID (HWID)</label>
            <input type="text" name="hwid" required placeholder="Paste HWID from Client Machine">
            <small style="color:#666;">On client machine run: <code>wmic csproduct get uuid</code> & <code>wmic diskdrive get serialnumber</code></small>
        </div>

        <div class="form-group">
            <label>Validity (Days)</label>
            <input type="number" name="days" value="<?php echo $defaultDays; ?>" required>
        </div>

        <button type="submit">Generate License Key</button>
    </form>

    <?php if ($generatedKey): ?>
        <div class="result">
            <h3>GENERATED LICENSE KEY:</h3>
            <div style="font-family: monospace; background: #fff; padding: 10px; border: 1px solid #ccc; margin-bottom: 10px;">
                <?php echo $generatedKey; ?>
            </div>
            <button class="copy-btn" onclick="navigator.clipboard.writeText('<?php echo $generatedKey; ?>')">Copy to Clipboard</button>
        </div>
    <?php endif; ?>
</div>

</body>
</html>