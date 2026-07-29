<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

header('Content-Type: application/json');


/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
*/

if (!hasPermission('make_sales')) {

    echo json_encode([
        'success' => false,
        'message' => 'Access Denied'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Only POST requests
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Read JSON
|--------------------------------------------------------------------------
*/

$input = json_decode(
    file_get_contents("php://input"),
    true
);


if (!$input) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid data received.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Cart
|--------------------------------------------------------------------------
*/

$cart = $input['cart'] ?? [];


/*
|--------------------------------------------------------------------------
| Payment information
|--------------------------------------------------------------------------
*/

$payment_method =
    trim($input['payment_method'] ?? 'Cash');

$amount_paid =
    (float)($input['amount_paid'] ?? 0);

$customer_id =
    !empty($input['customer_id'])
        ? (int)$input['customer_id']
        : null;

$discount =
    (float)($input['discount'] ?? 0);


/*
|--------------------------------------------------------------------------
| Validate cart
|--------------------------------------------------------------------------
*/

if (empty($cart)) {

    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Validate payment method
|--------------------------------------------------------------------------
*/

$allowed_methods = [
    'Cash',
    'Mpesa',
    'Credit',
    'Installment'
];


if (!in_array($payment_method, $allowed_methods, true)) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid payment method.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Credit / Installment requires customer
|--------------------------------------------------------------------------
*/

if (
    ($payment_method === 'Credit' ||
     $payment_method === 'Installment')
    &&
    !$customer_id
) {

    echo json_encode([
        'success' => false,
        'message' => 'Please select a customer for credit or installment sales.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Get logged-in user
|--------------------------------------------------------------------------
*/

$user_id = $_SESSION['user_id'] ?? 0;


if (!$user_id) {

    echo json_encode([
        'success' => false,
        'message' => 'User session expired. Please login again.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| Start database transaction
|--------------------------------------------------------------------------
*/

mysqli_begin_transaction($conn);


try {


    /*
    |--------------------------------------------------------------------------
    | Calculate subtotal from DATABASE prices
    |--------------------------------------------------------------------------
    */

    $subtotal = 0;

    $validated_items = [];


    foreach ($cart as $item) {


        $product_id =
            (int)($item['id'] ?? 0);

        $requested_qty =
            (int)($item['qty'] ?? 0);


        if ($product_id <= 0 || $requested_qty <= 0) {

            throw new Exception(
                "Invalid product information."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Lock product row while processing sale
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "
            SELECT
                id,
                product_name,
                selling_price,
                quantity
            FROM products
            WHERE id = ?
            FOR UPDATE
            "
        );


        if (!$stmt) {

            throw new Exception(
                mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $product_id
        );


        mysqli_stmt_execute($stmt);


        $result =
            mysqli_stmt_get_result($stmt);


        $product =
            mysqli_fetch_assoc($result);


        mysqli_stmt_close($stmt);


        if (!$product) {

            throw new Exception(
                "Product ID {$product_id} was not found."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Check stock
        |--------------------------------------------------------------------------
        */

        $available_stock =
            (int)$product['quantity'];


        if ($requested_qty > $available_stock) {

            throw new Exception(
                $product['product_name'] .
                " has only " .
                $available_stock .
                " item(s) in stock."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Use price from DATABASE
        |--------------------------------------------------------------------------
        */

        $price =
            (float)$product['selling_price'];


        $item_subtotal =
            $price * $requested_qty;


        $subtotal += $item_subtotal;


        $validated_items[] = [

            'product_id' =>
                $product_id,

            'product_name' =>
                $product['product_name'],

            'quantity' =>
                $requested_qty,

            'price' =>
                $price,

            'subtotal' =>
                $item_subtotal

        ];

    }


    /*
    |--------------------------------------------------------------------------
    | Validate discount
    |--------------------------------------------------------------------------
    */

    if ($discount < 0) {

        $discount = 0;

    }


    if ($discount > $subtotal) {

        throw new Exception(
            "Discount cannot exceed subtotal."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Calculate total
    |--------------------------------------------------------------------------
    */

    $total =
        $subtotal - $discount;


    /*
    |--------------------------------------------------------------------------
    | Calculate balance
    |--------------------------------------------------------------------------
    */

    $balance =
        $total - $amount_paid;


    if ($balance < 0) {

        $balance = 0;

    }


    /*
    |--------------------------------------------------------------------------
    | Generate UUID
    |--------------------------------------------------------------------------
    */

    $uuid =
        sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',

            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),

            mt_rand(0, 0xffff),

            mt_rand(0, 0x0fff) | 0x4000,

            mt_rand(0, 0x3fff) | 0x8000,

            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );


    /*
    |--------------------------------------------------------------------------
    | Generate invoice number
    |--------------------------------------------------------------------------
    */

    $invoice_no =
        'INV-' .
        date('YmdHis') .
        '-' .
        mt_rand(100, 999);


    /*
    |--------------------------------------------------------------------------
    | Insert sale
    |--------------------------------------------------------------------------
    */

    $stmt = mysqli_prepare(
        $conn,
        "
        INSERT INTO sales
        (
            uuid,
            invoice_no,
            customer_id,
            user_id,
            subtotal,
            total,
            discount,
            payment_method,
            amount_paid,
            balance,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'Completed'
        )
        "
    );


    if (!$stmt) {

        throw new Exception(
            mysqli_error($conn)
        );

    }


    mysqli_stmt_bind_param(
        $stmt,
        "ssiiddsddd",

        $uuid,
        $invoice_no,
        $customer_id,
        $user_id,
        $subtotal,
        $total,
        $discount,
        $payment_method,
        $amount_paid,
        $balance
    );


    if (!mysqli_stmt_execute($stmt)) {

        throw new Exception(
            mysqli_stmt_error($stmt)
        );

    }


    $sale_id =
        mysqli_insert_id($conn);


    mysqli_stmt_close($stmt);


    /*
    |--------------------------------------------------------------------------
    | Insert sale items + reduce stock
    |--------------------------------------------------------------------------
    */

    foreach ($validated_items as $item) {


        /*
        |--------------------------------------------------------------------------
        | Insert sale item
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "
            INSERT INTO sale_items
            (
                sale_id,
                product_id,
                quantity,
                price,
                subtotal
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?
            )
            "
        );


        if (!$stmt) {

            throw new Exception(
                mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $stmt,
            "iiidd",

            $sale_id,

            $item['product_id'],

            $item['quantity'],

            $item['price'],

            $item['subtotal']
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                mysqli_stmt_error($stmt)
            );

        }


        mysqli_stmt_close($stmt);


        /*
        |--------------------------------------------------------------------------
        | Reduce stock
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "
            UPDATE products
            SET quantity = quantity - ?
            WHERE id = ?
            AND quantity >= ?
            "
        );


        if (!$stmt) {

            throw new Exception(
                mysqli_error($conn)
            );

        }


        mysqli_stmt_bind_param(
            $stmt,
            "iii",

            $item['quantity'],

            $item['product_id'],

            $item['quantity']
        );


        if (!mysqli_stmt_execute($stmt)) {

            throw new Exception(
                mysqli_stmt_error($stmt)
            );

        }


        if (mysqli_stmt_affected_rows($stmt) !== 1) {

            throw new Exception(
                "Unable to update stock for " .
                $item['product_name']
            );

        }


        mysqli_stmt_close($stmt);

    }


    /*
    |--------------------------------------------------------------------------
    | Commit everything
    |--------------------------------------------------------------------------
    */

    mysqli_commit($conn);


    /*
    |--------------------------------------------------------------------------
    | Success
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        'success' => true,

        'message' =>
            'Sale completed successfully.',

        'sale_id' =>
            $sale_id,

        'invoice_no' =>
            $invoice_no,

        'subtotal' =>
            number_format($subtotal, 2, '.', ''),

        'discount' =>
            number_format($discount, 2, '.', ''),

        'total' =>
            number_format($total, 2, '.', ''),

        'amount_paid' =>
            number_format($amount_paid, 2, '.', ''),

        'balance' =>
    number_format($balance, 2, '.', ''),

'change' =>
    number_format(
        max(0, $amount_paid - $total),
        2,
        '.',
        ''
    )
            

    ]);


} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | Rollback
    |--------------------------------------------------------------------------
    */

    mysqli_rollback($conn);


    echo json_encode([

        'success' => false,

        'message' =>
            $e->getMessage()

    ]);

}

?>