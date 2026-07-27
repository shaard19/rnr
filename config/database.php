<?php
/*
|--------------------------------------------------------------------------
| R&R Collection POS
| Database Connection
|--------------------------------------------------------------------------
*/

$host = "localhost";
$username = "root";
$password = "";
$database = "rnr_collection";

$conn = mysqli_connect($host, $username, $password, $database);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Set UTF-8 character encoding
mysqli_set_charset($conn, "utf8mb4");
?>