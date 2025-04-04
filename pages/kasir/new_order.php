<?php
require_once '../../includes/auth_check.php';
checkRole(['kasir']);

require_once '../../config/database.php';

// Ambil data menu
$stmt = $pdo->query("SELECT * FROM menu WHERE status = 'tersedia' ORDER BY category, name");
$menu_items = $stmt->fetchAll();

// Proses form jika ada POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $table_number = $_POST['table_number'];
    $items = $_POST['items'];
    $quantities = $_POST['quantities'];
    $total_amount = 0;
    
    // Hitung total
    foreach ($items as $key => $menu_id) {
        if ($quantities[$key] > 0) {
            $stmt = $pdo->prepare("SELECT price FROM menu WHERE id = ?");
            $stmt->execute([$menu_id]);
            $price = $stmt->fetch()['price'];
            $total_amount += $price * $quantities[$key];
        }
    }
    
    // Insert ke tabel orders
    $stmt = $pdo->prepare("INSERT INTO orders (table_number, total_amount, cashier_id) VALUES (?, ?, ?)");
    $stmt->execute([$table_number, $total_amount, $_SESSION['user_id']]);
    $order_id = $pdo->lastInsertId();
    
    // Insert ke tabel order_items
    foreach ($items as $key => $menu_id) {
        if ($quantities[$key] > 0) {
            $stmt = $pdo->prepare("SELECT price FROM menu WHERE id = ?");
            $stmt->execute([$menu_id]);
            $price = $stmt->fetch()['price'];
            
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $menu_id, $quantities[$key], $price]);
        }
    }
    
    // Log aktivitas
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], "Membuat transaksi baru #$order_id"]);
    
    header('Location: transactions.php?success=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaksi Baru - Cafe Bisa Ngopi</title>
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
        <h2>Transaksi Baru</h2>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label class="form-label">Nomor Meja</label>
                <input type="number" name="table_number" class="form-control" required>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Menu</h5>
                    <div class="row">
                        <?php foreach ($menu_items as $item): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="text-muted"><?php echo ucfirst($item['category']); ?></p>
                                    <p class="fw-bold">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                    <input type="hidden" name="items[]" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantities[]" class="form-control" value="0" min="0">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Proses Transaksi</button>
            <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>