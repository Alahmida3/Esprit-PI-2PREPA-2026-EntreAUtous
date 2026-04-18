<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestion des Rendez-vous - Dasher Admin</title>

  <link rel="stylesheet" href="../../assets/BackOffice/css/theme.css">
  
  <?php include("../../partials/head/head-meta.html"); ?>
  <?php include("../../partials/head/head-links.html"); ?>

  <link rel="stylesheet" href="../../node_modules/swiper/swiper-bundle.min.css" />
</head>

<body class="ds-init" data-sidebar-size="default">
  <div class="wrapper">
    <?php include("../../partials/sidebar-collapse.html"); ?>

    <div id="content" class="main-content">
      <?php include("../../partials/topbar-second.html"); ?>
      
      <div class="custom-container">
        <div class="row mb-6 g-6">
          <div class="col-12">
            <div class="card card-lg">
              <div class="card-header border-bottom">
                <div class="d-flex align-items-center gap-3">
                  <div class="icon-shape icon-lg rounded-3 bg-primary-subtle text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                      <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                      <line x1="16" y1="2" x2="16" y2="6"></line>
                      <line x1="8" y1="2" x2="8" y2="6"></line>
                      <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                  </div>
                  <div>
                    <h5 class="mb-0">Programmer un Rendez-vous</h5>
                    <small class="text-muted">Saisissez les détails de l'intervention</small>
                  </div>
                </div>
              </div>
              
              <div class="card-body">
                <form id="formRDV" method="POST" action="">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label for="id_rdv" class="form-label">ID Rendez-vous <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="id_rdv" name="id_rdv" placeholder="ex: RDV-2024-001" required />
                    </div>
                    <div class="col-md-6">
                      <label for="id_vehicule" class="form-label">ID Véhicule <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="id_vehicule" name="id_vehicule" placeholder="Sélectionnez ou saisissez l'ID" required />
                    </div>
                    <div class="col-md-6">
                      <label for="date_rdv" class="form-label">Date du RDV <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="date_rdv" name="date_rdv" required />
                    </div>
                    <div class="col-md-6">
                      <label for="heure_rdv" class="form-label">Heure du RDV <span class="text-danger">*</span></label>
                      <input type="time" class="form-control" id="heure_rdv" name="heure_rdv" required />
                    </div>
                    <div class="col-md-6">
                      <label for="type_service" class="form-label">Type de Service <span class="text-danger">*</span></label>
                      <select class="form-select" id="type_service" name="type_service" required>
                        <option value="" selected disabled>Choisir un service...</option>
                        <option value="Réparation">Réparation</option>
                        <option value="Entretien">Entretien</option>
                        <option value="Diagnostic">Diagnostic</option>
                        <option value="Carrosserie">Carrosserie</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label for="statut" class="form-label">Statut <span class="text-danger">*</span></label>
                      <select class="form-select" id="statut" name="statut" required>
                        <option value="en attente">En attente</option>
                        <option value="confirmé">Confirmé</option>
                        <option value="annulé">Annulé</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label for="description" class="form-label">Description / Notes</label>
                      <textarea class="form-control" id="description" name="description" rows="3" placeholder="Détails supplémentaires sur le problème ou l'intervention..."></textarea>
                    </div>
                  </div>

                  <div id="formMsg" class="mt-4" style="display:none;"></div>

                  <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('formRDV').reset();">
                      Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                        <path d="M12 5v14M5 12h14" />
                      </svg>
                      Enregistrer le RDV
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php include("../../partials/scripts.html"); ?>
  <script src="../../assets/BackOffice/js/vendors/sidebarnav.js"></script>
  <script src="../../node_modules/jsvectormap/dist/js/jsvectormap.min.js"></script>
  <script src="../../node_modules/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../../node_modules/choices.js/public/assets/scripts/choices.min.js"></script>
  <script src="../../assets/BackOffice/js/vendors/choice.js"></script>
</body>

</html>