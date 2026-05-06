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

// ══════════════════════════════════════════════════════════════════════
//  NOTIFICATIONS — RDV à venir (fenêtre glissante 48h pour fiabilité)
//  100% PHP : indépendant du fuseau horaire MySQL
// ══════════════════════════════════════════════════════════════════════

$allRdvNotifSQL = "
    SELECT r.idRDV, r.dateRDV, r.heureRDV, r.type_serviceRDV,
           r.statutRDV, r.descriptionRDV, r.idclientRDV, r.idVehicule,
           COALESCE(u.nomclient,        CONCAT('Client #', r.idclientRDV)) AS nomclient,
           COALESCE(v.matriculevoiture, CONCAT('VEH-', r.idVehicule))      AS matriculevoiture
    FROM rendezvous r
    LEFT JOIN user     u ON u.id_client  = r.idclientRDV
    LEFT JOIN vehicule v ON v.idVehicule = r.idVehicule
    ORDER BY r.dateRDV ASC, r.heureRDV ASC
";
$allRdvNotifStmt = $pdo->prepare($allRdvNotifSQL);
$allRdvNotifStmt->execute();
$allRdvNotif = $allRdvNotifStmt->fetchAll(PDO::FETCH_ASSOC);

// Fenêtre : MAINTENANT → +48h (couvre les décalages d'horloge éventuels)
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

// Statistiques globales
$stats_total = count($allRDV);

// Statistiques par statut
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

// Statistiques par type de service
$stats_services = [];
foreach ($allRDV as $r) {
    $service = $r['type_serviceRDV'] ?? 'Autre';
    if (!isset($stats_services[$service])) {
        $stats_services[$service] = 0;
    }
    $stats_services[$service]++;
}
arsort($stats_services);

// Rendez-vous du mois en cours
$mois_courant = date('Y-m');
$stats_mois = 0;
$stats_mois_confirmes = 0;
foreach ($allRDV as $r) {
    if (substr($r['dateRDV'], 0, 7) === $mois_courant) {
        $stats_mois++;
        if (($r['statutRDV'] ?? 'en attente') === 'confirmé') {
            $stats_mois_confirmes++;
        }
    }
}

// Rendez-vous à venir
$stats_a_venir = 0;
$aujourdhui = date('Y-m-d');
foreach ($allRDV as $r) {
    if ($r['dateRDV'] >= $aujourdhui && ($r['statutRDV'] ?? 'en attente') !== 'annulé') {
        $stats_a_venir++;
    }
}

// Rendez-vous passés
$stats_passes = 0;
foreach ($allRDV as $r) {
    if ($r['dateRDV'] < $aujourdhui && ($r['statutRDV'] ?? 'en attente') !== 'annulé') {
        $stats_passes++;
    }
}

// Top clients
$top_clients = [];
foreach ($allRDV as $r) {
    $client = $r['nomclient'];
    if (!isset($top_clients[$client])) {
        $top_clients[$client] = 0;
    }
    $top_clients[$client]++;
}
arsort($top_clients);
$top_clients = array_slice($top_clients, 0, 5);

// Taux de satisfaction (basé sur les rendez-vous terminés vs total non annulés)
$stats_non_annules = $stats_total - $stats_annules;
$taux_satisfaction = $stats_non_annules > 0 ? round(($stats_termines / $stats_non_annules) * 100) : 0;

// Couleur du taux de satisfaction
$satisfaction_color = '#f59e0b';
$satisfaction_message = 'À améliorer';
if ($taux_satisfaction >= 80) {
    $satisfaction_color = '#10b981';
    $satisfaction_message = 'Excellent';
} elseif ($taux_satisfaction >= 60) {
    $satisfaction_color = '#3b82f6';
    $satisfaction_message = 'Bon';
} elseif ($taux_satisfaction >= 40) {
    $satisfaction_color = '#f59e0b';
    $satisfaction_message = 'Moyen';
} else {
    $satisfaction_color = '#ef4444';
    $satisfaction_message = 'À améliorer';
}

// ── PAGINATION ────────────────────────────────────────────────────────
$perPage    = 10;
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
    }
    .fb-group input:focus,
    .fb-group select:focus { outline: none; border-color: #38bdf8; }
    .btn-fb {
      padding: 6px 16px; border-radius: 7px; border: none;
      background: #0ea5e9; color: #fff; font-size: 0.8rem;
      cursor: pointer;
    }
    .btn-fb-reset {
      padding: 6px 12px; border-radius: 7px; border: 1px solid #1e293b;
      background: transparent; color: #64748b; font-size: 0.78rem;
      text-decoration: none;
    }
    .btn-fb-reset:hover { color: #ef4444; border-color: #ef4444; }

    .tbl-toolbar {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      align-items: center;
      padding: 14px 16px;
      border-bottom: 1px solid #1e293b;
    }
    .search-wrap { position: relative; flex: 1 1 200px; max-width: 300px; }
    .search-wrap .ico { position: absolute; left: 9px; top: 50%; transform: translateY(-50%); color: #475569; }
    .search-wrap input[name="q"] {
      width: 100%; padding: 6px 10px 6px 28px;
      background: #0b111e; border: 1px solid #1e293b;
      border-radius: 8px; color: #e2e8f0;
    }
    .btn-srch {
      padding: 6px 13px; border-radius: 8px; border: 1px solid #1e293b;
      background: #0b111e; color: #94a3b8;
      cursor: pointer;
    }
    .btn-srch:hover { border-color: #38bdf8; color: #38bdf8; }

    .sort-group { display: flex; gap: 5px; }
    .btn-sort {
      padding: 6px 12px; border-radius: 8px; border: 1px solid #1e293b;
      background: #0b111e; color: #94a3b8;
      text-decoration: none;
    }
    .btn-sort.active { background: rgba(56,189,248,.1); border-color: #38bdf8; color: #38bdf8; }

    .tbl-pagination {
      display: flex; align-items: center; justify-content: space-between;
      padding: 13px 16px; border-top: 1px solid #1e293b;
    }
    .pg-btn {
      display: inline-flex; align-items: center; justify-content: center;
      min-width: 32px; height: 32px; padding: 0 9px; border-radius: 7px;
      border: 1px solid #1e293b; background: #0b111e; color: #94a3b8;
      text-decoration: none;
    }
    .pg-btn.active { background: #38bdf8; border-color: #38bdf8; color: #0b111e; }
    .pg-btn.disabled { opacity: .3; pointer-events: none; }

    /* ========== STATISTIQUES STYLES MODERNES AVEC TITRES CLAIRS ========== */
    .stats-container {
        margin-bottom: 30px;
    }

    .stats-header {
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #1e293b;
    }
    .stats-header h3 {
        color: #e2e8f0;
        font-size: 1.1rem;
        font-weight: 600;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .stats-header h3 i {
        color: #38bdf8;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: linear-gradient(135deg, #111c2d 0%, #0f172a 100%);
        border: 1px solid #1e293b;
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        border-color: #38bdf8;
        box-shadow: 0 8px 25px rgba(56, 189, 248, 0.1);
    }

    .stat-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #38bdf8, #3b82f6);
        opacity: 0;
        transition: opacity 0.3s;
    }

    .stat-card:hover::before {
        opacity: 1;
    }

    .stat-card-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        margin-bottom: 15px;
    }

    .stat-card-icon.blue { background: rgba(56, 189, 248, 0.15); color: #38bdf8; }
    .stat-card-icon.green { background: rgba(16, 185, 129, 0.15); color: #10b981; }
    .stat-card-icon.orange { background: rgba(245, 158, 11, 0.15); color: #f59e0b; }
    .stat-card-icon.red { background: rgba(239, 68, 68, 0.15); color: #ef4444; }
    .stat-card-icon.purple { background: rgba(139, 92, 246, 0.15); color: #8b5cf6; }
    .stat-card-icon.cyan { background: rgba(6, 182, 212, 0.15); color: #06b6d4; }
    .stat-card-icon.pink { background: rgba(236, 72, 153, 0.15); color: #ec4899; }

    .stat-card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #ffffff;
        line-height: 1.2;
        margin-bottom: 5px;
    }

    .stat-card-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .stat-card-title {
        font-size: 0.85rem;
        font-weight: 600;
        color: #e2e8f0;
        margin-bottom: 8px;
    }

    .stat-card-percent {
        font-size: 0.75rem;
        color: #38bdf8;
        font-weight: 500;
    }

    .stat-card-sub {
        font-size: 0.65rem;
        color: #475569;
        margin-top: 8px;
    }

    .stats-double {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        margin-bottom: 25px;
    }

    .stats-box {
        background: #111c2d;
        border: 1px solid #1e293b;
        border-radius: 16px;
        overflow: hidden;
    }

    .stats-box-header {
        background: #0f172a;
        padding: 14px 20px;
        border-bottom: 1px solid #1e293b;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stats-box-header i {
        color: #38bdf8;
        font-size: 1rem;
    }

    .stats-box-header span {
        color: #e2e8f0;
    }

    .stats-box-body {
        padding: 20px;
    }

    .service-item {
        margin-bottom: 18px;
    }

    .service-item:last-child {
        margin-bottom: 0;
    }

    .service-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .service-name {
        color: #e2e8f0;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .service-count {
        color: #38bdf8;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .progress-bar-bg {
        height: 8px;
        background: #1e293b;
        border-radius: 4px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 4px;
        transition: width 0.5s ease;
        background: linear-gradient(90deg, #38bdf8, #3b82f6);
    }

    .client-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #1e293b;
    }

    .client-item:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .client-item:first-child {
        padding-top: 0;
    }

    .client-rank {
        width: 32px;
        font-size: 1.2rem;
        text-align: center;
    }

    .client-name {
        flex: 1;
        color: #e2e8f0;
        font-size: 0.85rem;
        font-weight: 500;
    }

    .client-badge {
        background: rgba(245, 158, 11, 0.15);
        color: #f59e0b;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }

    .satisfaction-card {
        background: linear-gradient(135deg, #0f172a 0%, #111c2d 100%);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        border: 1px solid #1e293b;
    }

    .satisfaction-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    .satisfaction-message {
        font-size: 0.8rem;
        font-weight: 500;
    }

    .notification-btn {
        background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);
        border: none;
        padding: 8px 18px;
        border-radius: 10px;
        color: white;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
        position: relative;
    }

    .notification-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(234, 88, 12, 0.4);
    }

    .notif-badge {
        background: #ef4444;
        border-radius: 20px;
        padding: 2px 8px;
        font-size: 0.7rem;
        font-weight: bold;
        animation: pulse 2s infinite;
    }

    .offcanvas {
        background-color: #111c2d !important;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.1); opacity: 0.8; }
        100% { transform: scale(1); opacity: 1; }
    }

    @keyframes ring {
        0% { transform: rotate(0deg); }
        25% { transform: rotate(15deg); }
        50% { transform: rotate(-15deg); }
        75% { transform: rotate(10deg); }
        100% { transform: rotate(0deg); }
    }

    .ringing {
        animation: ring 0.5s ease-in-out 3;
    }

    @media (max-width: 768px) {
        .stats-double {
            grid-template-columns: 1fr;
        }
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }
        .stat-card-value {
            font-size: 1.5rem;
        }
        .stat-card-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }
    }
  </style>

  <!-- jsPDF + AutoTable pour export PDF côté client -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>
  <!-- jQuery -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
  <div class="wrapper">
    <?php include("../../partials/sidebar-collapse.html"); ?>

    <div id="content" class="main-content">
      <?php include("../../partials/topbar-second.html"); ?>

      <div class="container-fluid mt-4 px-4">

        <!-- En-tête avec titre principal -->
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="text-white mb-1">
              <i class="fas fa-calendar-alt me-2 text-primary"></i>
              Planning des Rendez-vous
            </h4>
            <p class="text-muted small mb-0">
              <i class="fas fa-chart-line me-1"></i>
              <?= $total ?> rendez-vous trouvé<?= $total > 1 ? 's' : '' ?>
              <?php if ($search !== '' || $sf_from || $sf_to || $sf_type || $sf_statut): ?>
                · <a href="Gestion_rendezVousBackend.php" class="text-muted small">✕ Effacer les filtres</a>
              <?php endif; ?>
            </p>
          </div>

          <div class="d-flex gap-2 flex-wrap align-items-center">
            <a href="Gestion_rendezVousBackend.php" class="btn btn-outline-secondary btn-sm px-3">
              <i class="fas fa-sync-alt me-1"></i> Actualiser
            </a>
            <a href="calendrier_rendezVous.php" class="btn btn-sm px-3" style="background:rgba(56,189,248,.12);color:#38bdf8;border:1px solid rgba(56,189,248,.3);border-radius:8px;">
              <i class="fas fa-calendar-alt me-1"></i> Voir Calendrier
            </a>
            <button onclick="exportExcel()" class="btn-export btn-export-excel" style="background:rgba(16,185,129,.1);color:#10b981;border:1px solid rgba(16,185,129,.3);padding:6px 14px;border-radius:8px;">
              <i class="fas fa-file-excel me-1"></i> Excel
            </button>
            <button onclick="exportPDF()" class="btn-export btn-export-pdf" style="background:rgba(239,68,68,.1);color:#ef4444;border:1px solid rgba(239,68,68,.3);padding:6px 14px;border-radius:8px;">
              <i class="fas fa-file-pdf me-1"></i> PDF
            </button>
            <button class="notification-btn" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNotif">
              <i class="fas fa-bell"></i>
              <span>Notifications</span>
              <?php if ($nbNotifs > 0): ?>
                <span class="notif-badge"><?= $nbNotifs ?></span>
              <?php endif; ?>
            </button>
          </div>
        </div>

        <!-- ════════════════════════════════════════════════════════════
             STATISTIQUES AVEC TITRES CLAIRS À CÔTÉ DES ICÔNES
        ════════════════════════════════════════════════════════════ -->
        
        <div class="stats-container">
            
            <!-- Titre de la section -->
            <div class="stats-header">
                <h3>
                    <i class="fas fa-chart-pie"></i>
                    <span>📊 TABLEAU DE BORD STATISTIQUE</span>
                    <span class="badge bg-primary ms-2" style="font-size: 0.7rem;">
                        <i class="fas fa-calendar me-1"></i><?= date('F Y') ?>
                    </span>
                </h3>
            </div>

            <!-- Première ligne : Vue d'ensemble des statuts -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-card-title">Total des rendez-vous</div>
                    <div class="stat-card-value"><?= $stats_total ?></div>
                    <div class="stat-card-label">TOUS STATUTS CONFONDUS</div>
                    <div class="stat-card-percent">
                        <i class="fas fa-chart-simple me-1"></i>Vue d'ensemble
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-card-title">Rendez-vous confirmés</div>
                    <div class="stat-card-value"><?= $stats_confirmes ?></div>
                    <div class="stat-card-label">STATUT CONFIRMÉ</div>
                    <div class="stat-card-percent">
                        <i class="fas fa-percent me-1"></i><?= $stats_total > 0 ? round(($stats_confirmes / $stats_total) * 100) : 0 ?>% du total
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-card-title">En attente de validation</div>
                    <div class="stat-card-value"><?= $stats_attente ?></div>
                    <div class="stat-card-label">STATUT EN ATTENTE</div>
                    <div class="stat-card-percent">
                        <i class="fas fa-percent me-1"></i><?= $stats_total > 0 ? round(($stats_attente / $stats_total) * 100) : 0 ?>% du total
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon purple">
                        <i class="fas fa-check-double"></i>
                    </div>
                    <div class="stat-card-title">Rendez-vous terminés</div>
                    <div class="stat-card-value"><?= $stats_termines ?></div>
                    <div class="stat-card-label">STATUT TERMINÉ</div>
                    <div class="stat-card-percent">
                        <i class="fas fa-percent me-1"></i><?= $stats_total > 0 ? round(($stats_termines / $stats_total) * 100) : 0 ?>% du total
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon red">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-card-title">Rendez-vous annulés</div>
                    <div class="stat-card-value"><?= $stats_annules ?></div>
                    <div class="stat-card-label">STATUT ANNULÉ</div>
                    <div class="stat-card-percent">
                        <i class="fas fa-percent me-1"></i><?= $stats_total > 0 ? round(($stats_annules / $stats_total) * 100) : 0 ?>% du total
                    </div>
                </div>
            </div>

            <!-- Deuxième ligne : Analyses temporelles -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-icon cyan">
                        <i class="fas fa-calendar-week"></i>
                    </div>
                    <div class="stat-card-title">Rendez-vous ce mois-ci</div>
                    <div class="stat-card-value"><?= $stats_mois ?></div>
                    <div class="stat-card-label">MOIS EN COURS</div>
                    <div class="stat-card-sub">
                        <i class="fas fa-check-circle me-1 text-success"></i><?= $stats_mois_confirmes ?> confirmés
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon blue">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                    <div class="stat-card-title">Rendez-vous à venir</div>
                    <div class="stat-card-value"><?= $stats_a_venir ?></div>
                    <div class="stat-card-label">À PARTIR D'AUJOURD'HUI</div>
                    <div class="stat-card-sub">
                        <i class="fas fa-calendar me-1"></i>Futurs rendez-vous
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon orange">
                        <i class="fas fa-history"></i>
                    </div>
                    <div class="stat-card-title">Rendez-vous passés</div>
                    <div class="stat-card-value"><?= $stats_passes ?></div>
                    <div class="stat-card-label">DÉJÀ EFFECTUÉS</div>
                    <div class="stat-card-sub">
                        <i class="fas fa-check-double me-1"></i>Historique
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon pink">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-card-title">Types de services</div>
                    <div class="stat-card-value"><?= count($stats_services) ?></div>
                    <div class="stat-card-label">SERVICES PROPOSÉS</div>
                    <div class="stat-card-sub">
                        <i class="fas fa-wrench me-1"></i>Catégories différentes
                    </div>
                </div>
            </div>

            <!-- Troisième ligne : Analyse détaillée -->
            <div class="stats-double">
                
                <div class="stats-box">
                    <div class="stats-box-header">
                        <i class="fas fa-chart-bar"></i>
                        <span>📈 RÉPARTITION PAR TYPE DE SERVICE</span>
                    </div>
                    <div class="stats-box-body">
                        <?php if (!empty($stats_services)): ?>
                            <?php foreach ($stats_services as $service => $count): ?>
                                <?php $pourcentage = $stats_total > 0 ? round(($count / $stats_total) * 100) : 0; ?>
                                <div class="service-item">
                                    <div class="service-info">
                                        <span class="service-name">
                                            <i class="fas fa-wrench me-1 text-muted"></i>
                                            <?= ucfirst(htmlspecialchars($service)) ?>
                                        </span>
                                        <span class="service-count">
                                            <?= $count ?> rendez-vous <span class="text-muted">(<?= $pourcentage ?>%)</span>
                                        </span>
                                    </div>
                                    <div class="progress-bar-bg">
                                        <div class="progress-fill" style="width: <?= $pourcentage ?>%"></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-chart-pie fa-2x mb-2 d-block"></i>
                                Aucune donnée disponible
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="stats-box">
                    <div class="stats-box-header">
                        <i class="fas fa-trophy"></i>
                        <span>🏆 TOP 5 DES CLIENTS LES PLUS FIDÈLES</span>
                    </div>
                    <div class="stats-box-body">
                        <?php if (!empty($top_clients)): ?>
                            <?php $rank = 1; ?>
                            <?php foreach ($top_clients as $client => $count): ?>
                                <div class="client-item">
                                    <div class="client-rank">
                                        <?php if ($rank == 1): ?>
                                            <span title="1er - Plus fidèle">🥇</span>
                                        <?php elseif ($rank == 2): ?>
                                            <span title="2ème">🥈</span>
                                        <?php elseif ($rank == 3): ?>
                                            <span title="3ème">🥉</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary"><?= $rank ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="client-name">
                                        <i class="fas fa-user me-1 text-muted"></i>
                                        <?= htmlspecialchars(substr($client, 0, 30)) ?>
                                    </div>
                                    <div class="client-badge">
                                        <i class="fas fa-calendar-check me-1"></i><?= $count ?> RDV
                                    </div>
                                </div>
                                <?php $rank++; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="text-center text-muted py-4">
                                <i class="fas fa-users fa-2x mb-2 d-block"></i>
                                Aucun client pour le moment
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Quatrième ligne : Indicateurs de qualité -->
            <div class="stats-grid">
                <div class="satisfaction-card">
                    <div class="stat-card-icon" style="margin: 0 auto 15px auto; background: rgba(<?= $taux_satisfaction >= 80 ? '16,185,129' : ($taux_satisfaction >= 60 ? '59,130,246' : ($taux_satisfaction >= 40 ? '245,158,11' : '239,68,68')) ?>,0.15); width: 60px; height: 60px;">
                        <i class="fas fa-star fa-2x" style="color: <?= $satisfaction_color ?>;"></i>
                    </div>
                    <div class="stat-card-title">Taux de satisfaction client</div>
                    <div class="satisfaction-value" style="color: <?= $satisfaction_color ?>;">
                        <?= $taux_satisfaction ?>%
                    </div>
                    <div class="satisfaction-message" style="color: <?= $satisfaction_color ?>;">
                        <i class="fas fa-<?= $taux_satisfaction >= 80 ? 'smile' : ($taux_satisfaction >= 60 ? 'meh' : 'frown') ?> me-1"></i>
                        <?= $satisfaction_message ?>
                    </div>
                    <div class="stat-card-sub mt-2">
                        <i class="fas fa-chart-line me-1"></i>
                        Basé sur <?= $stats_termines ?> rendez-vous terminés
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon purple">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-card-title">Rendez-vous maintenus</div>
                    <div class="stat-card-value"><?= $stats_non_annules ?></div>
                    <div class="stat-card-label">HORS ANNULATIONS</div>
                    <div class="stat-card-sub">
                        <i class="fas fa-calendar-check me-1"></i>
                        Rendez-vous réalisés ou à venir
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-card-icon cyan">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div class="stat-card-title">Taux de réalisation</div>
                    <div class="stat-card-value">
                        <?= $stats_total > 0 ? round((($stats_total - $stats_annules) / $stats_total) * 100) : 0 ?>%
                    </div>
                    <div class="stat-card-label">RENDEZ-VOUS EFFECTUÉS</div>
                    <div class="stat-card-sub">
                        <i class="fas fa-chart-simple me-1"></i>
                        Taux de succès global
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-card-icon green">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-card-title">Taux de conversion</div>
                    <div class="stat-card-value">
                        <?= $stats_confirmes + $stats_termines > 0 ? round((($stats_confirmes + $stats_termines) / $stats_total) * 100) : 0 ?>%
                    </div>
                    <div class="stat-card-label">DEMANDES VALIDÉES</div>
                    <div class="stat-card-sub">
                        <i class="fas fa-thumbs-up me-1"></i>
                        Confirmés + Terminés
                    </div>
                </div>
            </div>
        </div>

        <!-- Barre de filtres -->
        <div class="filter-bar">
          <form method="GET" action="">
            <?php if ($sortOrder !== 'desc'): ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sortOrder) ?>">
            <?php endif; ?>
            <?php if ($search !== ''): ?>
            <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
            <?php endif; ?>
            <input type="hidden" name="page" value="1">

            <div class="fb-group">
              <label><i class="fas fa-calendar-alt me-1"></i> Du</label>
              <input type="date" name="sf_from" value="<?= htmlspecialchars($sf_from ?? '') ?>">
            </div>
            <div class="fb-group">
              <label><i class="fas fa-calendar-alt me-1"></i> Au</label>
              <input type="date" name="sf_to" value="<?= htmlspecialchars($sf_to ?? '') ?>">
            </div>
            <div class="fb-group">
              <label><i class="fas fa-tools me-1"></i> Type de service</label>
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
              <label><i class="fas fa-tag me-1"></i> Statut</label>
              <select name="sf_statut">
                <option value="">— Tous —</option>
                <?php foreach ($statuts as $s): ?>
                <option value="<?= htmlspecialchars($s) ?>" <?= $sf_statut === $s ? 'selected' : '' ?>>
                  <?= htmlspecialchars($s) ?>
                </option>
                <?php endforeach; ?>
              </select>
            </div>
            <button type="submit" class="btn-fb"><i class="fas fa-filter me-1"></i> Filtrer</button>
            <a href="Gestion_rendezVousBackend.php" class="btn-fb-reset"><i class="fas fa-times me-1"></i> Reset</a>
          </form>
        </div>

        <!-- Tableau principal -->
        <div class="card">

          <form method="GET" action="" style="display:contents">
            <?php if ($sf_from):   ?><input type="hidden" name="sf_from"   value="<?= htmlspecialchars($sf_from) ?>"><?php endif; ?>
            <?php if ($sf_to):     ?><input type="hidden" name="sf_to"     value="<?= htmlspecialchars($sf_to) ?>"><?php endif; ?>
            <?php if ($sf_type):   ?><input type="hidden" name="sf_type"   value="<?= htmlspecialchars($sf_type) ?>"><?php endif; ?>
            <?php if ($sf_statut): ?><input type="hidden" name="sf_statut" value="<?= htmlspecialchars($sf_statut) ?>"><?php endif; ?>
            <input type="hidden" name="sort" value="<?= htmlspecialchars($sortOrder) ?>">
            <input type="hidden" name="page" value="1">

            <div class="tbl-toolbar">
              <div class="search-wrap">
                <span class="ico"><i class="fas fa-search"></i></span>
                <input type="text" name="q"
                       placeholder="Nom client ou matricule…"
                       value="<?= htmlspecialchars($search) ?>"
                       autocomplete="off">
              </div>
              <button type="submit" class="btn-srch"><i class="fas fa-search me-1"></i> Chercher</button>

              <div class="sort-group">
                <a href="<?= buildQuery(['sort' => 'asc', 'page' => 1]) ?>"
                   class="btn-sort <?= $sortOrder === 'asc' ? 'active' : '' ?>">
                  <i class="fas fa-arrow-up me-1"></i> Plus ancien
                </a>
                <a href="<?= buildQuery(['sort' => 'desc', 'page' => 1]) ?>"
                   class="btn-sort <?= $sortOrder === 'desc' ? 'active' : '' ?>">
                  <i class="fas fa-arrow-down me-1"></i> Plus récent
                </a>
              </div>

              <span class="tbl-count ms-auto">
                <i class="fas fa-list me-1"></i>
                <span><?= count($listRDV) ?></span> / <?= $total ?>
              </span>
            </div>
          </form>

          <div class="table-responsive">
            <table class="table align-middle mb-0" id="rdvTable">
              <thead>
                <tr>
                  <th class="ps-4"><i class="fas fa-calendar-day me-1"></i> DATE</th>
                  <th><i class="fas fa-clock me-1"></i> HEURE</th>
                  <th><i class="fas fa-user me-1"></i> CLIENT</th>
                  <th><i class="fas fa-car me-1"></i> MATRICULE</th>
                  <th><i class="fas fa-tools me-1"></i> SERVICE</th>
                  <th><i class="fas fa-tag me-1"></i> STATUT</th>
                  <th><i class="fas fa-align-left me-1"></i> DESCRIPTION</th>
                  <th class="text-end pe-4"><i class="fas fa-cog me-1"></i> ACTION</th>
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
                    
                    $dateRdvFull = new DateTime($rdv['dateRDV'] . ' ' . $rdv['heureRDV']);
                    $maintenant  = new DateTime();
                    $diffHeures = ($dateRdvFull->getTimestamp() - $maintenant->getTimestamp()) / 3600;
                    $afficherRappel = ($diffHeures <= 24 && $diffHeures > 0 && $rdv['statutRDV'] !== 'annulé');
                  ?>
                  <tr>
                    <td class="ps-4"><?= htmlspecialchars($rdv['dateRDV']) ?></td>
                    <td style="color:#38bdf8;font-weight:600">
                      <?= htmlspecialchars(substr($rdv['heureRDV'], 0, 5)) ?>
                      <?php if ($afficherRappel): ?>
                        <i class="fas fa-bell text-warning anim-pulse ms-1" title="Rendez-vous imminent (moins de 24h)"></i>
                      <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($rdv['nomclient']) ?></td>
                    <td><span class="badge bg-secondary"><?= htmlspecialchars($rdv['matriculevoiture']) ?></span></td>
                    <td><?= htmlspecialchars($rdv['type_serviceRDV']) ?></td>
                    <td>
                      <span class="badge-statut <?= $badgeClass ?>">
                        <i class="fas <?= $statut === 'confirmé' ? 'fa-check-circle' : ($statut === 'annulé' ? 'fa-times-circle' : 'fa-clock') ?> me-1"></i>
                        <?= ucfirst($statut) ?>
                      </span>
                    </td>
                    <td><?= htmlspecialchars(substr($rdv['descriptionRDV'] ?? '', 0, 30)) ?></td>
                    <td class="text-end pe-4">
                      <div class="action-buttons">
                        <button type="button" class="btn-modifier"
                                data-bs-toggle="modal"
                                data-bs-target="#modalStatut<?= $rdv['idRDV'] ?>">
                          <i class="fas fa-edit me-1"></i> Modifier
                        </button>
                        <a href="<?= buildQuery(['delete_id' => $rdv['idRDV']]) ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('Supprimer ce rendez-vous ?')">
                          <i class="fas fa-trash-alt"></i>
                        </a>
                      </div>
                    </td>
                  </tr>

                  <!-- Modal modification statut -->
                  <div class="modal fade modal-custom" id="modalStatut<?= $rdv['idRDV'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title"><i class="fas fa-edit me-2 text-info"></i> Modifier le statut</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form method="POST">
                          <?php foreach ($_GET as $gk => $gv): ?>
                            <input type="hidden" name="<?= htmlspecialchars($gk) ?>" value="<?= htmlspecialchars($gv) ?>">
                          <?php endforeach; ?>
                          <div class="modal-body">
                            <input type="hidden" name="id_rdv" value="<?= $rdv['idRDV'] ?>">
                            
                            <div class="info-text mb-3">
                              <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="fas fa-calendar me-1"></i> Date :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['dateRDV']) ?> à <?= htmlspecialchars(substr($rdv['heureRDV'], 0, 5)) ?></div>
                              </div>
                              <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="fas fa-user me-1"></i> Client :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['nomclient']) ?></div>
                              </div>
                              <div class="row mb-2">
                                <div class="col-5 text-muted"><i class="fas fa-car me-1"></i> Véhicule :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['matriculevoiture']) ?></div>
                              </div>
                              <div class="row">
                                <div class="col-5 text-muted"><i class="fas fa-tools me-1"></i> Service :</div>
                                <div class="col-7 text-white"><?= htmlspecialchars($rdv['type_serviceRDV']) ?></div>
                              </div>
                            </div>

                            <label class="form-label-modal d-block mb-2">
                              <i class="fas fa-tag me-1"></i> Nouveau statut :
                            </label>
                            <select name="nouveau_statut" class="form-select-modal w-100" required>
                              <option value="en attente" <?= $statut === 'en attente' ? 'selected' : '' ?>>En attente</option>
                              <option value="confirmé" <?= $statut === 'confirmé' ? 'selected' : '' ?>>Confirmé</option>
                              <option value="annulé" <?= $statut === 'annulé' ? 'selected' : '' ?>>Annulé</option>
                              <option value="terminé" <?= $statut === 'terminé' ? 'selected' : '' ?>>Terminé</option>
                            </select>

                            <div class="mt-3 pt-2 text-center">
                              <small class="text-muted">
                                Statut actuel :
                                <span class="badge-statut <?= $badgeClass ?>">
                                  <i class="fas <?= $statut === 'confirmé' ? 'fa-check-circle' : ($statut === 'annulé' ? 'fa-times-circle' : 'fa-clock') ?> me-1"></i>
                                  <?= ucfirst($statut) ?>
                                </span>
                              </small>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                              <i class="fas fa-times me-1"></i> Annuler
                            </button>
                            <button type="submit" name="update_status" class="btn-modal-update">
                              <i class="fas fa-save me-1"></i> Enregistrer
                            </button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                      <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                      Aucun rendez-vous trouvé.
                    </td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <?php if ($totalPages > 1): ?>
          <div class="tbl-pagination">
            <span><i class="fas fa-chart-simple me-1"></i> Page <?= $page ?> / <?= $totalPages ?></span>
            <div>
              <a href="<?= buildQuery(['page' => $page - 1]) ?>" class="pg-btn <?= $page <= 1 ? 'disabled' : '' ?>">
                <i class="fas fa-chevron-left"></i>
              </a>
              <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                <a href="<?= buildQuery(['page' => $p]) ?>" class="pg-btn <?= $p === $page ? 'active' : '' ?>"><?= $p ?></a>
              <?php endfor; ?>
              <a href="<?= buildQuery(['page' => $page + 1]) ?>" class="pg-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
                <i class="fas fa-chevron-right"></i>
              </a>
            </div>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Offcanvas notifications -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasNotif">
    <div class="offcanvas-header">
      <h5 class="offcanvas-title">
        <i class="fas fa-bell text-warning me-2"></i>
        Prochains Rendez-vous (24h)
      </h5>
      <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body" style="padding:16px;">

      <?php if (empty($notifications)): ?>
        <div class="text-center py-5">
          <div style="font-size:3rem;">📅</div>
          <p class="text-muted mt-3 mb-1">Aucun rendez-vous imminent</p>
          <small class="text-muted">Aucun RDV prévu dans les 48 prochaines heures</small>
        </div>

      <?php else: ?>

        <div style="font-size:.82rem; color:#94a3b8; margin-bottom:14px;">
          <i class="fas fa-bell text-warning me-1"></i>
          <strong style="color:#f59e0b;"><?= $nbNotifs ?></strong>
          rendez-vous à venir
        </div>

        <?php foreach ($notifications as $n):
          $h        = (int)$n['heures_restantes'];
          $minTotal = (int)$n['minutes_restantes'];
          $minPart  = (int)$n['minutes_part'];
          $urgent   = ($h < 2);

          if      ($minTotal < 60) $label = $minTotal . ' min';
          elseif  ($minPart  >  0) $label = $h . 'h ' . $minPart . 'min';
          else                     $label = $h . 'h';

          $border   = $urgent ? '#ef4444' : ($h < 6 ? '#f59e0b' : '#38bdf8');
          $badgeBg  = $urgent ? 'rgba(239,68,68,.2)' : 'rgba(245,158,11,.15)';
          $badgeTx  = $urgent ? '#ef4444' : '#f59e0b';
          $pct      = max(4, min(100, (int)round(($minTotal / (48*60)) * 100)));
          $barColor = $urgent ? '#ef4444' : ($h < 6 ? '#f59e0b' : '#10b981');

          $st = strtolower(trim($n['statutRDV'] ?? ''));
          $stClass = ($st==='confirmé'||$st==='confirme') ? 'statut-confirme'
                   : (($st==='terminé'||$st==='termine')  ? 'statut-termine'
                   : 'statut-attente');
        ?>
        <div class="mb-3" style="background:#0f172a; border:1.5px solid <?= $border ?>; border-radius:12px; overflow:hidden;">
          <div style="height:4px; background:#1e293b;">
            <div style="height:100%; width:<?= $pct ?>%; background:<?= $barColor ?>;"></div>
          </div>
          <div style="padding:14px;">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <strong style="color:#e2e8f0; font-size:.88rem;">
                <i class="fas fa-user me-1" style="color:#475569; font-size:.75rem;"></i>
                <?= htmlspecialchars($n['nomclient']) ?>
              </strong>
              <span style="background:<?= $badgeBg ?>; color:<?= $badgeTx ?>; border-radius:20px; padding:3px 10px; font-size:.75rem; font-weight:700; white-space:nowrap; margin-left:8px;">
                ⏳ Dans <?= $label ?>
              </span>
            </div>
            <div style="font-size:.82rem; color:#94a3b8; line-height:2;">
              <div>
                <i class="fas fa-calendar-day me-1 text-info"></i>
                <strong style="color:#e2e8f0;"><?= date('d/m/Y', strtotime($n['dateRDV'])) ?></strong>
                à <strong style="color:#38bdf8;"><?= substr($n['heureRDV'], 0, 5) ?></strong>
              </div>
              <div>
                <i class="fas fa-car me-1"></i>
                <span class="badge bg-secondary" style="font-size:.72rem;"><?= htmlspecialchars($n['matriculevoiture']) ?></span>
              </div>
              <div>
                <i class="fas fa-tools me-1"></i>
                <?= htmlspecialchars($n['type_serviceRDV']) ?>
              </div>
            </div>
            <div class="mt-2 d-flex align-items-center gap-2">
              <span class="badge-statut <?= $stClass ?>" style="font-size:.72rem;"><?= ucfirst($n['statutRDV']) ?></span>
              <?php if ($urgent): ?>
                <span class="badge bg-danger" style="font-size:.7rem;"><i class="fas fa-exclamation-triangle me-1"></i>URGENT</span>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

      <?php endif; ?>
    </div>
  </div>

  <?php include("../../partials/scripts.html"); ?>
  
  <!-- Scripts JavaScript -->
  <script>
    // Script pour les notifications
    document.addEventListener('DOMContentLoaded', function() {
        // Animation de la cloche si notifications
        const notifBtn = document.querySelector('.notification-btn');
        if (notifBtn && <?= $nbNotifs ?> > 0) {
            setInterval(function() {
                notifBtn.classList.add('ringing');
                setTimeout(function() {
                    notifBtn.classList.remove('ringing');
                }, 1500);
            }, 5000);
        }
        
        // Masquer le badge quand l'utilisateur consulte les notifications
        const offcanvasElement = document.getElementById('offcanvasNotif');
        if (offcanvasElement) {
            offcanvasElement.addEventListener('show.bs.offcanvas', function () {
                const badge = document.querySelector('.notification-btn .notif-badge');
                if (badge) {
                    badge.style.display = 'none';
                }
            });
        }

        // Animation des barres de progression
        const progressBars = document.querySelectorAll('.progress-fill');
        progressBars.forEach(bar => {
            const width = bar.style.width;
            bar.style.width = '0%';
            setTimeout(() => {
                bar.style.width = width;
            }, 100);
        });

        // Tooltips Bootstrap
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });

    // Données pour l'export (tous les rendez-vous)
    const allRDV = <?= json_encode(array_map(function($r) {
        return [
            $r['dateRDV'],
            substr($r['heureRDV'], 0, 5),
            $r['nomclient'],
            $r['matriculevoiture'],
            $r['type_serviceRDV'],
            $r['statutRDV'] ?? 'en attente',
            $r['descriptionRDV'] ?? ''
        ];
    }, $allRDV), JSON_UNESCAPED_UNICODE) ?>;

    const HEADERS = ['Date', 'Heure', 'Client', 'Matricule', 'Service', 'Statut', 'Description'];

    // Export Excel
    function exportExcel() {
        let html = '<html><head><meta charset="UTF-8"><title>Rendez-vous</title></head><body>';
        html += '<h2>Planning des Rendez-vous</h2>';
        html += '<p>Exporté le ' + new Date().toLocaleDateString('fr-FR') + '</p>';
        html += '<table border="1"><thead><tr>';
        HEADERS.forEach(h => { html += `<th>${h}</th>`; });
        html += '</thead><tbody>';
        
        allRDV.forEach(r => {
            html += '<tr>';
            r.forEach(v => {
                html += `<td>${String(v).replace(/</g, '&lt;')}</td>`;
            });
            html += '</tr>';
        });
        
        html += '</tbody></table></body></html>';
        
        const blob = new Blob([html], { type: 'application/vnd.ms-excel;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'rendez-vous.xls';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    // Export PDF
    function exportPDF() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a4' });
        
        // Fond sombre
        doc.setFillColor(11, 17, 30);
        doc.rect(0, 0, 297, 210, 'F');
        
        // Titre
        doc.setFontSize(16);
        doc.setTextColor(56, 189, 248);
        doc.text('Planning des Rendez-vous', 14, 16);
        
        // Date d'export
        doc.setFontSize(9);
        doc.setTextColor(100, 116, 139);
        doc.text('Exporté le ' + new Date().toLocaleDateString('fr-FR'), 14, 23);
        
        // Statistiques résumées
        doc.setFontSize(8);
        doc.setTextColor(148, 163, 184);
        doc.text(`Total: <?= $stats_total ?> | Confirmés: <?= $stats_confirmes ?> | En attente: <?= $stats_attente ?> | Terminés: <?= $stats_termines ?> | Annulés: <?= $stats_annules ?>`, 14, 30);
        
        // Tableau
        doc.autoTable({
            head: [HEADERS],
            body: allRDV,
            startY: 35,
            styles: {
                fontSize: 7,
                cellPadding: 3,
                textColor: [226, 232, 240],
                fillColor: [17, 28, 45],
                lineColor: [30, 41, 59],
                lineWidth: 0.3,
                overflow: 'ellipsize'
            },
            headStyles: {
                fillColor: [30, 41, 59],
                textColor: [148, 163, 184],
                fontStyle: 'bold',
                fontSize: 7,
                halign: 'left'
            },
            alternateRowStyles: { fillColor: [15, 23, 42] },
            margin: { left: 14, right: 14 },
            columnStyles: {
                0: { cellWidth: 24 },
                1: { cellWidth: 14 },
                3: { cellWidth: 26 },
                5: { cellWidth: 22 },
                6: { cellWidth: 52 }
            }
        });
        
        doc.save('rendez-vous.pdf');
    }

    // Exporter les fonctions globalement
    window.exportExcel = exportExcel;
    window.exportPDF = exportPDF;
  </script>
</body>
</html>