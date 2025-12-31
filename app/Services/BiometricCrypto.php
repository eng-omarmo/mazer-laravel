<?php

namespace App\Services;

class BiometricCrypto
{
    public function encrypt(string $template): array
    {
        $key = (string) config('app.biometric_key', env('BIOMETRIC_KEY'));
        if ($key === '' || strlen(base64_decode($key, true) ?: '') !== 32) {
            throw new \RuntimeException('Invalid BIOMETRIC_KEY; expected base64-encoded 32-byte key');
        }
        $rawKey = base64_decode($key);
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt($template, 'aes-256-gcm', $rawKey, OPENSSL_RAW_DATA, $iv, $tag);
        if ($ciphertext === false) {
            throw new \RuntimeException('Encryption failed');
        }
        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }
}

