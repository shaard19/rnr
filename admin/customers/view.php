<?php
require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('view_customer')) {
    die("Access Denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid customer.");
}

$id = (int)$_GET['id'];

$stmt = mysqli_prepare($conn, "SELECT * FROM customers WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Customer not found.");
}

$customer = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customer Profile | R&R Collection POS</title>

<link rel="stylesheet" href="../../assets/css/dashboard.css">
<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/form.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main">

<?php include "../../includes/topbar.php"; ?>

<div class="container">

<div class="page-title">

<h1>
<i class="fas fa-user-circle"></i>
Customer Profile
</h1>

<p>View customer information.</p>

</div>


<div class="customer-profile">

<div class="profile-header">

<div class="avatar">

<i class="fas fa-user"></i>

</div>

<div>

<h2><?= htmlspecialchars($customer['customer_name']); ?></h2>

<p><?= htmlspecialchars($customer['customer_code']); ?></p>

</div>

</div>


<div class="profile-grid">

<div class="profile-card">

<h3><i class="fas fa-id-card"></i> Basic Information</h3>

<table class="profile-table">

<tr>
<td>Customer Code</td>
<td><?= htmlspecialchars($customer['customer_code']); ?></td>
</tr>

<tr>
<td>Name</td>
<td><?= htmlspecialchars($customer['customer_name']); ?></td>
</tr>

<tr>
<td>Phone</td>
<td><?= htmlspecialchars($customer['phone']); ?></td>
</tr>

<tr>
<td>Email</td>
<td><?= htmlspecialchars($customer['email']); ?></td>
</tr>

<tr>
<td>Address</td>
<td><?= nl2br(htmlspecialchars($customer['address'])); ?></td>
</tr>

</table>

</div>


<div class="profile-card">

<h3><i class="fas fa-wallet"></i> Account Information</h3>

<table class="profile-table">

<tr>
<td>Credit Limit</td>
<td>KES <?= number_format($customer['credit_limit'],2); ?></td>
</tr>

<tr>
<td>Credit Balance</td>
<td>KES <?= number_format($customer['credit_balance'],2); ?></td>
</tr>

<tr>
<td>Status</td>

<td>

<?php if($customer['status']=="Active"){ ?>

<span class="badge-active">

Active

</span>

<?php } else { ?>

<span class="badge-inactive">

Inactive

</span>

<?php } ?>

</td>

</tr>

</table>

</div>

</div>


<div class="customer-buttons">

<a href="edit.php?id=<?= $customer['id']; ?>" class="customer-btn customer-save">

<i class="fas fa-edit"></i>

Edit Customer

</a>

<a href="index.php" class="customer-btn customer-back">

<i class="fas fa-arrow-left"></i>

Back

</a>

</div>

</div>

</div>

</div>

</body>

</html>