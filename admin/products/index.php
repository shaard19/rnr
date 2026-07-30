<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


/*
|--------------------------------------------------------------------------
| PERMISSION CHECK
|--------------------------------------------------------------------------
*/

if (!hasPermission('view_stock')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| LOAD PRODUCTS
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        p.product_code,
        p.product_name,
        p.category_id,
        p.supplier_id,
        p.unit,
        p.buying_price,
        p.selling_price,
        p.quantity,
        p.reorder_level,
        p.status,
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


if (!$result) {
    die("Product Query Error: " . mysqli_error($conn));
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0"

>

<title>Products | R&R Collection POS</title>

<link
    rel="stylesheet"
    href="../../assets/css/dashboard.css"
>

<link
    rel="stylesheet"
    href="../../assets/css/sidebar.css"
>

<link
    rel="stylesheet"
    href="../../assets/css/forms.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

<style>

/* PRODUCT ACTION BUTTONS */

.product-action {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 12px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    margin-right: 5px;
    cursor: pointer;
}

.product-edit {
    background: #2563eb;
    color: #ffffff !important;
}

.product-edit:hover {
    background: #1d4ed8;
}

.product-delete {
    background: #dc2626;
    color: #ffffff !important;
}

.product-delete:hover {
    background: #b91c1c;
}

.product-inactive {
    background: #6b7280;
    color: #ffffff;
    padding: 5px 9px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.product-active {
    background: #16a34a;
    color: #ffffff;
    padding: 5px 9px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.stock-out {
    color: #dc2626;
    font-weight: bold;
}

.stock-low {
    color: #d97706;
    font-weight: bold;
}

.stock-good {
    color: #16a34a;
    font-weight: bold;
}

</style>

</head>

<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main">

<?php include "../../includes/topbar.php"; ?>

<div class="container">

<!-- PAGE TITLE -->

<div class="page-title">

<h1>

<i class="fa-solid fa-box"></i>

Products

</h1>

<p>
Manage your inventory
</p>

</div>

<!-- ADD PRODUCT -->

<div style="margin-bottom:20px;">

<a
href="add.php"
class="btn"

>

<i class="fa-solid fa-plus"></i>

Add Product

</a>

</div>

<!-- PRODUCT PANEL -->

<div class="panel">

<h3>

<i class="fa-solid fa-box-open"></i>

Product List

</h3>

<div style="overflow-x:auto;">

<table>

<thead>

<tr>

<th>Code</th>

<th>Product</th>

<th>Category</th>

<th>Supplier</th>

<th>Unit</th>

<th>Stock</th>

<th>Selling Price</th>

<th>Product Status</th>

<th>Stock Status</th>

<th>Action</th>

</tr>

</thead>

<tbody>

<?php if (mysqli_num_rows($result) > 0): ?>

<?php while ($row = mysqli_fetch_assoc($result)): ?>

<tr>

<!-- PRODUCT CODE -->

<td>

<?= htmlspecialchars($row['product_code']); ?>

</td>

<!-- PRODUCT NAME -->

<td>

<?= htmlspecialchars($row['product_name']); ?>

</td>

<!-- CATEGORY -->

<td>

<?= htmlspecialchars(
    $row['category_name'] ?? 'N/A'
); ?>

</td>

<!-- SUPPLIER -->

<td>

<?= htmlspecialchars(
    $row['supplier_name'] ?? 'N/A'
); ?>

</td>

<!-- UNIT -->

<td>

<?= htmlspecialchars(
    $row['unit'] ?? '-'
); ?>

</td>

<!-- STOCK -->

<td>

<?= (int)$row['quantity']; ?>

</td>

<!-- SELLING PRICE -->

<td>

KSh

<?= number_format(
    (float)$row['selling_price'],
    2
); ?>

</td>

<!-- PRODUCT STATUS -->

<td>

<?php if ($row['status'] === 'Active'): ?>

<span class="product-active">
Active
</span>

<?php else: ?>

<span class="product-inactive">
Inactive
</span>

<?php endif; ?>

</td>

<!-- STOCK STATUS -->

<td>

<?php

if ((int)$row['quantity'] === 0) {

    echo '<span class="stock-out">Out of Stock</span>';

} elseif (
    (int)$row['quantity'] <=
    (int)$row['reorder_level']
) {

    echo '<span class="stock-low">Low Stock</span>';

} else {

    echo '<span class="stock-good">In Stock</span>';

}

?>

</td>

<!-- ACTIONS -->

<td style="white-space: nowrap;">

<!-- EDIT -->

<a
href="edit.php?id=<?= (int)$row['id']; ?>"
class="product-action product-edit"
title="Edit Product"

>

<i class="fa-solid fa-pen-to-square"></i>

Edit

</a>

<!-- DELETE / DEACTIVATE -->

<a
href="delete.php?id=<?= (int)$row['id']; ?>"
class="product-action product-delete"
title="Delete or Deactivate Product"
onclick="return confirm(
'Are you sure you want to remove this product?\n\n' +
'If this product has sales history, it will be deactivated instead.'
);"

>

<i class="fa-solid fa-trash"></i>

Delete

</a>

</td>

</tr>

<?php endwhile; ?>

<?php else: ?>

<tr>

<td
    colspan="10"
    style="text-align:center;padding:30px;"
>

<i class="fa-solid fa-box-open"></i>

No products found.

</td>

</tr>

<?php endif; ?>

</tbody>

</table>

</div>

</div>

</div>

</div>

</body>

</html>
