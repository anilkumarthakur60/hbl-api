<?php

return [

    /*
    |--------------------------------------------------------------------------
    | HBL Payment Gateway Configuration
    |--------------------------------------------------------------------------
    |
    | This file holds all necessary settings to integrate with the HBL payment
    | gateway using PACO encryption and signature standards.
    |
    */

    /**
     * Merchant's unique Office ID provided by HBL.
     */
    'OfficeId' => (string) env('HBL_OFFICE_ID'),

    /**
     * Payment request endpoint URL.
     */
    'EndPoint' => (string) env('HBL_END_POINT', 'https://core.demo-paco.2c2p.com'),

    /**
     * OAuth 2.0 access token used for authentication.
     */
    'AccessToken' => (string) env('HBL_ACCESS_TOKEN'),

    /**
     * Token type used in Authorization headers (usually "JWT").
     */
    'TokenType' => (string) env('HBL_TOKEN_TYPE', 'JWT'),

    /*
    |--------------------------------------------------------------------------
    | Encryption and Signature Configuration
    |--------------------------------------------------------------------------
    */

    /**
     * Unique identifier for the encryption key (provided by HBL).
     */
    'EncryptionKeyId' => (string) env('HBL_ENCRYPTION_KEY_ID', '7664a2ed0dee4879bdfca0e8ce1ac313'),

    /**
     * JWS (JSON Web Signature) signing algorithm.
     * Recommended: PS256 (RSASSA-PSS using SHA-256).
     */
    'JWSAlgorithm' => (string) env('HBL_JWS_ALGORITHM', 'PS256'),

    /**
     * JWE (JSON Web Encryption) key encryption algorithm.
     * Recommended: RSA-OAEP.
     */
    'JWEAlgorithm' => (string) env('HBL_JWE_ALGORITHM', 'RSA-OAEP'),

    /**
     * JWE content encryption algorithm.
     * Recommended: A128CBC-HS256.
     */
    'JWEEncryptionAlgorithm' => (string) env('HBL_JWE_ENCRYPTION_ALGORITHM', 'A128CBC-HS256'),

    /**
     * Merchant's private key used to sign JWS (Base64-encoded PEM).
     */
    'MerchantSigningPrivateKey' => (string) env('HBL_MERCHANT_SIGNING_PRIVATE_KEY'),

    /**
     * Merchant's private key used to decrypt JWE responses (Base64-encoded PEM).
     */
    'MerchantDecryptionPrivateKey' => (string) env('HBL_MERCHANT_DECRYPTION_PRIVATE_KEY'),

    /**
     * PACO's public key used to encrypt requests (JWE).
     */
    'PacoEncryptionPublicKey' => (string) env('HBL_PACO_ENCRYPTION_PUBLIC_KEY'),

    /**
     * PACO's public key used to verify JWS signatures.
     */
    'PacoSigningPublicKey' => (string) env('HBL_PACO_SIGNING_PUBLIC_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Settings
    |--------------------------------------------------------------------------
    */

    /**
     * Currency for the transaction (e.g., "NPR", "USD").
     */
    'InputCurrency' => (string) env('HBL_INPUT_CURRENCY', 'NPR'),

    /**
     * 3DS (3D Secure) authentication setting.
     * Use "Y" for production, "N" for development/testing.
     */
    'Input3DS' => (string) env('HBL_INPUT_3DS', 'N'),

    'redirect_url' => [
        'success' => (string) env('HBL_JOSE_REDIRECT_URL_SUCCESS', config('app.url', 'http://hbl-api.test').'/success'),
        'failed' => (string) env('HBL_JOSE_REDIRECT_URL_FAILED', config('app.url', 'http://hbl-api.test').'/failed'),
        'cancel' => (string) env('HBL_JOSE_REDIRECT_URL_CANCEL', config('app.url', 'http://hbl-api.test').'/cancel'),
        'backend' => (string) env('HBL_JOSE_REDIRECT_URL_BACKEND', config('app.url', 'http://hbl-api.test').'/backend'),
    ],

    // language
    'language' => (string) env('HBL_LANGUAGE', 'en-US'),
    'payment_type' => (string) env('HBL_PAYMENT_TYPE', 'CC'),
    'payment_category' => (string) env('HBL_PAYMENT_CATEGORY', 'ECOM'),
    'store_card_flag' => (string) env('HBL_STORE_CARD_FLAG', 'N'),
    'mcp_flag' => (string) env('HBL_MCP_FLAG', 'N'),
    'decimal_places' => (int) env('HBL_DECIMAL_PLACES', 2),
    'device_details' => [
        'browserIp' => '1.0.0.1',
        'browser' => 'Postman Browser',
        'browserUserAgent' => 'PostmanRuntime/7.26.8 - not from header',
        'mobileDeviceFlag' => 'N',
    ],
];
