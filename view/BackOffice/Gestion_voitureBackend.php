<?php
require_once(__DIR__ . '/../../config.php');
$pdo = config::getConnexion();
require_once(__DIR__ . '/../../controller/Vehicule.php');

$vehiculeC = new VoitureC();

// ── SUPPRESSION ──────────────────────────────────────────────────────
if (isset($_GET['delete_id'])) {
    $vehiculeC->supprimerVehicule($_GET['delete_id']);
    header('Location: Gestion_voitureBackend.php');
    exit();
}

// ── RÉCUPÉRATION VÉHICULES ───────────────────────────────────────────
$raw = $vehiculeC->afficherVehicule();
if ($raw instanceof PDOStatement) {
    $listVehicules = $raw->fetchAll(PDO::FETCH_ASSOC);
} elseif (is_array($raw)) {
    $listVehicules = $raw;
} else {
    $listVehicules = [];
}

// ── TRI PAR DATE ─────────────────────────────────────────────────────
$sortOrder = (isset($_GET['sort']) && $_GET['sort'] === 'asc') ? 'asc' : 'desc';
usort($listVehicules, function($a, $b) use ($sortOrder) {
    $dA = strtotime($a['date_ajoutV'] ?? '1970-01-01');
    $dB = strtotime($b['date_ajoutV'] ?? '1970-01-01');
    return $sortOrder === 'asc' ? $dA - $dB : $dB - $dA;
});

// ── PAGINATION ────────────────────────────────────────────────────────
$perPage    = 5;
$total      = count($listVehicules);
$totalPages = max(1, ceil($total / $perPage));
$page       = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset     = ($page - 1) * $perPage;
$pageVehicules = array_slice($listVehicules, $offset, $perPage);

function buildQuery(array $overrides = []): string {
    $params = [];
    if (isset($_GET['sort']))     $params['sort']     = $_GET['sort'];
    if (isset($_GET['page']))     $params['page']     = $_GET['page'];
    if (isset($_GET['sf_from']))  $params['sf_from']  = $_GET['sf_from'];
    if (isset($_GET['sf_to']))    $params['sf_to']    = $_GET['sf_to'];
    if (isset($_GET['sf_type']))  $params['sf_type']  = $_GET['sf_type'];
    if (isset($_GET['sf_statut']))$params['sf_statut']= $_GET['sf_statut'];
    foreach ($overrides as $k => $v) $params[$k] = $v;
    return $params ? '?' . http_build_query($params) : '?';
}

// ══════════════════════════════════════════════════════════════════════
// STATISTIQUES — basées sur la table rendezvous JOIN voiture
// ══════════════════════════════════════════════════════════════════════
$sf_dateFrom = (isset($_GET['sf_from'])   && $_GET['sf_from']   !== '') ? $_GET['sf_from']   : null;
$sf_dateTo   = (isset($_GET['sf_to'])     && $_GET['sf_to']     !== '') ? $_GET['sf_to']     : null;
$sf_type     = (isset($_GET['sf_type'])   && $_GET['sf_type']   !== '') ? $_GET['sf_type']   : null;
$sf_statut   = (isset($_GET['sf_statut']) && $_GET['sf_statut'] !== '') ? $_GET['sf_statut'] : null;

$sWhere  = [];
$sParams = [];
if ($sf_dateFrom) { $sWhere[] = 'r.dateRDV >= :sf_from';        $sParams[':sf_from']   = $sf_dateFrom; }
if ($sf_dateTo)   { $sWhere[] = 'r.dateRDV <= :sf_to';          $sParams[':sf_to']     = $sf_dateTo;   }
if ($sf_type)     { $sWhere[] = 'r.type_serviceRDV = :sf_type'; $sParams[':sf_type']   = $sf_type;     }
if ($sf_statut)   { $sWhere[] = 'r.statutRDV = :sf_statut';     $sParams[':sf_statut'] = $sf_statut;   }
$sWhereSQL = $sWhere ? 'WHERE ' . implode(' AND ', $sWhere) : '';

// Top 5 véhicules par nombre de RDV
$sqlTop5 = "
    SELECT
        v.idVehicule,
        v.matriculevoiture,
        v.marqueV,
        v.imageVoiture,
        COUNT(r.idRDV) AS nbRDV
    FROM rendezvous r
    INNER JOIN vehicule v ON v.idVehicule = r.idVehicule
    $sWhereSQL
    GROUP BY r.idVehicule, v.idVehicule, v.matriculevoiture, v.marqueV, v.imageVoiture
    ORDER BY nbRDV DESC
    LIMIT 5
";
$stTop5 = $pdo->prepare($sqlTop5);
$stTop5->execute($sParams);
$top5    = $stTop5->fetchAll(PDO::FETCH_ASSOC);
$champion = $top5[0] ?? null;

// KPI : total RDV
$stTot = $pdo->prepare("SELECT COUNT(*) FROM rendezvous r $sWhereSQL");
$stTot->execute($sParams);
$totalRDV = (int)$stTot->fetchColumn();

// KPI : véhicules avec au moins 1 RDV
$stVeh = $pdo->prepare("SELECT COUNT(DISTINCT r.idVehicule) FROM rendezvous r $sWhereSQL");
$stVeh->execute($sParams);
$vehAvecRDV = (int)$stVeh->fetchColumn();

// KPI : moyenne RDV / véhicule
$avgRDV = ($vehAvecRDV > 0) ? round($totalRDV / $vehAvecRDV, 1) : 0;

// Listes pour les selects
$types   = $pdo->query("SELECT DISTINCT type_serviceRDV FROM rendezvous WHERE type_serviceRDV IS NOT NULL ORDER BY type_serviceRDV")->fetchAll(PDO::FETCH_COLUMN);
$statuts = $pdo->query("SELECT DISTINCT statutRDV FROM rendezvous WHERE statutRDV IS NOT NULL ORDER BY statutRDV")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Gestion des Véhicules — Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  <?php include("../../partials/head/head-meta.html"); ?>
  <?php include("../../partials/head/head-links.html"); ?>

  <style>
    :root {
      --bg:      #080e1a;
      --surf:    #0f1825;
      --surf2:   #111c2d;
      --border:  #1a2840;
      --border2: #1e293b;
      --text:    #e2e8f0;
      --muted:   #475569;
      --muted2:  #64748b;
      --accent:  #38bdf8;
      --accent2: #0ea5e9;
      --gold:    #f59e0b;
      --gold2:   #fbbf24;
      --silver:  #94a3b8;
      --bronze:  #c2784f;
      --green:   #34d399;
      --red:     #ef4444;
      --mono:    'DM Mono', monospace;
      --head:    'Syne', sans-serif;
    }

    body { background:var(--bg); color:var(--text); font-family:var(--mono); font-size:.84rem; }

    @keyframes fadeUp {
      from { opacity:0; transform:translateY(8px); }
      to   { opacity:1; transform:translateY(0); }
    }
    @keyframes rowIn {
      from { opacity:0; transform:translateY(5px); }
      to   { opacity:1; transform:translateY(0); }
    }

    /* ── Page header ─────────────────────────────────────────── */
    .page-header { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:28px; }
    .page-header h4 { font-family:var(--head); font-size:1.4rem; color:#fff; margin:0; }
    .page-header p  { color:var(--muted); font-size:.76rem; margin:3px 0 0; }
    .btn-refresh {
      padding:7px 16px; border-radius:8px; border:1px solid var(--border2);
      background:var(--surf2); color:var(--silver); font-size:.78rem;
      text-decoration:none; transition:all .2s;
    }
    .btn-refresh:hover { border-color:var(--accent); color:var(--accent); }

    /* ── Section title ───────────────────────────────────────── */
    .sec-title {
      font-family:var(--head); font-size:.78rem; font-weight:700;
      text-transform:uppercase; letter-spacing:.1em; color:var(--muted2);
      margin-bottom:14px; display:flex; align-items:center; gap:8px;
    }
    .sec-title::after { content:''; flex:1; height:1px; background:var(--border); }

    /* ── Stat filter ─────────────────────────────────────────── */
    .stat-filter {
      background:var(--surf2); border:1px solid var(--border);
      border-radius:12px; padding:16px 20px; margin-bottom:20px;
      animation:fadeUp .35s ease both;
    }
    .stat-filter form { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
    .sf-group { display:flex; flex-direction:column; gap:4px; flex:1 1 130px; }
    .sf-group label { font-size:.67rem; text-transform:uppercase; letter-spacing:.08em; color:var(--muted); }
    .sf-group input,
    .sf-group select {
      padding:7px 10px; background:var(--bg); border:1px solid var(--border2);
      border-radius:7px; color:var(--text); font-family:var(--mono); font-size:.8rem;
      transition:border-color .2s; width:100%;
    }
    .sf-group input:focus, .sf-group select:focus { outline:none; border-color:var(--accent); }
    .sf-group select option { background:var(--surf); }
    .btn-sf {
      padding:7px 18px; border-radius:7px; border:none;
      background:var(--accent2); color:#fff; font-family:var(--mono);
      font-size:.8rem; cursor:pointer; transition:background .2s; white-space:nowrap;
    }
    .btn-sf:hover { background:var(--accent); }
    .btn-sf-reset {
      padding:7px 14px; border-radius:7px; border:1px solid var(--border2);
      background:transparent; color:var(--muted); font-family:var(--mono);
      font-size:.78rem; cursor:pointer; text-decoration:none; white-space:nowrap; transition:all .2s;
    }
    .btn-sf-reset:hover { color:var(--red); border-color:var(--red); }
    .ftags { display:flex; flex-wrap:wrap; gap:5px; margin-top:10px; }
    .ftag {
      padding:2px 9px; border-radius:20px;
      border:1px solid rgba(56,189,248,.2); background:rgba(56,189,248,.06);
      color:var(--accent); font-size:.68rem;
    }

    /* ── KPI row ─────────────────────────────────────────────── */
    .kpi-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:20px; }
    .kpi-card {
      background:var(--surf2); border:1px solid var(--border); border-radius:11px; padding:16px 18px;
      position:relative; overflow:hidden; animation:fadeUp .4s ease both;
    }
    .kpi-card::before { content:''; position:absolute; top:0;left:0;right:0; height:2px; }
    .kpi-blue::before   { background:var(--accent); }
    .kpi-gold::before   { background:var(--gold); }
    .kpi-green::before  { background:var(--green); }
    .kpi-purple::before { background:#a78bfa; }
    .kpi-label { font-size:.65rem; text-transform:uppercase; letter-spacing:.09em; color:var(--muted); margin-bottom:7px; }
    .kpi-val   { font-family:var(--head); font-size:1.8rem; font-weight:800; color:#fff; line-height:1; }
    .kpi-gold  .kpi-val  { color:var(--gold2); }
    .kpi-green .kpi-val  { color:var(--green); }
    .kpi-purple .kpi-val { color:#a78bfa; }
    .kpi-sub { font-size:.68rem; color:var(--muted); margin-top:4px; }

    /* ── Champion card ───────────────────────────────────────── */
    .champion-card {
      background:var(--surf2); border:1px solid var(--border2); border-radius:13px;
      padding:20px 24px; display:flex; align-items:center; gap:20px; flex-wrap:wrap;
      position:relative; overflow:hidden; margin-bottom:20px;
      animation:fadeUp .45s .05s ease both;
    }
    .champion-card::after {
      content:''; position:absolute; inset:0;
      background:radial-gradient(ellipse at 100% 0%,rgba(245,158,11,.08) 0%,transparent 55%);
      pointer-events:none;
    }
    .champ-crown { font-size:2.2rem; filter:drop-shadow(0 0 10px rgba(245,158,11,.5)); }
    .champ-img { width:80px; height:62px; object-fit:cover; border-radius:9px; border:2px solid var(--gold); flex-shrink:0; }
    .champ-info { flex:1; }
    .champ-badge {
      display:inline-block; padding:2px 9px; border-radius:20px;
      background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.25);
      color:var(--gold2); font-size:.65rem; text-transform:uppercase; letter-spacing:.06em; margin-bottom:6px;
    }
    .champ-plate { font-family:var(--head); font-size:1.6rem; font-weight:800; color:#fff; letter-spacing:.04em; }
    .champ-meta  { font-size:.74rem; color:var(--muted); margin-top:3px; }
    .champ-count-wrap { text-align:right; }
    .champ-count { font-family:var(--head); font-size:3rem; font-weight:800; color:var(--gold2); line-height:1; }
    .champ-count-lbl { font-size:.68rem; color:var(--muted); margin-top:3px; }

    /* ── Top 5 ───────────────────────────────────────────────── */
    .top5-list { display:flex; flex-direction:column; gap:8px; margin-bottom:28px; }
    .top5-row {
      background:var(--surf2); border:1px solid var(--border); border-radius:10px;
      padding:12px 16px; display:flex; align-items:center; gap:14px;
      animation:fadeUp .4s ease both;
    }
    .top5-row:nth-child(1){animation-delay:.04s}
    .top5-row:nth-child(2){animation-delay:.08s}
    .top5-row:nth-child(3){animation-delay:.12s}
    .top5-row:nth-child(4){animation-delay:.16s}
    .top5-row:nth-child(5){animation-delay:.20s}
    .rank-badge {
      width:30px; height:30px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      font-family:var(--head); font-weight:800; font-size:.78rem; flex-shrink:0;
    }
    .r1 { background:rgba(245,158,11,.12); color:var(--gold2);  border:1px solid rgba(245,158,11,.3); }
    .r2 { background:rgba(148,163,184,.08); color:var(--silver); border:1px solid rgba(148,163,184,.2); }
    .r3 { background:rgba(194,120,79,.1);   color:var(--bronze); border:1px solid rgba(194,120,79,.25); }
    .rn { background:rgba(30,41,59,.5);      color:var(--muted);  border:1px solid var(--border); }
    .top5-car-img { width:48px; height:38px; object-fit:cover; border-radius:6px; border:1px solid var(--border2); flex-shrink:0; }
    .top5-plate  { font-family:var(--head); font-weight:700; font-size:.9rem; color:#fff; }
    .top5-marque { font-size:.7rem; color:var(--muted); margin-top:2px; }
    .top5-bar-wrap { flex:2; min-width:60px; }
    .top5-track { background:var(--bg); border-radius:99px; height:6px; overflow:hidden; }
    .top5-fill  { height:100%; border-radius:99px; background:linear-gradient(90deg,var(--accent2),var(--accent)); }
    .top5-row:nth-child(1) .top5-fill { background:linear-gradient(90deg,#b45309,var(--gold2)); }
    .top5-row:nth-child(2) .top5-fill { background:linear-gradient(90deg,#475569,var(--silver)); }
    .top5-row:nth-child(3) .top5-fill { background:linear-gradient(90deg,#854d0e,var(--bronze)); }
    .top5-count { font-family:var(--head); font-weight:700; font-size:1rem; color:#fff; text-align:right; min-width:28px; }
    .top5-count small { display:block; font-family:var(--mono); font-size:.6rem; color:var(--muted); font-weight:400; }

    /* ── Divider ─────────────────────────────────────────────── */
    .section-divider { border:none; border-top:1px solid var(--border); margin:32px 0 28px; }

    /* ── Table card ──────────────────────────────────────────── */
    .vg-card { background:var(--surf2); border:1px solid var(--border2); border-radius:14px; overflow:hidden; }
    .vg-toolbar { display:flex; flex-wrap:wrap; gap:10px; align-items:center; padding:16px 20px; border-bottom:1px solid var(--border2); }
    .vg-search-wrap { position:relative; flex:1 1 200px; max-width:280px; }
    .vg-search-wrap .ico { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--muted); pointer-events:none; font-size:.78rem; }
    #searchMatricule {
      width:100%; padding:7px 10px 7px 30px; background:var(--bg); border:1px solid var(--border2);
      border-radius:8px; color:var(--text); font-family:var(--mono); font-size:.82rem; transition:border-color .2s;
    }
    #searchMatricule:focus { outline:none; border-color:var(--accent); }
    #searchMatricule::placeholder { color:var(--muted); }
    .vg-sort-group { display:flex; gap:5px; }
    .btn-sort {
      display:flex; align-items:center; gap:4px; padding:6px 12px;
      border-radius:8px; border:1px solid var(--border2); background:var(--bg);
      color:var(--silver); font-size:.76rem; font-weight:500; cursor:pointer;
      transition:all .2s; text-decoration:none;
    }
    .btn-sort svg { width:12px; height:12px; }
    .btn-sort:hover { border-color:var(--accent); color:var(--accent); }
    .btn-sort.active { background:rgba(56,189,248,.1); border-color:var(--accent); color:var(--accent); }
    .vg-count { margin-left:auto; font-size:.73rem; color:var(--muted); white-space:nowrap; }
    #visibleCount { color:var(--accent); font-weight:600; }

    .table { margin:0; }
    .table thead th {
      background:#0b1220; color:var(--muted2); text-transform:uppercase;
      font-size:.66rem; letter-spacing:.06em; padding:12px 16px;
      border-bottom:1px solid var(--border2); white-space:nowrap;
    }
    .table td { vertical-align:middle; border-color:var(--border2); color:var(--text); font-size:.83rem; padding:12px 16px; }
    .table tbody tr { transition:background .15s; animation:rowIn .2s ease both; }
    .table tbody tr:hover { background:rgba(56,189,248,.04); }
    <?php for($i=1;$i<=5;$i++): ?>
    .table tbody tr:nth-child(<?php echo $i; ?>) { animation-delay:<?php echo ($i-1)*0.04; ?>s; }
    <?php endfor; ?>

    .vehicle-photo { width:50px; height:44px; object-fit:cover; border-radius:7px; border:1px solid var(--border2); }
    .badge-plate {
      display:inline-block; background:rgba(251,191,36,.08); color:var(--gold2);
      padding:3px 8px; border-radius:5px; font-size:.76rem; font-weight:600; letter-spacing:.05em;
    }
    .badge-km { color:var(--green); font-weight:600; font-size:.8rem; }
    .date-cell { color:var(--muted2); font-size:.78rem; }
    .btn-delete {
      display:inline-flex; align-items:center; gap:5px; color:var(--red);
      background:rgba(239,68,68,.08); padding:6px 12px; border-radius:7px;
      border:1px solid transparent; font-size:.78rem; cursor:pointer;
      text-decoration:none; transition:all .2s;
    }
    .btn-delete:hover { background:var(--red); color:#fff; border-color:var(--red); }

    .vg-empty { text-align:center; padding:50px 20px; color:var(--muted); }
    .vg-empty svg { width:36px; height:36px; margin-bottom:10px; opacity:.35; }

    .vg-pagination {
      display:flex; align-items:center; justify-content:space-between;
      flex-wrap:wrap; gap:8px; padding:13px 20px; border-top:1px solid var(--border2);
    }
    .pg-info { font-size:.75rem; color:var(--muted); }
    .pg-info strong { color:var(--silver); }
    .pg-controls { display:flex; gap:4px; }
    .pg-btn {
      display:inline-flex; align-items:center; justify-content:center;
      min-width:32px; height:32px; padding:0 9px; border-radius:7px;
      border:1px solid var(--border2); background:var(--bg); color:var(--silver);
      font-size:.78rem; font-weight:500; text-decoration:none; transition:all .2s;
    }
    .pg-btn:hover:not(.disabled) { border-color:var(--accent); color:var(--accent); }
    .pg-btn.active  { background:var(--accent); border-color:var(--accent); color:#0b111e; font-weight:700; }
    .pg-btn.disabled { opacity:.3; pointer-events:none; }

    .stats-empty {
      text-align:center; padding:36px 20px;
      background:var(--surf2); border:1px solid var(--border);
      border-radius:12px; color:var(--muted); margin-bottom:28px; font-size:.82rem;
    }

    @media(max-width:600px){
      .champion-card { flex-direction:column; align-items:flex-start; }
      .champ-count-wrap { text-align:left; }
      .top5-bar-wrap { display:none; }
    }
    /* ── Boutons Export ──────────────────────────────────────────────── */
    .btn-export-group { display:flex; gap:8px; flex-wrap:wrap; }
    .btn-export {
      display:inline-flex; align-items:center; gap:6px;
      padding:7px 15px; border-radius:8px; border:1px solid;
      font-family:var(--mono); font-size:.78rem; font-weight:500;
      cursor:pointer; text-decoration:none; background:transparent;
      transition:all .2s; white-space:nowrap;
    }
    .btn-export-excel { color:#34d399; border-color:rgba(52,211,153,.3); background:rgba(52,211,153,.08); }
    .btn-export-excel:hover { background:#34d399; color:#0b111e; border-color:#34d399; }
    .btn-export-pdf   { color:var(--red);  border-color:rgba(239,68,68,.3);  background:rgba(239,68,68,.08); }
    .btn-export-pdf:hover   { background:var(--red); color:#fff; border-color:var(--red); }

  </style>

  <!-- jsPDF + AutoTable -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>

<body class="ds-init" data-sidebar-size="default">
<div class="wrapper">
  <?php include("../../partials/sidebar-collapse.html"); ?>

  <div id="content" class="main-content">
    <?php include("../../partials/topbar-second.html"); ?>

    <div class="container-fluid mt-4 px-4 pb-5">

      <!-- ── Page header ──────────────────────────────────────── -->
      <div class="page-header">
        <div>
          <h4>Gestion du Parc Automobile</h4>
          <p><?php echo $total; ?> véhicule<?php echo $total>1?'s':''; ?> · statistiques par rendez-vous</p>
        </div>
        <a href="Gestion_voitureBackend.php" class="btn-refresh">↺ Actualiser</a>
      </div>

      <!-- ════════════════════════════════════════════════════════
           STATISTIQUES
      ════════════════════════════════════════════════════════ -->
      <div class="sec-title">📊 Statistiques des rendez-vous</div>

      <!-- Filtre stats -->
      <div class="stat-filter">
        <form method="GET" action="">
          <?php if(isset($_GET['sort'])): ?>
          <input type="hidden" name="sort" value="<?php echo htmlspecialchars($_GET['sort']); ?>">
          <?php endif; ?>
          <div class="sf-group">
            <label>Du</label>
            <input type="date" name="sf_from" value="<?php echo htmlspecialchars($sf_dateFrom ?? ''); ?>">
          </div>
          <div class="sf-group">
            <label>Au</label>
            <input type="date" name="sf_to" value="<?php echo htmlspecialchars($sf_dateTo ?? ''); ?>">
          </div>
          <div class="sf-group">
            <label>Type de service</label>
            <select name="sf_type">
              <option value="">— Tous —</option>
              <?php foreach($types as $t): ?>
              <option value="<?php echo htmlspecialchars($t); ?>" <?php echo $sf_type===$t?'selected':''; ?>>
                <?php echo htmlspecialchars($t); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="sf-group">
            <label>Statut RDV</label>
            <select name="sf_statut">
              <option value="">— Tous —</option>
              <?php foreach($statuts as $s): ?>
              <option value="<?php echo htmlspecialchars($s); ?>" <?php echo $sf_statut===$s?'selected':''; ?>>
                <?php echo htmlspecialchars($s); ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <button type="submit" class="btn-sf">Filtrer</button>
          <a href="Gestion_voitureBackend.php<?php echo isset($_GET['sort'])?'?sort='.$_GET['sort']:''; ?>" class="btn-sf-reset">✕ Reset</a>
        </form>

        <?php if($sf_dateFrom || $sf_dateTo || $sf_type || $sf_statut): ?>
        <div class="ftags">
          <?php if($sf_dateFrom): ?><span class="ftag">📅 Depuis : <?php echo htmlspecialchars($sf_dateFrom); ?></span><?php endif; ?>
          <?php if($sf_dateTo):   ?><span class="ftag">📅 Jusqu'à : <?php echo htmlspecialchars($sf_dateTo); ?></span><?php endif; ?>
          <?php if($sf_type):     ?><span class="ftag">🔧 <?php echo htmlspecialchars($sf_type); ?></span><?php endif; ?>
          <?php if($sf_statut):   ?><span class="ftag">📌 <?php echo htmlspecialchars($sf_statut); ?></span><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- KPI cards -->
      <div class="kpi-row">
        <div class="kpi-card kpi-gold" style="animation-delay:.00s">
          <div class="kpi-label">🏆 Véhicule champion</div>
          <div class="kpi-val" style="font-size:1.05rem;letter-spacing:.04em">
            <?php echo $champion ? htmlspecialchars($champion['matriculevoiture']) : '—'; ?>
          </div>
          <div class="kpi-sub"><?php echo $champion ? htmlspecialchars($champion['marqueV']) : 'Aucun RDV'; ?></div>
        </div>
        <div class="kpi-card kpi-blue" style="animation-delay:.06s">
          <div class="kpi-label">Total rendez-vous</div>
          <div class="kpi-val"><?php echo $totalRDV; ?></div>
          <div class="kpi-sub">dans la sélection</div>
        </div>
        <div class="kpi-card kpi-green" style="animation-delay:.12s">
          <div class="kpi-label">Véhicules actifs</div>
          <div class="kpi-val"><?php echo $vehAvecRDV; ?></div>
          <div class="kpi-sub">avec au moins 1 RDV</div>
        </div>
        <div class="kpi-card kpi-purple" style="animation-delay:.18s">
          <div class="kpi-label">Moy. RDV / véhicule</div>
          <div class="kpi-val"><?php echo $avgRDV; ?></div>
          <div class="kpi-sub">rendez-vous en moyenne</div>
        </div>
      </div>

      <?php if($champion): ?>

      <!-- Champion card -->
      <div class="champion-card">
        <div class="champ-crown">🏆</div>
        <?php if(!empty($champion['imageVoiture'])): ?>
        <img src="/ProjetWeb/assets/img/<?php echo htmlspecialchars($champion['imageVoiture']); ?>"
             class="champ-img" onerror="this.style.display='none'">
        <?php endif; ?>
        <div class="champ-info">
          <div class="champ-badge">Nº 1 · Véhicule le plus sollicité</div>
          <div class="champ-plate"><?php echo htmlspecialchars($champion['matriculevoiture']); ?></div>
          <div class="champ-meta">
            Marque : <strong style="color:var(--text)"><?php echo htmlspecialchars($champion['marqueV']); ?></strong>
            <?php if($sf_type):   echo ' &nbsp;·&nbsp; Service : <strong style="color:var(--text)">'.htmlspecialchars($sf_type).'</strong>'; endif; ?>
            <?php if($sf_statut): echo ' &nbsp;·&nbsp; Statut : <strong style="color:var(--text)">'.htmlspecialchars($sf_statut).'</strong>'; endif; ?>
          </div>
        </div>
        <div class="champ-count-wrap">
          <div class="champ-count"><?php echo $champion['nbRDV']; ?></div>
          <div class="champ-count-lbl">rendez-vous</div>
        </div>
      </div>

      <!-- Top 5 -->
      <?php if(count($top5) > 1): ?>
      <div class="sec-title" style="margin-bottom:12px">🔝 Top <?php echo count($top5); ?> — véhicules les plus sollicités</div>
      <div class="top5-list">
        <?php foreach($top5 as $i => $v):
          $rank   = $i + 1;
          $pct    = $champion['nbRDV'] > 0 ? round(($v['nbRDV'] / $champion['nbRDV']) * 100) : 0;
          $rClass = match($rank) { 1=>'r1', 2=>'r2', 3=>'r3', default=>'rn' };
          $rLabel = match($rank) { 1=>'🥇', 2=>'🥈', 3=>'🥉', default=>$rank };
        ?>
        <div class="top5-row">
          <div class="rank-badge <?php echo $rClass; ?>"><?php echo $rLabel; ?></div>
          <?php if(!empty($v['imageVoiture'])): ?>
          <img src="/ProjetWeb/assets/img/<?php echo htmlspecialchars($v['imageVoiture']); ?>"
               class="top5-car-img" onerror="this.style.display='none'">
          <?php endif; ?>
          <div style="flex:1;min-width:0">
            <div class="top5-plate"><?php echo htmlspecialchars($v['matriculevoiture']); ?></div>
            <div class="top5-marque"><?php echo htmlspecialchars($v['marqueV']); ?></div>
          </div>
          <div class="top5-bar-wrap">
            <div class="top5-track">
              <div class="top5-fill" style="width:<?php echo $pct; ?>%"></div>
            </div>
          </div>
          <div class="top5-count">
            <?php echo $v['nbRDV']; ?>
            <small>rdv</small>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php else: ?>
      <div class="stats-empty">
        📭 Aucun rendez-vous trouvé avec ces filtres.<br>
        <span style="font-size:.75rem;color:var(--muted)">Élargissez la période ou supprimez un filtre.</span>
      </div>
      <?php endif; ?>

      <!-- ════════════════════════════════════════════════════════
           TABLEAU DES VÉHICULES
      ════════════════════════════════════════════════════════ -->
      <hr class="section-divider">
      <div class="sec-title">🚗 Liste des véhicules</div>

      <div class="vg-card shadow-lg">

        <!-- Toolbar -->
        <div class="vg-toolbar">
          <div class="vg-search-wrap">
            <span class="ico">🔍</span>
            <input type="text" id="searchMatricule" placeholder="Rechercher par matricule…" autocomplete="off">
          </div>
          <div class="vg-sort-group">
            <a href="<?php echo buildQuery(['sort'=>'asc','page'=>1]); ?>"
               class="btn-sort <?php echo $sortOrder==='asc'?'active':''; ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M3 8l9-5 9 5M3 16l9 5 9-5"/><line x1="12" y1="3" x2="12" y2="21"/>
              </svg>
              Plus ancien
            </a>
            <a href="<?php echo buildQuery(['sort'=>'desc','page'=>1]); ?>"
               class="btn-sort <?php echo $sortOrder==='desc'?'active':''; ?>">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M3 8l9-5 9 5M3 16l9 5 9-5"/><line x1="12" y1="21" x2="12" y2="3"/>
              </svg>
              Plus récent
            </a>
          </div>
          <span class="vg-count">
            <span id="visibleCount"><?php echo count($pageVehicules); ?></span> / <?php echo $total; ?>
          </span>
          <!-- Boutons Export -->
          <div class="btn-export-group">
            <button onclick="exportExcel()" class="btn-export btn-export-excel">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
              Excel
            </button>
            <button onclick="exportPDF()" class="btn-export btn-export-pdf">
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              PDF
            </button>
          </div>
        </div>

        <!-- Table -->
        <div class="table-responsive">
          <table class="table mb-0">
            <thead>
              <tr>
                <th>Photo</th>
                <th>Marque</th>
                <th>Matricule</th>
                <th>Kilométrage</th>
                <th>Date Ajout</th>
                <th>Client</th>
                <th class="text-center">Action</th>
              </tr>
            </thead>
            <tbody id="tableBody">
              <?php if($pageVehicules): foreach($pageVehicules as $v):
                  if(empty($v)||!isset($v['idVehicule'])) continue; ?>
              <tr data-matricule="<?php echo strtolower(htmlspecialchars($v['matriculevoiture'] ?? '')); ?>">
                <td>
                  <img src="/ProjetWeb/assets/img/<?php echo htmlspecialchars($v['imageVoiture']); ?>"
                       class="vehicle-photo" onerror="this.src='/ProjetWeb/assets/img/default-car.png'">
                </td>
                <td style="font-weight:600;color:#fff"><?php echo htmlspecialchars($v['marqueV']); ?></td>
                <td><span class="badge-plate"><?php echo htmlspecialchars($v['matriculevoiture']); ?></span></td>
                <td><span class="badge-km"><?php echo number_format($v['kilometrageV'],0,',',' '); ?> km</span></td>
                <td class="date-cell"><?php echo htmlspecialchars($v['date_ajoutV']); ?></td>
                <td style="color:var(--muted2)"><?php echo htmlspecialchars($v['nomclient'] ?? 'N/A'); ?></td>
                <td class="text-center">
                  <a href="Gestion_voitureBackend.php?delete_id=<?php echo $v['idVehicule']; ?><?php echo isset($_GET['sort'])?'&sort='.$_GET['sort']:''; ?>&page=<?php echo $page; ?>"
                     class="btn-delete"
                     onclick="return confirm('Confirmer la suppression de <?php echo htmlspecialchars($v['matriculevoiture']); ?> ?')">
                    🗑 Supprimer
                  </a>
                </td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="7">
                <div class="vg-empty">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/><line x1="8" y1="12" x2="16" y2="12"/>
                  </svg>
                  <p class="mb-0">Aucun véhicule enregistré.</p>
                </div>
              </td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <!-- No search result -->
        <div id="noSearchResult" style="display:none" class="vg-empty py-4">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:34px;height:34px;opacity:.35;margin-bottom:8px">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <p class="mb-0" style="font-size:.8rem">Aucun matricule correspondant.</p>
        </div>

        <!-- Pagination -->
        <?php if($totalPages > 1): ?>
        <div class="vg-pagination" id="paginationBar">
          <span class="pg-info">
            Page <strong><?php echo $page; ?></strong> / <strong><?php echo $totalPages; ?></strong>
            &nbsp;·&nbsp; <?php echo $offset+1; ?>–<?php echo min($offset+$perPage,$total); ?> sur <?php echo $total; ?>
          </span>
          <div class="pg-controls">
            <a href="<?php echo buildQuery(['page'=>$page-1]); ?>" class="pg-btn <?php echo $page<=1?'disabled':''; ?>">‹ Préc.</a>
            <?php
            $start=max(1,$page-2); $end=min($totalPages,$page+2);
            if($start>1) echo '<a href="'.buildQuery(['page'=>1]).'" class="pg-btn">1</a>';
            if($start>2) echo '<span class="pg-btn disabled">…</span>';
            for($p=$start;$p<=$end;$p++):
            ?><a href="<?php echo buildQuery(['page'=>$p]); ?>" class="pg-btn <?php echo $p===$page?'active':''; ?>"><?php echo $p; ?></a><?php
            endfor;
            if($end<$totalPages-1) echo '<span class="pg-btn disabled">…</span>';
            if($end<$totalPages)   echo '<a href="'.buildQuery(['page'=>$totalPages]).'" class="pg-btn">'.$totalPages.'</a>';
            ?>
            <a href="<?php echo buildQuery(['page'=>$page+1]); ?>" class="pg-btn <?php echo $page>=$totalPages?'disabled':''; ?>">Suiv. ›</a>
          </div>
        </div>
        <?php endif; ?>

      </div><!-- /.vg-card -->
    </div><!-- /.container-fluid -->
  </div>
</div>

<?php include("../../partials/scripts.html"); ?>
<script src="../../assets/BackOffice/js/vendors/sidebarnav.js"></script>
<script src="../../node_modules/jsvectormap/dist/js/jsvectormap.min.js"></script>
<script src="../../node_modules/jsvectormap/dist/maps/world.js"></script>
<script src="../../node_modules/jsvectormap/dist/maps/world-merc.js"></script>
<script src="../../node_modules/apexcharts/dist/apexcharts.min.js"></script>
<script src="../../assets/BackOffice/js/vendors/chart.js"></script>
<script src="../../node_modules/choices.js/public/assets/scripts/choices.min.js"></script>
<script src="../../assets/BackOffice/js/vendors/choice.js"></script>
<script src="../../node_modules/swiper/swiper-bundle.min.js"></script>
<script src="../../assets/BackOffice/js/vendors/swiper.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ── Recherche matricule ───────────────────────────────────────────────
(function(){
  const input  = document.getElementById('searchMatricule');
  const rows   = document.querySelectorAll('#tableBody tr[data-matricule]');
  const noRes  = document.getElementById('noSearchResult');
  const countEl= document.getElementById('visibleCount');
  const pgBar  = document.getElementById('paginationBar');
  if(!input) return;
  input.addEventListener('input', function(){
    const q = this.value.trim().toLowerCase();
    let vis = 0;
    rows.forEach(function(row){
      const match = !q || row.dataset.matricule.includes(q);
      row.style.display = match ? '' : 'none';
      if(match){ vis++; row.style.animation='none'; row.offsetHeight; row.style.animation=''; }
    });
    if(noRes)   noRes.style.display   = (vis===0 && q) ? 'block' : 'none';
    if(pgBar)   pgBar.style.display   = q ? 'none' : '';
    if(countEl) countEl.textContent   = vis;
  });
})();

// ── Données injectées par PHP (liste complète) ────────────────────────
const allVehicules = <?= json_encode(array_map(function($v) {
    return [
        $v['marqueV']          ?? '',
        $v['matriculevoiture'] ?? '',
        number_format((float)($v['kilometrageV'] ?? 0), 0, ',', ' ') . ' km',
        $v['date_ajoutV']      ?? '',
        $v['nomclient']        ?? 'N/A'
    ];
}, $listVehicules), JSON_UNESCAPED_UNICODE) ?>;

const HEADERS = ['Marque', 'Matricule', 'Kilométrage', 'Date Ajout', 'Client'];

// ── Export Excel ──────────────────────────────────────────────────────
function exportExcel() {
  let html = '<table><thead><tr>' + HEADERS.map(h => `<th>${h}</th>`).join('') + '</tr></thead><tbody>';
  allVehicules.forEach(r => {
    html += '<tr>' + r.map(v => `<td>${String(v).replace(/</g,'&lt;')}</td>`).join('') + '</tr>';
  });
  html += '</tbody></table>';
  const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
  downloadBlob(blob, 'vehicules.xls');
}

// ── Export PDF ────────────────────────────────────────────────────────
function exportPDF() {
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });

  doc.setFillColor(8, 14, 26);
  doc.rect(0, 0, 297, 210, 'F');

  doc.setFontSize(14);
  doc.setTextColor(56, 189, 248);
  doc.text('Gestion du Parc Automobile', 14, 16);

  doc.setFontSize(8);
  doc.setTextColor(71, 85, 105);
  doc.text('Exporté le ' + new Date().toLocaleDateString('fr-FR', {day:'2-digit',month:'2-digit',year:'numeric'}), 14, 22);

  doc.autoTable({
    head: [HEADERS],
    body: allVehicules,
    startY: 27,
    styles: {
      fontSize: 8.5,
      cellPadding: 4,
      textColor: [226, 232, 240],
      fillColor: [15, 24, 37],
      lineColor: [26, 40, 64],
      lineWidth: 0.3,
      overflow: 'ellipsize'
    },
    headStyles: {
      fillColor: [11, 18, 32],
      textColor: [100, 116, 139],
      fontStyle: 'bold',
      fontSize: 7.5,
      halign: 'left'
    },
    alternateRowStyles: { fillColor: [17, 28, 45] },
    columnStyles: {
      1: { cellWidth: 35 },
      2: { cellWidth: 32 },
      3: { cellWidth: 28 }
    },
    margin: { left: 14, right: 14 }
  });

  doc.save('vehicules.pdf');
}

// ── Helper download ───────────────────────────────────────────────────
function downloadBlob(blob, filename) {
  const url  = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href  = url;
  link.download = filename;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
}
</script>
</body>
</html>