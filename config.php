<?php
$host = 'localhost';
$dbname = 'entreautous';
$user = 'root';
$pass = 'root';
$port = '3307';

try {
    $pdo = new PDO(
        "mysql:host=$host:$port;dbname=$dbname;charset=utf8",
        $user,
        $pass
    );

    
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $pdo = null; 
    
}
?>