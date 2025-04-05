<?php
require_once '../../config/database.php';
require_once '../../config/auth.php';

// Cek role yang bisa akses
checkRole(['kasir', 'manajer', 'admin']);

// Ambil notifikasi yang belum dibaca
$query = "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$notifications = $result->fetch_all(MYSQLI_ASSOC);

// Format notifikasi untuk response
$formatted_notifications = array_map(function($notification) {
    return [
        'id' => $notification['id'],
        'message' => $notification['message'],
        'created_at' => date('d/m/Y H:i', strtotime($notification['created_at']))
    ];
}, $notifications);

// Set response header
header('Content-Type: application/json');

// Output notifikasi dalam format JSON
echo json_encode($formatted_notifications); 