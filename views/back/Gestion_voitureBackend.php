<?php
session_start();
require_once(__DIR__ . '/../../models/db.php');
$pdo = config::getConnexion();
require_once(__DIR__ . '/../../Controller/Vehicule.php');

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
<html lang="fr">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Gestion des Véhicules — Admin</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=DM+Mono:wght@400;500&family=Syne:wght@700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  <?php include("../../partials/head/head-meta.html"); ?>
  <?php include("../../partials/head/head-links.html"); ?>

  <style>
    body { background:#f1f5f9; font-family:'DM Mono',monospace; }
    @media (max-width: 991px) { #sidebar { transform: translateX(-100%); transition: transform .25s; } #sidebar.show { transform: translateX(0); } }

    :root {
      --bg:      #f1f5f9;
      --surf:    #ffffff;
      --surf2:   #f8fafc;
      --border:  #e2e8f0;
      --border2: #cbd5e1;
      --text:    #1e293b;
      --muted:   #64748b;
      --muted2:  #94a3b8;
      --accent:  #0ea5e9;
      --accent2: #0284c7;
      --gold:    #d97706;
      --gold2:   #f59e0b;
      --silver:  #64748b;
      --bronze:  #92400e;
      --green:   #059669;
      --red:     #dc2626;
      --mono:    'DM Mono', monospace;
      --head:    'Syne', sans-serif;
      --radius:  12px;
      --shadow:  0 1px 3px rgba(0,0,0,.06), 0 4px 12px rgba(0,0,0,.04);
      --shadow-md: 0 4px 16px rgba(0,0,0,.08), 0 1px 4px rgba(0,0,0,.04);
    }

    *, *::before, *::after { box-sizing: border-box; }
    body { background:var(--bg); color:var(--text); font-family:var(--mono); font-size:.84rem; }

    @keyframes fadeUp {
      from { opacity:0; transform:translateY(10px); }
      to   { opacity:1; transform:translateY(0); }
    }
    @keyframes rowIn {
      from { opacity:0; transform:translateY(4px); }
      to   { opacity:1; transform:translateY(0); }
    }
    @keyframes barGrow {
      from { width:0; }
    }

    /* ── Page header ─────────────────────────────────────────── */
    .page-header { display:flex; align-items:flex-end; justify-content:space-between; flex-wrap:wrap; gap:12px; margin-bottom:32px; }
    .page-header h4 { font-family:var(--head); font-size:1.5rem; color:#0f172a; margin:0; letter-spacing:-.01em; }
    .page-header p  { color:var(--muted); font-size:.76rem; margin:4px 0 0; }
    .btn-refresh {
      display:inline-flex; align-items:center; gap:6px;
      padding:8px 18px; border-radius:9px; border:1px solid var(--border2);
      background:#fff; color:var(--muted); font-size:.78rem; font-family:var(--mono);
      text-decoration:none; transition:all .2s; box-shadow:var(--shadow);
    }
    .btn-refresh:hover { border-color:var(--accent); color:var(--accent); background:#f0f9ff; }

    /* ── Section title ───────────────────────────────────────── */
    .sec-title {
      font-family:var(--head); font-size:.72rem; font-weight:700;
      text-transform:uppercase; letter-spacing:.12em; color:var(--muted2);
      margin-bottom:16px; display:flex; align-items:center; gap:10px;
    }
    .sec-title::after { content:''; flex:1; height:1px; background:var(--border); }

    /* ── Stat filter — formulaire de filtres ─────────────────── */
    .stat-filter {
      background:#fff; border:1px solid var(--border);
      border-radius:var(--radius); padding:20px 24px; margin-bottom:24px;
      animation:fadeUp .35s ease both;
      box-shadow:var(--shadow);
    }
    .stat-filter form { display:flex; flex-wrap:wrap; gap:12px; align-items:flex-end; }

    .sf-group { display:flex; flex-direction:column; gap:6px; flex:1 1 140px; min-width:0; }
    .sf-group label {
      font-size:.68rem; text-transform:uppercase; letter-spacing:.09em;
      color:var(--muted); font-weight:600;
    }
    .sf-group input,
    .sf-group select {
      padding:9px 12px;
      background:#fff;
      border:1.5px solid var(--border2);
      border-radius:9px;
      color:var(--text);
      font-family:var(--mono);
      font-size:.82rem;
      transition:border-color .2s, box-shadow .2s;
      width:100%;
      appearance:none;
      -webkit-appearance:none;
      line-height:1.4;
      height:40px;
    }
    .sf-group input:focus,
    .sf-group select:focus {
      outline:none;
      border-color:var(--accent);
      box-shadow:0 0 0 3px rgba(14,165,233,.12);
    }
    .sf-group input[type="date"] {
      color-scheme: light;
    }
    .sf-group input::placeholder { color:var(--muted2); }
    /* Chevron custom pour select */
    .sf-select-wrap { position:relative; }
    .sf-select-wrap select { padding-right:32px; cursor:pointer; }
    .sf-select-wrap::after {
      content:''; position:absolute; right:11px; top:50%; transform:translateY(-50%);
      width:0; height:0;
      border-left:4px solid transparent;
      border-right:4px solid transparent;
      border-top:5px solid var(--muted2);
      pointer-events:none;
    }

    .btn-sf {
      display:inline-flex; align-items:center; gap:6px;
      padding:0 20px; height:40px; border-radius:9px; border:none;
      background:var(--accent2); color:#fff; font-family:var(--mono);
      font-size:.82rem; font-weight:500; cursor:pointer; transition:all .2s; white-space:nowrap;
      box-shadow:0 2px 8px rgba(2,132,199,.25);
    }
    .btn-sf:hover { background:var(--accent); box-shadow:0 4px 14px rgba(14,165,233,.35); transform:translateY(-1px); }
    .btn-sf:active { transform:translateY(0); }
    .btn-sf-reset {
      display:inline-flex; align-items:center; gap:6px;
      padding:0 16px; height:40px; border-radius:9px; border:1.5px solid var(--border2);
      background:transparent; color:var(--muted); font-family:var(--mono);
      font-size:.8rem; cursor:pointer; text-decoration:none; white-space:nowrap; transition:all .2s;
    }
    .btn-sf-reset:hover { color:var(--red); border-color:rgba(220,38,38,.4); background:rgba(220,38,38,.04); }

    .ftags { display:flex; flex-wrap:wrap; gap:5px; margin-top:10px; }
    .ftag {
      padding:3px 10px; border-radius:20px;
      border:1px solid rgba(14,165,233,.2); background:rgba(14,165,233,.06);
      color:var(--accent); font-size:.68rem; font-weight:500;
    }

    /* ── KPI row ─────────────────────────────────────────────── */
    .kpi-row { display:grid; grid-template-columns:repeat(auto-fit,minmax(165px,1fr)); gap:14px; margin-bottom:24px; }
    .kpi-card {
      background:#fff; border:1px solid var(--border); border-radius:var(--radius); padding:18px 20px;
      position:relative; overflow:hidden; animation:fadeUp .4s ease both;
      box-shadow:var(--shadow); transition:box-shadow .2s, transform .2s;
    }
    .kpi-card:hover { box-shadow:var(--shadow-md); transform:translateY(-2px); }
    .kpi-card::before { content:''; position:absolute; top:0;left:0;right:0; height:3px; border-radius:3px 3px 0 0; }
    .kpi-blue::before   { background:linear-gradient(90deg,var(--accent2),var(--accent)); }
    .kpi-gold::before   { background:linear-gradient(90deg,var(--gold),var(--gold2)); }
    .kpi-green::before  { background:linear-gradient(90deg,#047857,var(--green)); }
    .kpi-purple::before { background:linear-gradient(90deg,#7c3aed,#a78bfa); }
    .kpi-label { font-size:.66rem; text-transform:uppercase; letter-spacing:.1em; color:var(--muted); margin-bottom:8px; font-weight:600; }
    .kpi-val   { font-family:var(--head); font-size:2rem; font-weight:800; color:#0f172a; line-height:1; }
    .kpi-gold  .kpi-val  { color:var(--gold2); }
    .kpi-green .kpi-val  { color:var(--green); }
    .kpi-purple .kpi-val { color:#8b5cf6; }
    .kpi-sub { font-size:.69rem; color:var(--muted2); margin-top:5px; }

    /* ── Champion card ───────────────────────────────────────── */
    .champion-card {
      background:#fff; border:1px solid var(--border); border-radius:14px;
      padding:22px 28px; display:flex; align-items:center; gap:22px; flex-wrap:wrap;
      position:relative; overflow:hidden; margin-bottom:24px;
      animation:fadeUp .45s .05s ease both;
      box-shadow:var(--shadow-md);
    }
    .champion-card::after {
      content:''; position:absolute; inset:0;
      background:radial-gradient(ellipse at 95% -10%,rgba(245,158,11,.1) 0%,transparent 50%),
                 radial-gradient(ellipse at 5% 110%,rgba(245,158,11,.05) 0%,transparent 50%);
      pointer-events:none;
    }
    .champ-crown { font-size:2.4rem; filter:drop-shadow(0 0 12px rgba(245,158,11,.6)); flex-shrink:0; }
    .champ-img {
      width:84px; height:66px; object-fit:cover; border-radius:10px;
      border:2px solid rgba(217,119,6,.35); flex-shrink:0;
      box-shadow:0 4px 12px rgba(217,119,6,.2);
    }
    .champ-info { flex:1; min-width:0; }
    .champ-badge {
      display:inline-flex; align-items:center; gap:5px;
      padding:3px 10px; border-radius:20px;
      background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.3);
      color:var(--gold2); font-size:.66rem; text-transform:uppercase; letter-spacing:.07em; margin-bottom:8px;
      font-weight:700;
    }
    .champ-plate { font-family:var(--head); font-size:1.7rem; font-weight:800; color:#0f172a; letter-spacing:.03em; line-height:1.1; }
    .champ-meta  { font-size:.75rem; color:var(--muted); margin-top:5px; }
    .champ-meta strong { color:var(--text); }
    .champ-count-wrap { text-align:right; flex-shrink:0; }
    .champ-count { font-family:var(--head); font-size:3.2rem; font-weight:800; color:var(--gold2); line-height:1; }
    .champ-count-lbl { font-size:.69rem; color:var(--muted); margin-top:4px; letter-spacing:.04em; }

    /* ── Top 5 ───────────────────────────────────────────────── */
    .top5-list { display:flex; flex-direction:column; gap:10px; margin-bottom:30px; }
    .top5-row {
      background:#fff; border:1px solid var(--border); border-radius:11px;
      padding:13px 18px; display:flex; align-items:center; gap:14px;
      animation:fadeUp .4s ease both;
      box-shadow:var(--shadow); transition:box-shadow .2s, transform .15s;
    }
    .top5-row:hover { box-shadow:var(--shadow-md); transform:translateX(2px); }
    .top5-row:nth-child(1){animation-delay:.04s}
    .top5-row:nth-child(2){animation-delay:.09s}
    .top5-row:nth-child(3){animation-delay:.14s}
    .top5-row:nth-child(4){animation-delay:.19s}
    .top5-row:nth-child(5){animation-delay:.24s}
    .rank-badge {
      width:32px; height:32px; border-radius:50%;
      display:flex; align-items:center; justify-content:center;
      font-family:var(--head); font-weight:800; font-size:.78rem; flex-shrink:0;
    }
    .r1 { background:rgba(245,158,11,.12); color:#b45309; border:1.5px solid rgba(245,158,11,.35); }
    .r2 { background:rgba(148,163,184,.1);  color:#475569; border:1.5px solid rgba(148,163,184,.25); }
    .r3 { background:rgba(180,83,9,.1);     color:#92400e; border:1.5px solid rgba(180,83,9,.25); }
    .rn { background:rgba(100,116,139,.08); color:var(--muted); border:1.5px solid var(--border2); }
    .top5-car-img { width:50px; height:40px; object-fit:cover; border-radius:7px; border:1px solid var(--border2); flex-shrink:0; }
    .top5-plate  { font-family:var(--head); font-weight:700; font-size:.9rem; color:#0f172a; }
    .top5-marque { font-size:.71rem; color:var(--muted); margin-top:2px; }
    .top5-bar-wrap { flex:2; min-width:60px; }
    .top5-track { background:var(--border); border-radius:99px; height:7px; overflow:hidden; }
    .top5-fill  {
      height:100%; border-radius:99px;
      background:linear-gradient(90deg,var(--accent2),var(--accent));
      animation: barGrow .7s ease both;
    }
    .top5-row:nth-child(1) .top5-fill { background:linear-gradient(90deg,#b45309,var(--gold2)); animation-delay:.05s; }
    .top5-row:nth-child(2) .top5-fill { background:linear-gradient(90deg,#475569,#94a3b8); animation-delay:.1s; }
    .top5-row:nth-child(3) .top5-fill { background:linear-gradient(90deg,#854d0e,#d97706); animation-delay:.15s; }
    .top5-count { font-family:var(--head); font-weight:800; font-size:1.05rem; color:#0f172a; text-align:right; min-width:30px; }
    .top5-count small { display:block; font-family:var(--mono); font-size:.61rem; color:var(--muted); font-weight:400; }

    /* ── Divider ─────────────────────────────────────────────── */
    .section-divider { border:none; border-top:1px solid var(--border); margin:36px 0 30px; }

    /* ── Table card ──────────────────────────────────────────── */
    .vg-card {
      background:#fff; border:1px solid var(--border); border-radius:14px;
      overflow:hidden; box-shadow:var(--shadow);
    }
    .vg-toolbar {
      display:flex; flex-wrap:wrap; gap:10px; align-items:center;
      padding:16px 20px; border-bottom:1px solid var(--border); background:#fafbfc;
    }
    .vg-search-wrap { position:relative; flex:1 1 200px; max-width:300px; }
    .vg-search-wrap .ico { position:absolute; left:11px; top:50%; transform:translateY(-50%); color:var(--muted); pointer-events:none; font-size:.8rem; }
    #searchMatricule {
      width:100%; padding:9px 12px 9px 34px; background:#fff; border:1.5px solid var(--border2);
      border-radius:9px; color:var(--text); font-family:var(--mono); font-size:.82rem;
      transition:border-color .2s, box-shadow .2s; height:40px;
    }
    #searchMatricule:focus { outline:none; border-color:var(--accent); box-shadow:0 0 0 3px rgba(14,165,233,.12); }
    #searchMatricule::placeholder { color:var(--muted2); }

    .vg-sort-group { display:flex; gap:5px; }
    .btn-sort {
      display:inline-flex; align-items:center; gap:4px; padding:0 12px; height:40px;
      border-radius:9px; border:1.5px solid var(--border2); background:#fff;
      color:var(--muted); font-size:.76rem; font-weight:500; cursor:pointer;
      transition:all .2s; text-decoration:none;
    }
    .btn-sort svg { width:12px; height:12px; }
    .btn-sort:hover { border-color:var(--accent); color:var(--accent); background:#f0f9ff; }
    .btn-sort.active { background:rgba(14,165,233,.1); border-color:var(--accent); color:var(--accent2); font-weight:600; }
    .vg-count { margin-left:auto; font-size:.73rem; color:var(--muted); white-space:nowrap; }
    #visibleCount { color:var(--accent); font-weight:600; }

    .table { margin:0; border-collapse:collapse; }
    .table thead th {
      background:#f8fafc; color:var(--muted); text-transform:uppercase;
      font-size:.66rem; letter-spacing:.08em; padding:13px 16px;
      border-bottom:1.5px solid var(--border); white-space:nowrap; font-weight:700;
    }
    .table td {
      vertical-align:middle; border-color:var(--border); color:var(--text);
      font-size:.83rem; padding:13px 16px; background:transparent;
    }
    .table tbody tr { transition:background .12s; animation:rowIn .2s ease both; }
    .table tbody tr:hover td { background:rgba(14,165,233,.03) !important; }
    <?php for($i=1;$i<=5;$i++): ?>
    .table tbody tr:nth-child(<?php echo $i; ?>) { animation-delay:<?php echo ($i-1)*0.04; ?>s; }
    <?php endfor; ?>

    .vehicle-photo {
      width:52px; height:44px; object-fit:cover; border-radius:8px;
      border:1.5px solid var(--border); transition:transform .2s;
    }
    .vehicle-photo:hover { transform:scale(1.08); }

    .badge-plate {
      display:inline-flex; align-items:center;
      background:rgba(180,83,9,.08); color:#92400e;
      padding:4px 10px; border-radius:6px; font-size:.77rem; font-weight:700; letter-spacing:.06em;
      border:1px solid rgba(180,83,9,.18);
    }
    .badge-km {
      display:inline-flex; align-items:center; gap:3px;
      color:#047857; font-weight:600; font-size:.8rem;
    }
    .date-cell { color:var(--muted2); font-size:.79rem; font-variant-numeric: tabular-nums; }
    .btn-delete {
      display:inline-flex; align-items:center; gap:5px; color:var(--red);
      background:rgba(220,38,38,.07); padding:7px 13px; border-radius:8px;
      border:1px solid rgba(220,38,38,.15); font-size:.78rem; cursor:pointer;
      text-decoration:none; transition:all .2s; font-weight:500;
    }
    .btn-delete:hover { background:var(--red); color:#fff; border-color:var(--red); box-shadow:0 4px 12px rgba(220,38,38,.3); }

    .vg-empty { text-align:center; padding:56px 20px; color:var(--muted); }
    .vg-empty svg { width:38px; height:38px; margin-bottom:12px; opacity:.3; }
    .vg-empty p { font-size:.84rem; }

    .vg-pagination {
      display:flex; align-items:center; justify-content:space-between;
      flex-wrap:wrap; gap:8px; padding:14px 20px; border-top:1px solid var(--border); background:#fafbfc;
    }
    .pg-info { font-size:.75rem; color:var(--muted); }
    .pg-info strong { color:var(--text); font-weight:700; }
    .pg-controls { display:flex; gap:4px; }
    .pg-btn {
      display:inline-flex; align-items:center; justify-content:center;
      min-width:34px; height:34px; padding:0 10px; border-radius:8px;
      border:1.5px solid var(--border); background:#fff; color:var(--muted);
      font-size:.78rem; font-weight:500; text-decoration:none; transition:all .2s;
    }
    .pg-btn:hover:not(.disabled) { border-color:var(--accent); color:var(--accent); background:#f0f9ff; }
    .pg-btn.active  { background:var(--accent2); border-color:var(--accent2); color:#fff; font-weight:700; }
    .pg-btn.disabled { opacity:.28; pointer-events:none; cursor:not-allowed; }

    .stats-empty {
      text-align:center; padding:40px 20px;
      background:#fff; border:1px solid var(--border);
      border-radius:var(--radius); color:var(--muted); margin-bottom:30px; font-size:.82rem;
      box-shadow:var(--shadow);
    }

    /* ── Boutons Export ──────────────────────────────────────────────── */
    .btn-export-group { display:flex; gap:8px; flex-wrap:wrap; }
    .btn-export {
      display:inline-flex; align-items:center; gap:6px;
      padding:0 16px; height:40px; border-radius:9px; border:1.5px solid;
      font-family:var(--mono); font-size:.78rem; font-weight:500;
      cursor:pointer; text-decoration:none; background:transparent;
      transition:all .2s; white-space:nowrap;
    }
    .btn-export-excel { color:#047857; border-color:rgba(4,120,87,.25); background:rgba(4,120,87,.06); }
    .btn-export-excel:hover { background:#047857; color:#fff; border-color:#047857; box-shadow:0 4px 12px rgba(4,120,87,.25); }
    .btn-export-pdf   { color:#b91c1c; border-color:rgba(185,28,28,.25); background:rgba(185,28,28,.06); }
    .btn-export-pdf:hover { background:#b91c1c; color:#fff; border-color:#b91c1c; box-shadow:0 4px 12px rgba(185,28,28,.25); }

    /* ── Dropdown ────────────────────────────────────────────── */
    .dropdown-menu { background:#fff !important; border:1px solid var(--border2) !important; }
    .dropdown-item { color:var(--text) !important; }
    .dropdown-item:hover { background:var(--surf2) !important; }
    .dropdown-item.text-danger { color:var(--red) !important; }

    /* ── Responsive ──────────────────────────────────────────── */
    @media(max-width:768px){
      .champion-card { flex-direction:column; align-items:flex-start; }
      .champ-count-wrap { text-align:left; }
      .top5-bar-wrap { display:none; }
      .kpi-row { grid-template-columns: repeat(2,1fr); }
      .vg-toolbar { flex-wrap:wrap; gap:8px; }
      .btn-export-group { flex-wrap:wrap; }
    }
    @media(max-width:480px){
      .kpi-row { grid-template-columns: 1fr; }
      .stat-filter form { flex-direction:column; }
      .sf-group { flex:none; width:100%; }
    }
    @media(max-width:991px){
      div[style*="margin-left:260px"] { margin-left:0 !important; }
    }

  </style>

  <!-- jsPDF + AutoTable -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
</head>

<body>
<div class="layout-wrapper" style="display:flex;min-height:100vh;">

<!-- ══ SIDEBAR ══ -->
<aside style="width:260px;background:#0f172a;position:fixed;left:0;top:0;height:100vh;z-index:1040;display:flex;flex-direction:column;overflow:hidden;" id="sidebar">
  <a href="admin.php" style="display:flex;align-items:center;gap:10px;padding:20px 20px 16px;border-bottom:1px solid rgba(255,255,255,.08);text-decoration:none;">
    <div style="width:36px;height:36px;border-radius:10px;background:#6366f1;display:flex;align-items:center;justify-content:center;font-weight:800;color:#fff;font-size:1rem;">AT</div>
    <span style="font-weight:700;color:#fff;font-size:1.15rem;">Entre AuTout</span>
    <span style="font-size:9px;background:rgba(99,102,241,.25);color:#a5b4fc;border-radius:4px;padding:1px 6px;margin-left:4px;">Admin</span>
  </a>
  <nav style="flex:1;overflow-y:auto;padding:12px 0;">
    <div style="font-size:10px;font-weight:700;letter-spacing:.08em;color:rgba(255,255,255,.35);text-transform:uppercase;padding:16px 20px 6px;">Principal</div>
    <a href="admin.php" style="display:flex;align-items:center;gap:12px;padding:9px 20px;color:rgba(255,255,255,.65);text-decoration:none;font-size:.875rem;font-weight:500;" onmouseover="this.style.background='rgba(255,255,255,.06)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.65)'">
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="ti ti-layout-dashboard"></i></span> Tableau de bord
    </a>
    <div style="font-size:10px;font-weight:700;letter-spacing:.08em;color:rgba(255,255,255,.35);text-transform:uppercase;padding:16px 20px 6px;">Modules</div>
    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/BackOffice/back.php" style="display:flex;align-items:center;gap:12px;padding:9px 20px;color:rgba(255,255,255,.65);text-decoration:none;font-size:.875rem;font-weight:500;" onmouseover="this.style.background='rgba(255,255,255,.06)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.65)'">
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="ti ti-building"></i></span> Garages &amp; Services
      <span style="margin-left:auto;font-size:10px;padding:1px 7px;border-radius:20px;background:#6c63ff;color:#fff;">Rayen</span>
    </a>
    <a href="#navVehicules" data-bs-toggle="collapse" style="display:flex;align-items:center;gap:12px;padding:9px 20px;background:rgba(99,102,241,.2);color:#a5b4fc;text-decoration:none;font-size:.875rem;font-weight:500;border-left:3px solid #6366f1;">
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="ti ti-car"></i></span> Véhicules
      <span style="margin-left:auto;font-size:10px;padding:1px 7px;border-radius:20px;background:rgba(239,68,68,.15);color:#ef4444;">Ela</span>
    </a>
    <div class="collapse show" id="navVehicules">
      <div style="padding-left:52px;">
        <a href="Gestion_voitureBackend.php" style="display:flex;align-items:center;gap:12px;padding:6px 16px 6px 0;color:#a5b4fc;text-decoration:none;font-size:.82rem;">Gestion des véhicules</a>
        <a href="Gestion_rendezVousBackend.php" style="display:flex;align-items:center;gap:12px;padding:6px 16px 6px 0;color:rgba(255,255,255,.5);text-decoration:none;font-size:.82rem;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Gestion des rendez-vous</a>
      </div>
    </div>
    <a href="#navEntretien" data-bs-toggle="collapse" style="display:flex;align-items:center;gap:12px;padding:9px 20px;color:rgba(255,255,255,.65);text-decoration:none;font-size:.875rem;font-weight:500;" onmouseover="this.style.background='rgba(255,255,255,.06)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.65)'">
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="ti ti-tools"></i></span> Entretien
      <span style="margin-left:auto;font-size:10px;padding:1px 7px;border-radius:20px;background:rgba(6,182,212,.15);color:#06b6d4;">Asma</span>
    </a>
    <div class="collapse" id="navEntretien"><div style="padding-left:52px;"><a href="#" style="display:flex;padding:6px 16px 6px 0;color:rgba(255,255,255,.5);text-decoration:none;font-size:.82rem;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Rendez-vous</a></div></div>
    <a href="#navPieces" data-bs-toggle="collapse" style="display:flex;align-items:center;gap:12px;padding:9px 20px;color:rgba(255,255,255,.65);text-decoration:none;font-size:.875rem;font-weight:500;" onmouseover="this.style.background='rgba(255,255,255,.06)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.65)'">
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="ti ti-shopping-cart"></i></span> Vente de Pièces
      <span style="margin-left:auto;font-size:10px;padding:1px 7px;border-radius:20px;background:rgba(245,158,11,.15);color:#f59e0b;">Amen</span>
    </a>
    <div class="collapse" id="navPieces"><div style="padding-left:52px;"><a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=admin#fraud-section" style="display:flex;padding:6px 16px 6px 0;color:rgba(255,255,255,.5);text-decoration:none;font-size:.82rem;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Panel Admin</a></div></div>
    <a href="#navClients" data-bs-toggle="collapse" style="display:flex;align-items:center;gap:12px;padding:9px 20px;color:rgba(255,255,255,.65);text-decoration:none;font-size:.875rem;font-weight:500;" onmouseover="this.style.background='rgba(255,255,255,.06)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.65)'">
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="ti ti-users"></i></span> Clients &amp; Utilisateurs
      <span style="margin-left:auto;font-size:10px;padding:1px 7px;border-radius:20px;background:rgba(148,163,184,.15);color:#94a3b8;">Insaf</span>
    </a>
    <div class="collapse" id="navClients"><div style="padding-left:52px;"><a href="admin.php" style="display:flex;padding:6px 16px 6px 0;color:rgba(255,255,255,.5);text-decoration:none;font-size:.82rem;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='rgba(255,255,255,.5)'">Liste des clients</a></div></div>
    <div style="font-size:10px;font-weight:700;letter-spacing:.08em;color:rgba(255,255,255,.35);text-transform:uppercase;padding:16px 20px 6px;">Système</div>
    <a href="#" style="display:flex;align-items:center;gap:12px;padding:9px 20px;color:rgba(255,255,255,.65);text-decoration:none;font-size:.875rem;font-weight:500;" onmouseover="this.style.background='rgba(255,255,255,.06)';this.style.color='#fff'" onmouseout="this.style.background='';this.style.color='rgba(255,255,255,.65)'">
      <span style="width:20px;height:20px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;"><i class="ti ti-settings"></i></span> Configuration
    </a>
  </nav>
  <div style="padding:14px 20px;border-top:1px solid rgba(255,255,255,.08);">
    <div style="display:flex;align-items:center;gap:10px;">
      <div style="width:34px;height:34px;border-radius:50%;background:#6366f1;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:.8rem;">A</div>
      <div>
        <div style="font-size:.82rem;font-weight:600;color:#fff;">Administrateur</div>
        <div style="font-size:.72rem;color:rgba(255,255,255,.4);"><?= htmlspecialchars($_SESSION['email'] ?? 'admin@autout.tn') ?></div>
      </div>
      <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/UserController.php?action=logout" style="margin-left:auto;color:rgba(255,255,255,.4);text-decoration:none;font-size:1rem;" onmouseover="this.style.color='#f87171'" onmouseout="this.style.color='rgba(255,255,255,.4)'" title="Déconnexion"><i class="ti ti-logout"></i></a>
    </div>
  </div>
</aside>

<!-- ══ MAIN ══ -->
<div style="margin-left:260px;flex:1;display:flex;flex-direction:column;min-height:100vh;">

  <!-- Topbar -->
  <header style="height:64px;background:#fff;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;padding:0 24px;position:sticky;top:0;z-index:1030;">
    <div style="display:flex;align-items:center;gap:8px;">
      <button class="d-lg-none border-0 bg-transparent" onclick="document.getElementById('sidebar').classList.toggle('show')" style="width:36px;height:36px;border-radius:50%;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:center;cursor:pointer;color:#64748b;"><i class="ti ti-menu-2"></i></button>
      <div>
        <div style="font-size:1.05rem;font-weight:700;color:#1e293b;">Gestion des Véhicules</div>
        <div style="font-size:.75rem;color:#94a3b8;"><?= date('l d F Y') ?></div>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;">
      <div class="dropdown">
        <a class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" href="#">
          <div style="width:34px;height:34px;border-radius:50%;background:#6366f1;display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:.8rem;">A</div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
          <li><a class="dropdown-item text-danger" href="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/UserController.php?action=logout"><i class="ti ti-logout me-2"></i>Déconnexion</a></li>
        </ul>
      </div>
    </div>
  </header>

  <div style="padding:24px;flex:1;">
  <div class="container-fluid px-0">

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
        <img src="../../assets/img/<?php echo htmlspecialchars($champion['imageVoiture']); ?>"
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
          <img src="../../assets/img/<?php echo htmlspecialchars($v['imageVoiture']); ?>"
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
                  <img src="../../assets/img/<?php echo htmlspecialchars($v['imageVoiture']); ?>"
                       class="vehicle-photo" onerror="this.src='../../assets/img/'">
                </td>
                <td style="font-weight:600;color:var(--text)"><?php echo htmlspecialchars($v['marqueV']); ?></td>
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
</div><!-- /container-fluid -->
</div><!-- /padding div -->
</div><!-- /main area -->
</div><!-- /layout-wrapper -->
</body>
</html>