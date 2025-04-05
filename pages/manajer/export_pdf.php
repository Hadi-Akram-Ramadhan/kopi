<?php
require_once '../../includes/auth_check.php';
checkRole(['manajer']);

require_once '../../config/database.php';
require_once '../../vendor/autoload.php';

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

// Buat PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Cafe Bisa Ngopi');
$pdf->SetTitle('Laporan Penjualan');

// Set default header data
$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// Set header and footer fonts
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// Set default monospaced font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Set margins
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Set auto page breaks
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Set font
$pdf->SetFont('helvetica', '', 10);

// Add a page
$pdf->AddPage();

// Penjualan Harian
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Penjualan Harian', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(40, 7, 'Tanggal', 1);
$pdf->Cell(40, 7, 'Total Transaksi', 1);
$pdf->Cell(40, 7, 'Total Penjualan', 1);
$pdf->Ln();
foreach ($daily_sales as $sale) {
    $pdf->Cell(40, 7, $sale['date'], 1);
    $pdf->Cell(40, 7, $sale['total_orders'], 1);
    $pdf->Cell(40, 7, 'Rp ' . number_format($sale['total_sales'], 0, ',', '.'), 1);
    $pdf->Ln();
}

// Penjualan per Menu
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Penjualan per Menu', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(40, 7, 'Menu', 1);
$pdf->Cell(30, 7, 'Kategori', 1);
$pdf->Cell(30, 7, 'Total Pesanan', 1);
$pdf->Cell(30, 7, 'Total Quantity', 1);
$pdf->Cell(40, 7, 'Total Penjualan', 1);
$pdf->Ln();
foreach ($menu_sales as $menu) {
    $pdf->Cell(40, 7, $menu['name'], 1);
    $pdf->Cell(30, 7, ucfirst($menu['category']), 1);
    $pdf->Cell(30, 7, $menu['total_orders'], 1);
    $pdf->Cell(30, 7, $menu['total_quantity'], 1);
    $pdf->Cell(40, 7, 'Rp ' . number_format($menu['total_sales'], 0, ',', '.'), 1);
    $pdf->Ln();
}

// Penjualan per Kasir
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Penjualan per Kasir', 0, 1, 'L');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(60, 7, 'Kasir', 1);
$pdf->Cell(60, 7, 'Total Transaksi', 1);
$pdf->Cell(60, 7, 'Total Penjualan', 1);
$pdf->Ln();
foreach ($cashier_sales as $cashier) {
    $pdf->Cell(60, 7, $cashier['username'], 1);
    $pdf->Cell(60, 7, $cashier['total_orders'], 1);
    $pdf->Cell(60, 7, 'Rp ' . number_format($cashier['total_sales'], 0, ',', '.'), 1);
    $pdf->Ln();
}

// Output PDF
$pdf->Output('laporan_penjualan.pdf', 'D'); 