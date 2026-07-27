<?php

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


if (!hasPermission('view_supplier')) {
 die("Access Denied");
}


$query = mysqli_query($conn, "SELECT * FROM suppliers ORDER BY id DESC");


?>


<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Suppliers | R&R Collection POS</title>


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

<h1>Suppliers</h1>

<p>Manage your suppliers</p>

</div>



<div class="panel">


<a href="add.php" class="btn">

<i class="fa fa-plus"></i>
Add Supplier

</a>


<br><br>



<table border="1" width="100%" cellpadding="10">

<tr>

<th>#</th>
<th>Name</th>
<th>Phone</th>
<th>Email</th>
<th>Address</th>
<th>Date Added</th>
<th>Actions</th>
</tr>


<?php

$count = 1;


while($row = mysqli_fetch_assoc($query)){

?>

<tr>

<td>
<?php echo $count++; ?>
</td>


<td>
<?php echo $row['supplier_name']; ?>
</td>


<td>
<?php echo $row['phone']; ?>
</td>


<td>
<?php echo $row['email']; ?>
</td>


<td>
<?php echo $row['address']; ?>
</td>


<td>
<?php echo $row['created_at']; ?>
</td>
<td>
    <div class="action-group">

        <a href="edit.php?id=<?php echo $row['id']; ?>" class="action edit">
            <i class="fa fa-edit"></i> Edit
        </a>

        <a href="delete.php?id=<?php echo $row['id']; ?>"
           class="action delete"
           onclick="return confirm('Are you sure you want to delete this supplier?');">
            <i class="fa fa-trash"></i> Delete
        </a>

    </div>
</td>
</tr>
<?php
}
?>
</table>
</div>
</div>
</div>
</body>
</html>