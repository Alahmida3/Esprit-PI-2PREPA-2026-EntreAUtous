<?php
/**
 * banque_secure.php — VUE UNIQUEMENT
 * ─────────────────────────────────────────────────────────────
 * Responsabilité : afficher la page de paiement.
 * - Aucune validation de carte ici.
 * - Aucun UPDATE BDD ici.
 * - La logique métier est dans PaiementController + paiement_action.php.
 */
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once __DIR__ . '/../../models/db.php';
require_once '../../controller/PaiementController.php';

$idEntretien = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idEntretien <= 0) {
    header('Location: liste_entretien.php');
    exit;
}

$ctrl      = new PaiementController($pdo);
$entretien = $ctrl->getPaymentData($idEntretien);

if (!$entretien) {
    header('Location: liste_entretien.php?paiement_err=1');
    exit;
}

$montant = $entretien['montant_ttc'] ?? '0.000';
$ref     = $entretien['ref_facture'] ?? 'N/A';
$marque  = $entretien['marqueV']     ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiement sécurisé — EntreAutous</title>
    <link rel="icon" href="../../assets/front/assets/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        :root { --gold: #ffc800; --dark: #1a1a2e; --card-bg: #16213e; --input-bg: #0f3460; }
        * { box-sizing: border-box; }
        body { min-height: 100vh; background: linear-gradient(135deg, #0f0c29, #302b63, #24243e); font-family: 'Segoe UI', Arial, sans-serif; display: flex; flex-direction: column; }
        .bank-header { background: rgba(255,255,255,.04); border-bottom: 1px solid rgba(255,255,255,.08); padding: 14px 32px; display: flex; align-items: center; justify-content: space-between; }
        .bank-logo { display: flex; align-items: center; gap: 10px; color: #fff; font-weight: 700; font-size: 1rem; }
        .bank-logo .shield { width: 34px; height: 34px; background: var(--gold); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #000; font-size: 16px; }
        .ssl-badge { display: flex; align-items: center; gap: 6px; background: rgba(25,135,84,.2); border: 1px solid rgba(25,135,84,.4); border-radius: 20px; padding: 4px 14px; color: #6ee7b7; font-size: 12px; font-weight: 600; }
        .steps-bar { display: flex; justify-content: center; gap: 0; padding: 24px 0 8px; }
        .step { display: flex; align-items: center; gap: 8px; font-size: 12px; color: rgba(255,255,255,.35); }
        .step.active { color: rgba(255,255,255,.9); }
        .step.done   { color: #6ee7b7; }
        .step-num { width: 26px; height: 26px; border-radius: 50%; background: rgba(255,255,255,.1); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
        .step.active .step-num { background: var(--gold); color: #000; }
        .step.done   .step-num { background: #198754; color: #fff; }
        .step-sep { width: 50px; height: 1px; background: rgba(255,255,255,.15); margin: 0 6px; align-self: center; }
        .card-3d-wrap { perspective: 1000px; width: 340px; height: 200px; margin: 0 auto 28px; }
        .card-3d { width: 100%; height: 100%; border-radius: 16px; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); border: 1px solid rgba(255,255,255,.15); padding: 24px 28px 20px; position: relative; box-shadow: 0 20px 60px rgba(0,0,0,.5); transition: transform .6s ease; }
        .card-3d:hover { transform: rotateY(-6deg) rotateX(4deg); }
        .card-chip { width: 42px; height: 32px; background: linear-gradient(135deg, #d4af37, #f5d060, #d4af37); border-radius: 6px; margin-bottom: 20px; position: relative; }
        .card-chip::after { content: ''; position: absolute; inset: 6px; border: 1px solid rgba(0,0,0,.2); border-radius: 3px; }
        .card-number-display { font-size: 18px; letter-spacing: 4px; color: #fff; font-family: 'Courier New', monospace; font-weight: 600; margin-bottom: 16px; }
        .card-bottom { display: flex; justify-content: space-between; align-items: flex-end; }
        .card-label { font-size: 9px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; }
        .card-value { font-size: 13px; color: #fff; font-weight: 600; margin-top: 2px; }
        .card-brand { position: absolute; top: 20px; right: 24px; }
        .card-brand svg { width: 56px; height: auto; }
        .card-contactless { position: absolute; bottom: 20px; right: 90px; color: rgba(255,255,255,.3); font-size: 18px; }
        .payment-card { background: rgba(255,255,255,.06); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,.1); border-radius: 20px; padding: 32px; max-width: 440px; margin: 0 auto; }
        .payment-title { text-align: center; margin-bottom: 24px; }
        .payment-title h5 { color: #fff; font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
        .payment-title p  { color: rgba(255,255,255,.5); font-size: 13px; }
        .amount-box { background: rgba(255,200,0,.1); border: 1px solid rgba(255,200,0,.3); border-radius: 12px; padding: 14px 20px; text-align: center; margin-bottom: 24px; }
        .amount-box .amount-label { font-size: 11px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; }
        .amount-box .amount-value { font-size: 28px; font-weight: 700; color: var(--gold); }
        .amount-box .amount-ref   { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; }
        .form-label-custom { font-size: 12px; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 6px; display: block; }
        .form-control-custom { background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.15); border-radius: 10px; color: #fff; padding: 12px 16px; font-size: 15px; width: 100%; outline: none; transition: border-color .2s, background .2s; font-family: 'Courier New', monospace; }
        .form-control-custom::placeholder { color: rgba(255,255,255,.2); }
        .form-control-custom:focus { border-color: var(--gold); background: rgba(255,200,0,.06); box-shadow: 0 0 0 3px rgba(255,200,0,.1); }
        .form-control-custom.is-error { border-color: #f87171; }
        .field-icon { position: relative; }
        .field-icon .icon { position: absolute; right: 14px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,.3); font-size: 16px; pointer-events: none; }
        .card-networks { display: flex; gap: 8px; margin-bottom: 6px; }
        .network-badge { background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.1); border-radius: 6px; padding: 4px 8px; font-size: 10px; color: rgba(255,255,255,.5); cursor: pointer; transition: all .2s; }
        .network-badge.active, .network-badge:hover { border-color: var(--gold); color: var(--gold); background: rgba(255,200,0,.08); }
        .btn-pay { width: 100%; background: linear-gradient(135deg, #ffc800, #ffb000); color: #000; font-weight: 700; font-size: 16px; border: none; border-radius: 12px; padding: 15px; cursor: pointer; margin-top: 20px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: transform .15s, box-shadow .15s; letter-spacing: .3px; }
        .btn-pay:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(255,200,0,.4); }
        .btn-pay:active:not(:disabled) { transform: scale(.98); }
        .btn-pay:disabled { opacity: .6; cursor: not-allowed; }
        .error-message { background: rgba(248,113,113,.1); border: 1px solid #f87171; border-radius: 10px; padding: 10px; margin-bottom: 15px; color: #f87171; font-size: 13px; text-align: center; display: none; }
        .loader-overlay { position: fixed; inset: 0; background: rgba(10,10,30,.92); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 999; }
        .loader-overlay.show { display: flex; }
        .loader-ring { width: 64px; height: 64px; border: 4px solid rgba(255,200,0,.2); border-top-color: var(--gold); border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 20px; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader-text { color: #fff; font-size: 15px; font-weight: 600; }
        .loader-sub  { color: rgba(255,255,255,.4); font-size: 12px; margin-top: 6px; }
        .success-overlay { position: fixed; inset: 0; background: rgba(10,10,30,.95); display: none; flex-direction: column; align-items: center; justify-content: center; z-index: 1000; text-align: center; padding: 40px; }
        .success-overlay.show { display: flex; }
        .success-circle { width: 80px; height: 80px; background: linear-gradient(135deg, #198754, #20c997); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 36px; margin-bottom: 24px; animation: popIn .5s cubic-bezier(.175,.885,.32,1.275); }
        @keyframes popIn { from { transform: scale(0); opacity:0; } to { transform: scale(1); opacity:1; } }
        .success-title { color: #fff; font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .success-sub   { color: rgba(255,255,255,.6); font-size: 14px; margin-bottom: 8px; }
        .success-ref   { color: var(--gold); font-size: 13px; font-weight: 600; }
        .success-redirect { color: rgba(255,255,255,.35); font-size: 12px; margin-top: 16px; }
        .security-badges { display: flex; justify-content: center; gap: 20px; padding: 20px 0; margin-top: auto; }
        .sec-badge { display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,.3); font-size: 11px; }
        .sec-badge i { font-size: 14px; color: rgba(255,255,255,.2); }
    </style>
</head>
<body>

<!-- LOADER OVERLAY -->
<div class="loader-overlay" id="loaderOverlay">
    <div class="loader-ring"></div>
    <div class="loader-text">Traitement en cours…</div>
    <div class="loader-sub" id="loaderMsg">Connexion au réseau bancaire sécurisé</div>
</div>

<!-- SUCCÈS OVERLAY -->
<div class="success-overlay" id="successOverlay">
    <div class="success-circle">✓</div>
    <div class="success-title">Paiement accepté !</div>
    <div class="success-sub">Votre transaction a été traitée avec succès.</div>
    <div class="success-ref">Réf. <?= htmlspecialchars($ref) ?> — <?= number_format((float)$montant, 3, ',', ' ') ?> TND</div>
    <div class="success-redirect" id="redirectMsg">Redirection dans 3 secondes…</div>
</div>

<!-- HEADER BANQUE -->
<div class="bank-header">
    <div class="bank-logo">
        <div class="shield"><i class="fas fa-shield-halved"></i></div>
        <div>
            <div style="font-size:.95rem;">EntreAutous &nbsp;<span style="color:rgba(255,255,255,.4);font-weight:400;">|</span>&nbsp; Paiement sécurisé</div>
            <div style="font-size:.68rem;color:rgba(255,255,255,.4);font-weight:400;">Powered by SecureGateway™</div>
        </div>
    </div>
    <div class="ssl-badge"><i class="fas fa-lock"></i> SSL 256-bit</div>
</div>

<!-- STEPS -->
<div class="steps-bar">
    <div class="step done"><div class="step-num"><i class="fas fa-check" style="font-size:9px;"></i></div> <span>Entretien</span></div>
    <div class="step-sep"></div>
    <div class="step active"><div class="step-num">2</div> <span>Paiement</span></div>
    <div class="step-sep"></div>
    <div class="step"><div class="step-num">3</div> <span>Confirmation</span></div>
</div>

<!-- CONTENU PRINCIPAL -->
<div class="container py-3">
    <div class="payment-card">

        <!-- Zone d'affichage des erreurs serveur -->
        <div class="error-message" id="errorMessage"></div>

        <div class="payment-title">
            <h5><i class="fas fa-credit-card me-2" style="color:var(--gold);"></i>Saisir les informations de paiement</h5>
            <p><?= htmlspecialchars($marque) ?> · <?= htmlspecialchars($entretien['Matricule']) ?></p>
        </div>

        <div class="amount-box">
            <div class="amount-label">Montant à régler</div>
            <div class="amount-value"><?= number_format((float)$montant, 3, ',', ' ') ?> <span style="font-size:16px;">TND</span></div>
            <div class="amount-ref">Facture <?= htmlspecialchars($ref) ?> · <?= htmlspecialchars($entretien['type_intervention']) ?></div>
        </div>

        <!-- Carte 3D animée -->
        <div class="card-3d-wrap">
            <div class="card-3d">
                <div class="card-brand">
                    <svg viewBox="0 0 60 20" xmlns="http://www.w3.org/2000/svg">
                        <text x="0" y="16" fill="#fff" font-family="Arial" font-weight="900" font-size="18" letter-spacing="-1">VISA</text>
                    </svg>
                </div>
                <div class="card-chip"></div>
                <div class="card-number-display" id="displayNumber">•••• •••• •••• ••••</div>
                <div class="card-bottom">
                    <div>
                        <div class="card-label">Titulaire</div>
                        <div class="card-value" id="displayName">VOTRE NOM</div>
                    </div>
                    <div>
                        <div class="card-label">Expire</div>
                        <div class="card-value" id="displayExpiry">MM/AA</div>
                    </div>
                </div>
                <div class="card-contactless"><i class="fas fa-wifi"></i></div>
            </div>
        </div>

        <!-- Réseaux -->
        <div class="card-networks mb-3">
            <div class="network-badge active">VISA</div>
            <div class="network-badge">Mastercard</div>
            <div class="network-badge">Amex</div>
            <div class="network-badge">Mada</div>
        </div>

        <!-- Champs de saisie — affichage uniquement, validation dans paiement_action.php -->
        <div class="mb-3">
            <label class="form-label-custom">Numéro de carte</label>
            <div class="field-icon">
                <input type="text" id="cardNumber" name="card_number" class="form-control-custom"
                    placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number">
                <span class="icon"><i class="fas fa-credit-card"></i></span>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label-custom">Titulaire de la carte</label>
            <div class="field-icon">
                <input type="text" id="cardName" name="card_name" class="form-control-custom"
                    placeholder="NOM PRÉNOM" maxlength="26" autocomplete="cc-name"
                    style="text-transform:uppercase;">
                <span class="icon"><i class="fas fa-user"></i></span>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-7">
                <label class="form-label-custom">Date d'expiration</label>
                <input type="text" id="cardExpiry" name="card_expiry" class="form-control-custom"
                    placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
            </div>
            <div class="col-5">
                <label class="form-label-custom">CVV <i class="fas fa-question-circle" style="font-size:10px;color:rgba(255,255,255,.3);" title="3 chiffres au dos de votre carte"></i></label>
                <div class="field-icon">
                    <input type="password" id="cardCVV" name="card_cvv" class="form-control-custom"
                        placeholder="•••" maxlength="4" autocomplete="cc-csc">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                </div>
            </div>
        </div>

        <button class="btn-pay" id="btnPay" type="button">
            <i class="fas fa-lock"></i>
            Payer <?= number_format((float)$montant, 3, ',', ' ') ?> TND
        </button>

        <div style="text-align:center;margin-top:14px;color:rgba(255,255,255,.25);font-size:11px;">
            <i class="fas fa-shield-halved me-1"></i> Paiement chiffré 256-bit SSL &nbsp;·&nbsp;
            <i class="fas fa-eye-slash me-1"></i> Données non stockées
        </div>
    </div>
</div>

<!-- BADGES SÉCURITÉ -->
<div class="security-badges">
    <div class="sec-badge"><i class="fas fa-shield-halved"></i> 3D Secure</div>
    <div class="sec-badge"><i class="fas fa-lock"></i> PCI DSS</div>
    <div class="sec-badge"><i class="fas fa-certificate"></i> SSL 256-bit</div>
    <div class="sec-badge"><i class="fas fa-ban"></i> Anti-fraude</div>
</div>

<script>
(function () {
    const ID_ENTRETIEN = <?= (int)$idEntretien ?>;

    // ── Formatage d'affichage UNIQUEMENT (aucune validation) ──
    document.getElementById('cardNumber').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 16);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
        document.getElementById('displayNumber').textContent =
            (v + '0000000000000000').slice(0, 16)
            .replace(/(.{4})/g, '$1 ').trim()
            .replace(/\d(?=.{5,})/g, '•');
    });

    document.getElementById('cardName').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
        document.getElementById('displayName').textContent = this.value || 'VOTRE NOM';
    });

    document.getElementById('cardExpiry').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 4);
        if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
        this.value = v;
        document.getElementById('displayExpiry').textContent = this.value || 'MM/AA';
    });

    document.getElementById('cardCVV').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    // Réseau cosmétique
    document.querySelectorAll('.network-badge').forEach(b => {
        b.addEventListener('click', function () {
            document.querySelectorAll('.network-badge').forEach(x => x.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ── Soumission au contrôleur (paiement_action.php gère la validation) ──
    document.getElementById('btnPay').addEventListener('click', async function () {
        this.disabled = true;

        const errorDiv = document.getElementById('errorMessage');
        errorDiv.style.display = 'none';

        const loader = document.getElementById('loaderOverlay');
        const msgs = [
            'Connexion au réseau bancaire sécurisé…',
            'Vérification des données de la carte…',
            'Authentification 3D Secure…',
            'Traitement de la transaction…',
            'Finalisation du paiement…',
        ];

        loader.classList.add('show');
        let step = 0;
        const interval = setInterval(() => {
            step++;
            if (step < msgs.length) {
                document.getElementById('loaderMsg').textContent = msgs[step];
            }
        }, 600);

        await new Promise(r => setTimeout(r, 1500));
        clearInterval(interval);

        const formData = new FormData();
        formData.append('id_entretien', ID_ENTRETIEN);
        formData.append('card_number',  document.getElementById('cardNumber').value);
        formData.append('card_name',    document.getElementById('cardName').value);
        formData.append('card_expiry',  document.getElementById('cardExpiry').value);
        formData.append('card_cvv',     document.getElementById('cardCVV').value);

        try {
            const response = await fetch('../../controller/paiement_action.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            loader.classList.remove('show');

            if (result.success) {
                document.getElementById('successOverlay').classList.add('show');
                let count = 3;
                const timer = setInterval(() => {
                    count--;
                    document.getElementById('redirectMsg').textContent =
                        count > 0 ? `Redirection dans ${count} seconde${count > 1 ? 's' : ''}…` : 'Redirection…';
                    if (count <= 0) {
                        clearInterval(timer);
                        window.location.href = 'liste_entretien.php?paiement_ok=1';
                    }
                }, 1000);
            } else {
                errorDiv.textContent = result.error || 'Une erreur est survenue lors du paiement.';
                errorDiv.style.display = 'block';
                errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
                this.disabled = false;
            }
        } catch (err) {
            loader.classList.remove('show');
            errorDiv.textContent = 'Erreur de connexion au serveur. Veuillez réessayer.';
            errorDiv.style.display = 'block';
            this.disabled = false;
        }
    });
})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>