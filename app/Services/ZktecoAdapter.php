<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ZktecoAdapter implements FingerprintDeviceService
{
    protected string $endpoint;
    protected string $apiKey;
    protected string $transport = 'usb';

    public function __construct(?string $endpoint = null)
    {
        $this->endpoint = $endpoint ?: (string) config('fingerprint.agent_url', env('FINGERPRINT_AGENT_URL', 'http://127.0.0.1:8282'));
        $this->apiKey = (string) config('fingerprint.agent_key', env('FINGERPRINT_AGENT_KEY', ''));
    }

    public function initialize(): void
    {
        // No-op for HTTP agent, but could enumerate devices or preflight
    }

    public function setTransport(string $transport): void
    {
        $t = strtolower($transport);
        if (! in_array($t, ['usb', 'bluetooth', 'wifi'], true)) {
            throw new \InvalidArgumentException('Unsupported transport');
        }
        $this->transport = $t;
    }

    public function getTransport(): string
    {
        return $this->transport;
    }

    public function handshake(): bool
    {
        try {
            $req = Http::timeout(2)->retry(3, 300)->asJson();
            if ($this->apiKey) {
                $req->withHeaders(['X-AGENT-KEY' => $this->apiKey]);
            }
            $resp = $req->post($this->endpoint.'/handshake', [
                'client' => 'laravel',
                'ts' => now()->timestamp,
                'transport' => $this->transport,
            ]);
            return $resp->ok() && ($resp->json('ok') === true);
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function captureTemplate(int|string $userId, int $attempts = 3): array
    {
        $lastError = null;
        for ($i = 0; $i < max(1, $attempts); $i++) {
            try {
                $req = Http::timeout(2)->retry(2, 250)->asJson();
                if ($this->apiKey) {
                    $req->withHeaders(['X-AGENT-KEY' => $this->apiKey]);
                }
                $resp = $req->post($this->endpoint.'/capture', [
                    'userId' => (string) $userId,
                    'fingerIndex' => 0,
                    'min_dpi' => 500,
                    'quality_threshold' => 70,
                    'transport' => $this->transport,
                ]);
                if ($resp->ok()) {
                    $json = $resp->json();
                    if (($json['ok'] ?? false) && ($json['dpi'] ?? 0) >= 500) {
                        return [
                            'template' => (string) ($json['template'] ?? ''),
                            'dpi' => (int) ($json['dpi'] ?? 0),
                            'quality' => (int) ($json['quality'] ?? 0),
                            'device_sn' => (string) ($json['device_sn'] ?? ''),
                            'algorithm' => (string) ($json['algorithm'] ?? ''),
                        ];
                    }
                    $lastError = $json['error'] ?? 'Unacceptable capture';
                } else {
                    $lastError = 'Agent HTTP error';
                }
            } catch (\Throwable $e) {
                $lastError = $e->getMessage();
            }
        }
        throw new \RuntimeException('Capture failed: '.$lastError);
    }
}
