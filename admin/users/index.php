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
| Load Users
|--------------------------------------------------------------------------
*/

$result = mysqli_query(
    $conn,
    "
    SELECT
        id,
        fullname,
        username,
        role,
        status,
        last_login,
        created_at
    FROM users
    ORDER BY id DESC
    "
);

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
Users - R&R Collection
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
            System Users
        </h1>

        <p>
            Manage administrators and cashiers
        </p>

    </div>


    <a
        href="add.php"
        class="add-user-btn"
    >
        + Add User
    </a>

</div>



<!-- =====================================================
     USERS TABLE
====================================================== -->

<div class="users-card">


<div class="table-wrapper">


<table>


<thead>

<tr>

<th>
#
</th>

<th>
Full Name
</th>

<th>
Username
</th>

<th>
Role
</th>

<th>
Status
</th>

<th>
Last Login
</th>

<th>
Created
</th>

<th>
Actions
</th>

</tr>

</thead>


<tbody>


<?php if ($result && mysqli_num_rows($result) > 0): ?>


<?php while ($user = mysqli_fetch_assoc($result)): ?>


<tr>


<td>

<?= $user['id']; ?>

</td>


<td>

<strong>

<?= htmlspecialchars(
    $user['fullname']
); ?>

</strong>

</td>


<td>

<?= htmlspecialchars(
    $user['username']
); ?>

</td>


<td>

<span class="role-badge">

<?= htmlspecialchars(
    $user['role']
); ?>

</span>

</td>


<td>


<?php if ($user['status'] === 'Active'): ?>

<span class="status-active">
    Active
</span>

<?php else: ?>

<span class="status-inactive">
    Inactive
</span>

<?php endif; ?>


</td>


<td>

<?php

if (!empty($user['last_login'])) {

    echo date(
        'd M Y H:i',
        strtotime($user['last_login'])
    );

} else {

    echo 'Never';

}

?>

</td>


<td>

<?= date(
    'd M Y',
    strtotime($user['created_at'])
); ?>

</td>


<td>

<div class="user-actions">


<a
    href="edit.php?id=<?= $user['id']; ?>"
    class="edit-btn"
>
    Edit
</a>


<a
    href="delete.php?id=<?= $user['id']; ?>"
    class="delete-btn"
    onclick="return confirm('Are you sure you want to deactivate this user?');"
>
    Deactivate
</a>


</div>

</td>


</tr>


<?php endwhile; ?>


<?php else: ?>


<tr>

<td
    colspan="8"
    class="empty-state"
>

No users found.

</td>

</tr>


<?php endif; ?>


</tbody>


</table>


</div>


</div>


</div>


</body>

</html>