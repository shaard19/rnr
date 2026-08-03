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
| Date Filters
|--------------------------------------------------------------------------
*/

$from = $_GET['from'] ?? date('Y-m-d');
$to   = $_GET['to'] ?? date('Y-m-d');


/*
|--------------------------------------------------------------------------
| Validate Dates
|--------------------------------------------------------------------------
*/

$from_check = DateTime::createFromFormat('Y-m-d', $from);
$to_check   = DateTime::createFromFormat('Y-m-d', $to);

if (
    !$from_check ||
    !$to_check ||
    $from_check->format('Y-m-d') !== $from ||
    $to_check->format('Y-m-d') !== $to
) {

    $from = date('Y-m-d');
    $to   = date('Y-m-d');

}


/*
|--------------------------------------------------------------------------
| Correct Reversed Dates
|--------------------------------------------------------------------------
*/

if ($from > $to) {

    $temp = $from;
    $from = $to;
    $to = $temp;

}


/*
|--------------------------------------------------------------------------
| Customer Credit Report
|--------------------------------------------------------------------------
|
| Period purchases and payments:
|     sales.sale_date
|
| Current outstanding:
|     customers.credit_balance
|
| We DO NOT use:
|     s.status
|
| because the actual sales table does not contain
| a status column.
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        c.id,
        c.customer_name,
        c.credit_balance,

        COUNT(s.id) AS transactions,

        COALESCE(
            SUM(s.total),
            0
        ) AS total_purchases,

        COALESCE(
            SUM(s.amount_paid),
            0
        ) AS total_paid

    FROM customers c

    LEFT JOIN sales s

        ON s.customer_id = c.id

        AND DATE(s.sale_date)
            BETWEEN ? AND ?

    WHERE
        c.status = 'Active'

    GROUP BY

        c.id,
        c.customer_name,
        c.credit_balance

    HAVING

        transactions > 0
        OR c.credit_balance > 0

    ORDER BY

        c.credit_balance DESC,
        c.customer_name ASC
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Customer Credit Report Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $from,
    $to
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        "Unable to generate Customer Credit Report: " .
        htmlspecialchars(mysqli_stmt_error($stmt))
    );

}


$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$total_customers = 0;
$total_purchases = 0;
$total_paid = 0;
$total_outstanding = 0;


while ($row = mysqli_fetch_assoc($result)) {

    $total_customers++;

    $total_purchases +=
        (float) $row['total_purchases'];

    $total_paid +=
        (float) $row['total_paid'];

    $total_outstanding +=
        (float) $row['credit_balance'];

}


/*
|--------------------------------------------------------------------------
| Re-run Query For Table
|--------------------------------------------------------------------------
*/

mysqli_stmt_close($stmt);


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Unable to reload Customer Credit Report: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $from,
    $to
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        "Unable to reload Customer Credit Report: " .
        htmlspecialchars(mysqli_stmt_error($stmt))
    );

}


$result = mysqli_stmt_get_result($stmt);

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
     DATE FILTER
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
    required
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
    required
>

</div>


<button type="submit">
    Generate Report
</button>


<a href="customers.php">
    Reset
</a>


</form>

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
            Period Purchases
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
            Amount Paid
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
            Current Outstanding
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

        <?= htmlspecialchars($from); ?>

        &nbsp;to&nbsp;

        <?= htmlspecialchars($to); ?>

        &nbsp;|&nbsp;

        Current credit balances

    </p>

</div>



<!-- =====================================================
     EXCEL EXPORT
====================================================== -->

<div>

<a
    href="export_credit_excel.php?from=<?= urlencode($from); ?>&to=<?= urlencode($to); ?>"
    target="_blank"
    class="btn btn-success"
>

    📊 Export Excel

</a>

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
Period Purchases
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


<?php

$current_balance =
    (float) $row['credit_balance'];

?>


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
    $current_balance,
    2
); ?>

</strong>

</td>


<td>


<?php if ($current_balance > 0): ?>

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

No customer credit records found.

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


<?php

if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}

?>