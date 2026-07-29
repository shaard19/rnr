<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('add_category')) {
    die("Access Denied");
}

/* Load Departments */
$departments = mysqli_query(
    $conn,
    "SELECT id, department_name
     FROM departments
     WHERE status='Active'
     ORDER BY department_name ASC"
);

if (isset($_POST['save'])) {

    $department_id = (int)$_POST['department_id'];
    $category_name = trim($_POST['category_name']);
    $category_code = strtoupper(trim($_POST['category_code']));
    $description   = trim($_POST['description']);
    $status        = $_POST['status'];

    /* Duplicate Category Name */
    $check = mysqli_prepare(
        $conn,
        "SELECT id FROM categories WHERE category_name=?"
    );

    mysqli_stmt_bind_param($check, "s", $category_name);
    mysqli_stmt_execute($check);
    $result = mysqli_stmt_get_result($check);

    if (mysqli_num_rows($result) > 0) {

        $error = "Category already exists.";

    } else {

        $sql = mysqli_prepare(
            $conn,
            "INSERT INTO categories
            (
                department_id,
                category_name,
                category_code,
                description,
                status
            )
            VALUES
            (?,?,?,?,?)"
        );

        mysqli_stmt_bind_param(
            $sql,
            "issss",
            $department_id,
            $category_name,
            $category_code,
            $description,
            $status
        );

        if(mysqli_stmt_execute($sql)){

            header("Location: index.php");
            exit();

        }else{

            $error = mysqli_error($conn);

        }

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Add Category | R&R Collection POS</title>

<link rel="stylesheet" href="../../assets/css/dashboard.css">
<link rel="stylesheet" href="../../assets/css/sidebar.css">
<link rel="stylesheet" href="../../assets/css/form.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

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

<p>Create a new inventory category.</p>

</div>

<div class="customer-panel">

<?php
if(isset($error)){
?>
<div class="error-message">
<?= $error; ?>
</div>
<?php } ?>

<form method="POST">

<div class="customer-row">

<div class="form-group">

<label>

Department
<span class="required">*</span>

</label>

<select name="department_id" required>

<option value="">Select Department</option>

<?php while($department=mysqli_fetch_assoc($departments)){ ?>

<option value="<?= $department['id']; ?>">

<?= htmlspecialchars($department['department_name']); ?>

</option>

<?php } ?>

</select>

</div>

<div class="form-group">

<label>

Category Code
<span class="required">*</span>

</label>

<input
type="text"
name="category_code"
placeholder="MJKT"
maxlength="10"
required>

</div>

</div>

<div class="customer-row">

<div class="form-group">

<label>

Category Name
<span class="required">*</span>

</label>

<input
type="text"
name="category_name"
required>

</div>

<div class="form-group">

<label>Status</label>

<select name="status">

<option value="Active">
Active
</option>

<option value="Inactive">
Inactive
</option>

</select>

</div>

</div>

<div class="form-group">

<label>Description</label>

<textarea
name="description"
rows="4"></textarea>

</div>

<div class="customer-buttons">

<button
type="submit"
name="save"
class="customer-btn customer-save">

<i class="fa-solid fa-floppy-disk"></i>

Save Category

</button>

<a
href="index.php"
class="customer-btn customer-back">

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