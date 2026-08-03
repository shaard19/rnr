<?php

/*
|--------------------------------------------------------------------------
| R&R COLLECTION POS
| Admin Dashboard
|--------------------------------------------------------------------------
*/

require_once "../config/session.php";
require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| Safe database helper
|--------------------------------------------------------------------------
*/

function dashboardQuery($conn, $sql)
{
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        error_log("R&R Dashboard SQL Error: " . mysqli_error($conn));
        return false;
    }

    return $result;
}

function dashboardCount($conn, $sql)
{
    $result = dashboardQuery($conn, $sql);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (int)($row['total'] ?? 0);
}

function dashboardAmount($conn, $sql)
{
    $result = dashboardQuery($conn, $sql);

    if (!$result) {
        return 0;
    }

    $row = mysqli_fetch_assoc($result);

    return (float)($row['total'] ?? 0);
}


/*
|--------------------------------------------------------------------------
| BASIC STATISTICS
|--------------------------------------------------------------------------
*/

$totalProducts = dashboardCount(
    $conn,
    "SELECT COUNT(*) AS total FROM products"
);

$activeProducts = dashboardCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'Active'"
);

$totalCategories = dashboardCount(
    $conn,
    "SELECT COUNT(*) AS total FROM categories"
);

$totalCustomers = dashboardCount(
    $conn,
    "SELECT COUNT(*) AS total FROM customers"
);

$lowStockProducts = dashboardCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM products
     WHERE status = 'Active'
     AND quantity > 0
     AND quantity <= reorder_level"
);


/*
|--------------------------------------------------------------------------
| TODAY'S SALES
|--------------------------------------------------------------------------
*/

$todaySales = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()"
);

$todayTransactions = dashboardCount(
    $conn,
    "SELECT COUNT(*) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()"
);

$todayAmountPaid = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(amount_paid), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()"
);

$todayOutstanding = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(balance), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()
     AND balance > 0"
);


/*
|--------------------------------------------------------------------------
| PAYMENT METHOD SUMMARY
|--------------------------------------------------------------------------
*/

$cashSales = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()
     AND payment_method = 'Cash'"
);

$mpesaSales = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()
     AND payment_method = 'Lipa na M-Pesa'"
);


/*
|--------------------------------------------------------------------------
| PAYMENT STATUS SUMMARY
|--------------------------------------------------------------------------
*/

$paidSales = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()
     AND payment_status = 'Paid'"
);

$partialSales = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()
     AND payment_status = 'Partial'"
);

$creditSales = dashboardAmount(
    $conn,
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM sales
     WHERE DATE(sale_date) = CURDATE()
     AND payment_status = 'Credit'"
);


/*
|--------------------------------------------------------------------------
| PRODUCTS BY CATEGORY
|--------------------------------------------------------------------------
*/

$categoryData = [];

$categoryQuery = dashboardQuery(
    $conn,
    "SELECT
        c.category_name,
        COUNT(p.id) AS product_count
     FROM categories c
     LEFT JOIN products p
        ON p.category_id = c.id
        AND p.status = 'Active'
     GROUP BY c.id, c.category_name
     HAVING product_count > 0
     ORDER BY product_count DESC"
);

if ($categoryQuery) {

    while ($row = mysqli_fetch_assoc($categoryQuery)) {

        $categoryData[] = [
            'name'  => $row['category_name'],
            'count' => (int)$row['product_count']
        ];
    }
}


/*
|--------------------------------------------------------------------------
| BUILD PIE CHART GRADIENT
|--------------------------------------------------------------------------
*/

$chartColors = [
    '#2563EB',
    '#16A34A',
    '#EA580C',
    '#9333EA',
    '#0891B2',
    '#DC2626',
    '#CA8A04',
    '#4F46E5'
];

$totalCategoryProducts = 0;

foreach ($categoryData as $category) {
    $totalCategoryProducts += $category['count'];
}

$gradientParts = [];
$currentDegree = 0;

foreach ($categoryData as $index => $category) {

    if ($totalCategoryProducts <= 0) {
        break;
    }

    $percentage = ($category['count'] / $totalCategoryProducts) * 100;
    $degrees = ($percentage / 100) * 360;

    $start = $currentDegree;
    $end = $currentDegree + $degrees;

    $color = $chartColors[$index % count($chartColors)];

    $gradientParts[] = "{$color} {$start}deg {$end}deg";

    $currentDegree = $end;
}

$pieGradient = !empty($gradientParts)
    ? "conic-gradient(" . implode(',', $gradientParts) . ")"
    : "#E5E7EB";


/*
|--------------------------------------------------------------------------
| RECENT TRANSACTIONS
|--------------------------------------------------------------------------
*/

$recentSales = dashboardQuery(
    $conn,
    "SELECT
        id,
        invoice_no,
        total,
        payment_method,
        payment_status,
        amount_paid,
        balance,
        sale_date
     FROM sales
     ORDER BY sale_date DESC
     LIMIT 8"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Dashboard | R&R Collection POS</title>

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
    href="../assets/css/forms.css"
>

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
>

</head>

<body>

<?php include "../includes/sidebar.php"; ?>

<div class="main">

<?php include "../includes/topbar.php"; ?>

<div class="container">


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="page-title">

    <div>

        <h1>
            <i class="fa-solid fa-gauge-high"></i>
            Dashboard
        </h1>

        <p>
            Welcome back,
            <strong>
                <?= htmlspecialchars($_SESSION['fullname'] ?? 'User'); ?>
            </strong>
        </p>

    </div>

    <div class="dashboard-status">

        <span class="dashboard-badge badge-success">

            <i class="fa-solid fa-circle-check"></i>

            System Online

        </span>

        <?php if ($lowStockProducts > 0): ?>

            <span class="dashboard-badge badge-warning">

                <i class="fa-solid fa-triangle-exclamation"></i>

                <?= $lowStockProducts; ?> Low Stock

            </span>

        <?php else: ?>

            <span class="dashboard-badge badge-success">

                <i class="fa-solid fa-boxes-stacked"></i>

                Stock Levels Good

            </span>

        <?php endif; ?>

    </div>

</div>


<!-- =========================================================
     STATISTICS CARDS
========================================================= -->

<div class="cards">


    <div class="card">

        <div>

            <small>Total Products</small>

            <h2><?= $totalProducts; ?></h2>

        </div>

        <div class="card-icon blue">

            <i class="fa-solid fa-box-open"></i>

        </div>

    </div>


    <div class="card">

        <div>

            <small>Active Products</small>

            <h2><?= $activeProducts; ?></h2>

        </div>

        <div class="card-icon green">

            <i class="fa-solid fa-boxes-stacked"></i>

        </div>

    </div>


    <div class="card">

        <div>

            <small>Customers</small>

            <h2><?= $totalCustomers; ?></h2>

        </div>

        <div class="card-icon orange">

            <i class="fa-solid fa-users"></i>

        </div>

    </div>


    <div class="card">

        <div>

            <small>Categories</small>

            <h2><?= $totalCategories; ?></h2>

        </div>

        <div class="card-icon blue">

            <i class="fa-solid fa-layer-group"></i>

        </div>

    </div>


    <div class="card">

        <div>

            <small>Low Stock</small>

            <h2><?= $lowStockProducts; ?></h2>

        </div>

        <div class="card-icon orange">

            <i class="fa-solid fa-triangle-exclamation"></i>

        </div>

    </div>


    <div class="card">

        <div>

            <small>Today's Sales</small>

            <h2>
                KSh <?= number_format($todaySales, 2); ?>
            </h2>

        </div>

        <div class="card-icon red">

            <i class="fa-solid fa-sack-dollar"></i>

        </div>

    </div>

</div>


<!-- =========================================================
     TODAY'S FINANCIAL SUMMARY
========================================================= -->

<div class="analytics-grid">


    <div class="panel">

        <div class="panel-heading">

            <h3>
                <i class="fa-solid fa-chart-line"></i>
                Today's Financial Summary
            </h3>

        </div>

        <div class="summary-grid">

            <div class="summary-item">

                <span>
                    <i class="fa-solid fa-receipt"></i>
                    Transactions
                </span>

                <strong>
                    <?= $todayTransactions; ?>
                </strong>

            </div>


            <div class="summary-item">

                <span>
                    <i class="fa-solid fa-money-bill-wave"></i>
                    Total Sales
                </span>

                <strong>
                    KSh <?= number_format($todaySales, 2); ?>
                </strong>

            </div>


            <div class="summary-item">

                <span>
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                    Amount Received
                </span>

                <strong>
                    KSh <?= number_format($todayAmountPaid, 2); ?>
                </strong>

            </div>


            <div class="summary-item danger-item">

                <span>
                    <i class="fa-solid fa-clock"></i>
                    Outstanding
                </span>

                <strong>
                    KSh <?= number_format($todayOutstanding, 2); ?>
                </strong>

            </div>

        </div>


        <div class="payment-breakdown">

            <div class="breakdown-row">

                <span>
                    <i class="fa-solid fa-money-bill"></i>
                    Cash
                </span>

                <strong>
                    KSh <?= number_format($cashSales, 2); ?>
                </strong>

            </div>


            <div class="breakdown-row">

                <span>
                    <i class="fa-solid fa-mobile-screen-button"></i>
                    Lipa na M-Pesa
                </span>

                <strong>
                    KSh <?= number_format($mpesaSales, 2); ?>
                </strong>

            </div>

        </div>

    </div>


    <!-- =====================================================
         PRODUCT CATEGORY PIE CHART
    ====================================================== -->

    <div class="panel chart-panel">

        <div class="panel-heading">

            <h3>
                <i class="fa-solid fa-chart-pie"></i>
                Products by Category
            </h3>

        </div>

        <?php if (!empty($categoryData)): ?>

            <div class="pie-chart-wrapper">

                <div
                    class="pie-chart"
                    style="background: <?= htmlspecialchars($pieGradient); ?>;"
                >

                    <div class="pie-hole">

                        <strong>
                            <?= $totalCategoryProducts; ?>
                        </strong>

                        <small>
                            Active Products
                        </small>

                    </div>

                </div>


                <div class="chart-legend">

                    <?php foreach ($categoryData as $index => $category): ?>

                        <?php

                        $color =
                            $chartColors[$index % count($chartColors)];

                        $percentage =
                            $totalCategoryProducts > 0
                            ? ($category['count'] / $totalCategoryProducts) * 100
                            : 0;

                        ?>

                        <div class="legend-item">

                            <span
                                class="legend-dot"
                                style="background: <?= $color; ?>;"
                            ></span>

                            <span class="legend-name">

                                <?= htmlspecialchars($category['name']); ?>

                            </span>

                            <strong>

                                <?= $category['count']; ?>

                                <small>
                                    (<?= number_format($percentage, 1); ?>%)
                                </small>

                            </strong>

                        </div>

                    <?php endforeach; ?>

                </div>

            </div>

        <?php else: ?>

            <div class="empty-state">

                <i class="fa-solid fa-chart-pie"></i>

                <p>
                    No active products available for the chart.
                </p>

            </div>

        <?php endif; ?>

    </div>

</div>


<!-- =========================================================
     PAYMENT STATUS
========================================================= -->

<div class="panel payment-status-panel">

    <div class="panel-heading">

        <h3>
            <i class="fa-solid fa-wallet"></i>
            Today's Payment Status
        </h3>

    </div>

    <div class="status-grid">


        <div class="status-box paid">

            <i class="fa-solid fa-circle-check"></i>

            <div>

                <small>Paid</small>

                <strong>
                    KSh <?= number_format($paidSales, 2); ?>
                </strong>

            </div>

        </div>


        <div class="status-box partial">

            <i class="fa-solid fa-circle-half-stroke"></i>

            <div>

                <small>Partial</small>

                <strong>
                    KSh <?= number_format($partialSales, 2); ?>
                </strong>

            </div>

        </div>


        <div class="status-box credit">

            <i class="fa-solid fa-credit-card"></i>

            <div>

                <small>Credit</small>

                <strong>
                    KSh <?= number_format($creditSales, 2); ?>
                </strong>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     RECENT TRANSACTIONS
========================================================= -->

<div class="panel">

    <div class="panel-heading">

        <h3>

            <i class="fa-solid fa-clock-rotate-left"></i>

            Recent Transactions

        </h3>

        <a
            href="sales/index.php"
            class="panel-link"
        >
            View Sales
        </a>

    </div>


    <div class="table-wrapper">

        <table class="recent-table">

            <thead>

                <tr>

                    <th>Invoice</th>

                    <th>Total</th>

                    <th>Paid</th>

                    <th>Balance</th>

                    <th>Method</th>

                    <th>Status</th>

                    <th>Date</th>

                </tr>

            </thead>

            <tbody>

            <?php if ($recentSales && mysqli_num_rows($recentSales) > 0): ?>

                <?php while ($sale = mysqli_fetch_assoc($recentSales)): ?>

                    <tr>

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $sale['invoice_no'] ?? 'N/A'
                                ); ?>

                            </strong>

                        </td>

                        <td>

                            KSh <?= number_format(
                                (float)$sale['total'],
                                2
                            ); ?>

                        </td>

                        <td>

                            KSh <?= number_format(
                                (float)$sale['amount_paid'],
                                2
                            ); ?>

                        </td>

                        <td>

                            KSh <?= number_format(
                                (float)$sale['balance'],
                                2
                            ); ?>

                        </td>

                        <td>

                            <?php if ($sale['payment_method'] === 'Lipa na M-Pesa'): ?>

                                <span class="method-badge mpesa">

                                    <i class="fa-solid fa-mobile-screen"></i>

                                    M-Pesa

                                </span>

                            <?php else: ?>

                                <span class="method-badge cash">

                                    <i class="fa-solid fa-money-bill"></i>

                                    Cash

                                </span>

                            <?php endif; ?>

                        </td>

                        <td>

                            <?php

                            $statusClass = strtolower(
                                $sale['payment_status']
                            );

                            ?>

                            <span
                                class="status-badge <?= htmlspecialchars($statusClass); ?>"
                            >

                                <?= htmlspecialchars(
                                    $sale['payment_status']
                                ); ?>

                            </span>

                        </td>

                        <td>

                            <?= date(
                                'd M Y, H:i',
                                strtotime($sale['sale_date'])
                            ); ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="7"
                        class="empty-table"
                    >

                        <i class="fa-solid fa-receipt"></i>

                        No transactions recorded yet.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================================
     QUICK ACTIONS
========================================================= -->

<div class="panel">

    <div class="panel-heading">

        <h3>

            <i class="fa-solid fa-bolt"></i>

            Quick Actions

        </h3>

    </div>


    <div class="quick-actions">


        <a
            href="categories/index.php"
            class="quick-action"
        >

            <i class="fa-solid fa-layer-group"></i>

            <div>

                <strong>Categories</strong>

                <small>Manage categories</small>

            </div>

        </a>


        <a
            href="products/index.php"
            class="quick-action"
        >

            <i class="fa-solid fa-box-open"></i>

            <div>

                <strong>Products</strong>

                <small>Manage inventory</small>

            </div>

        </a>


        <a
            href="customers/index.php"
            class="quick-action"
        >

            <i class="fa-solid fa-users"></i>

            <div>

                <strong>Customers</strong>

                <small>Manage customers</small>

            </div>

        </a>


        <a
            href="products/add.php"
            class="quick-action"
        >

            <i class="fa-solid fa-plus"></i>

            <div>

                <strong>Add Product</strong>

                <small>Register new stock</small>

            </div>

        </a>


        <a
            href="customers/add.php"
            class="quick-action"
        >

            <i class="fa-solid fa-user-plus"></i>

            <div>

                <strong>Add Customer</strong>

                <small>Register customer</small>

            </div>

        </a>


        <a
            href="../cashier/dashboard.php"
            class="quick-action sales-action"
        >

            <i class="fa-solid fa-cart-shopping"></i>

            <div>

                <strong>Open POS</strong>

                <small>Process a sale</small>
            </div>

        </a>

    </div>

</div>


</div>

</div>

</body>

</html>