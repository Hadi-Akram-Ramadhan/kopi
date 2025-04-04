<?php
require_once '../../includes/auth_check.php';
checkRole(['kasir']);

require_once '../../config/database.php';

// Ambil data transaksi
$stmt = $pdo->prepare("
    SELECT o.*, u.username as cashier_name 
    FROM orders o 
    JOIN users u ON o.cashier_id = u.id 
    WHERE o.cashier_id = ? 
    ORDER BY o.created_at DESC
");
$stmt->execute([$_SESSION['user_id']]);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Cafe Bisa Ngopi - Kasir</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Riwayat Transaksi</h2>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Transaksi berhasil dibuat!
        </div>
        <?php endif; ?>
        
        <div class="table-responsive mt-4">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nomor Meja</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td>#<?php echo $transaction['id']; ?></td>
                        <td><?php echo $transaction['table_number']; ?></td>
                        <td>Rp <?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $transaction['status'] == 'pending' ? 'warning' : 
                                    ($transaction['status'] == 'paid' ? 'success' : 'info'); 
                            ?>">
                                <?php echo ucfirst($transaction['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?></td>
                        <td>
                            <a href="view_transaction.php?id=<?php echo $transaction['id']; ?>" 
                               class="btn btn-sm btn-info">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 