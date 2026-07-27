<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('edit_product')) {
    die("Access Denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int)$_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Categories
|--------------------------------------------------------------------------
*/

$categories = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     ORDER BY category_name ASC"
);

if (!$categories) {
    die("Category Error: " . mysqli_error($conn));
}

/*
|--------------------------------------------------------------------------
| Load Product
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT * FROM products WHERE id=?"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$product = mysqli_fetch_assoc($result);

if (!$product) {
    die("Product not found.");
}

/*
|--------------------------------------------------------------------------
| Update Product
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {

    $category_id   = (int)$_POST['category_id'];

    $supplier_id   =
        !empty($_POST['supplier_id'])
        ? (int)$_POST['supplier_id']
        : NULL;

    $barcode       = trim($_POST['barcode']);
    $product_name  = trim($_POST['product_name']);

    $buying_price  = (float)$_POST['buying_price'];
    $selling_price = (float)$_POST['selling_price'];

    $unit          = trim($_POST['unit']);

    $quantity      = (int)$_POST['quantity'];

    $reorder_level = (int)$_POST['reorder_level'];

    $status        = $_POST['status'];

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE products
        SET
            category_id=?,
            supplier_id=?,
            barcode=?,
            product_name=?,
            buying_price=?,
            selling_price=?,
            unit=?,
            quantity=?,
            reorder_level=?,
            status=?
        WHERE id=?"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "iissddsissi",
        $category_id,
        $supplier_id,
        $barcode,
        $product_name,
        $buying_price,
        $selling_price,
        $unit,
        $quantity,
        $reorder_level,
        $status,
        $id
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

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Product | R&R Collection POS</title>

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

<h1>Edit Product</h1>

<p>Update product information.</p>

</div>

<div class="panel">

<form method="POST">

<div class="form-group">

<label>Category</label>

<select name="category_id" required>

<?php while($category = mysqli_fetch_assoc($categories)) { ?>

<option
value="<?php echo $category['id']; ?>"
<?php if($category['id'] == $product['category_id']) echo "selected"; ?>>

<?php echo htmlspecialchars($category['category_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>Supplier (Optional)</label>

<select name="supplier_id">

<option value="">No Supplier</option>
</select>

</div>

<div class="form-group">

<label>Barcode</label>

<input
type="text"
name="barcode"
value="<?php echo htmlspecialchars($product['barcode']); ?>"
required>

</div>

<div class="form-group">

<label>Product Name</label>

<input
type="text"
name="product_name"
value="<?php echo htmlspecialchars($product['product_name']); ?>"
required>

</div>

<div class="form-group">

<label>Buying Price</label>

<input
type="number"
step="0.01"
name="buying_price"
value="<?php echo $product['buying_price']; ?>"
required>

</div>

<div class="form-group">

<label>Selling Price</label>

<input
type="number"
step="0.01"
name="selling_price"
value="<?php echo $product['selling_price']; ?>"
required>

</div>

<div class="form-group">

<label>Unit</label>

<input
type="text"
name="unit"
value="<?php echo htmlspecialchars($product['unit']); ?>"
required>

</div>

<div class="form-group">

<label>Opening Stock</label>

<input
type="number"
name="quantity"
value="<?php echo $product['quantity']; ?>"
required>

</div>

<div class="form-group">

<label>Reorder Level</label>

<input
type="number"
name="reorder_level"
value="<?php echo $product['reorder_level']; ?>"
required>

</div>

<div class="form-group">

<label>Status</label>

<select name="status">

<option
value="Available"
<?php if($product['status']=="Available") echo "selected"; ?>>

Available

</option>

<option
value="Out of Stock"
<?php if($product['status']=="Out of Stock") echo "selected"; ?>>

Out of Stock

</option>

</select>

</div>

<br>

<button
type="submit"
name="update"
class="btn">

<i class="fa-solid fa-floppy-disk"></i>

Update Product

</button>

<a
href="index.php"
class="btn">

<i class="fa-solid fa-arrow-left"></i>

Back

</a>

</form>

</div>

</div>

</div>

</body>

</html>