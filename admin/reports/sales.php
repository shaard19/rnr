<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";

/*
|--------------------------------------------------------------------------
| PERMISSION
|--------------------------------------------------------------------------
*/

if (!hasPermission('view_reports')) {
    die("Access Denied");
}

/*
|--------------------------------------------------------------------------
| FILTERS
|--------------------------------------------------------------------------
*/

$from = $_GET['from'] ?? date('Y-m-d');
$to = $_GET['to'] ?? date('Y-m-d');
$payment = $_GET['payment'] ?? '';
$status = $_GET['status'] ?? '';

/*
|--------------------------------------------------------------------------
| VALIDATE DATES
|--------------------------------------------------------------------------
*/

$fromDate = DateTime::createFromFormat('Y-m-d', $from);
$toDate = DateTime::createFromFormat('Y-m-d', $to);

if (
    !$fromDate ||
    !$toDate ||
    $fromDate->format('Y-m-d') !== $from ||
    $toDate->format('Y-m-d') !== $to
) {
    die("Invalid date range.");
}

if ($from > $to) {
    die("From date cannot be later than To date.");
}

/*
|--------------------------------------------------------------------------
| VALID PAYMENT METHODS
|--------------------------------------------------------------------------
*/

$allowedPayments = [
    '',
    'Cash',
    'Lipa na M-Pesa'
];

if (!in_array($payment, $allowedPayments, true)) {
    die("Invalid payment method.");
}

/*
|--------------------------------------------------------------------------
| VALID PAYMENT STATUS
|--------------------------------------------------------------------------
*/

$allowedStatuses = [
    '',
    'Paid',
    'Partial',
    'Credit'
];

if (!in_array($status, $allowedStatuses, true)) {
    die("Invalid payment status.");
}

/*
|--------------------------------------------------------------------------
| ESCAPE VALUES
|--------------------------------------------------------------------------
*/

$fromEscaped = mysqli_real_escape_string(
    $conn,
    $from
);

$toEscaped = mysqli_real_escape_string(
    $conn,
    $to
);

/*
|--------------------------------------------------------------------------
| BUILD CONDITIONS
|--------------------------------------------------------------------------
*/

$conditions = [];

$conditions[] = "
    DATE(s.sale_date)
    BETWEEN '$fromEscaped' AND '$toEscaped'
";

/*
|--------------------------------------------------------------------------
| PAYMENT FILTER
|--------------------------------------------------------------------------
*/

if ($payment !== '') {

    $paymentEscaped = mysqli_real_escape_string(
        $conn,
        $payment
    );

    $conditions[] =
        "s.payment_method = '$paymentEscaped'";
}

/*
|--------------------------------------------------------------------------
| STATUS FILTER
|--------------------------------------------------------------------------
*/

if ($status !== '') {

    $statusEscaped = mysqli_real_escape_string(
        $conn,
        $status
    );

    $conditions[] =
        "s.payment_status = '$statusEscaped'";
}

$where = implode(
    " AND ",
    $conditions
);

/*
|--------------------------------------------------------------------------
| SUMMARY
|--------------------------------------------------------------------------
*/

$summarySql = "

    SELECT

        COUNT(*) AS transactions,

        COALESCE(
            SUM(s.total),
            0
        ) AS total,

        COALESCE(
            SUM(s.amount_paid),
            0
        ) AS paid,

        COALESCE(
            SUM(s.balance),
            0
        ) AS balance

    FROM sales s

    WHERE $where

";

$summaryResult = mysqli_query(
    $conn,
    $summarySql
);

if (!$summaryResult) {

    die(
        "SUMMARY SQL ERROR: " .
        mysqli_error($conn)
    );
}

$summary = mysqli_fetch_assoc(
    $summaryResult
);

/*
|--------------------------------------------------------------------------
| SALES TRANSACTIONS
|--------------------------------------------------------------------------
*/

$salesSql = "

    SELECT

        s.id,

        s.invoice_no,

        s.total,

        s.payment_method,

        s.payment_status,

        s.amount_paid,

        s.balance,

        s.sale_date,

        COALESCE(
            c.customer_name,
            'Walk In Customer'
        ) AS customer_name

    FROM sales s

    LEFT JOIN customers c
        ON c.id = s.customer_id

    WHERE $where

    ORDER BY s.sale_date DESC

";

$sales = mysqli_query(
    $conn,
    $salesSql
);

if (!$sales) {

    die(
        "SALES SQL ERROR: " .
        mysqli_error($conn)
    );
}

/*
|--------------------------------------------------------------------------
| EXPORT URL
|--------------------------------------------------------------------------
*/

$exportUrl =
    "export_sales_excel.php?" .
    "from=" . urlencode($from) .
    "&to=" . urlencode($to) .
    "&payment=" . urlencode($payment) .
    "&status=" . urlencode($status);

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
    Sales Report - R&R Collection
</title>

<link
    rel="stylesheet"
    href="../../assets/css/style.css"
>

<link
    rel="stylesheet"
    href="../../assets/css/reports.css"
>

<style>

/* =====================================================
   REPORT ACTIONS
===================================================== */

.report-actions {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.btn-back,
.btn-export {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 7px;

    padding: 10px 16px;

    border-radius: 8px;

    text-decoration: none;

    font-weight: 600;

    transition: all 0.2s ease;

    white-space: nowrap;
}

.btn-back {
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #cbd5e1;
}

.btn-back:hover {
    background: #e2e8f0;
}

.btn-export {
    background: #166534;
    color: #ffffff;
    border: 1px solid #166534;
}

.btn-export:hover {
    background: #14532d;
}

/* =====================================================
   STATUS BADGES
===================================================== */

.status-badge {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 999px;
    font-size: 12px;
    font-weight: 700;
}

.status-paid {
    background: #dcfce7;
    color: #166534;
}

.status-partial {
    background: #fef3c7;
    color: #92400e;
}

.status-credit {
    background: #fee2e2;
    color: #991b1b;
}

/* =====================================================
   REPORT HEADER
===================================================== */

.reports-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

/* =====================================================
   MOBILE
===================================================== */

@media (max-width: 768px) {

    .reports-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .report-actions {
        width: 100%;
    }

    .btn-back,
    .btn-export {
        flex: 1;
    }

}

</style>

</head>

<body>

<div class="reports-container">

<!-- =====================================================
     HEADER
====================================================== -->

<div class="reports-header">

    <div>

        <h1>
            Sales Report
        </h1>

        <p>
            Detailed sales performance
        </p>

    </div>

    <div class="report-actions">

        <a
    href="../dashboard.php"
    class="btn-back"
>
    ← Back to Dashboard
</a>

        <a
            href="<?= htmlspecialchars($exportUrl); ?>"
            target="_blank"
            rel="noopener"
            class="btn-export"
        >
            📊 Export to Excel
        </a>

        <div class="report-date">

            <?= date('d M Y'); ?>

        </div>

    </div>

</div>

<!-- =====================================================
     FILTER
====================================================== -->

<div class="report-filter">

<form method="GET">

    <div>

        <label>
            From
        </label>

        <input
            type="date"
            name="from"
            value="<?= htmlspecialchars($from); ?>"
            required
        >

    </div>

    <div>

        <label>
            To
        </label>

        <input
            type="date"
            name="to"
            value="<?= htmlspecialchars($to); ?>"
            required
        >

    </div>

    <div>

        <label>
            Payment Method
        </label>

        <select name="payment">

            <option value="">
                All Payments
            </option>

            <option
                value="Cash"
                <?= $payment === 'Cash' ? 'selected' : ''; ?>
            >
                Cash
            </option>

            <option
                value="Lipa na M-Pesa"
                <?= $payment === 'Lipa na M-Pesa' ? 'selected' : ''; ?>
            >
                Lipa na M-Pesa
            </option>

        </select>

    </div>

    <div>

        <label>
            Payment Status
        </label>

        <select name="status">

            <option value="">
                All Statuses
            </option>

            <option
                value="Paid"
                <?= $status === 'Paid' ? 'selected' : ''; ?>
            >
                Paid
            </option>

            <option
                value="Partial"
                <?= $status === 'Partial' ? 'selected' : ''; ?>
            >
                Partial
            </option>

            <option
                value="Credit"
                <?= $status === 'Credit' ? 'selected' : ''; ?>
            >
                Credit
            </option>

        </select>

    </div>

    <button type="submit">
        Generate Report
    </button>

    <a href="sales.php">
        Reset
    </a>

</form>

</div>

<!-- =====================================================
     SUMMARY CARDS
====================================================== -->

<div class="report-cards">

    <!-- TRANSACTIONS -->

    <div class="report-card">

        <div class="card-icon">
            🧾
        </div>

        <div>

            <span>
                Transactions
            </span>

            <h2>

                <?= number_format(
                    (int)$summary['transactions']
                ); ?>

            </h2>

        </div>

    </div>


    <!-- TOTAL SALES -->

    <div class="report-card">

        <div class="card-icon">
            💰
        </div>

        <div>

            <span>
                Total Sales
            </span>

            <h2>

                KSh <?= number_format(
                    (float)$summary['total'],
                    2
                ); ?>

            </h2>

        </div>

    </div>


    <!-- AMOUNT PAID -->

    <div class="report-card">

        <div class="card-icon">
            💵
        </div>

        <div>

            <span>
                Amount Paid
            </span>

            <h2>

                KSh <?= number_format(
                    (float)$summary['paid'],
                    2
                ); ?>

            </h2>

        </div>

    </div>


    <!-- OUTSTANDING -->

    <div class="report-card">

        <div class="card-icon">
            ⚠️
        </div>

        <div>

            <span>
                Outstanding
            </span>

            <h2>

                KSh <?= number_format(
                    (float)$summary['balance'],
                    2
                ); ?>

            </h2>

        </div>

    </div>

</div>

<!-- =====================================================
     SALES TABLE
====================================================== -->

<div class="recent-sales">

    <div class="section-header">

        <div>

            <h2>
                Sales Transactions
            </h2>

            <p>

                <?= htmlspecialchars($from); ?>

                to

                <?= htmlspecialchars($to); ?>

            </p>

        </div>

    </div>


    <div class="table-wrapper">

        <table>

            <thead>

                <tr>

                    <th>
                        Invoice
                    </th>

                    <th>
                        Customer
                    </th>

                    <th>
                        Payment
                    </th>

                    <th>
                        Status
                    </th>

                    <th>
                        Total
                    </th>

                    <th>
                        Paid
                    </th>

                    <th>
                        Balance
                    </th>

                    <th>
                        Date
                    </th>

                </tr>

            </thead>

            <tbody>

            <?php if (mysqli_num_rows($sales) > 0): ?>

                <?php while ($sale = mysqli_fetch_assoc($sales)): ?>

                    <tr>

                        <!-- INVOICE -->

                        <td>

                            <strong>

                                <?= htmlspecialchars(
                                    $sale['invoice_no']
                                ); ?>

                            </strong>

                        </td>


                        <!-- CUSTOMER -->

                        <td>

                            <?= htmlspecialchars(
                                $sale['customer_name']
                            ); ?>

                        </td>


                        <!-- PAYMENT -->

                        <td>

                            <?= htmlspecialchars(
                                $sale['payment_method']
                            ); ?>

                        </td>


                        <!-- STATUS -->

                        <td>

                            <?php

                            $saleStatus =
                                $sale['payment_status'];

                            $statusClass = '';

                            if ($saleStatus === 'Paid') {
                                $statusClass = 'status-paid';
                            } elseif ($saleStatus === 'Partial') {
                                $statusClass = 'status-partial';
                            } elseif ($saleStatus === 'Credit') {
                                $statusClass = 'status-credit';
                            }

                            ?>

                            <span
                                class="status-badge <?= $statusClass; ?>"
                            >

                                <?= htmlspecialchars(
                                    $saleStatus
                                ); ?>

                            </span>

                        </td>


                        <!-- TOTAL -->

                        <td>

                            <strong>

                                KSh <?= number_format(
                                    (float)$sale['total'],
                                    2
                                ); ?>

                            </strong>

                        </td>


                        <!-- PAID -->

                        <td>

                            KSh <?= number_format(
                                (float)$sale['amount_paid'],
                                2
                            ); ?>

                        </td>


                        <!-- BALANCE -->

                        <td>

                            <?php if (
                                (float)$sale['balance'] > 0
                            ): ?>

                                <span class="balance-warning">

                                    KSh <?= number_format(
                                        (float)$sale['balance'],
                                        2
                                    ); ?>

                                </span>

                            <?php else: ?>

                                <span class="balance-clear">

                                    KSh 0.00

                                </span>

                            <?php endif; ?>

                        </td>


                        <!-- DATE -->

                        <td>

                            <?= date(
                                'd M Y H:i',
                                strtotime(
                                    $sale['sale_date']
                                )
                            ); ?>

                        </td>

                    </tr>

                <?php endwhile; ?>

            <?php else: ?>

                <tr>

                    <td
                        colspan="8"
                        class="empty-state"
                    >

                        No sales found for the selected period.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

</div>

</body>

</html>