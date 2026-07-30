<?php

error_reporting(E_ALL);
ini_set('display_errors', 0);

header('Content-Type: application/json');

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


/*
|--------------------------------------------------------------------------
| PERMISSION
|--------------------------------------------------------------------------
*/

if (!hasPermission('make_sale')) {

    echo json_encode([
        'success' => false,
        'message' => 'Access Denied.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| USER
|--------------------------------------------------------------------------
*/

$userId = (int)($_SESSION['user_id'] ?? 0);

if ($userId <= 0) {

    echo json_encode([
        'success' => false,
        'message' => 'User session is invalid.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| READ REQUEST
|--------------------------------------------------------------------------
*/

$input = file_get_contents("php://input");

$data = json_decode($input, true);

if (!is_array($data)) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid sale request.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| BASIC DATA
|--------------------------------------------------------------------------
*/

$customerId = null;

if (
    isset($data['customer_id']) &&
    $data['customer_id'] !== '' &&
    $data['customer_id'] !== null
) {

    $customerId = (int)$data['customer_id'];

    if ($customerId <= 0) {
        $customerId = null;
    }
}


$paymentMethod = trim(
    $data['payment_method'] ?? ''
);


$amountPaid = (float)(
    $data['amount_paid'] ?? 0
);


$cart = $data['cart'] ?? [];


/*
|--------------------------------------------------------------------------
| CART VALIDATION
|--------------------------------------------------------------------------
*/

if (
    !is_array($cart) ||
    count($cart) === 0
) {

    echo json_encode([
        'success' => false,
        'message' => 'Cart is empty.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| NORMALIZE MPESA
|--------------------------------------------------------------------------
*/

if ($paymentMethod === 'Mpesa') {

    $paymentMethod = 'Lipa na M-Pesa';

}


/*
|--------------------------------------------------------------------------
| PAYMENT VALIDATION
|--------------------------------------------------------------------------
*/

if (
    $paymentMethod !== 'Cash' &&
    $paymentMethod !== 'Lipa na M-Pesa' &&
    $paymentMethod !== 'Credit'
) {

    echo json_encode([
        'success' => false,
        'message' => 'Invalid payment method.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| CREDIT FLAG
|--------------------------------------------------------------------------
*/

$isCredit =
    ($paymentMethod === 'Credit');


/*
|--------------------------------------------------------------------------
| PAYMENT STATUS
|--------------------------------------------------------------------------
|
| Your sales table has:
|
| payment_method:
| Cash / Lipa na M-Pesa
|
| payment_status:
| Paid / Partial / Credit
|
| Therefore a Credit sale is stored as:
|
| payment_method = Cash
| payment_status = Credit
|
*/

if ($isCredit) {

    $paymentMethod = 'Cash';

    $paymentStatus = 'Credit';

} else {

    $paymentStatus = 'Paid';

}


/*
|--------------------------------------------------------------------------
| START TRANSACTION
|--------------------------------------------------------------------------
*/

mysqli_begin_transaction($conn);


try {


    /*
    |--------------------------------------------------------------------------
    | CUSTOMER VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($isCredit) {

        /*
        | Credit must belong to a registered customer.
        */

        if ($customerId === null) {

            throw new Exception(
                "Credit sales require a registered customer. " .
                "Please select a customer."
            );

        }


        $customerStmt = mysqli_prepare(
            $conn,
            "
            SELECT
                id,
                customer_name,
                credit_balance
            FROM customers
            WHERE id = ?
            FOR UPDATE
            "
        );


        if (!$customerStmt) {

            throw new Exception(
                "Unable to validate customer."
            );

        }


        mysqli_stmt_bind_param(
            $customerStmt,
            "i",
            $customerId
        );


        if (!mysqli_stmt_execute($customerStmt)) {

            $error =
                mysqli_stmt_error($customerStmt);

            mysqli_stmt_close($customerStmt);

            throw new Exception(
                "Customer validation failed: " . $error
            );

        }


        $customerResult =
            mysqli_stmt_get_result(
                $customerStmt
            );


        if (
            !$customerResult ||
            mysqli_num_rows($customerResult) === 0
        ) {

            mysqli_stmt_close($customerStmt);

            throw new Exception(
                "Selected customer does not exist."
            );

        }


        mysqli_stmt_close($customerStmt);

    }


    /*
    |--------------------------------------------------------------------------
    | VALIDATE NORMAL CUSTOMER
    |--------------------------------------------------------------------------
    */

    if (
        !$isCredit &&
        $customerId !== null
    ) {

        $customerStmt = mysqli_prepare(
            $conn,
            "
            SELECT id
            FROM customers
            WHERE id = ?
            LIMIT 1
            "
        );


        if (!$customerStmt) {

            throw new Exception(
                "Customer validation failed."
            );

        }


        mysqli_stmt_bind_param(
            $customerStmt,
            "i",
            $customerId
        );


        mysqli_stmt_execute(
            $customerStmt
        );


        $customerResult =
            mysqli_stmt_get_result(
                $customerStmt
            );


        if (
            !$customerResult ||
            mysqli_num_rows($customerResult) === 0
        ) {

            mysqli_stmt_close($customerStmt);

            throw new Exception(
                "Selected customer does not exist."
            );

        }


        mysqli_stmt_close($customerStmt);

    }


    /*
    |--------------------------------------------------------------------------
    | PRODUCT QUERY
    |--------------------------------------------------------------------------
    */

    $productStmt = mysqli_prepare(
        $conn,
        "
        SELECT
            id,
            product_code,
            product_name,
            selling_price,
            quantity,
            status
        FROM products
        WHERE id = ?
        FOR UPDATE
        "
    );


    if (!$productStmt) {

        throw new Exception(
            "Unable to prepare product validation."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | BUILD SALE
    |--------------------------------------------------------------------------
    */

    $saleItems = [];

    $grandTotal = 0;


    foreach ($cart as $item) {

        $productId =
            (int)($item['id'] ?? 0);

        $requestedQuantity =
            (int)($item['quantity'] ?? 0);


        if ($productId <= 0) {

            throw new Exception(
                "Invalid product in cart."
            );

        }


        if ($requestedQuantity <= 0) {

            throw new Exception(
                "Invalid product quantity."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | LOCK PRODUCT
        |--------------------------------------------------------------------------
        */

        mysqli_stmt_bind_param(
            $productStmt,
            "i",
            $productId
        );


        if (!mysqli_stmt_execute($productStmt)) {

            throw new Exception(
                "Unable to validate product."
            );

        }


        $productResult =
            mysqli_stmt_get_result(
                $productStmt
            );


        if (
            !$productResult ||
            mysqli_num_rows($productResult) === 0
        ) {

            throw new Exception(
                "Product ID {$productId} was not found."
            );

        }


        $product =
            mysqli_fetch_assoc(
                $productResult
            );


        /*
        |--------------------------------------------------------------------------
        | ACTIVE CHECK
        |--------------------------------------------------------------------------
        */

        if ($product['status'] !== 'Active') {

            throw new Exception(
                "Product '{$product['product_name']}' is inactive."
            );

        }


        /*
        |--------------------------------------------------------------------------
        | STOCK CHECK
        |--------------------------------------------------------------------------
        */

        $availableStock =
            (int)$product['quantity'];


        if (
            $requestedQuantity >
            $availableStock
        ) {

            throw new Exception(

                "Insufficient stock for '" .
                $product['product_name'] .
                "'. Available: " .
                $availableStock .
                ", Requested: " .
                $requestedQuantity

            );

        }


        /*
        |--------------------------------------------------------------------------
        | USE DATABASE PRICE
        |--------------------------------------------------------------------------
        */

        $price =
            (float)$product['selling_price'];


        $subtotal =
            round(
                $price * $requestedQuantity,
                2
            );


        $grandTotal +=
            $subtotal;


        $saleItems[] = [

            'product_id' =>
                $productId,

            'product_name' =>
                $product['product_name'],

            'quantity' =>
                $requestedQuantity,

            'price' =>
                $price,

            'subtotal' =>
                $subtotal

        ];

    }


    mysqli_stmt_close(
        $productStmt
    );


    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */

    $grandTotal =
        round($grandTotal, 2);


    if ($grandTotal <= 0) {

        throw new Exception(
            "Sale total must be greater than zero."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | AMOUNT PAID
    |--------------------------------------------------------------------------
    */

    $amountPaid =
        round($amountPaid, 2);


    if ($amountPaid < 0) {

        throw new Exception(
            "Amount paid cannot be negative."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PAYMENT RULES
    |--------------------------------------------------------------------------
    */

    if ($isCredit) {

        /*
        | Credit supports:
        |
        | 0 paid       = full credit
        | 100 paid     = partial credit
        | total paid  = fully paid
        |
        */

        if ($amountPaid > $grandTotal) {

            throw new Exception(
                "Amount paid cannot exceed the sale total for a credit sale."
            );

        }


        $balance =
            round(
                $grandTotal - $amountPaid,
                2
            );


        /*
        | If everything has been paid,
        | it isn't really a credit sale.
        */

        if ($balance <= 0) {

            $paymentStatus = 'Paid';

        }

    } else {

        /*
        | Cash / M-Pesa
        */

        if ($amountPaid <= 0) {

            throw new Exception(
                "Please enter the amount paid."
            );

        }


        if ($amountPaid < $grandTotal) {

            throw new Exception(
                "Amount paid is less than the sale total."
            );

        }


        $balance = 0;

        $paymentStatus = 'Paid';

    }


    /*
    |--------------------------------------------------------------------------
    | INVOICE NUMBER
    |--------------------------------------------------------------------------
    */

    $invoiceNo =
        'INV-' .
        date('YmdHis') .
        '-' .
        strtoupper(
            substr(
                bin2hex(
                    random_bytes(3)
                ),
                0,
                6
            )
        );


    /*
    |--------------------------------------------------------------------------
    | INSERT SALE
    |--------------------------------------------------------------------------
    */

    $saleStmt = mysqli_prepare(
        $conn,
        "
        INSERT INTO sales
        (
            invoice_no,
            customer_id,
            user_id,
            total,
            payment_method,
            payment_status,
            amount_paid,
            balance
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?)
        "
    );


    if (!$saleStmt) {

        throw new Exception(
            "Unable to prepare sale record."
        );

    }


    mysqli_stmt_bind_param(
        $saleStmt,
        "siisssdd",
        $invoiceNo,
        $customerId,
        $userId,
        $grandTotal,
        $paymentMethod,
        $paymentStatus,
        $amountPaid,
        $balance
    );


    if (!mysqli_stmt_execute($saleStmt)) {

        $error =
            mysqli_stmt_error($saleStmt);

        mysqli_stmt_close($saleStmt);

        throw new Exception(
            "Unable to save sale: " . $error
        );

    }


    $saleId =
        mysqli_insert_id($conn);


    mysqli_stmt_close($saleStmt);


    /*
    |--------------------------------------------------------------------------
    | SALE ITEMS
    |--------------------------------------------------------------------------
    */

    $itemStmt = mysqli_prepare(
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
        (?, ?, ?, ?, ?)
        "
    );


    if (!$itemStmt) {

        throw new Exception(
            "Unable to prepare sale item."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | STOCK UPDATE
    |--------------------------------------------------------------------------
    */

    $stockStmt = mysqli_prepare(
        $conn,
        "
        UPDATE products
        SET quantity = quantity - ?
        WHERE id = ?
        AND quantity >= ?
        "
    );


    if (!$stockStmt) {

        mysqli_stmt_close($itemStmt);

        throw new Exception(
            "Unable to prepare stock update."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | STOCK MOVEMENT
    |--------------------------------------------------------------------------
    */

    $movementStmt = mysqli_prepare(
        $conn,
        "
        INSERT INTO stock_movements
        (
            product_id,
            quantity,
            movement_type,
            reference,
            user_id
        )
        VALUES
        (?, ?, 'OUT', ?, ?)
        "
    );


    if (!$movementStmt) {

        mysqli_stmt_close($itemStmt);
        mysqli_stmt_close($stockStmt);

        throw new Exception(
            "Unable to prepare stock movement."
        );

    }


    /*
    |--------------------------------------------------------------------------
    | PROCESS ITEMS
    |--------------------------------------------------------------------------
    */

    foreach ($saleItems as $item) {

        $productId =
            $item['product_id'];

        $quantity =
            $item['quantity'];

        $price =
            $item['price'];

        $subtotal =
            $item['subtotal'];


        /*
        | SALE ITEM
        */

        mysqli_stmt_bind_param(
            $itemStmt,
            "iiidd",
            $saleId,
            $productId,
            $quantity,
            $price,
            $subtotal
        );


        if (!mysqli_stmt_execute($itemStmt)) {

            throw new Exception(
                "Unable to save sale item."
            );

        }


        /*
        | STOCK
        */

        mysqli_stmt_bind_param(
            $stockStmt,
            "iii",
            $quantity,
            $productId,
            $quantity
        );


        if (!mysqli_stmt_execute($stockStmt)) {

            throw new Exception(
                "Unable to update stock."
            );

        }


        if (
            mysqli_stmt_affected_rows(
                $stockStmt
            ) !== 1
        ) {

            throw new Exception(
                "Stock changed while processing the sale. Sale cancelled."
            );

        }


        /*
        | STOCK MOVEMENT
        */

        $reference =
            $invoiceNo;


        mysqli_stmt_bind_param(
            $movementStmt,
            "iisi",
            $productId,
            $quantity,
            $reference,
            $userId
        );


        if (!mysqli_stmt_execute($movementStmt)) {

            throw new Exception(
                "Unable to record stock movement."
            );

        }

    }


    mysqli_stmt_close($itemStmt);
    mysqli_stmt_close($stockStmt);
    mysqli_stmt_close($movementStmt);


    /*
    |--------------------------------------------------------------------------
    | UPDATE CUSTOMER CREDIT
    |--------------------------------------------------------------------------
    |
    | IMPORTANT:
    | Only the outstanding balance is added.
    |
    | Example:
    |
    | Total = 160
    | Paid  = 100
    | Credit = 60
    |
    */

    if (
        $isCredit &&
        $balance > 0
    ) {

        $creditStmt = mysqli_prepare(
            $conn,
            "
            UPDATE customers
            SET credit_balance =
                credit_balance + ?
            WHERE id = ?
            "
        );


        if (!$creditStmt) {

            throw new Exception(
                "Unable to prepare customer credit update."
            );

        }


        mysqli_stmt_bind_param(
            $creditStmt,
            "di",
            $balance,
            $customerId
        );


        if (!mysqli_stmt_execute($creditStmt)) {

            $error =
                mysqli_stmt_error($creditStmt);

            mysqli_stmt_close($creditStmt);

            throw new Exception(
                "Unable to update customer credit balance: " .
                $error
            );

        }


        if (
            mysqli_stmt_affected_rows(
                $creditStmt
            ) !== 1
        ) {

            mysqli_stmt_close($creditStmt);

            throw new Exception(
                "Customer credit balance could not be updated."
            );

        }


        mysqli_stmt_close($creditStmt);

    }


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    mysqli_commit($conn);


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    echo json_encode([

        'success' =>
            true,

        'message' =>
            $isCredit
                ? 'Credit sale completed successfully.'
                : 'Sale completed successfully.',

        'sale_id' =>
            $saleId,

        'invoice_no' =>
            $invoiceNo,

        'total' =>
            number_format(
                $grandTotal,
                2,
                '.',
                ''
            ),

        'amount_paid' =>
            number_format(
                $amountPaid,
                2,
                '.',
                ''
            ),

        'balance' =>
            number_format(
                $balance,
                2,
                '.',
                ''
            ),

        'payment_status' =>
            $paymentStatus

    ]);

    exit;


} catch (Throwable $e) {


    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    mysqli_rollback($conn);


    error_log(
        "RNR SALE ERROR: " .
        $e->getMessage()
    );


    echo json_encode([

        'success' =>
            false,

        'message' =>
            $e->getMessage()

    ]);

    exit;

}

?>