<?php
session_start();
require_once "../../models/db.php";
$pdo = config::getConnexion();

// ── PARAMÈTRES DE NAVIGATION MENSUELLE ─────────────────────────────────
$mois  = isset($_GET['mois'])  ? (int)$_GET['mois']  : (int)date('m');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');

if ($mois < 1)  { $mois = 12; $annee--; }
if ($mois > 12) { $mois = 1;  $annee++; }

// ── DATE SÉLECTIONNÉE (clic sur un jour) ───────────────────────────────
$dateSelectionnee = isset($_GET['date']) ? $_GET['date'] : null;

// ── RÉCUPÉRATION DES RDV AVEC JOIN client + véhicule ──────────────────
try {
    $sql = "
    SELECT
        r.idRDV,
        r.dateRDV,
        r.heureRDV,
        r.type_serviceRDV,
        r.statutRDV,
        r.idVehicule,
        r.idclientRDV,
        r.descriptionRDV,
        CONCAT(u.nom, ' ', u.prenom) AS nomclient,
        v.matriculevoiture
    FROM rendezvous r
    LEFT JOIN client   u ON u.id_client  = r.idclientRDV
    LEFT JOIN vehicule v ON v.idVehicule = r.idVehicule
    ORDER BY r.dateRDV, r.heureRDV
";
    $stmt = $pdo->query($sql);
    $tousLesRDV = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $tousLesRDV = [];
}

// Indexer par date
$rdvParDate = [];
foreach ($tousLesRDV as $rdv) {
    $rdvParDate[$rdv['dateRDV']][] = $rdv;
}

// ── CALCULS CALENDRIER ─────────────────────────────────────────────────
$premierJour    = mktime(0, 0, 0, $mois, 1, $annee);
$nbJoursDuMois  = (int)date('t', $premierJour);
$jourSemDebut   = (int)date('N', $premierJour);

$moisPrecedent  = $mois - 1 < 1  ? 12 : $mois - 1;
$anneePrecedent = $mois - 1 < 1  ? $annee - 1 : $annee;
$moisSuivant    = $mois + 1 > 12 ? 1  : $mois + 1;
$anneeSuivant   = $mois + 1 > 12 ? $annee + 1 : $annee;

$nomsMois = [
    1=>'Janvier',2=>'Février',3=>'Mars',4=>'Avril',
    5=>'Mai',6=>'Juin',7=>'Juillet',8=>'Août',
    9=>'Septembre',10=>'Octobre',11=>'Novembre',12=>'Décembre'
];

$aujourdhui = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light">
<head>
  <meta charset="utf-8" />
  <title>Calendrier des Rendez-vous</title>
  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
  <?php include("../../partials/head/head-links.html"); ?>
  
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

    /* ========== PAGE HEADER ========== */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 28px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .page-header h4 {
      color: #0f172a;
      margin: 0;
      font-size: 1.35rem;
      font-weight: 700;
    }
    .page-header p {
      color: #64748b;
      font-size: 0.8rem;
      margin: 4px 0 0;
    }

    .btn-retour {
      display: inline-flex;
      align-items: center;
      gap: 7px;
      background: #eff6ff;
      color: #2563eb;
      border: 1px solid #bfdbfe;
      border-radius: 10px;
      padding: 9px 20px;
      font-size: 0.83rem;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.2s;
    }
    .btn-retour:hover {
      background: #2563eb;
      color: white;
      border-color: #2563eb;
    }

    /* ========== NAVIGATION MOIS ========== */
    .nav-mois {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 16px 28px;
      margin-bottom: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .nav-mois a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 38px;
      height: 38px;
      background: #f1f5f9;
      color: #64748b;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      text-decoration: none;
      font-size: 1.1rem;
      font-weight: 700;
      transition: all 0.2s;
    }
    .nav-mois a:hover {
      background: #3b82f6;
      color: white;
      border-color: #3b82f6;
    }
    .nav-mois .titre-mois {
      font-size: 1.15rem;
      font-weight: 700;
      color: #0f172a;
      min-width: 210px;
      text-align: center;
    }

    /* ========== LÉGENDE ========== */
    .legende {
      display: flex;
      gap: 18px;
      flex-wrap: wrap;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 14px 22px;
      font-size: 0.78rem;
      margin-bottom: 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .legende-item {
      display: flex;
      align-items: center;
      gap: 7px;
      color: #64748b;
      font-weight: 500;
    }
    .legende-dot {
      width: 10px;
      height: 10px;
      border-radius: 3px;
      flex-shrink: 0;
    }

    /* ========== CALENDRIER ========== */
    .calendrier-wrapper {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      overflow: hidden;
      margin-bottom: 28px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }

    .cal-entetes {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      background: #f8fafc;
      border-bottom: 1px solid #e2e8f0;
    }
    .cal-entete-jour {
      text-align: center;
      padding: 13px 4px;
      font-size: 0.71rem;
      font-weight: 700;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .cal-entete-jour.weekend {
      color: #ef4444;
    }

    .cal-grille {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
    }

    .cal-case {
      min-height: 120px;
      border-right: 1px solid #e2e8f0;
      border-bottom: 1px solid #e2e8f0;
      padding: 10px;
      position: relative;
      transition: background 0.15s;
      vertical-align: top;
    }
    .cal-case:nth-child(7n) {
      border-right: none;
    }
    .cal-case.vide {
      background: #f8fafc;
    }
    .cal-case.actif {
      cursor: pointer;
    }
    .cal-case.actif:hover {
      background: #eff6ff;
    }

    .num-jour {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 32px;
      height: 32px;
      border-radius: 50%;
      font-size: 0.85rem;
      font-weight: 600;
      color: #64748b;
      margin-bottom: 8px;
    }

    .cal-case.aujourd-hui .num-jour {
      background: #3b82f6;
      color: white;
      font-weight: 700;
      box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
    }

    .cal-case.selectionne {
      background: #eff6ff;
    }
    .cal-case.selectionne .num-jour {
      background: #2563eb;
      color: white;
      font-weight: 700;
    }

    .cal-case a.case-link {
      position: absolute;
      inset: 0;
      z-index: 1;
    }

    .rdv-pastilles {
      position: relative;
      z-index: 2;
    }
    .rdv-puce {
      display: flex;
      align-items: center;
      gap: 4px;
      background: #f1f5f9;
      border-left: 3px solid #3b82f6;
      border-radius: 0 6px 6px 0;
      padding: 4px 8px;
      margin-bottom: 4px;
      font-size: 0.7rem;
      color: #334155;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
      font-weight: 500;
    }
    .rdv-puce.confirme {
      border-color: #10b981;
      background: #d1fae5;
      color: #065f46;
    }
    .rdv-puce.annule {
      border-color: #ef4444;
      background: #fee2e2;
      color: #991b1b;
    }
    .rdv-puce.termine {
      border-color: #8b5cf6;
      background: #ede9fe;
      color: #5b21b6;
    }
    .rdv-puce.attente {
      border-color: #f59e0b;
      background: #fef3c7;
      color: #92400e;
    }

    .rdv-plus {
      font-size: 0.65rem;
      color: #64748b;
      padding: 2px 8px;
      font-weight: 600;
    }
    .cal-case.weekend .num-jour {
      color: #ef4444;
    }

    /* ========== PANNAU DÉTAIL ========== */
    .detail-panel {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      padding: 24px 28px;
      margin-bottom: 24px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .detail-panel h5 {
      color: #0f172a;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 9px;
      padding-bottom: 14px;
      border-bottom: 1px solid #e2e8f0;
    }

    .rdv-card {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 10px;
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
      transition: all 0.2s;
    }
    .rdv-card:hover {
      background: #ffffff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .rdv-card-heure {
      font-size: 1rem;
      font-weight: 800;
      color: #2563eb;
      min-width: 58px;
    }
    .rdv-card-info {
      flex: 1;
      min-width: 0;
    }
    .rdv-card-info .client {
      font-weight: 700;
      color: #0f172a;
      font-size: 0.9rem;
    }
    .rdv-card-info .vehicule {
      color: #64748b;
      font-size: 0.77rem;
      margin-top: 3px;
    }
    .rdv-card-info .service {
      color: #64748b;
      font-size: 0.77rem;
      margin-top: 2px;
    }

    .badge-statut {
      display: inline-flex;
      align-items: center;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.05em;
      white-space: nowrap;
    }
    .statut-confirme {
      background: #d1fae5;
      color: #065f46;
    }
    .statut-annule {
      background: #fee2e2;
      color: #991b1b;
    }
    .statut-attente {
      background: #fef3c7;
      color: #92400e;
    }
    .statut-termine {
      background: #ede9fe;
      color: #5b21b6;
    }

    /* ========== RESPONSIVE ========== */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .main-content {
        margin-left: 0;
      }
      .cal-case {
        min-height: 80px;
        padding: 6px;
      }
      .rdv-puce {
        display: none;
      }
      .rdv-plus {
        font-size: 0.6rem;
      }
      .nav-mois .titre-mois {
        font-size: 0.95rem;
        min-width: 140px;
      }
    }
  </style>
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
    <a href="Gestion_rendezVousBackend.php">Gestion des rendez-vous</a>
    <a href="calendrier_rendezVous.php" class="active">Calendrier RDV</a>
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
      <h1>Calendrier des Rendez-vous</h1>
      <p><i class="fas fa-calendar-alt"></i> <?= date('l d F Y') ?></p>
    </div>
    <div style="display: flex; gap: 12px;">
      <a href="Gestion_rendezVousBackend.php" class="btn-retour">
        <i class="fas fa-arrow-left"></i> Retour à la liste
      </a>
    </div>
  </div>

  <div style="padding: 28px;">
    
    <!-- Navigation mois -->
    <div class="nav-mois">
      <a href="?mois=<?= $moisPrecedent ?>&annee=<?= $anneePrecedent ?><?= $dateSelectionnee ? '&date='.$dateSelectionnee : '' ?>">‹</a>
      <span class="titre-mois"><?= $nomsMois[$mois] ?> <?= $annee ?></span>
      <a href="?mois=<?= $moisSuivant ?>&annee=<?= $anneeSuivant ?><?= $dateSelectionnee ? '&date='.$dateSelectionnee : '' ?>">›</a>
    </div>

    <!-- Légende -->
    <div class="legende">
      <span class="legende-item"><span class="legende-dot" style="background: #f59e0b;"></span> En attente</span>
      <span class="legende-item"><span class="legende-dot" style="background: #10b981;"></span> Confirmé</span>
      <span class="legende-item"><span class="legende-dot" style="background: #8b5cf6;"></span> Terminé</span>
      <span class="legende-item"><span class="legende-dot" style="background: #ef4444;"></span> Annulé</span>
      <span class="legende-item"><span class="legende-dot" style="background: #3b82f6; border-radius: 50%;"></span> Aujourd'hui</span>
    </div>

    <!-- Détail du jour sélectionné -->
    <?php if ($dateSelectionnee): ?>
      <?php
        $ts = strtotime($dateSelectionnee);
        $joursNoms = ['', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
        $labelDate = $joursNoms[(int)date('N', $ts)] . ' ' . (int)date('d', $ts) . ' ' . $nomsMois[(int)date('m', $ts)] . ' ' . date('Y', $ts);
        $rdvDuJour = $rdvParDate[$dateSelectionnee] ?? [];
      ?>
      <div class="detail-panel">
        <h5>
          <i class="fas fa-calendar-day" style="color: #3b82f6;"></i>
          <?= htmlspecialchars($labelDate) ?>
          <span style="color: #64748b; font-size: 0.8rem; font-weight: 400; margin-left: 8px;"><?= count($rdvDuJour) ?> rendez-vous</span>
        </h5>

        <?php if (empty($rdvDuJour)): ?>
          <div style="text-align: center; padding: 40px; color: #64748b;">
            <i class="fas fa-calendar-times" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
            Aucun rendez-vous ce jour-là.
          </div>
        <?php else: ?>
          <?php
            usort($rdvDuJour, fn($a, $b) => strcmp($a['heureRDV'], $b['heureRDV']));
            foreach ($rdvDuJour as $rdv):
              $statut = $rdv['statutRDV'] ?? 'en attente';
              $badgeMap = [
                'confirmé' => 'statut-confirme',
                'annulé'   => 'statut-annule',
                'terminé'  => 'statut-termine',
              ];
              $badge = $badgeMap[$statut] ?? 'statut-attente';
          ?>
          <div class="rdv-card">
            <div class="rdv-card-heure"><?= htmlspecialchars(substr($rdv['heureRDV'], 0, 5)) ?></div>
            <div class="rdv-card-info">
              <div class="client"><i class="fas fa-user me-1" style="color: #3b82f6;"></i> <?= htmlspecialchars($rdv['nomclient']) ?></div>
              <?php if (!empty($rdv['matriculevoiture'])): ?>
                <div class="vehicule"><i class="fas fa-car me-1"></i> <?= htmlspecialchars($rdv['matriculevoiture']) ?></div>
              <?php endif; ?>
              <div class="service"><i class="fas fa-wrench me-1"></i> <?= htmlspecialchars($rdv['type_serviceRDV']) ?></div>
            </div>
            <div><span class="badge-statut <?= $badge ?>"><?= ucfirst($statut) ?></span></div>
            <div style="color: #94a3b8; font-size: 0.7rem;">RDV #<?= htmlspecialchars($rdv['idRDV']) ?></div>
          </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <!-- Grille calendrier -->
    <div class="calendrier-wrapper">
      <div class="cal-entetes">
        <?php
          $joursEntetes = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'];
          foreach ($joursEntetes as $i => $j):
            $isWeekend = $i >= 5;
        ?>
          <div class="cal-entete-jour <?= $isWeekend ? 'weekend' : '' ?>"><?= $j ?></div>
        <?php endforeach; ?>
      </div>

      <div class="cal-grille">
        <!-- Cases vides avant le 1er -->
        <?php for ($v = 1; $v < $jourSemDebut; $v++): ?>
          <div class="cal-case vide"></div>
        <?php endfor; ?>

        <!-- Cases des jours du mois -->
        <?php for ($jour = 1; $jour <= $nbJoursDuMois; $jour++):
          $dateStr = sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
          $rdvJour = $rdvParDate[$dateStr] ?? [];
          $nbRdv = count($rdvJour);
          $jourdSem = (int)date('N', mktime(0, 0, 0, $mois, $jour, $annee));
          $isWeekend = $jourdSem >= 6;
          $isAujourdhui = $dateStr === $aujourdhui;
          $isSelectionne = $dateStr === $dateSelectionnee;

          $classes = 'cal-case actif';
          if ($isWeekend) $classes .= ' weekend';
          if ($isAujourdhui) $classes .= ' aujourd-hui';
          if ($isSelectionne) $classes .= ' selectionne';

          $lien = '?mois='.$mois.'&annee='.$annee.'&date='.$dateStr;
          if ($isSelectionne) $lien = '?mois='.$mois.'&annee='.$annee;
        ?>
          <div class="<?= $classes ?>">
            <a href="<?= $lien ?>" class="case-link" title="Voir les RDV du <?= $jour.' '.$nomsMois[$mois] ?>"></a>
            <div class="num-jour"><?= $jour ?></div>

            <?php if ($nbRdv > 0): ?>
              <div class="rdv-pastilles">
                <?php
                  usort($rdvJour, fn($a, $b) => strcmp($a['heureRDV'], $b['heureRDV']));
                  $max = 2;
                  $affiche = 0;
                  foreach ($rdvJour as $rdv):
                    if ($affiche >= $max) break;
                    $statut = $rdv['statutRDV'] ?? 'en attente';
                    $puceClass = match($statut) {
                      'confirmé' => 'confirme',
                      'annulé'   => 'annule',
                      'terminé'  => 'termine',
                      default    => 'attente',
                    };
                    $heure = substr($rdv['heureRDV'], 0, 5);
                ?>
                  <div class="rdv-puce <?= $puceClass ?>">
                    <span style="font-weight: 700;"><?= htmlspecialchars($heure) ?></span>
                    &nbsp;<?= htmlspecialchars($rdv['nomclient']) ?>
                  </div>
                <?php $affiche++; endforeach; ?>

                <?php if ($nbRdv > $max): ?>
                  <div class="rdv-plus">+ <?= $nbRdv - $max ?> autre<?= $nbRdv - $max > 1 ? 's' : '' ?></div>
                <?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endfor; ?>

        <!-- Cases vides après le dernier jour -->
        <?php
          $dernierJour = (int)date('N', mktime(0, 0, 0, $mois, $nbJoursDuMois, $annee));
          for ($v = $dernierJour + 1; $v <= 7; $v++):
        ?>
          <div class="cal-case vide"></div>
        <?php endfor; ?>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>