<?php
require_once "../../model/rendezvous.php";
require_once "../../controller/RendezVous.php";

$rendezVousC = new RendezVousC();
$rendezVousC->handleRequest();
?>
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

  <style>
    body { background-color: #0b111e; }
    .card { background-color: #111c2d; border: 1px solid #1e293b; }
    .table thead th { background-color: #1e293b; color: #94a3b8; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; border-bottom: none; }
    .table td { vertical-align: middle; border-color: #1e293b; color: #e2e8f0; }
    .btn-emerald { background-color: #198754; border-color: #198754; color: white; }
    .btn-emerald:hover { background-color: #157347; border-color: #146c43; }
    .btn-slate { background-color: #64748b; border-color: #64748b; color: white; }
    .btn-slate:hover { background-color: #475569; }
    .btn-orange { background-color: #f97316; border-color: #f97316; color: white; }
    .btn-orange:hover { background-color: #ea580c; border-color: #ea580c; }
    .form-control, .form-select { background-color: #0b111e; border-color: #1e293b; color: #f8fafc; }
    .modal-content { background-color: #111c2d; border: 1px solid #1e293b; }
    .modal-header { border-bottom-color: #1e293b; }
    .modal-footer { border-top-color: #1e293b; }
    .form-label { color: #94a3b8; }
    .fk-badge { font-size: 0.7rem; background-color: #2d3748; color: #a0aec0; padding: 2px 6px; border-radius: 4px; margin-left: 5px; }
  </style>
</head>

<body class="ds-init" data-sidebar-size="default">
  <div class="wrapper">
    <?php include("../../partials/sidebar-collapse.html"); ?>

    <div id="content" class="main-content">
      <?php include("../../partials/topbar-second.html"); ?>

      <div class="custom-container mt-4">
        <div class="d-flex align-items-center justify-content-between mb-4">
          <div>
            <h4 class="mb-0">Gestion des Rendez-vous</h4>
            <p class="text-muted mb-0">Consultez et gérez l'ensemble des rendez-vous programmés.</p>
          </div>
          <button class="btn btn-emerald d-flex align-items-center gap-2" onclick="openAddModal()">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            + Programmer un rendez-vous
          </button>
        </div>

        <div class="d-flex justify-content-end mb-3">
          <div class="input-group w-auto">
            <span class="input-group-text bg-transparent border-end-0"><i class="bi bi-search"></i></span>
            <input type="text" class="form-control border-start-0" id="searchInput" placeholder="Rechercher...">
          </div>
        </div>

        <div class="card">
          <div class="card-body p-0">
            <div class="table-responsive">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th class="ps-4">ID RDV</th>
                    <th>ID Véhicule</th>
                    <th>Marque</th>
                    <th>Immatriculation</th>
                    <th>Date RDV</th>
                    <th>Heure RDV</th>
                    <th>Type Service</th>
                    <th>Statut</th>
                    <th>Description</th>
                    <th class="text-end pe-4">ACTION</th>
                  </tr>
                </thead>
                <tbody id="tableBody">
                  <?php foreach($rendezVousC->getAll() as $rdv) {
                    $rendezVousC->afficher($rdv);
                  } ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="rdvModal" tabindex="-1" aria-labelledby="rdvModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
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
              <h5 class="mb-0" id="rdvModalLabel">Programmer un rendez-vous</h5>
              <small class="text-muted">Saisissez les détails de l'intervention</small>
            </div>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="formRDV" method="POST" action="">
            <input type="hidden" id="mode" name="mode" value="add">
            <input type="hidden" id="old_id_rdv" name="old_id_rdv">

            <div class="row g-4">
              <div class="col-md-6">
                <label for="id_rdv" class="form-label">ID Rendez-vous <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="id_rdv" name="id_rdv" placeholder="ex: RDV-001" required />
              </div>
              <div class="col-md-6">
                <label for="id_vehicule" class="form-label">ID Véhicule <span class="text-danger">*</span> <span class="fk-badge">FK</span></label>
                <select class="form-select" id="id_vehicule" name="id_vehicule" required onchange="updateVehiculeInfo()">
                  <option value="">Sélectionner un véhicule</option>
                  <option value="VEH-001">VEH-001 - Peugeot 3008 (205 TUN 1234)</option>
                  <option value="VEH-002">VEH-002 - Renault Clio (156 TUN 7890)</option>
                  <option value="VEH-003">VEH-003 - BMW Série 3 (123 TUN 4567)</option>
                  <option value="VEH-004">VEH-004 - Mercedes Classe A (789 TUN 1011)</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="marque_vehicule" class="form-label">Marque</label>
<input type="text" class="form-control" id="marque_vehicule" name="marque_vehicule" 
       readonly placeholder="Auto-rempli">              </div>
              <div class="col-md-6">
                <label for="immatriculation" class="form-label">Immatriculation</label>
<input type="text" class="form-control" id="immatriculation" name="immatriculation" 
       readonly placeholder="Auto-rempli">              </div>
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
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal" onclick="resetForm()">Annuler</button>
          <button type="button" class="btn btn-primary" onclick="submitForm()">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-2">
              <path d="M12 5v14M5 12h14" />
            </svg>
            <span id="submitBtnText">Enregistrer le RDV</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <?php include("../../partials/scripts.html"); ?>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    const vehiculesData = {
      'VEH-001': { marque: 'Peugeot 3008', immatriculation: '205 TUN 1234' },
      'VEH-002': { marque: 'Renault Clio', immatriculation: '156 TUN 7890' },
      'VEH-003': { marque: 'BMW Série 3', immatriculation: '123 TUN 4567' },
      'VEH-004': { marque: 'Mercedes Classe A', immatriculation: '789 TUN 1011' }
    };

    // Injection unique des données depuis PHP
    const rdvData = <?php echo $rendezVousC->getRdvDataForJS(); ?>;

    function updateVehiculeInfo() {
      const vehiculeId = document.getElementById('id_vehicule').value;
      const vehicule = vehiculesData[vehiculeId];
      if (vehicule) {
        document.getElementById('marque_vehicule').value = vehicule.marque;
        document.getElementById('immatriculation').value = vehicule.immatriculation;
      } else {
        document.getElementById('marque_vehicule').value = '';
        document.getElementById('immatriculation').value = '';
      }
    }

    // ── AJOUTER ──────────────────────────────────────────────
    function openAddModal() {
      document.getElementById('mode').value = 'add';
      document.getElementById('submitBtnText').innerText = 'Enregistrer le RDV';
      document.getElementById('rdvModalLabel').innerText = 'Programmer un rendez-vous';
      resetForm();
      new bootstrap.Modal(document.getElementById('rdvModal')).show();
    }

    // ── MODIFIER ─────────────────────────────────────────────
    function openEditModal(id) {
      const rdv = rdvData[id];
      if (!rdv) return;

      document.getElementById('mode').value = 'edit';
      document.getElementById('old_id_rdv').value = id;
      document.getElementById('submitBtnText').innerText = 'Modifier le RDV';
      document.getElementById('rdvModalLabel').innerText = 'Modifier le rendez-vous';

      document.getElementById('id_rdv').value = rdv.id_rdv;
      document.getElementById('id_vehicule').value = rdv.id_vehicule;
      updateVehiculeInfo();
      document.getElementById('date_rdv').value = rdv.date_rdv;
      document.getElementById('heure_rdv').value = rdv.heure_rdv;
      document.getElementById('type_service').value = rdv.type_service;
      document.getElementById('statut').value = rdv.statut;
      document.getElementById('description').value = rdv.description || '';

      new bootstrap.Modal(document.getElementById('rdvModal')).show();
    }

    // ── SUPPRIMER ────────────────────────────────────────────
    function deleteRendezVous(id) {
      if (confirm('Êtes-vous sûr de vouloir supprimer ce rendez-vous ?')) {
        window.location.href = '?action=delete&id=' + id;
      }
    }

    // ── SOUMETTRE (ajouter ou modifier) ──────────────────────
    function submitForm() {
      const form = document.getElementById('formRDV');
      const formData = new FormData(form);

      fetch(window.location.href, {
        method: 'POST',
        body: formData
      }).then(() => {
        window.location.reload();
      });
    }

    function resetForm() {
      document.getElementById('formRDV').reset();
      document.getElementById('marque_vehicule').value = '';
      document.getElementById('immatriculation').value = '';
    }

    document.getElementById('searchInput').addEventListener('input', function () {
      const searchTerm = this.value.toLowerCase();
      document.querySelectorAll('#tableBody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(searchTerm) ? '' : 'none';
      });
    });
  </script>
</body>
</html>