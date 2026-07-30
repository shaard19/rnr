<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/session.php";
require_once "../config/database.php";


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
     FROM products"
);

$product = mysqli_fetch_assoc($productQuery);

$totalProducts = (int)($product['total'] ?? 0);


/*
|--------------------------------------------------------------------------
| ACTIVE PRODUCTS
|--------------------------------------------------------------------------
*/

$activeProductQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'Active'"
);

$activeProduct = mysqli_fetch_assoc($activeProductQuery);

$activeProducts = (int)($activeProduct['total'] ?? 0);


/*
|--------------------------------------------------------------------------
| LOW STOCK PRODUCTS
|--------------------------------------------------------------------------
*/

$lowStockQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'Active'
     AND quantity > 0
     AND quantity <= reorder_level"
);

$lowStock = mysqli_fetch_assoc($lowStockQuery);

$lowStockProducts = (int)($lowStock['total'] ?? 0);


/*
|--------------------------------------------------------------------------
| TOTAL CATEGORIES
|--------------------------------------------------------------------------
*/

$categoryQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM categories"
);

$category = mysqli_fetch_assoc($categoryQuery);

$totalCategories = (int)($category['total'] ?? 0);


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

$customer = mysqli_fetch_assoc($customerQuery);

$totalCustomers = (int)($customer['total'] ?? 0);


/*
|--------------------------------------------------------------------------
| TODAY'S SALES
|--------------------------------------------------------------------------
*/

$salesQuery = mysqli_query(
    $conn,
    "SELECT
        IFNULL(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()"
);

$sales = mysqli_fetch_assoc($salesQuery);

$todaySales = (float)($sales['total'] ?? 0);


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

$transactions = mysqli_fetch_assoc($transactionQuery);

$todayTransactions = (int)($transactions['total'] ?? 0);

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
Dashboard | R&R Collection POS
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

<style>

/*
|--------------------------------------------------------------------------
| DASHBOARD STATUS
|--------------------------------------------------------------------------
*/

.dashboard-status {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 10px;
}


.dashboard-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 11px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}


.badge-success {
    background: #dcfce7;
    color: #166534;
}


.badge-warning {
    background: #fef3c7;
    color: #92400e;
}


.badge-danger {
    background: #fee2e2;
    color: #991b1b;
}


/*
|--------------------------------------------------------------------------
| QUICK ACTIONS
|--------------------------------------------------------------------------
*/

.quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 15px;
    margin-top: 20px;
}


.quick-action {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    text-decoration: none;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    background: #ffffff;
    transition: 0.2s ease;
}


.quick-action:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}


.quick-action i {
    font-size: 20px;
}


.quick-action strong {
    display: block;
}


.quick-action small {
    color: #6b7280;
}


/*
|--------------------------------------------------------------------------
| DASHBOARD RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 700px) {

    .cards {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php include "../includes/topbar.php"; ?>

<div class="container">

<!-- PAGE TITLE -->

<div class="page-title">

<h1>

<i class="fa-solid fa-gauge-high"></i>

Dashboard

</h1>

<p>

Welcome back, <strong>

<?= htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>

</strong>

</p>

<div class="dashboard-status">

<span class="dashboard-badge badge-success">

<i class="fa-solid fa-circle-check"></i>

System Online

</span>

<?php if ($lowStockProducts > 0): ?>

<span class="dashboard-badge badge-warning">

<i class="fa-solid fa-triangle-exclamation"></i>

<?= $lowStockProducts; ?>

Low Stock

</span>

<?php else: ?>

<span class="dashboard-badge badge-success">

<i class="fa-solid fa-boxes-stacked"></i>

Stock Levels Good

</span>

<?php endif; ?>

</div>

</div>

<!-- DASHBOARD CARDS -->

<div class="cards">

<!-- TOTAL PRODUCTS -->

<div class="card">

<div>

<small>Total Products</small>

<h2>
<?= $totalProducts; ?>
</h2>

</div>

<div class="card-icon blue">

<i class="fa-solid fa-box-open"></i>

</div>

</div>

<!-- ACTIVE PRODUCTS -->

<div class="card">

<div>

<small>Active Products</small>

<h2>
<?= $activeProducts; ?>
</h2>

</div>

<div class="card-icon green">

<i class="fa-solid fa-boxes-stacked"></i>

</div>

</div>

<!-- CUSTOMERS -->

<div class="card">

<div>

<small>Customers</small>

<h2>
<?= $totalCustomers; ?>
</h2>

</div>

<div class="card-icon orange">

<i class="fa-solid fa-users"></i>

</div>

</div>

<!-- CATEGORIES -->

<div class="card">

<div>

<small>Categories</small>

<h2>
<?= $totalCategories; ?>
</h2>

</div>

<div class="card-icon blue">

<i class="fa-solid fa-layer-group"></i>

</div>

</div>

<!-- LOW STOCK -->

<div class="card">

<div>

<small>Low Stock</small>

<h2>
<?= $lowStockProducts; ?>
</h2>

</div>

<div class="card-icon orange">

<i class="fa-solid fa-triangle-exclamation"></i>

</div>

</div>

<!-- TODAY SALES -->

<div class="card">

<div>

<small>Today's Sales</small>

<h2>

KSh

<?= number_format($todaySales, 2); ?>

</h2>

</div>

<div class="card-icon red">

<i class="fa-solid fa-sack-dollar"></i>

</div>

</div>

</div>

<!-- TODAY SUMMARY -->

<div class="panel">

<h3>

<i class="fa-solid fa-chart-simple"></i>

Today's Summary

</h3>

<table>

<tr>

<th>Metric</th>

<th>Value</th>

</tr>

<tr>

<td>
Sales Transactions
</td>

<td>

<strong>
<?= $todayTransactions; ?>
</strong>

</td>

</tr>

<tr>

<td>
Total Sales
</td>

<td>

<strong>

KSh

<?= number_format($todaySales, 2); ?>

</strong>

</td>

</tr>

<tr>

<td>
Low Stock Products
</td>

<td>

<strong>

<?= $lowStockProducts; ?>

</strong>

</td>

</tr>

</table>

</div>

<!-- QUICK ACTIONS -->

<div class="panel">

<h3>

<i class="fa-solid fa-bolt"></i>

Quick Actions

</h3>

<div class="quick-actions">

<a
href="categories/index.php"
class="quick-action"

>

<i class="fa-solid fa-layer-group"></i>

<div>

<strong>
Categories
</strong>

<small>
Manage categories
</small>

</div>

</a>

<a
href="products/index.php"
class="quick-action"

>

<i class="fa-solid fa-box-open"></i>

<div>

<strong>
Products
</strong>

<small>
Manage inventory
</small>

</div>

</a>

<a
href="customers/index.php"
class="quick-action"

>

<i class="fa-solid fa-users"></i>

<div>

<strong>
Customers
</strong>

<small>
Manage customers
</small>

</div>

</a>

<a
href="products/add.php"
class="quick-action"

>

<i class="fa-solid fa-plus"></i>

<div>

<strong>
Add Product
</strong>

<small>
Register new stock
</small>

</div>

</a>

<a
href="customers/add.php"
class="quick-action"

>

<i class="fa-solid fa-user-plus"></i>

<div>

<strong>
Add Customer
</strong>

<small>
Register customer
</small>

</div>

</a>

<div class="quick-action">

<i class="fa-solid fa-cart-shopping"></i>

<div>

<strong>
Sales
</strong>

<small>
Coming next
</small>

</div>

</div>

</div>

</div>

</div>

</div>

</body>

</html>
