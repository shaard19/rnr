<?php
/*
|--------------------------------------------------------------------------
| R&R Collection POS
| User Authentication
|--------------------------------------------------------------------------
*/

session_start();

require_once "../config/database.php";

// Check if form was submitted
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: login.php");
    exit();
}

// Get form values
$username = trim($_POST['username']);
$password = $_POST['password'];

// Basic validation
if (empty($username) || empty($password)) {
    header("Location: login.php?error=1");
    exit();
}

// Find user
$sql = "SELECT * FROM users
        WHERE username = ?
        AND status = 'Active'
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "s", $username);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

// User found?
if (mysqli_num_rows($result) == 1) {

    $user = mysqli_fetch_assoc($result);

    // Verify password
    if (password_verify($password, $user['password'])) {

        // Store session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['fullname'] = $user['fullname'];
        $_SESSION['role'] = $user['role'];

        // Update last login
        $update = mysqli_prepare(
            $conn,
            "UPDATE users SET last_login = NOW() WHERE id = ?"
        );

        mysqli_stmt_bind_param($update, "i", $user['id']);
        mysqli_stmt_execute($update);

        // Redirect according to role
        if ($user['role'] == "Admin") {

            header("Location: ../admin/dashboard.php");

        } else {

            header("Location: ../cashier/dashboard.php");

        }

        exit();
    }
}

// Login failed
header("Location: login.php?error=1");
exit();