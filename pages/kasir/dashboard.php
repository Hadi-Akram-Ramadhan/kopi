<?php
require_once '../../includes/auth_check.php';
checkRole(['kasir']);

require_once '../../config/database.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Cafe Bisa Ngopi - Kasir</a>
            <div class="navbar-nav ms-auto">
                <span class="nav-item nav-link text-light">Welcome, <?php echo $_SESSION['username']; ?></span>
                <a class="nav-link" href="../../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Transaksi Baru</h5>
                        <a href="new_order.php" class="btn btn-primary">Buat Transaksi</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Riwayat Transaksi</h5>
                        <a href="transactions.php" class="btn btn-info">Lihat Riwayat</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>