<?php
require_once '../../includes/auth_check.php';
checkRole(['manajer']);

require_once '../../config/database.php';
require_once '../../vendor/autoload.php'; // Pastikan sudah install PhpSpreadsheet via composer

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Query untuk laporan penjualan per hari
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_sales
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY date DESC
");
$stmt->execute([$start_date, $end_date]);
$daily_sales = $stmt->fetchAll();

// Query untuk laporan penjualan per menu
$stmt = $pdo->prepare("
    SELECT 
        m.name,
        m.category,
        COUNT(oi.id) as total_orders,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.quantity * oi.price) as total_sales
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY m.id
    ORDER BY total_sales DESC
");
$stmt->execute([$start_date, $end_date]);
$menu_sales = $stmt->fetchAll();

// Query untuk laporan penjualan per kasir
$stmt = $pdo->prepare("
    SELECT 
        u.username,
        COUNT(o.id) as total_orders,
        SUM(o.total_amount) as total_sales
    FROM orders o
    JOIN users u ON o.cashier_id = u.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY u.id
    ORDER BY total_sales DESC
");
$stmt->execute([$start_date, $end_date]);
$cashier_sales = $stmt->fetchAll();

// Buat spreadsheet
$spreadsheet = new Spreadsheet();

// Set document properties
$spreadsheet->getProperties()
    ->setCreator('Cafe Bisa Ngopi')
    ->setLastModifiedBy('Cafe Bisa Ngopi')
    ->setTitle('Laporan Penjualan')
    ->setSubject('Laporan Penjualan Cafe Bisa Ngopi')
    ->setDescription('Laporan Penjualan Cafe Bisa Ngopi periode ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));

// Penjualan Harian
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Penjualan Harian');

$sheet->setCellValue('A1', 'Laporan Penjualan Cafe Bisa Ngopi');
$sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));
$sheet->mergeCells('A1:C1');
$sheet->mergeCells('A2:C2');

$sheet->setCellValue('A4', 'Tanggal');
$sheet->setCellValue('B4', 'Total Transaksi');
$sheet->setCellValue('C4', 'Total Penjualan');

$row = 5;
foreach ($daily_sales as $sale) {
    $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($sale['date'])));
    $sheet->setCellValue('B' . $row, $sale['total_orders']);
    $sheet->setCellValue('C' . $row, $sale['total_sales']);
    $row++;
}

// Penjualan per Menu
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Penjualan per Menu');

$sheet->setCellValue('A1', 'Laporan Penjualan Cafe Bisa Ngopi');
$sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));
$sheet->mergeCells('A1:E1');
$sheet->mergeCells('A2:E2');

$sheet->setCellValue('A4', 'Menu');
$sheet->setCellValue('B4', 'Kategori');
$sheet->setCellValue('C4', 'Total Order');
$sheet->setCellValue('D4', 'Total Qty');
$sheet->setCellValue('E4', 'Total Penjualan');

$row = 5;
foreach ($menu_sales as $menu) {
    $sheet->setCellValue('A' . $row, $menu['name']);
    $sheet->setCellValue('B' . $row, ucfirst($menu['category']));
    $sheet->setCellValue('C' . $row, $menu['total_orders']);
    $sheet->setCellValue('D' . $row, $menu['total_quantity']);
    $sheet->setCellValue('E' . $row, $menu['total_sales']);
    $row++;
}

// Penjualan per Kasir
$sheet = $spreadsheet->createSheet();
$sheet->setTitle('Penjualan per Kasir');

$sheet->setCellValue('A1', 'Laporan Penjualan Cafe Bisa Ngopi');
$sheet->setCellValue('A2', 'Periode: ' . date('d/m/Y', strtotime($start_date)) . ' - ' . date('d/m/Y', strtotime($end_date)));
$sheet->mergeCells('A1:C1');
$sheet->mergeCells('A2:C2');

$sheet->setCellValue('A4', 'Kasir');
$sheet->setCellValue('B4', 'Total Transaksi');
$sheet->setCellValue('C4', 'Total Penjualan');

$row = 5;
foreach ($cashier_sales as $cashier) {
    $sheet->setCellValue('A' . $row, $cashier['username']);
    $sheet->setCellValue('B' . $row, $cashier['total_orders']);
    $sheet->setCellValue('C' . $row, $cashier['total_sales']);
    $row++;
}

// Set active sheet
$spreadsheet->setActiveSheetIndex(0);

// Output Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="laporan_penjualan.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output'); 