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
    $credit_limit  = (float)$_POST['credit_limit'];
    $status        = $_POST['status'];


    // Check duplicate customer code
    $check = mysqli_query(
        $conn,
        "SELECT id FROM customers WHERE customer_code='$customer_code'"
    );


    if (mysqli_num_rows($check) > 0) {
        die("Customer code already exists.");
    }


    $sql = "INSERT INTO customers
    (
        customer_code,
        customer_name,
        phone,
        email,
        address,
        credit_limit,
        credit_balance,
        status
    )
    VALUES
    (?, ?, ?, ?, ?, ?, 0, ?)";


    $stmt = mysqli_prepare($conn, $sql);


    mysqli_stmt_bind_param(
        $stmt,
        "sssssds",
        $customer_code,
        $customer_name,
        $phone,
        $email,
        $address,
        $credit_limit,
        $status
    );


    if(mysqli_stmt_execute($stmt)){

        header("Location: index.php");
        exit();

    }else{

        echo "Error: ".mysqli_error($conn);

    }

}

?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Customer | R&R Collection POS</title>


<link rel="stylesheet" href="../../assets/css/dashboard.css">
<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/forms.css?v=2">


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





<div class="customer-row">


<div class="form-group">

<label>
Credit Limit
</label>

<input 
type="number"
step="0.01"
name="credit_limit"
value="0">

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






<div class="customer-buttons">


<button 
type="submit"
name="save"
class="customer-btn customer-save">

<i class="fa-solid fa-floppy-disk"></i>
Save Customer

</button>




<a href="index.php"
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