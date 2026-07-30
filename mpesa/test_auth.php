<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/mpesa_config.php";

$credentials = base64_encode(
    MPESA_CONSUMER_KEY . ":" . MPESA_CONSUMER_SECRET
);

$ch = curl_init(MPESA_AUTH_URL);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Basic " . $credentials,
    "Content-Type: application/json"
]);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);

if ($response === false) {

    die(
        "cURL Error: " .
        curl_error($ch)
    );

}

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);

curl_close($ch);

echo "<h2>M-Pesa OAuth Test</h2>";

echo "<p><strong>HTTP Status:</strong> "
    . htmlspecialchars((string)$httpCode)
    . "</p>";

echo "<pre>";
echo htmlspecialchars($response);
echo "</pre>";