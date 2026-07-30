<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('add_customer')) {
    die("Access Denied");
}

if (isset($_POST['save'])) {

    $customer_code = trim($_POST['customer_code']);
    $customer_name = trim($_POST['customer_name']);
    $phone         = trim($_POST['phone']);
    $email         = trim($_POST['email']);
    $address       = trim($_POST['address']);
    $status        = $_POST['status'];

    /*
    |--------------------------------------------------------------------------
    | Check Duplicate Customer Code
    |--------------------------------------------------------------------------
    */

    $check_stmt = mysqli_prepare(
        $conn,
        "SELECT id FROM customers WHERE customer_code = ?"
    );

    if (!$check_stmt) {
        die("Database error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $check_stmt,
        "s",
        $customer_code
    );

    mysqli_stmt_execute($check_stmt);

    $check_result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($check_result) > 0) {
        die("Customer code already exists.");
    }

    mysqli_stmt_close($check_stmt);


    /*
    |--------------------------------------------------------------------------
    | Insert Customer
    |--------------------------------------------------------------------------
    |
    | credit_balance is automatically set to 0.00 by the database.
    |
    */

    $sql = "INSERT INTO customers
    (
        customer_code,
        customer_name,
        phone,
        email,
        address,
        status
    )
    VALUES
    (?, ?, ?, ?, ?, ?)";


    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Database error: " . mysqli_error($conn));
    }


    mysqli_stmt_bind_param(
        $stmt,
        "ssssss",
        $customer_code,
        $customer_name,
        $phone,
        $email,
        $address,
        $status
    );


    /*
    |--------------------------------------------------------------------------
    | Save Customer
    |--------------------------------------------------------------------------
    */

    if (mysqli_stmt_execute($stmt)) {

        mysqli_stmt_close($stmt);

        header("Location: index.php");
        exit();

    } else {

        echo "Error saving customer: " . mysqli_stmt_error($stmt);

        mysqli_stmt_close($stmt);
    }
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Add Customer | R&R Collection POS</title>

<link rel="stylesheet"
href="../../assets/css/dashboard.css">

<link rel="stylesheet"
href="../../assets/css/sidebar.css">

<link rel="stylesheet"
href="../../assets/css/form.css?v=2">

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
<i class="fa-solid fa-user-plus"></i>
Add Customer
</h1>

<p>Register a new customer.</p>

</div>

<div class="customer-panel">

<form method="POST">

<div class="customer-row">

<div class="form-group">

<label>
Customer Code <span class="required">*</span>
</label>

<input
type="text"
name="customer_code"
required>

</div>

<div class="form-group">

<label>
Customer Name <span class="required">*</span>
</label>

<input
type="text"
name="customer_name"
required>

</div>

</div>

<div class="customer-row">

<div class="form-group">

<label>
Phone
</label>

<input
type="text"
name="phone">

</div>

<div class="form-group">

<label>
Email
</label>

<input
type="email"
name="email">

</div>

</div>

<div class="form-group">

<label>
Address
</label>

<textarea
name="address"
rows="4"></textarea>

</div>

<div class="form-group">

<label>
Status
</label>

<select name="status">

<option value="Active">
Active
</option>

<option value="Inactive">
Inactive
</option>

</select>

</div>

<div class="customer-buttons">

<button
type="submit"
name="save"
class="customer-btn customer-save">

<i class="fa-solid fa-floppy-disk"></i>

Save Customer

</button>

<a
href="index.php"
class="customer-btn customer-back">

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
