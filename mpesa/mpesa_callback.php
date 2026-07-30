<?php

/*
|--------------------------------------------------------------------------
| R&R COLLECTION - M-PESA CALLBACK
|--------------------------------------------------------------------------
*/

error_reporting(E_ALL);
ini_set('display_errors', 1);


/*
|--------------------------------------------------------------------------
| RECEIVE SAFARICOM CALLBACK
|--------------------------------------------------------------------------
*/

$rawInput = file_get_contents("php://input");


/*
|--------------------------------------------------------------------------
| LOG CALLBACK
|--------------------------------------------------------------------------
|
| Very useful during development.
|
*/

$logFile = __DIR__ . "/mpesa_callback_log.txt";

file_put_contents(
    $logFile,
    date("Y-m-d H:i:s") .
    " CALLBACK RECEIVED:\n" .
    $rawInput .
    "\n\n",
    FILE_APPEND
);


/*
|--------------------------------------------------------------------------
| DECODE JSON
|--------------------------------------------------------------------------
*/

$data = json_decode(
    $rawInput,
    true
);


/*
|--------------------------------------------------------------------------
| BASIC VALIDATION
|--------------------------------------------------------------------------
*/

if (!$data) {

    http_response_code(400);

    echo json_encode([
        "ResultCode" => 1,
        "ResultDesc" => "Invalid callback data"
    ]);

    exit;

}


/*
|--------------------------------------------------------------------------
| EXTRACT CALLBACK DATA
|--------------------------------------------------------------------------
*/

$stkCallback =
    $data["Body"]["stkCallback"]
    ?? null;


if (!$stkCallback) {

    http_response_code(400);

    echo json_encode([
        "ResultCode" => 1,
        "ResultDesc" => "Invalid STK callback structure"
    ]);

    exit;

}


$merchantRequestID =
    $stkCallback["MerchantRequestID"]
    ?? null;


$checkoutRequestID =
    $stkCallback["CheckoutRequestID"]
    ?? null;


$resultCode =
    $stkCallback["ResultCode"]
    ?? null;


$resultDescription =
    $stkCallback["ResultDesc"]
    ?? "";


/*
|--------------------------------------------------------------------------
| PAYMENT RESULT
|--------------------------------------------------------------------------
*/

if ((int)$resultCode === 0) {

    /*
    |--------------------------------------------------------------------------
    | PAYMENT SUCCESSFUL
    |--------------------------------------------------------------------------
    |
    | DO NOT CREATE THE R&R SALE YET.
    |
    | We are first proving that Safaricom can
    | successfully reach our callback.
    |
    */

    $callbackStatus = "SUCCESS";

} else {

    /*
    |--------------------------------------------------------------------------
    | PAYMENT FAILED / CANCELLED
    |--------------------------------------------------------------------------
    */

    $callbackStatus = "FAILED";

}


/*
|--------------------------------------------------------------------------
| LOG PROCESSED RESULT
|--------------------------------------------------------------------------
*/

file_put_contents(
    $logFile,
    "Status: " .
    $callbackStatus .
    "\n" .
    "MerchantRequestID: " .
    $merchantRequestID .
    "\n" .
    "CheckoutRequestID: " .
    $checkoutRequestID .
    "\n" .
    "ResultCode: " .
    $resultCode .
    "\n" .
    "ResultDescription: " .
    $resultDescription .
    "\n\n",
    FILE_APPEND
);


/*
|--------------------------------------------------------------------------
| RESPOND TO SAFARICOM
|--------------------------------------------------------------------------
*/

http_response_code(200);

header("Content-Type: application/json");

echo json_encode([
    "ResultCode" => 0,
    "ResultDesc" => "Callback received successfully"
]);

exit;