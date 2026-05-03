<!DOCTYPE html>
<html lang="fr">

<head>
  <?php
  require_once '../../config.php';
  require_once '../../controller/EntretienController.php';
  require_once '../../controller/FactureController.php';
  require_once '../../Models/Entretien.php';
  require_once '../../Models/Facture.php';

  /* VIEW ONLY — les actions restore sont POST vers entretien_action.php */
  $entController  = new EntretienController($pdo);
  $factController = new FactureController($pdo);

  // ── Messages flash ─────────────────────────────────────────
  $message = '';
  if (isset($_GET['restored_ent'])) $message = '<div class="alert alert-success alert-dismissible fade show">Entretien restauré avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  if (isset($_GET['restored_fac'])) $message = '<div class="alert alert-success alert-dismissible fade show">Facture restaurée avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';

  // ── Données (lecture seule) ────────────────────────────────
  $actifsEnt = $entController->listEntretiens();
  $supprEnt  = $entController->listDeleted();
  $actifsFac = $factController->listAllFactures();
  $supprFac  = $factController->listDeleted();

  $badges = ['planifie'=>'text-warning-emphasis bg-warning-subtle','en_cours'=>'text-info-emphasis bg-info-subtle','termine'=>'text-success-emphasis bg-success-subtle','annule'=>'text-danger-emphasis bg-danger-subtle'];
  $labels = ['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé'];
  ?>
  <?php include '../../partials/back/head/head-meta.html'; ?>
  <title>Historique - Dasher</title>
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
              <li class="breadcrumb-item active">Historique</li>
            </ol>
          </nav>
        </div>
      </div>
      <!-- À ajouter dans le breadcrumb ou à côté, par exemple après le titre -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <nav aria-label="breadcrumb">
    <ol class="breadcrumb">
      <li class="breadcrumb-item"><a href="../../index.php" class="text-muted">Accueil</a></li>
      <li class="breadcrumb-item active">Historique</li>
    </ol>
  </nav>
  <a href="listeentretiens.php" class="btn btn-outline-primary">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
      <path d="M5 12l14 0" />
      <path d="M5 12l6 6" />
      <path d="M5 12l6 -6" />
    </svg>
    Retour à la liste des entretiens
  </a>
</div>

      <?php if ($message) echo $message; ?>

      <!-- Onglets -->
      <ul class="nav nav-tabs mb-5" id="histTabs" role="tablist">
        <li class="nav-item">
          <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-ent-actifs">
            <i class="ti ti-tools me-1"></i> Entretiens actifs
            <span class="badge bg-primary ms-1"><?php echo count($actifsEnt); ?></span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ent-suppr">
            <i class="ti ti-trash me-1"></i> Entretiens supprimés
            <span class="badge bg-danger ms-1"><?php echo count($supprEnt); ?></span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fac-actifs">
            <i class="ti ti-file-invoice me-1"></i> Factures actives
            <span class="badge bg-success ms-1"><?php echo count($actifsFac); ?></span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fac-suppr">
            <i class="ti ti-trash me-1"></i> Factures supprimées
            <span class="badge bg-danger ms-1"><?php echo count($supprFac); ?></span>
          </button>
        </li>
      </ul>

      <div class="tab-content">

        <!-- ── Tab : Entretiens actifs ──────────────────────── -->
        <div class="tab-pane fade show active" id="tab-ent-actifs">
          <div class="card card-lg">
            <div class="card-body">
              <h5 class="mb-5">Entretiens actifs</h5>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th><th>Matricule</th><th>Date</th><th>KM</th>
                      <th>Type</th><th>Statut</th><th>Prochaine échéance</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($actifsEnt)): foreach ($actifsEnt as $r): ?>
                      <?php $bc = $badges[$r['statut']] ?? 'bg-secondary'; $lb = $labels[$r['statut']] ?? $r['statut']; ?>
                      <tr>
                        <td><span class="text-muted small">#<?php echo $r['id_entretien']; ?></span></td>
                        <td><span class="badge" style="background:#ffc800;color:#212529;"><?php echo htmlspecialchars($r['Matricule']); ?></span></td>
                        <td><?php echo $r['date_entretien']; ?></td>
                        <td><?php echo number_format($r['kilometrage'],0,',',' '); ?> km</td>
                        <td><?php echo htmlspecialchars($r['type_intervention'] ?? '—'); ?></td>
                        <td><span class="badge <?php echo $bc; ?>"><?php echo $lb; ?></span></td>
                        <td><?php echo $r['prochaine_echeance'] ?: '—'; ?></td>
                      </tr>
                    <?php endforeach; else: ?>
                      <tr><td colspan="7" class="text-center text-muted py-4">Aucun entretien actif.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Tab : Entretiens supprimés ──────────────────── -->
        <div class="tab-pane fade" id="tab-ent-suppr">
          <div class="card card-lg">
            <div class="card-body">
              <h5 class="mb-5 text-danger">
                <i class="ti ti-trash me-2"></i>Entretiens supprimés (corbeille)
              </h5>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th><th>Matricule</th><th>Date</th><th>KM</th>
                      <th>Type</th><th>Statut</th><th>Supprimé le</th><th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($supprEnt)): foreach ($supprEnt as $r): ?>
                      <?php $bc = $badges[$r['statut']] ?? 'bg-secondary'; $lb = $labels[$r['statut']] ?? $r['statut']; ?>
                      <tr class="table-danger">
                        <td><span class="text-muted small">#<?php echo $r['id_entretien']; ?></span></td>
                        <td><span class="badge" style="background:#ffc800;color:#212529;"><?php echo htmlspecialchars($r['Matricule']); ?></span></td>
                        <td><?php echo $r['date_entretien']; ?></td>
                        <td><?php echo number_format($r['kilometrage'],0,',',' '); ?> km</td>
                        <td><?php echo htmlspecialchars($r['type_intervention'] ?? '—'); ?></td>
                        <td><span class="badge <?php echo $bc; ?>"><?php echo $lb; ?></span></td>
                        <td><small class="text-muted"><?php echo $r['deleted_at']; ?></small></td>
                        <td>
                          <form method="POST" action="../../controller/entretien_action.php?action=restore_ent"
                            onsubmit="return confirm('Restaurer cet entretien ?');" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $r['id_entretien']; ?>">
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 11l-4 4l4 4m-4-4h11a4 4 0 0 0 0 -8h-1"/>
                              </svg>
                              Restaurer
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; else: ?>
                      <tr><td colspan="8" class="text-center text-muted py-4">Aucun entretien supprimé.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Tab : Factures actives ───────────────────────── -->
        <div class="tab-pane fade" id="tab-fac-actifs">
          <div class="card card-lg">
            <div class="card-body">
              <h5 class="mb-5">Factures actives</h5>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th><th>Référence</th><th>Matricule</th><th>Date</th>
                      <th>HT</th><th>TVA</th><th>TTC</th><th>Mode</th><th>État</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($actifsFac)): foreach ($actifsFac as $f): ?>
                      <tr>
                        <td><span class="text-muted small">#<?php echo $f['id_facture']; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($f['ref_facture']); ?></strong></td>
                        <td><span class="badge" style="background:#ffc800;color:#212529;"><?php echo htmlspecialchars($f['Matricule'] ?? '—'); ?></span></td>
                        <td><?php echo $f['date_emission']; ?></td>
                        <td><?php echo number_format($f['montant_ht'],3,'.',' '); ?> TND</td>
                        <td><?php echo $f['taux_tva']; ?>%</td>
                        <td><strong><?php echo number_format($f['montant_ttc'],3,'.',' '); ?> TND</strong></td>
                        <td><?php echo htmlspecialchars($f['mode_paiement']); ?></td>
                        <td>
                          <?php $ep = $f['etat_paiement'];
                          $ec = ['Payée'=>'text-success-emphasis bg-success-subtle','En attente'=>'text-warning-emphasis bg-warning-subtle','Annulée'=>'text-danger-emphasis bg-danger-subtle'];
                          ?>
                          <span class="badge <?php echo $ec[$ep] ?? 'bg-secondary'; ?>"><?php echo htmlspecialchars($ep); ?></span>
                        </td>
                      </tr>
                    <?php endforeach; else: ?>
                      <tr><td colspan="9" class="text-center text-muted py-4">Aucune facture active.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- ── Tab : Factures supprimées ────────────────────── -->
        <div class="tab-pane fade" id="tab-fac-suppr">
          <div class="card card-lg">
            <div class="card-body">
              <h5 class="mb-5 text-danger">
                <i class="ti ti-trash me-2"></i>Factures supprimées (corbeille)
              </h5>
              <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>#</th><th>Référence</th><th>Matricule</th><th>Date</th>
                      <th>TTC</th><th>État</th><th>Supprimée le</th><th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($supprFac)): foreach ($supprFac as $f): ?>
                      <?php $ep=$f['etat_paiement']; $ec=['Payée'=>'text-success-emphasis bg-success-subtle','En attente'=>'text-warning-emphasis bg-warning-subtle','Annulée'=>'text-danger-emphasis bg-danger-subtle']; ?>
                      <tr class="table-danger">
                        <td><span class="text-muted small">#<?php echo $f['id_facture']; ?></span></td>
                        <td><strong><?php echo htmlspecialchars($f['ref_facture']); ?></strong></td>
                        <td><span class="badge" style="background:#ffc800;color:#212529;"><?php echo htmlspecialchars($f['Matricule'] ?? '—'); ?></span></td>
                        <td><?php echo $f['date_emission']; ?></td>
                        <td><strong><?php echo number_format($f['montant_ttc'],3,'.',' '); ?> TND</strong></td>
                        <td><span class="badge <?php echo $ec[$ep] ?? 'bg-secondary'; ?>"><?php echo htmlspecialchars($ep); ?></span></td>
                        <td><small class="text-muted"><?php echo $f['deleted_at']; ?></small></td>
                        <td>
                          <form method="POST" action="../../controller/entretien_action.php?action=restore_fac"
                            onsubmit="return confirm('Restaurer cette facture ?');" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo $f['id_facture']; ?>">
                            <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                <path d="M9 11l-4 4l4 4m-4-4h11a4 4 0 0 0 0 -8h-1"/>
                              </svg>
                              Restaurer
                            </button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach; else: ?>
                      <tr><td colspan="8" class="text-center text-muted py-4">Aucune facture supprimée.</td></tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

      </div><!-- end tab-content -->

    </div><!-- end custom-container -->
  </div><!-- end #content -->
</div>

<?php include '../../partials/back/scripts.html'; ?>
<script src="../../assets/back/js/vendors/sidebarnav.js"></script>
</body>
</html>