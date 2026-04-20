<?php
require_once(__DIR__ . '/../config/db.php');

class User {

    public static function register($data) {
        global $pdo;

        $sql = "INSERT INTO client (prenom, email, telephone, mot_de_passe, adresse, date_inscription)
                VALUES (?, ?, ?, ?, ?, NOW())";

        $stmt = $pdo->prepare($sql);

        return $stmt->execute([
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            password_hash($data['mot_de_passe'], PASSWORD_DEFAULT),
            $data['adresse']
        ]);
    }

    public static function login($email, $password) {
        global $pdo;

        $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ?");
        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            return $user;
        }

        return false;
    }
}