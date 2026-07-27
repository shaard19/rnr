<?php
session_start();

require '../../config/database.php';
require '../../config/session.php';
require '../../config/permissions.php';

if (!hasPermission('add_category')) {
    die("Access Denied");
}

if (isset($_POST['save'])) {

    $categories = mysqli_query(
    $conn,
    "SELECT id, category_name
     FROM categories
     WHERE status='Active'
     ORDER BY category_name ASC"
);

if (!$categories) {
    die("SQL ERROR: " . mysqli_error($conn));
}
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Category</title>
    <link rel="stylesheet" href="../../assets/css/form.css">

</head>
<body>

<h2>Add Category</h2>

<?php
if(isset($error)){
    echo "<p style='color:red;'>$error</p>";
}
?>

<form method="POST">

<label>Category Name</label><br>
<input type="text" name="category_name" required><br><br>

<label>Description</label><br>
<textarea name="description"></textarea><br><br>

<button type="submit" name="save">
Save Category
</button>

</form>

<br>

<a href="index.php">← Back</a>

</body>
</html>