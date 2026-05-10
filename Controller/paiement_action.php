<?php
/**
 * paiement_action.php — Controller endpoint (AJAX POST)
 * ─────────────────────────────────────────────────────────────
 * Reçoit les données du formulaire de paiement, délègue au
 * PaiementController pour validation + traitement BDD.
 * Retourne du JSON. Aucun HTML ici.
 */
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/PaiementController.php';

header('Content-Type: application/json; charset=utf-8');

// Seule la méthode POST est acceptée
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée.']);
    exit;
}

$idEntretien = isset($_POST['id_entretien']) ? (int)$_POST['id_entretien'] : 0;

if ($idEntretien <= 0) {
    echo json_encode(['success' => false, 'error' => 'ID entretien invalide.']);
    exit;
}

$cardData = [
    'card_number' => $_POST['card_number'] ?? '',
    'card_name'   => $_POST['card_name']   ?? '',
    'card_expiry' => $_POST['card_expiry'] ?? '',
    'card_cvv'    => $_POST['card_cvv']    ?? '',
];

$ctrl   = new PaiementController($pdo);
$result = $ctrl->processPayment($idEntretien, $cardData);

echo json_encode($result);
exit;
?>