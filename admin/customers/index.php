<?php
require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

// Uncomment after customer permissions are created
if (!hasPermission('view_customer')) {
    die("Access Denied");
}


$result = mysqli_query($conn, "SELECT * FROM customers ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customers | R&R Collection POS</title>

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
<i class="fas fa-users"></i>
Customers
</h1>

<p>Manage registered customers.</p>

</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

<a href="add.php" class="btn">
<i class="fas fa-user-plus"></i>
Add Customer
</a>

</div>

<div class="panel">

<table>

<thead>

<tr>

<th>#</th>
<th>Code</th>
<th>Name</th>
<th>Phone</th>
<th>Credit Balance</th>
<th>Status</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($result) > 0){

$count = 1;

while($row = mysqli_fetch_assoc($result)){

?>

<tr>

<td><?= $count++; ?></td>

<td><?= htmlspecialchars($row['customer_code']); ?></td>

<td><?= htmlspecialchars($row['customer_name']); ?></td>

<td><?= htmlspecialchars($row['phone']); ?></td>

<td>KES <?= number_format($row['credit_balance'],2); ?></td>

<td>

<?php if($row['status']=="Active"){ ?>

<span style="color:#198754;font-weight:bold;">
Active
</span>

<?php } else { ?>

<span style="color:#dc3545;font-weight:bold;">
Inactive
</span>

<?php } ?>

</td>

<td>

<div class="action-group">

<a
href="view.php?id=<?= $row['id']; ?>"
class="action"
style="background:#0d6efd;color:#fff;">

<i class="fas fa-eye"></i>

View

</a>

<a
href="edit.php?id=<?= $row['id']; ?>"
class="action edit">

<i class="fas fa-edit"></i>

Edit

</a>

<a
href="delete.php?id=<?= $row['id']; ?>"
class="action delete"
onclick="return confirm('Are you sure you want to remove this customer?');">

<i class="fas fa-trash"></i>

Delete

</a>

</div>

</td>

</tr>

<?php

}

}else{

?>

<tr>

<td colspan="7" style="text-align:center;padding:40px;">

<i class="fas fa-users"
style="font-size:45px;color:#bdbdbd;"></i>

<br><br>

No customers found.

<br><br>

<a href="add.php" class="btn">

<i class="fas fa-user-plus"></i>

Add First Customer

</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

</div>

</body>

</html>