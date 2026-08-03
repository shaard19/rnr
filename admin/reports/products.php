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
| Product Sales Report
|--------------------------------------------------------------------------
|
| IMPORTANT:
| The sales table uses payment_status:
| Paid / Partial / Credit
|
| Product sales should include ALL valid sales.
| Therefore, we do not filter by payment_status.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.product_name,

        COALESCE(SUM(si.quantity), 0) AS quantity_sold,

        COALESCE(SUM(si.subtotal), 0) AS revenue,

        COUNT(DISTINCT si.sale_id) AS transactions

    FROM sale_items si

    INNER JOIN sales s
        ON s.id = si.sale_id

    INNER JOIN products p
        ON p.id = si.product_id

    WHERE
        DATE(s.sale_date) BETWEEN '$from' AND '$to'

    GROUP BY
        p.id,
        p.product_name

    ORDER BY
        quantity_sold DESC
";


$result = mysqli_query($conn, $sql);


/*
|--------------------------------------------------------------------------
| Query Error Handling
|--------------------------------------------------------------------------
*/

if (!$result) {

    die(
        "Product Sales Report Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}


/*
|--------------------------------------------------------------------------
| Calculate Summary Totals
|--------------------------------------------------------------------------
*/

$total_quantity = 0;
$total_revenue = 0;
$total_transactions = 0;

while ($row = mysqli_fetch_assoc($result)) {

    $total_quantity += (float) $row['quantity_sold'];

    $total_revenue += (float) $row['revenue'];

    $total_transactions += (int) $row['transactions'];
}


/*
|--------------------------------------------------------------------------
| Re-run Query For Table
|--------------------------------------------------------------------------
*/

$result = mysqli_query($conn, $sql);

if (!$result) {

    die(
        "Unable to reload Product Sales Report: " .
        htmlspecialchars(mysqli_error($conn))
    );
}


$products_sold = mysqli_num_rows($result);

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
Product Sales Report - R&R Collection
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
            Product Sales Report
        </h1>

        <p>
            Products sold and revenue generated
        </p>

    </div>


    <div class="report-date">

        <?= date('d M Y'); ?>

    </div>

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


<a href="products.php">
    Reset
</a>


</form>

</div>



<!-- =====================================================
     SUMMARY CARDS
====================================================== -->

<div class="report-cards">


<div class="report-card">

<div class="card-icon">
📦
</div>

<div>

<span>
Units Sold
</span>

<h2>
<?= number_format($total_quantity); ?>
</h2>

</div>

</div>



<div class="report-card">

<div class="card-icon">
💰
</div>

<div>

<span>
Product Revenue
</span>

<h2>
KSh <?= number_format($total_revenue, 2); ?>
</h2>

</div>

</div>



<div class="report-card">

<div class="card-icon">
🧾
</div>

<div>

<span>
Sales Transactions
</span>

<h2>
<?= number_format($total_transactions); ?>
</h2>

</div>

</div>



<div class="report-card">

<div class="card-icon">
🏆
</div>

<div>

<span>
Products Sold
</span>

<h2>
<?= number_format($products_sold); ?>
</h2>

</div>

</div>


</div>



<!-- =====================================================
     PRODUCT PERFORMANCE
====================================================== -->

<div class="recent-sales">


<div class="section-header">


<div>

<h2>
Product Performance
</h2>

<p>

<?= htmlspecialchars($from); ?>

&nbsp;to&nbsp;

<?= htmlspecialchars($to); ?>

</p>

</div>


<!-- =====================================================
     EXCEL EXPORT BUTTON
====================================================== -->

<div>

<a
    href="export_products_excel.php?from=<?= urlencode($from); ?>&to=<?= urlencode($to); ?>"
    target="_blank"
    class="btn btn-success"
>
    📊 Export Excel
</a>

</div>


</div>



<!-- =====================================================
     PRODUCT TABLE
====================================================== -->

<div class="table-wrapper">


<table>


<thead>

<tr>

<th>
Rank
</th>

<th>
Product
</th>

<th>
Units Sold
</th>

<th>
Transactions
</th>

<th>
Revenue
</th>

</tr>

</thead>


<tbody>


<?php if ($result && mysqli_num_rows($result) > 0): ?>


<?php $rank = 1; ?>


<?php while ($row = mysqli_fetch_assoc($result)): ?>


<tr>


<td>

<strong>
#<?= $rank++; ?>
</strong>

</td>


<td>

<strong>

<?= htmlspecialchars(
    $row['product_name']
); ?>

</strong>

</td>


<td>

<?= number_format(
    $row['quantity_sold']
); ?>

</td>


<td>

<?= number_format(
    $row['transactions']
); ?>

</td>


<td>

<strong>

KSh <?= number_format(
    $row['revenue'],
    2
); ?>

</strong>

</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
    colspan="5"
    class="empty-state"
>

No product sales found for the selected period.

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
```
