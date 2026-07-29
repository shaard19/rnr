<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";


/*
|--------------------------------------------------------------------------
| Permission
|--------------------------------------------------------------------------
*/

if (!hasPermission('manage_users')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| Validate User ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid user ID.");
}

$user_id = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Prevent Self-Deactivation
|--------------------------------------------------------------------------
*/

if ($user_id === (int) $_SESSION['user_id']) {
    die("You cannot deactivate your own account.");
}


/*
|--------------------------------------------------------------------------
| Check User Exists
|--------------------------------------------------------------------------
*/

$check = mysqli_prepare(
    $conn,
    "
    SELECT id, fullname, status
    FROM users
    WHERE id = ?
    LIMIT 1
    "
);

mysqli_stmt_bind_param(
    $check,
    "i",
    $user_id
);

mysqli_stmt_execute($check);

$result = mysqli_stmt_get_result($check);

$user = mysqli_fetch_assoc($result);


if (!$user) {
    die("User not found.");
}


/*
|--------------------------------------------------------------------------
| Deactivate User
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "
    UPDATE users
    SET status = 'Inactive'
    WHERE id = ?
    LIMIT 1
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


if (mysqli_stmt_execute($stmt)) {

    header("Location: index.php");

    exit;

} else {

    die(
        "Unable to deactivate user: " .
        mysqli_error($conn)
    );
}