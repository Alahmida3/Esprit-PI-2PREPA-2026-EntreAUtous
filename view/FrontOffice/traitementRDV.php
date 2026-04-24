<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../controller/RendezVous.php');
require_once(__DIR__ . '/../../model/rendezvousC.php');

$controller = new RendezVousC();
$isEdit     = false;
$rdv_a_modifier = null;
$vehiculePreselectionne = null;

// ===== DÉTECTION MODE AJOUT AVEC VÉHICULE PRÉ-SÉLECTIONNÉ (GET ?vehicle=) =====
if (isset($_GET['vehicle']) && is_numeric($_GET['vehicle'])) {
    $vehiculePreselectionne = (int)$_GET['vehicle'];
}

// ===== DÉTECTION MODE MODIFICATION (GET ?id=) =====
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $rdv_a_modifier = $controller->getRdvById((int)$_GET['id']);
    if (!$rdv_a_modifier) {
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                  && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        if ($isAjax) {
            echo '<div class="alert alert-danger m-3">❌ Rendez-vous introuvable.</div>';
            exit();
        }
        header("Location: GestionVehicule.php");
        exit();
    }
}

// ===== TRAITEMENT SOUMISSION DU FORMULAIRE (POST) =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
              && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        $pdo = config::getConnexion();

        $id_client   = (int)($_POST['id_client']   ?? 0);
        $id_vehicule = (int)($_POST['id_vehicule']  ?? 0);
        $date_rdv    = trim($_POST['date_rdv']      ?? '');
        $heure_rdv   = trim($_POST['heure_rdv']     ?? '');
        $type_service= trim($_POST['type_service']  ?? '');
        $description = trim($_POST['description']   ?? '');
        $statut      = trim($_POST['statut']        ?? 'En attente');
        $idRDV       = isset($_POST['idRDV']) && is_numeric($_POST['idRDV']) ? (int)$_POST['idRDV'] : null;

        if (!$id_client || !$id_vehicule || !$date_rdv || !$heure_rdv || !$type_service || !$description) {
            throw new Exception("Tous les champs obligatoires doivent être remplis.");
        }
        if ($date_rdv < date('Y-m-d')) {
            throw new Exception("La date ne peut pas être dans le passé.");
        }

        if ($idRDV) {
            $stmt = $pdo->prepare("
                UPDATE rendezvous SET
                    idclientRDV      = :id_client,
                    idVehicule       = :id_vehicule,
                    dateRDV          = :date_rdv,
                    heureRDV         = :heure_rdv,
                    type_serviceRDV  = :type_service,
                    descriptionRDV   = :description,
                    statutRDV        = :statut
                WHERE idRDV = :idRDV
            ");
            $stmt->execute([
                'id_client'    => $id_client,
                'id_vehicule'  => $id_vehicule,
                'date_rdv'     => $date_rdv,
                'heure_rdv'    => $heure_rdv,
                'type_service' => $type_service,
                'description'  => $description,
                'statut'       => $statut,
                'idRDV'        => $idRDV,
            ]);
            $successMsg = "✅ Rendez-vous modifié avec succès !";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO rendezvous
                    (idclientRDV, idVehicule, dateRDV, heureRDV, type_serviceRDV, descriptionRDV, statutRDV)
                VALUES
                    (:id_client, :id_vehicule, :date_rdv, :heure_rdv, :type_service, :description, :statut)
            ");
            $stmt->execute([
                'id_client'    => $id_client,
                'id_vehicule'  => $id_vehicule,
                'date_rdv'     => $date_rdv,
                'heure_rdv'    => $heure_rdv,
                'type_service' => $type_service,
                'description'  => $description,
                'statut'       => $statut,
            ]);
            $successMsg = "✅ Rendez-vous ajouté avec succès !";
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => $successMsg]);
            exit();
        }
        header("Location: GestionVehicule.php?success=" . urlencode($successMsg));
        exit();

    } catch (Exception $e) {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ' . $e->getMessage()]);
            exit();
        }
        $errorMsg = $e->getMessage();
    }
}

// ===== RÉCUPÉRATION DES DONNÉES POUR LES SELECTS =====
try {
    $pdo = config::getConnexion();
    
    $clients = $pdo->query("SELECT id_client, nomclient FROM user ORDER BY nomclient")->fetchAll(PDO::FETCH_ASSOC);
    $vehicules = $pdo->query("SELECT idVehicule, marqueV, matriculevoiture FROM vehicule ORDER BY marqueV")->fetchAll(PDO::FETCH_ASSOC);
    $services = $pdo->query("SELECT nom_service FROM services ORDER BY nom_service")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $errorMsgBase = "Erreur base de données : " . $e->getMessage();
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        echo '<div class="alert alert-danger m-3">' . htmlspecialchars($errorMsgBase) . '</div>';
        exit();
    } else {
        die($errorMsgBase);
    }
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
          && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax):
?>
<!-- ===== FRAGMENT MODAL : header JAUNE pour les deux modes ===== -->
<div class="modal-header border-0 text-center d-block py-4 bg-warning">
    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
    <h3 class="modal-title-custom fw-bold text-uppercase letter-spacing-1 text-dark">
        <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-calendar-plus' ?> me-2"></i>
        <?= $isEdit ? "MODIFIER LE RENDEZ-VOUS" : "PLANIFIER UN RENDEZ-VOUS" ?>
    </h3>
</div>

<div class="modal-body p-4">
    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>

    <form id="formRDV_modal" action="traitementRDV.php" method="POST" novalidate>
        <?php if ($isEdit): ?>
            <input type="hidden" name="idRDV" value="<?= (int)$rdv_a_modifier['idRDV'] ?>">
        <?php endif; ?>

        <div class="row g-3">
            <!-- Client -->
            <div class="col-md-12">
                <label class="form-label fw-bold">Client</label>
                <select name="id_client" id="modal_id_client" class="form-select" required>
                    <option value="">Choisir un client...</option>
                    <?php foreach ($clients as $c): ?>
                        <option value="<?= (int)$c['id_client'] ?>"
                                data-nom="<?= htmlspecialchars($c['nomclient']) ?>"
                                <?= ($isEdit && isset($rdv_a_modifier['idclientRDV']) && $rdv_a_modifier['idclientRDV'] == $c['id_client']) ? 'selected' : '' ?>>
                            <?= (int)$c['id_client'] ?> — <?= htmlspecialchars($c['nomclient']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Nom du Client - se remplit automatiquement -->
            <div class="col-md-12">
                <label class="form-label fw-bold">Nom du Client</label>
                <input type="text" 
                       id="modal_nom_client_affiche" 
                       class="form-control bg-light fw-semibold"
                       value="<?php 
                           if ($isEdit && isset($rdv_a_modifier['idclientRDV'])) {
                               foreach ($clients as $c) {
                                   if ($c['id_client'] == $rdv_a_modifier['idclientRDV']) {
                                       echo htmlspecialchars($c['nomclient']);
                                       break;
                                   }
                               }
                           } 
                       ?>"
                       readonly>
            </div>

            <!-- Véhicule -->
            <div class="col-md-12">
                <label class="form-label fw-bold">Véhicule</label>
                <select name="id_vehicule" id="modal_id_vehicule" class="form-select" required>
                    <option value="">Choisir un véhicule...</option>
                    <?php foreach ($vehicules as $v): ?>
                        <option value="<?= (int)$v['idVehicule'] ?>"
                                <?= ($isEdit && isset($rdv_a_modifier['idVehicule']) && $rdv_a_modifier['idVehicule'] == $v['idVehicule']) ? 'selected' : ($vehiculePreselectionne == $v['idVehicule'] ? 'selected' : '') ?>>
                            <?= htmlspecialchars($v['marqueV']) ?> — <?= htmlspecialchars($v['matriculevoiture']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Date -->
            <div class="col-md-12">
                <label class="form-label fw-bold">Date du RDV</label>
                <input type="date" name="date_rdv" id="modal_date_rdv" class="form-control"
                       value="<?= $isEdit && isset($rdv_a_modifier['dateRDV']) ? htmlspecialchars($rdv_a_modifier['dateRDV']) : '' ?>"
                       required>
            </div>

            <!-- Heure -->
            <div class="col-md-12">
                <label class="form-label fw-bold">Heure du RDV</label>
                <input type="time" name="heure_rdv" id="modal_heure_rdv" class="form-control"
                       value="<?= $isEdit && isset($rdv_a_modifier['heureRDV']) ? htmlspecialchars($rdv_a_modifier['heureRDV']) : '' ?>"
                       required>
            </div>

            <!-- Service -->
            <div class="col-md-12">
                <label class="form-label fw-bold">Type de Service</label>
                <select name="type_service" id="modal_type_service" class="form-select" required>
                    <option value="">Choisir un service...</option>
                    <?php foreach ($services as $s): ?>
                        <option value="<?= htmlspecialchars($s['nom_service']) ?>"
                                <?= ($isEdit && isset($rdv_a_modifier['type_serviceRDV']) && $rdv_a_modifier['type_serviceRDV'] == $s['nom_service']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($s['nom_service']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Statut -->
            <div class="col-md-12">
                <label class="form-label fw-bold">Statut</label>
                <input type="text" class="form-control bg-light"
                       value="<?= $isEdit && isset($rdv_a_modifier['statutRDV']) ? htmlspecialchars($rdv_a_modifier['statutRDV']) : 'En attente' ?>" disabled>
                <input type="hidden" name="statut"
                       value="<?= $isEdit && isset($rdv_a_modifier['statutRDV']) ? htmlspecialchars($rdv_a_modifier['statutRDV']) : 'En attente' ?>">
            </div>

            <!-- Description -->
            <div class="col-12">
                <label class="form-label fw-bold">Description / Notes</label>
                <textarea name="description" id="modal_description" class="form-control" rows="3"
                          placeholder="Détails supplémentaires sur l'intervention..." required><?= $isEdit && isset($rdv_a_modifier['descriptionRDV']) ? htmlspecialchars($rdv_a_modifier['descriptionRDV']) : '' ?></textarea>
            </div>
        </div>
    </form>
</div>

<div class="modal-footer border-0 justify-content-center pb-4">
    <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">ANNULER</button>
    <button type="button" id="btn_submit_rdv_modal"
            class="btn btn-warning fw-bold px-4 text-dark">
        <i class="fas <?= $isEdit ? 'fa-save' : 'fa-calendar-check' ?> me-1"></i>
        <?= $isEdit ? "ENREGISTRER LES MODIFICATIONS" : "ENREGISTRER LE RDV" ?>
    </button>
</div>

<script>
(function () {
    var selClient = document.getElementById('modal_id_client');
    var nomAffiche = document.getElementById('modal_nom_client_affiche');
    
    function updateNomClient() {
        if (selClient && selClient.selectedIndex >= 0) {
            var opt = selClient.options[selClient.selectedIndex];
            var nom = opt.getAttribute('data-nom') || '';
            if (nomAffiche) nomAffiche.value = nom;
        }
    }
    
    if (selClient) {
        updateNomClient();
        selClient.addEventListener('change', updateNomClient);
    }

    function validateRDVForm() {
        var valid = true;
        var errors = [];

        var client = document.getElementById('modal_id_client');
        var vehicule = document.getElementById('modal_id_vehicule');
        var date = document.getElementById('modal_date_rdv');
        var heure = document.getElementById('modal_heure_rdv');
        var service = document.getElementById('modal_type_service');
        var description = document.getElementById('modal_description');

        if (!client.value) errors.push("Client");
        if (!vehicule.value) errors.push("Véhicule");
        if (!date.value) errors.push("Date");
        if (!heure.value) errors.push("Heure");
        if (!service.value) errors.push("Type de service");
        if (!description.value.trim()) errors.push("Description");

        if (errors.length > 0) {
            alert("Veuillez remplir tous les champs :\n- " + errors.join("\n- "));
            valid = false;
        }

        if (date.value && date.value < new Date().toISOString().split('T')[0]) {
            alert("La date ne peut pas être dans le passé.");
            valid = false;
        }

        return valid;
    }

    var btnSubmit = document.getElementById('btn_submit_rdv_modal');
    if (btnSubmit) {
        btnSubmit.addEventListener('click', function () {
            if (!validateRDVForm()) return;

            var form = document.getElementById('formRDV_modal');
            var formData = new FormData(form);

            btnSubmit.disabled = true;
            btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...';

            fetch('traitementRDV.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                if (data.success) {
                    var modalEl = document.getElementById('modalModifierRDV');
                    if (modalEl) {
                        var bsModal = bootstrap.Modal.getInstance(modalEl);
                        if (bsModal) bsModal.hide();
                    }
                    var toast = document.createElement('div');
                    toast.className = 'alert alert-success alert-fixed alert-dismissible fade show shadow';
                    toast.setAttribute('role', 'alert');
                    toast.innerHTML = '<strong>' + data.message + '</strong><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    document.body.appendChild(toast);
                    setTimeout(function () { location.reload(); }, 1800);
                } else {
                    alert(data.message);
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = '<i class="fas fa-save me-1"></i> <?= $isEdit ? "ENREGISTRER LES MODIFICATIONS" : "ENREGISTRER LE RDV" ?>';
                }
            })
            .catch(function (err) {
                alert('Erreur réseau : ' + err.message);
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-save me-1"></i> <?= $isEdit ? "ENREGISTRER LES MODIFICATIONS" : "ENREGISTRER LE RDV" ?>';
            });
        });
    }
})();
</script>

<?php
exit();
endif;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include_once __DIR__ . '/../../partials/head/head-meta.html'; ?>
    <title><?= $isEdit ? "Modifier" : "Planifier" ?> un Rendez-vous</title>
    <?php include_once __DIR__ . '/../../partials/head/head-links.html'; ?>
    <link href="/ProjetWeb/assets/Front office/css/styles.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .form-container {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 700px;
            margin: 50px auto;
            padding: 0;
            overflow: hidden;
        }
        .card-header-modif {
            border-bottom: none;
            text-align: center;
            display: block;
            padding: 2rem 1rem;
            background-color: #ffc107 !important;
        }
        .modal-title-custom { 
            font-weight: bold; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            margin: 0;
            color: #333 !important;
        }
        .form-body-padding { padding: 40px; }
        .form-label { font-weight: 700; color: #333; margin-bottom: 8px; }
        .form-control, .form-select { border: 1px solid #ced4da; padding: 12px; border-radius: 5px; }
        .btn-action-base { border: none; font-weight: bold; padding: 12px 30px; border-radius: 5px; text-transform: uppercase; }
        .btn-cancel { background-color: #6c757d; color: #fff; margin-right: 10px; }
        .btn-cancel:hover { background-color: #5a6268; color: #fff; }
        .btn-save { background-color: #ffc107; color: #333; }
        .btn-save:hover { background-color: #e0a800; color: #333; }
    </style>
</head>
<body>
<div class="container">
    <div class="form-container">
        <div class="card-header-modif">
            <h3 class="modal-title-custom">
                <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-calendar-plus' ?> me-2"></i>
                <?= $isEdit ? "MODIFIER LE RENDEZ-VOUS" : "PLANIFIER UN RENDEZ-VOUS" ?>
            </h3>
        </div>

        <div class="form-body-padding">
            <?php if (!empty($errorMsg)): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
            <?php endif; ?>

            <form id="formRDV" action="traitementRDV.php<?= $isEdit ? '?id='.(int)$rdv_a_modifier['idRDV'] : '' ?>" method="POST" novalidate>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="idRDV" value="<?= (int)$rdv_a_modifier['idRDV'] ?>">
                <?php endif; ?>

                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label">Client</label>
                        <select name="id_client" id="id_client" class="form-select" required>
                            <option value="">Choisir un client...</option>
                            <?php foreach ($clients as $c): ?>
                                <option value="<?= (int)$c['id_client'] ?>"
                                        data-nom="<?= htmlspecialchars($c['nomclient']) ?>"
                                        <?= ($isEdit && isset($rdv_a_modifier['idclientRDV']) && $rdv_a_modifier['idclientRDV'] == $c['id_client']) ? 'selected' : '' ?>>
                                    <?= (int)$c['id_client'] ?> — <?= htmlspecialchars($c['nomclient']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Nom du Client</label>
                        <input type="text" id="nom_client_affiche" class="form-control bg-light fw-semibold"
                               value="<?php 
                                   if ($isEdit && isset($rdv_a_modifier['idclientRDV'])) {
                                       foreach ($clients as $c) {
                                           if ($c['id_client'] == $rdv_a_modifier['idclientRDV']) {
                                               echo htmlspecialchars($c['nomclient']);
                                               break;
                                           }
                                       }
                                   }
                               ?>"
                               readonly>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Véhicule</label>
                        <select name="id_vehicule" id="id_vehicule" class="form-select" required>
                            <option value="">Choisir un véhicule...</option>
                            <?php foreach ($vehicules as $v): ?>
                                <option value="<?= (int)$v['idVehicule'] ?>"
                                        <?= ($isEdit && isset($rdv_a_modifier['idVehicule']) && $rdv_a_modifier['idVehicule'] == $v['idVehicule']) ? 'selected' : ($vehiculePreselectionne == $v['idVehicule'] ? 'selected' : '') ?>>
                                    <?= htmlspecialchars($v['marqueV']) ?> — <?= htmlspecialchars($v['matriculevoiture']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Date du RDV</label>
                        <input type="date" name="date_rdv" id="date_rdv" class="form-control"
                               value="<?= $isEdit && isset($rdv_a_modifier['dateRDV']) ? htmlspecialchars($rdv_a_modifier['dateRDV']) : '' ?>"
                               required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Heure du RDV</label>
                        <input type="time" name="heure_rdv" id="heure_rdv" class="form-control"
                               value="<?= $isEdit && isset($rdv_a_modifier['heureRDV']) ? htmlspecialchars($rdv_a_modifier['heureRDV']) : '' ?>"
                               required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Type de Service</label>
                        <select name="type_service" id="type_service" class="form-select" required>
                            <option value="">Choisir un service...</option>
                            <?php foreach ($services as $s): ?>
                                <option value="<?= htmlspecialchars($s['nom_service']) ?>"
                                        <?= ($isEdit && isset($rdv_a_modifier['type_serviceRDV']) && $rdv_a_modifier['type_serviceRDV'] == $s['nom_service']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($s['nom_service']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Statut</label>
                        <input type="text" class="form-control bg-light"
                               value="<?= $isEdit && isset($rdv_a_modifier['statutRDV']) ? htmlspecialchars($rdv_a_modifier['statutRDV']) : 'En attente' ?>" disabled>
                        <input type="hidden" name="statut"
                               value="<?= $isEdit && isset($rdv_a_modifier['statutRDV']) ? htmlspecialchars($rdv_a_modifier['statutRDV']) : 'En attente' ?>">
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" id="description" class="form-control" rows="4"
                                  placeholder="Détails supplémentaires sur l'intervention..." required><?= $isEdit && isset($rdv_a_modifier['descriptionRDV']) ? htmlspecialchars($rdv_a_modifier['descriptionRDV']) : '' ?></textarea>
                    </div>

                    <div class="col-12 text-center mt-5">
                        <a href="GestionVehicule.php" class="btn btn-action-base btn-cancel text-decoration-none">ANNULER</a>
                        <button type="submit" class="btn btn-action-base btn-save">
                            <?= $isEdit ? "ENREGISTRER LES MODIFICATIONS" : "ENREGISTRER LE RDV" ?>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
var selClient = document.getElementById('id_client');
var nomAffiche = document.getElementById('nom_client_affiche');

function updateNomClient() {
    if (selClient && selClient.selectedIndex >= 0) {
        var opt = selClient.options[selClient.selectedIndex];
        var nom = opt.getAttribute('data-nom') || '';
        if (nomAffiche) nomAffiche.value = nom;
    }
}

if (selClient) {
    updateNomClient();
    selClient.addEventListener('change', updateNomClient);
}

document.getElementById('formRDV').addEventListener('submit', function (e) {
    var errors = [];
    
    if (!document.getElementById('id_client').value) errors.push("Client");
    if (!document.getElementById('id_vehicule').value) errors.push("Véhicule");
    if (!document.getElementById('date_rdv').value) errors.push("Date");
    if (!document.getElementById('heure_rdv').value) errors.push("Heure");
    if (!document.getElementById('type_service').value) errors.push("Type de service");
    if (!document.getElementById('description').value.trim()) errors.push("Description");
    
    if (errors.length > 0) {
        e.preventDefault();
        alert("Veuillez remplir tous les champs :\n- " + errors.join("\n- "));
    }
    
    var dateField = document.getElementById('date_rdv');
    if (dateField.value && dateField.value < new Date().toISOString().split('T')[0]) {
        e.preventDefault();
        alert("La date ne peut pas être dans le passé.");
    }
});
</script>
</body>
</html>