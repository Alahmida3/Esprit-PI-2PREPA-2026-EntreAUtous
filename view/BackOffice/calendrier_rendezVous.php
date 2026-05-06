<?php
require_once "../../config.php";
$pdo = config::getConnexion();

// ── PARAMÈTRES DE NAVIGATION MENSUELLE ─────────────────────────────────
$mois  = isset($_GET['mois'])  ? (int)$_GET['mois']  : (int)date('m');
$annee = isset($_GET['annee']) ? (int)$_GET['annee'] : (int)date('Y');

if ($mois < 1)  { $mois = 12; $annee--; }
if ($mois > 12) { $mois = 1;  $annee++; }

// ── DATE SÉLECTIONNÉE (clic sur un jour) ───────────────────────────────
$dateSelectionnee = isset($_GET['date']) ? $_GET['date'] : null;

// ── RÉCUPÉRATION DES RDV AVEC JOIN client + véhicule ──────────────────
// On joint :
//   rendezvous  →  user     (idclientRDV = id_client)  pour nomclient
//   rendezvous  →  vehicule (idVehicule  = idVehicule)  pour matriculevoiture
//
// $pdo est la variable de connexion exposée par config.php.
// Si votre config.php expose un nom différent (ex. $conn, $db), adaptez ici.
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
            COALESCE(u.nomclient, CONCAT('Client #', r.idclientRDV))  AS nomclient,
            COALESCE(v.matriculevoiture, CONCAT('VEH-', r.idVehicule)) AS matriculevoiture
        FROM rendezvous r
        LEFT JOIN user    u ON u.id_client  = r.idclientRDV
        LEFT JOIN vehicule v ON v.idVehicule = r.idVehicule
        ORDER BY r.dateRDV, r.heureRDV
    ";
    $stmt = $pdo->query($sql);
    $tousLesRDV = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback : si la connexion s'appelle autrement, essayons $conn
    // Supprimez ce bloc si votre config.php expose bien $pdo
    $tousLesRDV = [];
}

// Indexer par date  →  ['2026-05-10' => [ rdv1, rdv2, ... ], ...]
$rdvParDate = [];
foreach ($tousLesRDV as $rdv) {
    $rdvParDate[$rdv['dateRDV']][] = $rdv;
}

// ── CALCULS CALENDRIER ─────────────────────────────────────────────────
$premierJour    = mktime(0, 0, 0, $mois, 1, $annee);
$nbJoursDuMois  = (int)date('t', $premierJour);
$jourSemDebut   = (int)date('N', $premierJour); // 1=Lun … 7=Dim

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
<html lang="fr" data-bs-theme="dark">
<head>
  <meta charset="utf-8" />
  <title>Calendrier des Rendez-vous</title>
  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  <?php include("../../partials/head/head-links.html"); ?>

  <style>
    /* ═══════════════════════════════════════════════
       BASE
    ═══════════════════════════════════════════════ */
    *, *::before, *::after { box-sizing: border-box; }
    body  { background: #0b111e; color: #e2e8f0; font-family: 'Inter', sans-serif; }

    /* ═══════════════════════════════════════════════
       EN-TÊTE DE PAGE
    ═══════════════════════════════════════════════ */
    .page-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 28px;
      flex-wrap: wrap;
      gap: 12px;
    }
    .page-header h4  { color: #f1f5f9; margin: 0; font-size: 1.25rem; font-weight: 700; }
    .page-header p   { color: #64748b; font-size: 0.82rem; margin: 2px 0 0; }

    .btn-retour {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: rgba(56,189,248,.1);
      color: #38bdf8;
      border: 1px solid rgba(56,189,248,.25);
      border-radius: 10px;
      padding: 8px 18px;
      font-size: 0.83rem;
      font-weight: 500;
      text-decoration: none;
      transition: all .2s;
    }
    .btn-retour:hover { background: #38bdf8; color: #0b111e; }

    /* ═══════════════════════════════════════════════
       NAVIGATION MOIS
    ═══════════════════════════════════════════════ */
    .nav-mois {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 20px;
      background: #111c2d;
      border: 1px solid #1e293b;
      border-radius: 14px;
      padding: 14px 24px;
      margin-bottom: 22px;
    }
    .nav-mois a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 36px; height: 36px;
      background: rgba(56,189,248,.1);
      color: #38bdf8;
      border: 1px solid rgba(56,189,248,.2);
      border-radius: 8px;
      text-decoration: none;
      font-size: 1rem;
      transition: all .2s;
    }
    .nav-mois a:hover { background: #38bdf8; color: #0b111e; }
    .nav-mois .titre-mois {
      font-size: 1.15rem;
      font-weight: 700;
      color: #f1f5f9;
      min-width: 200px;
      text-align: center;
    }

    /* ═══════════════════════════════════════════════
       GRILLE CALENDRIER
    ═══════════════════════════════════════════════ */
    .calendrier-wrapper {
      background: #111c2d;
      border: 1px solid #1e293b;
      border-radius: 16px;
      overflow: hidden;
      margin-bottom: 28px;
    }

    /* Ligne des jours de la semaine */
    .cal-entetes {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
      background: #0f172a;
      border-bottom: 1px solid #1e293b;
    }
    .cal-entete-jour {
      text-align: center;
      padding: 12px 4px;
      font-size: 0.72rem;
      font-weight: 700;
      color: #94a3b8;
      text-transform: uppercase;
      letter-spacing: .06em;
    }
    .cal-entete-jour.weekend { color: #ef4444; }

    /* Grille des cases */
    .cal-grille {
      display: grid;
      grid-template-columns: repeat(7, 1fr);
    }

    /* Case individuelle */
    .cal-case {
      min-height: 110px;
      border-right: 1px solid #1e293b;
      border-bottom: 1px solid #1e293b;
      padding: 8px;
      position: relative;
      transition: background .15s;
      vertical-align: top;
    }
    .cal-case:nth-child(7n) { border-right: none; }

    /* Case vide (jours d'un autre mois) */
    .cal-case.vide { background: #0d1625; }

    /* Case cliquable */
    .cal-case.actif { cursor: pointer; }
    .cal-case.actif:hover { background: rgba(56,189,248,.05); }

    /* Numéro du jour */
    .num-jour {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 28px; height: 28px;
      border-radius: 50%;
      font-size: 0.83rem;
      font-weight: 600;
      color: #94a3b8;
      margin-bottom: 5px;
    }

    /* Aujourd'hui */
    .cal-case.aujourd-hui .num-jour {
      background: #38bdf8;
      color: #0b111e;
    }

    /* Jour sélectionné */
    .cal-case.selectionne {
      background: rgba(59,130,246,.08);
      border-color: #3b82f6;
    }
    .cal-case.selectionne .num-jour {
      background: #3b82f6;
      color: #fff;
    }

    /* Lien invisible sur toute la case */
    .cal-case a.case-link {
      position: absolute;
      inset: 0;
      z-index: 1;
    }

    /* Pastilles RDV dans la case */
    .rdv-pastilles { position: relative; z-index: 2; }
    .rdv-puce {
      display: flex;
      align-items: center;
      gap: 4px;
      background: rgba(56,189,248,.12);
      border-left: 3px solid #38bdf8;
      border-radius: 0 6px 6px 0;
      padding: 3px 6px;
      margin-bottom: 3px;
      font-size: 0.68rem;
      color: #cbd5e1;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      max-width: 100%;
    }
    .rdv-puce.confirme  { border-color: #10b981; background: rgba(16,185,129,.1);  }
    .rdv-puce.annule    { border-color: #ef4444; background: rgba(239,68,68,.1);   }
    .rdv-puce.termine   { border-color: #3b82f6; background: rgba(59,130,246,.1);  }
    .rdv-puce.attente   { border-color: #f59e0b; background: rgba(245,158,11,.1);  }

    .rdv-plus {
      font-size: 0.65rem;
      color: #64748b;
      padding: 2px 6px;
    }

    /* Jours weekend */
    .cal-case.weekend .num-jour { color: #ef4444; }

    /* ═══════════════════════════════════════════════
       PANNEAU DÉTAIL (date sélectionnée)
    ═══════════════════════════════════════════════ */
    .detail-panel {
      background: #111c2d;
      border: 1px solid #1e293b;
      border-radius: 16px;
      padding: 24px;
      margin-bottom: 28px;
    }
    .detail-panel h5 {
      color: #38bdf8;
      font-size: 1rem;
      font-weight: 700;
      margin-bottom: 18px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .rdv-card {
      background: #0f172a;
      border: 1px solid #1e293b;
      border-radius: 12px;
      padding: 16px 20px;
      margin-bottom: 12px;
      display: flex;
      align-items: center;
      gap: 16px;
      flex-wrap: wrap;
    }
    .rdv-card-heure {
      font-size: 1.1rem;
      font-weight: 800;
      color: #38bdf8;
      min-width: 55px;
    }
    .rdv-card-info { flex: 1; }
    .rdv-card-info .client  { font-weight: 600; color: #f1f5f9; font-size: 0.9rem; }
    .rdv-card-info .vehicule { color: #64748b; font-size: 0.78rem; margin-top: 2px; }
    .rdv-card-info .service { color: #94a3b8; font-size: 0.78rem; }
    .badge-statut {
      display: inline-block;
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.68rem;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: .04em;
    }
    .statut-confirme { background: rgba(16,185,129,.15); color: #10b981; }
    .statut-annule   { background: rgba(239,68,68,.15);  color: #ef4444; }
    .statut-attente  { background: rgba(245,158,11,.15); color: #f59e0b; }
    .statut-termine  { background: rgba(59,130,246,.15); color: #3b82f6; }

    /* ═══════════════════════════════════════════════
       LÉGENDE
    ═══════════════════════════════════════════════ */
    .legende {
      display: flex;
      gap: 16px;
      flex-wrap: wrap;
      background: #111c2d;
      border: 1px solid #1e293b;
      border-radius: 12px;
      padding: 14px 20px;
      font-size: 0.78rem;
      margin-bottom: 28px;
    }
    .legende-item {
      display: flex;
      align-items: center;
      gap: 6px;
      color: #94a3b8;
    }
    .legende-dot {
      width: 10px; height: 10px;
      border-radius: 2px;
    }

    /* ═══════════════════════════════════════════════
       RESPONSIVE
    ═══════════════════════════════════════════════ */
    @media (max-width: 640px) {
      .cal-case       { min-height: 70px; padding: 4px; }
      .rdv-puce       { display: none; }
      .rdv-plus       { font-size: 0.6rem; }
      .nav-mois .titre-mois { font-size: 0.95rem; min-width: 140px; }
    }
  </style>
</head>

<body>
<div class="wrapper">
  <?php include("../../partials/sidebar-collapse.html"); ?>

  <div id="content" class="main-content">
    <?php include("../../partials/topbar-second.html"); ?>

    <div class="container-fluid mt-4 px-4">

      <!-- ── En-tête ── -->
      <div class="page-header">
        <div>
          <h4><i class="fas fa-calendar-alt me-2 text-info"></i>Calendrier des Rendez-vous</h4>
          <p>Vue mensuelle — cliquez sur un jour pour voir les détails</p>
        </div>
        <a href="Gestion_rendezVousBackend.php" class="btn-retour">
          <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
      </div>

      <!-- ── Navigation mois ── -->
      <div class="nav-mois">
        <a href="?mois=<?= $moisPrecedent ?>&annee=<?= $anneePrecedent ?>
            <?= $dateSelectionnee ? '&date='.$dateSelectionnee : '' ?>"
           title="Mois précédent">&#8249;</a>

        <span class="titre-mois">
          <?= $nomsMois[$mois] ?> <?= $annee ?>
        </span>

        <a href="?mois=<?= $moisSuivant ?>&annee=<?= $anneeSuivant ?>
            <?= $dateSelectionnee ? '&date='.$dateSelectionnee : '' ?>"
           title="Mois suivant">&#8250;</a>
      </div>

      <!-- ── Légende ── -->
      <div class="legende">
        <span class="legende-item">
          <span class="legende-dot" style="background:#f59e0b"></span> En attente
        </span>
        <span class="legende-item">
          <span class="legende-dot" style="background:#10b981"></span> Confirmé
        </span>
        <span class="legende-item">
          <span class="legende-dot" style="background:#3b82f6"></span> Terminé
        </span>
        <span class="legende-item">
          <span class="legende-dot" style="background:#ef4444"></span> Annulé
        </span>
        <span class="legende-item">
          <span class="legende-dot" style="background:#38bdf8; border-radius:50%"></span> Aujourd'hui
        </span>
      </div>

      <!-- ── Détail d'un jour sélectionné ── -->
      <?php if ($dateSelectionnee): ?>
        <?php
          // Formater la date pour l'affichage
          $ts = strtotime($dateSelectionnee);
          $joursNoms = ['','Lundi','Mardi','Mercredi','Jeudi','Vendredi','Samedi','Dimanche'];
          $labelDate = $joursNoms[(int)date('N', $ts)].' '.(int)date('d', $ts).' '
                       .$nomsMois[(int)date('m', $ts)].' '.date('Y', $ts);
          $rdvDuJour = $rdvParDate[$dateSelectionnee] ?? [];
        ?>
        <div class="detail-panel">
          <h5>
            <i class="fas fa-calendar-day"></i>
            <?= htmlspecialchars($labelDate) ?>
            <span style="color:#64748b; font-size:.8rem; font-weight:400; margin-left:8px;">
              <?= count($rdvDuJour) ?> rendez-vous
            </span>
          </h5>

          <?php if (empty($rdvDuJour)): ?>
            <div style="text-align:center; padding:30px; color:#475569;">
              <i class="fas fa-calendar-times" style="font-size:2rem; margin-bottom:10px; display:block;"></i>
              Aucun rendez-vous ce jour-là.
            </div>
          <?php else: ?>
            <?php
              // Trier par heure
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
              <div class="rdv-card-heure">
                <?= htmlspecialchars(substr($rdv['heureRDV'], 0, 5)) ?>
              </div>
              <div class="rdv-card-info">
                <div class="client">
                  <i class="fas fa-user me-1" style="color:#38bdf8"></i>
                  <?= htmlspecialchars($rdv['nomclient']) ?>
                </div>
                <?php if (!empty($rdv['matriculevoiture'])): ?>
                <div class="vehicule">
                  <i class="fas fa-car me-1"></i>
                  <?= htmlspecialchars($rdv['matriculevoiture']) ?>
                </div>
                <?php endif; ?>
                <div class="service">
                  <i class="fas fa-wrench me-1"></i>
                  <?= htmlspecialchars($rdv['type_serviceRDV']) ?>
                </div>
              </div>
              <div>
                <span class="badge-statut <?= $badge ?>">
                  <?= ucfirst($statut) ?>
                </span>
              </div>
              <div style="color:#475569; font-size:.75rem;">
                RDV #<?= htmlspecialchars($rdv['idRDV']) ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- ── Grille calendrier ── -->
      <div class="calendrier-wrapper">

        <!-- En-têtes jours semaine -->
        <div class="cal-entetes">
          <?php
            $joursEntetes = ['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'];
            foreach ($joursEntetes as $i => $j):
              $isWeekend = $i >= 5;
          ?>
          <div class="cal-entete-jour <?= $isWeekend ? 'weekend' : '' ?>">
            <?= $j ?>
          </div>
          <?php endforeach; ?>
        </div>

        <!-- Cases des jours -->
        <div class="cal-grille">

          <!-- Cases vides avant le 1er -->
          <?php for ($v = 1; $v < $jourSemDebut; $v++): ?>
          <div class="cal-case vide"></div>
          <?php endfor; ?>

          <!-- Cases des jours du mois -->
          <?php for ($jour = 1; $jour <= $nbJoursDuMois; $jour++):
            $dateStr   = sprintf('%04d-%02d-%02d', $annee, $mois, $jour);
            $rdvJour   = $rdvParDate[$dateStr] ?? [];
            $nbRdv     = count($rdvJour);
            $jourdSem  = (int)date('N', mktime(0,0,0,$mois,$jour,$annee)); // 1=lun
            $isWeekend = $jourdSem >= 6;
            $isAujourdhui  = $dateStr === $aujourdhui;
            $isSelectionne = $dateStr === $dateSelectionnee;

            $classes = 'cal-case actif';
            if ($isWeekend)    $classes .= ' weekend';
            if ($isAujourdhui) $classes .= " aujourd-hui";
            if ($isSelectionne) $classes .= ' selectionne';

            // Lien de sélection du jour
            $lien = '?mois='.$mois.'&annee='.$annee.'&date='.$dateStr;
            // Si déjà sélectionné, le clic désélectionne
            if ($isSelectionne) $lien = '?mois='.$mois.'&annee='.$annee;
          ?>
          <div class="<?= $classes ?>">
            <!-- Lien couvrant toute la case -->
            <a href="<?= $lien ?>" class="case-link" title="Voir les RDV du <?= $jour.' '.$nomsMois[$mois] ?>"></a>

            <div class="num-jour"><?= $jour ?></div>

            <?php if ($nbRdv > 0): ?>
            <div class="rdv-pastilles">
              <?php
                // Trier les RDV du jour par heure
                usort($rdvJour, fn($a, $b) => strcmp($a['heureRDV'], $b['heureRDV']));
                $max = 2; // max de puces visibles par case
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
                <span style="font-weight:700"><?= htmlspecialchars($heure) ?></span>
                &nbsp;<?= htmlspecialchars($rdv['nomclient']) ?>
                <?php if (!empty($rdv['matriculevoiture'])): ?>
                  · <span style="opacity:.75"><?= htmlspecialchars($rdv['matriculevoiture']) ?></span>
                <?php endif; ?>
              </div>
              <?php $affiche++; endforeach; ?>

              <?php if ($nbRdv > $max): ?>
              <div class="rdv-plus">+ <?= $nbRdv - $max ?> autre<?= $nbRdv - $max > 1 ? 's' : '' ?></div>
              <?php endif; ?>
            </div>
            <?php endif; ?>
          </div>
          <?php endfor; ?>

          <!-- Cases vides après le dernier jour pour compléter la grille -->
          <?php
            $dernierJour = (int)date('N', mktime(0,0,0,$mois,$nbJoursDuMois,$annee));
            for ($v = $dernierJour + 1; $v <= 7; $v++):
          ?>
          <div class="cal-case vide"></div>
          <?php endfor; ?>

        </div><!-- /.cal-grille -->
      </div><!-- /.calendrier-wrapper -->

    </div><!-- /.container-fluid -->
  </div><!-- /#content -->
</div><!-- /.wrapper -->

<?php include("../../partials/scripts.html"); ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>