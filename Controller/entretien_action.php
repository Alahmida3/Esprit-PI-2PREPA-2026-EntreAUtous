<?php
/**
 * entretien_action.php — Controller endpoint
 * ─────────────────────────────────────────────────────────────
 * Point d'entrée unique pour toutes les actions CRUD entretien.
 * Délègue au EntretienController, puis redirige ou répond JSON.
 * Aucun HTML ici.
 */
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/EntretienController.php';

$ctrl   = new EntretienController($pdo);
$action = $_REQUEST['action'] ?? '';

$result = $ctrl->handleRequest($action, array_merge($_GET, $_POST));

if (!$result['success'] && !empty($result['errors'])) {
    // Erreurs de validation → session + redirection vers le formulaire
    session_start();
    $_SESSION['entretien_errors'] = $result['errors'];
    $_SESSION['entretien_post']   = $_POST;

    // Revenir au formulaire source selon l'action
    $referer = $_SERVER['HTTP_REFERER'] ?? '../Views/back/listeentretiens.php';
    header('Location: ' . $referer);
    exit;
}

if (!empty($result['redirect'])) {
    header('Location: ../Views/back/' . $result['redirect']);
    exit;
}

// Fallback JSON pour actions AJAX éventuelles
header('Content-Type: application/json; charset=utf-8');
echo json_encode($result);
?>