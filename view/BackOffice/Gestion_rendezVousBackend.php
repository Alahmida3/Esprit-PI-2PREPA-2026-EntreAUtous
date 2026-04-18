<?php
require_once "../../config.php";
require_once "../../controller/RendezVous.php";

$rendezVousC = new RendezVousC();

// ── LOGIQUE DE SUPPRESSION ──────────────────────────────────────────
if (isset($_GET['delete_id'])) {
    $rendezVousC->delete($_GET['delete_id']);
    header('Location: Gestion_rendezVousBackend.php');
    exit();
}

// ── RÉCUPÉRATION DES DONNÉES ────────────────────────────────────────
$listRDV = null;
if (isset($_GET['search_id']) && !empty($_GET['search_id'])) {
    $res = $rendezVousC->getRdvById($_GET['search_id']);
    $listRDV = $res ? [$res] : [];
} else {
    $listRDV = $rendezVousC->getAll(); 
}
?>

<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
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
    .badge-id { background: rgba(56, 189, 248, 0.1); color: #38bdf8; padding: 4px 8px; border-radius: 4px; }
    .btn-delete { color: #ef4444; background: rgba(239, 68, 68, 0.1); padding: 8px 12px; border-radius: 8px; transition: 0.2s; }
    .btn-delete:hover { background: #ef4444; color: white; }
  </style>
</head>

<body>
  <div class="wrapper">
    <?php include("../../partials/sidebar-collapse.html"); ?>
  
    <div id="content" class="main-content">
      <?php include("../../partials/topbar-second.html"); ?>
      
      <div class="container-fluid mt-4 px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
          <div>
            <h4 class="text-white mb-1">Planning des Rendez-vous</h4>
            <p class="text-muted small">Gestion complète des interventions</p>
          </div>
          
          <div class="d-flex gap-2">
            <a href="Gestion_rendezVousBackend.php" class="btn btn-outline-secondary btn-sm px-3">Actualiser</a>
            <form action="" method="GET" class="input-group input-group-sm" style="width: 280px;">
              <input type="text" name="search_id" class="form-control" placeholder="Rechercher ID RDV..." value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>">
              <button type="submit" class="btn btn-primary">Chercher</button>
            </form>
          </div>
        </div>

        <div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-4">ID RDV</th>
                    <th>DATE</th>
                    <th>HEURE</th>
                    <th>SERVICE</th>
                    <th>STATUT</th>
                    <th>ID VÉHICULE</th>
                    <th>ID CLIENT</th> <th class="text-end pe-4">ACTION</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($listRDV)): ?>
                    <?php foreach ($listRDV as $rdv): ?>
                        <?php $rendezVousC->afficher($rdv); ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="8" class="text-center py-5 text-muted">Aucun rendez-vous trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
      </div>
    </div>
  </div>

  <?php include("../../partials/scripts.html"); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>