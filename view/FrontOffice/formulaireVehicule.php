﻿<?php
require_once(__DIR__ . '/../../config.php');
try {
    $pdo = config::getConnexion();
    $stmt = $pdo->query("SELECT id_client, nomclient FROM user ORDER BY nomclient");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $users = [];
}
?>

<div id="alertContainer" class="px-3 pt-3"></div>

<form id="formVehicule" enctype="multipart/form-data">
    <div class="modal-body">
        <input type="hidden" name="action" value="ajouter">

        <div class="mb-3">
            <label class="form-label fw-bold">Marque <span class="text-danger">*</span></label>
            <input type="text" name="marque" id="marque" class="form-control" placeholder="Ex: Peugeot">
            <div class="invalid-feedback">La marque est obligatoire.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Immatriculation <span class="text-danger">*</span></label>
            <input type="text" name="immatriculation" id="immatriculation" class="form-control" placeholder="Ex: 123 TU 456">
            <div class="invalid-feedback">L'immatriculation est obligatoire.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Date d'ajout <span class="text-danger">*</span></label>
            <input type="date" name="date_ajout" id="date_ajout" class="form-control" value="<?php echo date('Y-m-d'); ?>">
            <div class="invalid-feedback">La date est obligatoire.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Kilométrage (km) <span class="text-danger">*</span></label>
            <input type="number" name="kilometrage" id="kilometrage" class="form-control" min="0" placeholder="Ex: 50000">
            <div class="invalid-feedback">Le kilométrage est obligatoire.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Client <span class="text-danger">*</span></label>
            <select name="idClient" id="clientSelect" class="form-control">
                <option value="">-- Sélectionner un client --</option>
                <?php foreach ($users as $user): ?>
                    <option value="<?php echo (int)$user['id_client']; ?>"
                            data-nom="<?php echo htmlspecialchars(trim($user['nomclient'])); ?>">
                        <?php echo htmlspecialchars($user['nomclient']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="nomClient" id="nomClientHidden">
            <div class="invalid-feedback">Veuillez sélectionner un client.</div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Image du véhicule</label>
            <input type="file" name="imageVoiture" id="imageVoiture" class="form-control" accept="image/jpeg,image/png,image/gif">
            <small class="text-muted">Formats acceptés : JPG, PNG, GIF (max 5MB)</small>
            <div class="invalid-feedback">Format ou taille d'image invalide.</div>
        </div>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-warning fw-bold" id="btnEnregistrer">
            <span id="btnText"><i class="fas fa-plus"></i> Enregistrer</span>
            <span id="spinner" class="spinner-border spinner-border-sm d-none"></span>
        </button>
    </div>
</form>

<script>
(function () {

    const form       = document.getElementById('formVehicule');
    const marque     = document.getElementById('marque');
    const immat      = document.getElementById('immatriculation');
    const date       = document.getElementById('date_ajout');
    const km         = document.getElementById('kilometrage');
    const client     = document.getElementById('clientSelect');
    const image      = document.getElementById('imageVoiture');
    const btnSubmit  = document.getElementById('btnEnregistrer');
    const alertBox   = document.getElementById('alertContainer');

    // ── Validation individuelle ──────────────────────────────────────────────
    function check(field, condition) {
        if (condition) {
            field.classList.remove('is-invalid');
            field.classList.add('is-valid');
            return true;
        } else {
            field.classList.remove('is-valid');
            field.classList.add('is-invalid');
            return false;
        }
    }

    const vMarque  = () => check(marque,  marque.value.trim() !== '');
    const vImmat   = () => check(immat,   immat.value.trim()  !== '');
    const vDate    = () => check(date,    date.value          !== '');
    const vKm      = () => check(km,      km.value !== '' && Number(km.value) >= 0);
    const vClient  = () => check(client,  client.value        !== '');
    const vImage   = () => {
        const file = image.files[0];
        if (!file) return true; // optionnel
        const ok = ['image/jpeg','image/png','image/gif','image/jpg'].includes(file.type)
                   && file.size <= 5 * 1024 * 1024;
        return check(image, ok);
    };

    // ── Écoute en temps réel ─────────────────────────────────────────────────
    marque.addEventListener('input',  vMarque);
    immat.addEventListener('input',   vImmat);
    date.addEventListener('change',   vDate);
    km.addEventListener('input',      vKm);
    image.addEventListener('change',  vImage);

    client.addEventListener('change', function () {
        document.getElementById('nomClientHidden').value =
            this.options[this.selectedIndex].getAttribute('data-nom') || '';
        vClient();
    });

    // ── Soumission ───────────────────────────────────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        alertBox.innerHTML = '';

        const valid = vMarque() & vImmat() & vDate() & vKm() & vClient() & vImage();

        if (!valid) {
            alertBox.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>❌</strong> Veuillez corriger les champs en rouge.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            form.querySelector('.is-invalid').focus();
            return;
        }

        // Bloquer le bouton
        btnSubmit.disabled = true;
        document.getElementById('btnText').innerHTML = 'Enregistrement...';
        document.getElementById('spinner').classList.remove('d-none');

        fetch('GestionVehicule.php', {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalVehicule')).hide();
                location.reload();
            } else {
                alertBox.innerHTML = `
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>❌</strong> ${data.message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>`;
                reset_btn();
            }
        })
        .catch(() => {
            alertBox.innerHTML = `
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>❌</strong> Erreur de connexion.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>`;
            reset_btn();
        });
    });

    function reset_btn() {
        btnSubmit.disabled = false;
        document.getElementById('btnText').innerHTML = '<i class="fas fa-plus"></i> Enregistrer';
        document.getElementById('spinner').classList.add('d-none');
    }

})();
</script>