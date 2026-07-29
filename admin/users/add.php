<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('manage_users')) {
    die("Access Denied");
}

$message = '';
$message_type = '';

if (isset($_POST['save_user'])) {

    $fullname = trim($_POST['fullname']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role     = $_POST['role'];
    $status   = $_POST['status'];

    if ($fullname === '' || $username === '' || $password === '') {

        $message = "Please fill in all required fields.";
        $message_type = "error";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check username
        |--------------------------------------------------------------------------
        */

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE username = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $username
        );

        mysqli_stmt_execute($check);

        $check_result = mysqli_stmt_get_result($check);


        if (mysqli_num_rows($check_result) > 0) {

            $message = "Username already exists.";
            $message_type = "error";

        } else {

            /*
            |--------------------------------------------------------------------------
            | Hash password
            |--------------------------------------------------------------------------
            */

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /*
            |--------------------------------------------------------------------------
            | Insert user
            |--------------------------------------------------------------------------
            */

            $stmt = mysqli_prepare(
                $conn,
                "
                INSERT INTO users
                (
                    fullname,
                    username,
                    password,
                    role,
                    status
                )
                VALUES (?, ?, ?, ?, ?)
                "
            );

            mysqli_stmt_bind_param(
                $stmt,
                "sssss",
                $fullname,
                $username,
                $hashed_password,
                $role,
                $status
            );


            if (mysqli_stmt_execute($stmt)) {

                header("Location: index.php");

                exit;

            } else {

                $message =
                    "Unable to create user: " .
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
Add User - R&R Collection
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


<div class="users-header">

    <div>

        <h1>
            Add User
        </h1>

        <p>
            Create a new system user
        </p>

    </div>


    <a
        href="index.php"
        class="back-btn"
    >
        ← Back to Users
    </a>

</div>



<div class="user-form-card">


<?php if ($message !== ''): ?>

<div class="form-message <?= $message_type; ?>">

    <?= htmlspecialchars($message); ?>

</div>

<?php endif; ?>



<form method="POST">


<div class="form-grid">


<div class="form-group">

<label>
Full Name
</label>

<input
    type="text"
    name="fullname"
    placeholder="Enter full name"
    value="<?= htmlspecialchars($_POST['fullname'] ?? ''); ?>"
    required
>

</div>



<div class="form-group">

<label>
Username
</label>

<input
    type="text"
    name="username"
    placeholder="Enter username"
    value="<?= htmlspecialchars($_POST['username'] ?? ''); ?>"
    required
>

</div>



<div class="form-group">

<label>
Password
</label>

<input
    type="password"
    name="password"
    placeholder="Enter password"
    required
>

</div>



<div class="form-group">

<label>
Role
</label>

<select name="role" required>

<option value="Cashier">
Cashier
</option>

<option value="Admin">
Admin
</option>

</select>

</div>



<div class="form-group">

<label>
Status
</label>

<select name="status" required>

<option value="Active">
Active
</option>

<option value="Inactive">
Inactive
</option>

</select>

</div>


</div>



<div class="form-actions">

<button
    type="submit"
    name="save_user"
    class="save-btn"
>
    Save User
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