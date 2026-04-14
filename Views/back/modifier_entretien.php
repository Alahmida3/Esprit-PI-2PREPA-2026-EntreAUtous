<!DOCTYPE html>
<html lang="fr">
 
<head>
  <?php
  // Afficher les erreurs
  ini_set('display_errors', 1);
  error_reporting(E_ALL);
  
  require_once '../../config.php';
  require_once '../../controller/EntretienController.php';
  require_once '../../Models/Entretien.php';

  $error = '';
  $entretien = null;
  
  if (!$pdo) {
      $error = "Erreur de connexion à la base de données. Vérifiez config.php.";
  } else {
      $controller = new EntretienController($pdo);

      // Récupérer l'entretien à modifier
      if (isset($_GET['id'])) {
          $entretien = $controller->getEntretien($_GET['id']);
          if (!$entretien) {
              $error = "Entretien non trouvé.";
          }
      } else {
          $error = "ID d'entretien manquant.";
      }

      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_update'])) {
          try {
              $entretienObj = new Entretien(
                  $_POST['id_entretien'],
                  $_POST['id_voiture'],
                  $_POST['date_entretien'],
                  $_POST['kilometrage'],
                  $_POST['type_intervention'] ?? '',
                  $_POST['observations'] ?? '',
                  $_POST['statut'],
                  $_POST['prochaine_echeance'] ?? '',
                  $_POST['km_prochain'] ?? ''
              );

              if ($controller->updateEntretien($entretienObj)) {
                  header('Location: listeentretiens.php?updated=1');
                  exit;
              } else {
                  $error = "Erreur lors de la modification.";
              }
          } catch (Exception $e) {
              $error = "Erreur : " . $e->getMessage();
          }
      }
  }
  ?>
  <?php include '../../partials/back/head/head-meta.html'; ?>
  <title>Modifier un Entretien - Dasher</title>
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
                <li class="breadcrumb-item">
                  <a href="listeentretiens.php" class="text-muted">Entretiens</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Modifier un entretien</li>
              </ol>
            </nav>
          </div>
        </div>

        <?php if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>

        <!-- Contenu principal -->
        <div class="row g-6">
 
          <!-- Colonne formulaire -->
          <div class="col-xl-8 col-12">
            <div class="card card-lg">
              <div class="card-body">
 
                <!-- En-tête -->
                <div class="d-flex align-items-center gap-3 mb-6 pb-5 border-bottom">
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
                    <h5 class="mb-0">Modifier un entretien</h5>
                    <small class="text-muted">Modifiez les informations ci-dessous</small>
                  </div>
                </div>

                <?php if ($entretien): ?>
                <form action="#" method="POST" id="entretienForm">
                    
  <div class="mb-5">
    <label for="id_entretien" class="form-label fw-medium">
      ID Entretien <span class="text-danger">*</span>
    </label>
    <input type="text" class="form-control" id="id_entretien" name="id_entretien" value="<?php echo htmlspecialchars($entretien['id_entretien']); ?>" readonly>
  </div>

  <!-- ID voiture -->
  <div class="mb-5">
    <label for="id_voiture" class="form-label fw-medium">
      ID Voiture <span class="text-danger">*</span>
    </label>
    <input type="text" class="form-control" id="id_voiture" name="id_voiture" value="<?php echo htmlspecialchars($entretien['id_voiture']); ?>">
  </div>

  <!-- Date entretien -->
  <div class="mb-5">
    <label for="date_entretien" class="form-label fw-medium">
      Date entretien <span class="text-danger">*</span>
    </label>
    <input type="text" class="form-control" id="date_entretien" name="date_entretien" value="<?php echo htmlspecialchars($entretien['date_entretien']); ?>" placeholder="YYYY-MM-DD">
  </div>

  <!-- Kilométrage -->
  <div class="mb-5">
    <label for="kilometrage" class="form-label fw-medium">
      Kilométrage <span class="text-danger">*</span>
    </label>
    <input type="text" class="form-control" id="kilometrage" name="kilometrage" value="<?php echo htmlspecialchars($entretien['kilometrage']); ?>">
  </div>

  <!-- Type intervention -->
  <div class="mb-5">
    <label for="type_intervention" class="form-label fw-medium">
      Type d'intervention
    </label>
    <input type="text" class="form-control" id="type_intervention" name="type_intervention" value="<?php echo htmlspecialchars($entretien['type_intervention'] ?? ''); ?>">
  </div>

  <!-- Observations -->
  <div class="mb-5">
    <label for="observations" class="form-label fw-medium">
      Observations
    </label>
    <textarea class="form-control" id="observations" name="observations" rows="4"><?php echo htmlspecialchars($entretien['observations'] ?? ''); ?></textarea>
  </div>

  <!-- Statut -->
  <div class="mb-5">
    <label for="statut" class="form-label fw-medium">
      Statut <span class="text-danger">*</span>
    </label>
    <select class="form-select" id="statut" name="statut">
      <option value="">-- Sélectionner --</option>
      <option value="planifie" <?php echo ($entretien['statut'] == 'planifie') ? 'selected' : ''; ?>>Planifié</option>
      <option value="en_cours" <?php echo ($entretien['statut'] == 'en_cours') ? 'selected' : ''; ?>>En cours</option>
      <option value="termine" <?php echo ($entretien['statut'] == 'termine') ? 'selected' : ''; ?>>Terminé</option>
      <option value="annule" <?php echo ($entretien['statut'] == 'annule') ? 'selected' : ''; ?>>Annulé</option>
    </select>
  </div>

  <!-- Prochaine échéance -->
  <div class="mb-5">
    <label for="prochaine_echeance" class="form-label fw-medium">
      Prochaine échéance
    </label>
    <input type="text" class="form-control" id="prochaine_echeance" name="prochaine_echeance" value="<?php echo htmlspecialchars($entretien['prochaine_echeance'] ?? ''); ?>" placeholder="YYYY-MM-DD">
  </div>

  <!-- KM prochain -->
  <div class="mb-6">
    <label for="km_prochain" class="form-label fw-medium">
      Kilométrage prochaine visite
    </label>
    <input type="text" class="form-control" id="km_prochain" name="km_prochain" value="<?php echo htmlspecialchars($entretien['km_prochain'] ?? ''); ?>">
  </div>
 
                  <!-- Boutons -->
<div class="d-flex flex-wrap gap-3 pt-4 border-top">
        <button type="submit" name="submit_update" class="btn btn-primary d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M5 12l5 5l10 -10" />
            </svg>
            Modifier
        </button>
                    <a href="listeentretiens.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M18 6l-12 12" />
                        <path d="M6 6l12 12" />
                      </svg>
                      Annuler
                    </a>
                  </div>
 
                  <!-- Retour liste -->
                  <div class="mt-5 pt-2">
                    <a href="listeentretiens.php"
                      class="d-flex align-items-center gap-2 text-muted small text-decoration-none">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M5 12l14 0" />
                        <path d="M5 12l6 6" />
                        <path d="M5 12l6 -6" />
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
 
            <!-- Guide -->
            <div class="card card-lg mb-5">
              <div class="card-body">
                <h6 class="mb-4 d-flex align-items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"
                    class="text-primary">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                    <path d="M12 8h.01" />
                    <path d="M11 12h1v4h1" />
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
 
            <!-- Légende statuts -->
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
        </div><!-- end row -->
 
      </div><!-- end custom-container -->
    </div><!-- end #content -->
  </div>
 
  <?php include '../../partials/back/scripts.html'; ?>
  <script src="../../assets/js/vendors/sidebarnav.js"></script>
  
  <!-- Validation JavaScript -->
  <script>
    document.getElementById('entretienForm').addEventListener('submit', function(e) {
        let isValid = true;
        let errorMessages = [];

        // Validation ID Voiture
        const idVoiture = document.getElementById('id_voiture').value.trim();
        if (!idVoiture || isNaN(idVoiture) || idVoiture <= 0) {
            isValid = false;
            errorMessages.push('L\'ID Voiture doit être un nombre positif.');
            document.getElementById('id_voiture').style.borderColor = 'red';
        } else {
            document.getElementById('id_voiture').style.borderColor = '';
        }

        // Validation Date entretien
        const dateEntretien = document.getElementById('date_entretien').value;
        if (!dateEntretien) {
            isValid = false;
            errorMessages.push('La date d\'entretien est obligatoire.');
            document.getElementById('date_entretien').style.borderColor = 'red';
        } else {
            document.getElementById('date_entretien').style.borderColor = '';
        }

        // Validation Kilométrage
        const kilometrage = document.getElementById('kilometrage').value.trim();
        if (!kilometrage || isNaN(kilometrage) || kilometrage < 0) {
            isValid = false;
            errorMessages.push('Le kilométrage doit être un nombre positif ou nul.');
            document.getElementById('kilometrage').style.borderColor = 'red';
        } else {
            document.getElementById('kilometrage').style.borderColor = '';
        }

        // Validation Statut
        const statut = document.getElementById('statut').value;
        if (!statut) {
            isValid = false;
            errorMessages.push('Le statut est obligatoire.');
            document.getElementById('statut').style.borderColor = 'red';
        } else {
            document.getElementById('statut').style.borderColor = '';
        }

        // Validation KM prochain (si rempli)
        const kmProchain = document.getElementById('km_prochain').value.trim();
        if (kmProchain && (isNaN(kmProchain) || kmProchain < 0)) {
            isValid = false;
            errorMessages.push('Le kilométrage prochain doit être un nombre positif.');
            document.getElementById('km_prochain').style.borderColor = 'red';
        } else {
            document.getElementById('km_prochain').style.borderColor = '';
        }

        // Afficher les erreurs
        const existingError = document.querySelector('.validation-errors');
        if (existingError) {
            existingError.remove();
        }

        if (!isValid) {
            e.preventDefault();
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger validation-errors';
            errorDiv.innerHTML = '<strong>Erreurs de validation :</strong><ul>' + 
                errorMessages.map(msg => '<li>' + msg + '</li>').join('') + '</ul>';
            document.querySelector('.card-body').insertBefore(errorDiv, document.querySelector('form'));
        }
    });

    // Validation en temps réel
    document.getElementById('id_voiture').addEventListener('input', function() {
        const value = this.value.trim();
        if (!value || isNaN(value) || value <= 0) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });

    document.getElementById('kilometrage').addEventListener('input', function() {
        const value = this.value.trim();
        if (!value || isNaN(value) || value < 0) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });

    document.getElementById('km_prochain').addEventListener('input', function() {
        const value = this.value.trim();
        if (value && (isNaN(value) || value < 0)) {
            this.style.borderColor = 'red';
        } else {
            this.style.borderColor = '';
        }
    });
  </script>
</body>
</html>