<?php
/**
 * Med-Quick - Secure Pharmacy POS
 * Security Helper Class
 *
 * Author: Gemini CLI
 * Version: 1.0.0
 *
 * Provides static methods for AES-256-GCM encryption/decryption
 * and secure token generation.
 */

class SecurityHelper
{
    /**
     * The encryption cipher to use. AES-256-GCM is recommended for its
     * performance and security, as it provides both confidentiality and authenticity.
     */
    private const CIPHER = 'aes-256-gcm';

    /**
     * Encrypts a plaintext string using AES-256-GCM.
     *
     * IMPORTANT: The encryption key MUST be a securely generated, 32-byte key.
     * It should be stored outside of the web root in an environment variable
     * or a secure configuration file.
     *
     * Example of how to define the key in a config file:
     * define('ENCRYPTION_KEY', openssl_random_pseudo_bytes(32));
     *
     * @param string $plaintext The data to encrypt.
     * @return string|false A base64-encoded string containing the IV, tag, and ciphertext, or false on failure.
     */
    public static function encrypt(string $plaintext): string|false
    {
        // In a real application, get this from a secure environment variable.
        // getenv('APP_ENCRYPTION_KEY')
        $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : self::getFallbackKey();

        if (mb_strlen($key, '8bit') !== 32) {
            // This is a critical configuration error.
            error_log('SecurityHelper Error: Encryption key must be 32 bytes long.');
            return false;
        }

        $iv_length = openssl_cipher_iv_length(self::CIPHER);
        $iv = openssl_random_pseudo_bytes($iv_length);

        $ciphertext = openssl_encrypt(
            $plaintext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag // The authentication tag is generated and passed by reference.
        );

        if ($ciphertext === false) {
            return false;
        }

        // Combine IV, tag, and ciphertext, then base64-encode for safe storage.
        // Format: iv.tag.ciphertext
        return base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypts a base64-encoded string that was encrypted with the encrypt() method.
     *
     * @param string $encrypted_string The base64-encoded data.
     * @return string|false The original plaintext data, or false on failure (e.g., if the data is tampered with).
     */
    public static function decrypt(string $encrypted_string): string|false
    {
        $key = defined('ENCRYPTION_KEY') ? ENCRYPTION_KEY : self::getFallbackKey();
        
        if (mb_strlen($key, '8bit') !== 32) {
             error_log('SecurityHelper Error: Decryption key must be 32 bytes long.');
            return false;
        }
        
        $decoded_data = base64_decode($encrypted_string);
        if ($decoded_data === false) {
            return false;
        }

        $iv_length = openssl_cipher_iv_length(self::CIPHER);
        $tag_length = 16; // GCM tag length is typically 16 bytes.

        // Ensure decoded_data has enough length for IV, tag, and some ciphertext.
        if (mb_strlen($decoded_data, '8bit') < $iv_length + $tag_length + 1) { // +1 for at least one byte of ciphertext
            error_log('SecurityHelper Error: Decoded data too short for IV and Tag.');
            return false;
        }

        $iv = substr($decoded_data, 0, $iv_length);
        $tag = substr($decoded_data, $iv_length, $tag_length);
        $ciphertext = substr($decoded_data, $iv_length + $tag_length);

        // Additional check for actual IV, tag and ciphertext lengths
        if (mb_strlen($iv, '8bit') !== $iv_length || mb_strlen($tag, '8bit') !== $tag_length || empty($ciphertext)) {
             error_log('SecurityHelper Error: Invalid IV, Tag or Ciphertext length after substr.');
            return false;
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::CIPHER,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        return $plaintext;
    }

    /**
     * Generates a unique, URL-safe token for preventing duplicate form submissions (idempotency).
     *
     * @param int $length The desired length of the token string.
     * @return string The generated token.
     */
    public static function generateTransactionToken(int $length = 40): string
    {
        // `random_bytes` is cryptographically secure.
        // We convert it to a URL-safe hex representation.
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Provides a fallback key for demonstration purposes.
     * DO NOT USE THIS IN PRODUCTION.
     * @return string
     */
    private static function getFallbackKey(): string
    {
        static $fallback_key = null;

        if ($fallback_key === null) {
            // Generate a secure 32-byte key once for the duration of the script execution.
            // IMPORTANT: In a production environment, this key should be loaded from a secure, persistent source.
            // This fallback is ONLY for demonstration/development where ENCRYPTION_KEY is not explicitly set.
            $fallback_key = openssl_random_pseudo_bytes(32);
        }
        return $fallback_key;
    }
}
