<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "../config/mpesa_config.php";


/*
|--------------------------------------------------------------------------
| R&R COLLECTION - M-PESA STK PUSH TEST
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| SANDBOX TEST DETAILS
|--------------------------------------------------------------------------
*/

$phone = "254708374149";
$amount = 1;


/*
|--------------------------------------------------------------------------
| NORMALIZE PHONE NUMBER
|--------------------------------------------------------------------------
*/

$phone = trim($phone);

if (preg_match('/^07\d{8}$/', $phone)) {

    $phone = "254" . substr($phone, 1);

} elseif (preg_match('/^01\d{8}$/', $phone)) {

    $phone = "254" . substr($phone, 1);

} elseif (preg_match('/^\+254\d{9}$/', $phone)) {

    $phone = substr($phone, 1);

}


/*
|--------------------------------------------------------------------------
| VALIDATE PHONE
|--------------------------------------------------------------------------
*/

if (!preg_match('/^254\d{9}$/', $phone)) {

    die("Invalid Kenyan phone number.");

}


/*
|--------------------------------------------------------------------------
| VALIDATE AMOUNT
|--------------------------------------------------------------------------
*/

$amount = (int) $amount;

if ($amount < 1) {

    die("Invalid payment amount.");

}


/*
|--------------------------------------------------------------------------
| STEP 1 - GET OAUTH ACCESS TOKEN
|--------------------------------------------------------------------------
*/

$credentials = base64_encode(
    MPESA_CONSUMER_KEY . ":" . MPESA_CONSUMER_SECRET
);


$ch = curl_init(MPESA_AUTH_URL);

curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_TIMEOUT => 30,

    CURLOPT_HTTPHEADER => [

        "Authorization: Basic " . $credentials,

        "Content-Type: application/json"

    ]

]);


$authResponse = curl_exec($ch);


if ($authResponse === false) {

    $curlError = curl_error($ch);

    curl_close($ch);

    die(
        "OAuth cURL Error: " .
        htmlspecialchars($curlError)
    );

}


$authHttpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


curl_close($ch);


$authData = json_decode(
    $authResponse,
    true
);


if (
    $authHttpCode !== 200 ||
    empty($authData["access_token"])
) {

    die(

        "OAuth failed.<br><br>" .

        "HTTP Status: " .
        htmlspecialchars(
            (string) $authHttpCode
        ) .

        "<br><br>" .

        "<pre>" .
        htmlspecialchars($authResponse) .
        "</pre>"

    );

}


$accessToken =
    $authData["access_token"];


/*
|--------------------------------------------------------------------------
| STEP 2 - GENERATE TIMESTAMP
|--------------------------------------------------------------------------
*/

$timestamp = date("YmdHis");


/*
|--------------------------------------------------------------------------
| STEP 3 - GENERATE STK PASSWORD
|--------------------------------------------------------------------------
*/

$password = base64_encode(

    MPESA_SHORTCODE .
    MPESA_PASSKEY .
    $timestamp

);


/*
|--------------------------------------------------------------------------
| STEP 4 - CALLBACK URL
|--------------------------------------------------------------------------
*/

$callbackUrl =
    "https://suing-employee-vagrantly.ngrok-free.dev" .
    "/rnr_collection/mpesa/mpesa_callback.php";


/*
|--------------------------------------------------------------------------
| STEP 5 - BUILD STK REQUEST
|--------------------------------------------------------------------------
*/

$payload = [

    "BusinessShortCode" =>
        MPESA_SHORTCODE,

    "Password" =>
        $password,

    "Timestamp" =>
        $timestamp,

    "TransactionType" =>
        MPESA_TRANSACTION_TYPE,

    "Amount" =>
        $amount,

    "PartyA" =>
        $phone,

    "PartyB" =>
        MPESA_SHORTCODE,

    "PhoneNumber" =>
        $phone,

    "CallBackURL" =>
        $callbackUrl,

    "AccountReference" =>
        MPESA_ACCOUNT_REFERENCE,

    "TransactionDesc" =>
        MPESA_TRANSACTION_DESCRIPTION

];


/*
|--------------------------------------------------------------------------
| STEP 6 - SEND STK PUSH
|--------------------------------------------------------------------------
*/

$jsonPayload = json_encode(
    $payload
);


if ($jsonPayload === false) {

    die("Unable to encode STK request.");

}


$ch = curl_init(
    MPESA_STK_URL
);


curl_setopt_array($ch, [

    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS =>
        $jsonPayload,

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_TIMEOUT => 30,

    CURLOPT_HTTPHEADER => [

        "Authorization: Bearer " .
        $accessToken,

        "Content-Type: application/json"

    ]

]);


$response = curl_exec($ch);


if ($response === false) {

    $curlError = curl_error($ch);

    curl_close($ch);

    die(

        "STK cURL Error: " .
        htmlspecialchars($curlError)

    );

}


$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


curl_close($ch);


/*
|--------------------------------------------------------------------------
| DECODE SAFARICOM RESPONSE
|--------------------------------------------------------------------------
*/

$responseData = json_decode(
    $response,
    true
);


/*
|--------------------------------------------------------------------------
| DISPLAY RESPONSE
|--------------------------------------------------------------------------
*/

header(
    "Content-Type: text/html; charset=UTF-8"
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>R&R M-Pesa STK Test</title>

<style>

body {

    font-family: Arial, sans-serif;

    background: #f5f5f5;

    padding: 30px;

}

.container {

    max-width: 700px;

    margin: auto;

    background: white;

    padding: 25px;

    border-radius: 10px;

    box-shadow: 0 3px 12px rgba(0,0,0,.12);

}

.success {

    color: #198754;

}

.error {

    color: #dc3545;

}

pre {

    background: #f1f1f1;

    padding: 15px;

    border-radius: 6px;

    overflow-x: auto;

}

</style>

</head>

<body>

<div class="container">

<h2>R&R Collection - M-Pesa STK Test</h2>

<p>

<strong>HTTP Status:</strong>

<?= htmlspecialchars(
    (string) $httpCode
) ?>

</p>


<?php

/*
|--------------------------------------------------------------------------
| SUCCESSFUL STK REQUEST
|--------------------------------------------------------------------------
*/

if (
    isset($responseData["ResponseCode"]) &&
    (string)$responseData["ResponseCode"] === "0"
):

?>

<h3 class="success">
    STK Push Accepted ✅
</h3>

<p>

<?= htmlspecialchars(
    $responseData["CustomerMessage"]
    ?? "Request accepted."
) ?>

</p>

<p>

<strong>Merchant Request ID:</strong><br>

<?= htmlspecialchars(
    $responseData["MerchantRequestID"]
    ?? ""
) ?>

</p>

<p>

<strong>Checkout Request ID:</strong><br>

<?= htmlspecialchars(
    $responseData["CheckoutRequestID"]
    ?? ""
) ?>

</p>


<?php

/*
|--------------------------------------------------------------------------
| FAILED STK REQUEST
|--------------------------------------------------------------------------
*/

else:

?>

<h3 class="error">
    STK Push Failed ❌
</h3>

<pre><?= htmlspecialchars(
    $response
) ?></pre>

<?php endif; ?>


</div>

</body>

</html>