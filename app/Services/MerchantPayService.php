<?php

namespace App\Services;

use App\Models\ApiConfiguration;
use Illuminate\Support\Facades\Http;

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
        $this->currency = (string) config('merchantpay.currency', 'USD');
    }

    /**
     * Execute bulk payment transaction
     *
     * @param  array  $payments  Each item: ['receiver'=>string,'amount'=>float,'currency'=>string|int,'payment_method'=>int,'reference'=>string]
     * @return array Decoded JSON response
     *
     * @throws \RuntimeException on failure response
     */
    public function executeTransaction(array $payment): array
    {
        $configuration = ApiConfiguration::firstOrFail();


        $payload = [
            'client_id' => $configuration->token,
            'currency' => 1,
            'receiver' => $payment['receiver'],
            'amount' => $payment['amount'],
            'payment_method' => $payment['payment_method'],
            'reference' => $payment['reference'],
        ];

        $response = Http::timeout(15)->retry(2, 500)->asJson()->post($this->baseUrl.'/api/v2/bulk-pay', $payload);

        if (! $response->successful()) {
            logger()->error('MerchantPay bulk-pay error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('MerchantPay bulk-pay failed: status='.$response->status().' body='.$response->body());
        }

        return (array) $response->json();
    }

    public function WalletsInfo()
    {
        $configuration = ApiConfiguration::firstOrFail();
        $response = Http::timeout(15)->retry(2, 500)->asJson()->get($this->baseUrl.'/api/v2/wallets-info', [
            'client_id' => $configuration->token,
        ]);

        if (! $response->successful()) {
            logger()->error('MerchantPay wallets error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            throw new \RuntimeException('MerchantPay wallets failed: status='.$response->status().' body='.$response->body());
        }

        return (array) $response->json();
    }
}
