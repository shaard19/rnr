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

if (!hasPermission('delete_product')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| GET PRODUCT ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("Invalid Product ID.");
}


/*
|--------------------------------------------------------------------------
| LOAD PRODUCT
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        id,
        product_code,
        product_name,
        status
    FROM products
    WHERE id = ?
";


$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Product Load Error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param($stmt, "i", $id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


if (!$result || mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    die("Product not found.");
}


$product = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);


/*
|--------------------------------------------------------------------------
| CHECK SALES HISTORY
|--------------------------------------------------------------------------
*/

$history_sql = "
    SELECT COUNT(*) AS total_sales
    FROM sale_items
    WHERE product_id = ?
";


$history_stmt = mysqli_prepare($conn, $history_sql);

if (!$history_stmt) {
    die("Sales History Check Error: " . mysqli_error($conn));
}


mysqli_stmt_bind_param(
    $history_stmt,
    "i",
    $id
);


mysqli_stmt_execute($history_stmt);

$history_result = mysqli_stmt_get_result($history_stmt);

$history = mysqli_fetch_assoc($history_result);

$total_sales = (int)$history['total_sales'];

mysqli_stmt_close($history_stmt);


/*
|--------------------------------------------------------------------------
| PRODUCT HAS SALES HISTORY
|--------------------------------------------------------------------------
|
| Never physically delete a product that appears in sale_items.
| Instead, deactivate it.
|
*/

if ($total_sales > 0) {

    $update_sql = "
        UPDATE products
        SET status = 'Inactive'
        WHERE id = ?
    ";


    $update_stmt = mysqli_prepare($conn, $update_sql);

    if (!$update_stmt) {
        die(
            "Product Deactivation Error: "
            . mysqli_error($conn)
        );
    }


    mysqli_stmt_bind_param(
        $update_stmt,
        "i",
        $id
    );


    if (mysqli_stmt_execute($update_stmt)) {

        mysqli_stmt_close($update_stmt);

        header("Location: index.php");
        exit();

    }


    $error = mysqli_stmt_error($update_stmt);

    mysqli_stmt_close($update_stmt);

    die("Unable to deactivate product: " . $error);
}


/*
|--------------------------------------------------------------------------
| PRODUCT HAS NEVER BEEN SOLD
|--------------------------------------------------------------------------
|
| Safe to permanently delete.
|
*/

$delete_sql = "
    DELETE FROM products
    WHERE id = ?
";


$delete_stmt = mysqli_prepare($conn, $delete_sql);

if (!$delete_stmt) {
    die(
        "Product Delete Prepare Error: "
        . mysqli_error($conn)
    );
}


mysqli_stmt_bind_param(
    $delete_stmt,
    "i",
    $id
);


if (mysqli_stmt_execute($delete_stmt)) {

    mysqli_stmt_close($delete_stmt);

    header("Location: index.php");
    exit();

}


$error = mysqli_stmt_error($delete_stmt);

mysqli_stmt_close($delete_stmt);

die("Unable to delete product: " . $error);

?>
