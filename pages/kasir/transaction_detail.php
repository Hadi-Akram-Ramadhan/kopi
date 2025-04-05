<?php
require_once '../../includes/auth_check.php';
checkRole(['kasir']);

require_once '../../config/database.php';

// Cek ID transaksi
if (!isset($_GET['id'])) {
    header('Location: transactions.php');
    exit();
}

$order_id = $_GET['id'];

// Ambil data transaksi
$stmt = $pdo->prepare("
    SELECT o.*, u.username as cashier_name
    FROM orders o
    JOIN users u ON o.cashier_id = u.id
    WHERE o.id = ? AND o.cashier_id = ?
");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: transactions.php');
    exit();
}

// Ambil detail item transaksi
$stmt = $pdo->prepare("
    SELECT oi.*, m.name, m.category
    FROM order_items oi
    JOIN menu m ON oi.menu_id = m.id
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - Cafe Bisa Ngopi</title>
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
        <h2>Detail Transaksi</h2>

        <!-- Info Transaksi -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID Transaksi:</strong> <?php echo $order['id']; ?></p>
                        <p><strong>Tanggal:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?>
                        </p>
                        <p><strong>Kasir:</strong> <?php echo htmlspecialchars($order['cashier_name']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Nomor Meja:</strong> <?php echo $order['table_number']; ?></p>
                        <p><strong>Status:</strong>
                            <span
                                class="badge bg-<?php echo $order['status'] == 'completed' ? 'success' : 'warning'; ?>">
                                <?php echo ucfirst($order['status']); ?>
                            </span>
                        </p>
                        <p><strong>Total:</strong> Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Item -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Detail Item</h5>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Menu</th>
                                <th>Kategori</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo ucfirst($item['category']); ?></td>
                                <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-3">
            <a href="transactions.php" class="btn btn-secondary">Kembali</a>
            <?php if ($order['status'] == 'pending'): ?>
            <a href="complete_transaction.php?id=<?php echo $order['id']; ?>" class="btn btn-success">Selesai</a>
            <a href="edit_transaction.php?id=<?php echo $order['id']; ?>" class="btn btn-warning">Edit</a>
            <a href="transactions.php?delete=<?php echo $order['id']; ?>" class="btn btn-danger"
                onclick="return confirm('Yakin ingin menghapus transaksi ini?')">Hapus</a>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>