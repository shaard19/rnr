<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('edit_customer')) {
    die("Access Denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = (int) $_GET['id'];

$message = '';
$message_type = '';

/*
|--------------------------------------------------------------------------
| Load Customer
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        customer_code,
        customer_name,
        phone,
        email,
        address,
        credit_limit,
        status,
        premium_status,
        sms_enabled,
        new_arrival_alerts
     FROM customers
     WHERE id = ?"
);

if (!$stmt) {
    die("Customer Query Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (!mysqli_stmt_execute($stmt)) {
    die("Customer Load Error: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);
$customer = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$customer) {
    die("Customer not found.");
}

/*
|--------------------------------------------------------------------------
| Existing Values
|--------------------------------------------------------------------------
*/

$customer_code = $customer['customer_code'] ?? '';
$customer_name = $customer['customer_name'] ?? '';
$phone = $customer['phone'] ?? '';
$email = $customer['email'] ?? '';
$address = $customer['address'] ?? '';
$credit_limit = $customer['credit_limit'] ?? '0.00';
$status = $customer['status'] ?? 'Active';

$premium_status = $customer['premium_status'] ?? 'Regular';
$sms_enabled = (int)($customer['sms_enabled'] ?? 1);
$new_arrival_alerts = (int)($customer['new_arrival_alerts'] ?? 0);

/*
|--------------------------------------------------------------------------
| Update Customer
|--------------------------------------------------------------------------
*/

if (isset($_POST['update'])) {

    $customer_code = trim($_POST['customer_code'] ?? '');
    $customer_name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    $credit_limit = trim($_POST['credit_limit'] ?? '0.00');

    $status = $_POST['status'] ?? 'Active';

    $premium_status = $_POST['premium_status'] ?? 'Regular';

    $sms_enabled = isset($_POST['sms_enabled']) ? 1 : 0;

    $new_arrival_alerts = isset($_POST['new_arrival_alerts']) ? 1 : 0;

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($customer_name === '') {

        $message = "Customer name is required.";
        $message_type = "error";

    } elseif ($credit_limit === '' || !is_numeric($credit_limit)) {

        $message = "Please enter a valid credit limit.";
        $message_type = "error";

    } elseif ((float)$credit_limit < 0) {

        $message = "Credit limit cannot be negative.";
        $message_type = "error";

    } elseif ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } elseif (!in_array($status, ['Active', 'Inactive'], true)) {

        $message = "Invalid customer status.";
        $message_type = "error";

    } elseif (!in_array($premium_status, ['Regular', 'Premium'], true)) {

        $message = "Invalid customer type.";
        $message_type = "error";

    } else {

        $credit_limit = number_format(
            (float)$credit_limit,
            2,
            '.',
            ''
        );

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $stmt = mysqli_prepare(
            $conn,
            "UPDATE customers
             SET
                customer_code = ?,
                customer_name = ?,
                phone = ?,
                email = ?,
                address = ?,
                credit_limit = ?,
                status = ?,
                premium_status = ?,
                sms_enabled = ?,
                new_arrival_alerts = ?
             WHERE id = ?"
        );

        if (!$stmt) {

            $message = "Unable to prepare customer update: "
                . mysqli_error($conn);

            $message_type = "error";

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssiii",
                $customer_code,
                $customer_name,
                $phone,
                $email,
                $address,
                $credit_limit,
                $status,
                $premium_status,
                $sms_enabled,
                $new_arrival_alerts,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {

                mysqli_stmt_close($stmt);

                header(
                    "Location: view.php?id="
                    . $id
                    . "&updated=1"
                );

                exit();

            }

            $message = "Unable to update customer: "
                . mysqli_stmt_error($stmt);

            $message_type = "error";

            mysqli_stmt_close($stmt);
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

<title>Edit Customer | R&R Collection POS</title>

<link rel="stylesheet"
href="../../assets/css/dashboard.css">

<link rel="stylesheet"
href="../../assets/css/sidebar.css">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

.rnr-customer-page{
    min-height:calc(100vh - 70px);
    padding:32px;
    background:#f5f7fb
}

.rnr-customer-header{
    margin-bottom:24px
}

.rnr-customer-header h1{
    margin:0;
    font-size:30px;
    font-weight:700;
    color:#172033
}

.rnr-customer-header p{
    margin:7px 0 0;
    font-size:14px;
    color:#6b7280
}

.rnr-customer-card{
    width:100%;
    max-width:1100px;
    background:#fff;
    border:1px solid #e5e9f0;
    border-radius:16px;
    box-shadow:0 8px 30px rgba(15,23,42,.06);
    overflow:hidden
}

.rnr-customer-card-header{
    display:flex;
    align-items:center;
    gap:14px;
    padding:24px 28px;
    border-bottom:1px solid #edf0f4
}

.rnr-customer-icon{
    width:44px;
    height:44px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#edf3ff;
    color:#2563eb;
    font-size:18px
}

.rnr-customer-card-header h2{
    margin:0;
    font-size:18px;
    color:#172033
}

.rnr-customer-card-header p{
    margin:4px 0 0;
    font-size:13px;
    color:#7b8494
}

.rnr-customer-body{
    padding:28px
}

.rnr-customer-message{
    margin-bottom:22px;
    padding:13px 16px;
    border-radius:9px;
    font-size:14px;
    font-weight:600
}

.rnr-customer-message.error{
    color:#b42318;
    background:#fff1f0;
    border:1px solid #fecdca
}

.rnr-customer-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:20px 24px
}

.rnr-customer-section{
    grid-column:1/-1;
    display:flex;
    align-items:center;
    gap:12px;
    margin-top:8px
}

.rnr-customer-section-title{
    font-size:13px;
    font-weight:700;
    color:#344054;
    white-space:nowrap;
    text-transform:uppercase;
    letter-spacing:.04em
}

.rnr-customer-section-line{
    height:1px;
    flex:1;
    background:#edf0f4
}

.rnr-customer-group{
    display:flex;
    flex-direction:column
}

.rnr-customer-group.full{
    grid-column:1/-1
}

.rnr-customer-group label{
    margin-bottom:7px;
    font-size:13px;
    font-weight:600;
    color:#344054
}

.rnr-customer-group label span{
    font-weight:400;
    color:#98a2b3
}

.rnr-customer-group input,
.rnr-customer-group select,
.rnr-customer-group textarea{
    width:100%;
    box-sizing:border-box;
    border:1px solid #d6dbe4;
    border-radius:8px;
    background:#fff;
    color:#172033;
    font-size:14px;
    outline:none;
    transition:.18s
}

.rnr-customer-group input,
.rnr-customer-group select{
    height:45px;
    padding:0 12px
}

.rnr-customer-group textarea{
    min-height:90px;
    padding:12px;
    resize:vertical
}

.rnr-customer-group input:focus,
.rnr-customer-group select:focus,
.rnr-customer-group textarea:focus{
    border-color:#2563eb;
    box-shadow:0 0 0 3px rgba(37,99,235,.10)
}

.rnr-credit-box{
    position:relative
}

.rnr-credit-box .currency{
    position:absolute;
    left:12px;
    top:50%;
    transform:translateY(-50%);
    font-size:13px;
    font-weight:700;
    color:#667085;
    pointer-events:none
}

.rnr-credit-box input{
    padding-left:52px!important
}

.rnr-credit-help{
    margin-top:6px;
    font-size:12px;
    color:#667085
}

.notification-box{
    grid-column:1/-1;
    padding:20px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    background:#f8fafc
}

.notification-box h3{
    margin:0 0 6px;
    color:#172033;
    font-size:16px
}

.notification-box p{
    margin:0 0 18px;
    color:#667085;
    font-size:13px
}

.notification-option{
    display:flex;
    align-items:flex-start;
    gap:10px;
    margin-bottom:14px
}

.notification-option:last-child{
    margin-bottom:0
}

.notification-option input{
    width:17px;
    height:17px;
    margin-top:2px
}

.notification-option label{
    font-size:14px;
    color:#344054;
    cursor:pointer
}

.notification-option small{
    display:block;
    color:#667085;
    margin-top:3px
}

.rnr-customer-actions{
    display:flex;
    gap:11px;
    align-items:center;
    margin-top:28px;
    padding-top:22px;
    border-top:1px solid #edf0f4
}

.rnr-customer-btn{
    height:44px;
    padding:0 18px;
    display:inline-flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    border-radius:8px;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
    cursor:pointer
}

.rnr-customer-save{
    border:1px solid #2563eb;
    background:#2563eb;
    color:#fff
}

.rnr-customer-back{
    border:1px solid #d8dee8;
    background:#fff;
    color:#344054
}

@media(max-width:850px){

    .rnr-customer-page{
        padding:24px 20px 40px
    }

    .rnr-customer-grid{
        grid-template-columns:1fr
    }

    .rnr-customer-section,
    .rnr-customer-group.full,
    .notification-box{
        grid-column:auto
    }

}

@media(max-width:600px){

    .rnr-customer-page{
        padding:18px 14px 35px
    }

    .rnr-customer-card-header,
    .rnr-customer-body{
        padding:20px
    }

    .rnr-customer-header h1{
        font-size:25px
    }

    .rnr-customer-actions{
        flex-direction:column;
        align-items:stretch
    }

    .rnr-customer-btn{
        width:100%
    }

}

</style>

</head>

<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main">

<?php include "../../includes/topbar.php"; ?>

<div class="rnr-customer-page">

<div class="rnr-customer-header">

<h1>
<i class="fa-solid fa-user-pen"></i>
Edit Customer
</h1>

<p>
Update customer details, credit settings and notification preferences.
</p>

</div>

<div class="rnr-customer-card">

<div class="rnr-customer-card-header">

<div class="rnr-customer-icon">

<i class="fa-solid fa-user-pen"></i>

</div>

<div>

<h2>
Customer Information
</h2>

<p>
Modify customer details and communication preferences.
</p>

</div>

</div>

<div class="rnr-customer-body">

<?php if ($message !== ''): ?>

<div class="rnr-customer-message <?= htmlspecialchars($message_type); ?>">

<?= htmlspecialchars($message); ?>

</div>

<?php endif; ?>

<form method="POST">

<div class="rnr-customer-grid">

<div class="rnr-customer-section">

<div class="rnr-customer-section-title">
Customer Information
</div>

<div class="rnr-customer-section-line"></div>

</div>

<div class="rnr-customer-group">

<label>
Customer Code <span>(Optional)</span>
</label>

<input
type="text"
name="customer_code"
value="<?= htmlspecialchars($customer_code); ?>"
placeholder="e.g. CUST-001">

</div>

<div class="rnr-customer-group">

<label>
Customer Name
</label>

<input
type="text"
name="customer_name"
value="<?= htmlspecialchars($customer_name); ?>"
placeholder="Enter customer name"
required>

</div>

<div class="rnr-customer-group">

<label>
Phone <span>(Optional)</span>
</label>

<input
type="text"
name="phone"
value="<?= htmlspecialchars($phone); ?>"
placeholder="e.g. 0712345678">

</div>

<div class="rnr-customer-group">

<label>
Email <span>(Optional)</span>
</label>

<input
type="email"
name="email"
value="<?= htmlspecialchars($email); ?>"
placeholder="customer@example.com">

</div>

<div class="rnr-customer-group full">

<label>
Address <span>(Optional)</span>
</label>

<textarea
name="address"
placeholder="Enter customer address"><?= htmlspecialchars($address); ?></textarea>

</div>

<div class="rnr-customer-section">

<div class="rnr-customer-section-title">
Credit Management
</div>

<div class="rnr-customer-section-line"></div>

</div>

<div class="rnr-customer-group">

<label>
Credit Limit
</label>

<div class="rnr-credit-box">

<span class="currency">
KES
</span>

<input
type="number"
name="credit_limit"
min="0"
step="0.01"
value="<?= htmlspecialchars($credit_limit); ?>"
placeholder="0.00"
required>

</div>

<div class="rnr-credit-help">
Maximum amount this customer is allowed to owe R&amp;R.
</div>

</div>

<div class="rnr-customer-group">

<label>
Customer Status
</label>

<select name="status" required>

<option value="Active"
<?= $status === 'Active' ? 'selected' : ''; ?>>
Active
</option>

<option value="Inactive"
<?= $status === 'Inactive' ? 'selected' : ''; ?>>
Inactive
</option>

</select>

</div>

<div class="rnr-customer-section">

<div class="rnr-customer-section-title">
Customer Notifications
</div>

<div class="rnr-customer-section-line"></div>

</div>

<div class="rnr-customer-group">

<label>
Customer Type
</label>

<select name="premium_status">

<option value="Regular"
<?= $premium_status === 'Regular' ? 'selected' : ''; ?>>
Regular
</option>

<option value="Premium"
<?= $premium_status === 'Premium' ? 'selected' : ''; ?>>
Premium
</option>

</select>

</div>

<div class="rnr-customer-group">

<label>
SMS Permission
</label>

<select name="sms_enabled">

<option value="1"
<?= $sms_enabled === 1 ? 'selected' : ''; ?>>
Allowed
</option>

<option value="0"
<?= $sms_enabled === 0 ? 'selected' : ''; ?>>
Disabled
</option>

</select>

</div>

<div class="notification-box">

<h3>
<i class="fa-solid fa-bell"></i>
Notification Preferences
</h3>

<p>
These settings control which automated notifications R&amp;R may send later.
</p>

<div class="notification-option">

<input
type="checkbox"
id="new_arrival_alerts"
name="new_arrival_alerts"
value="1"
<?= $new_arrival_alerts === 1 ? 'checked' : ''; ?>>

<div>

<label for="new_arrival_alerts">
Receive new-arrival alerts
</label>

<small>
Premium customers can receive alerts when new products arrive.
</small>

</div>

</div>

</div>

</div>

<div class="rnr-customer-actions">

<button
type="submit"
name="update"
class="rnr-customer-btn rnr-customer-save">

<i class="fa-solid fa-floppy-disk"></i>

Save Customer

</button>

<a
href="view.php?id=<?= $id; ?>"
class="rnr-customer-btn rnr-customer-back">

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