<?php
require_once "../config/session.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

// Total Products
$productQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM products");
$product = mysqli_fetch_assoc($productQuery);
$totalProducts = $product['total'] ?? 0;

// Total Categories
$categoryQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM categories");
$category = mysqli_fetch_assoc($categoryQuery);
$totalCategories = $category['total'] ?? 0;

// Total Customers
$customerQuery = mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers");
$customer = mysqli_fetch_assoc($customerQuery);
$totalCustomers = $customer['total'] ?? 0;

// Today's Sales
$salesQuery = mysqli_query(
    $conn,
    "SELECT IFNULL(SUM(total),0) AS total
     FROM sales
     WHERE DATE(sale_date)=CURDATE()"
);

$sales = mysqli_fetch_assoc($salesQuery);
$todaySales = $sales['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard | R&R Collection POS</title>

<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
<link rel="stylesheet" href="../assets/css/forms.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php include "../includes/topbar.php"; ?>

<div class="container">

<div class="page-title">

<h1>Dashboard</h1>

<p>Welcome back,
<strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong>

</p>

</div>

<div class="cards">

<div class="card">

<div>

<small>Total Products</small>

<h2><?php echo $totalProducts; ?></h2>

</div>

<div class="card-icon blue">

<i class="fa-solid fa-box-open"></i>

</div>

</div>

<div class="card">

<div>

<small>Categories</small>

<h2><?php echo $totalCategories; ?></h2>

</div>

<div class="card-icon green">

<i class="fa-solid fa-layer-group"></i>

</div>

</div>

<div class="card">

<div>

<small>Customers</small>

<h2><?php echo $totalCustomers; ?></h2>

</div>

<div class="card-icon orange">

<i class="fa-solid fa-users"></i>

</div>

</div>

<div class="card">

<div>

<small>Today's Sales</small>

<h2>KSh <?php echo number_format($todaySales,2); ?></h2>

</div>

<div class="card-icon red">

<i class="fa-solid fa-sack-dollar"></i>

</div>

</div>

</div>

<div class="panel">

<h3>

<i class="fa-solid fa-bolt"></i>

Quick Actions

</h3>

<table>

<tr>

<th>Module</th>

<th>Action</th>

</tr>

<tr>

<td>Categories</td>

<td>

<a href="categories.php" class="btn">

Manage Categories

</a>

</td>

</tr>

<tr>

<td>Products</td>

<td>

<a href="products.php" class="btn">

Manage Products

</a>

</td>

</tr>

<tr>

<td>Customers</td>

<td>

<a href="customers.php" class="btn">

Manage Customers

</a>

</td>

</tr>

<tr>

<td>Sales</td>

<td>

Coming Soon

</td>

</tr>

</table>

</div>

</div>

</div>

</body>

</html>