<!DOCTYPE html>
<html lang="fr">

<head>
  <?php
  /* VIEW ONLY — le formulaire soumet vers entretien_action.php */
  require_once '../../config.php';
  require_once '../../controller/EntretienController.php';

  if (!$pdo) die("Erreur de connexion à la base de données.");

  $controller       = new EntretienController($pdo);
  $matricules       = $controller->getAllMatricules();
  $error            = '';
  $entretien        = null;
  $validationErrors = [];

  // Chargement de l'entretien à afficher (lecture seule)
  if (isset($_GET['id'])) {
      $entretien = $controller->getEntretien((int)$_GET['id']);
      if (!$entretien) $error = "Entretien non trouvé.";
  } else {
      $error = "ID d'entretien manquant.";
  }

  // Récupération des erreurs/valeurs depuis la session (retour du contrôleur)
  session_start();
  $validationErrors = $_SESSION['entretien_errors'] ?? [];
  $old              = $_SESSION['entretien_post']   ?? [];
  unset($_SESSION['entretien_errors'], $_SESSION['entretien_post']);

  // Si retour avec erreurs, on ré-affiche les valeurs soumises plutôt que celles de la BDD
  if (!empty($old)) $entretien = array_merge($entretien ?? [], $old);

  // Helper : retourne 'is-invalid' si le champ est mentionné dans les erreurs
  function fieldInvalid($errors, $keyword) {
      foreach ($errors as $e) {
          if (mb_stripos($e, $keyword) !== false) return 'is-invalid';
      }
      return '';
  }
  ?>
  <?php include '../../partials/back/head/head-meta.html'; ?>
  <title>Modifier un Entretien - Dasher</title>
  <?php include '../../partials/back/head/head-links.html'; ?>
</head>

<body>
  <div>
    <?php include '../../partials/back/sidebar-collapse.html'; ?>

    <div id="content" class="position-relative h-100">
      <?php include '../../partials/back/topbar-second.html'; ?>

      <div class="custom-container">

        <!-- Breadcrumb -->
        <div class="row mb-4">
          <div class="col-12">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../../index.php" class="text-muted">Accueil</a></li>
                <li class="breadcrumb-item"><a href="listeentretiens.php" class="text-muted">Entretiens</a></li>
                <li class="breadcrumb-item active" aria-current="page">Modifier un entretien</li>
              </ol>
            </nav>
          </div>
        </div>

        <?php if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
        <?php if (!empty($validationErrors)): ?>
          <div class="alert alert-danger">
            <strong>Erreurs de validation :</strong>
            <ul class="mb-0 mt-2">
              <?php foreach ($validationErrors as $ve): ?>
                <li><?php echo htmlspecialchars($ve); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="row g-6">

          <!-- Colonne formulaire -->
          <div class="col-xl-8 col-12">
            <div class="card card-lg">
              <div class="card-body">

                <!-- En-tête -->
                <div class="d-flex align-items-center gap-3 mb-6 pb-5 border-bottom">
                  <div class="icon-shape icon-lg rounded-circle bg-warning-subtle text-warning">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1" />
                      <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z" />
                      <path d="M16 5l3 3" />
                    </svg>
                  </div>
                  <div>
                    <h5 class="mb-0">Modifier un entretien</h5>
                    <small class="text-muted">Modifiez les informations ci-dessous</small>
                  </div>
                </div>

                <?php if ($entretien): ?>
                <form action="../../controller/entretien_action.php?action=update" method="POST" id="entretienForm">

                  <!-- ID Entretien (caché, readonly) -->
                  <input type="hidden" name="id_entretien" value="<?php echo htmlspecialchars($entretien['id_entretien']); ?>">

                  <div class="mb-5">
                    <label class="form-label fw-medium">ID Entretien</label>
                    <input type="text" class="form-control bg-light" value="#<?php echo htmlspecialchars($entretien['id_entretien']); ?>" readonly>
                  </div>

                  <!-- Matricule — liste déroulante -->
                  <div class="mb-5">
                    <label for="Matricule" class="form-label fw-medium">
                      Matricule du véhicule <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php echo fieldInvalid($validationErrors, 'matricule'); ?>" id="Matricule" name="Matricule">
                      <option value="">-- Sélectionner un véhicule --</option>
                      <?php foreach ($matricules as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['Matricule']); ?>"
                          <?php echo ($entretien['Matricule'] === $m['Matricule']) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($m['Matricule']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- Date entretien -->
                  <div class="mb-5">
                    <label for="date_entretien" class="form-label fw-medium">
                      Date entretien <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'date'); ?>" id="date_entretien" name="date_entretien"
                      value="<?php echo htmlspecialchars($entretien['date_entretien']); ?>">
                  </div>

                  <!-- Kilométrage -->
                  <div class="mb-5">
                    <label for="kilometrage" class="form-label fw-medium">
                      Kilométrage <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'km'); ?>" id="kilometrage" name="kilometrage"
                      value="<?php echo htmlspecialchars($entretien['kilometrage']); ?>"
                      placeholder="Ex: 120000">
                  </div>

                  <!-- Type intervention -->
                  <div class="mb-5">
                    <label for="type_intervention" class="form-label fw-medium">Type d'intervention</label>
                    <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'intervention'); ?>" id="type_intervention" name="type_intervention"
                      value="<?php echo htmlspecialchars($entretien['type_intervention'] ?? ''); ?>">
                  </div>

                  <!-- Observations -->
                  <div class="mb-5">
                    <label for="observations" class="form-label fw-medium">Observations</label>
                    <textarea class="form-control <?php echo fieldInvalid($validationErrors, 'observation'); ?>" id="observations" name="observations" rows="4"><?php echo htmlspecialchars($entretien['observations'] ?? ''); ?></textarea>
                  </div>

                  <!-- Statut -->
                  <div class="mb-5">
                    <label for="statut" class="form-label fw-medium">
                      Statut <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php echo fieldInvalid($validationErrors, 'statut'); ?>" id="statut" name="statut">
                      <option value="">-- Sélectionner --</option>
                      <option value="planifie" <?php echo ($entretien['statut'] === 'planifie') ? 'selected' : ''; ?>>Planifié</option>
                      <option value="en_cours" <?php echo ($entretien['statut'] === 'en_cours') ? 'selected' : ''; ?>>En cours</option>
                      <option value="termine"  <?php echo ($entretien['statut'] === 'termine')  ? 'selected' : ''; ?>>Terminé</option>
                      <option value="annule"   <?php echo ($entretien['statut'] === 'annule')   ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                  </div>

                  <!-- Prochaine échéance -->
                  <div class="mb-5">
                    <label for="prochaine_echeance" class="form-label fw-medium">Prochaine échéance</label>
                    <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'prochaine'); ?>" id="prochaine_echeance" name="prochaine_echeance"
                      value="<?php echo htmlspecialchars($entretien['prochaine_echeance'] ?? ''); ?>">
                  </div>

                  <!-- KM prochain -->
                  <div class="mb-6">
                    <label for="km_prochain" class="form-label fw-medium">Kilométrage prochaine visite</label>
                    <input type="text" class="form-control <?php echo fieldInvalid($validationErrors, 'prochain'); ?>" id="km_prochain" name="km_prochain"
                      value="<?php echo htmlspecialchars($entretien['km_prochain'] ?? ''); ?>"
                      placeholder="Ex: 150000">
                  </div>

                  <!-- Boutons -->
                  <div class="d-flex flex-wrap gap-3 pt-4 border-top">
                    <button type="submit" name="submit_update" class="btn btn-warning d-flex align-items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 12l5 5l10 -10" />
                      </svg>
                      Modifier
                    </button>
                    <a href="listeentretiens.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M18 6l-12 12" /><path d="M6 6l12 12" />
                      </svg>
                      Annuler
                    </a>
                  </div>

                  <!-- Retour liste -->
                  <div class="mt-5 pt-2">
                    <a href="listeentretiens.php" class="d-flex align-items-center gap-2 text-muted small text-decoration-none">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" />
                      </svg>
                      Retourner à la liste des entretiens
                    </a>
                  </div>

                </form>
                <?php endif; ?>

              </div>
            </div>
          </div>

          <!-- Colonne aide -->
          <div class="col-xl-4 col-12">
            <div class="card card-lg mb-5">
              <div class="card-body">
                <h6 class="mb-4 d-flex align-items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                    <path d="M12 8h.01" /><path d="M11 12h1v4h1" />
                  </svg>
                  Guide de modification
                </h6>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                  <li class="d-flex align-items-start gap-2">
                    <span class="badge bg-primary-subtle text-primary mt-1">1</span>
                    <span class="small text-muted">Modifiez les informations nécessaires</span>
                  </li>
                  <li class="d-flex align-items-start gap-2">
                    <span class="badge bg-primary-subtle text-primary mt-1">2</span>
                    <span class="small text-muted">Vérifiez que tous les champs requis sont remplis</span>
                  </li>
                  <li class="d-flex align-items-start gap-2">
                    <span class="badge bg-primary-subtle text-primary mt-1">3</span>
                    <span class="small text-muted">Cliquez sur "Modifier" pour sauvegarder</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="card card-lg">
              <div class="card-body">
                <h6 class="mb-4">Légende des statuts</h6>
                <div class="d-flex flex-column gap-3">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-warning-emphasis bg-warning-subtle">Planifié</span>
                    <span class="small text-muted">Programmé, pas encore eu lieu</span>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-info-emphasis bg-info-subtle">En cours</span>
                    <span class="small text-muted">En train de se dérouler</span>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-success-emphasis bg-success-subtle">Terminé</span>
                    <span class="small text-muted">Effectué avec succès</span>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-danger-emphasis bg-danger-subtle">Annulé</span>
                    <span class="small text-muted">Annulé ou reporté</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>

      </div>
    </div>
  </div>

  <?php include '../../partials/back/scripts.html'; ?>
  <script src="../../assets/back/js/vendors/sidebarnav.js"></script>

  
  <script>
  document.addEventListener('DOMContentLoaded', function () {
    const alertErr = document.querySelector('.alert-danger');
    if (alertErr) {
      alertErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
      alertErr.focus();
    }
  });
  </script>
</body>
</html>