<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('make_sales')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| Get Search Term
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$category_id = isset($_GET['category_id'])
    ? (int) $_GET['category_id']
    : 0;


/*
|--------------------------------------------------------------------------
| SEARCH PRODUCTS
|--------------------------------------------------------------------------
*/

if ($search !== '') {

    $searchTerm = "%" . $search . "%";

    $sql = "
        SELECT
            id,
            product_name,
            selling_price,
            quantity,
            image
        FROM products
        WHERE
            product_name LIKE ?
            OR barcode LIKE ?
            OR product_code LIKE ?
        ORDER BY product_name ASC
        LIMIT 30
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $searchTerm,
        $searchTerm,
        $searchTerm
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

}


/*
|--------------------------------------------------------------------------
| CATEGORY PRODUCTS
|--------------------------------------------------------------------------
*/

elseif ($category_id > 0) {

    $sql = "
        SELECT
            id,
            product_name,
            selling_price,
            quantity,
            image
        FROM products
        WHERE category_id = ?
        AND status = 'Available'
        ORDER BY product_name ASC
    ";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("Prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $category_id
    );

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

}


/*
|--------------------------------------------------------------------------
| NOTHING SEARCHED
|--------------------------------------------------------------------------
*/

else {

    exit;

}


/*
|--------------------------------------------------------------------------
| DISPLAY PRODUCTS
|--------------------------------------------------------------------------
*/

if (mysqli_num_rows($result) === 0) {

    echo '
        <div class="no-products">
            No products found.
        </div>
    ';

    exit;
}


while ($product = mysqli_fetch_assoc($result)) {

    $productId = (int) $product['id'];

    $productName = htmlspecialchars(
        $product['product_name'],
        ENT_QUOTES,
        'UTF-8'
    );

    $price = number_format(
        (float) $product['selling_price'],
        2
    );

    $quantity = (int) $product['quantity'];

    ?>

    <div class="product-card">

        <?php if (!empty($product['image'])): ?>

            <img
                src="../../uploads/products/<?= htmlspecialchars(
                    $product['image'],
                    ENT_QUOTES,
                    'UTF-8'
                ); ?>"
                alt="<?= $productName; ?>"
            >

        <?php else: ?>

            <div class="no-image">
                No Image
            </div>

        <?php endif; ?>


        <h4>
            <?= $productName; ?>
        </h4>


        <p>
            KSh <?= $price; ?>
        </p>


        <p>
            Stock:
            <?= $quantity; ?>
        </p>


        <?php if ($quantity > 0): ?>

            <button
                type="button"
                class="add-cart"
                data-id="<?= $productId; ?>"
                data-name="<?= $productName; ?>"
                data-price="<?= $product['selling_price']; ?>"
            >
                Add
            </button>

        <?php else: ?>

            <button
                type="button"
                disabled
            >
                Out of Stock
            </button>

        <?php endif; ?>

    </div>

    <?php
}


if (isset($stmt)) {
    mysqli_stmt_close($stmt);
}

?>