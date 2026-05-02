<?php
// ══════════════════════════════════════════════════════════════════════
//  Gestion des Rendez-vous — Back-office
//  Mise à jour : recherche · filtre · tri · pagination · JOIN client/véhicule
//  Design original conservé à 100%
// ══════════════════════════════════════════════════════════════════════
require_once "../../config.php";
$pdo = config::getConnexion();
require_once "../../controller/RendezVous.php";

$rendezVousC = new RendezVousC();

// ── SUPPRESSION ──────────────────────────────────────────────────────
if (isset($_GET['delete_id'])) {
    $rendezVousC->delete((int)$_GET['delete_id']);
    // Redirige en conservant tous les paramètres de filtre/tri/page
    $qs = $_GET;
    unset($qs['delete_id']);
    header('Location: Gestion_rendezVousBackend.php' . ($qs ? '?' . http_build_query($qs) : ''));
    exit();
}

// ── MODIFICATION DU STATUT (modal POST) ─────────────────────────────
if (isset($_POST['update_status'], $_POST['id_rdv'], $_POST['nouveau_statut'])) {
    $id           = (int)$_POST['id_rdv'];
    $nouveauStatut = $_POST['nouveau_statut'];
    $rdv = $rendezVousC->getRdvById($id);
    if ($rdv) {
        $rendezVousC->modifier(
            $id,
            $rdv['dateRDV'],
            $rdv['heureRDV'],
            $rdv['type_serviceRDV'],
            $nouveauStatut,
            $rdv['idVehicule'],
            $rdv['idclientRDV'],
            $rdv['descriptionRDV']
        );
    }
    // Redirige en conservant les paramètres GET actifs
    $qs = $_GET;
    header('Location: Gestion_rendezVousBackend.php' . ($qs ? '?' . http_build_query($qs) : ''));
    exit();
}

// ══════════════════════════════════════════════════════════════════════
//  PARAMÈTRES — recherche / filtre / tri / pagination
// ══════════════════════════════════════════════════════════════════════

$search    = trim($_GET['q']        ?? '');
$sf_from   = (isset($_GET['sf_from'])   && $_GET['sf_from']   !== '') ? $_GET['sf_from']   : null;
$sf_to     = (isset($_GET['sf_to'])     && $_GET['sf_to']     !== '') ? $_GET['sf_to']     : null;
$sf_type   = (isset($_GET['sf_type'])   && $_GET['sf_type']   !== '') ? $_GET['sf_type']   : null;
$sf_statut = (isset($_GET['sf_statut']) && $_GET['sf_statut'] !== '') ? $_GET['sf_statut'] : null;
$sortOrder = (isset($_GET['sort']) && $_GET['sort'] === 'asc') ? 'asc' : 'desc';

// Reconstruit l'URL en préservant tous les paramètres, avec surcharges
function buildQuery(array $overrides = []): string {
    $keep = ['q', 'sort', 'page', 'sf_from', 'sf_to', 'sf_type', 'sf_statut'];
    $params = [];
    foreach ($keep as $k) {
        if (isset($_GET[$k]) && $_GET[$k] !== '') {
            $params[$k] = $_GET[$k];
        }
    }
    foreach ($overrides as $k => $v) {
        if ($v === null || $v === '') {
            unset($params[$k]);
        } else {
            $params[$k] = $v;
        }
    }
    return '?' . ($params ? http_build_query($params) : '');
}

// ══════════════════════════════════════════════════════════════════════
//  REQUÊTE PRINCIPALE — JOIN user + vehicule (noms réels)
// ══════════════════════════════════════════════════════════════════════
$where  = [];
$params = [];

if ($sf_from)  { $where[] = 'r.dateRDV >= :sf_from';         $params[':sf_from']   = $sf_from;  }
if ($sf_to)    { $where[] = 'r.dateRDV <= :sf_to';           $params[':sf_to']     = $sf_to;    }
if ($sf_type)  { $where[] = 'r.type_serviceRDV = :sf_type';  $params[':sf_type']   = $sf_type;  }
if ($sf_statut){ $where[] = 'r.statutRDV = :sf_statut';      $params[':sf_statut'] = $sf_statut;}
if ($search !== '') {
    $where[] = '(u.nomclient LIKE :q OR v.matriculevoiture LIKE :q2)';
    $params[':q']  = '%' . $search . '%';
    $params[':q2'] = '%' . $search . '%';
}

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';
$sortSQL  = 'r.dateRDV ' . ($sortOrder === 'asc' ? 'ASC' : 'DESC')
          . ', r.heureRDV ' . ($sortOrder === 'asc' ? 'ASC' : 'DESC');

$sql = "
    SELECT
        r.idRDV,
        r.dateRDV,
        r.heureRDV,
        r.type_serviceRDV,
        r.statutRDV,
        r.descriptionRDV,
        r.idclientRDV,
        r.idVehicule,
        COALESCE(u.nomclient,        CONCAT('Client #',  r.idclientRDV)) AS nomclient,
        COALESCE(v.matriculevoiture, CONCAT('VEH-',      r.idVehicule))  AS matriculevoiture
    FROM rendezvous r
    LEFT JOIN user     u ON u.id_client  = r.idclientRDV
    LEFT JOIN vehicule v ON v.idVehicule = r.idVehicule
    $whereSQL
    ORDER BY $sortSQL
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allRDV = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── PAGINATION ────────────────────────────────────────────────────────
$perPage    = 5;
$total      = count($allRDV);
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset     = ($page - 1) * $perPage;
$listRDV    = array_slice($allRDV, $offset, $perPage);

// ── Listes pour les selects de filtre ────────────────────────────────
$types   = $pdo->query("SELECT DISTINCT type_serviceRDV FROM rendezvous WHERE type_serviceRDV IS NOT NULL ORDER BY type_serviceRDV")->fetchAll(PDO::FETCH_COLUMN);
$statuts = $pdo->query("SELECT DISTINCT statutRDV       FROM rendezvous WHERE statutRDV       IS NOT NULL ORDER BY statutRDV")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="utf-8" />
  <title>Gestion des Rendez-vous - Admin</title>
  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  <?php include("../../partials/head/head-links.html"); ?>
  <style>
    /* ── Design original ─────────────────────────────────────────────── */
    body { background-color: #0b111e; }
    .card { background-color: #111c2d; border: 1px solid #1e293b; border-radius: 12px; }
    .table thead th { background-color: #1e293b; color: #94a3b8; text-transform: uppercase; font-size: 0.7rem; padding: 15px; }
    .table td { vertical-align: middle; border-color: #1e293b; color: #e2e8f0; font-size: 0.85rem; padding: 15px; }
    .badge-id { background: rgba(56,189,248,0.1); color: #38bdf8; padding: 4px 8px; border-radius: 4px; }
    .btn-delete { color: #ef4444; background: rgba(239,68,68,0.1); padding: 8px 12px; border-radius: 8px; transition: 0.2s; }
    .btn-delete:hover { background: #ef4444; color: white; }

    .badge-statut { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; }
    .statut-confirme { background: rgba(16,185,129,0.2); color: #10b981; }
    .statut-annule   { background: rgba(239,68,68,0.2);  color: #ef4444; }
    .statut-attente  { background: rgba(245,158,11,0.2); color: #f59e0b; }
    .statut-termine  { background: rgba(59,130,246,0.2); color: #3b82f6; }

    /* Modal */
    .modal-custom .modal-content  { background: #111c2d; border: 1px solid #1e293b; border-radius: 16px; }
    .modal-custom .modal-header   { border-bottom-color: #1e293b; background: #0f172a; border-radius: 16px 16px 0 0; }
    .modal-custom .modal-footer   { border-top-color: #1e293b; background: #0f172a; border-radius: 0 0 16px 16px; }
    .modal-custom .modal-title    { color: white; }
    .modal-custom .btn-close      { filter: invert(1); }
    .form-label-modal    { color: #94a3b8; font-weight: 500; margin-bottom: 8px; }
    .form-select-modal   { background-color: #1e293b; border: 1px solid #334155; color: #f1f5f9; border-radius: 10px; padding: 10px 15px; }
    .form-select-modal:focus { background-color: #1e293b; border-color: #38bdf8; box-shadow: 0 0 0 0.2rem rgba(56,189,248,0.25); }
    .btn-modal-update        { background: linear-gradient(135deg,#3b82f6,#2563eb); border: none; border-radius: 10px; padding: 8px 25px; font-weight: 500; }
    .btn-modal-update:hover  { background: linear-gradient(135deg,#2563eb,#1d4ed8); transform: translateY(-1px); }
    .btn-modifier {
      background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3);
      padding: 5px 12px; border-radius: 8px; font-size: 0.75rem; transition: all 0.2s;
      text-decoration: none; display: inline-block;
    }
    .btn-modifier:hover { background: #3b82f6; color: white; cursor: pointer; }
    .action-buttons { display: flex; gap: 8px; justify-content: flex-end; align-items: center; }
    .info-text { background: #0f172a; padding: 10px; border-radius: 10px; margin-top: 15px; font-size: 0.85rem; }

    /* ── Nouvelle barre de filtres ───────────────────────────────────── */
    .filter-bar {
      background: #111c2d;
      border: 1px solid #1e293b;
      border-radius: 12px;
      padding: 14px 18px;
      margin-bottom: 18px;
    }
    .filter-bar form {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: flex-end;
    }
    .fb-group {
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1 1 120px;
    }
    .fb-group label {
      font-size: 0.67rem;
      text-transform: uppercase;
      letter-spacing: .07em;
      color: #64748b;
    }
    .fb-group input,
    .fb-group select {
      padding: 6px 10px;
      background: #0b111e;
      border: 1px solid #1e293b;
      border-radius: 7px;
      color: #e2e8f0;
      font-size: 0.8rem;
      width: 100%;
      transition: border-color .2s;
    }
    .fb-group input:focus,
    .fb-group select:focus { outline: none; border-color: #38bdf8; }
    .fb-group select option { background: #111c2d; }
    .btn-fb {
      padding: 6px 16px; border-radius: 7px; border: none;
      background: #0ea5e9; color: #fff; font-size: 0.8rem;
      cursor: pointer; white-space: nowrap; transition: background .2s;
    }
    .btn-fb:hover { background: #38bdf8; }
    .btn-fb-reset {
      padding: 6px 12px; border-radius: 7px; border: 1px solid #1e293b;
      background: transparent; color: #64748b; font-size: 0.78rem;
      text-decoration: none; white-space: nowrap; transition: all .2s;
    }
    .btn-fb-reset:hover { color: #ef4444; border-color: #ef4444; }

    /* Tags filtres actifs */
    .ftags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 10px; }
    .ftag {
      padding: 2px 9px; border-radius: 20px;
      border: 1px solid rgba(56,189,248,.2); background: rgba(56,189,248,.06);
      color: #38bdf8; font-size: 0.68rem;
    }

    /* ── Toolbar tableau (recherche + tri) ───────────────────────────── */
    .tbl-toolbar {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      padding: 14px 16px;
      border-bottom: 1px solid #1e293b;
    }
    .search-wrap { position: relative; flex: 1 1 200px; max-width: 300px; }
    .search-wrap .ico { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: #475569; font-size: 0.75rem; pointer-events: none; }
    .search-wrap input[name="q"] {
      width: 100%; padding: 6px 10px 6px 28px;
      background: #0b111e; border: 1px solid #1e293b;
      border-radius: 8px; color: #e2e8f0; font-size: 0.82rem; transition: border-color .2s;
    }
    .search-wrap input[name="q"]:focus { outline: none; border-color: #38bdf8; }
    .search-wrap input[name="q"]::placeholder { color: #475569; }
    .btn-srch {
      padding: 6px 13px; border-radius: 8px; border: 1px solid #1e293b;
      background: #0b111e; color: #94a3b8; font-size: 0.76rem;
      cursor: pointer; transition: all .2s; white-space: nowrap;
    }
    .btn-srch:hover { border-color: #38bdf8; color: #38bdf8; }

    .sort-group { display: flex; gap: 5px; }
    .btn-sort {
      display: inline-flex; align-items: center; gap: 4px;
      padding: 6px 12px; border-radius: 8px; border: 1px solid #1e293b;
      background: #0b111e; color: #94a3b8; font-size: 0.76rem;
      font-weight: 500; text-decoration: none; transition: all .2s;
    }
    .btn-sort:hover  { border-color: #38bdf8; color: #38bdf8; }
    .btn-sort.active { background: rgba(56,189,248,.1); border-color: #38bdf8; color: #38bdf8; }
    .tbl-count { margin-left: auto; font-size: 0.73rem; color: #475569; white-space: nowrap; }
    .tbl-count span { color: #38bdf8; font-weight: 600; }

    /* ── Pagination ──────────────────────────────────────────────────── */
    .tbl-pagination {
      display: flex; align-items: center; justify-content: space-between;
      flex-wrap: wrap; gap: 8px; padding: 13px 16px; border-top: 1px solid #1e293b;
    }
    .pg-info { font-size: 0.75rem; color: #475569; }
    .pg-info strong { color: #94a3b8; }
    .pg-controls { display: flex; gap: 4px; }
    .pg-btn {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 32px; height: 32px; padding: 0 9px; border-radius: 7px;
      border: 1px solid #1e293b; background: #0b111e; color: #94a3b8;
      font-size: 0.78rem; font-weight: 500; text-decoration: none; transition: all .2s;
    }
    .pg-btn:hover:not(.disabled) { border-color: #38bdf8; color: #38bdf8; }
    .pg-btn.active   { background: #38bdf8; border-color: #38bdf8; color: #0b111e; font-weight: 700; }
    .pg-btn.disabled { opacity: .3; pointer-events: none; }

    /* Matricule badge */
    .badge-plate {
      display: inline-block; background: rgba(251,191,36,.08); color: #fbbf24;
      padding: 3px 8px; border-radius: 5px; font-size: 0.75rem; font-weight: 600; letter-spacing: .04em;
    }
  </style>
</head>

<body>
  <div class="wrapper">
    <?php include("../../partials/sidebar-collapse.html"); ?>

    <div id="content" class="main-content">
      <?php include("../../partials/topbar-second.html"); ?>

      <div class="container-fluid mt-4 px-4">

        <!-- ── En-tête ──────────────────────────────────────────────── -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="text-white mb-1">Planning des Rendez-vous</h4>
            <p class="text-muted small">
              <?= $total ?> rendez-vous trouvé<?= $total > 1 ? 's' : '' ?>
              <?php if ($search !== '' || $sf_from || $sf_to || $sf_type || $sf_statut): ?>
                · <a href="Gestion_rendezVousBackend.php" class="text-muted small" style="text-decoration:none">✕ effacer les filtres</a>
              <?php endif; ?>
            </p>
          </div>
          <div class="d-flex gap-2">
            <a href="Gestion_rendezVousBackend.php" class="btn btn-outline-secondary btn-sm px-3">Actualiser</a>
            <a href="calendrier_rendezVous.php" class="btn btn-sm px-3"
               style="background:rgba(56,189,248,.12);color:#38bdf8;border:1px solid rgba(56,189,248,.3);border-radius:8px;font-weight:500;display:inline-flex;align-items:center;gap:6px;">
              <i class="fas fa-calendar-alt"></i> Voir Calendrier
            </a>
          </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             BARRE DE FILTRES (date · type · statut)
        ════════════════════════════════════════════════════════════ -->
        <div class="filter-bar">
          <form method="GET" action="">
            <!-- Conserver tri et page -->
            <?php if ($sortOrder !== 'desc'): ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sortOrder) ?>">
            <?php endif; ?>
            <?php if ($search !== ''): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>
            <input type="hidden" name="page" value="1">

            <div class="fb-group">
              <label>Du</label>
              <input type="date" name="sf_from" value="<?= htmlspecialchars($sf_from ?? '') ?>">
            </div>
            <div class="fb-group">
              <label>Au</label>
              <input type="date" name="sf_to" value="<?= htmlspecialchars($sf_to ?? '') ?>">
            </div>
            <div class="fb-group">
              <label>Type de service</label>
              <select name="sf_type">
                <option value="">— Tous —</option>
                <?php foreach ($types as $t): ?>
                <option value="<?= htmlspecialchars($t) ?>" <?= $sf_type === $t ? 'selected' : '' ?>>
                  <?= htmlspecialchars($t) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="fb-group">
              <label>Statut</label>
              <select name="sf_statut">
                <option value="">— Tous —</option>
                <?php foreach ($statuts as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $sf_statut === $s ? 'selected' : '' ?>>
                  <?= htmlspecialchars($s) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn-fb">🔽 Filtrer</button>
            <a href="Gestion_rendezVousBackend.php" class="btn-fb-reset">✕ Reset</a>
          </form>

          <!-- Tags filtres actifs -->
          <?php if ($sf_from || $sf_to || $sf_type || $sf_statut || $search !== ''): ?>
          <div class="ftags">
            <?php if ($search !== ''): ?><span class="ftag">🔍 "<?= htmlspecialchars($search) ?>"</span><?php endif; ?>
            <?php if ($sf_from):  ?><span class="ftag">📅 Depuis : <?= htmlspecialchars($sf_from) ?></span><?php endif; ?>
            <?php if ($sf_to):    ?><span class="ftag">📅 Jusqu'à : <?= htmlspecialchars($sf_to) ?></span><?php endif; ?>
            <?php if ($sf_type):  ?><span class="ftag">🔧 <?= htmlspecialchars($sf_type) ?></span><?php endif; ?>
            <?php if ($sf_statut):?><span class="ftag">📌 <?= htmlspecialchars($sf_statut) ?></span><?php endif; ?>
          </div>
          <?php endif; ?>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             TABLEAU PRINCIPAL
        ════════════════════════════════════════════════════════════ -->
        <div class="card">

          <!-- ── Toolbar : recherche texte + tri par date ── -->
          <form method="GET" action="" style="display:contents">
            <?php if ($sf_from):   ?><input type="hidden" name="sf_from"   value="<?= htmlspecialchars($sf_from) ?>"><?php endif; ?>
            <?php if ($sf_to):     ?><input type="hidden" name="sf_to"     value="<?= htmlspecialchars($sf_to) ?>"><?php endif; ?>
            <?php if ($sf_type):   ?><input type="hidden" name="sf_type"   value="<?= htmlspecialchars($sf_type) ?>"><?php endif; ?>
            <?php if ($sf_statut): ?><input type="hidden" name="sf_statut" value="<?= htmlspecialchars($sf_statut) ?>"><?php endif; ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sortOrder) ?>">
            <input type="hidden" name="page" value="1">

            <div class="tbl-toolbar">
              <!-- Recherche par nom client ou matricule -->
              <div class="search-wrap">
                <span class="ico"><i class="fas fa-search"></i></span>
                <input type="text" name="q"
                       placeholder="Nom client ou matricule…"
                       value="<?= htmlspecialchars($search) ?>"
                       autocomplete="off">
              </div>
              <button type="submit" class="btn-srch">Chercher</button>

              <!-- Tri par date -->
              <div class="sort-group">
                <a href="<?= buildQuery(['sort' => 'asc', 'page' => 1]) ?>"
                   class="btn-sort <?= $sortOrder === 'asc' ? 'active' : '' ?>">
                  ↑ Plus ancien
                </a>
                <a href="<?= buildQuery(['sort' => 'desc', 'page' => 1]) ?>"
                   class="btn-sort <?= $sortOrder === 'desc' ? 'active' : '' ?>">
                  ↓ Plus récent
                </a>
              </div>

              <!-- Compteur résultats -->
              <span class="tbl-count">
                <span><?= count($listRDV) ?></span> / <?= $total ?>
              </span>
            </div>
          </form>

          <!-- ── Table ── -->
          <div class="table-responsive">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th class="ps-4">ID RDV</th>
                  <th>DATE</th>
                  <th>HEURE</th>
                  <th>CLIENT</th>
                  <th>MATRICULE</th>
                  <th>SERVICE</th>
                  <th>STATUT</th>
                  <th>DESCRIPTION</th>
                  <th class="text-end pe-4">ACTION</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!empty($listRDV)): ?>
                  <?php foreach ($listRDV as $rdv):
                    $statut     = $rdv['statutRDV'] ?? 'en attente';
                    $badgeClass = 'statut-attente';
                    if ($statut === 'confirmé') $badgeClass = 'statut-confirme';
                    elseif ($statut === 'annulé') $badgeClass = 'statut-annule';
                    elseif ($statut === 'terminé') $badgeClass = 'statut-termine';
                    $desc = $rdv['descriptionRDV'] ?? 'Aucune description';
                    $descShort = mb_strlen($desc) > 30 ? mb_substr($desc, 0, 30) . '…' : $desc;
                  ?>
                  <tr>
                    <td class="ps-4">
                      <span class="text-info">#<?= htmlspecialchars($rdv['idRDV']) ?></span>
                    </td>
                    <td><?= htmlspecialchars($rdv['dateRDV']) ?></td>
                    <td style="color:#38bdf8;font-weight:600"><?= htmlspecialchars(substr($rdv['heureRDV'], 0, 5)) ?></td>
                    <td style="font-weight:600;color:#fff"><?= htmlspecialchars($rdv['nomclient']) ?></td>
                    <td><span class="badge-plate"><?= htmlspecialchars($rdv['matriculevoiture']) ?></span></td>
                    <td><?= htmlspecialchars($rdv['type_serviceRDV']) ?></td>
                    <td>
                      <span class="badge-statut <?= $badgeClass ?>">
                        <i class="fas <?= $statut === 'confirmé' ? 'fa-check-circle' : ($statut === 'annulé' ? 'fa-times-circle' : 'fa-clock') ?> me-1"></i>
                        <?= ucfirst($statut) ?>
                      </span>
                    </td>
                    <td style="color:#64748b;font-size:.78rem" title="<?= htmlspecialchars($desc) ?>">
                      <?= htmlspecialchars($descShort) ?>
                    </td>
                    <td class="text-end pe-4">
                      <div class="action-buttons">
                        <!-- Bouton Modifier → ouvre la modale Bootstrap -->
                        <button type="button" class="btn-modifier"
                                data-bs-toggle="modal"
                                data-bs-target="#modalStatut<?= $rdv['idRDV'] ?>">
                          <i class="fas fa-edit me-1"></i> Modifier
                        </button>
                        <!-- Bouton Supprimer -->
                        <a href="<?= buildQuery(['delete_id' => $rdv['idRDV']]) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Supprimer le RDV #<?= $rdv['idRDV'] ?> de <?= htmlspecialchars($rdv['nomclient']) ?> ?')">
                          <i class="fas fa-trash-alt"></i> Supprimer
                        </a>
                      </div>
                    </td>
                  </tr>

                  <!-- ── Modale modification statut ── -->
                  <div class="modal fade modal-custom" id="modalStatut<?= $rdv['idRDV'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">
                            <i class="fas fa-calendar-alt me-2 text-info"></i>
                            Modifier le statut — RDV #<?= $rdv['idRDV'] ?>
                          </h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <form method="POST">
                          <!-- Transmettre tous les paramètres GET pour rediriger au bon endroit -->
                          <?php foreach ($_GET as $gk => $gv): ?>
                          <input type="hidden" name="<?= htmlspecialchars($gk) ?>" value="<?= htmlspecialchars($gv) ?>">
                          <?php endforeach; ?>
                          <div class="modal-body">
                            <input type="hidden" name="id_rdv" value="<?= $rdv['idRDV'] ?>">

                            <div class="info-text">
                              <div class="row mb-2">
                                <div class="col-5 text-muted">Date :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['dateRDV']) ?> à <?= htmlspecialchars(substr($rdv['heureRDV'], 0, 5)) ?></div>
                              </div>
                              <div class="row mb-2">
                                <div class="col-5 text-muted">Client :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['nomclient']) ?></div>
                              </div>
                              <div class="row mb-2">
                                <div class="col-5 text-muted">Véhicule :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['matriculevoiture']) ?></div>
                              </div>
                              <div class="row">
                                <div class="col-5 text-muted">Service :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['type_serviceRDV']) ?></div>
                              </div>
                            </div>

                            <div class="mt-3">
                              <label class="form-label-modal d-block mb-2">
                                <i class="fas fa-tag me-1"></i> Nouveau statut :
                              </label>
                              <select name="nouveau_statut" class="form-select-modal w-100" required>
                                <option value="en attente" <?= $statut === 'en attente' ? 'selected' : '' ?>>En attente</option>
                                <option value="confirmé"   <?= $statut === 'confirmé'   ? 'selected' : '' ?>>Confirmé</option>
                                <option value="annulé"     <?= $statut === 'annulé'     ? 'selected' : '' ?>>Annulé</option>
                                <option value="terminé"    <?= $statut === 'terminé'    ? 'selected' : '' ?>>Terminé</option>
                              </select>
                            </div>

                            <div class="mt-3 pt-2 text-center">
                              <small class="text-muted">
                                Statut actuel :
                                <span class="badge-statut <?= $badgeClass ?>"><?= ucfirst($statut) ?></span>
                              </small>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                              <i class="fas fa-times me-1"></i> Annuler
                            </button>
                            <button type="submit" name="update_status" class="btn-modal-update btn">
                              <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <!-- /modale -->

                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                      <?php if ($search !== '' || $sf_from || $sf_to || $sf_type || $sf_statut): ?>
                        Aucun résultat pour ces filtres.
                        <a href="Gestion_rendezVousBackend.php" class="d-block mt-2 text-info" style="font-size:.8rem">Effacer les filtres</a>
                      <?php else: ?>
                        Aucun rendez-vous trouvé.
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- ── Pagination ── -->
          <?php if ($totalPages > 1): ?>
          <div class="tbl-pagination">
            <span class="pg-info">
              Page <strong><?= $page ?></strong> / <strong><?= $totalPages ?></strong>
              &nbsp;·&nbsp; <?= $offset + 1 ?>–<?= min($offset + $perPage, $total) ?> sur <?= $total ?>
            </span>
            <div class="pg-controls">
              <a href="<?= buildQuery(['page' => $page - 1]) ?>"
                 class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>">‹ Préc.</a>
              <?php
                $start = max(1, $page - 2);
                $end   = min($totalPages, $page + 2);
                if ($start > 1) echo '<a href="' . buildQuery(['page' => 1]) . '" class="pg-btn">1</a>';
                if ($start > 2) echo '<span class="pg-btn disabled">…</span>';
                for ($p = $start; $p <= $end; $p++):
              ?><a href="<?= buildQuery(['page' => $p]) ?>"
                   class="pg-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a><?php
                endfor;
                if ($end < $totalPages - 1) echo '<span class="pg-btn disabled">…</span>';
                if ($end < $totalPages)     echo '<a href="' . buildQuery(['page' => $totalPages]) . '" class="pg-btn">' . $totalPages . '</a>';
              ?>
              <a href="<?= buildQuery(['page' => $page + 1]) ?>"
                 class="pg-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">Suiv. ›</a>
            </div>
          </div>
          <?php endif; ?>

        </div><!-- /.card -->
      </div><!-- /.container-fluid -->
    </div><!-- /#content -->
  </div><!-- /.wrapper -->

  <?php include("../../partials/scripts.html"); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 