<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../controller/RendezVous.php');
require_once(__DIR__ . '/../../model/rendezvousC.php');

$success = false;
$rdv_info = [];

// ── TRAITEMENT DU FORMULAIRE ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idClient    = $_POST['id_client']    ?? '';
    $idVehicule  = $_POST['id_vehicule']  ?? '';
    $date        = $_POST['date_rdv']     ?? '';
    $heure       = $_POST['heure_rdv']    ?? '';
    $typeService = $_POST['type_service'] ?? '';
    $statut      = $_POST['statut']       ?? 'En attente';
    $description = $_POST['description']  ?? '';

    $rdv = new RendezVous(null, $date, $heure, $typeService, $statut, $idVehicule, $idClient, $description);

    $controller = new RendezVousC();
    $controller->ajouter($rdv);

    $success = true;
    $rdv_info = [
        'date'    => $date,
        'heure'   => $heure,
        'service' => $typeService
    ];
}

try {
    $pdo      = config::getConnexion();
    $clients  = $pdo->query("SELECT id_client, nomclient FROM `user`")->fetchAll(PDO::FETCH_ASSOC);
    $vehicules = $pdo->query("SELECT idVehicule, matriculevoiture FROM vehicule")->fetchAll(PDO::FETCH_ASSOC);
    $services  = $pdo->query("SELECT nom_service FROM services")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include_once __DIR__ . '/../../partials/head/head-meta.html'; ?>
    <title>Planifier un Rendez-vous</title>
    <?php include_once __DIR__ . '/../../partials/head/head-links.html'; ?>
    <link href="/ProjetWeb/assets/Front office/css/styles.css" rel="stylesheet" />
    <style>
        body { background-color: #f8f9fa; }
        .form-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 50px auto;
            padding: 40px;
        }
        .form-title {
            color: #ffc107;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }
        .form-label { font-weight: 700; color: #333; margin-bottom: 8px; }
        .form-control, .form-select { border: 1px solid #ced4da; padding: 12px; border-radius: 5px; }
        .btn-save { background-color: #ffc107; border: none; color: #fff; font-weight: bold; padding: 12px 30px; border-radius: 5px; }
        .btn-cancel { background-color: #6c757d; border: none; color: #fff; font-weight: bold; padding: 12px 30px; border-radius: 5px; margin-right: 10px; }
        .error-msg { color: #dc3545; font-size: 0.82rem; margin-top: 4px; display: none; }
        .form-control.is-invalid, .form-select.is-invalid { border-color: #dc3545; }
        #nom_client_affiche { background-color: #e9ecef; font-weight: 600; color: #495057; }

        /* Modal confirmation */
        .confirm-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 9999;
            justify-content: center;
            align-items: center;
        }
        .confirm-overlay.show { display: flex; }
        .confirm-box {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            text-align: center;
            max-width: 480px;
            width: 90%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            animation: popIn 0.3s ease;
        }
        @keyframes popIn {
            from { transform: scale(0.8); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        .confirm-icon { font-size: 3.5rem; margin-bottom: 15px; }
        .confirm-title { color: #ffc107; font-weight: 800; text-transform: uppercase; margin-bottom: 10px; }
        .info-box { background: #f8f9fa; border-radius: 8px; padding: 15px 20px; margin: 15px 0; text-align: left; }
        .info-box p { margin: 6px 0; font-size: 0.95rem; }
        .btn-ok {
            background-color: #ffc107;
            border: none; color: #fff;
            font-weight: bold;
            padding: 10px 30px;
            border-radius: 5px;
            margin-top: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-ok:hover { background-color: #e0a800; color: #fff; }
    </style>
</head>
<body>

<?php if ($success): ?>
<!-- ── POPUP CONFIRMATION ── -->
<div class="confirm-overlay show" id="confirmOverlay">
    <div class="confirm-box">
        <div class="confirm-icon">✅</div>
        <h3 class="confirm-title">Rendez-vous Enregistré !</h3>
        <p class="text-muted">Votre rendez-vous a été planifié avec succès.</p>
        <div class="info-box">
            <p>📅 <strong>Date :</strong> <?= htmlspecialchars($rdv_info['date']) ?></p>
            <p>🕐 <strong>Heure :</strong> <?= htmlspecialchars($rdv_info['heure']) ?></p>
            <p>🔧 <strong>Service :</strong> <?= htmlspecialchars($rdv_info['service']) ?></p>
            <p>📋 <strong>Statut :</strong> En attente</p>
        </div>
        <a href="GestionVehicule.php" class="btn-ok">← Retour à mes véhicules</a>
    </div>
</div>
<?php endif; ?>

<div class="container">
    <div class="form-container">
        <h2 class="text-center form-title">Planifier un Rendez-vous</h2>

        <form id="formRDV" action="formulaireRDV.php" method="POST" novalidate>
            <div class="row g-4">

                <div class="col-md-6">
                    <label class="form-label">Client</label>
                    <select name="id_client" id="id_client" class="form-select">
                        <option value="">Choisir un client...</option>
                        <?php foreach($clients as $c): ?>
                            <option value="<?= $c['id_client'] ?>" data-nom="<?= htmlspecialchars($c['nomclient']) ?>">
                                <?= $c['id_client'] ?> — <?= htmlspecialchars($c['nomclient']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-msg" id="err_client">Veuillez choisir un client.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Nom du Client</label>
                    <input type="text" id="nom_client_affiche" class="form-control" placeholder="Se remplit automatiquement..." readonly>
                    <input type="hidden" name="nom_client" id="nom_client_hidden">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Véhicule</label>
                    <select name="id_vehicule" id="id_vehicule" class="form-select">
                        <option value="">Choisir un véhicule...</option>
                        <?php foreach($vehicules as $v): ?>
                            <option value="<?= $v['idVehicule'] ?>"
                                <?= (isset($_GET['id_vehicule']) && $_GET['id_vehicule'] == $v['idVehicule']) ? 'selected' : '' ?>>
                                <?= $v['idVehicule'] ?> — <?= htmlspecialchars($v['matriculevoiture']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-msg" id="err_vehicule">Veuillez choisir un véhicule.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Date du RDV</label>
                    <input type="date" name="date_rdv" id="date_rdv" class="form-control">
                    <div class="error-msg" id="err_date">Veuillez choisir une date.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Heure du RDV</label>
                    <input type="time" name="heure_rdv" id="heure_rdv" class="form-control">
                    <div class="error-msg" id="err_heure">Veuillez choisir une heure.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Type de Service</label>
                    <select name="type_service" id="type_service" class="form-select">
                        <option value="">Choisir un service...</option>
                        <?php foreach($services as $s): ?>
                            <option value="<?= htmlspecialchars($s['nom_service']) ?>">
                                <?= htmlspecialchars($s['nom_service']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="error-msg" id="err_service">Veuillez choisir un type de service.</div>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Statut</label>
                    <input type="text" class="form-control" value="En attente" disabled>
                    <input type="hidden" name="statut" value="En attente">
                </div>

                <div class="col-12">
                    <label class="form-label">Description / Notes</label>
                    <textarea name="description" id="description" class="form-control" rows="4"
                              placeholder="Détails supplémentaires sur l'intervention..."></textarea>
                    <div class="error-msg" id="err_description">Veuillez entrer une description.</div>
                </div>

                <div class="col-12 text-center mt-5">
                    <a href="GestionVehicule.php" class="btn btn-cancel text-decoration-none">Annuler</a>
                    <button type="submit" class="btn btn-save text-uppercase">Enregistrer le RDV</button>
                </div>

            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.getElementById('id_client').addEventListener('change', function() {
    const nom = this.options[this.selectedIndex].getAttribute('data-nom') || '';
    document.getElementById('nom_client_affiche').value = nom;
    document.getElementById('nom_client_hidden').value = nom;
});

document.getElementById('formRDV').addEventListener('submit', function(e) {
    let valid = true;
    document.querySelectorAll('.error-msg').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    function showError(fieldId, errId) {
        const field = document.getElementById(fieldId);
        const err   = document.getElementById(errId);
        if(field) field.classList.add('is-invalid');
        if(err)   err.style.display = 'block';
        valid = false;
    }

    if (!document.getElementById('id_client').value)   showError('id_client',   'err_client');
    if (!document.getElementById('id_vehicule').value) showError('id_vehicule', 'err_vehicule');

    const date = document.getElementById('date_rdv');
    if (!date.value) {
        showError('date_rdv', 'err_date');
    } else {
        const today = new Date().toISOString().split('T')[0];
        if (date.value < today) {
            date.classList.add('is-invalid');
            const err = document.getElementById('err_date');
            err.textContent = 'La date ne peut pas être dans le passé.';
            err.style.display = 'block';
            valid = false;
        }
    }

    if (!document.getElementById('heure_rdv').value)   showError('heure_rdv',   'err_heure');
    if (!document.getElementById('type_service').value) showError('type_service', 'err_service');
    if (!document.getElementById('description').value.trim()) showError('description', 'err_description');

    if (!valid) {
        e.preventDefault();
        document.querySelector('.is-invalid').scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
</body>
</html>