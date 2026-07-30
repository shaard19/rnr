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

if (!hasPermission('view_reports')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$from = $_GET['from'] ?? date('Y-m-d');
$to = $_GET['to'] ?? date('Y-m-d');
$payment = $_GET['payment'] ?? '';


/*
|--------------------------------------------------------------------------
| ESCAPE VALUES
|--------------------------------------------------------------------------
*/

$from = mysqli_real_escape_string($conn, $from);
$to = mysqli_real_escape_string($conn, $to);


/*
|--------------------------------------------------------------------------
| BUILD CONDITIONS
|--------------------------------------------------------------------------
*/

$conditions = [];

$conditions[] = "DATE(s.sale_date) BETWEEN '$from' AND '$to'";

$conditions = [];

$conditions[] = "DATE(s.sale_date) BETWEEN '$from' AND '$to'";

if ($payment !== '') {
    $payment = mysqli_real_escape_string($conn, $payment);
    $conditions[] = "s.payment_method = '$payment'";
}

$where = implode(" AND ", $conditions);


if ($payment !== '') {

    $payment = mysqli_real_escape_string($conn, $payment);

    $conditions[] = "s.payment_method = '$payment'";

}


$where = implode(" AND ", $conditions);


/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$summary_sql = "

    SELECT

        COUNT(*) AS transactions,

        COALESCE(SUM(s.total), 0) AS total,

        COALESCE(SUM(s.amount_paid), 0) AS paid,

        COALESCE(SUM(s.balance), 0) AS balance

    FROM sales s

    WHERE $where

";


$summary_result = mysqli_query($conn, $summary_sql);


if (!$summary_result) {

    die(
        "SUMMARY SQL ERROR: "
        . mysqli_error($conn)
    );

}


$summary = mysqli_fetch_assoc($summary_result);


/*
|--------------------------------------------------------------------------
| SALES TRANSACTIONS
|--------------------------------------------------------------------------
*/

$sales_sql = "

    SELECT

        s.id,

        s.invoice_no,

        s.total,

        s.payment_method,

        s.amount_paid,

        s.balance,

        s.sale_date,

        COALESCE(
            c.customer_name,
            'Walk In Customer'
        ) AS customer_name

    FROM sales s

    LEFT JOIN customers c
        ON c.id = s.customer_id

    WHERE $where

    ORDER BY s.sale_date DESC

";


$sales = mysqli_query($conn, $sales_sql);


if (!$sales) {

    die(
        "SALES SQL ERROR: "
        . mysqli_error($conn)
    );

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
Sales Report - R&R Collection
</title>

<link
    rel="stylesheet"
    href="../../assets/css/style.css"
>

<link
    rel="stylesheet"
    href="../../assets/css/reports.css"
>

</head>

<body>

<div class="reports-container">

<!-- =====================================================
     HEADER
====================================================== -->

<div class="reports-header">

```
<div>

    <h1>
        Sales Report
    </h1>

    <p>
        Detailed sales performance
    </p>

</div>


<div class="report-date">

    <?= date('d M Y'); ?>

</div>
```

</div>

<!-- =====================================================
     FILTER
====================================================== -->

<div class="report-filter">

<form method="GET">

<div>

<label>
From
</label>

<input
type="date"
name="from"
value="<?= htmlspecialchars($from); ?>"

>

</div>

<div>

<label>
To
</label>

<input
type="date"
name="to"
value="<?= htmlspecialchars($to); ?>"

>

</div>

<div>

<label>
Payment Method
</label>

<select name="payment">

<option value="">
All Payments
</option>

<option
    value="Cash"
    <?= $payment === 'Cash' ? 'selected' : ''; ?>
>

Cash

</option>

<option
    value="Mpesa"
    <?= $payment === 'Mpesa' ? 'selected' : ''; ?>
>

M-Pesa

</option>

<option
    value="Credit"
    <?= $payment === 'Credit' ? 'selected' : ''; ?>
>

Credit

</option>

<option
    value="Installment"
    <?= $payment === 'Installment' ? 'selected' : ''; ?>
>

Installment

</option>

</select>

</div>

<button type="submit">

Generate Report

</button>

<a href="sales.php">

Reset

</a>

</form>

</div>

<!-- =====================================================
     SUMMARY CARDS
====================================================== -->

<div class="report-cards">

<!-- TRANSACTIONS -->

<div class="report-card">

```
<div class="card-icon">
    🧾
</div>

<div>

    <span>
        Transactions
    </span>

    <h2>

        <?= number_format(
            (int)$summary['transactions']
        ); ?>

    </h2>

</div>
```

</div>

<!-- TOTAL SALES -->

<div class="report-card">

```
<div class="card-icon">
    💰
</div>

<div>

    <span>
        Total Sales
    </span>

    <h2>

        KSh <?= number_format(
            (float)$summary['total'],
            2
        ); ?>

    </h2>

</div>
```

</div>

<!-- AMOUNT PAID -->

<div class="report-card">

```
<div class="card-icon">
    💵
</div>

<div>

    <span>
        Amount Paid
    </span>

    <h2>

        KSh <?= number_format(
            (float)$summary['paid'],
            2
        ); ?>

    </h2>

</div>
```

</div>

<!-- OUTSTANDING -->

<div class="report-card">

```
<div class="card-icon">
    ⚠️
</div>

<div>

    <span>
        Outstanding
    </span>

    <h2>

        KSh <?= number_format(
            (float)$summary['balance'],
            2
        ); ?>

    </h2>

</div>
```

</div>

</div>

<!-- =====================================================
     SALES TABLE
====================================================== -->

<div class="recent-sales">

<div class="section-header">

```
<div>

    <h2>
        Sales Transactions
    </h2>

    <p>

        <?= htmlspecialchars($from); ?>

        to

        <?= htmlspecialchars($to); ?>

    </p>

</div>
```

</div>

<div class="table-wrapper">

<table>

<thead>

<tr>

<th>
Invoice
</th>

<th>
Customer
</th>

<th>
Payment
</th>

<th>
Total
</th>

<th>
Paid
</th>

<th>
Balance
</th>

<th>
Date
</th>

</tr>

</thead>

<tbody>

<?php if (mysqli_num_rows($sales) > 0): ?>

<?php while ($sale = mysqli_fetch_assoc($sales)): ?>

<tr>

<td>

<strong>

<?= htmlspecialchars(
    $sale['invoice_no']
); ?>

</strong>

</td>

<td>

<?= htmlspecialchars(
    $sale['customer_name']
); ?>

</td>

<td>

<?= htmlspecialchars(
    $sale['payment_method']
); ?>

</td>

<td>

<strong>

KSh <?= number_format(
 (float)$sale['total'],
 2
); ?>

</strong>

</td>

<td>

KSh <?= number_format(
 (float)$sale['amount_paid'],
 2
); ?>

</td>

<td>

<?php if ((float)$sale['balance'] > 0): ?>

<span class="balance-warning">

KSh <?= number_format(
 (float)$sale['balance'],
 2
); ?>

</span>

<?php else: ?>

<span class="balance-clear">

KSh 0.00

</span>

<?php endif; ?>

</td>

<td>

<?= date(
    'd M Y H:i',
    strtotime($sale['sale_date'])
); ?>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td
    colspan="7"
    class="empty-state"
>

No sales found for the selected period.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</body>

</html>
