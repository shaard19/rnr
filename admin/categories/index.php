<?php

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('view_categories')) {
    die("Access Denied");
}

$query = mysqli_query($conn, "SELECT * FROM categories ORDER BY category_name ASC");

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Categories | R&R Collection POS</title>

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

<div class="page-header">

    <div class="left">

        <h1>Categories</h1>

        <p>Manage product categories</p>

    </div>

</div>

<div class="panel">

<?php if(hasPermission('add_category')) { ?>

<a href="add.php" class="btn">
    <i class="fa fa-plus"></i>
    Add Category
</a>

<?php } ?>

<br><br>

<table border="1" width="100%" cellpadding="10">

<tr>

<th>#</th>
<th>Category</th>
<th>Description</th>
<th>Status</th>

<?php if(hasPermission('edit_category') || hasPermission('delete_category')) { ?>

<th width="180">Actions</th>

<?php } ?>

</tr>

<?php

$count = 1;

while($row = mysqli_fetch_assoc($query)){

?>

<tr>

<td><?php echo $count++; ?></td>

<td><?php echo htmlspecialchars($row['category_name']); ?></td>

<td><?php echo htmlspecialchars($row['description']); ?></td>

<td>

<?php if($row['status'] == 'Active'){ ?>

    <span class="badge active">Active</span>

<?php } else { ?>

    <span class="badge inactive">Inactive</span>

<?php } ?>

</td>

<?php if(hasPermission('edit_category') || hasPermission('delete_category')) { ?>

<td class="actions">

<?php if(hasPermission('edit_category')) { ?>

<a href="edit.php?id=<?php echo $row['id']; ?>" class="action edit">

<i class="fa fa-edit"></i>

Edit

</a>

<?php } ?>

<?php if(hasPermission('delete_category')) { ?>

<a
href="delete.php?id=<?php echo $row['id']; ?>"
class="action delete"
onclick="return confirm('Are you sure you want to delete this category?');">

<i class="fa fa-trash"></i>

Delete

</a>

<?php } ?>

</td>

<?php } ?>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</body>

</html>