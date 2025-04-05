<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

// Cek role yang bisa akses
checkRole(['kasir']);

// Ambil ID transaksi dari URL
$order_id = isset($_GET['id']) ? $_GET['id'] : null;
if (!$order_id) {
    header('Location: transactions.php');
    exit();
}

// Ambil data transaksi
$query = "SELECT o.*, u.username as cashier_name 
          FROM orders o 
          JOIN users u ON o.cashier_id = u.id 
          WHERE o.id = ? AND o.cashier_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$order = $result->fetch_assoc();

if (!$order) {
    header('Location: transactions.php');
    exit();
}

// Ambil detail transaksi
$query = "SELECT oi.*, m.name, m.category 
          FROM order_items oi 
          JOIN menu m ON oi.menu_id = m.id 
          WHERE oi.order_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();
$items = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Transaksi #<?= $order_id ?> - Cafe System</title>
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
    </style>
</head>

<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col">
                <div class="receipt">
                    <h3 class="text-center">Cafe System</h3>
                    <p class="text-center">Struk Transaksi #<?= $order_id ?></p>
                    <p>Tanggal: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></p>
                    <p>Kasir: <?= htmlspecialchars($order['cashier_name']) ?></p>
                    <p>Meja: <?= $order['table_number'] ?></p>
                    <hr>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Qty</th>
                                <th>Harga</th>
                                <th>Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['name']) ?></td>
                                <td><?= $item['quantity'] ?></td>
                                <td>Rp <?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td>Rp <?= number_format($item['quantity'] * $item['price'], 0, ',', '.') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="3">Total</th>
                                <th>Rp <?= number_format($order['total_amount'], 0, ',', '.') ?></th>
                            </tr>
                        </tfoot>
                    </table>
                    <hr>
                    <p class="text-center">Terima Kasih</p>
                    <p class="text-center">Selamat Datang Kembali</p>
                </div>
            </div>
        </div>
        <div class="row mt-3 no-print">
            <div class="col text-center">
                <button class="btn btn-primary" onclick="window.print()">Print Struk</button>
                <a href="transactions.php" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>