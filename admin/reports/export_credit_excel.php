<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";
require_once "../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


/*
|--------------------------------------------------------------------------
| Permission Check
|--------------------------------------------------------------------------
*/

if (!hasPermission('view_reports')) {
    die("Access Denied");
}


/*
|--------------------------------------------------------------------------
| Date Filters
|--------------------------------------------------------------------------
*/

$from = $_GET['from'] ?? date('Y-m-d');
$to   = $_GET['to'] ?? date('Y-m-d');


/*
|--------------------------------------------------------------------------
| Validate Dates
|--------------------------------------------------------------------------
*/

$from_check = DateTime::createFromFormat('Y-m-d', $from);
$to_check   = DateTime::createFromFormat('Y-m-d', $to);


if (
    !$from_check ||
    !$to_check ||
    $from_check->format('Y-m-d') !== $from ||
    $to_check->format('Y-m-d') !== $to
) {

    $from = date('Y-m-d');
    $to   = date('Y-m-d');

}


/*
|--------------------------------------------------------------------------
| Correct Reversed Dates
|--------------------------------------------------------------------------
*/

if ($from > $to) {

    $temp = $from;
    $from = $to;
    $to = $temp;

}


/*
|--------------------------------------------------------------------------
| Customer Credit Query
|--------------------------------------------------------------------------
|
| Period purchases/payment:
|     sales.sale_date
|
| Current outstanding:
|     customers.credit_balance
|
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        c.id,
        c.customer_name,
        c.credit_balance,

        COUNT(s.id) AS transactions,

        COALESCE(
            SUM(s.total),
            0
        ) AS total_purchases,

        COALESCE(
            SUM(s.amount_paid),
            0
        ) AS total_paid

    FROM customers c

    LEFT JOIN sales s

        ON s.customer_id = c.id

        AND DATE(s.sale_date)
            BETWEEN ? AND ?

    WHERE
        c.status = 'Active'

    GROUP BY

        c.id,
        c.customer_name,
        c.credit_balance

    HAVING

        transactions > 0
        OR c.credit_balance > 0

    ORDER BY

        c.credit_balance DESC,
        c.customer_name ASC
";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die(
        "Customer Credit Excel Error: " .
        htmlspecialchars(mysqli_error($conn))
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $from,
    $to
);


if (!mysqli_stmt_execute($stmt)) {

    die(
        "Unable to generate Customer Credit Excel: " .
        htmlspecialchars(mysqli_stmt_error($stmt))
    );

}


$result = mysqli_stmt_get_result($stmt);


/*
|--------------------------------------------------------------------------
| Create Spreadsheet
|--------------------------------------------------------------------------
*/

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Customer Credit');


/*
|--------------------------------------------------------------------------
| Report Title
|--------------------------------------------------------------------------
*/

$sheet->mergeCells('A1:F1');

$sheet->setCellValue(
    'A1',
    'R&R COLLECTION - CUSTOMER CREDIT REPORT'
);


$sheet->getStyle('A1')
    ->getFont()
    ->setBold(true)
    ->setSize(16);


$sheet->getStyle('A1')
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );


/*
|--------------------------------------------------------------------------
| Date Range
|--------------------------------------------------------------------------
*/

$sheet->mergeCells('A2:F2');

$sheet->setCellValue(
    'A2',
    'Reporting Period: ' .
    $from .
    ' to ' .
    $to
);


$sheet->getStyle('A2')
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );


/*
|--------------------------------------------------------------------------
| Headers
|--------------------------------------------------------------------------
*/

$headers = [

    'Customer',
    'Transactions',
    'Period Purchases',
    'Amount Paid',
    'Outstanding',
    'Status'

];


$column = 'A';


foreach ($headers as $header) {

    $sheet->setCellValue(
        $column . '4',
        $header
    );

    $column++;

}


$sheet->getStyle('A4:F4')
    ->getFont()
    ->setBold(true);


$sheet->getStyle('A4:F4')
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );


/*
|--------------------------------------------------------------------------
| Data
|--------------------------------------------------------------------------
*/

$rowNumber = 5;


$totalCustomers = 0;
$totalPurchases = 0;
$totalPaid = 0;
$totalOutstanding = 0;


while ($row = mysqli_fetch_assoc($result)) {

    $purchases =
        (float) $row['total_purchases'];

    $paid =
        (float) $row['total_paid'];

    $outstanding =
        (float) $row['credit_balance'];


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    if ($outstanding > 0) {

        $status = 'CREDIT DUE';

    } else {

        $status = 'CLEARED';

    }


    /*
    |--------------------------------------------------------------------------
    | Write Row
    |--------------------------------------------------------------------------
    */

    $sheet->setCellValue(
        'A' . $rowNumber,
        $row['customer_name']
    );


    $sheet->setCellValue(
        'B' . $rowNumber,
        (int) $row['transactions']
    );


    $sheet->setCellValue(
        'C' . $rowNumber,
        $purchases
    );


    $sheet->setCellValue(
        'D' . $rowNumber,
        $paid
    );


    $sheet->setCellValue(
        'E' . $rowNumber,
        $outstanding
    );


    $sheet->setCellValue(
        'F' . $rowNumber,
        $status
    );


    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */

    $totalCustomers++;

    $totalPurchases += $purchases;

    $totalPaid += $paid;

    $totalOutstanding += $outstanding;


    $rowNumber++;

}


/*
|--------------------------------------------------------------------------
| Total Row
|--------------------------------------------------------------------------
*/

$sheet->setCellValue(
    'A' . $rowNumber,
    'TOTAL'
);


$sheet->setCellValue(
    'B' . $rowNumber,
    $totalCustomers
);


$sheet->setCellValue(
    'C' . $rowNumber,
    $totalPurchases
);


$sheet->setCellValue(
    'D' . $rowNumber,
    $totalPaid
);


$sheet->setCellValue(
    'E' . $rowNumber,
    $totalOutstanding
);


$sheet->getStyle(
    'A' . $rowNumber . ':F' . $rowNumber
)
->getFont()
->setBold(true);


/*
|--------------------------------------------------------------------------
| Currency Formatting
|--------------------------------------------------------------------------
*/

if ($rowNumber >= 5) {

    $sheet->getStyle(
        'C5:E' . $rowNumber
    )
    ->getNumberFormat()
    ->setFormatCode(
        '#,##0.00'
    );

}


/*
|--------------------------------------------------------------------------
| Column Widths
|--------------------------------------------------------------------------
*/

$sheet->getColumnDimension('A')
    ->setWidth(30);

$sheet->getColumnDimension('B')
    ->setWidth(18);

$sheet->getColumnDimension('C')
    ->setWidth(22);

$sheet->getColumnDimension('D')
    ->setWidth(20);

$sheet->getColumnDimension('E')
    ->setWidth(22);

$sheet->getColumnDimension('F')
    ->setWidth(18);


/*
|--------------------------------------------------------------------------
| Freeze Header
|--------------------------------------------------------------------------
*/

$sheet->freezePane('A5');


/*
|--------------------------------------------------------------------------
| Download File
|--------------------------------------------------------------------------
*/

$filename =
    'RNR_Customer_Credit_' .
    $from .
    '_to_' .
    $to .
    '.xlsx';


header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);


header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);


header('Cache-Control: max-age=0');


$writer = new Xlsx($spreadsheet);

$writer->save('php://output');

exit;