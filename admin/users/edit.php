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
| Load User
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "
    SELECT
        id,
        fullname,
        username,
        role,
        status
    FROM users
    WHERE id = ?
    LIMIT 1
    "
);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);


if (!$user) {
    die("User not found.");
}


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

$message = '';
$message_type = '';


/*
|--------------------------------------------------------------------------
| Update User
|--------------------------------------------------------------------------
*/

if (isset($_POST['update_user'])) {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $role     = $_POST['role'];
    $status   = $_POST['status'];
    $password = $_POST['password'];


    /*
    |--------------------------------------------------------------------------
    | Validate Required Fields
    |--------------------------------------------------------------------------
    */

    if ($fullname === '' || $username === '') {

        $message = "Full name and username are required.";
        $message_type = "error";

    } else {


        /*
        |--------------------------------------------------------------------------
        | Check Duplicate Username
        |--------------------------------------------------------------------------
        */

        $check = mysqli_prepare(
            $conn,
            "
            SELECT id
            FROM users
            WHERE username = ?
            AND id != ?
            LIMIT 1
            "
        );

        mysqli_stmt_bind_param(
            $check,
            "si",
            $username,
            $user_id
        );

        mysqli_stmt_execute($check);

        $check_result = mysqli_stmt_get_result($check);


        if (mysqli_num_rows($check_result) > 0) {

            $message = "Username already exists.";
            $message_type = "error";

        } else {


            /*
            |--------------------------------------------------------------------------
            | Update With Password
            |--------------------------------------------------------------------------
            */

            if ($password !== '') {

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                $update = mysqli_prepare(
                    $conn,
                    "
                    UPDATE users
                    SET
                        fullname = ?,
                        username = ?,
                        password = ?,
                        role = ?,
                        status = ?
                    WHERE id = ?
                    "
                );

                mysqli_stmt_bind_param(
                    $update,
                    "sssssi",
                    $fullname,
                    $username,
                    $hashed_password,
                    $role,
                    $status,
                    $user_id
                );


            /*
            |--------------------------------------------------------------------------
            | Update Without Password
            |--------------------------------------------------------------------------
            */

            } else {

                $update = mysqli_prepare(
                    $conn,
                    "
                    UPDATE users
                    SET
                        fullname = ?,
                        username = ?,
                        role = ?,
                        status = ?
                    WHERE id = ?
                    "
                );

                mysqli_stmt_bind_param(
                    $update,
                    "ssssi",
                    $fullname,
                    $username,
                    $role,
                    $status,
                    $user_id
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Execute Update
            |--------------------------------------------------------------------------
            */

            if (mysqli_stmt_execute($update)) {

                header("Location: index.php");

                exit;

            } else {

                $message =
                    "Unable to update user: " .
                    mysqli_error($conn);

                $message_type = "error";
            }
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

<title>
Edit User - R&R Collection
</title>

<link
    rel="stylesheet"
    href="../../assets/css/style.css"
>

<link
    rel="stylesheet"
    href="../../assets/css/users.css"
>

</head>


<body>


<div class="users-container">


<!-- =====================================================
     HEADER
====================================================== -->

<div class="users-header">

    <div>

        <h1>
            Edit User
        </h1>

        <p>
            Update system user details
        </p>

    </div>


    <a
        href="index.php"
        class="back-btn"
    >
        ← Back to Users
    </a>

</div>



<!-- =====================================================
     FORM CARD
====================================================== -->

<div class="user-form-card">


<?php if ($message !== ''): ?>

<div class="form-message <?= $message_type; ?>">

    <?= htmlspecialchars($message); ?>

</div>

<?php endif; ?>



<form method="POST">


<div class="form-grid">


<!-- FULL NAME -->

<div class="form-group">

<label>
Full Name
</label>

<input
    type="text"
    name="fullname"
    value="<?= htmlspecialchars($user['fullname']); ?>"
    placeholder="Enter full name"
    required
>

</div>



<!-- USERNAME -->

<div class="form-group">

<label>
Username
</label>

<input
    type="text"
    name="username"
    value="<?= htmlspecialchars($user['username']); ?>"
    placeholder="Enter username"
    required
>

</div>



<!-- PASSWORD -->

<div class="form-group">

<label>
New Password
</label>

<input
    type="password"
    name="password"
    placeholder="Leave blank to keep current password"
>

</div>



<!-- ROLE -->

<div class="form-group">

<label>
Role
</label>

<select name="role" required>

<option
    value="Cashier"
    <?= $user['role'] === 'Cashier' ? 'selected' : ''; ?>
>
    Cashier
</option>

<option
    value="Admin"
    <?= $user['role'] === 'Admin' ? 'selected' : ''; ?>
>
    Admin
</option>

</select>

</div>



<!-- STATUS -->

<div class="form-group">

<label>
Status
</label>

<select name="status" required>

<option
    value="Active"
    <?= $user['status'] === 'Active' ? 'selected' : ''; ?>
>
    Active
</option>

<option
    value="Inactive"
    <?= $user['status'] === 'Inactive' ? 'selected' : ''; ?>
>
    Inactive
</option>

</select>

</div>


</div>



<!-- ACTIONS -->

<div class="form-actions">


<button
    type="submit"
    name="update_user"
    class="save-btn"
>
    Update User
</button>


<a
    href="index.php"
    class="cancel-btn"
>
    Cancel
</a>


</div>


</form>


</div>


</div>


</body>

</html>