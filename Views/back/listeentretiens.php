<!DOCTYPE html>
<html lang="fr">

<head>
  <?php
  require_once '../../config.php';
  require_once '../../controller/EntretienController.php';
  require_once '../../controller/FactureController.php';
  require_once '../../Models/Entretien.php';

  if (!$pdo) {
      die("Erreur de connexion à la base de données. Vérifiez config.php.");
  }

  $controller = new EntretienController($pdo);
  $factureController = new FactureController($pdo);

  // Suppression entretien
  if (isset($_GET['delete'])) {
      $id = $_GET['delete'];
      if ($controller->deleteEntretien($id)) {
          header('Location: listeentretiens.php?deleted=1');
          exit;
      } else {
          $error = "Erreur lors de la suppression.";
      }
  }

  // Charger la liste
  $list = $controller->listEntretiens();

  // Pour chaque entretien, vérifier si une facture existe
  $facturesParEntretien = [];
  foreach ($list as $row) {
      $factures = $factureController->listByEntretien($row['id_entretien']);
      $facturesParEntretien[$row['id_entretien']] = $factures;
  }

  $message = '';
  if (isset($_GET['success']))      $message = '<div class="alert alert-success alert-dismissible fade show">Entretien ajouté avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  elseif (isset($_GET['updated']))  $message = '<div class="alert alert-success alert-dismissible fade show">Entretien modifié avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  elseif (isset($_GET['deleted']))  $message = '<div class="alert alert-success alert-dismissible fade show">Entretien supprimé avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  elseif (isset($_GET['fact_ok']))  $message = '<div class="alert alert-success alert-dismissible fade show">Facture enregistrée avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  elseif (isset($error))            $message = '<div class="alert alert-danger">' . $error . '</div>';
  ?>
  <?php include '../../partials/back/head/head-meta.html'; ?>
  <title>Liste des Entretiens - Dasher</title>
  <?php include '../../partials/back/head/head-links.html'; ?>
</head>

<body>
  <div>
    <?php include '../../partials/back/sidebar-collapse.html'; ?>

    <div id="content" class="position-relative h-100">
      <?php include '../../partials/back/topbar-second.html'; ?>

      <div class="custom-container">

        <!-- Breadcrumb -->
        <div class="row mb-4">
          <div class="col-12">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php" class="text-muted">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Entretiens</li>
              </ol>
            </nav>
          </div>
        </div>

        <?php if ($message) echo $message; ?>

        <div class="row mb-6 g-6">
          <div class="col-12">
            <div class="card card-lg">
              <div class="card-body">

                <!-- Titre + Bouton Ajouter -->
                <div class="d-flex justify-content-between align-items-center mb-6">
                  <div class="d-flex align-items-center gap-3">
                    <div class="icon-shape icon-lg rounded-circle bg-primary-darker text-primary-lighter">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4" />
                        <path d="M14.5 5.5l4 4" /><path d="M12 8l-5 -5l-4 4l5 5" />
                        <path d="M7 8l-1.5 1.5" /><path d="M16 12l5 5l-4 4l-5 -5" />
                        <path d="M16 17l-1.5 1.5" />
                      </svg>
                    </div>
                    <div>
                      <h5 class="mb-0">Liste des Entretiens</h5>
                      <small class="text-muted">Gérer tous les entretiens</small>
                    </div>
                  </div>
                  <a href="ajouter_entretien.php" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <path d="M12 5l0 14" /><path d="M5 12l14 0" />
                    </svg>
                    Ajouter un entretien
                  </a>
                </div>

                <!-- Filtres -->
                <div class="row g-3 mb-5">
                  <div class="col-md-4">
                    <div class="input-group">
                      <span class="input-group-text bg-transparent border-end-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                          <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                          <path d="M21 21l-6 -6" />
                        </svg>
                      </span>
                      <input type="text" class="form-control border-start-0" placeholder="Rechercher...">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <select class="form-select">
                      <option value="">Tous les statuts</option>
                      <option value="planifie">Planifié</option>
                      <option value="en_cours">En cours</option>
                      <option value="termine">Terminé</option>
                      <option value="annule">Annulé</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <input type="date" class="form-control">
                  </div>
                </div>

                <!-- Tableau -->
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>ID Voiture</th>
                        <th>Date entretien</th>
                        <th>Kilométrage</th>
                        <th>Type intervention</th>
                        <th>Statut</th>
                        <th>Prochaine échéance</th>
                        <th>KM prochain</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (!empty($list)): ?>
                        <?php foreach ($list as $row): ?>
                          <?php
                            $statut = $row['statut'];
                            $badges = [
                              'planifie' => 'text-warning-emphasis bg-warning-subtle',
                              'en_cours' => 'text-info-emphasis bg-info-subtle',
                              'termine'  => 'text-success-emphasis bg-success-subtle',
                              'annule'   => 'text-danger-emphasis bg-danger-subtle',
                            ];
                            $labels = [
                              'planifie' => 'Planifié',
                              'en_cours' => 'En cours',
                              'termine'  => 'Terminé',
                              'annule'   => 'Annulé',
                            ];
                            $badgeClass = $badges[$statut] ?? 'bg-secondary';
                            $label = $labels[$statut] ?? $statut;

                            // Vérifier si une facture existe pour cet entretien
                            $facturesEntretien = $facturesParEntretien[$row['id_entretien']] ?? [];
                            $aFacture = !empty($facturesEntretien);
                            $facture = $aFacture ? $facturesEntretien[0] : null;
                          ?>
                          <tr>
                            <td>V-<?php echo $row['id_voiture']; ?></td>
                            <td><?php echo $row['date_entretien']; ?></td>
                            <td><?php echo number_format($row['kilometrage'], 0, ',', ' '); ?> km</td>
                            <td><?php echo htmlspecialchars($row['type_intervention'] ?? ''); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $label; ?></span></td>
                            <td><?php echo !empty($row['prochaine_echeance']) ? $row['prochaine_echeance'] : '-'; ?></td>
                            <td><?php echo !empty($row['km_prochain']) ? number_format($row['km_prochain'], 0, ',', ' ') . ' km' : '-'; ?></td>
                            <td class="text-end">
                              <div class="d-flex justify-content-end gap-2 flex-wrap">

                                <!-- Bouton Modifier entretien -->
                                <a href="modifier_entretien.php?id=<?php echo $row['id_entretien']; ?>"
                                  class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                                    <path d="M16 5l3 3" />
                                  </svg>
                                  Modifier
                                </a>

                                <!-- Bouton Facture : intelligent -->
                                <?php if ($aFacture): ?>
                                  <!-- Facture existe → ouvrir modal avec infos -->
                                  <button type="button"
                                    class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 btn-voir-facture"
                                    data-id-facture="<?php echo $facture['id_facture']; ?>"
                                    data-ref="<?php echo htmlspecialchars($facture['ref_facture']); ?>"
                                    data-date="<?php echo $facture['date_emission']; ?>"
                                    data-ht="<?php echo $facture['montant_ht']; ?>"
                                    data-tva="<?php echo $facture['taux_tva']; ?>"
                                    data-ttc="<?php echo $facture['montant_ttc']; ?>"
                                    data-mode="<?php echo htmlspecialchars($facture['mode_paiement']); ?>"
                                    data-etat="<?php echo htmlspecialchars($facture['etat_paiement']); ?>"
                                    data-entretien="<?php echo $row['id_entretien']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                      <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                      <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                      <path d="M9 15l2 2l4 -4" />
                                    </svg>
                                    Voir facture
                                  </button>
                                <?php else: ?>
                                  <!-- Pas de facture → lien vers ajouter_facture -->
                                  <a href="ajouter_facture.php?entretien=<?php echo $row['id_entretien']; ?>"
                                    class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                      <path d="M14 3v4a1 1 0 0 0 1 1h4" />
                                      <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
                                      <path d="M12 11l0 4" /><path d="M10 13l4 0" />
                                    </svg>
                                    Ajouter facture
                                  </a>
                                <?php endif; ?>

                                <!-- Bouton Supprimer entretien -->
                                <a href="?delete=<?php echo $row['id_entretien']; ?>"
                                  class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                  onclick="return confirm('Supprimer cet entretien ?')">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" />
                                    <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                                    <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
                                  </svg>
                                  Supprimer
                                </a>

                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="9" class="text-center text-muted py-5">
                            Aucun entretien trouvé.<br>
                            <a href="ajouter_entretien.php" class="btn btn-primary btn-sm mt-3">Ajouter le premier</a>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                  <small class="text-muted">Total : <?php echo count($list); ?> entretien(s)</small>
                  <nav>
                    <ul class="pagination pagination-sm mb-0">
                      <li class="page-item disabled"><a class="page-link" href="#">Précédent</a></li>
                      <li class="page-item active"><a class="page-link" href="#">1</a></li>
                      <li class="page-item"><a class="page-link" href="#">Suivant</a></li>
                    </ul>
                  </nav>
                </div>

              </div>
            </div>
          </div>
        </div>

      </div><!-- end custom-container -->
    </div><!-- end #content -->
  </div>

  <!-- ===== MODAL : Voir / Modifier / Supprimer Facture ===== -->
  <div class="modal fade" id="modalVoirFacture" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-success">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M14 3v4a1 1 0 0 0 1 1h4" />
              <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z" />
              <path d="M9 15l2 2l4 -4" />
            </svg>
            Détails de la Facture
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body px-5 py-4">

          <!-- Mode consultation -->
          <div id="mode-consultation">
            <div class="row g-4 mb-4">
              <div class="col-md-6">
                <div class="bg-light rounded-3 p-4">
                  <small class="text-muted d-block mb-1">Référence facture</small>
                  <strong id="view-ref" class="fs-6"></strong>
                </div>
              </div>
              <div class="col-md-6">
                <div class="bg-light rounded-3 p-4">
                  <small class="text-muted d-block mb-1">Date d'émission</small>
                  <strong id="view-date" class="fs-6"></strong>
                </div>
              </div>
              <div class="col-md-4">
                <div class="bg-light rounded-3 p-4">
                  <small class="text-muted d-block mb-1">Montant HT</small>
                  <strong id="view-ht" class="fs-6"></strong>
                </div>
              </div>
              <div class="col-md-4">
                <div class="bg-light rounded-3 p-4">
                  <small class="text-muted d-block mb-1">Taux TVA</small>
                  <strong id="view-tva" class="fs-6"></strong>
                </div>
              </div>
              <div class="col-md-4">
                <div class="bg-light rounded-3 p-4">
                  <small class="text-muted d-block mb-1">Montant TTC</small>
                  <strong id="view-ttc" class="fs-6 text-success"></strong>
                </div>
              </div>
              <div class="col-md-6">
                <div class="bg-light rounded-3 p-4">
                  <small class="text-muted d-block mb-1">Mode de paiement</small>
                  <strong id="view-mode" class="fs-6"></strong>
                </div>
              </div>
              <div class="col-md-6">
                <div class="bg-light rounded-3 p-4">
                  <small class="text-muted d-block mb-1">État du paiement</small>
                  <strong id="view-etat" class="fs-6"></strong>
                </div>
              </div>
            </div>
          </div>

          <!-- Mode modification (formulaire caché par défaut) -->
          <div id="mode-modification" style="display:none;">
            <form id="form-modifier-facture">
              <input type="hidden" id="edit-id-facture" name="id_facture">
              <input type="hidden" id="edit-entretien" name="entretien">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label fw-medium">Référence facture</label>
                  <input type="text" class="form-control" id="edit-ref" name="ref_facture" >
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Date d'émission</label>
                  <input type="date" class="form-control" id="edit-date" name="date_emission">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-medium">Montant HT</label>
                  <input type="number" step="0.001" class="form-control" id="edit-ht" name="montant_ht">
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-medium">Taux TVA (%)</label>
                  <select class="form-select" id="edit-tva" name="taux_tva">
                    <option value="19">19%</option>
                    <option value="13">13%</option>
                    <option value="7">7%</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-medium">Montant TTC</label>
                  <input type="number" step="0.001" class="form-control" id="edit-ttc" name="montant_ttc">
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">Mode de paiement</label>
                  <select class="form-select" id="edit-mode" name="mode_paiement">
                    <option value="Espèces">Espèces</option>
                    <option value="Carte Bancaire">Carte Bancaire</option>
                    <option value="Chèque">Chèque</option>
                    <option value="Virement">Virement</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-medium">État du paiement</label>
                  <select class="form-select" id="edit-etat" name="etat_paiement">
                    <option value="En attente">En attente</option>
                    <option value="Payée">Payée</option>
                    <option value="Annulée">Annulée</option>
                  </select>
                </div>
              </div>
              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 12l5 5l10 -10" />
                  </svg>
                  Sauvegarder
                </button>
                <button type="button" class="btn btn-outline-secondary" id="btn-annuler-modif">Annuler</button>
              </div>
            </form>
          </div>

        </div>

        <div class="modal-footer border-0 pt-0 justify-content-between">
          <!-- Bouton retour liste -->
          <a href="listeentretiens.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" />
            </svg>
            Retour à la liste
          </a>
          <div class="d-flex gap-2" id="btns-consultation">
            <!-- Bouton Modifier -->
            <button type="button" class="btn btn-warning d-flex align-items-center gap-2" id="btn-ouvrir-modif">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                <path d="M16 5l3 3" />
              </svg>
              Modifier
            </button>
            <!-- Bouton Supprimer -->
            <button type="button" class="btn btn-danger d-flex align-items-center gap-2" id="btn-supprimer-facture">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M4 7l16 0" /><path d="M10 11l0 6" /><path d="M14 11l0 6" />
                <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12" />
                <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3" />
              </svg>
              Supprimer facture
            </button>
          </div>
        </div>

      </div>
    </div>
  </div>

  <?php include '../../partials/back/scripts.html'; ?>
  <script src="../../assets/back/js/vendors/sidebarnav.js"></script>

  <script>
  (function () {
    let currentFactureId = null;
  let currentEntretienId = null;

  // ✅ FIX : Event Delegation (بدل querySelectorAll)
  document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-voir-facture');
    if (!btn) return;

    currentFactureId  = btn.dataset.idFacture;
    currentEntretienId = btn.dataset.entretien;

    // Remplissage affichage
    document.getElementById('view-ref').textContent  = btn.dataset.ref || '—';
    document.getElementById('view-date').textContent = btn.dataset.date || '—';
    document.getElementById('view-ht').textContent   = btn.dataset.ht ? parseFloat(btn.dataset.ht).toFixed(3) + ' TND' : '—';
    document.getElementById('view-tva').textContent  = btn.dataset.tva ? btn.dataset.tva + '%' : '—';
    document.getElementById('view-ttc').textContent  = btn.dataset.ttc ? parseFloat(btn.dataset.ttc).toFixed(3) + ' TND' : '—';
    document.getElementById('view-mode').textContent = btn.dataset.mode || '—';
    document.getElementById('view-etat').textContent = btn.dataset.etat || '—';

    // Remplissage formulaire modification
    document.getElementById('edit-id-facture').value = currentFactureId;
    document.getElementById('edit-entretien').value  = currentEntretienId;
    document.getElementById('edit-ref').value        = btn.dataset.ref || '';
    document.getElementById('edit-date').value       = btn.dataset.date || '';
    document.getElementById('edit-ht').value         = btn.dataset.ht || '';
    document.getElementById('edit-tva').value        = btn.dataset.tva || '19';
    document.getElementById('edit-ttc').value        = btn.dataset.ttc || '';
    document.getElementById('edit-mode').value       = btn.dataset.mode || 'Espèces';
    document.getElementById('edit-etat').value       = btn.dataset.etat || 'En attente';

    afficherConsultation();

    new bootstrap.Modal(document.getElementById('modalVoirFacture')).show();
  });

  function afficherConsultation() {
    document.getElementById('mode-consultation').style.display = 'block';
    document.getElementById('mode-modification').style.display = 'none';
    document.getElementById('btns-consultation').style.display = 'flex';
  }

  function afficherModification() {
    document.getElementById('mode-consultation').style.display = 'none';
    document.getElementById('mode-modification').style.display = 'block';
    document.getElementById('btns-consultation').style.display = 'none';
  }

  document.getElementById('btn-ouvrir-modif').addEventListener('click', afficherModification);
  document.getElementById('btn-annuler-modif').addEventListener('click', afficherConsultation);

  // Calcul TTC
  document.getElementById('edit-ht').addEventListener('input', computeEditTTC);
  document.getElementById('edit-tva').addEventListener('change', computeEditTTC);

  function computeEditTTC() {
    const ht  = parseFloat(document.getElementById('edit-ht').value) || 0;
    const tva = parseFloat(document.getElementById('edit-tva').value) || 0;
    document.getElementById('edit-ttc').value = (ht * (1 + tva / 100)).toFixed(3);
  }

  // Update facture
  document.getElementById('form-modifier-facture').addEventListener('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    fetch('../../controller/facture_action.php?action=update', {
      method: 'POST',
      body: formData
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalVoirFacture')).hide();
        window.location.href = 'listeentretiens.php?fact_ok=1';
      } else {
        alert('Erreur modification facture');
      }
    });
  });

  // Delete facture
  document.getElementById('btn-supprimer-facture').addEventListener('click', function () {
    if (!confirm('Supprimer cette facture ?')) return;

    const fd = new FormData();
    fd.append('id', currentFactureId);

    fetch('../../controller/facture_action.php?action=delete', {
      method: 'POST',
      body: fd
    })
    .then(r => r.json())
    .then(res => {
      if (res.success) {
        bootstrap.Modal.getInstance(document.getElementById('modalVoirFacture')).hide();
        window.location.href = 'listeentretiens.php?deleted=1';
      } else {
        alert('Erreur suppression');
      }
    });
  });

  })();
  </script>

</body>
</html>