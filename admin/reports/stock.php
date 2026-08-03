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
| Stock Report
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        p.product_name,
        p.quantity,
        p.selling_price,
        c.category_name,
        s.supplier_name

    FROM products p

    LEFT JOIN categories c
        ON c.id = p.category_id

    LEFT JOIN suppliers s
        ON s.id = p.supplier_id

    ORDER BY
        p.quantity ASC,
        p.product_name ASC
";


$result = mysqli_query($conn, $sql);


/*
|--------------------------------------------------------------------------
| Query Error Handling
|--------------------------------------------------------------------------
*/

if (!$result) {

    die(
        "Stock Report Error: " .
        htmlspecialchars(mysqli_error($conn))
    );
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$total_products = 0;
$total_units = 0;
$low_stock = 0;
$out_of_stock = 0;


while ($row = mysqli_fetch_assoc($result)) {

    $total_products++;

    $quantity = (int) $row['quantity'];

    $total_units += $quantity;


    if ($quantity <= 5 && $quantity > 0) {

        $low_stock++;

    }


    if ($quantity <= 0) {

        $out_of_stock++;

    }

}


/*
|--------------------------------------------------------------------------
| Re-run Query For Table
|--------------------------------------------------------------------------
*/

$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Unable to reload Stock Report: " .
        htmlspecialchars(mysqli_error($conn))
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
Stock Report - R&R Collection
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
            Stock Report
        </h1>

        <p>
            Current inventory and stock levels
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
        📦
    </div>

    <div>

        <span>
            Products
        </span>

        <h2>
            <?= number_format($total_products); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        📊
    </div>

    <div>

        <span>
            Total Units
        </span>

        <h2>
            <?= number_format($total_units); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        ⚠️
    </div>

    <div>

        <span>
            Low Stock
        </span>

        <h2>
            <?= number_format($low_stock); ?>
        </h2>

    </div>

</div>



<div class="report-card">

    <div class="card-icon">
        🚨
    </div>

    <div>

        <span>
            Out of Stock
        </span>

        <h2>
            <?= number_format($out_of_stock); ?>
        </h2>

    </div>

</div>


</div>



<!-- =====================================================
     STOCK TABLE
====================================================== -->

<div class="recent-sales">


<div class="section-header">


<div>

    <h2>
        Inventory Status
    </h2>

    <p>
        Current stock position
    </p>

</div>


<!-- =====================================================
     EXCEL EXPORT
====================================================== -->

<div>

<a
    href="export_stock_excel.php"
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
Product
</th>

<th>
Category
</th>

<th>
Supplier
</th>

<th>
Stock
</th>

<th>
Selling Price
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
    $row['product_name']
); ?>

</strong>

</td>


<td>

<?= htmlspecialchars(
    $row['category_name'] ?? 'N/A'
); ?>

</td>


<td>

<?= htmlspecialchars(
    $row['supplier_name'] ?? 'N/A'
); ?>

</td>


<td>

<strong>

<?= number_format(
    $row['quantity']
); ?>

</strong>

</td>


<td>

KSh <?= number_format(
    $row['selling_price'],
    2
); ?>

</td>


<td>


<?php if ($row['quantity'] <= 0): ?>

<span class="balance-warning">
    OUT OF STOCK
</span>


<?php elseif ($row['quantity'] <= 5): ?>

<span class="balance-warning">
    LOW STOCK
</span>


<?php else: ?>

<span class="balance-clear">
    IN STOCK
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

No products found.

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
