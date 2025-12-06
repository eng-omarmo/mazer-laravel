<?php

return [
    'base_url' => env('MERCHANTPAY_BASE_URL', 'https://merchantpay.somxchange.com'),
    'client_id' => env('SOMX_CLIENT_ID', ''),
    'client_secret' => env('SOMX_CLIENT_SECRET', ''),
    'currency' => env('SOMX_CURRENCY', 'USD'),
];
