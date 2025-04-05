<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

// Cek role yang bisa akses
checkRole(['kasir', 'manajer', 'admin']);

// Ambil ID notifikasi dari request
$notification_id = isset($_POST['id']) ? $_POST['id'] : null;
if (!$notification_id) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'ID notifikasi tidak valid']);
    exit();
}

// Update status notifikasi jadi udah dibaca
$query = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $notification_id, $_SESSION['user_id']);
$success = $stmt->execute();

// Set response header
header('Content-Type: application/json');

// Output response dalam format JSON
echo json_encode(['success' => $success]); 