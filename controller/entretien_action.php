<?php
/**
 * entretien_action.php — Point d'entrée unique pour toutes les actions CRUD Entretien.
 * Les views ne font JAMAIS de traitement métier : elles postent ici.
 * Respecte le pattern MVC : View → Action → Controller → Model.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../controller/EntretienController.php';
require_once __DIR__ . '/../controller/FactureController.php';
require_once __DIR__ . '/../Models/Entretien.php';

$ctrl   = new EntretienController($pdo);
$action = $_REQUEST['action'] ?? '';

// ── Ajouter ────────────────────────────────────────────────
if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    try {
        $res = $ctrl->handleRequest('add', $_POST);
    } catch (Exception $e) {
        $res = ['success' => false, 'errors' => ["Erreur base de données : " . $e->getMessage()]];
    }
    if ($res['success']) {
        header('Location: ../Views/back/listeentretiens.php?success=1');
    } else {
        $_SESSION['entretien_errors'] = $res['errors'];
        $_SESSION['entretien_post']   = $_POST;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    exit;
}

// ── Modifier ───────────────────────────────────────────────
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();
    try {
        $res = $ctrl->handleRequest('update', $_POST);
    } catch (Exception $e) {
        $res = ['success' => false, 'errors' => ["Erreur base de données : " . $e->getMessage()]];
    }
    if ($res['success']) {
        header('Location: ../Views/back/listeentretiens.php?updated=1');
    } else {
        $_SESSION['entretien_errors'] = $res['errors'];
        $_SESSION['entretien_post']   = $_POST;
        header('Location: ' . $_SERVER['HTTP_REFERER']);
    }
    exit;
}

try {

    // ── Supprimer (soft delete) ────────────────────────────────
    if ($action === 'delete' && isset($_POST['id'])) {
        $ctrl->handleRequest('delete', ['id' => (int)$_POST['id']]);
        header('Location: ../Views/back/listeentretiens.php?deleted=1');
        exit;
    }

    // ── Restaurer entretien ────────────────────────────────────
    if ($action === 'restore_ent' && isset($_POST['id'])) {
        $ctrl->handleRequest('restore_ent', ['id' => (int)$_POST['id']]);
        header('Location: ../Views/back/historique_entretien.php?restored_ent=1');
        exit;
    }

    // ── Restaurer facture (délégué au FactureController) ───────
    if ($action === 'restore_fac' && isset($_POST['id'])) {
        $factCtrl = new FactureController($pdo);
        $factCtrl->restoreFacture((int)$_POST['id']);
        header('Location: ../Views/back/historique_entretien.php?restored_fac=1');
        exit;
    }

    // ── Liste filtrée (JSON, pour appels AJAX futurs) ──────────
    if ($action === 'list') {
        header('Content-Type: application/json; charset=utf-8');
        $statut = $_GET['statut'] ?? null;
        $date   = $_GET['date']   ?? null;
        $sort   = $_GET['sort']   ?? 'recent';
        $rows   = $ctrl->listFiltered($statut, $date, $sort);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    // ── Stats par statut (JSON) ────────────────────────────────
    if ($action === 'stats') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'data' => $ctrl->getStatsByStatut()]);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Action invalide.']);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>