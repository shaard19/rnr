<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

if (!hasPermission('view_customer')) {
    die("Access Denied");
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid customer.");
}

$id = (int) $_GET['id'];

/*
|--------------------------------------------------------------------------
| Load Customer
|--------------------------------------------------------------------------
*/

$stmt = mysqli_prepare(
    $conn,
    "SELECT *
     FROM customers
     WHERE id = ?"
);

if (!$stmt) {
    die("Customer query error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (!mysqli_stmt_execute($stmt)) {
    die("Customer load error: " . mysqli_stmt_error($stmt));
}

$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    die("Customer not found.");
}

$customer = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| Customer Information
|--------------------------------------------------------------------------
*/

$customerCode = $customer['customer_code'] ?? '';
$customerName = $customer['customer_name'] ?? '';
$phone        = $customer['phone'] ?? '';
$email        = $customer['email'] ?? '';
$address      = $customer['address'] ?? '';
$status       = $customer['status'] ?? 'Active';

/*
|--------------------------------------------------------------------------
| Notification Settings
|--------------------------------------------------------------------------
*/

$premiumStatus = $customer['premium_status'] ?? 'Regular';

$smsEnabled = (int) ($customer['sms_enabled'] ?? 1);

$newArrivalAlerts = (int) ($customer['new_arrival_alerts'] ?? 0);

/*
|--------------------------------------------------------------------------
| Credit Limit
|--------------------------------------------------------------------------
*/

$creditLimit = isset($customer['credit_limit']) && is_numeric($customer['credit_limit'])
    ? (float) $customer['credit_limit']
    : 0.00;

/*
|--------------------------------------------------------------------------
| Calculate Current Credit Balance
|--------------------------------------------------------------------------
|
| We calculate the outstanding balance directly from sales.balance.
| This keeps the displayed amount synchronized with actual credit sales.
|--------------------------------------------------------------------------
*/

$creditBalance = 0.00;

$balanceStmt = mysqli_prepare(
    $conn,
    "SELECT COALESCE(SUM(balance), 0)
     FROM sales
     WHERE customer_id = ?
       AND balance > 0"
);

if (!$balanceStmt) {
    die("Credit balance query error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($balanceStmt, "i", $id);

mysqli_stmt_execute($balanceStmt);

mysqli_stmt_bind_result(
    $balanceStmt,
    $calculatedBalance
);

mysqli_stmt_fetch($balanceStmt);

$creditBalance = (float) ($calculatedBalance ?? 0);

mysqli_stmt_close($balanceStmt);

/*
|--------------------------------------------------------------------------
| Available Credit
|--------------------------------------------------------------------------
*/

$availableCredit = max(
    0,
    $creditLimit - $creditBalance
);

/*
|--------------------------------------------------------------------------
| Credit Status
|--------------------------------------------------------------------------
*/

if ($creditBalance <= 0) {

    $creditStatus = "Clear";
    $creditStatusClass = "credit-clear";

} elseif ($creditLimit > 0 && $creditBalance >= $creditLimit) {

    $creditStatus = "Limit Reached";
    $creditStatusClass = "credit-danger";

} else {

    $creditStatus = "Outstanding";
    $creditStatusClass = "credit-warning";
}

/*
|--------------------------------------------------------------------------
| Notification Status Helpers
|--------------------------------------------------------------------------
*/

$smsStatus = $smsEnabled === 1
    ? "Enabled"
    : "Disabled";

$smsStatusClass = $smsEnabled === 1
    ? "credit-clear"
    : "credit-danger";

$arrivalStatus = $newArrivalAlerts === 1
    ? "Enabled"
    : "Disabled";

$arrivalStatusClass = $newArrivalAlerts === 1
    ? "credit-clear"
    : "credit-danger";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>
    Customer Profile | R&R Collection POS
</title>

<link rel="stylesheet"
      href="../../assets/css/dashboard.css">

<link rel="stylesheet"
      href="../../assets/css/sidebar.css">

<link rel="stylesheet"
      href="../../assets/css/form.css">

<link rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

<style>

.customer-profile {
    background: #fff;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 28px rgba(15, 23, 42, 0.08);
}

.profile-header {
    display: flex;
    align-items: center;
    gap: 18px;
    padding-bottom: 24px;
    margin-bottom: 30px;
    border-bottom: 1px solid #e5e7eb;
}

.avatar {
    width: 74px;
    height: 74px;
    border-radius: 50%;
    background: #1677ff;
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    flex-shrink: 0;
}

.profile-header h2 {
    margin: 0 0 5px;
    color: #172033;
    font-size: 24px;
}

.profile-header p {
    margin: 0;
    color: #6b7280;
    font-size: 14px;
}

.profile-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 24px;
}

.profile-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 22px;
    background: #fff;
}

.profile-card h3 {
    margin: 0 0 18px;
    color: #1677ff;
    font-size: 18px;
}

.profile-card h3 i {
    margin-right: 7px;
}

.profile-table {
    width: 100%;
    border-collapse: collapse;
}

.profile-table tr {
    border-bottom: 1px solid #edf0f4;
}

.profile-table tr:last-child {
    border-bottom: none;
}

.profile-table td {
    padding: 14px 8px;
    vertical-align: top;
}

.profile-table td:first-child {
    width: 44%;
    color: #374151;
    font-weight: 500;
}

.profile-table td:last-child {
    color: #172033;
    word-break: break-word;
}

.money-owed {
    color: #dc2626 !important;
    font-weight: 700;
}

.money-clear {
    color: #15803d !important;
    font-weight: 700;
}

.credit-status {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
}

.credit-clear {
    background: #dcfce7;
    color: #15803d;
}

.credit-warning {
    background: #fef3c7;
    color: #b45309;
}

.credit-danger {
    background: #fee2e2;
    color: #b91c1c;
}

.notification-card {
    grid-column: 1 / -1;
}

.notification-description {
    margin: -8px 0 18px;
    color: #6b7280;
    font-size: 13px;
}

.customer-buttons {
    display: flex;
    gap: 12px;
    margin-top: 26px;
}

.customer-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    min-height: 44px;
    padding: 0 18px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    transition: opacity .2s, transform .2s;
}

.customer-btn:hover {
    opacity: .9;
    transform: translateY(-1px);
}

.customer-edit {
    background: #198754;
    color: #fff;
}

.customer-back {
    background: #6c757d;
    color: #fff;
}

@media (max-width: 800px) {

    .profile-grid {
        grid-template-columns: 1fr;
    }

    .notification-card {
        grid-column: auto;
    }

    .customer-profile {
        padding: 20px;
    }

    .profile-header h2 {
        font-size: 21px;
    }

}

@media (max-width: 500px) {

    .customer-buttons {
        flex-direction: column;
    }

    .customer-btn {
        width: 100%;
        box-sizing: border-box;
    }

}

</style>

</head>

<body>

<?php include "../../includes/sidebar.php"; ?>

<div class="main">

    <?php include "../../includes/topbar.php"; ?>

    <div class="container">

        <div class="page-title">

            <h1>
                <i class="fas fa-user-circle"></i>
                Customer Profile
            </h1>

            <p>
                View customer information, credit account and notification preferences.
            </p>

        </div>

        <div class="customer-profile">

            <!-- PROFILE HEADER -->

            <div class="profile-header">

                <div class="avatar">
                    <i class="fas fa-user"></i>
                </div>

                <div>

                    <h2>
                        <?= htmlspecialchars($customerName); ?>
                    </h2>

                    <p>
                        Customer Code:
                        <?= htmlspecialchars($customerCode); ?>
                    </p>

                </div>

            </div>

            <div class="profile-grid">

                <!-- BASIC INFORMATION -->

                <div class="profile-card">

                    <h3>
                        <i class="fas fa-id-card"></i>
                        Basic Information
                    </h3>

                    <table class="profile-table">

                        <tr>
                            <td>Customer Code</td>

                            <td>
                                <?= htmlspecialchars($customerCode); ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Name</td>

                            <td>
                                <?= htmlspecialchars($customerName); ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Phone</td>

                            <td>
                                <?= htmlspecialchars($phone); ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Email</td>

                            <td>
                                <?= htmlspecialchars($email); ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Address</td>

                            <td>
                                <?= nl2br(htmlspecialchars($address)); ?>
                            </td>
                        </tr>

                        <tr>
                            <td>Customer Type</td>

                            <td>

                                <?php if ($premiumStatus === 'Premium'): ?>

                                    <span class="credit-status credit-warning">

                                        <i class="fas fa-crown"></i>

                                        Premium

                                    </span>

                                <?php else: ?>

                                    <span class="credit-status">

                                        Regular

                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <tr>
                            <td>Customer Status</td>

                            <td>

                                <?php if ($status === 'Active'): ?>

                                    <span class="credit-status credit-clear">

                                        Active

                                    </span>

                                <?php else: ?>

                                    <span class="credit-status credit-danger">

                                        <?= htmlspecialchars($status); ?>

                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                    </table>

                </div>


                <!-- CREDIT ACCOUNT -->

                <div class="profile-card">

                    <h3>
                        <i class="fas fa-wallet"></i>
                        Credit Account
                    </h3>

                    <table class="profile-table">

                        <tr>

                            <td>
                                Credit Limit
                            </td>

                            <td>

                                <strong>
                                    KES
                                    <?= number_format($creditLimit, 2); ?>
                                </strong>

                            </td>

                        </tr>

                        <tr>

                            <td>
                                Amount Owed
                            </td>

                            <td class="<?= $creditBalance > 0 ? 'money-owed' : 'money-clear'; ?>">

                                KES
                                <?= number_format($creditBalance, 2); ?>

                            </td>

                        </tr>

                        <tr>

                            <td>
                                Available Credit
                            </td>

                            <td>

                                <strong>
                                    KES
                                    <?= number_format($availableCredit, 2); ?>
                                </strong>

                            </td>

                        </tr>

                        <tr>

                            <td>
                                Credit Status
                            </td>

                            <td>

                                <span class="credit-status <?= $creditStatusClass; ?>">

                                    <?= htmlspecialchars($creditStatus); ?>

                                </span>

                            </td>

                        </tr>

                    </table>

                </div>


                <!-- NOTIFICATION PREFERENCES -->

                <div class="profile-card notification-card">

                    <h3>
                        <i class="fas fa-bell"></i>
                        Notification Preferences
                    </h3>

                    <p class="notification-description">
                        These settings determine which automated customer
                        notifications R&amp;R may send in the future.
                    </p>

                    <table class="profile-table">

                        <tr>

                            <td>
                                Customer Type
                            </td>

                            <td>

                                <?php if ($premiumStatus === 'Premium'): ?>

                                    <span class="credit-status credit-warning">

                                        <i class="fas fa-crown"></i>

                                        Premium Customer

                                    </span>

                                <?php else: ?>

                                    <span class="credit-status">

                                        Regular Customer

                                    </span>

                                <?php endif; ?>

                            </td>

                        </tr>

                        <tr>

                            <td>
                                SMS Notifications
                            </td>

                            <td>

                                <span class="credit-status <?= $smsStatusClass; ?>">

                                    <i class="fas <?= $smsEnabled === 1 ? 'fa-check' : 'fa-ban'; ?>"></i>

                                    <?= $smsStatus; ?>

                                </span>

                            </td>

                        </tr>

                        <tr>

                            <td>
                                New Arrival Alerts
                            </td>

                            <td>

                                <span class="credit-status <?= $arrivalStatusClass; ?>">

                                    <i class="fas <?= $newArrivalAlerts === 1 ? 'fa-check' : 'fa-ban'; ?>"></i>

                                    <?= $arrivalStatus; ?>

                                </span>

                            </td>

                        </tr>

                    </table>

                </div>

            </div>


            <!-- ACTIONS -->

            <div class="customer-buttons">

                <a
                    href="edit.php?id=<?= (int) $customer['id']; ?>"
                    class="customer-btn customer-edit">

                    <i class="fas fa-edit"></i>

                    Edit Customer

                </a>

                <a
                    href="index.php"
                    class="customer-btn customer-back">

                    <i class="fas fa-arrow-left"></i>

                    Back

                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>