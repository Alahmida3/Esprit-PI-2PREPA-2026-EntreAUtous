<?php
// Simple API endpoint to manage factures via AJAX or form posts.
require_once __DIR__ . '/FactureController.php';
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

$ctrl = new FactureController($pdo);
$action = $_REQUEST['action'] ?? 'list';

try {
    if ($action === 'list' && isset($_GET['entretien'])) {
        $ent = (int)$_GET['entretien'];
        $rows = $ctrl->listByEntretien($ent);
        echo json_encode(['success'=>true,'data'=>$rows]);
        exit;
    }

    if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {

    $entretienId = $_POST['entretien'] ?? null;
    $ref = trim($_POST['ref_facture'] ?? '');
    $date = trim($_POST['date_emission'] ?? '');
    $ht = $_POST['montant_ht'] ?? null;
    $tva = $_POST['taux_tva'] ?? null;
    $ttc = $_POST['montant_ttc'] ?? null;
    $mode = trim($_POST['mode_paiement'] ?? '');
    $etat = trim($_POST['etat_paiement'] ?? '');
    $entretien = $_POST['entretien'] ?? null;


    $errors = [];
    if ($ref === '' || !is_string($ref)) {
        $errors[] = "Référence invalide";
    }
    if ($date === '') {
        $errors[] = "Date obligatoire";
    }
    if ($ht === null || !is_numeric($ht) || $ht <= 0) {
        $errors[] = "Montant HT doit être un nombre > 0";
    }
    if ($tva === null || !is_numeric($tva)) {
        $errors[] = "TVA invalide";
    }
    if ($ttc === null || !is_numeric($ttc) || $ttc <= 0) {
        $errors[] = "Montant TTC doit être un nombre > 0";
    }
    if ($mode === '') {
        $errors[] = "Mode de paiement obligatoire";
    }
    if ($etat === '') {
        $errors[] = "État de paiement obligatoire";
    }
    if ($entretien === null || !is_numeric($entretien)) {
        $errors[] = "Entretien invalide";
    }

    $f = new Facture(
        null,
        $_POST['ref_facture'] ?? '',
        $_POST['date_emission'] ?? null,
        $_POST['montant_ht'] ?? 0,
        $_POST['taux_tva'] ?? 0,
        $_POST['montant_ttc'] ?? 0,
        $_POST['mode_paiement'] ?? '',
        $_POST['etat_paiement'] ?? '',
        $entretienId
    );

    $res = $ctrl->addFacture($f);

    if ($res) {
        header('Location: ../views/back/listeentretiens.php?fact_ok=1');
        exit;
    } else {
        echo json_encode(['success' => false]);
        exit;
    }
}

    if ($action === 'delete' && isset($_POST['id'])) {
        $res = $ctrl->deleteFacture((int)$_POST['id']);
        echo json_encode(['success'=>(bool)$res]);
        exit;
    }

    if ($action === 'get' && isset($_GET['id'])) {
        $row = $ctrl->getFacture((int)$_GET['id']);
        echo json_encode(['success'=>true,'data'=>$row]);
        exit;
    }

    if ($action === 'update' && $_SERVER['REQUEST_METHOD']==='POST') {
        $f = new Facture(
            $_POST['id_facture'] ?? null,
            $_POST['ref_facture'] ?? '',
            $_POST['date_emission'] ?? null,
            $_POST['montant_ht'] ?? 0,
            $_POST['taux_tva'] ?? 0,
            $_POST['montant_ttc'] ?? 0,
            $_POST['mode_paiement'] ?? '',
            $_POST['etat_paiement'] ?? '',
            $_POST['entretien'] ?? null
        );
        $res = $ctrl->updateFacture($f);
        echo json_encode(['success'=>(bool)$res]);
        exit;
    }

    echo json_encode(['success'=>false,'message'=>'Action invalide']);
} catch (Exception $e) {
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}

?>