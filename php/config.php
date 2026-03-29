<?php
// php/config.php

// Define a persistent ENCRYPTION_KEY if it's not already defined.
// In a production environment, this key should be loaded from a secure environment
// variable or a dedicated, non-web-accessible configuration store.
// For this demonstration, we'll generate a consistent key.

if (!defined('ENCRYPTION_KEY')) {
    // This key must be 32 bytes (256 bits) long for aes-256-gcm.
    // Use a securely generated random string and keep it consistent.
    // Example: openssl_random_pseudo_bytes(32) run once and value stored.
    // For consistency in this demo, a fixed key:
    define('ENCRYPTION_KEY', 'a_very_secure_32_byte_key_for_demo_purposes!'); // Replace with a real generated key for actual use
}

// You might also define other configuration variables here.

