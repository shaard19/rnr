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
| Product Sales Query
|--------------------------------------------------------------------------
|
| Product Sales includes:
| - Paid sales
| - Partial sales
| - Credit sales
|
| Therefore, we filter only by sale_date.
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.product_name,

        COALESCE(SUM(si.quantity), 0) AS quantity_sold,

        COALESCE(SUM(si.subtotal), 0) AS revenue,

        COUNT(DISTINCT si.sale_id) AS transactions

    FROM sale_items si

    INNER JOIN sales s
        ON s.id = si.sale_id

    INNER JOIN products p
        ON p.id = si.product_id

    WHERE
        DATE(s.sale_date) BETWEEN '$from' AND '$to'

    GROUP BY
        p.id,
        p.product_name

    ORDER BY
        quantity_sold DESC
";


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Unable to generate Excel report: " .
        htmlspecialchars(mysqli_error($conn))
    );
}


/*
|--------------------------------------------------------------------------
| Create Spreadsheet
|--------------------------------------------------------------------------
*/

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();

$sheet->setTitle('Product Sales');


/*
|--------------------------------------------------------------------------
| Report Title
|--------------------------------------------------------------------------
*/

$sheet->mergeCells('A1:E1');

$sheet->setCellValue(
    'A1',
    'R&R COLLECTION - PRODUCT SALES REPORT'
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
| Reporting Period
|--------------------------------------------------------------------------
*/

$sheet->mergeCells('A2:E2');

$sheet->setCellValue(
    'A2',
    'Period: ' . $from . ' to ' . $to
);

$sheet->getStyle('A2')
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );


/*
|--------------------------------------------------------------------------
| Column Headers
|--------------------------------------------------------------------------
*/

$headers = [
    'Rank',
    'Product',
    'Units Sold',
    'Transactions',
    'Revenue'
];

$column = 'A';

foreach ($headers as $header) {

    $sheet->setCellValue(
        $column . '4',
        $header
    );

    $column++;
}


$sheet->getStyle('A4:E4')
    ->getFont()
    ->setBold(true);

$sheet->getStyle('A4:E4')
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_CENTER
    );


/*
|--------------------------------------------------------------------------
| Report Data
|--------------------------------------------------------------------------
*/

$rowNumber = 5;

$rank = 1;

$totalQuantity = 0;

$totalRevenue = 0;

$totalTransactions = 0;


while ($row = mysqli_fetch_assoc($result)) {

    $quantity =
        (float) $row['quantity_sold'];

    $revenue =
        (float) $row['revenue'];

    $transactions =
        (int) $row['transactions'];


    $sheet->setCellValue(
        'A' . $rowNumber,
        $rank
    );


    $sheet->setCellValue(
        'B' . $rowNumber,
        $row['product_name']
    );


    $sheet->setCellValue(
        'C' . $rowNumber,
        $quantity
    );


    $sheet->setCellValue(
        'D' . $rowNumber,
        $transactions
    );


    $sheet->setCellValue(
        'E' . $rowNumber,
        $revenue
    );


    $totalQuantity += $quantity;

    $totalRevenue += $revenue;

    $totalTransactions += $transactions;


    $rowNumber++;

    $rank++;
}


/*
|--------------------------------------------------------------------------
| Totals Row
|--------------------------------------------------------------------------
*/

$sheet->setCellValue(
    'A' . $rowNumber,
    'TOTAL'
);

$sheet->setCellValue(
    'C' . $rowNumber,
    $totalQuantity
);

$sheet->setCellValue(
    'D' . $rowNumber,
    $totalTransactions
);

$sheet->setCellValue(
    'E' . $rowNumber,
    $totalRevenue
);


$sheet->getStyle(
    'A' . $rowNumber . ':E' . $rowNumber
)
->getFont()
->setBold(true);


/*
|--------------------------------------------------------------------------
| Revenue Formatting
|--------------------------------------------------------------------------
*/

$sheet->getStyle(
    'E5:E' . $rowNumber
)
->getNumberFormat()
->setFormatCode(
    '#,##0.00'
);


/*
|--------------------------------------------------------------------------
| Column Widths
|--------------------------------------------------------------------------
*/

$sheet->getColumnDimension('A')
    ->setWidth(10);

$sheet->getColumnDimension('B')
    ->setWidth(35);

$sheet->getColumnDimension('C')
    ->setWidth(15);

$sheet->getColumnDimension('D')
    ->setWidth(18);

$sheet->getColumnDimension('E')
    ->setWidth(20);


/*
|--------------------------------------------------------------------------
| Freeze Header
|--------------------------------------------------------------------------
*/

$sheet->freezePane('A5');


/*
|--------------------------------------------------------------------------
| Download
|--------------------------------------------------------------------------
*/

$filename =
    'RNR_Product_Sales_' .
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

