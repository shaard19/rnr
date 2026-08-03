```php
<?php

require_once __DIR__ . "/../config/config.php";
require_once __DIR__ . "/../config/database.php";

$currentPage = basename($_SERVER['PHP_SELF']);
$currentUrl  = $_SERVER['REQUEST_URI'];

/*
|--------------------------------------------------------------------------
| Pending Notifications Count
|--------------------------------------------------------------------------
*/

$pendingNotifications = 0;

$notificationQuery = mysqli_query(
    $conn,
    "SELECT COUNT(*) AS total
     FROM notifications
     WHERE status = 'Pending'"
);

if ($notificationQuery) {

    $notificationRow = mysqli_fetch_assoc($notificationQuery);

    $pendingNotifications = (int) ($notificationRow['total'] ?? 0);
}

?>

<div class="sidebar">

    <div class="logo-section">

        <div class="logo-circle">
            R
        </div>

        <h2>R&R Collection</h2>

        <p>POS & Inventory System</p>

    </div>

    <ul class="menu">

        <li class="<?php echo ($currentPage == 'dashboard.php') ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/dashboard.php">

                <i class="fas fa-home"></i>

                <span>Dashboard</span>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/categories/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/categories/index.php">

                <i class="fas fa-layer-group"></i>

                <span>Categories</span>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/products/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/products/index.php">

                <i class="fas fa-box-open"></i>

                <span>Products</span>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/suppliers/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/suppliers/index.php">

                <i class="fas fa-truck"></i>

                <span>Suppliers</span>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/customers/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/customers/index.php">

                <i class="fas fa-users"></i>

                <span>Customers</span>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/sales/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/sales/index.php">

                <i class="fas fa-cash-register"></i>

                <span>Sales / POS</span>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/reports/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/reports/index.php">

                <i class="fas fa-chart-line"></i>

                <span>Reports</span>

            </a>

        </li>


        <!-- NOTIFICATIONS -->

        <li class="<?php echo (strpos($currentUrl, '/notifications/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>notifications/index.php">

                <i class="fas fa-bell"></i>

                <span>Notifications</span>

                <?php if ($pendingNotifications > 0): ?>

                    <span class="notification-badge">

                        <?php echo $pendingNotifications; ?>

                    </span>

                <?php endif; ?>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/users/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/users/index.php">

                <i class="fas fa-user-shield"></i>

                <span>Users</span>

            </a>

        </li>


        <li class="<?php echo (strpos($currentUrl, '/settings/') !== false) ? 'active' : ''; ?>">

            <a href="<?php echo BASE_URL; ?>admin/settings/index.php">

                <i class="fas fa-cogs"></i>

                <span>Settings</span>

            </a>

        </li>


        <li>

            <a href="<?php echo BASE_URL; ?>auth/logout.php">

                <i class="fas fa-sign-out-alt"></i>

                <span>Logout</span>

            </a>

        </li>

    </ul>

</div>
```
