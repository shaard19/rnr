<?php
require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('add_product')) {
    die("Access Denied");
}

if (isset($_POST['save'])) {

    $supplier_name = trim($_POST['supplier_name']);
    $phone         = trim($_POST['phone']);
    $email         = trim($_POST['email']);
    $address       = trim($_POST['address']);

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO suppliers
        (
            supplier_name,
            phone,
            email,
            address
        )
        VALUES
        (?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "ssss",
        $supplier_name,
        $phone,
        $email,
        $address
    );

    mysqli_stmt_execute($stmt);

    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Supplier | R&R Collection POS</title>

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
<i class="fa-solid fa-truck"></i>
Add Supplier
</h1>

<p>Register a new supplier for your inventory.</p>

</div>

<div class="panel">

<form method="POST">

<div class="form-group">

<label>
<i class="fa-solid fa-building"></i>
Supplier Name
</label>

<input
type="text"
name="supplier_name"
placeholder="Enter supplier name"
required>

</div>

<div class="form-group">

<label>
<i class="fa-solid fa-phone"></i>
Phone Number
</label>

<input
type="text"
name="phone"
placeholder="e.g. 0712345678">

</div>

<div class="form-group">

<label>
<i class="fa-solid fa-envelope"></i>
Email Address
</label>

<input
type="email"
name="email"
placeholder="supplier@email.com">

</div>

<div class="form-group full">

<label>
<i class="fa-solid fa-location-dot"></i>
Physical Address
</label>

<textarea
name="address"
placeholder="Enter supplier address"></textarea>

</div>

<div class="form-actions">

<button
type="submit"
name="save"
class="btn">

<i class="fa-solid fa-floppy-disk"></i>

Save Supplier

</button>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</div>

</form>

</div>

</div>

</div>

</body>

</html>