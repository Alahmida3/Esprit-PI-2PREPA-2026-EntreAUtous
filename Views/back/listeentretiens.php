<!DOCTYPE html>
<html lang="fr">
 
<head>
  <?php
  require_once '../../config.php';
  require_once '../../controller/EntretienController.php';
  require_once '../../Models/Entretien.php';

  if (!$pdo) {
      die("Erreur de connexion à la base de données. Vérifiez config.php.");
  }

  $controller = new EntretienController($pdo);

  // Traitement de la suppression
  if (isset($_GET['delete'])) {
      $id = $_GET['delete'];
      if ($controller->deleteEntretien($id)) {
          header('Location: listeentretiens.php?deleted=1');
          exit;
      } else {
          $error = "Erreur lors de la suppression.";
      }
  }

  // Charger la liste
  $list = $controller->listEntretiens();

  $message = '';
  if (isset($_GET['success'])) {
      $message = '<div class="alert alert-success">Entretien ajouté avec succès.</div>';
  } elseif (isset($_GET['updated'])) {
      $message = '<div class="alert alert-success">Entretien modifié avec succès.</div>';
  } elseif (isset($_GET['deleted'])) {
      $message = '<div class="alert alert-success">Entretien supprimé avec succès.</div>';
  } elseif (isset($error)) {
      $message = '<div class="alert alert-danger">' . $error . '</div>';
  }

  $message = '';
  if (isset($_GET['success'])) {
      $message = '<div class="alert alert-success">Entretien ajouté avec succès.</div>';
  } elseif (isset($_GET['deleted'])) {
      $message = '<div class="alert alert-success">Entretien supprimé avec succès.</div>';
  }
  ?>
  <?php include '../../partials/back/head/head-meta.html'; ?>
  <title>Liste des Entretiens - Dasher</title>
  <?php include '../../partials/back/head/head-links.html'; ?>
</head>


<body>
  <div>
    <?php include '../../partials/back/sidebar-collapse.html'; ?>

    <!-- Main Content -->
    <div id="content" class="position-relative h-100">
      <?php include '../../partials/back/topbar-second.html'; ?>

      <!-- container -->
      <div class="custom-container">

        <!-- Breadcrumb -->
        <div class="row mb-4">
          <div class="col-12">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item">
                  <a href="../../index.php" class="text-muted">Accueil</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Entretiens</li>
              </ol>
            </nav>
          </div>
        </div>

        <?php if ($message) echo $message; ?>

        <!-- Contenu principal -->
        <div class="row mb-6 g-6">
          <div class="col-12">
            <div class="card card-lg">
              <div class="card-body">
 
                <!-- Titre + Bouton Ajouter -->
                <div class="d-flex justify-content-between align-items-center mb-6">
                  <div class="d-flex align-items-center gap-3">
                    <div class="icon-shape icon-lg rounded-circle bg-primary-darker text-primary-lighter">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-tools">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4" />
                        <path d="M14.5 5.5l4 4" />
                        <path d="M12 8l-5 -5l-4 4l5 5" />
                        <path d="M7 8l-1.5 1.5" />
                        <path d="M16 12l5 5l-4 4l-5 -5" />
                        <path d="M16 17l-1.5 1.5" />
                      </svg>
                    </div>
                    <div>
                      <h5 class="mb-0">Liste des Entretiens</h5>
                      <small class="text-muted">Gérer tous les entretiens</small>
                    </div>
                  </div>
                  <a href="ajouter_entretien.php" class="btn btn-primary d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <path d="M12 5l0 14" />
                      <path d="M5 12l14 0" />
                    </svg>
                    Ajouter un entretien
                  </a>
                </div>
 
                <!-- Filtres / Recherche -->
                <div class="row g-3 mb-5">
                  <div class="col-md-4">
                    <div class="input-group">
                      <span class="input-group-text bg-transparent border-end-0">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                          stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                          <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                          <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                          <path d="M21 21l-6 -6" />
                        </svg>
                      </span>
                      <input type="text" class="form-control border-start-0" placeholder="Rechercher...">
                    </div>
                  </div>
                  <div class="col-md-3">
                    <select class="form-select">
                      <option value="">Tous les statuts</option>
                      <option value="planifie">Planifié</option>
                      <option value="en_cours">En cours</option>
                      <option value="termine">Terminé</option>
                      <option value="annule">Annulé</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <input type="date" class="form-control">
                  </div>
                </div>
 
                <!-- Tableau -->
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
  <tr>
    <th>#</th>
    <th>ID Voiture</th>
    <th>Date entretien</th>
    <th>Kilométrage</th>
    <th>Type intervention</th>
    <th>Statut</th>
    <th>Prochaine échéance</th>
    <th>KM prochain</th>
    <th class="text-end">Actions</th>
  </tr>
</thead>

<tbody>

<tbody>
<?php foreach ($list as $row) { ?>
<tr>
    <td>#<?php echo $row['id_entretien']; ?></td>
    <td><?php echo $row['id_voiture']; ?></td>
    <td><?php echo $row['date_entretien']; ?></td>
    <td><?php echo $row['kilometrage']; ?></td>
    <td><?php echo $row['type_intervention']; ?></td>
    <td>
        <span class="badge bg-success">
            <?php echo $row['statut']; ?>
        </span>
    </td>
    <td><?php echo $row['prochaine_echeance']; ?></td>
    <td><?php echo $row['km_prochain']; ?></td>
    <td class="text-end">
        <a href="modifier_entretien.php?id=<?php echo $row['id_entretien']; ?>" 
           class="btn btn-sm btn-primary me-2">
           Modifier
        </a>
        <a href="?delete=<?php echo $row['id_entretien']; ?>" 
           class="btn btn-sm btn-danger"
           onclick="return confirm('Supprimer cet entretien ?')">
           Supprimer
        </a>
    </td>
</tr>
<?php } ?>
</tbody>
                  </table>
                </div>
 
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-5 pt-4 border-top">
                  <small class="text-muted">Affichage de 1 à 4 sur 4 entretiens</small>
                  <nav>
                    <ul class="pagination pagination-sm mb-0">
                      <li class="page-item disabled"><a class="page-link" href="#">Précédent</a></li>
                      <li class="page-item active"><a class="page-link" href="#">1</a></li>
                      <li class="page-item"><a class="page-link" href="#">Suivant</a></li>
                    </ul>
                  </nav>
                </div>
 
              </div>
            </div>
          </div>
        </div>
 
      </div><!-- end custom-container -->
    </div><!-- end #content -->
  </div>
 
  <!-- Modal Confirmation Suppression -->
  <div class="modal fade" id="modalSuppression" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title">Confirmer la suppression</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body text-center py-5">
          <div class="icon-shape icon-xl rounded-circle bg-danger-subtle text-danger mx-auto mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path stroke="none" d="M0 0h24v24H0z" fill="none" />
              <path d="M12 9v4" />
              <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" />
              <path d="M12 16h.01" />
            </svg>
          </div>
          <h6 class="mb-2">Êtes-vous sûr ?</h6>
          <p class="text-muted mb-0">Cette action est irréversible.</p>
        </div>
        <div class="modal-footer border-0 pt-0 justify-content-center gap-3">
          <button type="button" class="btn btn-outline-secondary px-5" data-bs-dismiss="modal">Annuler</button>
          <button type="button" class="btn btn-danger px-5">Supprimer</button>
        </div>
      </div>
    </div>
  </div>
 
  <?php include '../../partials/back/scripts.html'; ?>
  <script src="../../assets/js/vendors/sidebarnav.js"></script>
  <script>
    function confirmerSuppression(id) {
      const modal = new bootstrap.Modal(document.getElementById('modalSuppression'));
      modal.show();
    }
  </script>
</body>
</html>