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
| Stock Query
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        p.id,
        p.product_name,
        p.quantity,
        p.selling_price,
        c.category_name,
        s.supplier_name

    FROM products p

    LEFT JOIN categories c
        ON c.id = p.category_id

    LEFT JOIN suppliers s
        ON s.id = p.supplier_id

    ORDER BY
        p.quantity ASC,
        p.product_name ASC
";


$result = mysqli_query($conn, $sql);


if (!$result) {

    die(
        "Unable to generate Stock Excel report: " .
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

$sheet->setTitle('Stock Report');


/*
|--------------------------------------------------------------------------
| Report Title
|--------------------------------------------------------------------------
*/

$sheet->mergeCells('A1:F1');

$sheet->setCellValue(
    'A1',
    'R&R COLLECTION - STOCK REPORT'
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
| Report Date
|--------------------------------------------------------------------------
*/

$sheet->mergeCells('A2:F2');

$sheet->setCellValue(
    'A2',
    'Current Inventory - ' . date('d M Y')
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
    'Product',
    'Category',
    'Supplier',
    'Stock',
    'Selling Price',
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


$totalProducts = 0;
$totalUnits = 0;
$lowStock = 0;
$outOfStock = 0;


while ($row = mysqli_fetch_assoc($result)) {

    $quantity = (int) $row['quantity'];


    /*
    |--------------------------------------------------------------------------
    | Determine Stock Status
    |--------------------------------------------------------------------------
    */

    if ($quantity <= 0) {

        $status = 'OUT OF STOCK';

        $outOfStock++;

    } elseif ($quantity <= 5) {

        $status = 'LOW STOCK';

        $lowStock++;

    } else {

        $status = 'IN STOCK';

    }


    /*
    |--------------------------------------------------------------------------
    | Write Data
    |--------------------------------------------------------------------------
    */

    $sheet->setCellValue(
        'A' . $rowNumber,
        $row['product_name']
    );


    $sheet->setCellValue(
        'B' . $rowNumber,
        $row['category_name'] ?? 'N/A'
    );


    $sheet->setCellValue(
        'C' . $rowNumber,
        $row['supplier_name'] ?? 'N/A'
    );


    $sheet->setCellValue(
        'D' . $rowNumber,
        $quantity
    );


    $sheet->setCellValue(
        'E' . $rowNumber,
        (float) $row['selling_price']
    );


    $sheet->setCellValue(
        'F' . $rowNumber,
        $status
    );


    $totalProducts++;

    $totalUnits += $quantity;


    $rowNumber++;

}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

$summaryRow = $rowNumber + 2;


$sheet->setCellValue(
    'A' . $summaryRow,
    'SUMMARY'
);


$sheet->getStyle(
    'A' . $summaryRow
)
->getFont()
->setBold(true);


$summaryRow++;


$sheet->setCellValue(
    'A' . $summaryRow,
    'Products'
);

$sheet->setCellValue(
    'B' . $summaryRow,
    $totalProducts
);


$summaryRow++;


$sheet->setCellValue(
    'A' . $summaryRow,
    'Total Units'
);

$sheet->setCellValue(
    'B' . $summaryRow,
    $totalUnits
);


$summaryRow++;


$sheet->setCellValue(
    'A' . $summaryRow,
    'Low Stock'
);

$sheet->setCellValue(
    'B' . $summaryRow,
    $lowStock
);


$summaryRow++;


$sheet->setCellValue(
    'A' . $summaryRow,
    'Out of Stock'
);

$sheet->setCellValue(
    'B' . $summaryRow,
    $outOfStock
);


/*
|--------------------------------------------------------------------------
| Currency Formatting
|--------------------------------------------------------------------------
*/

if ($rowNumber > 5) {

    $sheet->getStyle(
        'E5:E' . ($rowNumber - 1)
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
    ->setWidth(35);

$sheet->getColumnDimension('B')
    ->setWidth(25);

$sheet->getColumnDimension('C')
    ->setWidth(30);

$sheet->getColumnDimension('D')
    ->setWidth(15);

$sheet->getColumnDimension('E')
    ->setWidth(20);

$sheet->getColumnDimension('F')
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
    'RNR_Stock_Report_' .
    date('Y-m-d') .
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

