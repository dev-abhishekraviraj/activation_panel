<?php

namespace App\Helpers;

class CustomEncryptor
{
    /**
     * Encrypt any string (letters, numbers, symbols, etc.)
     */
    public static function encrypt(string $value, string $key): string
    {
        // Generate a 256-bit key from the password
        $sslKey = openssl_digest($key, 'SHA256', true);

        // Generate a secure random IV
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Encrypt the data
        $encrypted = openssl_encrypt($value, 'aes-256-cbc', $sslKey, 0, $iv);

        // Combine IV + encrypted data, then Base64 encode
        return base64_encode($iv . $encrypted);
    }

    /**
     * Decrypt any string (letters, numbers, symbols, etc.)
     */
    public static function decrypt(string $encryptedBase64, string $key): string
    {
        // Decode Base64
        $decoded = base64_decode($encryptedBase64);

        // Extract IV and encrypted data
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decoded, 0, $ivLength);
        $encryptedData = substr($decoded, $ivLength);

        // Re-create the key
        $sslKey = openssl_digest($key, 'SHA256', true);

        // Decrypt the data
        $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $sslKey, 0, $iv);

        return $decrypted;
    }
}
