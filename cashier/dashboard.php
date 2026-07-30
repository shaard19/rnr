<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/permissions.php";


/*
|--------------------------------------------------------------------------
| CASHIER ACCESS
|--------------------------------------------------------------------------
*/

if (!hasPermission('make_sale')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| TOTAL PRODUCTS
|--------------------------------------------------------------------------
*/

$productQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'Active'"
);

if (!$productQuery) {
    die("Product Query Error: " . mysqli_error($conn));
}

$product = mysqli_fetch_assoc($productQuery);

$totalProducts = $product['total'] ?? 0;


/*
|--------------------------------------------------------------------------
| TOTAL CUSTOMERS
|--------------------------------------------------------------------------
*/

$customerQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM customers"
);

if (!$customerQuery) {
    die("Customer Query Error: " . mysqli_error($conn));
}

$customer = mysqli_fetch_assoc($customerQuery);

$totalCustomers = $customer['total'] ?? 0;


/*
|--------------------------------------------------------------------------
| TODAY'S SALES
|--------------------------------------------------------------------------
*/

$salesQuery = mysqli_query(
    $conn,
    "SELECT IFNULL(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()"
);

if (!$salesQuery) {
    die("Sales Query Error: " . mysqli_error($conn));
}

$sales = mysqli_fetch_assoc($salesQuery);

$todaySales = $sales['total'] ?? 0;


/*
|--------------------------------------------------------------------------
| TODAY'S TRANSACTIONS
|--------------------------------------------------------------------------
*/

$transactionQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()"
);

if (!$transactionQuery) {
    die(
        "Transaction Query Error: " .
        mysqli_error($conn)
    );
}

$transactions =
    mysqli_fetch_assoc($transactionQuery);

$todayTransactions =
    $transactions['total'] ?? 0;

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
Cashier Dashboard | R&R Collection POS
</title>


<link
    rel="stylesheet"
    href="../assets/css/dashboard.css"
>

<link
    rel="stylesheet"
    href="../assets/css/sidebar.css"
>

<link
    rel="stylesheet"
    href="../assets/css/forms.css"
>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>


</head>


<body>


<?php include "../includes/sidebar.php"; ?>


<div class="main">


<?php include "../includes/topbar.php"; ?>


<div class="container">


<!-- =====================================================
     PAGE TITLE
====================================================== -->

<div class="page-title">

<h1>

<i class="fa-solid fa-cash-register"></i>

Cashier Dashboard

</h1>


<p>

Welcome back,

<strong>

<?= htmlspecialchars(
    $_SESSION['fullname']
); ?>

</strong>

</p>

</div>


<!-- =====================================================
     DASHBOARD CARDS
====================================================== -->

<div class="cards">


<!-- PRODUCTS -->

<div class="card">

<div>

<small>
Products
</small>

<h2>
<?= (int)$totalProducts; ?>
</h2>

</div>


<div class="card-icon blue">

<i class="fa-solid fa-box-open"></i>

</div>

</div>


<!-- CUSTOMERS -->

<div class="card">

<div>

<small>
Customers
</small>

<h2>
<?= (int)$totalCustomers; ?>
</h2>

</div>


<div class="card-icon green">

<i class="fa-solid fa-users"></i>

</div>

</div>


<!-- TRANSACTIONS -->

<div class="card">

<div>

<small>
Today's Transactions
</small>

<h2>
<?= (int)$todayTransactions; ?>
</h2>

</div>


<div class="card-icon orange">

<i class="fa-solid fa-receipt"></i>

</div>

</div>


<!-- SALES -->

<div class="card">

<div>

<small>
Today's Sales
</small>

<h2>

KSh
<?= number_format(
    (float)$todaySales,
    2
); ?>

</h2>

</div>


<div class="card-icon red">

<i class="fa-solid fa-sack-dollar"></i>

</div>

</div>


</div>


<!-- =====================================================
     QUICK ACTIONS
====================================================== -->

<div class="panel">


<h3>

<i class="fa-solid fa-bolt"></i>

Quick Actions

</h3>


<table>


<thead>

<tr>

<th>
Module
</th>

<th>
Action
</th>

</tr>

</thead>


<tbody>


<!-- =================================================
     NEW SALE
================================================== -->

<tr>

<td>

New Sale

</td>


<td>

<a
    href="../admin/sales/index.php"
    class="btn"
>

<i class="fa-solid fa-cart-shopping"></i>

New Sale

</a>

</td>

</tr>


<!-- =================================================
     CUSTOMERS
================================================== -->

<tr>

<td>

Customers

</td>


<td>

<a
    href="../admin/customers/index.php"
    class="btn"
>

<i class="fa-solid fa-users"></i>

Customers

</a>

</td>

</tr>


<!-- =================================================
     PRODUCTS
================================================== -->

<tr>

<td>

Products

</td>


<td>

<a
    href="../admin/products/index.php"
    class="btn"
>

<i class="fa-solid fa-box-open"></i>

Find Product

</a>

</td>

</tr>


<!-- =================================================
     SALES HISTORY
================================================== -->

<tr>

<td>

Today's Sales

</td>


<td>

<a
    href="../admin/sales/index.php"
    class="btn"
>

<i class="fa-solid fa-receipt"></i>

View Sales

</a>

</td>

</tr>


</tbody>


</table>


</div>


</div>


</div>


</body>

</html>