<?php
// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'cafe_db';

// Create mysqli connection
$conn = new mysqli($host, $username, $password, $database);

// Check mysqli connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>