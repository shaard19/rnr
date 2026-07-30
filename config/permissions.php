<?php

function hasPermission($permission)
{
    global $conn;

    if (!isset($_SESSION['role'])) {
        return false;
    }

    $role = $_SESSION['role'];

    $role_safe = mysqli_real_escape_string($conn, $role);
    $permission_safe = mysqli_real_escape_string($conn, $permission);

    $query = "
        SELECT p.permission_name
        FROM role_permissions rp
        INNER JOIN permissions p
            ON rp.permission_id = p.id
        WHERE rp.role = '$role_safe'
        AND p.permission_name = '$permission_safe'
    ";

    $result = mysqli_query($conn, $query);

    if (!$result) {
        return false;
    }

    return mysqli_num_rows($result) > 0;
}

?>
