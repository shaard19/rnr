<?php
require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('view_stock')) {
    die("Access Denied");
}

$sql = "
SELECT
    p.*,
    c.category_name,
    s.supplier_name
FROM products p
LEFT JOIN categories c
    ON p.category_id = c.id
LEFT JOIN suppliers s
    ON p.supplier_id = s.id
ORDER BY p.product_name ASC
";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Products | R&R Collection POS</title>

<link rel="stylesheet" href="../../assets/css/dashboard.css">
<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/forms.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main">

<?php include "../../includes/topbar.php"; ?>

<div class="container">
    <div class="page-title">

<h1>Products</h1>

<p>
Manage your inventory
</p>

</div>
<div style="margin-bottom:20px;">

<a href="add.php" class="btn">

<i class="fa-solid fa-plus"></i>

Add Product

</a>
<div class="panel">

<h3>

<i class="fa-solid fa-box-open"></i>

Product List

</h3>

<table>

<tr>

<th>Barcode</th>

<th>Product</th>

<th>Category</th>

<th>Supplier</th>

<th>Stock</th>

<th>Selling Price</th>

<th>Status</th>

<th>Action</th>

</tr>
<?php while($row = mysqli_fetch_assoc($result)) { ?>

<tr>

<td><?php echo htmlspecialchars($row['barcode']); ?></td>

<td><?php echo htmlspecialchars($row['product_name']); ?></td>

<td><?php echo htmlspecialchars($row['category_name']); ?></td>

<td><?php echo htmlspecialchars($row['supplier_name']); ?></td>

<td><?php echo $row['quantity']; ?></td>

<td>

KSh <?php echo number_format($row['selling_price'],2); ?>

</td>
<td>

<?php

if($row['quantity']==0){

echo "<span style='color:red;font-weight:bold;'>Out of Stock</span>";

}
elseif($row['quantity']<=$row['reorder_level']){

echo "<span style='color:orange;font-weight:bold;'>Low Stock</span>";

}
else{

echo "<span style='color:green;font-weight:bold;'>In Stock</span>";

}

?>

</td>
<td>

<a href="edit.php?id=<?php echo $row['id']; ?>">

<i class="fa-solid fa-pen"></i>

</a>

&nbsp;

<a href="delete.php?id=<?php echo $row['id']; ?>">

<i class="fa-solid fa-trash"></i>

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