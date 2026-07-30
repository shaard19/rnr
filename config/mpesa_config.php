<?php

/*
|--------------------------------------------------------------------------
| R&R COLLECTION - M-PESA SANDBOX CONFIGURATION
|--------------------------------------------------------------------------
*/

define('MPESA_ENVIRONMENT', 'sandbox');

/*
|--------------------------------------------------------------------------
| DARaja API
|--------------------------------------------------------------------------
*/

define(
    'MPESA_BASE_URL',
    'https://sandbox.safaricom.co.ke'
);

define(
    'MPESA_AUTH_URL',
    MPESA_BASE_URL . '/oauth/v1/generate?grant_type=client_credentials'
);

define(
    'MPESA_STK_URL',
    MPESA_BASE_URL . '/mpesa/stkpush/v1/processrequest'
);


/*
|--------------------------------------------------------------------------
| R&R COLLECTION DARaja APP
|--------------------------------------------------------------------------
|
| Get these from:
| Daraja → My Apps → RR collection
|
| DO NOT share these values publicly.
|
*/

define(
    'MPESA_CONSUMER_KEY',
    'rnpqGvrG9feHl1ZmW9BYxYeaGYlgJBDBmD8D4JowXvZa38Ry'
);

define(
    'MPESA_CONSUMER_SECRET',
    'X3uCld3t92YdjujGao44BMbgzmU4ZhljYPZyPambELIFugv6GCpGaBDEVIXa0uej'
);


/*
|--------------------------------------------------------------------------
| SANDBOX STK PUSH
|--------------------------------------------------------------------------
|
| These are sandbox testing values.
|
*/

define(
    'MPESA_SHORTCODE',
    '174379'
);

define(
    'MPESA_PASSKEY',
    'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'
);


/*
|--------------------------------------------------------------------------
| STK PUSH SETTINGS
|--------------------------------------------------------------------------
*/

define(
    'MPESA_TRANSACTION_TYPE',
    'CustomerPayBillOnline'
);

define(
    'MPESA_ACCOUNT_REFERENCE',
    'RNR Collection'
);

define(
    'MPESA_TRANSACTION_DESCRIPTION',
    'RNR Collection Payment'
);