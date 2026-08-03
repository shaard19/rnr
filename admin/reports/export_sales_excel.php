<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

/*
|--------------------------------------------------------------------------
| R&R COLLECTION
| SALES EXCEL EXPORT
|--------------------------------------------------------------------------
*/

require_once "../../config/session.php";
require_once "../../config/database.php";
require_once "../../config/permissions.php";
require_once "../../vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

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
| VALID PAYMENT
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
| BUILD CONDITIONS
|--------------------------------------------------------------------------
*/

$conditions = [];

$fromEscaped = mysqli_real_escape_string(
    $conn,
    $from
);

$toEscaped = mysqli_real_escape_string(
    $conn,
    $to
);

$conditions[] = "
    DATE(s.sale_date)
    BETWEEN '$fromEscaped'
    AND '$toEscaped'
";

if ($payment !== '') {

    $paymentEscaped = mysqli_real_escape_string(
        $conn,
        $payment
    );

    $conditions[] =
        "s.payment_method = '$paymentEscaped'";
}

$where = implode(
    " AND ",
    $conditions
);

/*
|--------------------------------------------------------------------------
| FETCH SALES
|--------------------------------------------------------------------------
*/

$sql = "

    SELECT

        s.invoice_no,

        COALESCE(
            c.customer_name,
            'Walk In Customer'
        ) AS customer_name,

        s.payment_method,

        s.payment_status,

        s.total,

        s.amount_paid,

        s.balance,

        s.sale_date

    FROM sales s

    LEFT JOIN customers c
        ON c.id = s.customer_id

    WHERE $where

    ORDER BY s.sale_date DESC

";

$result = mysqli_query(
    $conn,
    $sql
);

if (!$result) {

    die(
        "EXPORT SQL ERROR: " .
        mysqli_error($conn)
    );
}

/*
|--------------------------------------------------------------------------
| CREATE SPREADSHEET
|--------------------------------------------------------------------------
*/

$spreadsheet = new Spreadsheet();

$sheet = $spreadsheet
    ->getActiveSheet();

$sheet->setTitle(
    'Sales Report'
);

/*
|--------------------------------------------------------------------------
| TITLE
|--------------------------------------------------------------------------
*/

$sheet->mergeCells('A1:H1');

$sheet->setCellValue(
    'A1',
    'R&R COLLECTION - SALES REPORT'
);

$sheet->mergeCells('A2:H2');

$sheet->setCellValue(
    'A2',
    "Period: $from to $to"
);

if ($payment !== '') {

    $sheet->mergeCells('A3:H3');

    $sheet->setCellValue(
        'A3',
        "Payment Method: $payment"
    );

    $headerRow = 5;

} else {

    $headerRow = 4;
}

/*
|--------------------------------------------------------------------------
| HEADERS
|--------------------------------------------------------------------------
*/

$headers = [
    'Invoice',
    'Customer',
    'Payment Method',
    'Payment Status',
    'Total',
    'Amount Paid',
    'Balance',
    'Sale Date'
];

foreach (
    $headers as $index => $header
) {

    $column = chr(
        65 + $index
    );

    $sheet->setCellValue(
        $column . $headerRow,
        $header
    );
}

/*
|--------------------------------------------------------------------------
| TITLE STYLE
|--------------------------------------------------------------------------
*/

$sheet
    ->getStyle('A1:H1')
    ->applyFromArray([

        'font' => [
            'bold' => true,
            'size' => 16
        ],

        'alignment' => [
            'horizontal' =>
                Alignment::HORIZONTAL_CENTER
        ]

    ]);

$sheet
    ->getStyle(
        "A2:H" . ($headerRow - 1)
    )
    ->applyFromArray([

        'alignment' => [
            'horizontal' =>
                Alignment::HORIZONTAL_CENTER
        ]

    ]);

/*
|--------------------------------------------------------------------------
| HEADER STYLE
|--------------------------------------------------------------------------
*/

$sheet
    ->getStyle(
        "A{$headerRow}:H{$headerRow}"
    )
    ->applyFromArray([

        'font' => [
            'bold' => true,
            'color' => [
                'rgb' => 'FFFFFF'
            ]
        ],

        'fill' => [
            'fillType' =>
                Fill::FILL_SOLID,

            'startColor' => [
                'rgb' => '2563EB'
            ]
        ],

        'alignment' => [
            'horizontal' =>
                Alignment::HORIZONTAL_CENTER
        ],

        'borders' => [
            'allBorders' => [
                'borderStyle' =>
                    Border::BORDER_THIN
            ]
        ]

    ]);

/*
|--------------------------------------------------------------------------
| DATA
|--------------------------------------------------------------------------
*/

$row = $headerRow + 1;

$totalSales = 0;
$totalPaid = 0;
$totalBalance = 0;
$transactionCount = 0;

while (
    $sale = mysqli_fetch_assoc($result)
) {

    $sheet->setCellValue(
        "A$row",
        $sale['invoice_no'] ?? 'N/A'
    );

    $sheet->setCellValue(
        "B$row",
        $sale['customer_name']
    );

    $sheet->setCellValue(
        "C$row",
        $sale['payment_method']
    );

    $sheet->setCellValue(
        "D$row",
        $sale['payment_status']
    );

    $sheet->setCellValue(
        "E$row",
        (float)$sale['total']
    );

    $sheet->setCellValue(
        "F$row",
        (float)$sale['amount_paid']
    );

    $sheet->setCellValue(
        "G$row",
        (float)$sale['balance']
    );

    $sheet->setCellValue(
        "H$row",
        date(
            'd M Y H:i',
            strtotime(
                $sale['sale_date']
            )
        )
    );

    $totalSales +=
        (float)$sale['total'];

    $totalPaid +=
        (float)$sale['amount_paid'];

    $totalBalance +=
        (float)$sale['balance'];

    $transactionCount++;

    $row++;
}

/*
|--------------------------------------------------------------------------
| TOTAL ROW
|--------------------------------------------------------------------------
*/

$totalRow = $row + 1;

$sheet->setCellValue(
    "A$totalRow",
    'TOTAL'
);

$sheet->setCellValue(
    "D$totalRow",
    "$transactionCount Transactions"
);

$sheet->setCellValue(
    "E$totalRow",
    $totalSales
);

$sheet->setCellValue(
    "F$totalRow",
    $totalPaid
);

$sheet->setCellValue(
    "G$totalRow",
    $totalBalance
);

/*
|--------------------------------------------------------------------------
| TOTAL STYLE
|--------------------------------------------------------------------------
*/

$sheet
    ->getStyle(
        "A$totalRow:H$totalRow"
    )
    ->applyFromArray([

        'font' => [
            'bold' => true
        ],

        'fill' => [
            'fillType' =>
                Fill::FILL_SOLID,

            'startColor' => [
                'rgb' => 'E5E7EB'
            ]
        ],

        'borders' => [
            'allBorders' => [
                'borderStyle' =>
                    Border::BORDER_THIN
            ]
        ]

    ]);

/*
|--------------------------------------------------------------------------
| CURRENCY FORMAT
|--------------------------------------------------------------------------
*/

$sheet
    ->getStyle(
        "E" .
        ($headerRow + 1) .
        ":G$totalRow"
    )
    ->getNumberFormat()
    ->setFormatCode(
        '#,##0.00'
    );

/*
|--------------------------------------------------------------------------
| TABLE BORDERS
|--------------------------------------------------------------------------
*/

$sheet
    ->getStyle(
        "A{$headerRow}:H" .
        ($row - 1)
    )
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(
        Border::BORDER_THIN
    );

/*
|--------------------------------------------------------------------------
| ALIGN NUMBERS
|--------------------------------------------------------------------------
*/

$sheet
    ->getStyle(
        "E" .
        ($headerRow + 1) .
        ":G$totalRow"
    )
    ->getAlignment()
    ->setHorizontal(
        Alignment::HORIZONTAL_RIGHT
    );

/*
|--------------------------------------------------------------------------
| AUTO WIDTH
|--------------------------------------------------------------------------
*/

foreach (
    range('A', 'H') as $column
) {

    $sheet
        ->getColumnDimension($column)
        ->setAutoSize(true);
}

/*
|--------------------------------------------------------------------------
| FREEZE HEADER
|--------------------------------------------------------------------------
*/

$sheet->freezePane(
    "A" . ($headerRow + 1)
);

/*
|--------------------------------------------------------------------------
| DOWNLOAD
|--------------------------------------------------------------------------
*/

$filename =
    'RNR_Sales_Report_' .
    $from .
    '_to_' .
    $to .
    '.xlsx';

/*
|--------------------------------------------------------------------------
| CLEAN OUTPUT BUFFER
|--------------------------------------------------------------------------
*/

while (
    ob_get_level() > 0
) {
    ob_end_clean();
}

header(
    'Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
);

header(
    'Content-Disposition: attachment; filename="' .
    $filename .
    '"'
);

header(
    'Cache-Control: max-age=0'
);

header(
    'Pragma: public'
);

/*
|--------------------------------------------------------------------------
| WRITE FILE
|--------------------------------------------------------------------------
*/

$writer = new Xlsx(
    $spreadsheet
);

$writer->save(
    'php://output'
);

$spreadsheet->disconnectWorksheets();

unset($spreadsheet);

exit;