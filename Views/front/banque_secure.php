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
        :root {
            --gold: #ffc800;
            --dark: #1a1a2e;
            --card-bg: #16213e;
            --input-bg: #0f3460;
        }

        * { box-sizing: border-box; }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* ── HEADER BANQUE ── */
        .bank-header {
            background: rgba(255,255,255,.04);
            border-bottom: 1px solid rgba(255,255,255,.08);
            padding: 14px 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .bank-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
        }
        .bank-logo .shield {
            width: 34px;
            height: 34px;
            background: var(--gold);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #000;
            font-size: 16px;
        }
        .ssl-badge {
            display: flex;
            align-items: center;
            gap: 6px;
            background: rgba(25,135,84,.2);
            border: 1px solid rgba(25,135,84,.4);
            border-radius: 20px;
            padding: 4px 14px;
            color: #6ee7b7;
            font-size: 12px;
            font-weight: 600;
        }

        /* ── STEPS ── */
        .steps-bar {
            display: flex;
            justify-content: center;
            gap: 0;
            padding: 24px 0 8px;
        }
        .step {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            color: rgba(255,255,255,.35);
        }
        .step.active { color: rgba(255,255,255,.9); }
        .step.done   { color: #6ee7b7; }
        .step-num {
            width: 26px; height: 26px;
            border-radius: 50%;
            background: rgba(255,255,255,.1);
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
        }
        .step.active .step-num { background: var(--gold); color: #000; }
        .step.done   .step-num { background: #198754; color: #fff; }
        .step-sep { width: 50px; height: 1px; background: rgba(255,255,255,.15); margin: 0 6px; align-self: center; }

        /* ── CARTE VISA ANIMÉE ── */
        .card-3d-wrap {
            perspective: 1000px;
            width: 340px;
            height: 200px;
            margin: 0 auto 28px;
        }
        .card-3d {
            width: 100%;
            height: 100%;
            border-radius: 16px;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%);
            border: 1px solid rgba(255,255,255,.15);
            padding: 24px 28px 20px;
            position: relative;
            box-shadow: 0 20px 60px rgba(0,0,0,.5);
            transition: transform .6s ease;
        }
        .card-3d:hover { transform: rotateY(-6deg) rotateX(4deg); }

        .card-chip {
            width: 42px; height: 32px;
            background: linear-gradient(135deg, #d4af37, #f5d060, #d4af37);
            border-radius: 6px;
            margin-bottom: 20px;
            position: relative;
        }
        .card-chip::after {
            content: '';
            position: absolute;
            inset: 6px;
            border: 1px solid rgba(0,0,0,.2);
            border-radius: 3px;
        }
        .card-number-display {
            font-size: 18px;
            letter-spacing: 4px;
            color: #fff;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            margin-bottom: 16px;
        }
        .card-bottom {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .card-label { font-size: 9px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; }
        .card-value { font-size: 13px; color: #fff; font-weight: 600; margin-top: 2px; }
        .card-brand {
            position: absolute;
            top: 20px;
            right: 24px;
        }
        .card-brand svg { width: 56px; height: auto; }
        .card-contactless {
            position: absolute;
            bottom: 20px;
            right: 90px;
            color: rgba(255,255,255,.3);
            font-size: 18px;
        }

        /* ── FORMULAIRE ── */
        .payment-card {
            background: rgba(255,255,255,.06);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px;
            padding: 32px;
            max-width: 440px;
            margin: 0 auto;
        }
        .payment-title {
            text-align: center;
            margin-bottom: 24px;
        }
        .payment-title h5 { color: #fff; font-size: 1.1rem; font-weight: 700; margin-bottom: 4px; }
        .payment-title p  { color: rgba(255,255,255,.5); font-size: 13px; }

        .amount-box {
            background: rgba(255,200,0,.1);
            border: 1px solid rgba(255,200,0,.3);
            border-radius: 12px;
            padding: 14px 20px;
            text-align: center;
            margin-bottom: 24px;
        }
        .amount-box .amount-label { font-size: 11px; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: 1px; }
        .amount-box .amount-value { font-size: 28px; font-weight: 700; color: var(--gold); }
        .amount-box .amount-ref   { font-size: 11px; color: rgba(255,255,255,.4); margin-top: 2px; }

        .form-label-custom {
            font-size: 12px;
            color: rgba(255,255,255,.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 6px;
            display: block;
        }
        .form-control-custom {
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.15);
            border-radius: 10px;
            color: #fff;
            padding: 12px 16px;
            font-size: 15px;
            width: 100%;
            outline: none;
            transition: border-color .2s, background .2s;
            font-family: 'Courier New', monospace;
        }
        .form-control-custom::placeholder { color: rgba(255,255,255,.2); }
        .form-control-custom:focus {
            border-color: var(--gold);
            background: rgba(255,200,0,.06);
            box-shadow: 0 0 0 3px rgba(255,200,0,.1);
        }
        .form-control-custom.is-error {
            border-color: #f87171;
            background: rgba(248,113,113,.07);
        }
        .form-control-custom.is-valid {
            border-color: #34d399;
            background: rgba(52,211,153,.06);
        }
        .field-icon {
            position: relative;
        }
        .field-icon .icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255,255,255,.3);
            font-size: 16px;
            pointer-events: none;
        }
        .field-error { color: #f87171; font-size: 11px; margin-top: 4px; display: none; }

        /* ── RÉSEAU CARTES ── */
        .card-networks {
            display: flex;
            gap: 8px;
            margin-bottom: 6px;
        }
        .network-badge {
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(255,255,255,.1);
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 10px;
            color: rgba(255,255,255,.5);
            cursor: pointer;
            transition: all .2s;
        }
        .network-badge.active, .network-badge:hover {
            border-color: var(--gold);
            color: var(--gold);
            background: rgba(255,200,0,.08);
        }

        /* ── BOUTON PAYER ── */
        .btn-pay {
            width: 100%;
            background: linear-gradient(135deg, #ffc800, #ffb000);
            color: #000;
            font-weight: 700;
            font-size: 16px;
            border: none;
            border-radius: 12px;
            padding: 15px;
            cursor: pointer;
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform .15s, box-shadow .15s;
            letter-spacing: .3px;
        }
        .btn-pay:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255,200,0,.4);
        }
        .btn-pay:active:not(:disabled) { transform: scale(.98); }
        .btn-pay:disabled { opacity: .6; cursor: not-allowed; }

        /* ── LOADER ── */
        .loader-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10,10,30,.92);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 999;
        }
        .loader-overlay.show { display: flex; }
        .loader-ring {
            width: 64px; height: 64px;
            border: 4px solid rgba(255,200,0,.2);
            border-top-color: var(--gold);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loader-text { color: #fff; font-size: 15px; font-weight: 600; }
        .loader-sub  { color: rgba(255,255,255,.4); font-size: 12px; margin-top: 6px; }

        /* ── SUCCÈS ── */
        .success-overlay {
            position: fixed;
            inset: 0;
            background: rgba(10,10,30,.95);
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            text-align: center;
            padding: 40px;
        }
        .success-overlay.show { display: flex; }
        .success-circle {
            width: 80px; height: 80px;
            background: linear-gradient(135deg, #198754, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin-bottom: 24px;
            animation: popIn .5s cubic-bezier(.175,.885,.32,1.275);
        }
        @keyframes popIn { from { transform: scale(0); opacity:0; } to { transform: scale(1); opacity:1; } }
        .success-title { color: #fff; font-size: 22px; font-weight: 700; margin-bottom: 8px; }
        .success-sub   { color: rgba(255,255,255,.6); font-size: 14px; margin-bottom: 8px; }
        .success-ref   { color: var(--gold); font-size: 13px; font-weight: 600; }
        .success-redirect { color: rgba(255,255,255,.35); font-size: 12px; margin-top: 16px; }

        /* ── PIED ── */
        .security-badges {
            display: flex;
            justify-content: center;
            gap: 20px;
            padding: 20px 0;
            margin-top: auto;
        }
        .sec-badge { display: flex; align-items: center; gap: 6px; color: rgba(255,255,255,.3); font-size: 11px; }
        .sec-badge i { font-size: 14px; color: rgba(255,255,255,.2); }
    </style>
</head>
<body>

<?php
/**
 * VIEW ONLY — aucune mutation ici.
 * La mutation (UPDATE statut) est dans controller/paiement_action.php
 */
require_once '../../config.php';

$idEntretien = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($idEntretien <= 0) {
    header('Location: liste_entretien.php');
    exit;
}

// Lecture de l'entretien + facture (doit être Carte Bancaire)
$stmt = $pdo->prepare("
    SELECT e.id_entretien, e.Matricule, e.type_intervention, e.statut,
           v.marqueV,
           f.ref_facture, f.montant_ttc, f.taux_tva, f.mode_paiement
    FROM entre e
    JOIN vehicule v ON e.Matricule = v.Matricule
    INNER JOIN facture f ON f.entretien = e.id_entretien
                        AND f.mode_paiement = 'Carte Bancaire'
                        AND f.deleted_at IS NULL
    WHERE e.id_entretien = ? AND e.deleted_at IS NULL
    LIMIT 1
");
$stmt->execute([$idEntretien]);
$entretien = $stmt->fetch(PDO::FETCH_ASSOC);

// Double vérification serveur : statut termine + facture Carte Bancaire
if (!$entretien || $entretien['statut'] !== 'termine') {
    header('Location: liste_entretien.php?paiement_err=1');
    exit;
}

$montant = $entretien['montant_ttc'] ?? '0.000';
$ref     = $entretien['ref_facture'] ?? 'N/A';
$marque  = $entretien['marqueV']     ?? '';
?>

<!-- ── LOADER OVERLAY ── -->
<div class="loader-overlay" id="loaderOverlay">
    <div class="loader-ring"></div>
    <div class="loader-text">Traitement en cours…</div>
    <div class="loader-sub" id="loaderMsg">Connexion au réseau bancaire sécurisé</div>
</div>

<!-- ── SUCCÈS OVERLAY ── -->
<div class="success-overlay" id="successOverlay">
    <div class="success-circle">✓</div>
    <div class="success-title">Paiement accepté !</div>
    <div class="success-sub">Votre transaction a été traitée avec succès.</div>
    <div class="success-ref">Réf. <?= htmlspecialchars($ref) ?> — <?= number_format((float)$montant, 3, ',', ' ') ?> TND</div>
    <div class="success-redirect" id="redirectMsg">Redirection dans 3 secondes…</div>
</div>

<!-- ── HEADER BANQUE ── -->
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

<!-- ── STEPS ── -->
<div class="steps-bar">
    <div class="step done"><div class="step-num"><i class="fas fa-check" style="font-size:9px;"></i></div> <span>Entretien</span></div>
    <div class="step-sep"></div>
    <div class="step active"><div class="step-num">2</div> <span>Paiement</span></div>
    <div class="step-sep"></div>
    <div class="step"><div class="step-num">3</div> <span>Confirmation</span></div>
</div>

<!-- ── CONTENU PRINCIPAL ── -->
<div class="container py-3">
    <div class="payment-card">

        <div class="payment-title">
            <h5><i class="fas fa-credit-card me-2" style="color:var(--gold);"></i>Saisir les informations de paiement</h5>
            <p><?= htmlspecialchars($marque) ?> · <?= htmlspecialchars($entretien['Matricule']) ?></p>
        </div>

        <!-- Montant -->
        <div class="amount-box">
            <div class="amount-label">Montant à régler</div>
            <div class="amount-value"><?= number_format((float)$montant, 3, ',', ' ') ?> <span style="font-size:16px;">TND</span></div>
            <div class="amount-ref">Facture <?= htmlspecialchars($ref) ?> · <?= htmlspecialchars($entretien['type_intervention']) ?></div>
        </div>

        <!-- Carte 3D animée -->
        <div class="card-3d-wrap">
            <div class="card-3d">
                <div class="card-brand">
                    <!-- VISA SVG -->
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

        <!-- Formulaire -->
        <div class="mb-3">
            <label class="form-label-custom">Numéro de carte</label>
            <div class="field-icon">
                <input type="text" id="cardNumber" class="form-control-custom"
                    placeholder="0000 0000 0000 0000" maxlength="19" autocomplete="cc-number">
                <span class="icon"><i class="fas fa-credit-card"></i></span>
            </div>
            <div class="field-error" id="errCardNumber">Numéro de carte invalide (16 chiffres requis).</div>
        </div>

        <div class="mb-3">
            <label class="form-label-custom">Titulaire de la carte</label>
            <div class="field-icon">
                <input type="text" id="cardName" class="form-control-custom"
                    placeholder="NOM PRÉNOM" maxlength="26" autocomplete="cc-name"
                    style="text-transform:uppercase;">
                <span class="icon"><i class="fas fa-user"></i></span>
            </div>
            <div class="field-error" id="errCardName">Veuillez saisir le nom du titulaire.</div>
        </div>

        <div class="row g-3">
            <div class="col-7">
                <label class="form-label-custom">Date d'expiration</label>
                <input type="text" id="cardExpiry" class="form-control-custom"
                    placeholder="MM/AA" maxlength="5" autocomplete="cc-exp">
                <div class="field-error" id="errCardExpiry">Date invalide ou carte expirée.</div>
            </div>
            <div class="col-5">
                <label class="form-label-custom">CVV <i class="fas fa-question-circle" style="font-size:10px;color:rgba(255,255,255,.3);" title="3 chiffres au dos de votre carte"></i></label>
                <div class="field-icon">
                    <input type="password" id="cardCVV" class="form-control-custom"
                        placeholder="•••" maxlength="4" autocomplete="cc-csc">
                    <span class="icon"><i class="fas fa-lock"></i></span>
                </div>
                <div class="field-error" id="errCardCVV">CVV invalide.</div>
            </div>
        </div>

        <!-- Bouton payer -->
        <button class="btn-pay" id="btnPay" type="button">
            <i class="fas fa-lock"></i>
            Payer <?= number_format((float)$montant, 3, ',', ' ') ?> TND
        </button>

        <!-- Infos sécurité -->
        <div style="text-align:center;margin-top:14px;color:rgba(255,255,255,.25);font-size:11px;">
            <i class="fas fa-shield-halved me-1"></i> Paiement chiffré 256-bit SSL &nbsp;·&nbsp;
            <i class="fas fa-eye-slash me-1"></i> Données non stockées
        </div>
    </div>
</div>

<!-- ── BADGES SÉCURITÉ ── -->
<div class="security-badges">
    <div class="sec-badge"><i class="fas fa-shield-halved"></i> 3D Secure</div>
    <div class="sec-badge"><i class="fas fa-lock"></i> PCI DSS</div>
    <div class="sec-badge"><i class="fas fa-certificate"></i> SSL 256-bit</div>
    <div class="sec-badge"><i class="fas fa-ban"></i> Anti-fraude</div>
</div>

<script>
(function () {

    const ID_ENTRETIEN = <?= (int)$idEntretien ?>;

    /* ── Formatage automatique des champs ── */
    const cardNumberInput = document.getElementById('cardNumber');
    const cardNameInput   = document.getElementById('cardName');
    const cardExpiryInput = document.getElementById('cardExpiry');
    const cardCVVInput    = document.getElementById('cardCVV');

    // Numéro : groupes de 4 chiffres
    cardNumberInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 16);
        this.value = v.replace(/(.{4})/g, '$1 ').trim();
        document.getElementById('displayNumber').textContent =
            (v + '0000000000000000').slice(0, 16)
            .replace(/(.{4})/g, '$1 ').trim()
            .replace(/\d(?=.{5,})/g, '•');
        updateCardStyle(v);
    });

    // Nom : majuscules
    cardNameInput.addEventListener('input', function () {
        this.value = this.value.toUpperCase();
        document.getElementById('displayName').textContent = this.value || 'VOTRE NOM';
    });

    // Expiry : MM/AA
    cardExpiryInput.addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').slice(0, 4);
        if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2);
        this.value = v;
        document.getElementById('displayExpiry').textContent = this.value || 'MM/AA';
    });

    // CVV : masqué
    cardCVVInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 4);
    });

    function updateCardStyle(num) {
        const net = document.querySelector('.network-badge.active');
        if (net) net.classList.remove('active');
        const prefix = num.slice(0, 1);
        const idx = prefix === '4' ? 0 : prefix === '5' ? 1 : prefix === '3' ? 2 : -1;
        if (idx >= 0) document.querySelectorAll('.network-badge')[idx].classList.add('active');
    }

    /* ── Validation JS (aucun attribut HTML5) ── */
    function validateAll() {
        let valid = true;

        // Numéro carte — 16 chiffres
        const num = cardNumberInput.value.replace(/\s/g, '');
        if (!/^\d{16}$/.test(num)) {
            showErr('cardNumber', 'errCardNumber'); valid = false;
        } else {
            clearErr('cardNumber', 'errCardNumber');
        }

        // Nom
        if (cardNameInput.value.trim().length < 3) {
            showErr('cardName', 'errCardName'); valid = false;
        } else {
            clearErr('cardName', 'errCardName');
        }

        // Expiry MM/AA
        const exp = cardExpiryInput.value;
        if (!/^\d{2}\/\d{2}$/.test(exp)) {
            showErr('cardExpiry', 'errCardExpiry'); valid = false;
        } else {
            const [mm, yy] = exp.split('/').map(Number);
            const now = new Date();
            const expDate = new Date(2000 + yy, mm - 1, 1);
            const today   = new Date(now.getFullYear(), now.getMonth(), 1);
            if (mm < 1 || mm > 12 || expDate < today) {
                showErr('cardExpiry', 'errCardExpiry'); valid = false;
            } else {
                clearErr('cardExpiry', 'errCardExpiry');
            }
        }

        // CVV 3 ou 4 chiffres
        if (!/^\d{3,4}$/.test(cardCVVInput.value)) {
            showErr('cardCVV', 'errCardCVV'); valid = false;
        } else {
            clearErr('cardCVV', 'errCardCVV');
        }

        return valid;
    }

    function showErr(fieldId, errId) {
        document.getElementById(fieldId).classList.add('is-error');
        document.getElementById(fieldId).classList.remove('is-valid');
        document.getElementById(errId).style.display = 'block';
    }
    function clearErr(fieldId, errId) {
        document.getElementById(fieldId).classList.remove('is-error');
        document.getElementById(fieldId).classList.add('is-valid');
        document.getElementById(errId).style.display = 'none';
    }

    /* ── Séquence de paiement ── */
    document.getElementById('btnPay').addEventListener('click', async function () {

        if (!validateAll()) return;

        this.disabled = true;
        const loader = document.getElementById('loaderOverlay');
        const msgs   = [
            'Connexion au réseau bancaire sécurisé…',
            'Vérification des données de la carte…',
            'Authentification 3D Secure…',
            'Traitement de la transaction…',
            'Finalisation du paiement…',
        ];

        loader.classList.add('show');

        // Simulation des étapes bancaires (setTimeout)
        let step = 0;
        const interval = setInterval(() => {
            step++;
            if (step < msgs.length) {
                document.getElementById('loaderMsg').textContent = msgs[step];
            }
        }, 600);

        // Attente réaliste de 2 secondes + fetch vers paiement_action.php
        await new Promise(r => setTimeout(r, 2000));
        clearInterval(interval);

        try {
            const fd = new FormData();
            fd.append('id_entretien', ID_ENTRETIEN);

            const res  = await fetch('../../controller/paiement_action.php', { method: 'POST', body: fd });
            const json = await res.json();

            loader.classList.remove('show');

            if (json.success) {
                // ── Afficher l'écran de succès ──
                document.getElementById('successOverlay').classList.add('show');
                // Décompte redirection
                let count = 3;
                const timer = setInterval(() => {
                    count--;
                    document.getElementById('redirectMsg').textContent =
                        count > 0 ? `Redirection dans ${count} seconde${count>1?'s':''}…` : 'Redirection…';
                    if (count <= 0) {
                        clearInterval(timer);
                        window.location.href = 'liste_entretien.php?paiement_ok=1';
                    }
                }, 1000);
            } else {
                alert('Erreur : ' + (json.error || 'Transaction refusée.'));
                this.disabled = false;
            }
        } catch (err) {
            loader.classList.remove('show');
            alert('Erreur réseau. Veuillez réessayer.');
            this.disabled = false;
        }
    });

    // Réseau cliquable
    document.querySelectorAll('.network-badge').forEach(b => {
        b.addEventListener('click', function () {
            document.querySelectorAll('.network-badge').forEach(x => x.classList.remove('active'));
            this.classList.add('active');
        });
    });

})();
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>