<?php
session_start(); // Nécessaire pour récupérer l'ID client
header('Content-Type: application/json; charset=utf-8');

// Vérifie le chemin vers ton fichier de connexion !
require_once __DIR__ . '/../models/db.php'; 
require_once __DIR__ . '/PaiementController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

// Récupération de l'ID client (Vérifie si c'est 'id_client' ou 'user_id' dans ton login)
$clientId = $_SESSION['user_id'] ?? 0;
$idEntretien = isset($_POST['id_entretien']) ? (int)$_POST['id_entretien'] : 0;

if ($idEntretien <= 0 || $clientId === 0) {
    echo json_encode(['success' => false, 'error' => 'Session expirée ou ID invalide.']);
    exit;
}

try {
    $ctrl = new PaiementController($pdo); // $pdo vient de db.php

    // Vérification de sécurité
    $entretien = $ctrl->getPaymentData($idEntretien, $clientId);

    if (!$entretien) {
        echo json_encode(['success' => false, 'error' => 'Entretien introuvable ou accès refusé.']);
        exit;
    }

    $cardData = [
        'card_number' => $_POST['card_number'] ?? '',
        'card_name'   => $_POST['card_name']   ?? '',
        'card_expiry' => $_POST['card_expiry'] ?? '',
        'card_cvv'    => $_POST['card_cvv']    ?? '',
    ];

    $result = $ctrl->processPayment($idEntretien, $cardData, $clientId);
    echo json_encode($result);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur PHP : ' . $e->getMessage()]);
}
exit;