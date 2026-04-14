<?php
require_once 'config.php';
require_once 'Models/Entretien.php';
require_once 'controller/EntretienController.php';

if (!$pdo) {
    echo "Erreur : Connexion BD échouée.";
} else {
    try {
        $controller = new EntretienController($pdo);
        $list = $controller->listEntretiens();
        echo "Connexion et récupération réussie. Nombre d'entretiens : " . count($list);
    } catch (Exception $e) {
        echo "Erreur : " . $e->getMessage();
    }
}
?>
