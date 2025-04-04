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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Cafe Bisa Ngopi - Manajer</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Laporan Penjualan</h2>
        
        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" name="start_date" class="form-control" value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" name="end_date" class="form-control" value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary d-block">Filter</button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Grafik Penjualan Harian -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Penjualan Harian</h5>
                <canvas id="dailySalesChart"></canvas>
            </div>
        </div>
        
        <!-- Tabel Penjualan per Menu -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Penjualan per Menu</h5>
                <div class="table-responsive">
                    <table class="table">
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
                                <td><?php echo htmlspecialchars($menu['name']); ?></td>
                                <td><?php echo ucfirst($menu['category']); ?></td>
                                <td><?php echo $menu['total_orders']; ?></td>
                                <td><?php echo $menu['total_quantity']; ?></td>
                                <td>Rp <?php echo number_format($menu['total_sales'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <!-- Tabel Penjualan per Kasir -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Penjualan per Kasir</h5>
                <div class="table-responsive">
                    <table class="table">
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
                                <td><?php echo htmlspecialchars($cashier['username']); ?></td>
                                <td><?php echo $cashier['total_orders']; ?></td>
                                <td>Rp <?php echo number_format($cashier['total_sales'], 0, ',', '.'); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Grafik Penjualan Harian
        const ctx = document.getElementById('dailySalesChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($daily_sales, 'date')); ?>,
                datasets: [{
                    label: 'Total Penjualan',
                    data: <?php echo json_encode(array_column($daily_sales, 'total_sales')); ?>,
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