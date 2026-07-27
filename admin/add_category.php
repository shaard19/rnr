<?php
require_once "../config/session.php";
require_once "../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $category = trim($_POST['category_name']);
    $description = trim($_POST['description']);

    if (empty($category)) {

        $error = "Category name is required.";

    } else {

        // Check if category already exists
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM categories WHERE category_name = ?"
        );

        mysqli_stmt_bind_param($check, "s", $category);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {

            $error = "Category already exists.";

        } else {

            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO categories(category_name, description)
                 VALUES(?, ?)"
            );

            mysqli_stmt_bind_param(
                $insert,
                "ss",
                $category,
                $description
            );

            mysqli_stmt_execute($insert);

            header("Location: categories.php?success=1");
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Category</title>

<link rel="stylesheet" href="../assets/css/dashboard.css">
<link rel="stylesheet" href="../assets/css/sidebar.css">
<link rel="stylesheet" href="../assets/css/forms.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php include "../includes/topbar.php"; ?>

<div class="container">

<div class="page-title">

<h1>Add Category</h1>

<p>Create a new product category</p>

</div>

<div class="panel">

<?php if($error!=""){ ?>

<div style="background:#FEE2E2;color:#B91C1C;padding:15px;border-radius:10px;margin-bottom:20px;">

<?php echo htmlspecialchars($error); ?>

</div>

<?php } ?>

<form method="POST">

<div style="margin-bottom:20px;">

<label><strong>Category Name</strong></label>

<input
type="text"
name="category_name"
style="width:100%;padding:12px;margin-top:8px;border:1px solid #ccc;border-radius:8px;"
required>

</div>

<div style="margin-bottom:20px;">

<label><strong>Description</strong></label>

<textarea
name="description"
rows="5"
style="width:100%;padding:12px;margin-top:8px;border:1px solid #ccc;border-radius:8px;"></textarea>

</div>

<button class="btn">

<i class="fa-solid fa-floppy-disk"></i>

Save Category

</button>

<a href="categories.php"
class="btn"
style="background:#6B7280;">

Cancel

</a>

</form>

</div>

</div>

</div>

</body>

</html>