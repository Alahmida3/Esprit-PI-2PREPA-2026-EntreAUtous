<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Inscription - Entre AuTout</title>
    <link rel="icon" type="image/x-icon" href="../../assets/front/assets/favicon.ico" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <style>
        /* ── Fond page ── */
        section#register {
            background-color: #212529;
            background-image: url("../../assets/front/assets/img/map-image.png");
            background-repeat: no-repeat;
            background-position: center;
            min-height: 100vh;
            padding: 100px 0 60px;
        }

        /* ── Stepper ── */
        .stepper { display:flex; align-items:center; justify-content:center; gap:0; margin-bottom:40px; }
        .step {
            display:flex; flex-direction:column; align-items:center;
            gap:6px; position:relative; z-index:1;
        }
        .step-circle {
            width:42px; height:42px; border-radius:50%;
            background:rgba(255,255,255,.1); border:2px solid rgba(255,255,255,.2);
            color:rgba(255,255,255,.4); font-weight:700; font-size:.95rem;
            display:flex; align-items:center; justify-content:center;
            transition:all .35s;
        }
        .step.active   .step-circle { background:#ffc800; border-color:#ffc800; color:#212529; }
        .step.done     .step-circle { background:#28a745; border-color:#28a745; color:#fff; }
        .step-label { font-size:.72rem; color:rgba(255,255,255,.45); text-align:center; max-width:80px; transition:color .35s; }
        .step.active .step-label { color:#ffc800; }
        .step.done   .step-label { color:#28a745; }
        .step-line { flex:1; height:2px; background:rgba(255,255,255,.12); margin:0 6px; margin-bottom:22px; max-width:70px; transition:background .35s; }
        .step-line.done { background:#28a745; }

        /* ── Formulaire ── */
        .form-control { padding: 1rem; border-radius: 0.5rem; }
        #passwordStrength { height: 5px; border-radius: 3px; transition: width .3s, background .3s; }
        .error-msg { color: #ff6b6b; font-size: 12px; margin-top: 5px; display: none; }
        .input-wrapper { position: relative; }
        .toggle-eye {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            cursor: pointer; color: #aaa;
            background: none; border: none; padding: 0;
        }
        .toggle-eye:hover { color: #fff; }

        /* ── Panneau Face ID ── */
        #faceStep { display:none; }
        .face-card {
            max-width:440px; margin:0 auto;
            background:rgba(255,255,255,.06); padding:36px;
            border-radius:20px; border:1px solid rgba(255,255,255,.1);
        }
        .face-container {
            position:relative; width:220px; height:220px;
            margin:0 auto 20px; border-radius:50%; overflow:hidden;
            border:3px solid rgba(255,255,255,.15); background:#000;
        }
        #videoFeed { width:100%; height:100%; object-fit:cover; transform:scaleX(-1); }

        /* Cadre animé */
        .face-frame {
            position:absolute; inset:0; pointer-events:none;
            display:flex; align-items:center; justify-content:center;
        }
        .face-frame::before {
            content:'';
            width:130px; height:130px;
            border:2.5px solid #ffc800; border-radius:50%;
            animation:pulse 1.8s ease-in-out infinite;
        }
        @keyframes pulse {
            0%,100% { opacity:.35; transform:scale(.94); }
            50%      { opacity:1;  transform:scale(1.06); }
        }

        /* Cercle de progression scan */
        .scan-progress { display:none; text-align:center; margin-bottom:10px; }
        .scan-progress svg { width:56px; height:56px; }
        .scan-progress circle { transition:stroke-dashoffset .08s linear; }

        /* Badge statut */
        .face-status {
            text-align:center; font-size:.8rem; padding:5px 14px;
            border-radius:20px; display:inline-block; margin-bottom:14px;
        }
        .face-status.waiting  { background:rgba(255,255,255,.08); color:rgba(255,255,255,.55); }
        .face-status.scanning { background:rgba(255,200,0,.15);   color:#ffc800; }
        .face-status.success  { background:rgba(40,167,69,.15);   color:#28a745; }
        .face-status.error    { background:rgba(220,53,69,.15);   color:#dc3545; }

        /* Boutons cam */
        .btn-cam { border-radius:10px; font-size:.85rem; padding:.52rem 1.1rem; }
        .btn-cam.on  { background:#ffc800; border:none; color:#212529; }
        .btn-cam.off { background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.2); color:white; }
        .btn-yellow  { background:#ffc800; border:none; color:#212529; font-weight:700; border-radius:10px; padding:.75rem; }
        .btn-yellow:hover { background:#ffb300; }

        /* Étapes enrôlement */
        .enroll-steps { list-style:none; padding:0; margin-top:8px; }
        .enroll-steps li { padding:5px 10px; font-size:.78rem; border-radius:8px; margin-bottom:3px; color:rgba(255,255,255,.5); }
        .enroll-steps li.done   { color:#28a745; }
        .enroll-steps li.done::before   { content:'✓ '; }
        .enroll-steps li.active { color:#ffc800; background:rgba(255,200,0,.08); }
        .enroll-steps li.active::before { content:'⊙ '; }

        /* Canvas caché */
        #captureCanvas { display:none; }

        /* Info passer l'étape */
        .skip-link { color:rgba(255,255,255,.35); font-size:.78rem; cursor:pointer; text-decoration:underline; border:none; background:none; }
        .skip-link:hover { color:rgba(255,255,255,.65); }
    </style>
</head>
<body id="page-top">
<section class="page-section" id="register">
<div class="container">

    <!-- ══ TITRE ══ -->
    <div class="text-center mb-4">
        <h2 class="section-heading text-uppercase text-white">Inscription</h2>
        <h3 class="section-subheading text-muted">Créez votre compte pour accéder à nos services.</h3>
    </div>

    <!-- ══ STEPPER ══ -->
    <div class="stepper" id="stepper">
        <div class="step active" id="st1">
            <div class="step-circle">1</div>
            <div class="step-label">Informations</div>
        </div>
        <div class="step-line" id="line1"></div>
        <div class="step" id="st2">
            <div class="step-circle">2</div>
            <div class="step-label">Face ID</div>
        </div>
        <div class="step-line" id="line2"></div>
        <div class="step" id="st3">
            <div class="step-circle"><i class="fas fa-check"></i></div>
            <div class="step-label">Terminé</div>
        </div>
    </div>

    <!-- ══ ERREURS SERVEUR ══ -->
    <?php if (isset($_GET['error'])): ?>
    <div class="row justify-content-center mb-3">
        <div class="col-md-6">
            <div class="alert alert-danger py-2 small">
                <?php
                $errors = [
                    'invalid_data'  => 'Données invalides. Vérifiez tous les champs.',
                    'invalid_phone' => 'Le numéro de téléphone doit contenir exactement 8 chiffres.',
                    'email_exists'  => 'Cet email est déjà utilisé. Essayez de vous connecter.',
                ];
                echo $errors[$_GET['error']] ?? 'Une erreur est survenue.';
                ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════
         ÉTAPE 1 : FORMULAIRE D'INSCRIPTION
    ══════════════════════════════════════════════════ -->
    <div id="formStep">
        <!-- Le formulaire ne soumet plus directement : onsubmit intercepte -->
        <form id="registerForm" onsubmit="return goToFaceStep()">

            <div class="row justify-content-center mb-5">
                <div class="col-md-6">

                    <div class="form-group mb-3">
                        <input class="form-control" name="prenom" id="prenom" type="text" placeholder="Votre Prénom *" />
                        <div class="error-msg" id="err-prenom">⚠ Le prénom est obligatoire.</div>
                    </div>

                    <div class="form-group mb-3">
                        <input class="form-control" name="nom" id="nom" type="text" placeholder="Votre Nom *" />
                        <div class="error-msg" id="err-nom">⚠ Le nom est obligatoire.</div>
                    </div>

                    <div class="form-group mb-3">
                        <input class="form-control" name="email" id="email" type="text" placeholder="Votre Email *" />
                        <div class="error-msg" id="err-email">⚠ Veuillez entrer une adresse email valide (ex: nom@domaine.com).</div>
                    </div>

                    <div class="form-group mb-3">
                        <input class="form-control" name="telephone" id="telephone" type="text"
                               placeholder="Téléphone (8 chiffres) *" maxlength="8" />
                        <div class="error-msg" id="err-tel">⚠ Le téléphone doit contenir exactement 8 chiffres.</div>
                    </div>

                    <div class="form-group mb-3">
                        <div class="input-wrapper">
                            <input class="form-control" name="mot_de_passe" id="password"
                                   type="password" placeholder="Mot de passe (min 6 caractères) *"
                                   oninput="checkStrength(this.value)" />
                            <button type="button" class="toggle-eye" onclick="toggleEye('password','eye-reg')">
                                <i class="fas fa-eye" id="eye-reg"></i>
                            </button>
                        </div>
                        <div class="mt-2 bg-secondary rounded">
                            <div id="passwordStrength" style="width:0"></div>
                        </div>
                        <small id="strengthLabel" class="text-muted"></small>
                        <div class="error-msg" id="err-pass">⚠ Le mot de passe doit contenir au moins 6 caractères.</div>
                    </div>

                    <div class="form-group mb-0">
                        <textarea class="form-control" name="adresse" id="adresse"
                                  placeholder="Votre Adresse *" rows="2"></textarea>
                        <div class="error-msg" id="err-adresse">⚠ L'adresse est obligatoire.</div>
                    </div>

                </div>
            </div>

            <div class="text-center">
                <button class="btn btn-primary btn-xl text-uppercase" type="submit">
                    <i class="fas fa-arrow-right me-2"></i>Suivant : Configurer Face ID
                </button>
                <div class="mt-3">
                    <a href="login.php" class="text-white-50">
                        Déjà inscrit ? <span class="text-white">Connectez-vous ici.</span>
                    </a>
                </div>
            </div>
        </form>
    </div><!-- /formStep -->


    <!-- ══════════════════════════════════════════════════
         ÉTAPE 2 : CAPTURE FACE ID
    ══════════════════════════════════════════════════ -->
    <div id="faceStep">
        <div class="face-card">
            <h4 class="text-white text-center fw-bold mb-1">
                <i class="fas fa-face-smile me-2 text-warning"></i>Configurer votre Face ID
            </h4>
            <p class="text-center text-white-50 small mb-4">
                Positionnez votre visage dans le cercle, puis cliquez sur <strong>Capturer</strong>.
            </p>

            <!-- Caméra -->
            <div class="face-container">
                <video id="videoFeed" autoplay playsinline muted></video>
                <div class="face-frame"></div>
            </div>

            <!-- Cercle de progression -->
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

            <!-- Badge statut -->
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

            <!-- Bouton capturer -->
            <button id="btnCapture" class="btn btn-yellow w-100 mb-3" onclick="captureAndRegister()" disabled>
                📸 Capturer et S'inscrire
            </button>

            <!-- Étapes visuelles -->
            <ul class="enroll-steps" id="enrollSteps">
                <li id="step1">Étape 1 : Activez la caméra</li>
                <li id="step2">Étape 2 : Cliquez sur "Capturer"</li>
                <li id="step3">Étape 3 : Envoi des données</li>
                <li id="step4">Étape 4 : Compte créé !</li>
            </ul>

            <!-- Canvas caché pour capture -->
            <canvas id="captureCanvas" width="240" height="240"></canvas>

            <!-- Résultat -->
            <div id="faceResult" class="mt-3 text-center small" style="display:none;"></div>

            <!-- Passer l'étape -->
            <hr class="border-secondary mt-4">
            <div class="text-center">
                <p class="text-white-50 small mb-2">Vous pouvez aussi configurer le Face ID plus tard.</p>
                <button class="skip-link" onclick="skipFaceAndRegister()">
                    ⏭ Ignorer et s'inscrire sans Face ID
                </button>
            </div>
        </div>
    </div><!-- /faceStep -->

    <!-- ══ Formulaire caché qui sera soumis au serveur ══
         Il reçoit les données de l'étape 1 + l'image du visage -->
    <form id="hiddenForm" action="/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/UserController.php" method="POST" style="display:none">
        <input type="hidden" name="action"       value="register">
        <input type="hidden" name="prenom"       id="h-prenom">
        <input type="hidden" name="nom"          id="h-nom">
        <input type="hidden" name="email"        id="h-email">
        <input type="hidden" name="telephone"    id="h-telephone">
        <input type="hidden" name="mot_de_passe" id="h-password">
        <input type="hidden" name="adresse"      id="h-adresse">
        <!-- Image du visage (base64 JPEG) — vide si l'étape est ignorée -->
        <input type="hidden" name="face_image"   id="h-face-image" value="">
    </form>

</div><!-- /container -->
</section><!-- /register -->

<!-- ════════════════════════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════════════════════════ -->
<script>
// ══════════════════════════════════════════════════════════════
//  STEP 1 → STEP 2 : validation + affichage Face ID
// ══════════════════════════════════════════════════════════════
function goToFaceStep() {
    if (!validateForm()) return false;

    // ── Passer au stepper étape 2 ──
    document.getElementById('st1').classList.remove('active');
    document.getElementById('st1').classList.add('done');
    document.getElementById('st1').querySelector('.step-circle').innerHTML = '<i class="fas fa-check"></i>';
    document.getElementById('line1').classList.add('done');
    document.getElementById('st2').classList.add('active');

    // ── Masquer formulaire, afficher Face ID ──
    document.getElementById('formStep').style.display = 'none';
    document.getElementById('faceStep').style.display = 'block';

    // ── Remplir le formulaire caché ──
    document.getElementById('h-prenom').value    = document.getElementById('prenom').value.trim();
    document.getElementById('h-nom').value       = document.getElementById('nom').value.trim();
    document.getElementById('h-email').value     = document.getElementById('email').value.trim();
    document.getElementById('h-telephone').value = document.getElementById('telephone').value.trim();
    document.getElementById('h-password').value  = document.getElementById('password').value;
    document.getElementById('h-adresse').value   = document.getElementById('adresse').value.trim();

    // Démarrer la caméra automatiquement
    startCamera();

    return false; // empêche la soumission native
}

// ══════════════════════════════════════════════════════════════
//  CAMÉRA
// ══════════════════════════════════════════════════════════════
let stream       = null;
let scanInterval = null;

async function startCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({
            video: { width:{ ideal:480 }, height:{ ideal:480 }, facingMode:'user' }
        });
        document.getElementById('videoFeed').srcObject = stream;
        document.getElementById('btnStartCam').style.display = 'none';
        document.getElementById('btnStopCam').style.display  = 'inline-flex';
        document.getElementById('btnCapture').disabled = false;
        setStatus('scanning', 'Prêt — positionnez votre visage');
        setStep(1);
    } catch(e) {
        setStatus('error', '❌ Caméra inaccessible : ' + e.message);
    }
}

function stopCamera() {
    if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; }
    document.getElementById('videoFeed').srcObject = null;
    document.getElementById('btnStartCam').style.display = 'inline-flex';
    document.getElementById('btnStopCam').style.display  = 'none';
    document.getElementById('btnCapture').disabled = true;
    setStatus('waiting', 'Caméra désactivée');
}

// ══════════════════════════════════════════════════════════════
//  CAPTURE + SOUMISSION DU FORMULAIRE COMPLET
// ══════════════════════════════════════════════════════════════
function captureAndRegister() {
    setStep(2);
    setStatus('scanning', '📸 Capture en cours…');

    // ── Animation cercle pendant 1,8 s ──
    const circle = document.getElementById('scanCircle');
    document.getElementById('scanProgress').style.display = 'block';
    document.getElementById('btnCapture').disabled = true;
    let progress = 0;
    const DURATION = 1800;
    const start    = Date.now();

    scanInterval = setInterval(() => {
        progress = Math.min(100, ((Date.now() - start) / DURATION) * 100);
        circle.setAttribute('stroke-dashoffset', 100 - progress);
        if (progress >= 100) {
            clearInterval(scanInterval);
            finishCapture();
        }
    }, 30);
}

function finishCapture() {
    // ── Capturer le frame de la vidéo ──
    const canvas = document.getElementById('captureCanvas');
    const ctx    = canvas.getContext('2d');
    const video  = document.getElementById('videoFeed');
    ctx.save();
    ctx.scale(-1, 1);
    ctx.drawImage(video, -canvas.width, 0, canvas.width, canvas.height);
    ctx.restore();
    const imageData = canvas.toDataURL('image/jpeg', 0.75);

    // ── Injecter dans le formulaire caché et soumettre ──
    document.getElementById('h-face-image').value = imageData;
    setStep(3);
    setStatus('success', '✅ Visage capturé ! Inscription en cours…');

    // Afficher un retour visuel puis soumettre
    setTimeout(() => {
        setStep(4);
        document.getElementById('faceResult').style.display = 'block';
        document.getElementById('faceResult').className     = 'mt-3 text-center small text-success';
        document.getElementById('faceResult').textContent   = '✅ Envoi des données… veuillez patienter.';
        document.getElementById('hiddenForm').submit();
    }, 800);
}

// ══════════════════════════════════════════════════════════════
//  IGNORER LE FACE ID → inscription sans image
// ══════════════════════════════════════════════════════════════
function skipFaceAndRegister() {
    stopCamera();
    document.getElementById('h-face-image').value = ''; // pas d'image
    document.getElementById('faceResult').style.display = 'block';
    document.getElementById('faceResult').className     = 'mt-3 text-center small text-white-50';
    document.getElementById('faceResult').textContent   = '⏳ Inscription sans Face ID en cours…';
    document.getElementById('hiddenForm').submit();
}

// ══════════════════════════════════════════════════════════════
//  HELPERS UI
// ══════════════════════════════════════════════════════════════
function setStatus(type, msg) {
    const el = document.getElementById('faceStatus');
    el.className   = 'face-status ' + type;
    el.textContent = msg;
}

function setStep(n) {
    document.querySelectorAll('#enrollSteps li').forEach((li, i) => {
        li.className = i + 1 < n ? 'done' : (i + 1 === n ? 'active' : '');
    });
}

// ══════════════════════════════════════════════════════════════
//  VALIDATION FORMULAIRE (étape 1)
// ══════════════════════════════════════════════════════════════
function showError(id, show) {
    const el = document.getElementById('err-' + id);
    if (el) el.style.display = show ? 'block' : 'none';
}

function validateForm() {
    let valid = true;

    const prenom  = document.getElementById('prenom').value.trim();
    const nom     = document.getElementById('nom').value.trim();
    const email   = document.getElementById('email').value.trim();
    const tel     = document.getElementById('telephone').value.trim();
    const pass    = document.getElementById('password').value;
    const adresse = document.getElementById('adresse').value.trim();

    if (prenom.length === 0)                              { showError('prenom',  true);  valid = false; }
    else                                                   { showError('prenom',  false); }

    if (nom.length === 0)                                 { showError('nom',     true);  valid = false; }
    else                                                   { showError('nom',     false); }

    const emailOk = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    if (!emailOk)                                         { showError('email',   true);  valid = false; }
    else                                                   { showError('email',   false); }

    const telOk = /^[0-9]{8}$/.test(tel);
    if (!telOk)                                           { showError('tel',     true);  valid = false; }
    else                                                   { showError('tel',     false); }

    if (pass.length < 6)                                  { showError('pass',    true);  valid = false; }
    else                                                   { showError('pass',    false); }

    if (adresse.length === 0)                             { showError('adresse', true);  valid = false; }
    else                                                   { showError('adresse', false); }

    return valid;
}

// ══════════════════════════════════════════════════════════════
//  BARRE DE FORCE DU MOT DE PASSE
// ══════════════════════════════════════════════════════════════
function checkStrength(v) {
    const bar   = document.getElementById('passwordStrength');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (v.length >= 6)           score++;
    if (v.length >= 10)          score++;
    if (/[A-Z]/.test(v))         score++;
    if (/[0-9]/.test(v))         score++;
    if (/[^a-zA-Z0-9]/.test(v))  score++;
    const levels = [
        { w:'20%', c:'#dc3545', l:'Très faible' },
        { w:'40%', c:'#fd7e14', l:'Faible'      },
        { w:'60%', c:'#ffc107', l:'Moyen'        },
        { w:'80%', c:'#20c997', l:'Fort'         },
        { w:'100%',c:'#198754', l:'Très fort'    },
    ];
    const lv = levels[Math.max(0, score - 1)];
    bar.style.width      = v.length ? lv.w : '0';
    bar.style.background = lv.c;
    label.textContent    = v.length ? lv.l : '';
}

// ══════════════════════════════════════════════════════════════
//  VOIR / MASQUER MOT DE PASSE
// ══════════════════════════════════════════════════════════════
function toggleEye(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type     = 'text';
        icon.className = 'fas fa-eye-slash';
    } else {
        input.type     = 'password';
        icon.className = 'fas fa-eye';
    }
}

// ══════════════════════════════════════════════════════════════
//  EFFACER ERREURS EN TEMPS RÉEL
// ══════════════════════════════════════════════════════════════
[
    { id:'prenom',    key:'prenom'  },
    { id:'nom',       key:'nom'     },
    { id:'email',     key:'email'   },
    { id:'telephone', key:'tel'     },
    { id:'password',  key:'pass'    },
    { id:'adresse',   key:'adresse' },
].forEach(f => {
    const el = document.getElementById(f.id);
    if (el) el.addEventListener('input', () => showError(f.key, false));
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/front/js/scripts.js"></script>
</body>
</html>