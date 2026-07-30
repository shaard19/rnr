<?php

require_once "../config/mpesa_config.php";

$url =
    "https://sandbox.safaricom.co.ke/oauth/v1/generate" .
    "?grant_type=client_credentials";

$credentials = base64_encode(
    MPESA_CONSUMER_KEY . ":" . MPESA_CONSUMER_SECRET
);

$ch = curl_init($url);

curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_TIMEOUT => 30,

    CURLOPT_HTTPHEADER => [

        "Authorization: Basic " . $credentials,

        "Content-Type: application/json"

    ]

]);

$response = curl_exec($ch);

if ($response === false) {

    echo "CURL ERROR: " .
         htmlspecialchars(curl_error($ch));

    curl_close($ch);

    exit;

}

$httpCode =
    curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

echo "<h2>R&R M-Pesa OAuth Test</h2>";

echo "<strong>HTTP Status:</strong> " .
     htmlspecialchars((string)$httpCode);

echo "<pre>";

echo htmlspecialchars($response);

echo "</pre>";