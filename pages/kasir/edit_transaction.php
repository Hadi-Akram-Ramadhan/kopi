<?php
require_once '../../includes/auth_check.php';
checkRole(['kasir']);

require_once '../../config/database.php';

// Cek apakah transaksi ada dan milik kasir ini
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND cashier_id = ? AND status = 'pending'");
$stmt->execute([$_GET['id'], $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header('Location: transactions.php');
    exit();
}


// Ambil data menu
$stmt = $pdo->query("SELECT * FROM menu ORDER BY category, name");
$menu_items = $stmt->fetchAll();

// Ambil data order items
$stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order['id']]);
$order_items = $stmt->fetchAll();

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
    
    // Update order
    $stmt = $pdo->prepare("UPDATE orders SET table_number = ?, total_amount = ? WHERE id = ?");
    $stmt->execute([$table_number, $total_amount, $order['id']]);
    
    // Hapus order items lama
    $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
    $stmt->execute([$order['id']]);
    
    // Insert order items baru
    foreach ($items as $key => $menu_id) {
        if ($quantities[$key] > 0) {
            $stmt = $pdo->prepare("SELECT price FROM menu WHERE id = ?");
            $stmt->execute([$menu_id]);
            $price = $stmt->fetch()['price'];
            
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, menu_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order['id'], $menu_id, $quantities[$key], $price]);
        }
    }
    
    // Log aktivitas
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], "Mengubah transaksi #" . $order['id']]);
    
    header('Location: transactions.php?updated=1');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Transaksi - Cafe Bisa Ngopi</title>
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
        <h2>Edit Transaksi #<?php echo $order['id']; ?></h2>
        <form method="POST" class="mt-4">
            <div class="mb-3">
                <label class="form-label">Nomor Meja</label>
                <input type="number" name="table_number" class="form-control"
                    value="<?php echo $order['table_number']; ?>" required>
            </div>

            <div class="card mb-3">
                <div class="card-body">
                    <h5 class="card-title">Menu</h5>
                    <div class="row">
                        <?php foreach ($menu_items as $item): 
                            // Cek apakah item ada di order
                            $quantity = 0;
                            foreach ($order_items as $order_item) {
                                if ($order_item['menu_id'] == $item['id']) {
                                    $quantity = $order_item['quantity'];
                                    break;
                                }
                            }
                        ?>
                        <div class="col-md-4 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                    <p class="text-muted"><?php echo ucfirst($item['category']); ?></p>
                                    <p class="fw-bold">Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></p>
                                    <input type="hidden" name="items[]" value="<?php echo $item['id']; ?>">
                                    <input type="number" name="quantities[]" class="form-control"
                                        value="<?php echo $quantity; ?>" min="0">
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="transactions.php" class="btn btn-secondary">Kembali</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>