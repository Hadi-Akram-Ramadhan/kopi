<?php
session_start();

// Function buat cek role
function checkRole($allowed_roles) {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        header('Location: ../login.php');
        exit();
    }

    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ../unauthorized.php');
        exit();
    }
}

// Function buat cek login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Function buat cek role spesifik
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Function buat log aktivitas
function logActivity($conn, $activity) {
    if (isset($_SESSION['user_id'])) {
        $query = "INSERT INTO activity_logs (user_id, activity) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $_SESSION['user_id'], $activity);
        $stmt->execute();
    }
}

// Function buat kirim notifikasi
function sendNotification($conn, $user_id, $message) {
    $query = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $message);
    $stmt->execute();
} 