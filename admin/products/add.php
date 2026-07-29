<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";
require_once "../../config/product_code.php";

if (!hasPermission('add_product')) {
    die("Access Denied");
}

/*
|--------------------------------------------------------------------------
| LOAD CATEGORIES
|--------------------------------------------------------------------------
*/

$categories = mysqli_query(
    $conn,
    "SELECT
        c.id,
        c.category_name,
        d.department_name
     FROM categories c
     INNER JOIN departments d
        ON d.id = c.department_id
     WHERE c.status='Active'
     ORDER BY d.department_name,c.category_name"
);

if (!$categories) {
    die("Category Error : " . mysqli_error($conn));
}

/*
|--------------------------------------------------------------------------
| LOAD SUPPLIERS
|--------------------------------------------------------------------------
*/

$suppliers = mysqli_query(
    $conn,
    "SELECT
        id,
        supplier_name
     FROM suppliers
     ORDER BY supplier_name"
);

if (!$suppliers) {
    die("Supplier Error : " . mysqli_error($conn));
}

/*
|--------------------------------------------------------------------------
| DEFAULT MESSAGE
|--------------------------------------------------------------------------
*/

$message = "";
$message_type = "";

/*
|--------------------------------------------------------------------------
| SAVE PRODUCT
|--------------------------------------------------------------------------
*/

if (isset($_POST['save'])) {

    $category_id    = (int)$_POST['category_id'];

    $supplier_id    = !empty($_POST['supplier_id'])
                        ? (int)$_POST['supplier_id']
                        : NULL;

    $barcode        = trim($_POST['barcode']);

    $product_name   = trim($_POST['product_name']);

    $buying_price   = (float)$_POST['buying_price'];

    $selling_price  = (float)$_POST['selling_price'];

    $unit           = trim($_POST['unit']);

    $quantity       = (int)$_POST['quantity'];

    $reorder_level  = (int)$_POST['reorder_level'];

    $status         = $_POST['status'];

    if (empty($product_name)) {

        $message = "Product Name is required.";

        $message_type = "error";

    } elseif ($selling_price < $buying_price) {

        $message = "Selling price cannot be less than buying price.";

        $message_type = "error";

    } else {

        $uuid = generateUUID();

        $product_code = generateProductCode(
            $conn,
            $category_id
        );

        if (!$product_code) {

            die("Unable to generate Product Code.");

        }

        $sql = "
        INSERT INTO products
        (
            uuid,
            category_id,
            supplier_id,
            barcode,
            product_code,
            product_name,
            buying_price,
            selling_price,
            unit,
            quantity,
            reorder_level,
            status
        )

        VALUES
        (
            ?,?,?,?,?,?,?,?,?,?,?,?
        )
        ";

        $stmt = mysqli_prepare($conn,$sql);

        mysqli_stmt_bind_param(

            $stmt,

            "siissddsiiss",

            $uuid,

            $category_id,

            $supplier_id,

            $barcode,

            $product_code,

            $product_name,

            $buying_price,

            $selling_price,

            $unit,

            $quantity,

            $reorder_level,

            $status

        );

        if(mysqli_stmt_execute($stmt)){

            header("Location:index.php");

            exit();

        }else{

            $message = mysqli_error($conn);

            $message_type = "error";

        }

    }

}

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Product | R&R Collection POS</title>

    <link rel="stylesheet"
          href="../../assets/css/dashboard.css">

    <link rel="stylesheet"
          href="../../assets/css/sidebar.css">

    <link rel="stylesheet"
          href="../../assets/css/form.css">

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

            <i class="fa-solid fa-box"></i>

            Add Product

        </h1>

        <p>Create a new inventory item.</p>

    </div>

    <?php if(!empty($message)): ?>

        <div class="alert <?= $message_type; ?>">

            <?= htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>

    <div class="customer-panel">

        <form method="POST">

            <!-- =========================
                 CATEGORY & SUPPLIER
            ========================== -->

            <div class="customer-row">

                <div class="form-group">

                    <label>
                        Category
                        <span style="color:red">*</span>
                    </label>

                    <select
                        name="category_id"
                        required>

                        <option value="">
                            Select Category
                        </option>

                        <?php while($cat=mysqli_fetch_assoc($categories)): ?>

                            <option
                                value="<?= $cat['id']; ?>">

                                <?= htmlspecialchars($cat['department_name']); ?>

                                -

                                <?= htmlspecialchars($cat['category_name']); ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

                <div class="form-group">

                    <label>

                        Supplier

                    </label>

                    <select name="supplier_id">

                        <option value="">

                            No Supplier

                        </option>

                        <?php while($sup=mysqli_fetch_assoc($suppliers)): ?>

                            <option
                                value="<?= $sup['id']; ?>">

                                <?= htmlspecialchars($sup['supplier_name']); ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>

            </div>
             <!-- =========================
                 BARCODE & PRODUCT NAME
            ========================== -->

            <div class="customer-row">

                <div class="form-group">

                    <label>Barcode</label>

                    <input
                        type="text"
                        name="barcode"
                        placeholder="Scan or Enter Barcode">

                </div>

                <div class="form-group">

                    <label>
                        Product Name
                        <span style="color:red">*</span>
                    </label>

                    <input
                        type="text"
                        name="product_name"
                        required
                        placeholder="Enter Product Name">

                </div>

            </div>


            <!-- =========================
                 BUYING & SELLING PRICE
            ========================== -->

            <div class="customer-row">

                <div class="form-group">

                    <label>Buying Price</label>

                    <input
                        type="number"
                        name="buying_price"
                        step="0.01"
                        min="0"
                        required>

                </div>

                <div class="form-group">

                    <label>Selling Price</label>

                    <input
                        type="number"
                        name="selling_price"
                        step="0.01"
                        min="0"
                        required>

                </div>

            </div>


            <!-- =========================
                 UNIT & OPENING STOCK
            ========================== -->

            <div class="customer-row">

                <div class="form-group">

                    <label>Unit</label>

                    <input
                        type="text"
                        name="unit"
                        placeholder="Piece, Box, Packet, Kg"
                        required>

                </div>

                <div class="form-group">

                    <label>Opening Stock</label>

                    <input
                        type="number"
                        name="quantity"
                        value="0"
                        min="0"
                        required>

                </div>

     </div>           
                 <!-- =========================
                 REORDER LEVEL & STATUS
            ========================== -->

            <div class="customer-row">

                <div class="form-group">

                    <label>Reorder Level</label>

                    <input
                        type="number"
                        name="reorder_level"
                        value="5"
                        min="0"
                        required>

                </div>

                <div class="form-group">

                    <label>Status</label>

                    <select name="status" required>

                        <option value="Available" selected>
                            Available
                        </option>

                        <option value="Out of Stock">
                            Out of Stock
                        </option>

                    </select>

                </div>

            </div>


            <!-- =========================
                 BUTTONS
            ========================== -->

            <div class="customer-buttons">

                <button
                    type="submit"
                    name="save"
                    class="customer-btn customer-save">

                    <i class="fa-solid fa-floppy-disk"></i>

                    Save Product

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