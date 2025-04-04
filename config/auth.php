<?php
session_start();

// Cek apakah user sudah login
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Cek role user
function checkRole($allowed_roles) {
    if (!isLoggedIn()) {
        header('Location: /kopi/auth/login.php');
        exit();
    }

    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: /kopi/auth/login.php');
        exit();
    }
}

// Log aktivitas user
function logActivity($conn, $activity) {
    if (isLoggedIn()) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $activity);
        $stmt->execute();
    }
} 