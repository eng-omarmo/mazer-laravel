<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class MerchantPayService
{
    protected string $baseUrl;
    protected string $clientId;
    protected string $clientSecret;
    protected string $currency;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('merchantpay.base_url'), '/');
        $this->clientId = (string) config('merchantpay.client_id');
        $this->clientSecret = (string) config('merchantpay.client_secret');
        $this->currency = (string) config('merchantpay.currency', 'USD');
    }

    /**
     * Execute bulk payment transaction
     *
     * @param array $payments Each item: ['receiver'=>string,'amount'=>float,'currency'=>string|int,'payment_method'=>int,'reference'=>string]
     * @return array Decoded JSON response
     * @throws \RuntimeException on failure response
     */
    public function executeTransaction(array $payment): array
    {
        $currency = is_numeric($this->currency) ? (int) $this->currency : $this->currency;
        $payload = [
            'client_id' => $this->clientId,
            'currency' => $currency,
            'receiver' => $payment['receiver'],
            'amount' => $payment['amount'],
            'currency' => $payment['currency'],
            'payment_method' => $payment['payment_method'],
            'reference' => $payment['reference'],
        ];

        $response = Http::timeout(15)->retry(2, 500)->asJson()->post($this->baseUrl . '/api/v2/bulk-pay', $payload);

        if (! $response->successful()) {
            logger()->error('MerchantPay bulk-pay error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('MerchantPay bulk-pay failed: status=' . $response->status() . ' body=' . $response->body());
        }

        return (array) $response->json();
    }
}
