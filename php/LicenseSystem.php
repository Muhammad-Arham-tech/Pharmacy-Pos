<?php
/**
 * License System Core Logic
 * Handles Encryption, HWID generation, and Validation.
 */

class LicenseSystem {
    // ==========================================
    // CONFIGURATION
    // ==========================================
    private const SECRET_KEY = 'CHANGE_THIS_TO_A_VERY_LONG_RANDOM_STRING_FOR_PRODUCTION'; 
    private const ENCRYPTION_METHOD = 'AES-256-CBC';
    private const STATE_FILE = __DIR__ . '/../data/.license_state'; // Hidden file to store time data

    /**
     * Generates a unique Hardware ID for the machine.
     * Uses Motherboard UUID and Disk Serial Number.
     */
    public static function getHWID() {
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows: Get UUID and Disk Serial
            $uuid = self::runCommand('wmic csproduct get uuid');
            $disk = self::runCommand('wmic diskdrive get serialnumber');
            return hash('sha256', $uuid . $disk);
        } else {
            // Fallback for Linux/Mac (if moved later)
            $uuid = self::runCommand('cat /sys/class/dmi/id/product_uuid');
            return hash('sha256', $uuid);
        }
    }

    /**
     * Encrypts the license data into a license key.
     */
    public static function generateLicenseKey($clientName, $hwid, $expiryDate) {
        $data = json_encode([
            'client' => $clientName,
            'hwid' => $hwid,
            'expiry' => $expiryDate,
            'issue_date' => time()
        ]);

        $key = hash('sha256', self::SECRET_KEY, true);
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length(self::ENCRYPTION_METHOD));
        $encrypted = openssl_encrypt($data, self::ENCRYPTION_METHOD, $key, 0, $iv);

        // Return IV + Encrypted Data (Base64 encoded)
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypts and Validates a license key.
     * Returns: ['valid' => bool, 'message' => string, 'days_left' => int]
     */
    public static function verifyLicense($licenseKey) {
        // 1. Decode
        $payload = base64_decode($licenseKey);
        $ivLength = openssl_cipher_iv_length(self::ENCRYPTION_METHOD);
        
        if (strlen($payload) < $ivLength) {
            return ['valid' => false, 'message' => 'Invalid License Format'];
        }

        $iv = substr($payload, 0, $ivLength);
        $encryptedData = substr($payload, $ivLength);
        $key = hash('sha256', self::SECRET_KEY, true);

        $json = openssl_decrypt($encryptedData, self::ENCRYPTION_METHOD, $key, 0, $iv);
        $data = json_decode($json, true);

        if (!$data) {
            return ['valid' => false, 'message' => 'License Corrupted or Invalid Key'];
        }

        // 2. Check HWID
        $currentHWID = self::getHWID();
        if ($data['hwid'] !== $currentHWID) {
            return ['valid' => false, 'message' => 'License Locked to Different Hardware'];
        }

        // 3. Check Clock Manipulation
        if (self::detectTimeTravel()) {
            return ['valid' => false, 'message' => 'System Clock Manipulation Detected. Please restore correct date/time.'];
        }

        // 4. Check Expiry
        $expiryTimestamp = strtotime($data['expiry']);
        $currentTimestamp = time();
        
        if ($currentTimestamp > $expiryTimestamp) {
            return ['valid' => false, 'message' => 'Subscription Expired on ' . $data['expiry']];
        }

        $daysLeft = ceil(($expiryTimestamp - $currentTimestamp) / 86400);

        return ['valid' => true, 'message' => 'Active', 'days_left' => $daysLeft, 'client' => $data['client']];
    }

    /**
     * Detects if the system time has been moved backwards.
     */
    private static function detectTimeTravel() {
        $currentTime = time();
        $lastKnownTime = 0;

        // Create data directory if it doesn't exist
        $dir = dirname(self::STATE_FILE);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if (file_exists(self::STATE_FILE)) {
            $content = file_get_contents(self::STATE_FILE);
            // Simple obfuscation/protection of the state file could be added here
            $savedData = json_decode($content, true);
            if ($savedData && isset($savedData['last_check'])) {
                $lastKnownTime = $savedData['last_check'];
            }
        }

        // Allow a small buffer (e.g., 5 minutes) for legitimate NTP adjustments or small drift
        // But generally, time should strictly increase.
        // A buffer of -300 seconds allows for minor reboots/drifts.
        if ($currentTime < ($lastKnownTime - 300)) {
            return true; // Time traveled backwards significantly
        }

        // Update the state file with the new latest time
        // Only update if current time is actually greater (to avoid locking in a bad past date if we just barely passed check)
        if ($currentTime > $lastKnownTime) {
            file_put_contents(self::STATE_FILE, json_encode(['last_check' => $currentTime]));
        }

        return false;
    }

    private static function runCommand($cmd) {
        // Suppress output to keep things clean
        $output = [];
        exec($cmd, $output);
        // Usually the first line is header, second is value. 
        // We join all output and strip whitespace to get a clean raw string.
        return preg_replace('/[^a-zA-Z0-9]/', '', implode('', $output));
    }
}
?>