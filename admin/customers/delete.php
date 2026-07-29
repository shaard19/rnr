<?php
require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('delete_customer')) {
    die("Access Denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid customer.");
}

$id = (int)$_GET['id'];

// Check customer exists
$stmt = mysqli_prepare($conn, "SELECT customer_name FROM customers WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Customer not found.");
}

$customer = mysqli_fetch_assoc($result);

// Delete customer
$stmt = mysqli_prepare($conn, "DELETE FROM customers WHERE id=?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {

    header("Location: index.php?success=Customer deleted successfully");
    exit();

} else {

    die("Unable to delete customer.");

}
?>