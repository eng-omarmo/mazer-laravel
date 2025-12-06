<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MerchantPayService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('merchantpay.base_url'), '/');
        $this->clientId = (string) config('merchantpay.client_id');
        $this->clientSecret = (string) config('merchantpay.client_secret');
    }

    public function getAccessToken(): string
    {
        $cacheKey = 'merchantpay_access_token';
        $cached = Cache::get($cacheKey);
        if ($cached) {
            return $cached;
        }


        $resp = Http::post($this->baseUrl . '/merchant/api/verify', [
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
        ]);

        if (! $resp->successful()) {
            throw new \RuntimeException('MerchantPay verify failed');
        }
        $token = (string) data_get($resp->json(), 'data.access_token');
        if (! $token) {
            throw new \RuntimeException('MerchantPay token missing');
        }
        Cache::put($cacheKey, $token, now()->addMinutes(30));
        return $token;
    }

    public function createTransactionInfo(string $phone, string $amount, string $currency, string $successUrl, string $cancelUrl, array $orderInfo): array
    {

        $token = $this->getAccessToken();
        $payload = [
            'phone' => $phone,
            'amount' => $amount,
            'currency' => $currency,
            'successUrl' => $successUrl,
            'cancelUrl' => $cancelUrl,
            'order_info' => $orderInfo,
        ];

        $resp = Http::asJson()->withToken($token)->post($this->baseUrl . '/merchant/api/transaction-info', $payload);
        if (! $resp->successful()) {
            throw new \RuntimeException('MerchantPay transaction-info failed');
        }
        return $resp->json();
    }

    public function verifyTransaction(string $uuid): array
    {
        $token = $this->getAccessToken();
        $resp = Http::asJson()->withToken($token)->post($this->baseUrl . '/merchant/api/transaction-verify', [
            'uuid' => $uuid,
        ]);
        if (! $resp->successful()) {
            throw new \RuntimeException('MerchantPay transaction-verify failed');
        }
        return $resp->json();
    }
}
