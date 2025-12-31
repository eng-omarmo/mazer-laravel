<?php

namespace App\Services;

interface FingerprintDeviceService
{
    public function initialize(): void;
    public function handshake(): bool;
    public function captureTemplate(int $attempts = 3): array;
    public function setTransport(string $transport): void;
    public function getTransport(): string;
}
