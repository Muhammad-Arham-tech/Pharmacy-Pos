<?php
// check_license_status.php
// Diagnostic tool to inspect the current license state

require_once __DIR__ . '/php/LicenseSystem.php';

$licenseFile = __DIR__ . '/data/license.key';
$status = "No License File Found";
$isValid = false;
$details = [];
$currentHWID = LicenseSystem::getHWID();

if (file_exists($licenseFile)) {
    $keyContent = trim(file_get_contents($licenseFile));
    if ($keyContent) {
        $result = LicenseSystem::verifyLicense($keyContent);
        $isValid = $result['valid'];
        $status = $isValid ? "VALID" : "INVALID";
        
        // Decode details for display
        $payload = base64_decode($keyContent);
        $ivLength = openssl_cipher_iv_length('AES-256-CBC');
        if (strlen($payload) >= $ivLength) {
            $details = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>License Status - Med-Quick</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
    <style>
        :root {
            --bg-dark: #0D1117;
            --card-dark: #161B22;
            --accent-green: #2ecc71;
            --accent-red: #e74c3c;
            --text-primary: #c9d1d9;
            --text-secondary: #8b949e;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: var(--bg-dark);
            color: var(--text-primary);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            width: 100%;
            max-width: 600px;
            background-color: var(--card-dark);
            border: 1px solid #30363d;
            border-radius: 12px;
            padding: 2.5rem;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
        }
        h1 { margin-bottom: 1.5rem; color: var(--text-primary); }
        .status-icon { font-size: 4rem; margin-bottom: 1rem; }
        .valid { color: var(--accent-green); }
        .invalid { color: var(--accent-red); }
        
        .message-box {
            background: rgba(255,255,255,0.05);
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
            text-align: left;
        }
        .hwid-container {
            background: #000;
            padding: 1rem;
            border-radius: 6px;
            border: 1px solid #30363d;
            margin: 1rem 0;
            font-family: monospace;
            font-size: 1.1rem;
            word-break: break-all;
            color: var(--accent-green);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .copy-btn {
            background: none;
            border: none;
            color: var(--text-secondary);
            cursor: pointer;
            transition: color 0.2s;
        }
        .copy-btn:hover { color: #fff; }
        
        .btn {
            display: block;
            width: 100%;
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: transform 0.1s, opacity 0.2s;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:active { transform: scale(0.98); }
        .btn-whatsapp { background-color: #25D366; color: white; }
        .btn-email { background-color: #007bff; color: white; }
        .btn-back { background-color: #333; color: #ccc; margin-top: 1rem;}
        
        p { line-height: 1.6; color: var(--text-secondary); }
        strong { color: var(--text-primary); }
    </style>
</head>
<body>

    <div class="container">
        <?php if ($isValid): ?>
            <!-- VALID LICENSE UI -->
            <div class="status-icon valid"><i class="fas fa-check-circle"></i></div>
            <h1 style="color: var(--accent-green);">License Active</h1>
            <p>Your software is fully activated and ready to use.</p>
            
            <div class="message-box">
                <p><strong>Client:</strong> <?php echo htmlspecialchars($details['client'] ?? 'Unknown'); ?></p>
                <p><strong>Days Remaining:</strong> <?php echo $details['days_left'] ?? 'N/A'; ?> days</p>
                <p><strong>Expires:</strong> <?php echo $details['expires'] ?? 'N/A'; ?></p>
            </div>
            
            <a href="index.php" class="btn" style="background-color: var(--accent-green); color: #000;">Go to Dashboard</a>

        <?php else: ?>
            <!-- INVALID / NOT ACTIVATED UI -->
            <div class="status-icon invalid"><i class="fas fa-times-circle"></i></div>
            <h1>Software Not Activated</h1>
            
            <p style="font-size: 1.1rem; color: var(--text-primary);">
                Your software is not activated. Please provide your Hardware ID to <strong>Arham Ali</strong> to get a key.
            </p>

            <div class="message-box">
                <p style="margin-bottom: 0.5rem; font-size: 0.9rem;">Your Hardware ID (HWID):</p>
                <div class="hwid-container">
                    <span id="hwid-text"><?php echo $currentHWID; ?></span>
                    <button class="copy-btn" onclick="copyHWID()" title="Copy to clipboard"><i class="far fa-copy"></i></button>
                </div>
            </div>

            <a href="https://wa.me/923278047689?text=Assalam-o-Alaikum%20Arham,%20here%20is%20my%20HWID:%20<?php echo urlencode($currentHWID); ?>%20-%20Please%20provide%20a%20license%20key." target="_blank" class="btn btn-whatsapp">
                <i class="fab fa-whatsapp"></i> Contact on WhatsApp
            </a>
            
            <a href="mailto:arhamrehmani048@gmail.com?subject=License%20Activation%20Request&body=Here%20is%20my%20HWID:%20<?php echo $currentHWID; ?>" class="btn btn-email">
                <i class="fas fa-envelope"></i> Contact via Email
            </a>
            
            <?php if (file_exists($licenseFile)): ?>
                <p style="font-size: 0.8rem; margin-top: 1rem;">Error Detail: <?php echo htmlspecialchars($details['message'] ?? $status); ?></p>
            <?php endif; ?>

        <?php endif; ?>
    </div>

    <script>
        function copyHWID() {
            const hwid = document.getElementById('hwid-text').innerText;
            navigator.clipboard.writeText(hwid).then(() => {
                alert('Hardware ID copied to clipboard!');
            });
        }
    </script>
</body>
</html>
