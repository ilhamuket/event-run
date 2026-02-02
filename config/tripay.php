<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tripay Mode
    |--------------------------------------------------------------------------
    |
    | Set to 'sandbox' for testing or 'production' for live transactions.
    |
    */
    'mode' => env('TRIPAY_MODE', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | Merchant Code
    |--------------------------------------------------------------------------
    |
    | Your Tripay merchant code.
    |
    */
    'merchant_code' => env('TRIPAY_MERCHANT_CODE', ''),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Tripay API key.
    |
    */
    'api_key' => env('TRIPAY_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Private Key
    |--------------------------------------------------------------------------
    |
    | Your Tripay private key for generating signatures.
    |
    */
    'private_key' => env('TRIPAY_PRIVATE_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | Default Payment Method
    |--------------------------------------------------------------------------
    |
    | Default payment method to use (QRIS).
    |
    */
    'default_payment_method' => env('TRIPAY_DEFAULT_METHOD', 'QRIS'),

    /*
    |--------------------------------------------------------------------------
    | Transaction Expiry
    |--------------------------------------------------------------------------
    |
    | Transaction expiry time in hours.
    |
    */
    'expiry_hours' => env('TRIPAY_EXPIRY_HOURS', 24),

    /*
    |--------------------------------------------------------------------------
    | Callback URL
    |--------------------------------------------------------------------------
    |
    | URL for Tripay to send payment notifications.
    |
    */
    'callback_url' => env('TRIPAY_CALLBACK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Return URL
    |--------------------------------------------------------------------------
    |
    | URL to redirect user after payment.
    |
    */
    'return_url' => env('TRIPAY_RETURN_URL'),

];
