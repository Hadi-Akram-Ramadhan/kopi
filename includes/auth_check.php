<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../../index.php');
    exit();
}

// Cek role sesuai halaman
function checkRole($allowed_roles) {
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ../../index.php?error=unauthorized');
        exit();
    }
}
?> 