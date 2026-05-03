<?php
/* VIEW ONLY — le formulaire soumet vers facture_action.php?action=create */
require_once '../../config.php';
if (!$pdo) die("Erreur de connexion à la base de données.");

session_start();
$validationErrors = $_SESSION['facture_errors'] ?? [];
$old              = $_SESSION['facture_post']   ?? [];
unset($_SESSION['facture_errors'], $_SESSION['facture_post']);

// Helper : retourne 'is-invalid' si le champ est mentionné dans les erreurs
function fieldInvalid($errors, $keyword) {
    foreach ($errors as $e) {
        if (mb_stripos($e, $keyword) !== false) return 'is-invalid';
    }
    return '';
}

// Entretien lié (GET ou retour POST)
$entId = $old['entretien'] ?? (isset($_GET['entretien']) ? (int)$_GET['entretien'] : '');
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <?php include("../../partials/back/head/head-meta.html"); ?>
    <title>entreAUtous - Ajouter une Facture</title>
    <?php include("../../partials/back/head/head-links.html"); ?>
</head>

<body>
    <div id="db-wrapper">
        <?php include("../../partials/back/sidebar-collapse.html"); ?>

        <main id="page-content">
            <div class="header">
                <?php include("../../partials/back/topbar-second.html"); ?>
            </div>

            <div class="container-fluid pt-10 pb-6">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-9">
                        
                        <div class="card border-0 mb-4 shadow-sm" style="background: linear-gradient(90deg, #23395b 0%, #4a90e2 100%); border-radius: 12px;">
                            <div class="card-body p-4 text-white">
                                <h3 class="fw-bold mb-1 text-white">📄 Nouvelle Facture</h3>
                                <p class="mb-0 opacity-75">Enregistrement des données de facturation.</p>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 fw-bold text-dark">Informations de la Facture</h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if (!empty($validationErrors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="validation-errors">
                                    <strong>Erreurs de validation :</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($validationErrors as $ve): ?>
                                            <li><?php echo htmlspecialchars($ve); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>
                                <form action="../../controller/facture_action.php?action=create" method="POST">
                                    <div class="row g-4">
                                        <!-- id_facture is AUTO_INCREMENT in DB; do not provide it -->
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Référence Facture</label>
                                            <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'référence'); ?>" name="ref_facture" placeholder="Ex: FAC-001" value="<?php echo htmlspecialchars($old['ref_facture'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Date d'émission</label>
                                            <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'date'); ?>" name="date_emission" value="<?php echo htmlspecialchars($old['date_emission'] ?? ''); ?>">
                                        </div>

                                        <!-- entretien passed as hidden field (foreign key) -->
                                        <input type="hidden" name="entretien" value="<?php echo htmlspecialchars($entId); ?>">

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Montant HT</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'ht'); ?>" name="montant_ht" value="<?php echo htmlspecialchars($old['montant_ht'] ?? ''); ?>">
                                                <span class="input-group-text">TND</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Taux TVA (%)</label>
                                            <select class="form-select <?php echo fieldInvalid($validationErrors, 'tva'); ?>" name="taux_tva">
                                                <option value="19" <?php echo (($old['taux_tva'] ?? '19') == '19') ? 'selected' : ''; ?>>19%</option>
                                                <option value="13" <?php echo (($old['taux_tva'] ?? '') == '13') ? 'selected' : ''; ?>>13%</option>
                                                <option value="7"  <?php echo (($old['taux_tva'] ?? '') == '7')  ? 'selected' : ''; ?>>7%</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Montant TTC</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'ttc'); ?>" name="montant_ttc" value="<?php echo htmlspecialchars($old['montant_ttc'] ?? ''); ?>">
                                                <span class="input-group-text">TND</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Mode de paiement</label>
                                            <select class="form-select <?php echo fieldInvalid($validationErrors, 'mode'); ?>" name="mode_paiement">
                                                <option value="Espèces"       <?php echo (($old['mode_paiement'] ?? 'Espèces') === 'Espèces')       ? 'selected' : ''; ?>>Espèces</option>
                                                <option value="Carte Bancaire"<?php echo (($old['mode_paiement'] ?? '') === 'Carte Bancaire')        ? 'selected' : ''; ?>>Carte Bancaire</option>
                                                <option value="Chèque"        <?php echo (($old['mode_paiement'] ?? '') === 'Chèque')                ? 'selected' : ''; ?>>Chèque</option>
                                                <option value="Virement"      <?php echo (($old['mode_paiement'] ?? '') === 'Virement')              ? 'selected' : ''; ?>>Virement</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">État du paiement</label>
                                            <select class="form-select <?php echo fieldInvalid($validationErrors, 'état'); ?>" name="etat_paiement">
                                                <option value="En attente"<?php echo (($old['etat_paiement'] ?? 'En attente') === 'En attente') ? 'selected' : ''; ?>>En attente</option>
                                                <option value="Payée"     <?php echo (($old['etat_paiement'] ?? '') === 'Payée')                ? 'selected' : ''; ?>>Payée</option>
                                                <option value="Annulée"   <?php echo (($old['etat_paiement'] ?? '') === 'Annulée')              ? 'selected' : ''; ?>>Annulée</option>
                                            </select>
                                        </div>

                                        <div class="col-12 mt-5 text-end border-top pt-4">
                                            <button type="reset" class="btn btn-outline-secondary px-4 me-2">Réinitialiser</button>
                                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Enregistrer la Facture</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>

    <?php include("../../partials/back/scripts.html"); ?>
    <script src="../../assets/back/js/vendors/sidebarnav.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertErr = document.getElementById('validation-errors');
        if (alertErr) {
            alertErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    </script>
</body>

</html>