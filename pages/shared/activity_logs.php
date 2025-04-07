<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

// Cek role yang bisa akses
checkRole(['kasir', 'manajer', 'admin']);

// Filter tanggal
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Query untuk ambil log aktivitas
$query = "SELECT 
    al.*,
    u.username,
    u.role
FROM activity_logs al
JOIN users u ON al.user_id = u.id
WHERE DATE(al.created_at) BETWEEN ? AND ?
ORDER BY al.created_at DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $start_date, $end_date);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Aktivitas - Cafe System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <button class="btn btn-outline-light" onclick="history.back()">
                <i class="bi bi-arrow-left"></i> Kembali
            </button>
            <span class="navbar-brand mx-auto">Log Aktivitas</span>
            <a class="btn btn-outline-light" href="../auth/logout.php">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col">
                <h2>Log Aktivitas</h2>
            </div>
        </div>


        hadi hadi hadi hadi


        <!-- Filter Form -->
        <div class="row mb-4">
            <div class="col">
                <form method="GET" class="row g-3">
                    <div class="col-auto">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date"
                            value="<?= $start_date ?>">
                    </div>
                    <div class="col-auto">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" value="<?= $end_date ?>">
                    </div>
                    <div class="col-auto">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="form-control btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Log Table -->
        <div class="row">
            <div class="col">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Waktu</th>
                                <th>User</th>
                                <th>Role</th>
                                <th>Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($row['created_at'])) ?></td>
                                <td><?= htmlspecialchars($row['username']) ?></td>
                                <td>
                                    <span
                                        class="badge bg-<?= $row['role'] == 'admin' ? 'danger' : ($row['role'] == 'manajer' ? 'warning' : 'info') ?>">
                                        <?= ucfirst($row['role']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['activity']) ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>