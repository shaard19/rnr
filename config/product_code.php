<?php

/**
 * ============================================
 * R&R COLLECTION POS
 * Product Code Helper
 * ============================================
 */


/**
 * Generate UUID
 */
function generateUUID()
{
    return bin2hex(random_bytes(16));
}


/**
 * Generate Product Code
 *
 * Example:
 * ELE-00001
 * CLO-00002
 * STA-00003
 */
function generateProductCode(mysqli $conn, int $category_id)
{
    // Get Category Name
    $sql = "SELECT category_name
            FROM categories
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $category_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if (!$row = mysqli_fetch_assoc($result)) {
        return false;
    }

    // Generate Prefix
    $prefix = strtoupper(
        substr(
            preg_replace("/[^A-Za-z]/", "", $row['category_name']),
            0,
            3
        )
    );

    if ($prefix == "") {
        $prefix = "PRD";
    }

    // Count Existing Products
    $sql = "SELECT COUNT(*) AS total
            FROM products
            WHERE category_id = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, "i", $category_id);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $count = mysqli_fetch_assoc($result);

    $number = $count['total'] + 1;

    return $prefix . "-" . str_pad($number, 5, "0", STR_PAD_LEFT);
}
