<?php

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


if (!hasPermission('edit_supplier')) {
    die("Access Denied");
}


if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}


$id = $_GET['id'];


// Fetch supplier data

$stmt = mysqli_prepare($conn, "SELECT * FROM suppliers WHERE id=?");

mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$supplier = mysqli_fetch_assoc($result);


if (!$supplier) {
    die("Supplier not found");
}



// Update supplier

if(isset($_POST['update'])){


    $supplier_name = trim($_POST['supplier_name']);
    $phone         = trim($_POST['phone']);
    $email         = trim($_POST['email']);
    $address       = trim($_POST['address']);


    $stmt = mysqli_prepare(
        $conn,
        "UPDATE suppliers SET 
        supplier_name=?,
        phone=?,
        email=?,
        address=?
        WHERE id=?"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "ssssi",
        $supplier_name,
        $phone,
        $email,
        $address,
        $id
    );


    mysqli_stmt_execute($stmt);


    header("Location:index.php");
    exit();

}


?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Supplier | R&R Collection POS</title>


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

<h1>Edit Supplier</h1>

<p>Update supplier details</p>

</div>



<div class="panel">


<form method="POST">


<div class="form-group">

<label>Supplier Name</label>

<input 
type="text"
name="supplier_name"
value="<?php echo $supplier['supplier_name']; ?>"
required>

</div>



<div class="form-group">

<label>Phone</label>

<input 
type="text"
name="phone"
value="<?php echo $supplier['phone']; ?>">

</div>



<div class="form-group">

<label>Email</label>

<input 
type="email"
name="email"
value="<?php echo $supplier['email']; ?>">

</div>



<div class="form-group">

<label>Address</label>

<textarea name="address"><?php echo $supplier['address']; ?></textarea>

</div>



<button 
type="submit"
name="update"
class="btn">

<i class="fa fa-save"></i>

Update Supplier

</button>


<a href="index.php" class="btn">

<i class="fa fa-arrow-left"></i>

Back

</a>


</form>


</div>


</div>


</div>


</body>

</html>