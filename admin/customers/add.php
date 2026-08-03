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

    $customer_code = trim($_POST['customer_code'] ?? '');
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $status        = $_POST['status'] ?? 'Active';

    $premium_status      = $_POST['premium_status'] ?? 'Regular';
    $sms_enabled         = isset($_POST['sms_enabled']) ? 1 : 0;
    $new_arrival_alerts  = isset($_POST['new_arrival_alerts']) ? 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | Validate Notification Settings
    |--------------------------------------------------------------------------
    */

    if (!in_array($premium_status, ['Regular', 'Premium'], true)) {
        die("Invalid customer type.");
    }

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
        mysqli_stmt_close($check_stmt);
        die("Customer code already exists.");
    }

    mysqli_stmt_close($check_stmt);

    /*
    |--------------------------------------------------------------------------
    | Insert Customer
    |--------------------------------------------------------------------------
    */

    $sql = "INSERT INTO customers
    (
        customer_code,
        customer_name,
        phone,
        email,
        address,
        status,
        premium_status,
        sms_enabled,
        new_arrival_alerts
    )
    VALUES
    (?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Database error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sssssssii",
        $customer_code,
        $customer_name,
        $phone,
        $email,
        $address,
        $status,
        $premium_status,
        $sms_enabled,
        $new_arrival_alerts
    );

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

<style>

.notification-box {
    margin-top: 24px;
    padding: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    background: #f8fafc;
}

.notification-box h3 {
    margin: 0 0 6px;
    color: #172033;
    font-size: 16px;
}

.notification-box p {
    margin: 0 0 18px;
    color: #667085;
    font-size: 13px;
}

.notification-option {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    margin-bottom: 14px;
}

.notification-option input {
    width: 17px;
    height: 17px;
    margin-top: 2px;
}

.notification-option label {
    font-size: 14px;
    color: #344054;
    cursor: pointer;
}

.notification-option small {
    display: block;
    color: #667085;
    margin-top: 3px;
}

</style>

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
name="phone"
placeholder="e.g. 0712345678">

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

<div class="customer-row">

<div class="form-group">

<label>
Customer Type
</label>

<select name="premium_status">

<option value="Regular">
Regular
</option>

<option value="Premium">
Premium
</option>

</select>

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

</div>

<div class="notification-box">

<h3>
<i class="fa-solid fa-bell"></i>
Customer Notifications
</h3>

<p>
Choose which notifications this customer is allowed to receive.
</p>

<div class="notification-option">

<input
type="checkbox"
id="sms_enabled"
name="sms_enabled"
value="1"
checked>

<div>

<label for="sms_enabled">
Allow SMS notifications
</label>

<small>
Allows R&amp;R to send important customer notifications by SMS.
</small>

</div>

</div>

<div class="notification-option">

<input
type="checkbox"
id="new_arrival_alerts"
name="new_arrival_alerts"
value="1">

<div>

<label for="new_arrival_alerts">
Receive new-arrival alerts
</label>

<small>
Premium customers can receive notifications when new products arrive.
</small>

</div>

</div>

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