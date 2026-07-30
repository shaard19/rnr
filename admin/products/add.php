<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";
require_once "../../config/product_code.php";


/*
|--------------------------------------------------------------------------
| PERMISSION CHECK
|--------------------------------------------------------------------------
*/

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
     WHERE c.status = 'Active'
     ORDER BY d.department_name, c.category_name"
);

if (!$categories) {
    die("Category Error: " . mysqli_error($conn));
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
    die("Supplier Error: " . mysqli_error($conn));
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

    /*
    |--------------------------------------------------------------------------
    | GET FORM DATA
    |--------------------------------------------------------------------------
    */

    $category_id = (int)($_POST['category_id'] ?? 0);

    $supplier_id = !empty($_POST['supplier_id'])
        ? (int)$_POST['supplier_id']
        : NULL;

    $product_name = trim($_POST['product_name'] ?? '');

    $buying_price = (float)($_POST['buying_price'] ?? 0);

    $selling_price = (float)($_POST['selling_price'] ?? 0);

    $unit = trim($_POST['unit'] ?? '');

    $quantity = (int)($_POST['quantity'] ?? 0);

    $reorder_level = (int)($_POST['reorder_level'] ?? 0);

    $status = $_POST['status'] ?? 'Active';


    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    if ($category_id <= 0) {

        $message = "Please select a category.";
        $message_type = "error";

    } elseif (empty($product_name)) {

        $message = "Product Name is required.";
        $message_type = "error";

    } elseif ($buying_price < 0 || $selling_price < 0) {

        $message = "Prices cannot be negative.";
        $message_type = "error";

    } elseif ($selling_price < $buying_price) {

        $message = "Selling price cannot be less than buying price.";
        $message_type = "error";

    } elseif ($quantity < 0) {

        $message = "Quantity cannot be negative.";
        $message_type = "error";

    } elseif ($reorder_level < 0) {

        $message = "Reorder level cannot be negative.";
        $message_type = "error";

    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {

        $message = "Invalid product status.";
        $message_type = "error";

    } else {


        /*
        |--------------------------------------------------------------------------
        | GENERATE PRODUCT CODE
        |--------------------------------------------------------------------------
        */

        $product_code = generateProductCode(
            $conn,
            $category_id
        );

        if (!$product_code) {

            die("Unable to generate Product Code.");

        }


        /*
        |--------------------------------------------------------------------------
        | INSERT PRODUCT
        |--------------------------------------------------------------------------
        */

        $sql = "
            INSERT INTO products
            (
                product_code,
                product_name,
                category_id,
                supplier_id,
                unit,
                buying_price,
                selling_price,
                quantity,
                reorder_level,
                status
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
        ";


        /*
        |--------------------------------------------------------------------------
        | PREPARE STATEMENT
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare($conn, $sql);

        if (!$stmt) {

            die(
                "Product INSERT Prepare Error: "
                . mysqli_error($conn)
            );

        }


        /*
        |--------------------------------------------------------------------------
        | BIND PARAMETERS
        |--------------------------------------------------------------------------
        */

        mysqli_stmt_bind_param(
            $stmt,
            "ssiisddiis",
            $product_code,
            $product_name,
            $category_id,
            $supplier_id,
            $unit,
            $buying_price,
            $selling_price,
            $quantity,
            $reorder_level,
            $status
        );


        /*
        |--------------------------------------------------------------------------
        | EXECUTE
        |--------------------------------------------------------------------------
        */

        if (mysqli_stmt_execute($stmt)) {

            mysqli_stmt_close($stmt);

            header("Location: index.php");
            exit();

        } else {

            $message = mysqli_stmt_error($stmt);
            $message_type = "error";

            mysqli_stmt_close($stmt);
        }
    }
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

    <title>Add Product | R&R Collection POS</title>


    <!-- DASHBOARD CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/dashboard.css"
    >


    <!-- SIDEBAR CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/sidebar.css"
    >


    <!-- FORM CSS -->

    <link
        rel="stylesheet"
        href="../../assets/css/form.css"
    >


    <!-- FONT AWESOME -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

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

                Add Product

            </h1>

            <p>
                Create a new inventory item.
            </p>

        </div>


        <!-- MESSAGE -->

        <?php if (!empty($message)): ?>

            <div class="alert <?= htmlspecialchars($message_type); ?>">

                <?= htmlspecialchars($message); ?>

            </div>

        <?php endif; ?>


        <!-- PRODUCT FORM -->

        <div class="customer-panel">


            <form method="POST">


                <!-- =========================
                     CATEGORY & SUPPLIER
                ========================== -->

                <div class="customer-row">


                    <!-- CATEGORY -->

                    <div class="form-group">

                        <label>

                            Category

                            <span style="color:red">*</span>

                        </label>


                        <select
                            name="category_id"
                            required
                        >

                            <option value="">
                                Select Category
                            </option>


                            <?php while ($cat = mysqli_fetch_assoc($categories)): ?>

                                <option
                                    value="<?= (int)$cat['id']; ?>"
                                >

                                    <?= htmlspecialchars($cat['department_name']); ?>

                                    -

                                    <?= htmlspecialchars($cat['category_name']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- SUPPLIER -->

                    <div class="form-group">

                        <label>
                            Supplier
                        </label>


                        <select name="supplier_id">

                            <option value="">
                                No Supplier
                            </option>


                            <?php while ($sup = mysqli_fetch_assoc($suppliers)): ?>

                                <option
                                    value="<?= (int)$sup['id']; ?>"
                                >

                                    <?= htmlspecialchars($sup['supplier_name']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>

                </div>


                <!-- =========================
                     PRODUCT NAME
                ========================== -->

                <div class="customer-row">


                    <div class="form-group">

                        <label>

                            Product Name

                            <span style="color:red">*</span>

                        </label>


                        <input
                            type="text"
                            name="product_name"
                            required
                            maxlength="150"
                            placeholder="Enter Product Name"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Unit
                        </label>


                        <input
                            type="text"
                            name="unit"
                            maxlength="50"
                            placeholder="Piece, Box, Packet, Kg"
                        >

                    </div>

                </div>


                <!-- =========================
                     BUYING & SELLING PRICE
                ========================== -->

                <div class="customer-row">


                    <div class="form-group">

                        <label>
                            Buying Price
                        </label>


                        <input
                            type="number"
                            name="buying_price"
                            step="0.01"
                            min="0"
                            required
                            placeholder="0.00"
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Selling Price
                        </label>


                        <input
                            type="number"
                            name="selling_price"
                            step="0.01"
                            min="0"
                            required
                            placeholder="0.00"
                        >

                    </div>

                </div>


                <!-- =========================
                     STOCK & REORDER LEVEL
                ========================== -->

                <div class="customer-row">


                    <div class="form-group">

                        <label>
                            Opening Stock
                        </label>


                        <input
                            type="number"
                            name="quantity"
                            value="0"
                            min="0"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label>
                            Reorder Level
                        </label>


                        <input
                            type="number"
                            name="reorder_level"
                            value="5"
                            min="0"
                            required
                        >

                    </div>

                </div>


                <!-- =========================
                     STATUS
                ========================== -->

                <div class="customer-row">


                    <div class="form-group">

                        <label>
                            Status
                        </label>


                        <select
                            name="status"
                            required
                        >

                            <option
                                value="Active"
                                selected
                            >
                                Active
                            </option>


                            <option
                                value="Inactive"
                            >
                                Inactive
                            </option>

                        </select>

                    </div>


                    <div class="form-group">

                        <!-- EMPTY SPACE -->

                    </div>

                </div>


                <!-- =========================
                     BUTTONS
                ========================== -->

                <div class="customer-buttons">


                    <button
                        type="submit"
                        name="save"
                        class="customer-btn customer-save"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save Product

                    </button>


                    <a
                        href="index.php"
                        class="customer-btn customer-back"
                    >

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