<?php
session_start();
require_once '../config/database.php';

// Log aktivitas logout
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $activity = "Logout dari sistem";
    
    $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $activity);
    $stmt->execute();
}

// Hapus semua data session
session_destroy();

// Redirect ke halaman login
header('Location: ../index.php');
exit();
?>