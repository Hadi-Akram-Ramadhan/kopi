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

// Cek apakah transaksi ada dan milik kasir ini
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND cashier_id = ? AND status = 'pending'");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: transactions.php');
    exit();
}

// Update status transaksi
$stmt = $pdo->prepare("UPDATE orders SET status = 'completed' WHERE id = ?");
$stmt->execute([$order_id]);

// Log aktivitas
$stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
$stmt->execute([$_SESSION['user_id'], "Menyelesaikan transaksi #$order_id"]);

header('Location: transactions.php?completed=1');
exit(); 