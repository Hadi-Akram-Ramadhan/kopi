<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Redirect ke halaman login jika belum login
if (!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// Redirect ke dashboard sesuai role
header('Location: pages/' . $_SESSION['role'] . '/dashboard.php');
exit();