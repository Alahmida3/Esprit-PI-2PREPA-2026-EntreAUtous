<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Gestion de Véhicules - Dasher Admin</title>

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
                      <rect x="1" y="3" width="15" height="13" rx="2" />
                      <path d="M16 8h4l3 3v5h-7V8z" />
                      <circle cx="5.5" cy="18.5" r="2.5" />
                      <circle cx="18.5" cy="18.5" r="2.5" />
                    </svg>
                  </div>
                  <div>
                    <h5 class="mb-0">Ajouter un véhicule</h5>
                    <small class="text-muted">Remplissez les informations du véhicule</small>
                  </div>
                </div>
              </div>
              <div class="card-body">
                <form id="formVehicule" method="POST" action="">
                  <div class="row g-4">
                    <div class="col-md-6">
                      <label for="id_vehicule" class="form-label">ID Véhicule <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="id_vehicule" name="id_vehicule" placeholder="ex: VEH-001" required />
                    </div>
                    <div class="col-md-6">
                      <label for="marque" class="form-label">Marque <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="marque" name="marque" placeholder="ex: Peugeot, BMW, Toyota..." required />
                    </div>
                    <div class="col-md-6">
                      <label for="immatriculation" class="form-label">Immatriculation <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="immatriculation" name="immatriculation" placeholder="ex: AB-123-CD" oninput="this.value = this.value.toUpperCase()" required />
                    </div>
                    <div class="col-md-6">
                      <label for="date_ajout" class="form-label">Date d'ajout <span class="text-danger">*</span></label>
                      <input type="date" class="form-control" id="date_ajout" name="date_ajout" required />
                    </div>
                    <div class="col-md-6">
                      <label for="kilometrage" class="form-label">Kilométrage <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="kilometrage" name="kilometrage" placeholder="ex: 45000" min="0" required />
                    </div>
                    <div class="col-md-6">
                      <label for="id_client" class="form-label">ID Client <span class="text-muted fw-normal">(optionnel)</span></label>
                      <input type="number" class="form-control" id="id_client" name="id_client" placeholder="ex: 2045" min="1" />
                    </div>
                  </div>

                  <div id="formMsg" class="mt-4" style="display:none;"></div>

                  <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                    <button type="button" class="btn btn-outline-secondary" onclick="document.getElementById('formVehicule').reset(); document.getElementById('formMsg').style.display='none';">
                      Réinitialiser
                    </button>
                    <button type="submit" class="btn btn-primary">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
                        <path d="M12 5v14M5 12h14" />
                      </svg>
                      Ajouter le véhicule
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
  <script src="../assets/BackOffice/js/vendors/sidebarnav.js"></script>
  <script src="../node_modules/jsvectormap/dist/js/jsvectormap.min.js"></script>
  <script src="../node_modules/jsvectormap/dist/maps/world.js"></script>
  <script src="../node_modules/jsvectormap/dist/maps/world-merc.js"></script>
  <script src="../node_modules/apexcharts/dist/apexcharts.min.js"></script>
  <script src="../assets/BackOffice/js/vendors/chart.js"></script>
  <script src="../node_modules/choices.js/public/assets/scripts/choices.min.js"></script>
  <script src="../assets/BackOffice/js/vendors/choice.js"></script>
  <script src="../node_modules/swiper/swiper-bundle.min.js"></script>
  <script src="../assets/BackOffice/js/vendors/swiper.js"></script>
</body>

</html>