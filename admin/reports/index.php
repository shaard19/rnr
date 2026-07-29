<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
*/

if (!hasPermission('view_reports')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| Today's date
|--------------------------------------------------------------------------
*/

$today = date('Y-m-d');


/*
|--------------------------------------------------------------------------
| Total Sales Today
|--------------------------------------------------------------------------
*/

$sales_today = 0;

$result = mysqli_query(
    $conn,
    "
    SELECT COALESCE(SUM(total), 0) AS total
    FROM sales
    WHERE DATE(sale_date) = '$today'
    AND status = 'Completed'
    "
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $sales_today = (float)$row['total'];

}


/*
|--------------------------------------------------------------------------
| Transactions Today
|--------------------------------------------------------------------------
*/

$transactions_today = 0;

$result = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM sales
    WHERE DATE(sale_date) = '$today'
    AND status = 'Completed'
    "
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $transactions_today = (int)$row['total'];

}


/*
|--------------------------------------------------------------------------
| Amount Collected Today
|--------------------------------------------------------------------------
*/

$collected_today = 0;

$result = mysqli_query(
    $conn,
    "
    SELECT COALESCE(SUM(amount_paid), 0) AS total
    FROM sales
    WHERE DATE(sale_date) = '$today'
    AND status = 'Completed'
    "
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $collected_today = (float)$row['total'];

}


/*
|--------------------------------------------------------------------------
| Outstanding Balance
|--------------------------------------------------------------------------
*/

$outstanding_balance = 0;

$result = mysqli_query(
    $conn,
    "
    SELECT COALESCE(SUM(balance), 0) AS total
    FROM sales
    WHERE balance > 0
    AND status = 'Completed'
    "
);

if ($result) {

    $row = mysqli_fetch_assoc($result);

    $outstanding_balance = (float)$row['total'];

}


/*
|--------------------------------------------------------------------------
| Recent Sales
|--------------------------------------------------------------------------
*/

$recent_sales = mysqli_query(
    $conn,
    "
    SELECT
        s.id,
        s.invoice_no,
        s.total,
        s.amount_paid,
        s.balance,
        s.payment_method,
        s.sale_date,
        COALESCE(c.customer_name, 'Walk In Customer') AS customer_name
    FROM sales s

    LEFT JOIN customers c
        ON c.id = s.customer_id

    WHERE s.status = 'Completed'

    ORDER BY s.id DESC

    LIMIT 10
    "
);

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
Reports - R&R Collection
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

    <div>

        <h1>
            Reports
        </h1>

        <p>
            R&R Collection business performance overview
        </p>

    </div>


    <div class="report-date">

        <?= date('d M Y'); ?>

    </div>

</div>



<!-- =====================================================
     SUMMARY CARDS
====================================================== -->

<div class="report-cards">


<div class="report-card">

    <div class="card-icon">
        💰
    </div>

    <div>

        <span>
            Sales Today
        </span>

        <h2>
            KSh <?= number_format($sales_today, 2); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        🧾
    </div>

    <div>

        <span>
            Transactions
        </span>

        <h2>
            <?= number_format($transactions_today); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        💵
    </div>

    <div>

        <span>
            Collected Today
        </span>

        <h2>
            KSh <?= number_format($collected_today, 2); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        ⚠️
    </div>

    <div>

        <span>
            Outstanding
        </span>

        <h2>
            KSh <?= number_format($outstanding_balance, 2); ?>
        </h2>

    </div>

</div>


</div>



<!-- =====================================================
     REPORT LINKS
====================================================== -->

<div class="report-links">


<a href="sales.php">

    <strong>
        Sales Report
    </strong>

    <span>
        View sales by date, payment method and customer
    </span>

</a>



<a href="products.php">

    <strong>
        Product Sales
    </strong>

    <span>
        View products sold and revenue generated
    </span>

</a>



<a href="stock.php">

    <strong>
        Stock Report
    </strong>

    <span>
        View current stock and low-stock products
    </span>

</a>



<a href="customers.php">

    <strong>
        Customer / Credit Report
    </strong>

    <span>
        View customer purchases and outstanding balances
    </span>

</a>


</div>



<!-- =====================================================
     RECENT SALES
====================================================== -->

<div class="recent-sales">


<div class="section-header">

    <div>

        <h2>
            Recent Sales
        </h2>

        <p>
            Latest completed transactions
        </p>

    </div>


    <a href="sales.php">
        View All
    </a>

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


<?php if ($recent_sales && mysqli_num_rows($recent_sales) > 0): ?>


<?php while ($sale = mysqli_fetch_assoc($recent_sales)): ?>


<tr>

<td>

<strong>
<?= htmlspecialchars($sale['invoice_no']); ?>
</strong>

</td>


<td>

<?= htmlspecialchars($sale['customer_name']); ?>

</td>


<td>

<span class="payment-badge">

<?= htmlspecialchars($sale['payment_method']); ?>

</span>

</td>


<td>

KSh <?= number_format($sale['total'], 2); ?>

</td>


<td>

KSh <?= number_format($sale['amount_paid'], 2); ?>

</td>


<td>

<?php if ($sale['balance'] > 0): ?>

<span class="balance-warning">

KSh <?= number_format($sale['balance'], 2); ?>

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

No completed sales found.

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

