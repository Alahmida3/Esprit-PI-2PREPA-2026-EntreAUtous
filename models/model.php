<?php
/**
 * Model.php — Classe de base MVC
 * Contient la connexion PDO (plus besoin de config/Database.php)
 * Tous les modèles héritent de cette classe.
 */
class Model {

    // ── Connexion DB (Singleton) ──────────────────────────────
    private static ?PDO $pdo = null;

    // Accessible depuis le controller aussi
    public static function db(): PDO {
        if (self::$pdo === null) {
            $host    = 'localhost';
            $dbname  = 'web_voiture';
            $user    = 'root';
            $pass    = '';          // Modifier si mot de passe XAMPP
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            try {
                self::$pdo = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                // En production : logger l'erreur, ne pas l'afficher
                http_response_code(500);
                die(json_encode([
                    'success' => false,
                    'message' => 'Connexion base de données échouée.'
                ]));
            }
        }
        return self::$pdo;
    }
}
?>