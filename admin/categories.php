<?php
require_once "../config/session.php";
require_once "../config/database.php";

// Fetch all categories
$query = mysqli_query($conn, "SELECT * FROM categories ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Categories | R&R Collection POS</title>

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

<h1>Categories</h1>

<p>Manage product categories</p>

</div>

<div class="panel">

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">

<h3>
<i class="fa-solid fa-layer-group"></i>
Category List
</h3>

<a href="add_category.php" class="btn">
<i class="fa-solid fa-plus"></i>
Add Category
</a>

</div>

<table>

<tr>

<th>ID</th>

<th>Category Name</th>

<th>Description</th>

<th>Created</th>

<th>Action</th>

</tr>

<?php while($row = mysqli_fetch_assoc($query)){ ?>

<tr>

<td><?= $row['id']; ?></td>

<td><?= htmlspecialchars($row['category_name']); ?></td>

<td><?= htmlspecialchars($row['description']); ?></td>

<td><?= $row['created_at']; ?></td>

<td>

<a href="edit_category.php?id=<?= $row['id']; ?>" class="btn">

Edit

</a>

<a href="delete_category.php?id=<?= $row['id']; ?>"
class="btn"
style="background:#DC2626;margin-left:8px;">

Delete

</a>

</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</body>

</html>