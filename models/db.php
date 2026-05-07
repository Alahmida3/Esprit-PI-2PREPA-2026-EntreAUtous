<?php
// models/db.php
$host   = 'localhost';
$dbname = 'garage_db';
$user   = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE,        PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En production, ne jamais afficher le message brut
    error_log("DB Error: " . $e->getMessage());
    die(json_encode(['error' => 'Erreur de connexion à la base de données.']));
}