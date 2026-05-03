<!DOCTYPE html>
<html lang="fr">

<head>
  <?php
  /* ═══════════════════════════════════════════════════════════
   *  VIEW ONLY — aucune logique métier ici.
   *  Toutes les actions (delete, restore…) passent par
   *  controller/entretien_action.php
   * ═══════════════════════════════════════════════════════════ */
  require_once '../../config.php';
  require_once '../../controller/EntretienController.php';
  require_once '../../controller/FactureController.php';

  if (!$pdo) die("Erreur de connexion à la base de données.");

  $controller        = new EntretienController($pdo);
  $factureController = new FactureController($pdo);

  // ── Filtres (lecture seule) ────────────────────────────────
  $filterStatut = isset($_GET['statut']) ? trim($_GET['statut']) : '';
  $filterDate   = isset($_GET['date'])   ? trim($_GET['date'])   : '';
  $filterSort   = isset($_GET['sort'])   ? trim($_GET['sort'])   : 'recent';

  // ── Pagination ─────────────────────────────────────────────
  $parPage     = 5;
  $pageCourante = max(1, (int)($_GET['page'] ?? 1));

  // ── Données via le contrôleur ──────────────────────────────
  $allList = $controller->listFiltered(
      $filterStatut !== '' ? $filterStatut : null,
      $filterDate   !== '' ? $filterDate   : null,
      $filterSort
  );
  $stats       = $controller->getStatsByStatut();
  $totalItems  = count($allList);
  $totalPages  = max(1, (int)ceil($totalItems / $parPage));
  $pageCourante = min($pageCourante, $totalPages);
  $offset      = ($pageCourante - 1) * $parPage;
  $list        = array_slice($allList, $offset, $parPage);

  // Helper URL pagination (conserve les filtres actifs)
  function paginationUrl($page, $statut, $date, $sort) {
      $params = array_filter(['page'=>$page,'statut'=>$statut,'date'=>$date,'sort'=>$sort]);
      return 'listeentretiens.php?' . http_build_query($params);
  }

  // Factures par entretien
  $facturesParEntretien = [];
  foreach ($list as $row) {
      $facturesParEntretien[$row['id_entretien']] =
          $factureController->listByEntretien($row['id_entretien']);
  }

  // ── Messages flash ─────────────────────────────────────────
  $message = '';
  if (isset($_GET['success']))     $message = '<div class="alert alert-success alert-dismissible fade show">Entretien ajouté avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  elseif (isset($_GET['updated'])) $message = '<div class="alert alert-success alert-dismissible fade show">Entretien modifié avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  elseif (isset($_GET['deleted'])) $message = '<div class="alert alert-success alert-dismissible fade show">Entretien supprimé avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
  elseif (isset($_GET['fact_ok'])) $message = '<div class="alert alert-success alert-dismissible fade show">Facture enregistrée avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';

  // ── Helpers d'affichage ────────────────────────────────────
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

        <!-- ══ Statistiques par statut ══════════════════════════ -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-md-3">
            <a href="?statut=planifie" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #ffc107 !important;">
                <div class="fs-2 fw-bold text-warning"><?php echo $stats['planifie']; ?></div>
                <small class="text-muted">Planifiés</small>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="?statut=en_cours" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #0dcaf0 !important;">
                <div class="fs-2 fw-bold text-info"><?php echo $stats['en_cours']; ?></div>
                <small class="text-muted">En cours</small>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="?statut=termine" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #198754 !important;">
                <div class="fs-2 fw-bold text-success"><?php echo $stats['termine']; ?></div>
                <small class="text-muted">Terminés</small>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="?statut=annule" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #dc3545 !important;">
                <div class="fs-2 fw-bold text-danger"><?php echo $stats['annule']; ?></div>
                <small class="text-muted">Annulés</small>
              </div>
            </a>
          </div>
        </div>

        <div class="row mb-6 g-6">
          <div class="col-12">
            <div class="card card-lg">
              <div class="card-body">

                <!-- Titre + Boutons -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <div class="d-flex align-items-center gap-3">
                    <div class="icon-shape icon-lg rounded-circle bg-primary-darker text-primary-lighter">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4"/>
                        <path d="M14.5 5.5l4 4"/><path d="M12 8l-5 -5l-4 4l5 5"/>
                        <path d="M7 8l-1.5 1.5"/><path d="M16 12l5 5l-4 4l-5 -5"/><path d="M16 17l-1.5 1.5"/>
                      </svg>
                    </div>
                    <div>
                      <h5 class="mb-0">Liste des Entretiens</h5>
                      <small class="text-muted">
                        <?php echo $stats['total']; ?> entretien(s) au total
                        <?php if ($filterStatut || $filterDate): ?> — <span class="text-primary fw-semibold">filtre actif</span><?php endif; ?>
                      </small>
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" id="btn-analyse-ia" class="btn btn-dark d-flex align-items-center gap-2"
                            style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);border:1px solid #e94560;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="#e94560" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9.5 2a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/>
                        <path d="M14.5 17a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/>
                        <path d="M4.5 14.5a3 3 0 1 1 0 6 3 3 0 0 1 0-6z"/>
                        <path d="M14.5 4.5l-10 10M9.5 4.5l5 3-3 3.5"/>
                      </svg>
                      <span style="color:#e94560;font-weight:600;">Analyse IA</span>
                    </button>
                    <a href="historique_entretien.php" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                      <i class="ti ti-history"></i> Historique
                    </a>
                    <a href="ajouter_entretien.php" class="btn btn-primary d-flex align-items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 5l0 14"/><path d="M5 12l14 0"/>
                      </svg>
                      Ajouter un entretien
                    </a>
                  </div>
                </div>

                <!-- ══ Filtres ══════════════════════════════════════ -->
                <form method="GET" action="listeentretiens.php" class="row g-3 mb-4 align-items-end">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Statut</label>
                    <select class="form-select" name="statut">
                      <option value="">Tous les statuts</option>
                      <option value="planifie" <?php echo $filterStatut === 'planifie' ? 'selected' : ''; ?>>Planifié</option>
                      <option value="en_cours" <?php echo $filterStatut === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                      <option value="termine"  <?php echo $filterStatut === 'termine'  ? 'selected' : ''; ?>>Terminé</option>
                      <option value="annule"   <?php echo $filterStatut === 'annule'   ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Date d'entretien</label>
                    <input type="text" class="form-control" name="date" value="<?php echo htmlspecialchars($filterDate); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Trier par date</label>
                    <select class="form-select" name="sort">
                      <option value="recent" <?php echo $filterSort === 'recent' ? 'selected' : ''; ?>>Plus récent</option>
                      <option value="oldest" <?php echo $filterSort === 'oldest' ? 'selected' : ''; ?>>Plus ancien</option>
                    </select>
                  </div>
                  <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filtrer</button>
                    <?php if ($filterStatut || $filterDate || $filterSort !== 'recent'): ?>
                      <a href="listeentretiens.php" class="btn btn-outline-secondary" title="Réinitialiser">✕</a>
                    <?php endif; ?>
                  </div>
                </form>

                <!-- Raccourcis rapides par statut -->
                <div class="d-flex gap-2 mb-4 flex-wrap">
                  <a href="listeentretiens.php" class="btn btn-sm <?php echo !$filterStatut ? 'btn-dark' : 'btn-outline-secondary'; ?>">
                    Tous <span class="badge bg-secondary ms-1"><?php echo $stats['total']; ?></span>
                  </a>
                  <a href="?statut=planifie" class="btn btn-sm <?php echo $filterStatut === 'planifie' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                    Planifiés <span class="badge ms-1"><?php echo $stats['planifie']; ?></span>
                  </a>
                  <a href="?statut=en_cours" class="btn btn-sm <?php echo $filterStatut === 'en_cours' ? 'btn-info' : 'btn-outline-info'; ?>">
                    En cours <span class="badge ms-1"><?php echo $stats['en_cours']; ?></span>
                  </a>
                  <a href="?statut=termine" class="btn btn-sm <?php echo $filterStatut === 'termine' ? 'btn-success' : 'btn-outline-success'; ?>">
                    Terminés <span class="badge ms-1"><?php echo $stats['termine']; ?></span>
                  </a>
                  <a href="?statut=annule" class="btn btn-sm <?php echo $filterStatut === 'annule' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                    Annulés <span class="badge ms-1"><?php echo $stats['annule']; ?></span>
                  </a>
                </div>

                <!-- ══ Tableau ══════════════════════════════════════ -->
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Matricule</th>
                        <th>Date entretien</th>
                        <th>Kilométrage</th>
                        <th>Type intervention</th>
                        <th>Statut</th>
                        <th>Prochaine échéance</th>
                        <th>KM prochain</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="entretien-tbody">
                      <?php if (!empty($list)): ?>
                        <?php foreach ($list as $row): ?>
                          <?php
                            $statut     = $row['statut'];
                            $badgeClass = $badges[$statut] ?? 'bg-secondary';
                            $label      = $labels[$statut] ?? $statut;
                            $facturesEntretien = $facturesParEntretien[$row['id_entretien']] ?? [];
                            $aFacture          = !empty($facturesEntretien);
                            $facture           = $aFacture ? $facturesEntretien[0] : null;
                          ?>
                          <tr data-row="1">
                            <td><?php echo htmlspecialchars($row['Matricule']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_entretien']); ?></td>
                            <td class="col-km" data-km="<?php echo (int)$row['kilometrage']; ?>"><?php echo number_format($row['kilometrage'], 0, ',', ' '); ?> km</td>
                            <td class="col-type" data-type="<?php echo htmlspecialchars(strtolower(trim($row['type_intervention'] ?? ''))); ?>"><?php echo htmlspecialchars($row['type_intervention'] ?? ''); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $label; ?></span></td>
                            <td><?php echo !empty($row['prochaine_echeance']) ? htmlspecialchars($row['prochaine_echeance']) : '—'; ?></td>
                            <td><?php echo !empty($row['km_prochain']) ? number_format($row['km_prochain'], 0, ',', ' ') . ' km' : '—'; ?></td>
                            <td class="text-end">
                              <div class="d-flex justify-content-end gap-2 flex-wrap">

                                <!-- Modifier -->
                                <a href="modifier_entretien.php?id=<?php echo $row['id_entretien']; ?>"
                                  class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                    <path d="M16 5l3 3"/>
                                  </svg>
                                  Modifier
                                </a>

                                <!-- Facture -->
                                <?php if ($aFacture): ?>
                                  <button type="button"
                                    class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 btn-voir-facture"
                                    data-id-facture="<?php echo $facture['id_facture']; ?>"
                                    data-ref="<?php echo htmlspecialchars($facture['ref_facture']); ?>"
                                    data-date="<?php echo htmlspecialchars($facture['date_emission']); ?>"
                                    data-ht="<?php echo $facture['montant_ht']; ?>"
                                    data-tva="<?php echo $facture['taux_tva']; ?>"
                                    data-ttc="<?php echo $facture['montant_ttc']; ?>"
                                    data-mode="<?php echo htmlspecialchars($facture['mode_paiement']); ?>"
                                    data-etat="<?php echo htmlspecialchars($facture['etat_paiement']); ?>"
                                    data-entretien="<?php echo $row['id_entretien']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                      <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                      <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                      <path d="M9 15l2 2l4 -4"/>
                                    </svg>
                                    Voir facture
                                  </button>
                                <?php else: ?>
                                  <a href="ajouter_facture.php?entretien=<?php echo $row['id_entretien']; ?>"
                                    class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                      <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                      <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                      <path d="M12 11l0 4"/><path d="M10 13l4 0"/>
                                    </svg>
                                    Ajouter facture
                                  </a>
                                <?php endif; ?>

                                <!-- Supprimer — POST vers entretien_action.php -->
                                <form method="POST" action="../../controller/entretien_action.php?action=delete"
                                  onsubmit="return confirm('Supprimer cet entretien ?');" class="d-inline">
                                  <input type="hidden" name="id" value="<?php echo $row['id_entretien']; ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                      <path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/>
                                      <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                      <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                    </svg>
                                    Supprimer
                                  </button>
                                </form>

                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center text-muted py-5">
                            <?php if ($filterStatut || $filterDate): ?>
                              Aucun entretien ne correspond aux filtres sélectionnés.<br>
                              <a href="listeentretiens.php" class="btn btn-outline-secondary btn-sm mt-3">Voir tous</a>
                            <?php else: ?>
                              Aucun entretien trouvé.<br>
                              <a href="ajouter_entretien.php" class="btn btn-primary btn-sm mt-3">Ajouter le premier</a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

                <!-- ══ Pagination ══════════════════════════════════ -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                  <small class="text-muted">
                    <?php echo $offset + 1; ?>–<?php echo min($offset + $parPage, $totalItems); ?>
                    sur <?php echo $totalItems; ?> entretien(s)
                    <?php if ($filterStatut || $filterDate): ?>(filtrés)<?php endif; ?>
                  </small>
                  <ul class="pagination pagination-sm mb-0">
                    <!-- Précédent -->
                    <li class="page-item <?php echo $pageCourante <= 1 ? 'disabled' : ''; ?>">
                      <a class="page-link" href="<?php echo paginationUrl($pageCourante-1,$filterStatut,$filterDate,$filterSort); ?>">‹</a>
                    </li>
                    <!-- Pages numérotées -->
                    <?php
                    $debut = max(1, $pageCourante - 2);
                    $fin   = min($totalPages, $pageCourante + 2);
                    if ($debut > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif;
                    for ($p = $debut; $p <= $fin; $p++): ?>
                      <li class="page-item <?php echo $p === $pageCourante ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo paginationUrl($p,$filterStatut,$filterDate,$filterSort); ?>"><?php echo $p; ?></a>
                      </li>
                    <?php endfor;
                    if ($fin < $totalPages): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                    <!-- Suivant -->
                    <li class="page-item <?php echo $pageCourante >= $totalPages ? 'disabled' : ''; ?>">
                      <a class="page-link" href="<?php echo paginationUrl($pageCourante+1,$filterStatut,$filterDate,$filterSort); ?>">›</a>
                    </li>
                  </ul>
                </nav>
                <?php else: ?>
                <div class="mt-4 pt-3 border-top">
                  <small class="text-muted"><?php echo $totalItems; ?> entretien(s) au total</small>
                </div>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </div>

      </div><!-- end custom-container -->
    </div><!-- end #content -->
  </div>

  <!-- ===== MODAL Facture ===== -->
  <div class="modal fade" id="modalVoirFacture" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title">Détails de la Facture</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-5 py-4">
          <div id="mode-consultation">
            <div class="row g-4 mb-4">
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Référence</small><strong id="view-ref"></strong></div></div>
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Date d'émission</small><strong id="view-date"></strong></div></div>
              <div class="col-md-4"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Montant HT</small><strong id="view-ht"></strong></div></div>
              <div class="col-md-4"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Taux TVA</small><strong id="view-tva"></strong></div></div>
              <div class="col-md-4"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Montant TTC</small><strong id="view-ttc" class="text-success"></strong></div></div>
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Mode paiement</small><strong id="view-mode"></strong></div></div>
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">État paiement</small><strong id="view-etat"></strong></div></div>
            </div>
          </div>
          <div id="mode-modification" style="display:none;">
            <form id="form-modifier-facture">
              <input type="hidden" id="edit-id-facture" name="id_facture">
              <input type="hidden" id="edit-entretien"  name="entretien">
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-medium">Référence</label><input type="text" class="form-control" id="edit-ref" name="ref_facture"></div>
                <div class="col-md-6"><label class="form-label fw-medium">Date d'émission</label><input type="text" class="form-control" id="edit-date" name="date_emission"></div>
                <div class="col-md-4"><label class="form-label fw-medium">Montant HT</label><input type="text" class="form-control" id="edit-ht" name="montant_ht"></div>
                <div class="col-md-4"><label class="form-label fw-medium">Taux TVA</label>
                  <select class="form-select" id="edit-tva" name="taux_tva">
                    <option value="19">19%</option><option value="13">13%</option><option value="7">7%</option>
                  </select>
                </div>
                <div class="col-md-4"><label class="form-label fw-medium">Montant TTC</label><input type="text" class="form-control" id="edit-ttc" name="montant_ttc"></div>
                <div class="col-md-6"><label class="form-label fw-medium">Mode paiement</label>
                  <select class="form-select" id="edit-mode" name="mode_paiement">
                    <option>Espèces</option><option>Carte Bancaire</option><option>Chèque</option><option>Virement</option>
                  </select>
                </div>
                <div class="col-md-6"><label class="form-label fw-medium">État paiement</label>
                  <select class="form-select" id="edit-etat" name="etat_paiement">
                    <option>En attente</option><option>Payée</option><option>Annulée</option>
                  </select>
                </div>
              </div>
              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
                <button type="button" class="btn btn-outline-secondary" id="btn-annuler-modif">Annuler</button>
              </div>
            </form>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 justify-content-between">
          <a href="listeentretiens.php" class="btn btn-outline-secondary">← Retour à la liste</a>
          <div class="d-flex gap-2" id="btns-consultation">
            <button type="button" class="btn btn-warning" id="btn-ouvrir-modif">Modifier</button>
            <button type="button" class="btn btn-danger"  id="btn-supprimer-facture">Supprimer facture</button>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- ===== MODAL ANALYSE IA (Claude API) ===== -->
  <div class="modal fade" id="modalAnalyseIA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" style="background:#0d1117;border:1px solid #30363d;border-radius:16px;color:#e6edf3;">

        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <div class="d-flex align-items-center gap-3">
            <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#e94560,#c23152);display:flex;align-items:center;justify-content:center;">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 2a1 1 0 0 1 .993 .883l.007 .117v1a1 1 0 0 1 -1.993 .117l-.007 -.117v-1a1 1 0 0 1 1 -1z"/>
                <path d="M3.5 9.5a8.5 8.5 0 1 0 14.357 -4.57"/>
                <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
              </svg>
            </div>
            <div>
              <h5 class="modal-title mb-0" style="color:#e6edf3;font-weight:700;font-size:1.2rem;">
                Analyse IA — Tableau de bord écologique
              </h5>
              <small style="color:#8b949e;">Propulsé par Claude (Anthropic)</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body px-4 pb-4 pt-3">

          <!-- État : chargement -->
          <div id="ia-loading" style="display:none;text-align:center;padding:60px 0;">
            <div class="spinner-border" style="color:#e94560;width:3rem;height:3rem;" role="status"></div>
            <p style="color:#8b949e;margin-top:16px;font-size:.9rem;">Claude analyse vos données…</p>
          </div>

          <!-- État : erreur -->
          <div id="ia-error" style="display:none;background:rgba(233,69,96,.1);border:1px solid rgba(233,69,96,.4);
               border-radius:10px;padding:20px;color:#e94560;font-size:.9rem;"></div>

          <!-- État : résultats -->
          <div id="ia-result" style="display:none;">

            <!-- KPI cards -->
            <div class="row g-3 mb-4" id="ia-scores"></div>

            <!-- Répartition -->
            <div class="row g-3 mb-4">
              <div class="col-lg-6">
                <div style="background:#161b22;border:1px solid #30363d;border-radius:12px;padding:20px;">
                  <p style="color:#8b949e;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">
                    Répartition des interventions
                  </p>
                  <div id="ia-types-chart"></div>
                </div>
              </div>
              <div class="col-lg-6">
                <div style="background:#161b22;border:1px solid #30363d;border-radius:12px;padding:20px;">
                  <p style="color:#8b949e;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">
                    Kilométrage moyen par type
                  </p>
                  <div id="ia-km-chart"></div>
                </div>
              </div>
            </div>

            <!-- Recommandations IA (texte brut depuis Claude) -->
            <div style="background:#161b22;border:1px solid #30363d;border-radius:12px;padding:20px;">
              <p style="color:#8b949e;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;">
                Recommandations intelligentes — Claude
              </p>
              <div id="ia-recommandations"
                   style="color:#c9d1d9;font-size:.88rem;line-height:1.8;white-space:pre-wrap;"></div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  (function () {

    /* ── Scraping DOM ─────────────────────────────────────────── */
    function scrapeTableau() {
      const rows = document.querySelectorAll('#entretien-tbody tr[data-row]');
      const data = [];
      rows.forEach(tr => {
        const typeEl = tr.querySelector('.col-type');
        const kmEl   = tr.querySelector('.col-km');
        if (!typeEl || !kmEl) return;
        const type = (typeEl.dataset.type || '').trim();
        const km   = parseInt(kmEl.dataset.km, 10) || 0;
        if (type) data.push({ type, km });
      });
      return data;
    }

    /* ── Stats locales (pour les graphiques) ─────────────────── */
    function computeStats(data) {
      const cats = {};
      data.forEach(({ type, km }) => {
        const key = type.toLowerCase();
        if (!cats[key]) cats[key] = { count: 0, kmTotal: 0 };
        cats[key].count++;
        cats[key].kmTotal += km;
      });
      return cats;
    }

    /* ── Graphiques à barres ─────────────────────────────────── */
    const BAR_COLORS = ['#e94560','#1f6feb','#238636','#d29922','#8957e5','#0dcaf0','#fd7e14','#20c997'];
    function barChart(containerId, items) {
      const max = Math.max(...items.map(i => i.val), 1);
      document.getElementById(containerId).innerHTML = items.map((item, i) => `
        <div class="d-flex align-items-center gap-2 mb-2">
          <div style="width:110px;font-size:.72rem;color:#c9d1d9;white-space:nowrap;overflow:hidden;
                      text-overflow:ellipsis;" title="${item.label}">${item.label}</div>
          <div style="flex:1;background:#21262d;border-radius:6px;height:16px;overflow:hidden;">
            <div style="width:${Math.round(item.val/max*100)}%;background:${BAR_COLORS[i % BAR_COLORS.length]};
                        height:100%;border-radius:6px;transition:width .6s ease;"></div>
          </div>
          <div style="width:56px;font-size:.72rem;color:#8b949e;text-align:right;">${item.disp}</div>
        </div>
      `).join('');
    }

    function renderCharts(cats) {
      const entries = Object.entries(cats).sort((a, b) => b[1].count - a[1].count);
      barChart('ia-types-chart', entries.map(([k, v]) => ({
        label: k, val: v.count, disp: v.count + ' ×'
      })));
      barChart('ia-km-chart', entries.map(([k, v]) => ({
        label: k,
        val: v.count ? Math.round(v.kmTotal / v.count) : 0,
        disp: v.count ? Math.round(v.kmTotal / v.count).toLocaleString('fr-FR') + ' km' : '—'
      })));
    }

    /* ── KPI cards ───────────────────────────────────────────── */
    function renderScores(data, cats) {
      const total    = data.length;
      const kmMoyen  = total ? Math.round(data.reduce((s, r) => s + r.km, 0) / total) : 0;
      const nbCats   = Object.keys(cats).length;
      const dominant = Object.entries(cats).sort((a, b) => b[1].count - a[1].count)[0];

      const cards = [
        { icon:'🔧', label:'Interventions analysées', value: total,
          sub: nbCats + ' type(s) détecté(s)', color:'#1f6feb', bg:'rgba(31,111,235,.15)', border:'rgba(31,111,235,.4)' },
        { icon:'🏎️', label:'Kilométrage moyen', value: kmMoyen.toLocaleString('fr-FR') + ' km',
          sub:'Moyenne sur les entretiens visibles', color:'#d29922', bg:'rgba(210,153,34,.15)', border:'rgba(210,153,34,.4)' },
        { icon:'📊', label:'Intervention dominante', value: dominant ? dominant[0] : '—',
          sub: dominant ? dominant[1].count + ' occurrence(s)' : '', color:'#e94560', bg:'rgba(233,69,96,.15)', border:'rgba(233,69,96,.4)' },
        { icon:'🤖', label:'Analyse', value:'Claude AI',
          sub:'Recommandations personnalisées', color:'#8957e5', bg:'rgba(137,87,229,.15)', border:'rgba(137,87,229,.4)' },
      ];

      document.getElementById('ia-scores').innerHTML = cards.map(cd => `
        <div class="col-6 col-lg-3">
          <div style="background:${cd.bg};border:1px solid ${cd.border};border-radius:12px;padding:16px;">
            <div style="font-size:1.4rem;margin-bottom:6px;">${cd.icon}</div>
            <div style="font-size:1.15rem;font-weight:700;color:${cd.color};
                        overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${cd.value}</div>
            <div style="font-size:.7rem;color:#8b949e;margin-top:4px;">${cd.label}</div>
            <div style="font-size:.65rem;color:#6e7681;margin-top:2px;">${cd.sub}</div>
          </div>
        </div>
      `).join('');
    }

    /* ── Appel API Claude ────────────────────────────────────── */
    async function callGroq(data) {
      const resume = data.map(r => `- ${r.type} à ${r.km.toLocaleString('fr-FR')} km`).join('\n');

      const prompt = `Tu es un expert en maintenance automobile et en écologie.
Voici les données d'entretien d'un parc de véhicules (${data.length} entrées) :

${resume}

Analyse ces données et fournis :
1. **Impact écologique estimé** : calcule une estimation du CO₂ économisé grâce à ces entretiens (base : vidange/filtre optimise la consommation de 5%, pneus 6%, climatisation 8%) exprimé en kg de CO₂.
2. **Tendances détectées** : identifie les types d'intervention les plus fréquents et ce que ça révèle sur l'état du parc.
3. **3 recommandations concrètes** : conseils précis et personnalisés basés sur ces données pour réduire la consommation et améliorer la durée de vie des véhicules.
4. **Alerte si nécessaire** : signale toute anomalie (ex: freinage trop fréquent, kilométrage très élevé entre entretiens).

Réponds de manière structurée, professionnelle et concise. Utilise des emojis pertinents pour la lisibilité.`;

      const response = await fetch('../../controller/groq_proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          messages: [{ role: 'user', content: prompt }]
        })
      });

      if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        const msg = typeof err.error === 'string' ? err.error
                  : typeof err.error === 'object' ? JSON.stringify(err.error)
                  : `Erreur HTTP ${response.status}`;
        throw new Error(msg);
      }

      const json = await response.json();
      if (!json.success) throw new Error(json.error || 'Erreur proxy Groq.');
      return json.text;
    }

    /* ── Init ────────────────────────────────────────────────── */
    document.getElementById('btn-analyse-ia').addEventListener('click', async function () {
      const data = scrapeTableau();
      if (!data.length) {
        alert("Aucune donnée dans le tableau pour effectuer l'analyse.");
        return;
      }

      const modal   = new bootstrap.Modal(document.getElementById('modalAnalyseIA'));
      const loading = document.getElementById('ia-loading');
      const result  = document.getElementById('ia-result');
      const errDiv  = document.getElementById('ia-error');

      loading.style.display = 'block';
      result.style.display  = 'none';
      errDiv.style.display  = 'none';

      modal.show();

      /* Graphiques locaux (instantanés) */
      const cats = computeStats(data);
      renderScores(data, cats);
      renderCharts(cats);

      /* Appel Claude */
      try {
        const texte = await callGroq(data);
        document.getElementById('ia-recommandations').textContent = texte;
        loading.style.display = 'none';
        result.style.display  = 'block';
      } catch (err) {
        loading.style.display = 'none';
        errDiv.style.display  = 'block';
        errDiv.innerHTML = `<strong>Erreur IA Groq :</strong> ${err.message}`;
      }
    });

  })();
  </script>

  <?php include '../../partials/back/scripts.html'; ?>
  <script src="../../assets/back/js/vendors/sidebarnav.js"></script>
  <script>
  (function () {
    let currentFactureId = null, currentEntretienId = null;

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-voir-facture');
      if (!btn) return;
      currentFactureId   = btn.dataset.idFacture;
      currentEntretienId = btn.dataset.entretien;
      document.getElementById('view-ref').textContent  = btn.dataset.ref  || '—';
      document.getElementById('view-date').textContent = btn.dataset.date || '—';
      document.getElementById('view-ht').textContent   = btn.dataset.ht  ? parseFloat(btn.dataset.ht).toFixed(3)  + ' TND' : '—';
      document.getElementById('view-tva').textContent  = btn.dataset.tva ? btn.dataset.tva + '%' : '—';
      document.getElementById('view-ttc').textContent  = btn.dataset.ttc ? parseFloat(btn.dataset.ttc).toFixed(3) + ' TND' : '—';
      document.getElementById('view-mode').textContent = btn.dataset.mode || '—';
      document.getElementById('view-etat').textContent = btn.dataset.etat || '—';
      document.getElementById('edit-id-facture').value = currentFactureId;
      document.getElementById('edit-entretien').value  = currentEntretienId;
      document.getElementById('edit-ref').value  = btn.dataset.ref  || '';
      document.getElementById('edit-date').value = btn.dataset.date || '';
      document.getElementById('edit-ht').value   = btn.dataset.ht   || '';
      document.getElementById('edit-tva').value  = btn.dataset.tva  || '19';
      document.getElementById('edit-ttc').value  = btn.dataset.ttc  || '';
      document.getElementById('edit-mode').value = btn.dataset.mode || 'Espèces';
      document.getElementById('edit-etat').value = btn.dataset.etat || 'En attente';
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

    document.getElementById('edit-ht').addEventListener('input',   computeTTC);
    document.getElementById('edit-tva').addEventListener('change', computeTTC);
    function computeTTC() {
      const ht = parseFloat(document.getElementById('edit-ht').value) || 0;
      const tv = parseFloat(document.getElementById('edit-tva').value) || 0;
      document.getElementById('edit-ttc').value = (ht * (1 + tv / 100)).toFixed(3);
    }

    document.getElementById('form-modifier-facture').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('../../controller/facture_action.php?action=update', { method:'POST', body: new FormData(this) })
        .then(r => r.json()).then(res => {
          if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalVoirFacture')).hide(); window.location.href = 'listeentretiens.php?fact_ok=1'; }
          else alert('Erreur modification facture.');
        });
    });

    document.getElementById('btn-supprimer-facture').addEventListener('click', function () {
      if (!confirm('Supprimer cette facture ?')) return;
      const fd = new FormData(); fd.append('id', currentFactureId);
      fetch('../../controller/facture_action.php?action=delete', { method:'POST', body: fd })
        .then(r => r.json()).then(res => {
          if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalVoirFacture')).hide(); window.location.href = 'listeentretiens.php?deleted=1'; }
          else alert('Erreur suppression.');
        });
    });
  })();
  </script>
</body>
</html>