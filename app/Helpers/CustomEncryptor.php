<?php 
namespace App\Helpers;

class CustomEncryptor
{
   
    public static function encryptInt(int $value, string $key): string
    {
        $data = $value;
        $encryptionKey = $key;

        // Generate a 256-bit key from the password
        $sslkey = openssl_digest($encryptionKey, 'SHA256', true);

        // Generate a secure random IV
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Encrypt
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $sslkey, 0, $iv);

        // Encode the result with IV for transmission (e.g., base64)
        $encryptedWithIv = base64_encode($iv . $encrypted);

        return $encryptedWithIv;

    }
     

    public static function decryptInt(string $encryptedBase64, string $key): int
    {
        $encryptedWithIv =  $encryptedBase64; // or from storage
        $encryptionKey = $key;

        // Decode
        $decoded = base64_decode($encryptedWithIv);

        // Extract IV and encrypted data
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decoded, 0, $ivLength);
        $encryptedData = substr($decoded, $ivLength);

        // Re-create the key
        $sslkey = openssl_digest($encryptionKey, 'SHA256', true);

        // Decrypt
        $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $sslkey, 0, $iv);

        return $decrypted;
    }

    public static function encryptString(String $value, string $key): string
    {
        $data = $value;
        $encryptionKey = $key;

        // Generate a 256-bit key from the password
        $sslkey = openssl_digest($encryptionKey, 'SHA256', true);

        // Generate a secure random IV
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivLength);

        // Encrypt
        $encrypted = openssl_encrypt($data, 'aes-256-cbc', $sslkey, 0, $iv);

        // Encode the result with IV for transmission (e.g., base64)
        $encryptedWithIv = base64_encode($iv . $encrypted);

        return $encryptedWithIv;

    }

    public static function decryptString(string $encryptedBase64, string $key): String
    {
        $encryptedWithIv =  $encryptedBase64; // or from storage
        $encryptionKey = $key;

        // Decode
        $decoded = base64_decode($encryptedWithIv);

        // Extract IV and encrypted data
        $ivLength = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($decoded, 0, $ivLength);
        $encryptedData = substr($decoded, $ivLength);

        // Re-create the key
        $sslkey = openssl_digest($encryptionKey, 'SHA256', true);

        // Decrypt
        $decrypted = openssl_decrypt($encryptedData, 'aes-256-cbc', $sslkey, 0, $iv);

        return $decrypted;
    }
}

