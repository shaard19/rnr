<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
$rawInput = file_get_contents("php://input");
$logFile = __DIR__ . "/mpesa_callback_log.txt";
file_put_contents(
    $logFile,
    date("Y-m-d H:i:s") .
    " CALLBACK RECEIVED:\n" .
    $rawInput .
    "\n\n",
    FILE_APPEND
);
$data = json_decode(
    $rawInput,
    true
);
if (!$data) {
    http_response_code(400);
    echo json_encode([
        "ResultCode" => 1,
        "ResultDesc" => "Invalid callback data"
    ]);
    exit;
}
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
if ((int)$resultCode === 0) {
       $callbackStatus = "SUCCESS";
} else {
       $callbackStatus = "FAILED";
}
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
http_response_code(200);
header("Content-Type: application/json");
echo json_encode([
    "ResultCode" => 0,
    "ResultDesc" => "Callback received successfully"
]);
exit;