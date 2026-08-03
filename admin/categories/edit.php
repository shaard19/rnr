<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('edit_category')) {
    die("Access Denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];
$message = '';

/*
|--------------------------------------------------------------------------
| Load Departments
|--------------------------------------------------------------------------
| The categories table requires department_id, so load available
| departments for the edit form.
|--------------------------------------------------------------------------
*/
$departments = mysqli_query(
    $conn,
    "SELECT id, department_name
     FROM departments
     ORDER BY department_name ASC"
);

if (!$departments) {
    die("Department Error: " . mysqli_error($conn));
}

/*
|--------------------------------------------------------------------------
| Load Category
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare(
    $conn,
    "SELECT id, category_name, category_code, description, department_id, status
     FROM categories
     WHERE id = ?"
);

if (!$stmt) {
    die("Database Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$category = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$category) {
    die("Category not found.");
}

/*
|--------------------------------------------------------------------------
| Current Values
|--------------------------------------------------------------------------
*/
$categoryName = $category['category_name'] ?? '';
$categoryCode = $category['category_code'] ?? '';
$description = $category['description'] ?? '';
$departmentId = (int) ($category['department_id'] ?? 0);
$status = $category['status'] ?? 'Active';

/*
|--------------------------------------------------------------------------
| Update Category
|--------------------------------------------------------------------------
*/
if (isset($_POST['update'])) {

    $categoryName = trim($_POST['category_name'] ?? '');
    $categoryCode = trim($_POST['category_code'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $departmentId = (int) ($_POST['department_id'] ?? 0);
    $status = $_POST['status'] ?? 'Active';

    if ($categoryName === '') {

        $message = "Category name is required.";

    } elseif ($departmentId <= 0) {

        $message = "Please select a department.";

    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {

        $message = "Invalid category status.";

    } else {

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE categories
             SET
                category_name = ?,
                category_code = ?,
                description = ?,
                department_id = ?,
                status = ?
             WHERE id = ?"
        );

        if (!$stmt) {

            $message = "Update Error: " . mysqli_error($conn);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "sssisi",
                $categoryName,
                $categoryCode,
                $description,
                $departmentId,
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header("Location: index.php?updated=1");
                exit();

            } else {

                $message = "Could not update category: " . mysqli_stmt_error($stmt);
                mysqli_stmt_close($stmt);
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

<title>Edit Category | R&R Collection POS</title>

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
    href="../../assets/css/forms.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

<style>

.rnr-category-page {
    min-height: calc(100vh - 70px);
    padding: 32px;
    background: #f5f7fb;
}

.rnr-category-header {
    margin-bottom: 24px;
}

.rnr-category-header h1 {
    margin: 0;
    font-size: 30px;
    color: #172033;
}

.rnr-category-header p {
    margin: 7px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.rnr-category-card {
    width: 100%;
    max-width: 900px;
    background: #fff;
    border: 1px solid #e5e9f0;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(15, 23, 42, .06);
    overflow: hidden;
}

.rnr-category-card-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 24px 28px;
    border-bottom: 1px solid #edf0f4;
}

.rnr-category-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #edf3ff;
    color: #2563eb;
}

.rnr-category-card-header h2 {
    margin: 0;
    font-size: 18px;
    color: #172033;
}

.rnr-category-card-header p {
    margin: 4px 0 0;
    font-size: 13px;
    color: #7b8494;
}

.rnr-category-body {
    padding: 28px;
}

.rnr-category-message {
    margin-bottom: 22px;
    padding: 13px 16px;
    border-radius: 9px;
    background: #fff1f0;
    border: 1px solid #fecdca;
    color: #b42318;
    font-size: 14px;
    font-weight: 600;
}

.rnr-category-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 21px 24px;
}

.rnr-category-group {
    display: flex;
    flex-direction: column;
}

.rnr-category-group.full {
    grid-column: 1 / -1;
}

.rnr-category-group label {
    margin-bottom: 7px;
    color: #344054;
    font-size: 13px;
    font-weight: 600;
}

.rnr-category-group label span {
    color: #98a2b3;
    font-weight: 400;
}

.rnr-category-group input,
.rnr-category-group select,
.rnr-category-group textarea {
    width: 100%;
    box-sizing: border-box;
    border: 1px solid #d6dbe4;
    border-radius: 8px;
    background: #fff;
    color: #172033;
    font-size: 14px;
    outline: none;
    transition: .18s ease;
}

.rnr-category-group input,
.rnr-category-group select {
    height: 45px;
    padding: 0 12px;
}

.rnr-category-group textarea {
    min-height: 105px;
    padding: 12px;
    resize: vertical;
}

.rnr-category-group input:focus,
.rnr-category-group select:focus,
.rnr-category-group textarea:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
}

.rnr-category-help {
    margin-top: 6px;
    font-size: 12px;
    color: #667085;
}

.rnr-category-actions {
    display: flex;
    gap: 11px;
    margin-top: 28px;
    padding-top: 22px;
    border-top: 1px solid #edf0f4;
}

.rnr-category-btn {
    min-height: 44px;
    padding: 0 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
}

.rnr-category-save {
    border: 1px solid #2563eb;
    background: #2563eb;
    color: #fff;
}

.rnr-category-back {
    border: 1px solid #d8dee8;
    background: #fff;
    color: #344054;
}

@media (max-width: 750px) {

    .rnr-category-page {
        padding: 22px 16px 35px;
    }

    .rnr-category-grid {
        grid-template-columns: 1fr;
    }

    .rnr-category-group.full {
        grid-column: auto;
    }
}

@media (max-width: 550px) {

    .rnr-category-card-header,
    .rnr-category-body {
        padding: 20px;
    }

    .rnr-category-actions {
        flex-direction: column;
    }

    .rnr-category-btn {
        width: 100%;
    }
}

</style>

</head>

<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main">

<?php include "../../includes/topbar.php"; ?>

<div class="rnr-category-page">

    <div class="rnr-category-header">

        <h1>
            Edit Category
        </h1>

        <p>
            Update category information and department assignment.
        </p>

    </div>


    <div class="rnr-category-card">

        <div class="rnr-category-card-header">

            <div class="rnr-category-icon">
                <i class="fa-solid fa-layer-group"></i>
            </div>

            <div>

                <h2>
                    Category Information
                </h2>

                <p>
                    Modify the category details below.
                </p>

            </div>

        </div>


        <div class="rnr-category-body">

            <?php if ($message !== ''): ?>

                <div class="rnr-category-message">
                    <?= htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="rnr-category-grid">

                    <div class="rnr-category-group">

                        <label for="category_name">
                            Category Name
                        </label>

                        <input
                            type="text"
                            id="category_name"
                            name="category_name"
                            value="<?= htmlspecialchars($categoryName); ?>"
                            placeholder="e.g. Stationery"
                            required
                        >

                    </div>


                    <div class="rnr-category-group">

                        <label for="category_code">
                            Category Code
                            <span>(Optional)</span>
                        </label>

                        <input
                            type="text"
                            id="category_code"
                            name="category_code"
                            value="<?= htmlspecialchars($categoryCode); ?>"
                            placeholder="e.g. STAT-001"
                        >

                    </div>


                    <div class="rnr-category-group full">

                        <label for="description">
                            Description
                            <span>(Optional)</span>
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            placeholder="Enter a short category description"
                        ><?= htmlspecialchars($description); ?></textarea>

                    </div>


                    <div class="rnr-category-group">

                        <label for="department_id">
                            Department
                        </label>

                        <select
                            id="department_id"
                            name="department_id"
                            required
                        >

                            <option value="">
                                Select Department
                            </option>

                            <?php while ($department = mysqli_fetch_assoc($departments)): ?>

                                <option
                                    value="<?= (int) $department['id']; ?>"
                                    <?= ((int) $department['id'] === $departmentId) ? 'selected' : ''; ?>
                                >
                                    <?= htmlspecialchars($department['department_name']); ?>
                                </option>

                            <?php endwhile; ?>

                        </select>

                        <div class="rnr-category-help">
                            Select the department where this category belongs.
                        </div>

                    </div>


                    <div class="rnr-category-group">

                        <label for="status">
                            Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            required
                        >

                            <option
                                value="Active"
                                <?= $status === 'Active' ? 'selected' : ''; ?>
                            >
                                Active
                            </option>

                            <option
                                value="Inactive"
                                <?= $status === 'Inactive' ? 'selected' : ''; ?>
                            >
                                Inactive
                            </option>

                        </select>

                    </div>

                </div>


                <div class="rnr-category-actions">

                    <button
                        type="submit"
                        name="update"
                        class="rnr-category-btn rnr-category-save"
                    >
                        <i class="fa-solid fa-floppy-disk"></i>
                        Update Category
                    </button>

                    <a
                        href="index.php"
                        class="rnr-category-btn rnr-category-back"
                    >
                        <i class="fa-solid fa-arrow-left"></i>
                        Cancel
                    </a>

                </div>

            </form>

        </div>

    </div>

</div>

</div>

</body>
</html>
