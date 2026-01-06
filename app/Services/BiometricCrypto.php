<?php

namespace App\Services;

use RuntimeException;

class BiometricCrypto
{
    protected string $key;

    public function __construct()
    {
        $key = (string) config('app.biometric_key', env('BIOMETRIC_KEY', ''));
        if ($key === '' || strlen(base64_decode($key, true) ?: '') !== 32) {
            throw new RuntimeException('Invalid BIOMETRIC_KEY; expected base64-encoded 32-byte key');
        }
        $this->key = base64_decode($key);
    }

    /**
     * Encrypt a biometric template using AES-256-GCM.
     *
     * @param string $template
     * @return array{ciphertext: string, iv: string, tag: string}
     */
    public function encrypt(string $template): array
    {
        $iv = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $template,
            'aes-256-gcm',
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($ciphertext === false) {
            throw new RuntimeException('Encryption failed');
        }

        return [
            'ciphertext' => base64_encode($ciphertext),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
        ];
    }

    /**
     * Decrypt a previously encrypted biometric template.
     *
     * @param array{ciphertext: string, iv: string, tag: string} $data
     * @return string
     */
    public function decrypt(array $data): string
    {
        $ciphertext = base64_decode($data['ciphertext'] ?? '');
        $iv = base64_decode($data['iv'] ?? '');
        $tag = base64_decode($data['tag'] ?? '');

        if ($ciphertext === false || $iv === false || $tag === false) {
            throw new RuntimeException('Invalid data for decryption');
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $this->key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($plaintext === false) {
            throw new RuntimeException('Decryption failed');
        }

        return $plaintext;
    }
}
