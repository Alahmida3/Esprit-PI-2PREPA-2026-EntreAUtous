<?php
// ══════════════════════════════════════════════════════════════════════
//  Gestion des Rendez-vous — Back-office
//  Mise à jour : recherche · filtre · tri · pagination · JOIN client/véhicule
//  Design original conservé à 100%
// ══════════════════════════════════════════════════════════════════════
require_once "../../models/db.php";
$pdo = config::getConnexion();
require_once "../../Controller/RendezVous.php";

$rendezVousC = new RendezVousC();

// ── SUPPRESSION ──────────────────────────────────────────────────────
if (isset($_GET['delete_id'])) {
    $rendezVousC->delete((int)$_GET['delete_id']);
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
//  REQUÊTE PRINCIPALE — JOIN user + vehicule
// ══════════════════════════════════════════════════════════════════════
$where  = [];
$params = [];

if ($sf_from)  { $where[] = 'r.dateRDV >= :sf_from';         $params[':sf_from']   = $sf_from;  }
if ($sf_to)    { $where[] = 'r.dateRDV <= :sf_to';           $params[':sf_to']     = $sf_to;    }
if ($sf_type)  { $where[] = 'r.type_serviceRDV = :sf_type';  $params[':sf_type']   = $sf_type;  }
if ($sf_statut){ $where[] = 'r.statutRDV = :sf_statut';      $params[':sf_statut'] = $sf_statut;}
if ($search !== '') {
    $where[] = '(u.nom LIKE :q OR u.prenom LIKE :q2 OR v.matriculevoiture LIKE :q3)';
    $params[':q']  = '%' . $search . '%';
    $params[':q2'] = '%' . $search . '%';
    $params[':q3'] = '%' . $search . '%';
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
        CONCAT(u.nom, ' ', u.prenom) AS nomclient,
        v.matriculevoiture
    FROM rendezvous r
    LEFT JOIN client   u ON u.id_client  = r.idclientRDV
    LEFT JOIN vehicule v ON v.idVehicule = r.idVehicule
    $whereSQL
    ORDER BY $sortSQL
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allRDV = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ══════════════════════════════════════════════════════════════════════
//  NOTIFICATIONS
// ══════════════════════════════════════════════════════════════════════

$allRdvNotifSQL = "
    SELECT 
        r.idRDV, r.dateRDV, r.heureRDV, r.type_serviceRDV,
        r.statutRDV, r.descriptionRDV, r.idclientRDV, r.idVehicule,
        CONCAT(u.nom, ' ', u.prenom) AS nomclient,
        v.matriculevoiture
    FROM rendezvous r
    LEFT JOIN client   u ON u.id_client  = r.idclientRDV
    LEFT JOIN vehicule v ON v.idVehicule = r.idVehicule
    ORDER BY r.dateRDV ASC, r.heureRDV ASC
";
$allRdvNotifStmt = $pdo->prepare($allRdvNotifSQL);
$allRdvNotifStmt->execute();
$allRdvNotif = $allRdvNotifStmt->fetchAll(PDO::FETCH_ASSOC);

$maintenant = new DateTime();
$limite     = (clone $maintenant)->modify('+48 hours');
$notifications = [];

foreach ($allRdvNotif as $r) {
    $statut = strtolower(trim($r['statutRDV'] ?? ''));
    if (in_array($statut, ['annulé','annule','cancelled','canceled'])) continue;
    try { $dtRdv = new DateTime(trim($r['dateRDV']) . ' ' . trim($r['heureRDV'])); }
    catch (Exception $e) { continue; }
    if ($dtRdv > $maintenant && $dtRdv <= $limite) {
        $diffSec      = $dtRdv->getTimestamp() - $maintenant->getTimestamp();
        $totalMinutes = (int)floor($diffSec / 60);
        $notifications[] = array_merge($r, [
            'heures_restantes'  => (int)floor($totalMinutes / 60),
            'minutes_restantes' => $totalMinutes,
            'minutes_part'      => $totalMinutes % 60,
        ]);
    }
}
$nbNotifs = count($notifications);

// ══════════════════════════════════════════════════════════════════════
//  STATISTIQUES
// ══════════════════════════════════════════════════════════════════════

$stats_total = count($allRDV);
$stats_confirmes = 0;
$stats_annules = 0;
$stats_attente = 0;
$stats_termines = 0;

foreach ($allRDV as $r) {
    $statut = $r['statutRDV'] ?? 'en attente';
    if ($statut === 'confirmé') $stats_confirmes++;
    elseif ($statut === 'annulé') $stats_annules++;
    elseif ($statut === 'terminé') $stats_termines++;
    else $stats_attente++;
}

$stats_services = [];
foreach ($allRDV as $r) {
    $service = $r['type_serviceRDV'] ?? 'Autre';
    if (!isset($stats_services[$service])) $stats_services[$service] = 0;
    $stats_services[$service]++;
}
arsort($stats_services);

$mois_courant = date('Y-m');
$stats_mois = 0;
$stats_mois_confirmes = 0;
foreach ($allRDV as $r) {
    if (substr($r['dateRDV'], 0, 7) === $mois_courant) {
        $stats_mois++;
        if (($r['statutRDV'] ?? 'en attente') === 'confirmé') $stats_mois_confirmes++;
    }
}

$aujourdhui = date('Y-m-d');
$stats_a_venir = 0;
$stats_passes = 0;
foreach ($allRDV as $r) {
    if ($r['dateRDV'] >= $aujourdhui && ($r['statutRDV'] ?? 'en attente') !== 'annulé') $stats_a_venir++;
    if ($r['dateRDV'] < $aujourdhui && ($r['statutRDV'] ?? 'en attente') !== 'annulé') $stats_passes++;
}

$top_clients = [];
foreach ($allRDV as $r) {
    $client = $r['nomclient'];
    if (!isset($top_clients[$client])) $top_clients[$client] = 0;
    $top_clients[$client]++;
}
arsort($top_clients);
$top_clients = array_slice($top_clients, 0, 5);

$stats_non_annules = $stats_total - $stats_annules;
$taux_satisfaction = $stats_non_annules > 0 ? round(($stats_termines / $stats_non_annules) * 100) : 0;

$satisfaction_color = '#f59e0b';
$satisfaction_message = 'À améliorer';
if ($taux_satisfaction >= 80) { $satisfaction_color = '#10b981'; $satisfaction_message = 'Excellent'; }
elseif ($taux_satisfaction >= 60) { $satisfaction_color = '#3b82f6'; $satisfaction_message = 'Bon'; }
elseif ($taux_satisfaction >= 40) { $satisfaction_color = '#f59e0b'; $satisfaction_message = 'Moyen'; }
else { $satisfaction_color = '#ef4444'; $satisfaction_message = 'À améliorer'; }

// ── PAGINATION ────────────────────────────────────────────────────────
$perPage    = 10;
$total      = count($allRDV);
$totalPages = max(1, (int)ceil($total / $perPage));
$page       = isset($_GET['page']) ? max(1, min((int)$_GET['page'], $totalPages)) : 1;
$offset     = ($page - 1) * $perPage;
$listRDV    = array_slice($allRDV, $offset, $perPage);

$types   = $pdo->query("SELECT DISTINCT type_serviceRDV FROM rendezvous WHERE type_serviceRDV IS NOT NULL ORDER BY type_serviceRDV")->fetchAll(PDO::FETCH_COLUMN);
$statuts = $pdo->query("SELECT DISTINCT statutRDV FROM rendezvous WHERE statutRDV IS NOT NULL ORDER BY statutRDV")->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Gestion des Rendez-vous - Admin</title>
  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
  <?php include("../../partials/head/head-links.html"); ?>
  
  <!-- Styles -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Inter', sans-serif;
      background: #f5f7fa;
      color: #1e293b;
    }

    /* ========== SIDEBAR STYLES ========== */
    .sidebar {
      width: 280px;
      background: #ffffff;
      border-right: 1px solid #e2e8f0;
      position: fixed;
      left: 0;
      top: 0;
      height: 100vh;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      transition: all 0.3s;
    }

    .sidebar-header {
      padding: 24px 24px 20px;
      border-bottom: 1px solid #e2e8f0;
    }

    .logo {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .logo-icon {
      width: 42px;
      height: 42px;
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 800;
      font-size: 1.2rem;
    }

    .logo-text {
      font-weight: 800;
      font-size: 1.2rem;
      color: #0f172a;
    }

    .logo-badge {
      background: #e0f2fe;
      color: #0284c7;
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 0.65rem;
      font-weight: 600;
      margin-left: 6px;
    }

    .nav-section {
      padding: 20px 16px 8px 24px;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.1em;
      color: #94a3b8;
    }

    .nav-item {
      padding: 10px 20px;
      margin: 4px 12px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      gap: 12px;
      color: #64748b;
      text-decoration: none;
      transition: all 0.2s;
      font-size: 0.9rem;
      font-weight: 500;
    }

    .nav-item:hover {
      background: #f1f5f9;
      color: #1e293b;
    }

    .nav-item.active {
      background: #eff6ff;
      color: #2563eb;
    }

    .nav-item i, .nav-item .ti {
      font-size: 1.2rem;
    }

    .subnav {
      padding-left: 56px;
      margin: 4px 0;
    }

    .subnav a {
      display: block;
      padding: 8px 0;
      color: #64748b;
      text-decoration: none;
      font-size: 0.85rem;
      transition: color 0.2s;
    }

    .subnav a:hover, .subnav a.active {
      color: #2563eb;
    }

    .user-footer {
      margin-top: auto;
      padding: 20px 24px;
      border-top: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .user-avatar {
      width: 40px;
      height: 40px;
      background: linear-gradient(135deg, #3b82f6, #1d4ed8);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-weight: 700;
    }

    /* ========== MAIN CONTENT ========== */
    .main-content {
      margin-left: 280px;
      min-height: 100vh;
    }

    .topbar {
      background: #ffffff;
      border-bottom: 1px solid #e2e8f0;
      padding: 0 32px;
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 99;
    }

    .page-title h1 {
      font-size: 1.5rem;
      font-weight: 700;
      color: #0f172a;
      margin-bottom: 4px;
    }

    .page-title p {
      font-size: 0.8rem;
      color: #64748b;
      margin: 0;
    }

    /* ========== STATS CARDS ========== */
    .stats-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 20px;
      margin-bottom: 24px;
    }

    .stat-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
      border: 1px solid #e2e8f0;
      transition: all 0.2s;
    }

    .stat-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }

    .stat-icon {
      width: 48px;
      height: 48px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.3rem;
      margin-bottom: 16px;
    }

    .stat-value {
      font-size: 2rem;
      font-weight: 800;
      color: #0f172a;
      line-height: 1.2;
    }

    .stat-label {
      font-size: 0.7rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #64748b;
      font-weight: 600;
      margin-top: 6px;
    }

    .stat-title {
      font-size: 0.85rem;
      font-weight: 600;
      color: #334155;
      margin-bottom: 4px;
    }

    /* ========== DOUBLE CARDS ========== */
    .double-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      margin-bottom: 24px;
    }

    .info-card {
      background: #ffffff;
      border-radius: 20px;
      border: 1px solid #e2e8f0;
      overflow: hidden;
    }

    .card-header-custom {
      background: #f8fafc;
      padding: 16px 20px;
      border-bottom: 1px solid #e2e8f0;
      font-weight: 700;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #475569;
    }

    .card-body-custom {
      padding: 20px;
    }

    .service-item {
      margin-bottom: 16px;
    }

    .service-info {
      display: flex;
      justify-content: space-between;
      margin-bottom: 6px;
      font-size: 0.85rem;
    }

    .progress-bar-bg {
      height: 8px;
      background: #e2e8f0;
      border-radius: 10px;
      overflow: hidden;
    }

    .progress-fill {
      height: 100%;
      background: linear-gradient(90deg, #3b82f6, #60a5fa);
      border-radius: 10px;
      transition: width 0.5s;
    }

    .client-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 12px 0;
      border-bottom: 1px solid #e2e8f0;
    }

    .client-item:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }

    .client-rank {
      width: 32px;
      font-size: 1.2rem;
    }

    .client-name {
      flex: 1;
      font-weight: 500;
    }

    .client-count {
      background: #fef3c7;
      color: #d97706;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
    }

    /* ========== QUALITY CARDS ========== */
    .quality-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
      margin-bottom: 28px;
    }

    .quality-card {
      background: #ffffff;
      border-radius: 20px;
      padding: 20px;
      text-align: center;
      border: 1px solid #e2e8f0;
    }

    .quality-icon {
      width: 60px;
      height: 60px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 12px;
    }

    .quality-value {
      font-size: 2rem;
      font-weight: 800;
    }

    /* ========== FILTER BAR ========== */
    .filter-bar {
      background: #ffffff;
      border-radius: 16px;
      padding: 16px 20px;
      margin-bottom: 24px;
      border: 1px solid #e2e8f0;
    }

    .filter-form {
      display: flex;
      flex-wrap: wrap;
      gap: 12px;
      align-items: flex-end;
    }

    .filter-group {
      flex: 1;
      min-width: 140px;
    }

    .filter-group label {
      font-size: 0.7rem;
      font-weight: 600;
      text-transform: uppercase;
      color: #64748b;
      margin-bottom: 4px;
      display: block;
    }

    .filter-group input, .filter-group select {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      font-size: 0.85rem;
      background: #ffffff;
    }

    .btn-filter {
      background: #3b82f6;
      color: white;
      border: none;
      padding: 8px 20px;
      border-radius: 10px;
      font-weight: 600;
    }

    .btn-reset {
      background: #f1f5f9;
      color: #64748b;
      border: none;
      padding: 8px 16px;
      border-radius: 10px;
    }

    /* ========== TABLE ========== */
    .data-table {
      background: #ffffff;
      border-radius: 20px;
      border: 1px solid #e2e8f0;
      overflow: hidden;
    }

    .table-toolbar {
      padding: 16px 20px;
      border-bottom: 1px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 12px;
    }

    .search-box {
      position: relative;
    }

    .search-box input {
      padding: 8px 12px 8px 36px;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      width: 260px;
    }

    .search-box i {
      position: absolute;
      left: 12px;
      top: 50%;
      transform: translateY(-50%);
      color: #94a3b8;
    }

    .sort-buttons {
      display: flex;
      gap: 8px;
    }

    .sort-btn {
      padding: 6px 14px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: white;
      font-size: 0.8rem;
    }

    .sort-btn.active {
      background: #eff6ff;
      border-color: #3b82f6;
      color: #2563eb;
    }

    table {
      width: 100%;
    }

    th {
      padding: 14px 16px;
      text-align: left;
      font-size: 0.7rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      color: #64748b;
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
    }

    td {
      padding: 14px 16px;
      border-bottom: 1px solid #e2e8f0;
      font-size: 0.85rem;
    }

    .badge-status {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.7rem;
      font-weight: 600;
    }

    .badge-confirme { background: #d1fae5; color: #065f46; }
    .badge-attente { background: #fef3c7; color: #92400e; }
    .badge-annule { background: #fee2e2; color: #991b1b; }
    .badge-termine { background: #dbeafe; color: #1e40af; }

    .btn-edit {
      background: #eff6ff;
      color: #2563eb;
      border: none;
      padding: 5px 12px;
      border-radius: 8px;
      font-size: 0.75rem;
    }

    .btn-delete {
      background: #fef2f2;
      color: #dc2626;
      border: none;
      padding: 5px 10px;
      border-radius: 8px;
    }

    .pagination-area {
      padding: 16px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-top: 1px solid #e2e8f0;
    }

    .pagination {
      display: flex;
      gap: 6px;
    }

    .page-link {
      padding: 6px 12px;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      text-decoration: none;
      color: #64748b;
    }

    .page-link.active {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
    }

    .notification-btn {
      background: linear-gradient(135deg, #f59e0b, #ea580c);
      border: none;
      padding: 8px 18px;
      border-radius: 12px;
      color: white;
      display: flex;
      align-items: center;
      gap: 8px;
      font-weight: 600;
    }

    .notif-badge {
      background: #ef4444;
      border-radius: 20px;
      padding: 2px 8px;
      font-size: 0.7rem;
    }

    @media (max-width: 1200px) {
      .stats-grid, .quality-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .main-content {
        margin-left: 0;
      }
      .stats-grid, .quality-grid, .double-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sidebar-header">
    <div class="logo">
      <div class="logo-icon">AT</div>
      <div>
        <span class="logo-text">Entre AuTout</span>
        <span class="logo-badge">Admin</span>
      </div>
    </div>
  </div>

  <div class="nav-section">PRINCIPAL</div>
  <a href="admin.php" class="nav-item">
    <i class="ti ti-layout-dashboard"></i>
    <span>Tableau de bord</span>
  </a>

  <div class="nav-section">MODULES</div>
  <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/BackOffice/back.php" class="nav-item">
    <i class="ti ti-building"></i>
    <span>Garages & Services</span>
  </a>

  <a href="#" class="nav-item active">
    <i class="ti ti-car"></i>
    <span>Véhicules</span>
  </a>
  <div class="subnav">
    <a href="Gestion_voitureBackend.php">Gestion des véhicules</a>
    <a href="Gestion_rendezVousBackend.php" class="active">Gestion des rendez-vous</a>
  </div>

  <a href="#" class="nav-item">
    <i class="ti ti-tools"></i>
    <span>Entretien</span>
  </a>

  <a href="#" class="nav-item">
    <i class="ti ti-shopping-cart"></i>
    <span>Vente de Pièces</span>
  </a>

  <a href="#" class="nav-item">
    <i class="ti ti-users"></i>
    <span>Clients & Utilisateurs</span>
  </a>

  <div class="nav-section">SYSTÈME</div>
  <a href="#" class="nav-item">
    <i class="ti ti-settings"></i>
    <span>Configuration</span>
  </a>

  <div class="user-footer">
    <div class="user-avatar">A</div>
    <div>
      <div style="font-weight: 600; font-size: 0.85rem;">Administrateur</div>
      <div style="font-size: 0.7rem; color: #94a3b8;">admin@autout.tn</div>
    </div>
    <i class="ti ti-logout" style="margin-left: auto; color: #94a3b8; cursor: pointer;"></i>
  </div>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">
  <div class="topbar">
    <div class="page-title">
      <h1>Gestion des Rendez-vous</h1>
      <p><i class="fas fa-calendar-alt"></i> <?= date('l d F Y') ?></p>
    </div>
    <div style="display: flex; gap: 12px;">
      <button class="notification-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNotif">
        <i class="fas fa-bell"></i>
        <span>Notifications</span>
        <?php if ($nbNotifs > 0): ?>
          <span class="notif-badge"><?= $nbNotifs ?></span>
        <?php endif; ?>
      </button>
    </div>
  </div>

  <div style="padding: 28px;">
    
    <!-- STATS ROW 1 -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;"><i class="fas fa-calendar-alt"></i></div>
        <div class="stat-title">Total des rendez-vous</div>
        <div class="stat-value"><?= $stats_total ?></div>
        <div class="stat-label">TOUS STATUTS CONFONDUS</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #d1fae5; color: #10b981;"><i class="fas fa-check-circle"></i></div>
        <div class="stat-title">Rendez-vous confirmés</div>
        <div class="stat-value"><?= $stats_confirmes ?></div>
        <div class="stat-label"><?= $stats_total > 0 ? round(($stats_confirmes / $stats_total) * 100) : 0 ?>% du total</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;"><i class="fas fa-clock"></i></div>
        <div class="stat-title">En attente de validation</div>
        <div class="stat-value"><?= $stats_attente ?></div>
        <div class="stat-label"><?= $stats_total > 0 ? round(($stats_attente / $stats_total) * 100) : 0 ?>% du total</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #ede9fe; color: #8b5cf6;"><i class="fas fa-check-double"></i></div>
        <div class="stat-title">Rendez-vous terminés</div>
        <div class="stat-value"><?= $stats_termines ?></div>
        <div class="stat-label"><?= $stats_total > 0 ? round(($stats_termines / $stats_total) * 100) : 0 ?>% du total</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fee2e2; color: #ef4444;"><i class="fas fa-times-circle"></i></div>
        <div class="stat-title">Rendez-vous annulés</div>
        <div class="stat-value"><?= $stats_annules ?></div>
        <div class="stat-label"><?= $stats_total > 0 ? round(($stats_annules / $stats_total) * 100) : 0 ?>% du total</div>
      </div>
    </div>

    <!-- STATS ROW 2 -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="background: #cffafe; color: #06b6d4;"><i class="fas fa-calendar-week"></i></div>
        <div class="stat-title">Rendez-vous ce mois-ci</div>
        <div class="stat-value"><?= $stats_mois ?></div>
        <div class="stat-label">MOIS EN COURS</div>
        <div style="font-size: 0.7rem; margin-top: 6px;">✓ <?= $stats_mois_confirmes ?> confirmés</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #eff6ff; color: #3b82f6;"><i class="fas fa-hourglass-half"></i></div>
        <div class="stat-title">Rendez-vous à venir</div>
        <div class="stat-value"><?= $stats_a_venir ?></div>
        <div class="stat-label">À PARTIR D'AUJOURD'HUI</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;"><i class="fas fa-history"></i></div>
        <div class="stat-title">Rendez-vous passés</div>
        <div class="stat-value"><?= $stats_passes ?></div>
        <div class="stat-label">DÉJÀ EFFECTUÉS</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="background: #fce7f3; color: #ec4899;"><i class="fas fa-tools"></i></div>
        <div class="stat-title">Types de services</div>
        <div class="stat-value"><?= count($stats_services) ?></div>
        <div class="stat-label">SERVICES PROPOSÉS</div>
      </div>
    </div>

    <!-- DOUBLE CARDS -->
    <div class="double-grid">
      <div class="info-card">
        <div class="card-header-custom">
          <i class="fas fa-chart-bar"></i> RÉPARTITION PAR TYPE DE SERVICE
        </div>
        <div class="card-body-custom">
          <?php foreach ($stats_services as $service => $count): ?>
            <?php $pourcentage = $stats_total > 0 ? round(($count / $stats_total) * 100) : 0; ?>
            <div class="service-item">
              <div class="service-info">
                <span><?= ucfirst($service) ?></span>
                <span><?= $count ?> rendez-vous (<?= $pourcentage ?>%)</span>
              </div>
              <div class="progress-bar-bg">
                <div class="progress-fill" style="width: <?= $pourcentage ?>%"></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="info-card">
        <div class="card-header-custom">
          <i class="fas fa-trophy"></i> TOP 5 DES CLIENTS LES PLUS FIDÈLES
        </div>
        <div class="card-body-custom">
          <?php $rank = 1; foreach ($top_clients as $client => $count): ?>
            <div class="client-item">
              <div class="client-rank">
                <?php if ($rank == 1): ?>🥇<?php elseif ($rank == 2): ?>🥈<?php elseif ($rank == 3): ?>🥉<?php else: echo $rank; endif; ?>
              </div>
              <div class="client-name"><?= htmlspecialchars($client) ?></div>
              <div class="client-count"><?= $count ?> RDV</div>
            </div>
          <?php $rank++; endforeach; ?>
        </div>
      </div>
    </div>

    <!-- QUALITY CARDS -->
    <div class="quality-grid">
      <div class="quality-card">
        <div class="quality-icon" style="background: rgba(<?= $taux_satisfaction >= 80 ? '16,185,129' : ($taux_satisfaction >= 60 ? '59,130,246' : '239,68,68') ?>,0.1);">
          <i class="fas fa-star fa-2x" style="color: <?= $satisfaction_color ?>;"></i>
        </div>
        <div class="quality-value" style="color: <?= $satisfaction_color ?>;"><?= $taux_satisfaction ?>%</div>
        <div style="font-weight: 600; margin: 6px 0;">Taux de satisfaction</div>
        <div style="font-size: 0.75rem; color: #64748b;"><?= $satisfaction_message ?></div>
      </div>
      <div class="quality-card">
        <div class="quality-icon" style="background: #ede9fe;"><i class="fas fa-chart-line fa-2x" style="color: #8b5cf6;"></i></div>
        <div class="quality-value"><?= $stats_non_annules ?></div>
        <div>Rendez-vous maintenus</div>
        <div style="font-size: 0.7rem; color: #64748b;">HORS ANNULATIONS</div>
      </div>
      <div class="quality-card">
        <div class="quality-icon" style="background: #cffafe;"><i class="fas fa-percent fa-2x" style="color: #06b6d4;"></i></div>
        <div class="quality-value"><?= $stats_total > 0 ? round((($stats_total - $stats_annules) / $stats_total) * 100) : 0 ?>%</div>
        <div>Taux de réalisation</div>
        <div style="font-size: 0.7rem; color: #64748b;">RENDEZ-VOUS EFFECTUÉS</div>
      </div>
      <div class="quality-card">
        <div class="quality-icon" style="background: #d1fae5;"><i class="fas fa-handshake fa-2x" style="color: #10b981;"></i></div>
        <div class="quality-value"><?= $stats_total > 0 ? round((($stats_confirmes + $stats_termines) / $stats_total) * 100) : 0 ?>%</div>
        <div>Taux de conversion</div>
        <div style="font-size: 0.7rem; color: #64748b;">DEMANDES VALIDÉES</div>
      </div>
    </div>

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <form method="GET" class="filter-form">
        <div class="filter-group">
          <label>📅 Du</label>
          <input type="date" name="sf_from" value="<?= htmlspecialchars($sf_from ?? '') ?>">
        </div>
        <div class="filter-group">
          <label>📅 Au</label>
          <input type="date" name="sf_to" value="<?= htmlspecialchars($sf_to ?? '') ?>">
        </div>
        <div class="filter-group">
          <label>🔧 Type de service</label>
          <select name="sf_type">
            <option value="">— Tous —</option>
            <?php foreach ($types as $t): ?>
              <option value="<?= htmlspecialchars($t) ?>" <?= $sf_type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="filter-group">
          <label>🏷️ Statut</label>
          <select name="sf_statut">
            <option value="">— Tous —</option>
            <?php foreach ($statuts as $s): ?>
              <option value="<?= htmlspecialchars($s) ?>" <?= $sf_statut === $s ? 'selected' : '' ?>><?= htmlspecialchars($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" class="btn-filter"><i class="fas fa-filter"></i> Filtrer</button>
        <a href="Gestion_rendezVousBackend.php" class="btn-reset"><i class="fas fa-times"></i> Reset</a>
      </form>
    </div>

    <!-- DATA TABLE -->
    <div class="data-table">
      <div class="table-toolbar">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <form method="GET" style="display: inline;">
            <input type="text" name="q" placeholder="Nom client ou matricule..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit" style="display: none;"></button>
          </form>
        </div>
        <div class="sort-buttons">
          <a href="<?= buildQuery(['sort' => 'asc', 'page' => 1]) ?>" class="sort-btn <?= $sortOrder === 'asc' ? 'active' : '' ?>">📅 Plus ancien</a>
          <a href="<?= buildQuery(['sort' => 'desc', 'page' => 1]) ?>" class="sort-btn <?= $sortOrder === 'desc' ? 'active' : '' ?>">📅 Plus récent</a>
        </div>
        <div>
          <button onclick="exportExcel()" style="background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; padding: 6px 14px; border-radius: 8px;"><i class="fas fa-file-excel"></i> Excel</button>
          <button onclick="exportPDF()" style="background: #fff1f2; color: #991b1b; border: 1px solid #fecaca; padding: 6px 14px; border-radius: 8px; margin-left: 8px;"><i class="fas fa-file-pdf"></i> PDF</button>
          <a href="calendrier_rendezVous.php" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; padding: 6px 14px; border-radius: 8px; margin-left: 8px; text-decoration: none;"><i class="fas fa-calendar-alt"></i> Calendrier</a>
        </div>
      </div>

      <div class="table-responsive">
        <table>
          <thead>
            <tr>
              <th>DATE</th>
              <th>HEURE</th>
              <th>CLIENT</th>
              <th>MATRICULE</th>
              <th>SERVICE</th>
              <th>STATUT</th>
              <th>DESCRIPTION</th>
              <th>ACTION</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($listRDV)): ?>
              <?php foreach ($listRDV as $rdv):
                $statut = $rdv['statutRDV'] ?? 'en attente';
                $badgeClass = '';
                if ($statut === 'confirmé') $badgeClass = 'badge-confirme';
                elseif ($statut === 'annulé') $badgeClass = 'badge-annule';
                elseif ($statut === 'terminé') $badgeClass = 'badge-termine';
                else $badgeClass = 'badge-attente';
              ?>
                <tr>
                  <td><?= htmlspecialchars($rdv['dateRDV']) ?></td>
                  <td><strong><?= htmlspecialchars(substr($rdv['heureRDV'], 0, 5)) ?></strong></td>
                  <td><?= htmlspecialchars($rdv['nomclient']) ?></td>
                  <td><?= htmlspecialchars($rdv['matriculevoiture']) ?></td>
                  <td><?= htmlspecialchars($rdv['type_serviceRDV']) ?></td>
                  <td><span class="badge-status <?= $badgeClass ?>"><?= ucfirst($statut) ?></span></td>
                  <td><?= htmlspecialchars(substr($rdv['descriptionRDV'] ?? '', 0, 30)) ?></td>
                  <td>
                    <button class="btn-edit" data-bs-toggle="modal" data-bs-target="#modalStatut<?= $rdv['idRDV'] ?>">✏️ Modifier</button>
                    <a href="<?= buildQuery(['delete_id' => $rdv['idRDV']]) ?>" class="btn-delete" onclick="return confirm('Supprimer ce rendez-vous ?')">🗑️</a>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td colspan="8" style="text-align: center; padding: 60px;">Aucun rendez-vous trouvé.</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <?php if ($totalPages > 1): ?>
        <div class="pagination-area">
          <span>Page <?= $page ?> / <?= $totalPages ?></span>
          <div class="pagination">
            <a href="<?= buildQuery(['page' => $page - 1]) ?>" class="page-link <?= $page <= 1 ? 'disabled' : '' ?>">←</a>
            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
              <a href="<?= buildQuery(['page' => $p]) ?>" class="page-link <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
            <?php endfor; ?>
            <a href="<?= buildQuery(['page' => $page + 1]) ?>" class="page-link <?= $page >= $totalPages ? 'disabled' : '' ?>">→</a>
          </div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- MODALS -->
<?php foreach ($listRDV as $rdv): ?>
<div class="modal fade" id="modalStatut<?= $rdv['idRDV'] ?>" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius: 16px;">
      <form method="POST">
        <div class="modal-header" style="border-bottom: 1px solid #e2e8f0;">
          <h5 class="modal-title">Modifier le statut</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_rdv" value="<?= $rdv['idRDV'] ?>">
          <div style="background: #f8fafc; padding: 12px; border-radius: 12px; margin-bottom: 16px;">
            <div><strong>Client:</strong> <?= htmlspecialchars($rdv['nomclient']) ?></div>
            <div><strong>Date:</strong> <?= $rdv['dateRDV'] ?> à <?= substr($rdv['heureRDV'], 0, 5) ?></div>
            <div><strong>Service:</strong> <?= htmlspecialchars($rdv['type_serviceRDV']) ?></div>
          </div>
          <select name="nouveau_statut" class="form-select" required>
            <option value="en attente" <?= ($rdv['statutRDV'] ?? '') === 'en attente' ? 'selected' : '' ?>>En attente</option>
            <option value="confirmé" <?= ($rdv['statutRDV'] ?? '') === 'confirmé' ? 'selected' : '' ?>>Confirmé</option>
            <option value="annulé" <?= ($rdv['statutRDV'] ?? '') === 'annulé' ? 'selected' : '' ?>>Annulé</option>
            <option value="terminé" <?= ($rdv['statutRDV'] ?? '') === 'terminé' ? 'selected' : '' ?>>Terminé</option>
          </select>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #e2e8f0;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" name="update_status" class="btn btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php endforeach; ?>

<!-- OFFCANVAS NOTIFICATIONS -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNotif">
  <div class="offcanvas-header">
    <h5 class="offcanvas-title"><i class="fas fa-bell"></i> Prochains Rendez-vous</h5>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
  </div>
  <div class="offcanvas-body">
    <?php if (empty($notifications)): ?>
      <div class="text-center py-5">Aucun rendez-vous imminent</div>
    <?php else: ?>
      <div class="mb-3">🔔 <strong><?= $nbNotifs ?></strong> rendez-vous à venir</div>
      <?php foreach ($notifications as $n): ?>
        <div style="background: #f8fafc; border-radius: 12px; padding: 12px; margin-bottom: 12px;">
          <div><strong><?= htmlspecialchars($n['nomclient']) ?></strong></div>
          <div><?= date('d/m/Y', strtotime($n['dateRDV'])) ?> à <?= substr($n['heureRDV'], 0, 5) ?></div>
          <div><?= htmlspecialchars($n['type_serviceRDV']) ?> - <?= htmlspecialchars($n['matriculevoiture']) ?></div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
  const allRDV = <?= json_encode(array_map(function($r) {
      return [$r['dateRDV'], substr($r['heureRDV'], 0, 5), $r['nomclient'], $r['matriculevoiture'], $r['type_serviceRDV'], $r['statutRDV'] ?? 'en attente', $r['descriptionRDV'] ?? ''];
  }, $allRDV)) ?>;

  function exportExcel() {
    let html = '<html><head><meta charset="UTF-8"><title>Rendez-vous</title></head><body>';
    html += '<h2>Planning des Rendez-vous</h2><table border="1"><tr><th>Date</th><th>Heure</th><th>Client</th><th>Matricule</th><th>Service</th><th>Statut</th><th>Description</th></tr>';
    allRDV.forEach(r => { html += '<tr>' + r.map(v => `<td>${String(v).replace(/</g, '&lt;')}</td>`).join('') + '</tr>'; });
    html += '</table></body></html>';
    const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    const a = document.createElement('a');
    a.href = URL.createObjectURL(blob);
    a.download = 'rendez-vous.xls';
    a.click();
    URL.revokeObjectURL(a.href);
  }

  function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'landscape' });
    doc.autoTable({ head: [['Date', 'Heure', 'Client', 'Matricule', 'Service', 'Statut', 'Description']], body: allRDV });
    doc.save('rendez-vous.pdf');
  }

  window.exportExcel = exportExcel;
  window.exportPDF = exportPDF;
</script>

</body>
</html>