<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../config/session.php";
require_once "../config/database.php";
require_once "../config/permissions.php";

/*
|--------------------------------------------------------------------------
| Admin Notification Center
|--------------------------------------------------------------------------
*/

if (!hasPermission('view_notifications')) {
    die("Access Denied");
}

/*
|--------------------------------------------------------------------------
| Summary Counts
|--------------------------------------------------------------------------
*/

$pendingCount = 0;
$creditCount = 0;
$arrivalCount = 0;
$sentCount = 0;
$failedCount = 0;

/* Pending */

$result = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE status = 'Pending'
    "
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $pendingCount = (int)($row['total'] ?? 0);
}

/* Credit reminders */

$result = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE notification_type = 'Credit Reminder'
    AND status = 'Pending'
    "
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $creditCount = (int)($row['total'] ?? 0);
}

/* New arrivals */

$result = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE notification_type = 'New Arrival'
    AND status = 'Pending'
    "
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $arrivalCount = (int)($row['total'] ?? 0);
}

/* Sent */

$result = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE status = 'Sent'
    "
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $sentCount = (int)($row['total'] ?? 0);
}

/* Failed */

$result = mysqli_query(
    $conn,
    "
    SELECT COUNT(*) AS total
    FROM notifications
    WHERE status = 'Failed'
    "
);

if ($result) {
    $row = mysqli_fetch_assoc($result);
    $failedCount = (int)($row['total'] ?? 0);
}

/*
|--------------------------------------------------------------------------
| Recent Notifications
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        n.id,
        n.notification_type,
        n.message,
        n.phone,
        n.status,
        n.created_at,
        c.customer_name
    FROM notifications n

    INNER JOIN customers c
        ON c.id = n.customer_id

    ORDER BY n.id DESC

    LIMIT 50
";

$notifications = mysqli_query($conn, $sql);

if (!$notifications) {
    die(
        "Unable to load notifications: "
        . mysqli_error($conn)
    );
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
    Notifications | R&R Collection POS
</title>

<link
    rel="stylesheet"
    href="../assets/css/dashboard.css"
>

<link
    rel="stylesheet"
    href="../assets/css/sidebar.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

<style>

.notification-page {
    padding-bottom: 40px;
}

.notification-header {
    margin-bottom: 25px;
}

.notification-header h1 {
    margin: 0;
    color: #172033;
}

.notification-header p {
    margin: 7px 0 0;
    color: #667085;
    font-size: 14px;
}

/*
|--------------------------------------------------------------------------
| Summary Cards
|--------------------------------------------------------------------------
*/

.notification-summary {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 18px;
    margin-bottom: 25px;
}

.notification-stat {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.notification-stat-icon {
    width: 45px;
    height: 45px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #eef4ff;
    color: #2563eb;
    font-size: 18px;
}

.notification-stat strong {
    display: block;
    font-size: 24px;
    color: #172033;
}

.notification-stat span {
    font-size: 13px;
    color: #667085;
}

/*
|--------------------------------------------------------------------------
| Main Panel
|--------------------------------------------------------------------------
*/

.notification-panel {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    overflow: hidden;
}

.notification-panel-header {
    padding: 20px 22px;
    border-bottom: 1px solid #edf0f4;
}

.notification-panel-header h2 {
    margin: 0;
    font-size: 18px;
    color: #172033;
}

.notification-panel-header p {
    margin: 5px 0 0;
    color: #667085;
    font-size: 13px;
}

/*
|--------------------------------------------------------------------------
| Table
|--------------------------------------------------------------------------
*/

.notification-table-wrapper {
    overflow-x: auto;
}

.notification-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
}

.notification-table th {
    background: #f8fafc;
    color: #475467;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .03em;
    text-align: left;
    padding: 14px 16px;
}

.notification-table td {
    padding: 16px;
    border-top: 1px solid #edf0f4;
    color: #344054;
    font-size: 13px;
    vertical-align: top;
}

.customer-name {
    font-weight: 600;
    color: #172033;
}

.phone {
    color: #667085;
}

.message {
    max-width: 380px;
    line-height: 1.5;
}

/*
|--------------------------------------------------------------------------
| Badges
|--------------------------------------------------------------------------
*/

.notification-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 10px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 600;
}

.badge-pending {
    background: #fff7e6;
    color: #b54708;
}

.badge-sent {
    background: #ecfdf3;
    color: #027a48;
}

.badge-failed {
    background: #fef3f2;
    color: #b42318;
}

.badge-credit {
    background: #eef4ff;
    color: #175cd3;
}

.badge-arrival {
    background: #f4f3ff;
    color: #6938ef;
}

/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

.notification-empty {
    text-align: center;
    padding: 60px 20px;
    color: #667085;
}

.notification-empty i {
    font-size: 42px;
    margin-bottom: 15px;
    color: #98a2b3;
}

.notification-empty h3 {
    margin: 0 0 6px;
    color: #344054;
}

/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 1000px) {

    .notification-summary {
        grid-template-columns: repeat(2, 1fr);
    }

}

@media (max-width: 600px) {

    .notification-summary {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php include "../includes/topbar.php"; ?>

<div class="container notification-page">

    <!-- HEADER -->

    <div class="notification-header">

        <h1>
            <i class="fa-solid fa-bell"></i>
            Notifications
        </h1>

        <p>
            Monitor customer notifications generated by R&amp;R.
        </p>

    </div>


    <!-- SUMMARY -->

    <div class="notification-summary">

        <div class="notification-stat">

            <div class="notification-stat-icon">

                <i class="fa-solid fa-bell"></i>

            </div>

            <div>

                <strong>
                    <?= $pendingCount; ?>
                </strong>

                <span>
                    Pending
                </span>

            </div>

        </div>


        <div class="notification-stat">

            <div class="notification-stat-icon">

                <i class="fa-solid fa-credit-card"></i>

            </div>

            <div>

                <strong>
                    <?= $creditCount; ?>
                </strong>

                <span>
                    Credit Reminders
                </span>

            </div>

        </div>


        <div class="notification-stat">

            <div class="notification-stat-icon">

                <i class="fa-solid fa-box-open"></i>

            </div>

            <div>

                <strong>
                    <?= $arrivalCount; ?>
                </strong>

                <span>
                    New Arrivals
                </span>

            </div>

        </div>


        <div class="notification-stat">

            <div class="notification-stat-icon">

                <i class="fa-solid fa-paper-plane"></i>

            </div>

            <div>

                <strong>
                    <?= $sentCount; ?>
                </strong>

                <span>
                    Sent
                </span>

            </div>

        </div>

    </div>


    <!-- NOTIFICATION LIST -->

    <div class="notification-panel">

        <div class="notification-panel-header">

            <h2>
                Notification History
            </h2>

            <p>
                Latest 50 generated notifications.
            </p>

        </div>


        <div class="notification-table-wrapper">

            <?php if (mysqli_num_rows($notifications) > 0): ?>

                <table class="notification-table">

                    <thead>

                        <tr>

                            <th>
                                Customer
                            </th>

                            <th>
                                Type
                            </th>

                            <th>
                                Message
                            </th>

                            <th>
                                Phone
                            </th>

                            <th>
                                Status
                            </th>

                            <th>
                                Created
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while ($notification = mysqli_fetch_assoc($notifications)): ?>

                        <tr>

                            <td>

                                <div class="customer-name">

                                    <?= htmlspecialchars(
                                        $notification['customer_name']
                                    ); ?>

                                </div>

                            </td>


                            <td>

                                <?php if (
                                    $notification['notification_type']
                                    === 'Credit Reminder'
                                ): ?>

                                    <span class="notification-badge badge-credit">

                                        <i class="fa-solid fa-credit-card"></i>

                                        Credit Reminder

                                    </span>

                                <?php else: ?>

                                    <span class="notification-badge badge-arrival">

                                        <i class="fa-solid fa-box-open"></i>

                                        <?= htmlspecialchars(
                                            $notification['notification_type']
                                        ); ?>

                                    </span>

                                <?php endif; ?>

                            </td>


                            <td>

                                <div class="message">

                                    <?= htmlspecialchars(
                                        $notification['message']
                                    ); ?>

                                </div>

                            </td>


                            <td>

                                <span class="phone">

                                    <?= htmlspecialchars(
                                        $notification['phone'] ?? ''
                                    ); ?>

                                </span>

                            </td>


                            <td>

                                <?php

                                $status = $notification['status'];

                                $statusClass = match ($status) {

                                    'Sent' =>
                                        'badge-sent',

                                    'Failed' =>
                                        'badge-failed',

                                    default =>
                                        'badge-pending'

                                };

                                ?>

                                <span
                                    class="notification-badge <?= $statusClass; ?>"
                                >

                                    <?= htmlspecialchars($status); ?>

                                </span>

                            </td>


                            <td>

                                <?= htmlspecialchars(
                                    $notification['created_at']
                                ); ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="notification-empty">

                    <i class="fa-regular fa-bell-slash"></i>

                    <h3>
                        No notifications yet
                    </h3>

                    <p>
                        Generated customer notifications will appear here.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>

</div>

</div>

</body>

</html>