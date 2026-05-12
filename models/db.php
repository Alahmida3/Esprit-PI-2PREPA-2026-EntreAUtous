<?php
// models/db.php
$host     = '127.0.0.1';
$port     = '3306';
$dbname   = 'integration';
$user     = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    die(json_encode([
        'error' => 'Erreur de connexion : ' . $e->getMessage()
    ]));
}

// ✅ Pour VoitureC.php, EntretienController.php etc. → config::getConnexion()
class config {
    public static function getConnexion(): PDO {
        global $pdo;
        return $pdo;
    }
}

// ✅ Pour piece_model.php, vente_model.php → new connexion()
class connexion {
    public $conx;
    public function __construct() {
        global $pdo;
        $this->conx = $pdo;
    }
}