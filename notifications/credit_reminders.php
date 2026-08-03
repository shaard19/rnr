<?php

/**
 * R&R Collection POS
 * Credit Reminder Notification Engine
 *
 * Purpose:
 * - Find customers with outstanding credit
 * - Check SMS permission and phone number
 * - Create pending credit reminder notifications
 *
 * IMPORTANT:
 * This file DOES NOT send SMS.
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/

$notificationType = 'Credit Reminder';

/*
|--------------------------------------------------------------------------
| Counters
|--------------------------------------------------------------------------
*/

$created = 0;
$skipped = 0;
$errors = 0;

/*
|--------------------------------------------------------------------------
| Load Customers With Outstanding Credit
|--------------------------------------------------------------------------
|
| We calculate the actual outstanding amount from sales.balance.
| This keeps the notification engine consistent with the customer profile.
|
*/

$sql = "
    SELECT
        c.id,
        c.customer_name,
        c.phone,
        c.sms_enabled,
        COALESCE(SUM(s.balance), 0) AS outstanding_balance
    FROM customers c
    INNER JOIN sales s
        ON s.customer_id = c.id
    WHERE c.status = 'Active'
      AND c.sms_enabled = 1
      AND c.phone IS NOT NULL
      AND TRIM(c.phone) <> ''
      AND s.balance > 0
    GROUP BY
        c.id,
        c.customer_name,
        c.phone,
        c.sms_enabled
    HAVING outstanding_balance > 0
    ORDER BY c.customer_name ASC
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die(
        "Unable to load customers: "
        . mysqli_error($conn)
    );
}

/*
|--------------------------------------------------------------------------
| Process Customers
|--------------------------------------------------------------------------
*/

while ($customer = mysqli_fetch_assoc($result)) {

    $customerId = (int) $customer['id'];

    $customerName = trim(
        $customer['customer_name'] ?? ''
    );

    $phone = trim(
        $customer['phone'] ?? ''
    );

    $balance = (float) (
        $customer['outstanding_balance'] ?? 0
    );

    /*
    |--------------------------------------------------------------------------
    | Safety Checks
    |--------------------------------------------------------------------------
    */

    if (
        $customerId <= 0 ||
        $customerName === '' ||
        $phone === '' ||
        $balance <= 0
    ) {

        $skipped++;

        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Pending Credit Reminders
    |--------------------------------------------------------------------------
    |
    | We don't want the engine creating the same pending reminder every
    | time the script runs.
    |
    */

    $duplicateSql = "
        SELECT id
        FROM notifications
        WHERE customer_id = ?
          AND notification_type = ?
          AND status = 'Pending'
        LIMIT 1
    ";

    $duplicateStmt = mysqli_prepare(
        $conn,
        $duplicateSql
    );

    if (!$duplicateStmt) {

        $errors++;

        continue;
    }

    mysqli_stmt_bind_param(
        $duplicateStmt,
        "is",
        $customerId,
        $notificationType
    );

    if (!mysqli_stmt_execute($duplicateStmt)) {

        mysqli_stmt_close($duplicateStmt);

        $errors++;

        continue;
    }

    $duplicateResult = mysqli_stmt_get_result(
        $duplicateStmt
    );

    $alreadyPending = (
        $duplicateResult &&
        mysqli_num_rows($duplicateResult) > 0
    );

    mysqli_stmt_close($duplicateStmt);

    if ($alreadyPending) {

        $skipped++;

        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Build Message
    |--------------------------------------------------------------------------
    */

    $message = sprintf(
        "Hello %s, this is a reminder from R&R Collection. "
        . "Your outstanding credit balance is KSh %s. "
        . "Kindly make payment at your earliest convenience. "
        . "Thank you.",
        $customerName,
        number_format($balance, 2)
    );

    /*
    |--------------------------------------------------------------------------
    | Create Pending Notification
    |--------------------------------------------------------------------------
    */

    $insertSql = "
        INSERT INTO notifications
        (
            customer_id,
            notification_type,
            message,
            phone,
            status
        )
        VALUES
        (?, ?, ?, ?, 'Pending')
    ";

    $insertStmt = mysqli_prepare(
        $conn,
        $insertSql
    );

    if (!$insertStmt) {

        $errors++;

        continue;
    }

    mysqli_stmt_bind_param(
        $insertStmt,
        "isss",
        $customerId,
        $notificationType,
        $message,
        $phone
    );

    if (mysqli_stmt_execute($insertStmt)) {

        $created++;

    } else {

        $errors++;
    }

    mysqli_stmt_close($insertStmt);
}

mysqli_free_result($result);

/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    Credit Reminder Engine | R&R Collection
</title>

<style>

body {
    margin: 0;
    padding: 40px;
    background: #f5f7fb;
    font-family: Arial, sans-serif;
    color: #172033;
}

.container {
    max-width: 800px;
    margin: 0 auto;
}

.card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 25px rgba(15, 23, 42, .06);
}

h1 {
    margin: 0 0 8px;
}

.subtitle {
    color: #667085;
    margin-bottom: 28px;
}

.status {
    padding: 16px;
    border-radius: 10px;
    background: #ecfdf3;
    color: #027a48;
    margin-bottom: 20px;
}

.stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
}

.stat {
    padding: 18px;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
}

.stat strong {
    display: block;
    font-size: 26px;
    margin-bottom: 5px;
}

.stat span {
    color: #667085;
    font-size: 13px;
}

.warning {
    margin-top: 24px;
    padding: 14px;
    border-radius: 9px;
    background: #fffaeb;
    color: #b54708;
    font-size: 13px;
}

@media (max-width: 600px) {

    body {
        padding: 20px;
    }

    .stats {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="container">

    <div class="card">

        <h1>
            Credit Reminder Engine
        </h1>

        <div class="subtitle">
            R&amp;R Collection Notification System
        </div>

        <div class="status">

            <strong>
                Notification generation completed.
            </strong>

            <br>

            No SMS was sent.

        </div>

        <div class="stats">

            <div class="stat">

                <strong>
                    <?= $created; ?>
                </strong>

                <span>
                    Notifications Created
                </span>

            </div>

            <div class="stat">

                <strong>
                    <?= $skipped; ?>
                </strong>

                <span>
                    Skipped
                </span>

            </div>

            <div class="stat">

                <strong>
                    <?= $errors; ?>
                </strong>

                <span>
                    Errors
                </span>

            </div>

        </div>

        <div class="warning">

            <strong>Development mode:</strong>

            Notifications are currently stored as
            <strong>Pending</strong> only.

            No external SMS provider has been connected.

        </div>

    </div>

</div>

</body>

</html>