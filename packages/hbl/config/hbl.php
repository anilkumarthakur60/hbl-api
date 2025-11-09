<?php

return [
    'currency_code' => env('HBL_CURRENCY_CODE', 'USD'),
    'payment_type' => env('HBL_PAYMENT_TYPE', 'CC'),
    'payment_category' => env('HBL_PAYMENT_CATEGORY', 'ECOM'),
    'language' => env('HBL_LANGUAGE', 'en-US'),
    'store_card_flag' => env('HBL_STORE_CARD_FLAG', 'N'),
    'ipp_flag' => env('HBL_IPP_FLAG', 'N'),
    'installment_period' => env('HBL_INSTALLMENT_PERIOD', 0),
    'mcp_flag' => env('HBL_MCP_FLAG', 'N'),
    'request_3ds_flag' => env('HBL_REQUEST_3DS_FLAG', 'N'),
    'aud' => env('HBL_AUD', 'PacoAudience'),
    'redirect_urls' => [
        'confirmation' => config('app.url').'/success',
        'failed' => config('app.url').'/failed',
        'cancel' => config('app.url').'/cancel',
        'backend' => config('app.url').'/backend',
    ],

    'device_details' => [
        'browser_ip' => env('HBL_BROWSER_IP', '1.0.0.1'),
        'browser' => env('HBL_BROWSER', 'Postman Browser'),
        'browser_user_agent' => env('HBL_BROWSER_USER_AGENT', 'PostmanRuntime/7.26.8 - not from header'),
        'mobile_device_flag' => env('HBL_MOBILE_DEVICE_FLAG', 'N'),
    ],

    'end_point' => env('HBL_END_POINT', 'https://core.demo-paco.2c2p.com'),
    'access_token' => env('HBL_ACCESS_TOKEN', '65805a1636c74b8e8ac81a991da80be4'),
    'merchant_id' => env('HBL_MERCHANT_ID', 9104137120),

    'merchant_signing_private_key' => env('HBL_MERCHANT_SIGNING_PRIVATE_KEY', ''),
    'merchant_decryption_private_key' => env('HBL_MERCHANT_DECRYPTION_PRIVATE_KEY', ''),
    'paco_encryption_public_key' => env('HBL_PACO_ENCRYPTION_PUBLIC_KEY', ''),
    'paco_signing_public_key' => env('HBL_PACO_SIGNING_PUBLIC_KEY', ''),
    'decimal_places' => env('HBL_DECIMAL_PLACES', 2),
];
