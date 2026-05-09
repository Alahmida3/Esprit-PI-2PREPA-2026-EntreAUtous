<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Connexion - Entre AuTout</title>
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <style>
        body { background-color:#212529; color:white; padding-top:80px; min-height:100vh; }

        /* ── Card principale ── */
        .login-card {
            max-width:460px; margin:auto;
            background:rgba(255,255,255,0.06);
            padding:36px; border-radius:20px;
            border:1px solid rgba(255,255,255,0.1);
            backdrop-filter:blur(10px);
        }

        /* ── Onglets méthode de connexion ── */
        .login-tabs { display:flex; gap:0; border-radius:12px; overflow:hidden; background:rgba(0,0,0,0.3); padding:4px; margin-bottom:24px; }
        .login-tab {
            flex:1; text-align:center; padding:10px 8px; font-size:.82rem; font-weight:600;
            border:none; background:transparent; color:rgba(255,255,255,.5);
            cursor:pointer; border-radius:9px; transition:all .2s; letter-spacing:.03em;
        }
        .login-tab.active { background:rgba(255,200,0,.9); color:#212529; }
        .login-tab:hover:not(.active) { color:rgba(255,255,255,.85); }

        /* ── Inputs ── */
        .form-control {
            background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.18);
            color:white; padding:.8rem 1rem; border-radius:10px;
        }
        .form-control::placeholder { color:rgba(255,255,255,0.4); }
        .form-control:focus {
            background:rgba(255,255,255,0.16); color:white;
            border-color:#ffc800; box-shadow:0 0 0 .2rem rgba(255,200,0,.2);
        }
        label { color:rgba(255,255,255,.7); font-size:.83rem; margin-bottom:4px; }
        .error-msg { color:#ff6b6b; font-size:12px; margin-top:4px; display:none; }
        .input-wrapper { position:relative; }
        .toggle-eye {
            position:absolute; right:14px; top:50%; transform:translateY(-50%);
            cursor:pointer; color:#aaa; background:none; border:none; padding:0;
        }
        .toggle-eye:hover { color:#fff; }

        /* ── Face ID zone ── */
        #facePanel { display:none; }
        .face-container {
            position:relative; width:240px; height:240px;
            margin:0 auto 20px; border-radius:50%; overflow:hidden;
            border:3px solid rgba(255,255,255,.15);
            background:#000;
        }
        #videoFeed {
            width:100%; height:100%; object-fit:cover;
            transform:scaleX(-1); /* miroir */
        }
        /* Cadre de détection animé */
        .face-frame {
            position:absolute; inset:0; pointer-events:none;
            display:flex; align-items:center; justify-content:center;
        }
        .face-frame::before {
            content:'';
            width:140px; height:140px;
            border:2.5px solid #ffc800;
            border-radius:50%;
            animation:pulse 1.8s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,100%  { opacity:.4; transform:scale(.95); }
            50%       { opacity:1;  transform:scale(1.05); }
        }

        /* Cercle de progression scan */
        .scan-progress { display:none; text-align:center; margin-bottom:12px; }
        .scan-progress svg { width:60px; height:60px; }
        .scan-progress circle { transition:stroke-dashoffset .1s linear; }

        /* États badge */
        .face-status {
            text-align:center; font-size:.82rem; padding:6px 14px;
            border-radius:20px; display:inline-block; margin-bottom:16px;
        }
        .face-status.waiting  { background:rgba(255,255,255,.08); color:rgba(255,255,255,.6); }
        .face-status.scanning { background:rgba(255,200,0,.15);   color:#ffc800; }
        .face-status.success  { background:rgba(40,167,69,.15);   color:#28a745; }
        .face-status.error    { background:rgba(220,53,69,.15);   color:#dc3545; }

        /* Toggle caméra ON/OFF */
        .btn-cam { border-radius:10px; font-size:.85rem; padding:.55rem 1.2rem; }
        .btn-cam.on  { background:#ffc800; border:none; color:#212529; }
        .btn-cam.off { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.2); color:white; }

        /* ── Enrollement ── */
        #enrollSection { display:none; }
        .enroll-steps { list-style:none; padding:0; }
        .enroll-steps li { padding:6px 10px; font-size:.8rem; border-radius:8px; margin-bottom:4px; color:rgba(255,255,255,.6); }
        .enroll-steps li.done { color:#28a745; }
        .enroll-steps li.done::before { content:'✓ '; }
        .enroll-steps li.active { color:#ffc800; background:rgba(255,200,0,.08); }
        .enroll-steps li.active::before { content:'⊙ '; }

        /* ── Bouton principal ── */
        .btn-yellow { background:#ffc800; border:none; color:#212529; font-weight:700; border-radius:10px; padding:.75rem; }
        .btn-yellow:hover { background:#ffb300; }

        /* Séparateur */
        .divider { display:flex; align-items:center; gap:10px; margin:18px 0; }
        .divider::before,.divider::after { content:''; flex:1; height:1px; background:rgba(255,255,255,.12); }
        .divider span { color:rgba(255,255,255,.4); font-size:.78rem; }
    </style>
</head>
<body>
<div class="container">
    <div class="login-card">
        <h2 class="text-center text-uppercase mb-1 fw-bold">Connexion</h2>
        <p class="text-center text-white-50 small mb-4">Accédez à votre espace Entre AuTout</p>
        <hr class="border-secondary mb-4">

        <!-- Alertes serveur -->
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger py-2 small mb-3">
            <?php
            $errors = [
                'failed'           => 'Email ou mot de passe incorrect.',
                'face_failed'      => 'Reconnaissance faciale échouée. Réessayez.',
                'account_disabled' => '⚠️ Ce compte garagiste est désactivé. Contactez l\'administrateur.',
            ];
            echo $errors[$_GET['error']] ?? 'Une erreur est survenue.';
            ?>
        </div>
        <?php endif; ?>
        <?php if (isset($_GET['success']) && $_GET['success']==='registered'): ?>
        <div class="alert alert-success py-2 small mb-3">
            ✅ Compte créé avec succès !
            <?php if (isset($_GET['face']) && $_GET['face'] === '1'): ?>
                <br><span class="text-success fw-semibold">🤳 Face ID enregistré — vous pouvez vous connecter par reconnaissance faciale.</span>
            <?php else: ?>
                Connectez-vous ci-dessous.
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- ── Onglets ── -->
        <div class="login-tabs">
            <button class="login-tab active" id="tabClassic" onclick="switchMethod('classic')">
                🔑 Email / Mot de passe
            </button>
            <button class="login-tab" id="tabFace" onclick="switchMethod('face')">
                🤳 Face ID
            </button>
        </div>

        <!-- ════ MÉTHODE 1 : Classique ════ -->
        <div id="classicPanel">
            <form id="loginForm" action="/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/UserController.php" method="POST"
                  onsubmit="return validateLogin()">
                <input type="hidden" name="action" value="login">

                <div class="mb-3">
                    <label>Adresse email</label>
                    <input type="text" name="email" id="email" class="form-control" placeholder="votre@email.com">
                    <div class="error-msg" id="err-email">⚠ Adresse email invalide.</div>
                </div>

                <div class="mb-4">
                    <label>Mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control" placeholder="••••••••">
                        <button type="button" class="toggle-eye" onclick="toggleEye('mot_de_passe','eye-login')">
                            <i class="fas fa-eye" id="eye-login"></i>
                        </button>
                    </div>
                    <div class="error-msg" id="err-pass">⚠ Le mot de passe est obligatoire.</div>
                </div>

                <button type="submit" class="btn btn-yellow w-100">Se connecter</button>
            </form>

            <div class="divider"><span>ou essayez</span></div>
            <button class="btn btn-outline-light w-100 rounded-pill py-2 small" onclick="switchMethod('face')">
                🤳 Connexion avec Face ID
            </button>
        </div>

        <!-- ════ MÉTHODE 2 : Face ID ════ -->
        <div id="facePanel">

            <!-- Sous-onglets : Connexion / Enregistrement -->
            <div class="d-flex gap-2 mb-4">
                <button id="subLogin" class="btn btn-sm btn-yellow flex-grow-1" onclick="setFaceMode('login')">
                    Se connecter
                </button>
                <button id="subEnroll" class="btn btn-sm btn-off flex-grow-1" onclick="setFaceMode('enroll')"
                        style="background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.2);color:white;border-radius:10px;font-size:.85rem;">
                    Enregistrer mon visage
                </button>
            </div>

            <!-- ── Vidéo ── -->
            <div class="face-container" id="faceContainer">
                <video id="videoFeed" autoplay playsinline muted></video>
                <div class="face-frame" id="faceFrame"></div>
            </div>

            <!-- Progression scan (SVG circle) -->
            <div class="scan-progress" id="scanProgress">
                <svg viewBox="0 0 36 36">
                    <circle cx="18" cy="18" r="15.9155" fill="none" stroke="rgba(255,255,255,.1)" stroke-width="2.5"/>
                    <circle id="scanCircle" cx="18" cy="18" r="15.9155" fill="none"
                            stroke="#ffc800" stroke-width="2.5"
                            stroke-dasharray="100" stroke-dashoffset="100"
                            stroke-linecap="round"
                            transform="rotate(-90 18 18)"/>
                </svg>
            </div>

            <div class="text-center mb-3">
                <span class="face-status waiting" id="faceStatus">Caméra désactivée</span>
            </div>

            <!-- Boutons caméra -->
            <div class="d-flex gap-2 mb-3">
                <button id="btnStartCam" class="btn btn-cam on flex-grow-1" onclick="startCamera()">
                    📷 Activer la caméra
                </button>
                <button id="btnStopCam" class="btn btn-cam off" onclick="stopCamera()" style="display:none">
                    ⏹ Arrêter
                </button>
            </div>

            <!-- Bouton action principal -->
            <button id="btnFaceAction" class="btn btn-yellow w-100 mb-3" onclick="triggerFaceAction()" disabled>
                🤳 Scanner mon visage
            </button>

            <!-- Informations mode enregistrement -->
            <div id="enrollSection">
                <hr class="border-secondary">
                <p class="small text-white-50 text-center mb-2">Associez votre visage à votre compte :</p>
                <div class="mb-2">
                    <label class="small">Email du compte</label>
                    <input type="text" id="enrollEmail" class="form-control form-control-sm" placeholder="votre@email.com">
                </div>
                <ul class="enroll-steps" id="enrollSteps">
                    <li id="step1">Étape 1 : Activez la caméra</li>
                    <li id="step2">Étape 2 : Cliquez sur "Capturer"</li>
                    <li id="step3">Étape 3 : Enregistrement sur le serveur</li>
                </ul>
            </div>

            <!-- Canvas caché pour capture -->
            <canvas id="captureCanvas" style="display:none;" width="240" height="240"></canvas>

            <!-- Résultat -->
            <div id="faceResult" class="mt-2 text-center small" style="display:none;"></div>

            <div class="divider"><span>ou</span></div>
            <button class="btn btn-outline-light w-100 rounded-pill py-2 small" onclick="switchMethod('classic')">
                🔑 Connexion classique
            </button>
        </div>

        <div class="text-center mt-4">
            <a href="register.php" class="text-white-50 small">
                Pas encore de compte ? <span class="text-white fw-semibold">S'inscrire</span>
            </a>
        </div>
    </div>
</div>

<script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ════════════════════════════════════════════════════════════════
//  VARIABLES GLOBALES
// ════════════════════════════════════════════════════════════════
let stream        = null;
let faceMode      = 'login';       // 'login' | 'enroll'
let scanInterval  = null;
let scanProgress  = 0;
const SCAN_DURATION = 2500;        // ms de « scan simulé »

// ════════════════════════════════════════════════════════════════
//  BASCULE MÉTHODE DE CONNEXION
// ════════════════════════════════════════════════════════════════
function switchMethod(method) {
    document.getElementById('classicPanel').style.display = method === 'classic' ? 'block' : 'none';
    document.getElementById('facePanel').style.display    = method === 'face'    ? 'block' : 'none';
    document.getElementById('tabClassic').classList.toggle('active', method === 'classic');
    document.getElementById('tabFace').classList.toggle('active',    method === 'face');
    if (method !== 'face') stopCamera();
}

// ════════════════════════════════════════════════════════════════
//  BASCULE SOUS-MODE FACE (connexion / enregistrement)
// ════════════════════════════════════════════════════════════════
function setFaceMode(mode) {
    faceMode = mode;
    const isEnroll = mode === 'enroll';
    document.getElementById('enrollSection').style.display = isEnroll ? 'block' : 'none';
    document.getElementById('subLogin').style.background  = isEnroll ? 'rgba(255,255,255,.08)' : '';
    document.getElementById('subEnroll').style.background = isEnroll ? '#ffc800' : 'rgba(255,255,255,.08)';
    document.getElementById('subLogin').style.color  = isEnroll ? 'white' : '#212529';
    document.getElementById('subEnroll').style.color = isEnroll ? '#212529' : 'white';
    document.getElementById('btnFaceAction').textContent = isEnroll ? '📸 Capturer mon visage' : '🤳 Scanner mon visage';
    setStatus('waiting', isEnroll ? 'Activez la caméra puis capturez' : 'Activez la caméra pour scanner');
    resetScan();
    updateEnrollSteps(0);
}

// ════════════════════════════════════════════════════════════════
//  CAMÉRA
// ════════════════════════════════════════════════════════════════
async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { width:{ ideal:480 }, height:{ ideal:480 }, facingMode:'user' }
        });
        document.getElementById('videoFeed').srcObject = stream;
        document.getElementById('btnStartCam').style.display = 'none';
        document.getElementById('btnStopCam').style.display  = 'inline-flex';
        document.getElementById('btnFaceAction').disabled = false;
        setStatus('scanning', faceMode === 'enroll' ? 'Prêt à capturer' : 'Prêt à scanner');
        if (faceMode === 'enroll') updateEnrollSteps(1);
    } catch(e) {
        setStatus('error', '❌ Caméra non accessible : ' + e.message);
    }
}

function stopCamera() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    const v = document.getElementById('videoFeed');
    v.srcObject = null;
    document.getElementById('btnStartCam').style.display = 'inline-flex';
    document.getElementById('btnStopCam').style.display  = 'none';
    document.getElementById('btnFaceAction').disabled = true;
    setStatus('waiting', 'Caméra désactivée');
    resetScan();
}

// ════════════════════════════════════════════════════════════════
//  ACTION PRINCIPALE (scan ou capture)
// ════════════════════════════════════════════════════════════════
function triggerFaceAction() {
    if (faceMode === 'login')  startScan();
    else                        captureAndEnroll();
}

// ── SCAN (connexion) ─────────────────────────────────────────
function startScan() {
    document.getElementById('btnFaceAction').disabled = true;
    document.getElementById('scanProgress').style.display = 'block';
    setStatus('scanning', '⊙ Analyse du visage en cours…');
    scanProgress = 0;
    const circle = document.getElementById('scanCircle');
    const start  = Date.now();

    scanInterval = setInterval(() => {
        const elapsed  = Date.now() - start;
        scanProgress   = Math.min(100, (elapsed / SCAN_DURATION) * 100);
        circle.setAttribute('stroke-dashoffset', 100 - scanProgress);

        if (scanProgress >= 100) {
            clearInterval(scanInterval);
            finishScan();
        }
    }, 30);
}

function finishScan() {
    // Capture frame
    const canvas = document.getElementById('captureCanvas');
    const ctx    = canvas.getContext('2d');
    const video  = document.getElementById('videoFeed');
    ctx.save();
    ctx.scale(-1, 1);
    ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
    ctx.restore();
    const imageData = canvas.toDataURL('image/jpeg', 0.7);

    // Envoyer au serveur pour vérification
    sendToServer('face_login', { image: imageData });
}

// ── CAPTURE + ENRÔLEMENT ─────────────────────────────────────
function captureAndEnroll() {
    const email = document.getElementById('enrollEmail').value.trim();
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showResult('error', '⚠ Entrez un email valide avant de capturer.');
        return;
    }
    updateEnrollSteps(2);
    setStatus('scanning', '📸 Capture en cours…');

    const canvas = document.getElementById('captureCanvas');
    const ctx    = canvas.getContext('2d');
    const video  = document.getElementById('videoFeed');
    ctx.save();
    ctx.scale(-1, 1);
    ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
    ctx.restore();
    const imageData = canvas.toDataURL('image/jpeg', 0.7);

    updateEnrollSteps(3);
    sendToServer('face_enroll', { image: imageData, email: email });
}

// ════════════════════════════════════════════════════════════════
//  COMMUNICATION SERVEUR
// ════════════════════════════════════════════════════════════════
async function sendToServer(action, payload) {
    try {
        const res  = await fetch('/autout/controller/FaceController.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ action, ...payload }),
        });
        const data = await res.json();
        handleServerResponse(action, data);
    } catch(e) {
        // Mode démo : simulation locale si le endpoint n'existe pas encore
        simulateDemo(action, payload);
    }
}

function handleServerResponse(action, data) {
    document.getElementById('scanProgress').style.display = 'none';
    if (data.success) {
        if (action === 'face_login') {
            setStatus('success', '✅ Visage reconnu ! Connexion…');
            showResult('success', '✅ Connexion réussie ! Redirection…');
            setTimeout(() => { window.location.href = data.redirect || '/autout/views/front/home.php'; }, 1200);
        } else {
            setStatus('success', '✅ Visage enregistré avec succès !');
            showResult('success', '✅ ' + (data.message || 'Visage enregistré ! Vous pouvez maintenant vous connecter avec Face ID.'));
            updateEnrollSteps(4);
        }
    } else {
        setStatus('error', '❌ ' + (data.message || 'Échec'));
        showResult('error', '❌ ' + (data.message || 'Échec de la reconnaissance.'));
        document.getElementById('btnFaceAction').disabled = false;
    }
}

// ── Mode démo (sans backend) ─────────────────────────────────
function simulateDemo(action, payload) {
    document.getElementById('scanProgress').style.display = 'none';
    if (action === 'face_login') {
        const stored = localStorage.getItem('faceId_demo');
        if (stored) {
            setStatus('success', '✅ [DÉMO] Visage reconnu !');
            showResult('success', '✅ [DÉMO] Connexion simulée. En production, le serveur validerait votre visage.');
        } else {
            setStatus('error', '❌ [DÉMO] Aucun visage enregistré.');
            showResult('error', '❌ [DÉMO] Enregistrez d\'abord votre visage via l\'onglet "Enregistrer mon visage".');
        }
    } else {
        localStorage.setItem('faceId_demo', payload.email + ':' + Date.now());
        setStatus('success', '✅ [DÉMO] Visage capturé localement !');
        showResult('success', '✅ [DÉMO] Capture réussie. <br><small class="opacity-75">En production, l\'image serait envoyée à votre serveur PHP avec un modèle de reconnaissance.</small>');
        updateEnrollSteps(4);
    }
    document.getElementById('btnFaceAction').disabled = false;
}

// ════════════════════════════════════════════════════════════════
//  UI HELPERS
// ════════════════════════════════════════════════════════════════
function setStatus(type, msg) {
    const el = document.getElementById('faceStatus');
    el.className = 'face-status ' + type;
    el.textContent = msg;
}

function showResult(type, html) {
    const el = document.getElementById('faceResult');
    el.style.display = 'block';
    el.className = 'mt-2 text-center small ' + (type === 'success' ? 'text-success' : 'text-danger');
    el.innerHTML = html;
}

function resetScan() {
    clearInterval(scanInterval);
    scanProgress = 0;
    const circle = document.getElementById('scanCircle');
    if (circle) circle.setAttribute('stroke-dashoffset', 100);
    document.getElementById('scanProgress').style.display = 'none';
    document.getElementById('faceResult').style.display   = 'none';
}

function updateEnrollSteps(active) {
    const steps = document.querySelectorAll('#enrollSteps li');
    steps.forEach((li, i) => {
        li.className = i + 1 < active ? 'done' : (i + 1 === active ? 'active' : '');
    });
}

// ════════════════════════════════════════════════════════════════
//  CONNEXION CLASSIQUE
// ════════════════════════════════════════════════════════════════
function toggleEye(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type     = input.type === 'password' ? 'text' : 'password';
    icon.className = input.type === 'text' ? 'fas fa-eye-slash' : 'fas fa-eye';
}

function validateLogin() {
    let valid = true;
    const email = document.getElementById('email').value.trim();
    const pass  = document.getElementById('mot_de_passe').value;
    const errE  = document.getElementById('err-email');
    const errP  = document.getElementById('err-pass');

    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    errE.style.display = emailOk ? 'none' : 'block';
    if (!emailOk) valid = false;

    errP.style.display = pass.length > 0 ? 'none' : 'block';
    if (pass.length === 0) valid = false;

    return valid;
}

document.getElementById('email')?.addEventListener('input', () =>
    document.getElementById('err-email').style.display = 'none');
document.getElementById('mot_de_passe')?.addEventListener('input', () =>
    document.getElementById('err-pass').style.display = 'none');

// Init
setFaceMode('login');
</script>
</body>
</html>