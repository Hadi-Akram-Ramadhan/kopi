<?php
require_once '../../includes/auth_check.php';
checkRole(['manajer']);

require_once '../../config/database.php';

// Proses form jika ada POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $stmt = $pdo->prepare("INSERT INTO menu (name, category, price, status) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['name'], $_POST['category'], $_POST['price'], 'tersedia']);
            
            // Log aktivitas
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Menambah menu: " . $_POST['name']]);
            
        } elseif ($_POST['action'] == 'edit') {
            $stmt = $pdo->prepare("UPDATE menu SET name = ?, category = ?, price = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $_POST['name'],
                $_POST['category'],
                $_POST['price'],
                $_POST['status'],
                $_POST['id']
            ]);
            
            // Log aktivitas
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Mengubah menu: " . $_POST['name']]);
        }
        
        header('Location: menu.php?success=1');
        exit();
    }
}

// Ambil data menu
$stmt = $pdo->query("SELECT * FROM menu ORDER BY category, name");
$menu_items = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Cafe Bisa Ngopi - Manajer</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Kelola Menu</h2>

        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            Menu berhasil diupdate!
        </div>
        <?php endif; ?>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tambah Menu Baru</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label class="form-label">Nama Menu</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category" class="form-control" required>
                                    <option value="makanan">Makanan</option>
                                    <option value="minuman">Minuman</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Harga</label>
                                <input type="number" name="price" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Tambah Menu</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Daftar Menu</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($menu_items as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                                        <td><?php echo ucfirst($item['category']); ?></td>
                                        <td>Rp <?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo $item['status'] == 'tersedia' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                                data-bs-target="#editModal<?php echo $item['id']; ?>">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?php echo $item['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit Menu</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id"
                                                            value="<?php echo $item['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Nama Menu</label>
                                                            <input type="text" name="name" class="form-control"
                                                                value="<?php echo htmlspecialchars($item['name']); ?>"
                                                                required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Kategori</label>
                                                            <select name="category" class="form-control" required>
                                                                <option value="makanan"
                                                                    <?php echo $item['category'] == 'makanan' ? 'selected' : ''; ?>>
                                                                    Makanan
                                                                </option>
                                                                <option value="minuman"
                                                                    <?php echo $item['category'] == 'minuman' ? 'selected' : ''; ?>>
                                                                    Minuman
                                                                </option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Harga</label>
                                                            <input type="number" name="price" class="form-control"
                                                                value="<?php echo $item['price']; ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Status</label>
                                                            <select name="status" class="form-control" required>
                                                                <option value="tersedia"
                                                                    <?php echo $item['status'] == 'tersedia' ? 'selected' : ''; ?>>
                                                                    Tersedia
                                                                </option>
                                                                <option value="tidak_tersedia"
                                                                    <?php echo $item['status'] == 'tidak_tersedia' ? 'selected' : ''; ?>>
                                                                    Tidak Tersedia
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary">Simpan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <a href="dashboard.php" class="btn btn-secondary mt-3">Kembali</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>