<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


/*
|--------------------------------------------------------------------------
| PERMISSION
|--------------------------------------------------------------------------
*/

if (!hasPermission('make_sale')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| SALE ID
|--------------------------------------------------------------------------
*/

$saleId = isset($_GET['id'])
    ? (int)$_GET['id']
    : 0;


if ($saleId <= 0) {
    die("Invalid sale.");
}


/*
|--------------------------------------------------------------------------
| LOAD SALE
|--------------------------------------------------------------------------
*/

$saleStmt = mysqli_prepare(
    $conn,
    "
    SELECT
        s.id,
        s.invoice_no,
        s.customer_id,
        s.user_id,
        s.total,
        s.payment_method,
        s.payment_status,
        s.amount_paid,
        s.balance,
        s.sale_date,
        c.customer_name,
        u.fullname
    FROM sales s
    LEFT JOIN customers c
        ON s.customer_id = c.id
    INNER JOIN users u
        ON s.user_id = u.id
    WHERE s.id = ?
    LIMIT 1
    "
);


if (!$saleStmt) {
    die("Sale Query Error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $saleStmt,
    "i",
    $saleId
);


mysqli_stmt_execute($saleStmt);


$saleResult =
    mysqli_stmt_get_result($saleStmt);


if (
    !$saleResult ||
    mysqli_num_rows($saleResult) === 0
) {
    mysqli_stmt_close($saleStmt);
    die("Sale not found.");
}


$sale =
    mysqli_fetch_assoc($saleResult);


mysqli_stmt_close($saleStmt);


/*
|--------------------------------------------------------------------------
| LOAD SALE ITEMS
|--------------------------------------------------------------------------
*/

$itemStmt = mysqli_prepare(
    $conn,
    "
    SELECT
        si.quantity,
        si.price,
        si.subtotal,
        p.product_code,
        p.product_name,
        p.unit
    FROM sale_items si
    INNER JOIN products p
        ON si.product_id = p.id
    WHERE si.sale_id = ?
    ORDER BY si.id ASC
    "
);


if (!$itemStmt) {
    die("Sale Items Query Error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $itemStmt,
    "i",
    $saleId
);


mysqli_stmt_execute($itemStmt);


$itemsResult =
    mysqli_stmt_get_result($itemStmt);


if (!$itemsResult) {
    mysqli_stmt_close($itemStmt);
    die("Unable to load sale items.");
}

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
Receipt <?= htmlspecialchars($sale['invoice_no']); ?>
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    padding: 20px;

    background: #f2f2f2;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: #111;

}


.receipt {

    width: 80mm;

    max-width: 100%;

    margin: 0 auto;

    background: #fff;

    padding: 15px;

}


.center {
    text-align: center;
}


.store-name {

    font-size: 21px;

    font-weight: bold;

    margin-bottom: 4px;

}


.receipt-title {

    font-size: 14px;

    font-weight: bold;

    margin: 10px 0;

}


.meta {

    font-size: 12px;

    line-height: 1.6;

}


.divider {

    border-top: 1px dashed #000;

    margin: 10px 0;

}


table {

    width: 100%;

    border-collapse: collapse;

    font-size: 11px;

}


th,
td {

    padding: 4px 0;

    vertical-align: top;

}


th {

    border-bottom: 1px solid #000;

}


.text-right {
    text-align: right;
}


.text-center {
    text-align: center;
}


.total-row td {

    font-size: 13px;

    font-weight: bold;

    border-top: 1px solid #000;

    padding-top: 7px;

}


.payment {

    font-size: 12px;

    line-height: 1.7;

}


.footer {

    text-align: center;

    font-size: 11px;

    margin-top: 15px;

}


.print-button {

    display: block;

    width: 80mm;

    max-width: 100%;

    margin: 15px auto;

    padding: 10px;

    border: 0;

    border-radius: 5px;

    background: #111;

    color: #fff;

    cursor: pointer;

    font-size: 14px;

}


@media print {

    body {

        background: #fff;

        padding: 0;

    }


    .receipt {

        width: 80mm;

        margin: 0;

        padding: 5mm;

    }


    .print-button {

        display: none;

    }

}


</style>

</head>


<body>


<div class="receipt">


    <!-- STORE -->

    <div class="center">

        <div class="store-name">
            R&R Collection
        </div>

        <div class="receipt-title">
            SALES RECEIPT
        </div>

    </div>


    <div class="divider"></div>


    <!-- SALE INFORMATION -->

    <div class="meta">

        <strong>
            Invoice:
        </strong>

        <?= htmlspecialchars(
            $sale['invoice_no']
        ); ?>

        <br>


        <strong>
            Date:
        </strong>

        <?= date(
            'd/m/Y H:i',
            strtotime($sale['sale_date'])
        ); ?>

        <br>


        <strong>
            Customer:
        </strong>

        <?= htmlspecialchars(
            $sale['customer_name']
                ?? 'Walk In Customer'
        ); ?>

        <br>


        <strong>
            Cashier:
        </strong>

        <?= htmlspecialchars(
            $sale['fullname']
        ); ?>

    </div>


    <div class="divider"></div>


    <!-- ITEMS -->

    <table>

        <thead>

            <tr>

                <th>
                    Item
                </th>

                <th class="text-center">
                    Qty
                </th>

                <th class="text-right">
                    Total
                </th>

            </tr>

        </thead>


        <tbody>

        <?php while ($item = mysqli_fetch_assoc($itemsResult)): ?>

            <tr>

                <td>

                    <?= htmlspecialchars(
                        $item['product_name']
                    ); ?>

                    <br>

                    <small>
                        <?= htmlspecialchars(
                            $item['product_code']
                        ); ?>
                    </small>

                </td>


                <td class="text-center">

                    <?= (int)$item['quantity']; ?>

                    <?php if (!empty($item['unit'])): ?>

                        <?= htmlspecialchars(
                            $item['unit']
                        ); ?>

                    <?php endif; ?>

                </td>


                <td class="text-right">

                    KSh
                    <?= number_format(
                        (float)$item['subtotal'],
                        2
                    ); ?>

                </td>

            </tr>

        <?php endwhile; ?>

        </tbody>


        <tfoot>

            <tr class="total-row">

                <td colspan="2">
                    TOTAL
                </td>

                <td class="text-right">

                    KSh
                    <?= number_format(
                        (float)$sale['total'],
                        2
                    ); ?>

                </td>

            </tr>

        </tfoot>

    </table>


    <div class="divider"></div>


    <!-- PAYMENT -->

    <div class="payment">

        <strong>
            Payment:
        </strong>

        <?= htmlspecialchars(
            $sale['payment_method']
        ); ?>

        <br>


        <strong>
            Status:
        </strong>

        <?= htmlspecialchars(
            $sale['payment_status']
        ); ?>

        <br>


        <strong>
            Amount Paid:
        </strong>

        KSh
        <?= number_format(
            (float)$sale['amount_paid'],
            2
        ); ?>


        <?php

        $change = max(
            0,
            (float)$sale['amount_paid'] -
            (float)$sale['total']
        );

        ?>


        <?php if ($change > 0): ?>

            <br>

            <strong>
                Change:
            </strong>

            KSh
            <?= number_format(
                $change,
                2
            ); ?>

        <?php endif; ?>


        <?php if ((float)$sale['balance'] > 0): ?>

            <br>

            <strong>
                Balance:
            </strong>

            KSh
            <?= number_format(
                (float)$sale['balance'],
                2
            ); ?>

        <?php endif; ?>

    </div>


    <div class="divider"></div>


    <!-- FOOTER -->

    <div class="footer">

        <strong>
            Thank you for shopping with R&R Collection!
        </strong>

        <br>

        Please keep this receipt for your records.<br>
        Developed By: Shadrack K Kitele, BSIT.
    </div>


</div>


<!-- PRINT -->

<button
    type="button"
    class="print-button"
    onclick="window.print()"
>

    🖨 Print Receipt

</button>


</body>

</html>

<?php

mysqli_stmt_close($itemStmt);

?>