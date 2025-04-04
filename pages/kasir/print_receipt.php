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
    <title>Struk Transaksi - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
            .receipt {
                width: 80mm;
                margin: 0 auto;
            }
        }
        .receipt {
            width: 80mm;
            margin: 0 auto;
            font-family: 'Courier New', monospace;
        }
        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }
        .receipt-item {
            margin-bottom: 5px;
            border-bottom: 1px dashed #ccc;
        }
        .receipt-total {
            margin-top: 10px;
            border-top: 1px dashed #ccc;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="no-print mb-3">
            <a href="transactions.php" class="btn btn-secondary">Kembali</a>
            <button onclick="window.print()" class="btn btn-primary">Print Struk</button>
        </div>
        
        <div class="receipt">
            <div class="receipt-header">
                <h4>Cafe Bisa Ngopi</h4>
                <p>Jl. Contoh No. 123</p>
                <p>Telp: (021) 1234567</p>
                <p>================================</p>
                <p>Struk Transaksi</p>
                <p>No: <?php echo $order['id']; ?></p>
                <p>Tanggal: <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                <p>Kasir: <?php echo htmlspecialchars($order['cashier_name']); ?></p>
                <p>Meja: <?php echo $order['table_number']; ?></p>
                <p>================================</p>
            </div>
            
            <div class="receipt-items">
                <?php foreach ($items as $item): ?>
                <div class="receipt-item">
                    <p><?php echo htmlspecialchars($item['name']); ?></p>
                    <p><?php echo $item['quantity']; ?> x Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                    <p>Rp <?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="receipt-total">
                <p>================================</p>
                <p>Total: Rp <?php echo number_format($order['total_amount'], 0, ',', '.'); ?></p>
                <p>================================</p>
                <p>Terima Kasih</p>
                <p>Selamat Datang Kembali</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 