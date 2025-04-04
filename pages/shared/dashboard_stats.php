<?php
require_once '../../includes/auth_check.php';
checkRole(['kasir', 'manajer', 'admin']);

require_once '../../config/database.php';

// Filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Query untuk statistik transaksi
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total_orders,
        SUM(total_amount) as total_sales,
        COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_orders,
        COUNT(CASE WHEN status = 'selesai' THEN 1 END) as completed_orders
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    " . ($_SESSION['role'] == 'kasir' ? "AND cashier_id = ?" : "")
);
if ($_SESSION['role'] == 'kasir') {
    $stmt->execute([$start_date, $end_date, $_SESSION['user_id']]);
} else {
    $stmt->execute([$start_date, $end_date]);
}
$stats = $stmt->fetch();

// Query untuk grafik penjualan harian
$stmt = $pdo->prepare("
    SELECT 
        DATE(created_at) as date,
        COUNT(*) as total_orders,
        SUM(total_amount) as total_sales
    FROM orders 
    WHERE DATE(created_at) BETWEEN ? AND ?
    " . ($_SESSION['role'] == 'kasir' ? "AND cashier_id = ?" : "") . "
    GROUP BY DATE(created_at)
    ORDER BY date ASC
");
if ($_SESSION['role'] == 'kasir') {
    $stmt->execute([$start_date, $end_date, $_SESSION['user_id']]);
} else {
    $stmt->execute([$start_date, $end_date]);
}
$daily_sales = $stmt->fetchAll();

// Query untuk grafik penjualan per kategori
$stmt = $pdo->prepare("
    SELECT 
        m.category,
        COUNT(oi.id) as total_orders,
        SUM(oi.quantity) as total_quantity,
        SUM(oi.quantity * oi.price) as total_sales
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    JOIN orders o ON oi.order_id = o.id
    WHERE DATE(o.created_at) BETWEEN ? AND ?
    " . ($_SESSION['role'] == 'kasir' ? "AND o.cashier_id = ?" : "") . "
    GROUP BY m.category
    ORDER BY total_sales DESC
");
if ($_SESSION['role'] == 'kasir') {
    $stmt->execute([$start_date, $end_date, $_SESSION['user_id']]);
} else {
    $stmt->execute([$start_date, $end_date]);
}
$category_sales = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="../<?php echo $_SESSION['role']; ?>/dashboard.php">Cafe Bisa Ngopi - <?php echo ucfirst($_SESSION['role']); ?></a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Dashboard</h2>
        
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
        
        <!-- Statistik -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Transaksi</h5>
                        <h3><?php echo $stats['total_orders']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Penjualan</h5>
                        <h3>Rp <?php echo number_format($stats['total_sales'], 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body">
                        <h5 class="card-title">Transaksi Pending</h5>
                        <h3><?php echo $stats['pending_orders']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Transaksi Selesai</h5>
                        <h3><?php echo $stats['completed_orders']; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Grafik -->
        <div class="row">
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Penjualan Harian</h5>
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Penjualan per Kategori</h5>
                        <canvas id="categorySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <a href="../<?php echo $_SESSION['role']; ?>/dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Grafik Penjualan Harian
        const dailyCtx = document.getElementById('dailySalesChart').getContext('2d');
        new Chart(dailyCtx, {
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
        
        // Grafik Penjualan per Kategori
        const categoryCtx = document.getElementById('categorySalesChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode(array_column($category_sales, 'category')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($category_sales, 'total_sales')); ?>,
                    backgroundColor: [
                        'rgb(255, 99, 132)',
                        'rgb(54, 162, 235)',
                        'rgb(255, 205, 86)',
                        'rgb(75, 192, 192)',
                        'rgb(153, 102, 255)'
                    ]
                }]
            },
            options: {
                responsive: true
            }
        });
    </script>
</body>
</html> 