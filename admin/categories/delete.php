<?php
session_start();

require '../../config/database.php';
require '../../config/session.php';
require '../../config/permissions.php';

if (!hasPermission('delete_category')) {
    die("Access Denied");
}

$id = intval($_GET['id']);

mysqli_query($conn,"DELETE FROM categories WHERE id='$id'");

header("Location: index.php");
exit();