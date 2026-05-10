<?php
/**
 * facture_action.php — Controller endpoint
 * ─────────────────────────────────────────────────────────────
 * Point d'entrée unique pour toutes les actions CRUD facture.
 * Délègue au FactureController. Aucun HTML ici.
 */
require_once __DIR__ . '/FactureController.php';
require_once __DIR__ . '/../models/db.php';

$ctrl   = new FactureController($pdo);
$action = $_REQUEST['action'] ?? 'list';

try {

    // ── LIST par entretien ──────────────────────────────────────
    if ($action === 'list' && isset($_GET['entretien'])) {
        header('Content-Type: application/json; charset=utf-8');
        $rows = $ctrl->listByEntretien((int)$_GET['entretien']);
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    // ── CREATE ─────────────────────────────────────────────────
    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $f = new Facture(
            null,
            $_POST['ref_facture']   ?? '',
            $_POST['date_emission'] ?? null,
            $_POST['montant_ht']    ?? 0,
            $_POST['taux_tva']      ?? 0,
            $_POST['montant_ttc']   ?? 0,
            $_POST['mode_paiement'] ?? '',
            $_POST['etat_paiement'] ?? '',
            $_POST['entretien']     ?? null
        );
        $res = $ctrl->addFacture($f);
        if ($res['success']) {
            header('Location: ../Views/back/listeentretiens.php?fact_ok=1');
            exit;
        }
        session_start();
        $_SESSION['facture_errors'] = $res['errors'];
        $_SESSION['facture_post']   = $_POST;
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '../Views/back/ajouter_facture.php'));
        exit;
    }

    // ── UPDATE ─────────────────────────────────────────────────
    if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        header('Content-Type: application/json; charset=utf-8');
        $f = new Facture(
            $_POST['id_facture']    ?? null,
            $_POST['ref_facture']   ?? '',
            $_POST['date_emission'] ?? null,
            $_POST['montant_ht']    ?? 0,
            $_POST['taux_tva']      ?? 0,
            $_POST['montant_ttc']   ?? 0,
            $_POST['mode_paiement'] ?? '',
            $_POST['etat_paiement'] ?? '',
            $_POST['entretien']     ?? null
        );
        $res = $ctrl->updateFacture($f);
        echo json_encode($res);
        exit;
    }

    // ── DELETE (soft) ──────────────────────────────────────────
    if ($action === 'delete' && isset($_POST['id'])) {
        header('Content-Type: application/json; charset=utf-8');
        $res = $ctrl->deleteFacture((int)$_POST['id']);
        echo json_encode(['success' => (bool)$res]);
        exit;
    }

    // ── RESTORE ────────────────────────────────────────────────
    if ($action === 'restore' && isset($_POST['id'])) {
        $res = $ctrl->restoreFacture((int)$_POST['id']);

        // Cas spécial : entretien parent encore supprimé
        if ($res === 'entretien_supprime') {
            header('Location: ../Views/back/historique_entretien.php?err_fac_parent=1');
            exit;
        }

        header('Location: ../Views/back/historique_entretien.php?restored_fac=1');
        exit;
    }

    // ── GET ────────────────────────────────────────────────────
    if ($action === 'get' && isset($_GET['id'])) {
        header('Content-Type: application/json; charset=utf-8');
        $row = $ctrl->getFacture((int)$_GET['id']);
        echo json_encode(['success' => true, 'data' => $row]);
        exit;
    }

    // ── PDF ────────────────────────────────────────────────────
    if ($action === 'pdf' && isset($_GET['id'])) {
        $path = __DIR__ . '/../libdompdf/dompdf/autoload.inc.php';
        if (file_exists($path)) {
            require_once $path;
            $ctrl->exportToPDF((int)$_GET['id']);
        } else {
            die("Erreur : Dompdf introuvable dans " . $path);
        }
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => 'Action invalide']);

} catch (Exception $e) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>