<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../controller/Vehicule.php');

$vehiculeC = new VoitureC(); 

// ── LOGIQUE DE SUPPRESSION ──────────────────────────────────────────
if (isset($_GET['delete_id'])) {
    $vehiculeC->supprimerVehicule($_GET['delete_id']);
    header('Location: Gestion_voitureBackend.php'); 
    exit();
}

// ── RÉCUPÉRATION DES DONNÉES (Affichage Complet) ─────────────────────
$listVehicules = null;
$isSearch = false;

if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
    $listVehicules = $vehiculeC->getVehiculeById($_GET['search_id']); 
    $isSearch = true;
} else {
    $listVehicules = $vehiculeC->afficherVehicule(); 
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestion des Véhicules - Admin</title>

  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  <?php include("../../partials/head/head-meta.html"); ?>
  <?php include("../../partials/head/head-links.html"); ?>

  <style>
    body { background-color: #0b111e; }
    .card { background-color: #111c2d; border: 1px solid #1e293b; border-radius: 12px; }
    .table thead th { background-color: #1e293b; color: #94a3b8; text-transform: uppercase; font-size: 0.7rem; padding: 15px; }
    .table td { vertical-align: middle; border-color: #1e293b; color: #e2e8f0; font-size: 0.85rem; padding: 15px; }
    .vehicle-photo { width: 55px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #334155; }
    .badge-id { background: rgba(56, 189, 248, 0.1); color: #38bdf8; padding: 4px 8px; border-radius: 4px; font-weight: 500; }
    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 8px 12px; border-radius: 8px; border: none; transition: 0.2s; }
    .btn-delete:hover { background: #ef4444; color: white; }
  </style>
</head>

<body class="ds-init" data-sidebar-size="default">
  <div class="wrapper">
    <?php include("../../partials/sidebar-collapse.html"); ?>
  
    <div id="content" class="main-content">
      <?php include("../../partials/topbar-second.html"); ?>
      
      <div class="container-fluid mt-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="text-white mb-1">Gestion du Parc Automobile</h4>
            <p class="text-muted small">Vue complète de la table véhicule</p>
          </div>
          <div class="d-flex gap-2">
            <a href="Gestion_voitureBackend.php" class="btn btn-sm btn-outline-secondary px-3">Actualiser</a>
            <form action="" method="GET" class="input-group input-group-sm" style="width: 250px;">
              <input type="text" name="search_id" class="form-control" placeholder="ID Véhicule...">
              <button type="submit" class="btn btn-primary">Chercher</button>
            </form>
          </div>
        </div>

        <div class="card shadow-lg">
          <div class="table-responsive">
            <table class="table mb-0">
              <thead>
                <tr>
                  <th>ID </th>
                  <th>Photo</th>
                  <th>Marque</th>
                  <th>Immatriculation</th>
                  <th>Kilométrage</th>
                  <th>Date Ajout</th>
                  <th>Client</th>
                  <th>ID Client</th>
                  <th class="text-center">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php 
                if ($listVehicules):
                    $results = $isSearch ? [$listVehicules] : $listVehicules;
                    foreach ($results as $v): 
                        if (empty($v) || !isset($v['idVehicule'])) continue;
                ?>
                <tr>
                  <td><span class="badge-id">#<?php echo $v['idVehicule']; ?></span></td>
                  <td>
                    <img src="/ProjetWeb/assets/img/<?php echo htmlspecialchars($v['imageVoiture']); ?>" class="vehicle-photo shadow-sm">
                  </td>
                  
                  <td class="fw-bold text-white text-uppercase"><?php echo htmlspecialchars($v['marqueV']); ?></td>
                  <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($v['matriculevoiture']); ?></span></td>
                  <td><span class="text-info fw-semibold"><?php echo number_format($v['kilometrageV'], 0, ',', ' '); ?></span> km</td>
                  <td class="text-muted"><?php echo $v['date_ajoutV']; ?></td>
                  <td><?php echo htmlspecialchars($v['nomclient'] ?? 'N/A'); ?></td>
                  <td><small class="text-muted"><?php echo $v['idclient'] ?? '-'; ?></small></td>
                  <td class="text-center">
                    <a href="Gestion_voitureBackend.php?delete_id=<?php echo $v['idVehicule']; ?>" 
                       class="btn-delete" 
                       onclick="return confirm('Confirmer la suppression du véhicule #<?php echo $v['idVehicule']; ?> ?')">
                      <i class="fas fa-trash-alt">Supprimer Véhicule</i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="9" class="text-center py-5 text-muted fst-italic">Aucun enregistrement trouvé.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
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
</body>
</html>