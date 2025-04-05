<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

// Cek role yang bisa akses
checkRole(['kasir', 'manajer', 'admin']);

// Set default tanggal (7 hari terakhir)
$end_date = date('Y-m-d');
$start_date = date('Y-m-d', strtotime('-7 days'));

// Ambil tanggal dari form jika ada
if (isset($_GET['start_date']) && isset($_GET['end_date'])) {
    $start_date = $_GET['start_date'];
    $end_date = $_GET['end_date'];
}

// Ambil statistik transaksi
$query = "SELECT 
            COUNT(*) as total_orders,
            SUM(total_amount) as total_sales,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_orders,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_orders
          FROM orders 
          WHERE DATE(created_at) BETWEEN ? AND ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$stats = $result->fetch_assoc();

// Ambil data penjualan harian untuk grafik
$query = "SELECT 
            DATE(created_at) as date,
            COUNT(*) as total_orders,
            SUM(total_amount) as total_sales
          FROM orders 
          WHERE DATE(created_at) BETWEEN ? AND ?
          GROUP BY DATE(created_at)
          ORDER BY date ASC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$daily_sales = $result->fetch_all(MYSQLI_ASSOC);

// Ambil data penjualan per kategori untuk grafik
$query = "SELECT 
            m.category,
            COUNT(oi.id) as total_orders,
            SUM(oi.quantity) as total_quantity,
            SUM(oi.quantity * oi.price) as total_sales
          FROM order_items oi
          JOIN menu m ON oi.menu_id = m.id
          JOIN orders o ON oi.order_id = o.id
          WHERE DATE(o.created_at) BETWEEN ? AND ?
          GROUP BY m.category
          ORDER BY total_sales DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
$category_sales = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cafe System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Cafe System</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Dashboard</h2>
                <form class="row g-3">
                    <div class="col-auto">
                        <input type="date" class="form-control" name="start_date" value="<?= $start_date ?>">
                    </div>
                    <div class="col-auto">
                        <input type="date" class="form-control" name="end_date" value="<?= $end_date ?>">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Transaksi</h5>
                        <h2 class="card-text"><?= number_format($stats['total_orders']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Penjualan</h5>
                        <h2 class="card-text">Rp <?= number_format($stats['total_sales'], 0, ',', '.') ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transaksi Pending</h5>
                        <h2 class="card-text"><?= number_format($stats['pending_orders']) ?></h2>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transaksi Selesai</h5>
                        <h2 class="card-text"><?= number_format($stats['completed_orders']) ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Penjualan Harian</h5>
                        <canvas id="dailySalesChart"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Penjualan per Kategori</h5>
                        <canvas id="categorySalesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Data untuk grafik penjualan harian
    const dailySalesData = {
        labels: <?= json_encode(array_map(function($sale) {
                return date('d/m', strtotime($sale['date']));
            }, $daily_sales)) ?>,
        datasets: [{
            label: 'Total Penjualan',
            data: <?= json_encode(array_map(function($sale) {
                    return $sale['total_sales'];
                }, $daily_sales)) ?>,
            borderColor: 'rgb(75, 192, 192)',
            tension: 0.1
        }]
    };

    // Data untuk grafik penjualan per kategori
    const categorySalesData = {
        labels: <?= json_encode(array_map(function($sale) {
                return ucfirst($sale['category']);
            }, $category_sales)) ?>,
        datasets: [{
            data: <?= json_encode(array_map(function($sale) {
                    return $sale['total_sales'];
                }, $category_sales)) ?>,
            backgroundColor: [
                'rgb(255, 99, 132)',
                'rgb(54, 162, 235)',
                'rgb(255, 206, 86)',
                'rgb(75, 192, 192)',
                'rgb(153, 102, 255)'
            ]
        }]
    };

    // Inisialisasi grafik penjualan harian
    new Chart(document.getElementById('dailySalesChart'), {
        type: 'line',
        data: dailySalesData,
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString('id-ID');
                        }
                    }
                }
            }
        }
    });

    // Inisialisasi grafik penjualan per kategori
    new Chart(document.getElementById('categorySalesChart'), {
        type: 'pie',
        data: categorySalesData,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    </script>
</body>

</html>