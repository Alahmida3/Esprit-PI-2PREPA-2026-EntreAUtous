<?php
/**
 * paiement_action.php — Contrôleur de finalisation du paiement.
 * Reçoit l'id_entretien en POST (depuis banque_secure.php via fetch).
 * Met à jour : entre.statut → 'paye' + facture.etat_paiement → 'Payée'
 * Aucune nouvelle table créée.
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: application/json; charset=utf-8');

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

try {
    // ── Vérifier que l'entretien existe et est bien "termine" ──
    $check = $pdo->prepare("SELECT statut FROM entre WHERE id_entretien = ? AND deleted_at IS NULL");
    $check->execute([$idEntretien]);
    $row = $check->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'error' => 'Entretien introuvable.']);
        exit;
    }
    if ($row['statut'] === 'paye') {
        echo json_encode(['success' => true, 'already' => true, 'message' => 'Déjà payé.']);
        exit;
    }
    if ($row['statut'] !== 'termine') {
        echo json_encode(['success' => false, 'error' => 'Cet entretien ne peut pas être payé (statut : ' . $row['statut'] . ').']);
        exit;
    }

    // ── UPDATE entre : statut → paye ──────────────────────────
    $updEnt = $pdo->prepare("UPDATE entre SET statut = 'paye' WHERE id_entretien = ?");
    $updEnt->execute([$idEntretien]);

    // ── UPDATE facture liée : etat_paiement → Payée ───────────
    $updFac = $pdo->prepare("UPDATE facture SET etat_paiement = 'Payée' WHERE entretien = ? AND deleted_at IS NULL");
    $updFac->execute([$idEntretien]);

    echo json_encode([
        'success' => true,
        'message' => 'Paiement enregistré avec succès.',
        'id'      => $idEntretien,
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erreur base de données.']);
}
?>