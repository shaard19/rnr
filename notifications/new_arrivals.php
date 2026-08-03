<?php

/**
 * R&R Collection POS
 * Premium Customer New-Arrival Notification Engine
 *
 * IMPORTANT:
 * This script creates Pending notifications only.
 * It DOES NOT send SMS yet.
 *
 * Usage:
 * new_arrivals.php?product_id=15
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Validate Product ID
|--------------------------------------------------------------------------
*/

$productId = isset($_GET['product_id'])
    ? (int) $_GET['product_id']
    : 0;

if ($productId <= 0) {
    die("Invalid product ID.");
}

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$productSql = "
    SELECT
        p.id,
        p.product_name,
        p.selling_price,
        p.unit,
        p.quantity,
        p.status,
        c.category_name
    FROM products p

    LEFT JOIN categories c
        ON c.id = p.category_id

    WHERE p.id = ?
    LIMIT 1
";

$productStmt = mysqli_prepare(
    $conn,
    $productSql
);

if (!$productStmt) {
    die(
        "Product query error: "
        . mysqli_error($conn)
    );
}

mysqli_stmt_bind_param(
    $productStmt,
    "i",
    $productId
);

if (!mysqli_stmt_execute($productStmt)) {

    mysqli_stmt_close($productStmt);

    die(
        "Unable to load product: "
        . mysqli_stmt_error($productStmt)
    );
}

$productResult = mysqli_stmt_get_result(
    $productStmt
);

$product = mysqli_fetch_assoc(
    $productResult
);

mysqli_stmt_close($productStmt);

if (!$product) {
    die("Product not found.");
}

/*
|--------------------------------------------------------------------------
| Only Active Products
|--------------------------------------------------------------------------
*/

if ($product['status'] !== 'Active') {
    die("This product is not active.");
}

/*
|--------------------------------------------------------------------------
| Product Details
|--------------------------------------------------------------------------
*/

$productName = trim(
    $product['product_name'] ?? ''
);

$productPrice = (float) (
    $product['selling_price'] ?? 0
);

$productUnit = trim(
    $product['unit'] ?? ''
);

$categoryName = trim(
    $product['category_name'] ?? ''
);

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
| Load Eligible Premium Customers
|--------------------------------------------------------------------------
|
| Requirements:
|
| 1. Active customer
| 2. Premium customer
| 3. SMS enabled
| 4. New-arrival alerts enabled
| 5. Valid phone number
|
|--------------------------------------------------------------------------
*/

$customerSql = "
    SELECT
        id,
        customer_name,
        phone
    FROM customers
    WHERE status = 'Active'
      AND premium_status = 'Premium'
      AND sms_enabled = 1
      AND new_arrival_alerts = 1
      AND phone IS NOT NULL
      AND TRIM(phone) <> ''
    ORDER BY customer_name ASC
";

$customers = mysqli_query(
    $conn,
    $customerSql
);

if (!$customers) {
    die(
        "Customer query error: "
        . mysqli_error($conn)
    );
}

/*
|--------------------------------------------------------------------------
| Process Premium Customers
|--------------------------------------------------------------------------
*/

while ($customer = mysqli_fetch_assoc($customers)) {

    $customerId = (int) (
        $customer['id'] ?? 0
    );

    $customerName = trim(
        $customer['customer_name'] ?? ''
    );

    $phone = trim(
        $customer['phone'] ?? ''
    );

    /*
    |--------------------------------------------------------------------------
    | Safety Check
    |--------------------------------------------------------------------------
    */

    if (
        $customerId <= 0 ||
        $customerName === '' ||
        $phone === ''
    ) {

        $skipped++;

        continue;
    }

    /*
    |--------------------------------------------------------------------------
    | Prevent Duplicate Notification
    |--------------------------------------------------------------------------
    |
    | A customer should not receive the same product's
    | pending notification more than once.
    |
    */

    $duplicateSql = "
        SELECT id
        FROM notifications
        WHERE customer_id = ?
          AND notification_type = 'New Arrival'
          AND status = 'Pending'
          AND message LIKE ?
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

    /*
    |--------------------------------------------------------------------------
    | Product-specific duplicate marker
    |--------------------------------------------------------------------------
    */

    $productMarker = "%{$productName}%";

    mysqli_stmt_bind_param(
        $duplicateStmt,
        "is",
        $customerId,
        $productMarker
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
    | Build SMS Message
    |--------------------------------------------------------------------------
    */

    $message =
        "Hello {$customerName}, "
        . "R&R Collection has a new arrival: "
        . "{$productName}";

    if ($categoryName !== '') {

        $message .=
            " ({$categoryName})";
    }

    if ($productPrice > 0) {

        $message .=
            " at KSh "
            . number_format($productPrice, 2);

    }

    if ($productUnit !== '') {

        $message .=
            " per {$productUnit}";
    }

    $message .=
        ". Visit R&R Collection to check it out. "
        . "Thank you.";

    /*
    |--------------------------------------------------------------------------
    | Insert Pending Notification
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
        (?, 'New Arrival', ?, ?, 'Pending')
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
        "iss",
        $customerId,
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

mysqli_free_result($customers);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    New Arrival Notification | R&R Collection
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
    max-width: 850px;
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
    margin-bottom: 25px;
}

.product-box {
    background: #f8fafc;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 18px;
    margin-bottom: 22px;
}

.product-box strong {
    display: block;
    font-size: 18px;
    margin-bottom: 5px;
}

.product-box span {
    color: #667085;
    font-size: 13px;
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
            <i>📦</i>
            Premium New-Arrival Engine
        </h1>

        <div class="subtitle">
            R&amp;R Collection Notification System
        </div>

        <div class="product-box">

            <strong>
                <?= htmlspecialchars($productName); ?>
            </strong>

            <span>

                <?php if ($categoryName !== ''): ?>

                    <?= htmlspecialchars($categoryName); ?>

                <?php endif; ?>

                <?php if ($productPrice > 0): ?>

                    · KSh
                    <?= number_format($productPrice, 2); ?>

                <?php endif; ?>

            </span>

        </div>

        <div class="status">

            <strong>
                Premium notification generation completed.
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

            Eligible Premium customers receive a
            <strong>Pending</strong> notification record only.

            No external SMS provider is connected yet.

        </div>

    </div>

</div>

</body>

</html>