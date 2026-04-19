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

// ── LOGIQUE DE MODIFICATION DU STATUT ────────────────────────────────
if (isset($_POST['update_status']) && isset($_POST['id_rdv']) && isset($_POST['nouveau_statut'])) {
    $id = $_POST['id_rdv'];
    $nouveauStatut = $_POST['nouveau_statut'];
    
    // Récupérer le rendez-vous actuel
    $rdv = $rendezVousC->getRdvById($id);
    if ($rdv) {
        // Modifier uniquement le statut
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
    
    /* Badges pour les statuts */
    .badge-statut {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .statut-confirme { background: rgba(16, 185, 129, 0.2); color: #10b981; }
    .statut-annule { background: rgba(239, 68, 68, 0.2); color: #ef4444; }
    .statut-attente { background: rgba(245, 158, 11, 0.2); color: #f59e0b; }
    .statut-termine { background: rgba(59, 130, 246, 0.2); color: #3b82f6; }
    
    /* Modal styles */
    .modal-custom .modal-content {
        background: #111c2d;
        border: 1px solid #1e293b;
        border-radius: 16px;
    }
    .modal-custom .modal-header {
        border-bottom-color: #1e293b;
        background: #0f172a;
        border-radius: 16px 16px 0 0;
    }
    .modal-custom .modal-footer {
        border-top-color: #1e293b;
        background: #0f172a;
        border-radius: 0 0 16px 16px;
    }
    .modal-custom .modal-title {
        color: white;
    }
    .modal-custom .btn-close {
        filter: invert(1);
    }
    .form-label-modal {
        color: #94a3b8;
        font-weight: 500;
        margin-bottom: 8px;
    }
    .form-select-modal {
        background-color: #1e293b;
        border: 1px solid #334155;
        color: #f1f5f9;
        border-radius: 10px;
        padding: 10px 15px;
    }
    .form-select-modal:focus {
        background-color: #1e293b;
        border-color: #38bdf8;
        box-shadow: 0 0 0 0.2rem rgba(56, 189, 248, 0.25);
    }
    .btn-modal-update {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        border: none;
        border-radius: 10px;
        padding: 8px 25px;
        font-weight: 500;
    }
    .btn-modal-update:hover {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        transform: translateY(-1px);
    }
    .btn-modifier {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
        border: 1px solid rgba(59, 130, 246, 0.3);
        padding: 5px 12px;
        border-radius: 8px;
        font-size: 0.75rem;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }
    .btn-modifier:hover {
        background: #3b82f6;
        color: white;
        cursor: pointer;
    }
    .action-buttons {
        display: flex;
        gap: 8px;
        justify-content: flex-end;
        align-items: center;
    }
    .info-text {
        background: #0f172a;
        padding: 10px;
        border-radius: 10px;
        margin-top: 15px;
        font-size: 0.85rem;
    }
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
                            <th>ID CLIENT</th> 
                            <th>DESCRIPTION</th>
                            <th class="text-end pe-4">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($listRDV)): ?>
                            <?php foreach ($listRDV as $rdv): ?>
                                <tr>
                                    <td class="ps-4">
                                        <span class="text-info">#<?= htmlspecialchars($rdv['idRDV']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($rdv['dateRDV']) ?></td>
                                    <td><?= htmlspecialchars($rdv['heureRDV']) ?></td>
                                    <td><?= htmlspecialchars($rdv['type_serviceRDV']) ?></td>
                                    <td>
                                        <?php
                                        $statut = $rdv['statutRDV'];
                                        $badgeClass = 'statut-attente';
                                        if ($statut == 'confirmé') $badgeClass = 'statut-confirme';
                                        elseif ($statut == 'annulé') $badgeClass = 'statut-annule';
                                        elseif ($statut == 'terminé') $badgeClass = 'statut-termine';
                                        ?>
                                        <span class="badge-statut <?= $badgeClass ?>">
                                            <i class="fas <?= $statut == 'confirmé' ? 'fa-check-circle' : ($statut == 'annulé' ? 'fa-times-circle' : 'fa-clock') ?> me-1"></i>
                                            <?= ucfirst($statut ?? 'En attente') ?>
                                        </span>
                                    </td>
                                    <td><span class="text-white">VEH-<?= htmlspecialchars($rdv['idVehicule']) ?></span></td>
                                    <td><?= htmlspecialchars($rdv['idclientRDV']) ?></td>
                                    <td><?= htmlspecialchars(substr($rdv['descriptionRDV'] ?? 'Aucune description', 0, 30)) ?>...</td>
                                    <td class="text-end pe-4">
                                        <div class="action-buttons">
                                            <!-- Bouton Modifier (ouvre la modale) -->
                                            <button type="button" class="btn-modifier" data-bs-toggle="modal" data-bs-target="#modalStatut<?= $rdv['idRDV'] ?>">
                                                <i class="fas fa-edit me-1"></i> Modifier
                                            </button>
                                            
                                            <!-- Bouton Supprimer -->
                                            <a href="?delete_id=<?= $rdv['idRDV'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Voulez-vous vraiment supprimer le rendez-vous #<?= $rdv['idRDV'] ?> ?')">
                                                <i class="fas fa-trash-alt"> Supprimer</i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- MODALE pour ce rendez-vous -->
                                <div class="modal fade modal-custom" id="modalStatut<?= $rdv['idRDV'] ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-calendar-alt me-2 text-info"></i>
                                                    Modifier le statut - RDV #<?= $rdv['idRDV'] ?>
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id_rdv" value="<?= $rdv['idRDV'] ?>">
                                                    
                                                    <!-- Informations du rendez-vous -->
                                                    <div class="info-text">
                                                        <div class="row mb-2">
                                                            <div class="col-5 text-muted">Date :</div>
                                                            <div class="col-7 text-white"><?= htmlspecialchars($rdv['dateRDV']) ?> à <?= htmlspecialchars($rdv['heureRDV']) ?></div>
                                                        </div>
                                                        <div class="row mb-2">
                                                            <div class="col-5 text-muted">Service :</div>
                                                            <div class="col-7 text-white"><?= htmlspecialchars($rdv['type_serviceRDV']) ?></div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-5 text-muted">Client :</div>
                                                            <div class="col-7 text-white">#<?= htmlspecialchars($rdv['idclientRDV']) ?></div>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- Sélection du nouveau statut -->
                                                    <div class="mt-3">
                                                        <label class="form-label-modal d-block mb-2">
                                                            <i class="fas fa-tag me-1"></i> Nouveau statut :
                                                        </label>
                                                        <select name="nouveau_statut" class="form-select-modal w-100" required>
                                                            <option value="en attente" <?= $rdv['statutRDV'] == 'en attente' ? 'selected' : '' ?>>En attente</option>

                                                            <option value="confirmé" <?= $rdv['statutRDV'] == 'confirmé' ? 'selected' : '' ?>> Confirmé</option>
                                                            <option value="annulé" <?= $rdv['statutRDV'] == 'annulé' ? 'selected' : '' ?>> Annulé</option>
                                                            <option value="terminé" <?= $rdv['statutRDV'] == 'terminé' ? 'selected' : '' ?>> Terminé</option>
                                                        </select>
                                                    </div>
                                                    
                                                    <!-- Statut actuel -->
                                                    <div class="mt-3 pt-2 text-center">
                                                        <small class="text-muted">
                                                            Statut actuel : 
                                                            <span class="badge-statut <?= $badgeClass ?>">
                                                                <?= ucfirst($rdv['statutRDV'] ?? 'En attente') ?>
                                                            </span>
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
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="9" class="text-center py-5 text-muted">Aucun rendez-vous trouvé.</td></tr>
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