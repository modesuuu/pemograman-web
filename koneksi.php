<?php 
$host = "localhost";
$user = "root";
$pass = "";
$db   = "myWeb";

try {
    $connection = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}
?>