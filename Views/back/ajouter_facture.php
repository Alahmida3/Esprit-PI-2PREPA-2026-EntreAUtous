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
                                <form action="../../controllers/factureController.php" method="POST">
                                    <div class="row g-4">
                                        
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold text-primary">ID Facture</label>
                                            <input type="text" class="form-control" name="id_facture" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Référence Facture</label>
                                            <input type="text" class="form-control" name="ref_facture" placeholder="Ex: FAC-001" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Date d'émission</label>
                                            <input type="date" class="form-control" name="date_emission" required>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">ID Entretien lié</label>
                                            <input type="text" class="form-control" name="id_entretien">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">ID Rendez-vous</label>
                                            <input type="text" class="form-control" name="id_rdv">
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Montant HT</label>
                                            <div class="input-group">
                                                <input type="number" step="0.001" class="form-control" name="montant_ht">
                                                <span class="input-group-text">TND</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Taux TVA (%)</label>
                                            <select class="form-select" name="taux_tva">
                                                <option value="19">19%</option>
                                                <option value="13">13%</option>
                                                <option value="7">7%</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Montant TTC</label>
                                            <div class="input-group">
                                                <input type="number" step="0.001" class="form-control" name="montant_ttc">
                                                <span class="input-group-text">TND</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Mode de paiement</label>
                                            <select class="form-select" name="mode_paiement">
                                                <option value="Espèces">Espèces</option>
                                                <option value="Carte Bancaire">Carte Bancaire</option>
                                                <option value="Chèque">Chèque</option>
                                                <option value="Virement">Virement</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">État du paiement</label>
                                            <select class="form-select" name="etat_paiement">
                                                <option value="En attente">En attente</option>
                                                <option value="Payée">Payée</option>
                                                <option value="Annulée">Annulée</option>
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
</body>

</html>