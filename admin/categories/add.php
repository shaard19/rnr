<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";
if (!hasPermission('add_category')) {
    die("Access Denied");
}
$error = '';
$departments = mysqli_query(
    $conn,
    "SELECT id, department_name
     FROM departments
     WHERE status = 'Active'
     ORDER BY department_name ASC"
);
if (!$departments) {
    die("Department Query Error: " . mysqli_error($conn));
}
if (isset($_POST['save'])) {
    $department_id = (int)($_POST['department_id'] ?? 0);
    $category_name = trim(
        $_POST['category_name'] ?? ''
    );
    $description = trim(
        $_POST['description'] ?? ''
    );
    $status = $_POST['status'] ?? 'Active';
     if ($department_id <= 0) {
        $error = "Please select a department.";
    } elseif ($category_name === '') {
        $error = "Category name is required.";
    } else {
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM categories
             WHERE category_name = ?
             LIMIT 1"
        );
        if (!$check) {
            die(
                "Duplicate Check Error: " .
                mysqli_error($conn)
            );
        }
        mysqli_stmt_bind_param(
            $check,
            "s",
            $category_name
        );
        mysqli_stmt_execute($check);
        $result = mysqli_stmt_get_result($check);
        if (mysqli_num_rows($result) > 0) {
            $error = "Category already exists.";
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO categories
                (
                    category_name,
                    department_id,
                    description,
                    status
                )
                VALUES (?, ?, ?, ?)"
            );
            if (!$stmt) {
                die(
                    "Category Prepare Error: " .
                    mysqli_error($conn)
                );
            }
            mysqli_stmt_bind_param(
                $stmt,
                "siss",
                $category_name,
                $department_id,
                $description,
                $status
            );
            /* Execute */
            if (mysqli_stmt_execute($stmt)) {
                header("Location: index.php");
                exit();
            } else {
                $error =
                    "Unable to save category: " .
                    mysqli_stmt_error($stmt);
            }
            mysqli_stmt_close($stmt);
        }
        mysqli_stmt_close($check);
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
Add Category | R&R Collection POS
</title>
<link
    rel="stylesheet"
    href="../../assets/css/dashboard.css"
>
<link
    rel="stylesheet"
    href="../../assets/css/sidebar.css"
>
<link
    rel="stylesheet"
    href="../../assets/css/form.css"
>
<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>
</head>
<body>
<?php include "../../includes/sidebar.php"; ?>
<div class="main">
<?php include "../../includes/topbar.php"; ?>
<div class="container">
<div class="page-title">
<h1>
<i class="fa-solid fa-layer-group"></i>
Add Category
</h1>
<p>
Create a new inventory category.
</p>
</div>
<div class="customer-panel">
<?php if ($error !== ''): ?>
<div class="error-message">
<?= htmlspecialchars($error); ?>
</div>
<?php endif; ?>
<form method="POST">

<div class="customer-row">
<div class="form-group">
<label>
Department
<span class="required">*</span>
</label>
<select
name="department_id"
required
>
<option value="">
Select Department
</option>
<?php while (
    $department = mysqli_fetch_assoc($departments)
): ?>
<option
    value="<?= $department['id']; ?>"
    <?= (
        isset($_POST['department_id']) &&
        $_POST['department_id'] == $department['id']
    ) ? 'selected' : ''; ?>
>
<?= htmlspecialchars(
    $department['department_name']
); ?>
</option>
<?php endwhile; ?>
</select>
</div>
<div class="form-group">
<label>
Category Name
<span class="required">*</span>
</label>
<input
type="text"
name="category_name"
placeholder="Enter category name"
value="<?= htmlspecialchars(
     $_POST['category_name'] ?? ''
 ); ?>"
required
>
</div>
</div>
<div class="form-group">
<label>
Description
</label>
<textarea
    name="description"
    rows="4"
    placeholder="Enter category description"
><?= htmlspecialchars(
    $_POST['description'] ?? ''
); ?></textarea>
</div>
<div class="form-group">
<label>
Status
</label>
<select name="status">
<option
    value="Active"
    <?= (
        ($_POST['status'] ?? 'Active') === 'Active'
    ) ? 'selected' : ''; ?>
>
Active
</option>
<option
    value="Inactive"
    <?= (
        ($_POST['status'] ?? '') === 'Inactive'
    ) ? 'selected' : ''; ?>
>
Inactive
</option>
</select>
</div>
<div class="customer-buttons">
<button
type="submit"
name="save"
class="customer-btn customer-save"
>
<i class="fa-solid fa-floppy-disk"></i>
Save Category
</button>
<a
href="index.php"
class="customer-btn customer-back"
>
<i class="fa-solid fa-arrow-left"></i>
Back
</a>
</div>
</form>
</div>
</div>
</div>
</body>
</html>
