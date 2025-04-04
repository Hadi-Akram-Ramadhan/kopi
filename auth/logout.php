<?php
session_start();
require_once '../config/database.php';

if (isset($_SESSION['user_id'])) {
    // Log aktivitas logout
    $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, 'Logout')");
    $stmt->execute([$_SESSION['user_id']]);
    
    // Hapus session
    session_destroy();
}

header('Location: ../index.php');
exit();
?>