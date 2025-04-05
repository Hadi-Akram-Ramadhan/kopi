<?php
require_once 'config/database.php';
require_once 'config/auth.php';

// Log aktivitas sebelum logout
if (isLoggedIn()) {
    logActivity($conn, 'Logout dari sistem');
}

// Hapus semua data session
session_destroy();

// Redirect ke halaman login
header('Location: login.php');
exit();
?>