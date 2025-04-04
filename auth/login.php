<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Log aktivitas login
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, activity) VALUES (?, 'Login')");
        $stmt->execute([$user['id']]);

        // Redirect berdasarkan role
        switch($user['role']) {
            case 'kasir':
                header('Location: ../pages/kasir/dashboard.php');
                break;
            case 'manajer':
                header('Location: ../pages/manajer/dashboard.php');
                break;
            case 'admin':
                header('Location: ../pages/admin/dashboard.php');
                break;
        }
        exit();
    } else {
        header('Location: ../index.php?error=1');
        exit();
    }
}
?>