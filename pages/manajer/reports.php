<?php
require_once '../../includes/auth_check.php';
checkRole(['manajer']);

require_once '../../config/database.php';

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

// Hitung total penjualan
$total_sales = 0;
foreach ($daily_sales as $sale) {
    $total_sales += $sale['total_sales'];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <div class="d-flex align-items-center">
                    <a href="dashboard.php" class="btn btn-secondary me-3">
                        <i class="bi bi-arrow-left"></i> Back
                    </a>
                    <div>
                        <h2 class="mb-0">Laporan Penjualan</h2>
                        <p class="text-muted mb-0">Periode: <?= date('d/m/Y', strtotime($start_date)) ?> -
                            <?= date('d/m/Y', strtotime($end_date)) ?></p>
                    </div>
                </div>
            </div>
            <div class="col-auto">
                <div class="btn-group">
                    <a href="export_pdf.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>"
                        class="btn btn-danger">
                        <i class="bi bi-file-pdf"></i> Export PDF
                    </a>
                    <a href="export_excel.php?start_date=<?= $start_date ?>&end_date=<?= $end_date ?>"
                        class="btn btn-success">
                        <i class="bi bi-file-excel"></i> Export Excel
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="bi bi-printer"></i> Print
                    </button>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Filter Tanggal</h5>
                        <form method="GET" class="row g-3">
                            <div class="col-auto">
                                <label for="start_date" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="start_date" name="start_date"
                                    value="<?= $start_date ?>">
                            </div>
                            <div class="col-auto">
                                <label for="end_date" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control" id="end_date" name="end_date"
                                    value="<?= $end_date ?>">
                            </div>
                            <div class="col-auto">
                                <label class="form-label">&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">Filter</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Penjualan</h5>
                        <h2 class="text-primary">Rp <?= number_format($total_sales, 0, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Grafik Penjualan Harian</h5>
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Penjualan per Menu</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Menu</th>
                                        <th>Kategori</th>
                                        <th>Total Pesanan</th>
                                        <th>Total Quantity</th>
                                        <th>Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menu_sales as $menu): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($menu['name']) ?></td>
                                        <td><?= ucfirst($menu['category']) ?></td>
                                        <td><?= $menu['total_orders'] ?></td>
                                        <td><?= $menu['total_quantity'] ?></td>
                                        <td>Rp <?= number_format($menu['total_sales'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Penjualan per Kasir</h5>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kasir</th>
                                        <th>Total Transaksi</th>
                                        <th>Total Penjualan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($cashier_sales as $cashier): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($cashier['username']) ?></td>
                                        <td><?= $cashier['total_orders'] ?></td>
                                        <td>Rp <?= number_format($cashier['total_sales'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Data untuk grafik
    const dates = <?= json_encode(array_column($daily_sales, 'date')) ?>;
    const sales = <?= json_encode(array_column($daily_sales, 'total_sales')) ?>;

    // Buat grafik
    const ctx = document.getElementById('salesChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Total Penjualan',
                data: sales,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
    </script>
</body>

</html>