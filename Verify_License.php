<?php
// Verify_License.php
// INCLUDE THIS FILE AT THE VERY TOP OF 'index.php' or 'config.php'

require_once __DIR__ . '/php/LicenseSystem.php';

// Path to where the license key is stored on the client's machine
// In a real scenario, this might be in a file like 'license.key' or a DB entry.
// For this standalone setup, we'll look for a 'license.key' file in the root or data folder.
$licenseFile = __DIR__ . '/data/license.key';

// 1. Check if License File Exists
if (!file_exists($licenseFile)) {
    // Attempt to see if it's in the root
    if (file_exists(__DIR__ . '/license.key')) {
        $licenseFile = __DIR__ . '/license.key';
    } else {
        showLicenseError("No License Found", "Please activate your software.", true);
    }
}

// 2. Read License
$licenseKey = trim(file_get_contents($licenseFile));
if (empty($licenseKey)) {
    showLicenseError("Empty License File", "License file is empty.", true);
}

// 3. Verify
$result = LicenseSystem::verifyLicense($licenseKey);

if (!$result['valid']) {
    showLicenseError("License Error", $result['message']);
}

// 4. (Optional) Warning for expiring soon (e.g., less than 7 days)
if ($result['days_left'] < 7 && $result['days_left'] > 0) {
    echo "<div style='background: #fff3cd; color: #856404; padding: 10px; text-align: center; border-bottom: 1px solid #ffeeba;'>
            ⚠️ Your license expires in {$result['days_left']} days. Please contact support to renew.
          </div>";
}

// ==========================================================
// HELPER FUNCTION: Block Access and Show Error Page
// ==========================================================
function showLicenseError($title, $message, $showHWID = false) {
    $hwid = LicenseSystem::getHWID();
    http_response_code(403);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Software Activation Required</title>
        <style>
            body { font-family: sans-serif; background: #eee; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
            .box { background: white; padding: 40px; border-radius: 8px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); text-align: center; max-width: 500px; width: 100%; }
            h1 { color: #dc3545; margin-top: 0; }
            p { color: #555; line-height: 1.6; }
            .hwid-box { background: #f8f9fa; border: 1px solid #ddd; padding: 15px; margin: 20px 0; font-family: monospace; font-size: 1.1em; word-break: break-all; user-select: all; }
            .contact { margin-top: 20px; font-weight: bold; }
            button { background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin-top: 10px; }
            button:hover { background: #0056b3; }
        </style>
    </head>
    <body>
        <div class="box">
            <h1>🚫 Access Denied</h1>
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <p><?php echo htmlspecialchars($message); ?></p>
            
            <?php if ($showHWID || true): // Always show HWID on error so user can send it ?>
                <p>Please send this Hardware ID to your administrator:</p>
                <div class="hwid-box"><?php echo $hwid; ?></div>
                <button onclick="navigator.clipboard.writeText('<?php echo $hwid; ?>')">Copy HWID</button>
            <?php endif; ?>
            
            <div class="contact">
                Contact Support: admin@pharmacypos.com
            </div>
            
            <?php if (isset($_POST['activate_license'])): 
                // Simple activation handler for the error page
                $newKey = $_POST['new_license_key'] ?? '';
                if ($newKey) {
                    file_put_contents(__DIR__ . '/data/license.key', $newKey);
                    echo "<script>window.location.reload();</script>";
                }
            ?>
            <?php else: ?>
                <hr>
                <form method="POST" style="margin-top: 20px;">
                    <label>Have a new key?</label><br>
                    <textarea name="new_license_key" rows="3" style="width: 100%; margin-top: 5px;" placeholder="Paste license key here..."></textarea>
                    <button type="submit" name="activate_license">Activate</button>
                </form>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit(); // STOP EXECUTION OF THE REST OF THE APP
}
?>