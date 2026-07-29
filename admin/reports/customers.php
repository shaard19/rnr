<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('view_reports')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| Customer Credit Report
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT

        c.id,
        c.customer_name,

        COUNT(s.id) AS transactions,

        COALESCE(SUM(s.total), 0) AS total_purchases,

        COALESCE(SUM(s.amount_paid), 0) AS total_paid,

        COALESCE(SUM(s.balance), 0) AS outstanding

    FROM customers c

    INNER JOIN sales s
        ON s.customer_id = c.id

    WHERE s.status = 'Completed'

    GROUP BY
        c.id,
        c.customer_name

    ORDER BY
        outstanding DESC,
        c.customer_name ASC
";

$result = mysqli_query($conn, $sql);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$total_customers = 0;
$total_purchases = 0;
$total_paid = 0;
$total_outstanding = 0;

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {

        $total_customers++;

        $total_purchases += (float)$row['total_purchases'];

        $total_paid += (float)$row['total_paid'];

        $total_outstanding += (float)$row['outstanding'];

    }

    // Re-run query because the first loop consumed it
    $result = mysqli_query($conn, $sql);
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
Customer Credit Report - R&R Collection
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
            Customer / Credit Report
        </h1>

        <p>
            Customer purchases and outstanding balances
        </p>

    </div>


    <div class="report-date">

        <?= date('d M Y'); ?>

    </div>

</div>



<!-- =====================================================
     SUMMARY
====================================================== -->

<div class="report-cards">


<div class="report-card">

    <div class="card-icon">
        👥
    </div>

    <div>

        <span>
            Customers
        </span>

        <h2>
            <?= number_format($total_customers); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        🛒
    </div>

    <div>

        <span>
            Total Purchases
        </span>

        <h2>
            KSh <?= number_format(
                $total_purchases,
                2
            ); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        💵
    </div>

    <div>

        <span>
            Total Paid
        </span>

        <h2>
            KSh <?= number_format(
                $total_paid,
                2
            ); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        ⚠️
    </div>

    <div>

        <span>
            Outstanding Credit
        </span>

        <h2>
            KSh <?= number_format(
                $total_outstanding,
                2
            ); ?>
        </h2>

    </div>

</div>


</div>



<!-- =====================================================
     CUSTOMER TABLE
====================================================== -->

<div class="recent-sales">


<div class="section-header">

    <div>

        <h2>
            Customer Accounts
        </h2>

        <p>
            Customers with recorded sales
        </p>

    </div>

</div>



<div class="table-wrapper">


<table>


<thead>

<tr>

<th>
Customer
</th>

<th>
Transactions
</th>

<th>
Total Purchases
</th>

<th>
Amount Paid
</th>

<th>
Outstanding
</th>

<th>
Status
</th>

</tr>

</thead>


<tbody>


<?php if ($result && mysqli_num_rows($result) > 0): ?>


<?php while ($row = mysqli_fetch_assoc($result)): ?>


<tr>


<td>

<strong>

<?= htmlspecialchars(
    $row['customer_name']
); ?>

</strong>

</td>


<td>

<?= number_format(
    $row['transactions']
); ?>

</td>


<td>

KSh <?= number_format(
    $row['total_purchases'],
    2
); ?>

</td>


<td>

KSh <?= number_format(
    $row['total_paid'],
    2
); ?>

</td>


<td>

<strong>

KSh <?= number_format(
    $row['outstanding'],
    2
); ?>

</strong>

</td>


<td>


<?php if ($row['outstanding'] > 0): ?>

<span class="balance-warning">
    CREDIT DUE
</span>

<?php else: ?>

<span class="balance-clear">
    CLEARED
</span>

<?php endif; ?>


</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
    colspan="6"
    class="empty-state"
>

No customer sales found.

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