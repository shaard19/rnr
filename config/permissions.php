<?php

function hasPermission($permission)
{
    global $conn;

    if(!isset($_SESSION['role']))
    {
        return false;
    }

    $role = $_SESSION['role'];

    $query = "
    SELECT p.permission_name 
    FROM role_permissions rp
    INNER JOIN permissions p 
    ON rp.permission_id = p.id
    WHERE rp.role = '$role'
    AND p.permission_name = '$permission'
    ";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0)
    {
        return true;
    }

    return false;
}

?>