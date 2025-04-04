<?php
require_once '../../includes/auth_check.php';
checkRole(['admin']);

require_once '../../config/database.php';

// Proses form jika ada POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
            $stmt->execute([$_POST['username'], $password, $_POST['role']]);
            
            // Log aktivitas
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Menambah user: " . $_POST['username']]);
            
        } elseif ($_POST['action'] == 'edit') {
            if (!empty($_POST['password'])) {
                $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET username = ?, password = ?, role = ? WHERE id = ?");
                $stmt->execute([$_POST['username'], $password, $_POST['role'], $_POST['id']]);
            } else {
                $stmt = $pdo->prepare("UPDATE users SET username = ?, role = ? WHERE id = ?");
                $stmt->execute([$_POST['username'], $_POST['role'], $_POST['id']]);
            }
            
            // Log aktivitas
            $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], "Mengubah user: " . $_POST['username']]);
        }
        
        header('Location: users.php?success=1');
        exit();
    }
}

// Ambil data users
$stmt = $pdo->query("SELECT * FROM users ORDER BY role, username");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Cafe Bisa Ngopi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">Cafe Bisa Ngopi - Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="../../auth/logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2>Kelola User</h2>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            User berhasil diupdate!
        </div>
        <?php endif; ?>
        
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Tambah User Baru</h5>
                        <form method="POST">
                            <input type="hidden" name="action" value="add">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-control" required>
                                    <option value="kasir">Kasir</option>
                                    <option value="manajer">Manajer</option>
                                    <option value="admin">Admin</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Tambah User</button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Daftar User</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Tanggal Dibuat</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($user['username']); ?></td>
                                        <td><?php echo ucfirst($user['role']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $user['id']; ?>">
                                                Edit
                                            </button>
                                        </td>
                                    </tr>
                                    
                                    <!-- Modal Edit -->
                                    <div class="modal fade" id="editModal<?php echo $user['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Edit User</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="action" value="edit">
                                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                        <div class="mb-3">
                                                            <label class="form-label">Username</label>
                                                            <input type="text" name="username" class="form-control" 
                                                                   value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                                                            <input type="password" name="password" class="form-control">
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label">Role</label>
                                                            <select name="role" class="form-control" required>
                                                                <option value="kasir" <?php echo $user['role'] == 'kasir' ? 'selected' : ''; ?>>
                                                                    Kasir
                                                                </option>
                                                                <option value="manajer" <?php echo $user['role'] == 'manajer' ? 'selected' : ''; ?>>
                                                                    Manajer
                                                                </option>
                                                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>
                                                                    Admin
                                                                </option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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