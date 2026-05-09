<?php
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path' => '/Esprit-PI-2PREPA-2026-EntreAUtous/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../../models/db.php';
$stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) { session_destroy(); header("Location: login.php"); exit; }

$prenom  = $user['prenom']    ?? '';
$nom     = $user['nom']       ?? '';
$email   = $user['email']     ?? '';
$tel     = $user['telephone'] ?? '';
$adresse = $user['adresse']   ?? '';
$date_i  = $user['date_inscription'] ? date('d/m/Y', strtotime($user['date_inscription'])) : '—';

// ── Parcours guidé ─────────────────────────────────────────
$j = null; $jScore = 0; $jPct = 0;
try {
    $pdo->prepare("INSERT IGNORE INTO user_journey (id_client) VALUES (?)")->execute([$_SESSION['user_id']]);
    $jStmt = $pdo->prepare("SELECT * FROM user_journey WHERE id_client = ?");
    $jStmt->execute([$_SESSION['user_id']]);
    $j = $jStmt->fetch(PDO::FETCH_ASSOC);
    if ($j) {
        // Auto-valider step_profile si téléphone + adresse renseignés
        if (!$j['step_profile'] && !empty($tel) && !empty($adresse)) {
            $pdo->prepare("UPDATE user_journey SET step_profile=1, score=score+1 WHERE id_client=? AND step_profile=0")->execute([$_SESSION['user_id']]);
            $j['step_profile'] = 1;
        }
        $jScore = (int)($j['score'] ?? 0);
        $jPct   = round($jScore / 6 * 100);
    }
} catch (PDOException $e) { /* Table pas encore créée */ }

// ── Suivi d'activité ────────────────────────────────────────
$trackGlobal = ['total_visits' => 0, 'total_s' => 0, 'last_visit' => null];
$trackPages  = [];
try {
    $tg = $pdo->prepare("SELECT COUNT(*) as total_visits, SUM(duration_s) as total_s, MAX(visited_at) as last_visit FROM user_tracking WHERE id_client=?");
    $tg->execute([$_SESSION['user_id']]);
    $trackGlobal = $tg->fetch(PDO::FETCH_ASSOC) ?: $trackGlobal;

    $tp = $pdo->prepare("SELECT page, COUNT(*) as v, SUM(duration_s) as s FROM user_tracking WHERE id_client=? AND visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) GROUP BY page ORDER BY v DESC LIMIT 6");
    $tp->execute([$_SESSION['user_id']]);
    $trackPages = $tp->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { /* Table pas encore créée */ }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Mon Profil - Entre AuTout</title>
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .profile-header { background:#212529; padding:60px 0; color:white; margin-bottom:30px; }
        .card-custom { border:none; border-radius:15px; box-shadow:0 10px 30px rgba(0,0,0,0.08); }
        .info-row { border-bottom:1px solid #f0f0f0; padding:10px 0; }
        .info-row:last-child { border-bottom:none; }
        .section-tab { cursor:pointer; padding:8px 16px; border-radius:8px; font-size:14px; font-weight:500; border:none; background:transparent; color:#666; }
        .section-tab.active { background:#0d6efd; color:white; }
        .tab-content-panel { display:none; }
        .tab-content-panel.active { display:block; }
        .edit-mode { display:none; }
        .alert-float { position:fixed; top:80px; right:20px; z-index:9999; min-width:280px; animation:slideIn .3s ease; }
        @keyframes slideIn { from{transform:translateX(100%);opacity:0} to{transform:translateX(0);opacity:1} }
        .input-wrapper { position:relative; }
        .toggle-eye { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#aaa; padding:0; }
        .toggle-eye:hover { color:#333; }
        .error-msg { color:#dc3545; font-size:12px; margin-top:4px; display:none; }
        .team-member-card { border-radius:10px; transition:transform 0.3s; border:1px solid #eee; background:white; }
        .team-member-card:hover { transform:translateY(-5px); }
    </style>
</head>
<body id="page-top">

    <!-- Notification flottante -->
    <?php if (isset($_GET['success'])): ?>
    <div class="alert alert-success alert-dismissible alert-float shadow" id="notif">
        <i class="fas fa-check-circle me-2"></i>
        <?php
        $msgs = [
            'updated'  => 'Profil mis à jour avec succès !',
            'password' => 'Mot de passe changé avec succès !',
        ];
        echo $msgs[$_GET['success']] ?? 'Opération réussie !';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php elseif (isset($_GET['error'])): ?>
    <div class="alert alert-danger alert-dismissible alert-float shadow" id="notif">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?php
        $errs = [
            'update_failed'   => 'Échec de la mise à jour.',
            'invalid_phone'   => 'Téléphone invalide (8 chiffres requis).',
            'wrong_password'  => 'Ancien mot de passe incorrect.',
            'password_failed' => 'Erreur lors du changement de mot de passe.',
        ];
        echo $errs[$_GET['error']] ?? 'Une erreur est survenue.';
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container">
            <a class="navbar-brand" href="home.php">Entre AuTout</a>
            <div class="ms-auto d-flex gap-2">
                <a class="btn btn-outline-light btn-sm" href="home.php">
                    <i class="fas fa-home me-1"></i> Accueil
                </a>
                <a class="btn btn-danger btn-sm"  href="/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/UserController.php?action=logout">
                    <i class="fas fa-sign-out-alt me-1"></i> Déconnexion
                </a>
            </div>
        </div>
    </nav>

    <!-- Header profil -->
    <header class="profile-header text-center mt-5">
        <div class="container">
            <div class="bg-primary text-white rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center fw-bold"
                 style="width:80px;height:80px;font-size:2rem;">
                <?= strtoupper(mb_substr($prenom,0,1) . mb_substr($nom,0,1)) ?>
            </div>
            <h1 class="display-5 fw-bold"><?= htmlspecialchars($prenom . ' ' . $nom) ?></h1>
            <span class="badge bg-primary" style="font-size:.8rem;padding:5px 14px;border-radius:20px;">Client</span>
            <p class="mt-2 text-white-50 small">Membre depuis le <?= $date_i ?></p>
        </div>
    </header>

    <div class="container mb-5">

        <!-- Onglets -->
        <div class="d-flex gap-2 mb-4 flex-wrap">
            <button class="section-tab active" onclick="switchTab('infos', this)">
                <i class="fas fa-user me-1"></i> Mes Informations
            </button>
            <button class="section-tab" onclick="switchTab('securite', this)">
                <i class="fas fa-lock me-1"></i> Sécurité
            </button>
            <button class="section-tab" onclick="switchTab('services', this)">
                <i class="fas fa-car me-1"></i> Mes Services
            </button>
            <button class="section-tab" onclick="switchTab('parcours', this)">
                <i class="fas fa-map me-1"></i> Mon Parcours
            </button>
            <button class="section-tab" onclick="switchTab('activite', this)">
                <i class="fas fa-chart-bar me-1"></i> Mon Activité
            </button>
            <button class="section-tab" onclick="switchTab('equipe', this)">
                <i class="fas fa-users me-1"></i> Équipe
            </button>
        </div>

        <!-- ══ ONGLET 1 : Informations ══ -->
        <div class="tab-content-panel active" id="tab-infos">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="card card-custom h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h4 class="fw-bold mb-0">Mes Informations</h4>
                                <button class="btn btn-sm btn-primary" id="btnToggleEdit" onclick="toggleEdit()">
                                    <i class="fas fa-edit me-1"></i> Modifier
                                </button>
                            </div>

                            <!-- Vue -->
                            <div id="viewMode">
                                <div class="info-row"><span class="text-muted small d-block">Nom Complet</span>
                                    <strong><?= htmlspecialchars($prenom . ' ' . $nom) ?></strong></div>
                                <div class="info-row"><span class="text-muted small d-block">Email</span>
                                    <strong><?= htmlspecialchars($email) ?></strong></div>
                                <div class="info-row"><span class="text-muted small d-block">Téléphone</span>
                                    <strong><?= htmlspecialchars($tel ?: '—') ?></strong></div>
                                <div class="info-row"><span class="text-muted small d-block">Adresse</span>
                                    <strong><?= htmlspecialchars($adresse ?: '—') ?></strong></div>
                                <div class="info-row"><span class="text-muted small d-block">Inscription</span>
                                    <strong><?= $date_i ?></strong></div>
                            </div>

                            <!-- Formulaire édition -->
                            <div id="editMode" class="edit-mode">
                                <form id="formUpdate" action="/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/UserController.php" method="POST"
                                      onsubmit="return validateUpdate()">
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="mb-3">
                                        <label class="small text-muted">Prénom</label>
                                        <input type="text" name="prenom" id="upd-prenom" class="form-control"
                                               value="<?= htmlspecialchars($prenom) ?>">
                                        <div class="error-msg" id="err-upd-prenom">⚠ Le prénom est obligatoire.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small text-muted">Nom</label>
                                        <input type="text" name="nom" id="upd-nom" class="form-control"
                                               value="<?= htmlspecialchars($nom) ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label class="small text-muted">Téléphone (8 chiffres)</label>
                                        <input type="text" name="telephone" id="upd-tel" class="form-control"
                                               maxlength="8" value="<?= htmlspecialchars($tel) ?>">
                                        <div class="error-msg" id="err-upd-tel">⚠ Le téléphone doit contenir exactement 8 chiffres.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="small text-muted">Adresse</label>
                                        <textarea name="adresse" class="form-control" rows="2"><?= htmlspecialchars($adresse) ?></textarea>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <button type="submit" class="btn btn-success flex-grow-1">
                                            <i class="fas fa-save me-1"></i> Enregistrer
                                        </button>
                                        <button type="button" class="btn btn-light" onclick="toggleEdit()">Annuler</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Résumé rapide -->
                <div class="col-lg-6">
                    <div class="card card-custom h-100 bg-light border-0">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">Accès rapides</h5>
                            <div class="row g-3">
                                <div class="col-6"><div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="fas fa-calendar-check fa-2x text-primary mb-2"></i>
                                    <p class="small fw-bold mb-0">Rendez-vous</p>
                                </div></div>
                                <div class="col-6"><div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="fas fa-shopping-bag fa-2x text-success mb-2"></i>
                                    <p class="small fw-bold mb-0">Commandes</p>
                                </div></div>
                                <div class="col-6"><div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="fas fa-car fa-2x text-warning mb-2"></i>
                                    <p class="small fw-bold mb-0">Véhicules</p>
                                </div></div>
                                <div class="col-6"><div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="fas fa-envelope fa-2x text-info mb-2"></i>
                                    <p class="small fw-bold mb-0">Messagerie</p>
                                </div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ ONGLET 2 : Sécurité ══ -->
        <div class="tab-content-panel" id="tab-securite">
            <div class="row g-4">

                <!-- Changer mot de passe -->
                <div class="col-lg-6">
                    <div class="card card-custom">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1"><i class="fas fa-key me-2 text-primary"></i>Changer le mot de passe</h5>
                            <p class="text-muted small mb-4">Choisissez un mot de passe sécurisé d'au moins 6 caractères.</p>

                            <form id="formPassword" action="/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/UserController.php" method="POST"
                                  onsubmit="return validatePassword()">
                                <input type="hidden" name="action" value="change_password">

                                <div class="mb-3">
                                    <label class="small text-muted">Ancien mot de passe</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="old_password" id="old_pass" class="form-control" placeholder="••••••••">
                                        <button type="button" class="toggle-eye" onclick="toggleEye('old_pass','eye-old')">
                                            <i class="fas fa-eye" id="eye-old"></i>
                                        </button>
                                    </div>
                                    <div class="error-msg" id="err-old-pass">⚠ Ce champ est obligatoire.</div>
                                </div>

                                <div class="mb-3">
                                    <label class="small text-muted">Nouveau mot de passe</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="new_password" id="new_pass" class="form-control"
                                               placeholder="Min. 6 caractères" oninput="checkStrengthProfile(this.value)">
                                        <button type="button" class="toggle-eye" onclick="toggleEye('new_pass','eye-new')">
                                            <i class="fas fa-eye" id="eye-new"></i>
                                        </button>
                                    </div>
                                    <div class="mt-2 bg-secondary rounded" style="height:5px;">
                                        <div id="profileStrength" style="width:0;height:5px;border-radius:3px;transition:width .3s,background .3s;"></div>
                                    </div>
                                    <small id="profileStrengthLabel" class="text-muted"></small>
                                    <div class="error-msg" id="err-new-pass">⚠ Le mot de passe doit contenir au moins 6 caractères.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="small text-muted">Confirmer le nouveau mot de passe</label>
                                    <div class="input-wrapper">
                                        <input type="password" name="confirm_password" id="confirm_pass" class="form-control" placeholder="••••••••">
                                        <button type="button" class="toggle-eye" onclick="toggleEye('confirm_pass','eye-confirm')">
                                            <i class="fas fa-eye" id="eye-confirm"></i>
                                        </button>
                                    </div>
                                    <div class="error-msg" id="err-confirm-pass">⚠ Les mots de passe ne correspondent pas.</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-save me-1"></i> Changer le mot de passe
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Supprimer le compte -->
                <div class="col-lg-6">
                    <div class="card card-custom border-danger">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1 text-danger"><i class="fas fa-trash-alt me-2"></i>Supprimer mon compte</h5>
                            <p class="text-muted small mb-4">
                                Cette action est <strong>irréversible</strong>. Toutes vos données seront définitivement supprimées.
                            </p>
                            <button class="btn btn-outline-danger w-100" onclick="confirmDelete()">
                                <i class="fas fa-trash me-2"></i> Supprimer mon compte
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ══ ONGLET 3 : Services ══ -->
        <div class="tab-content-panel" id="tab-services">
            <div class="card card-custom">
                <div class="card-body p-4">
                    <h5 class="fw-bold mb-4"><i class="fas fa-microchip me-2 text-primary"></i>Dernier Diagnostic</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-microchip fa-2x text-success mb-2"></i>
                            <p class="small text-muted mb-1">Moteur</p>
                            <span class="badge bg-success">Normal</span>
                        </div></div>
                        <div class="col-md-4"><div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-battery-three-quarters fa-2x text-warning mb-2"></i>
                            <p class="small text-muted mb-1">Système Élec.</p>
                            <span class="badge bg-warning text-dark">À surveiller</span>
                        </div></div>
                        <div class="col-md-4"><div class="p-3 bg-light rounded text-center">
                            <i class="fas fa-oil-can fa-2x text-success mb-2"></i>
                            <p class="small text-muted mb-1">Maintenance</p>
                            <span class="badge bg-success">À jour</span>
                        </div></div>
                    </div>
                    <div class="p-3 bg-light rounded border-start border-primary border-4">
                        <h6 class="fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Note du technicien</h6>
                        <p class="small mb-0 text-muted">Fonctionnement normal du contrôleur moteur. Prochain contrôle recommandé dans <strong>5 000 km</strong>.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- ══ ONGLET 4 : Équipe ══ -->
        <div class="tab-content-panel" id="tab-equipe">
            <div class="row g-4">
                <?php
                $team = [
                    ['name'=>'Insaf Ben Ahmed',    'role'=>'Responsable Relation Client',   'icon'=>'fa-headset'],
                    ['name'=>'Asma Ayachi',        'role'=>'Directrice Technique (CTO)',     'icon'=>'fa-user-gear'],
                    ['name'=>'Mohamed Adel Makni', 'role'=>'Expert Diagnostic Systèmes',    'icon'=>'fa-laptop-code'],
                    ['name'=>'Amen Naghmouchi',    'role'=>'Chef d\'Atelier',               'icon'=>'fa-screwdriver-wrench'],
                    ['name'=>'Ela Hmida',          'role'=>'Responsable Marketing Digital', 'icon'=>'fa-palette'],
                    ['name'=>'Rayen Ben Selem',    'role'=>'Ingénieur Maintenance Auto',    'icon'=>'fa-car-side'],
                ];
                foreach ($team as $m): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="team-member-card p-4 text-center">
                        <div class="bg-dark text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center"
                             style="width:55px;height:55px;">
                            <i class="fas <?= $m['icon'] ?> fa-lg"></i>
                        </div>
                        <h6 class="fw-bold mb-1"><?= htmlspecialchars($m['name']) ?></h6>
                        <p class="small text-muted mb-0"><?= htmlspecialchars($m['role']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>


        <!-- ══ ONGLET 5 : Mon Parcours ══ -->
        <div class="tab-content-panel" id="tab-parcours">
            <?php if ($j): ?>
            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card card-custom">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-1"><i class="fas fa-map me-2 text-primary"></i>Mon Parcours Guidé</h5>
                            <p class="text-muted small mb-3">Complétez les étapes pour maîtriser la plateforme.</p>
                            <!-- Barre globale -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="small fw-semibold">Progression globale</span>
                                    <span class="small fw-bold text-primary"><?= $jPct ?>%</span>
                                </div>
                                <div style="height:10px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                                    <div style="height:100%;width:<?= $jPct ?>%;background:linear-gradient(90deg,#6366f1,#3b82f6);border-radius:99px;transition:width .6s;"></div>
                                </div>
                                <p class="text-muted small mt-1 mb-0"><?= $jScore ?>/6 étapes complétées</p>
                            </div>
                            <?php
                            $steps_p = [
                                ['key'=>'step_profile',    'icon'=>'👤','label'=>'Compléter le profil',  'tip'=>'Téléphone + adresse renseignés'],
                                ['key'=>'step_diagnostic', 'icon'=>'🔍','label'=>'Explorer les services','tip'=>'Visiter la section Services'],
                                ['key'=>'step_garage',  'icon'=>'🏢','label'=>'Consulter un garage',  'tip'=>'Voir les garages partenaires'],
                                ['key'=>'step_vehicle', 'icon'=>'🚗','label'=>'Ajouter un véhicule',  'tip'=>'Enregistrer votre voiture'],
                                ['key'=>'step_rdv',     'icon'=>'📅','label'=>'Prendre rendez-vous',  'tip'=>'Réserver un créneau'],
                                ['key'=>'step_message', 'icon'=>'✉️','label'=>'Envoyer un message',   'tip'=>'Contacter un technicien'],
                            ];
                            foreach ($steps_p as $i => $sp):
                                $done_p = !empty($j[$sp['key']]);
                            ?>
                            <div class="d-flex align-items-center gap-3 p-2 rounded-3 mb-2"
                                 style="background:<?= $done_p ? '#f0fdf4' : '#f8fafc' ?>;border:1px solid <?= $done_p ? '#bbf7d0' : '#e2e8f0' ?>;">
                                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     style="width:32px;height:32px;background:<?= $done_p ? '#16a34a' : '#e2e8f0' ?>;color:<?= $done_p ? 'white' : '#94a3b8' ?>;font-size:12px;">
                                    <?= $done_p ? '✓' : ($i+1) ?>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small fw-<?= $done_p ? '500' : '600' ?>" style="color:<?= $done_p ? '#94a3b8' : '#1e293b' ?>;text-decoration:<?= $done_p ? 'line-through' : 'none' ?>;">
                                        <?= $sp['icon'] ?> <?= htmlspecialchars($sp['label']) ?>
                                    </div>
                                    <div style="font-size:.7rem;color:#94a3b8;"><?= htmlspecialchars($sp['tip']) ?></div>
                                </div>
                                <?php if (!$done_p): ?>
                                <button onclick="markJStep('<?= $sp['key'] ?>')" class="btn btn-sm btn-outline-primary" style="font-size:.72rem;">Faire</button>
                                <?php else: ?>
                                <span class="badge" style="background:#dcfce7;color:#16a34a;font-size:.7rem;">Fait</span>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card card-custom text-white h-100" style="background:linear-gradient(135deg,#6366f1,#3b82f6);">
                        <div class="card-body p-4 text-center">
                            <h5 class="fw-bold mb-3">🏆 Mon Niveau</h5>
                            <?php
                            $lvls=[['min'=>0,'name'=>'Débutant','e'=>'🌱'],['min'=>2,'name'=>'Explorateur','e'=>'🔍'],['min'=>4,'name'=>'Conducteur','e'=>'🚗'],['min'=>6,'name'=>'Expert','e'=>'🏆']];
                            $lvl=$lvls[0]; foreach($lvls as $lv) if($jScore>=$lv['min']) $lvl=$lv;
                            ?>
                            <div style="font-size:3.5rem;margin-bottom:8px;"><?= $lvl['e'] ?></div>
                            <h3 class="fw-bold"><?= $lvl['name'] ?></h3>
                            <p class="opacity-75 small"><?= $jScore ?>/6 étapes · <?= $jPct ?>%</p>
                            <div style="height:8px;background:rgba(255,255,255,.2);border-radius:99px;overflow:hidden;margin-top:16px;">
                                <div style="height:100%;width:<?= $jPct ?>%;background:white;border-radius:99px;"></div>
                            </div>
                            <div class="mt-3">
                                <button onclick="jResetProfile()" class="btn btn-sm btn-outline-light" style="font-size:.72rem;">Réinitialiser</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">Exécutez <code>migration_journey_tracking.sql</code> pour activer le parcours.</div>
            <?php endif; ?>
        </div>

        <!-- ══ ONGLET 6 : Mon Activité ══ -->
        <div class="tab-content-panel" id="tab-activite">
            <div class="row g-3 mb-4">
                <?php
                $tv  = (int)($trackGlobal['total_visits'] ?? 0);
                $ts_ = (int)($trackGlobal['total_s'] ?? 0);
                $tm  = floor($ts_/60);
                $llv = $trackGlobal['last_visit'] ? date('d/m/Y H:i', strtotime($trackGlobal['last_visit'])) : '—';
                $statC = [
                    ['label'=>'Visites totales',    'val'=>$tv,      'icon'=>'fa-mouse-pointer','color'=>'primary'],
                    ['label'=>'Temps total',         'val'=>$tm.' min','icon'=>'fa-clock',       'color'=>'success'],
                    ['label'=>'Pages visitées',      'val'=>count($trackPages), 'icon'=>'fa-file','color'=>'warning'],
                    ['label'=>'Dernière visite',     'val'=>$llv,     'icon'=>'fa-calendar',     'color'=>'info'],
                ];
                foreach ($statC as $sc): ?>
                <div class="col-md-3 col-sm-6">
                    <div class="card card-custom">
                        <div class="card-body d-flex align-items-center gap-3 p-3">
                            <div class="rounded-circle bg-<?= $sc['color'] ?>-subtle text-<?= $sc['color'] ?> d-flex align-items-center justify-content-center flex-shrink-0"
                                 style="width:42px;height:42px;">
                                <i class="fas <?= $sc['icon'] ?>"></i>
                            </div>
                            <div>
                                <div class="fw-bold" style="font-size:.95rem;"><?= htmlspecialchars((string)$sc['val']) ?></div>
                                <div class="text-muted" style="font-size:.72rem;"><?= $sc['label'] ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="row g-4">
                <!-- Pages visitées -->
                <div class="col-lg-7">
                    <div class="card card-custom">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-chart-bar me-2 text-primary"></i>Pages visitées (30 jours)</h5>
                            <?php if (empty($trackPages)): ?>
                            <p class="text-muted small text-center py-4">
                                Aucune activité enregistrée.<br>
                                <small>Assurez-vous que <code>tracking.js</code> est bien inclus et que la table <code>user_tracking</code> existe.</small>
                            </p>
                            <?php else:
                                $maxV = max(array_column($trackPages,'v'));
                                foreach($trackPages as $tp):
                                    $pctBar = $maxV>0 ? round($tp['v']/$maxV*100) : 0;
                                    $durM   = floor($tp['s']/60);
                            ?>
                            <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid #f1f5f9;">
                                <span style="width:80px;font-size:.78rem;font-weight:600;color:#374151;flex-shrink:0;"><?= htmlspecialchars($tp['page']) ?></span>
                                <div style="flex:1;height:7px;background:#f1f5f9;border-radius:99px;overflow:hidden;">
                                    <div style="height:100%;width:<?= $pctBar ?>%;background:linear-gradient(90deg,#6366f1,#3b82f6);border-radius:99px;"></div>
                                </div>
                                <span class="badge bg-primary-subtle text-primary" style="font-size:.72rem;"><?= $tp['v'] ?>x</span>
                                <span style="font-size:.7rem;color:#9ca3af;min-width:40px;text-align:right;"><?= $durM ?>min</span>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Session en cours -->
                <div class="col-lg-5">
                    <div class="card card-custom">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-stopwatch me-2 text-success"></i>Session en cours</h5>
                            <div class="text-center py-3">
                                <div id="liveTimer" style="font-size:2.8rem;font-weight:700;color:#0d6efd;font-variant-numeric:tabular-nums;letter-spacing:2px;">00:00</div>
                                <p class="text-muted small mt-1">Temps sur cette page</p>
                            </div>
                            <hr>
                            <div class="small">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Page actuelle</span>
                                    <strong>profile</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted">Historique local</span>
                                    <button class="btn btn-sm btn-outline-primary" style="font-size:.72rem;padding:2px 10px;" onclick="showLocalHist()">Voir</button>
                                </div>
                                <div id="localHistBox" style="display:none;max-height:150px;overflow-y:auto;margin-top:8px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /container -->

    <!-- Modal confirmation suppression -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header border-danger">
                    <h5 class="modal-title text-danger fw-bold">
                        <i class="fas fa-exclamation-triangle me-2"></i>Confirmer la suppression
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir supprimer votre compte ?</p>
                    <p class="text-danger small mb-0"><strong>⚠ Cette action est irréversible.</strong> Toutes vos données seront perdues.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/UserController.php?action=delete_account"
                       class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Oui, supprimer
                    </a>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 bg-dark text-white text-center">
        <small>Entre AuTout &copy; 2026 — Système de Gestion Automobile</small>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/front/js/tracking.js"></script>
    <script>
    // ── Onglets ───────────────────────────────────────────────
    function switchTab(name, btn) {
        document.querySelectorAll('.tab-content-panel').forEach(p => p.classList.remove('active'));
        document.querySelectorAll('.section-tab').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-' + name).classList.add('active');
        btn.classList.add('active');
    }

    // ── Toggle affichage formulaire édition ───────────────────
    function toggleEdit() {
        const view = document.getElementById('viewMode');
        const edit = document.getElementById('editMode');
        const btn  = document.getElementById('btnToggleEdit');
        const open = edit.style.display === '' || edit.style.display === 'none';
        edit.style.display = open ? 'block' : 'none';
        view.style.display = open ? 'none'  : 'block';
        btn.innerHTML  = open ? '<i class="fas fa-times me-1"></i> Annuler' : '<i class="fas fa-edit me-1"></i> Modifier';
        btn.className  = open ? 'btn btn-sm btn-outline-secondary' : 'btn btn-sm btn-primary';
    }

    // ── Voir / Masquer mot de passe ───────────────────────────
    function toggleEye(inputId, iconId) {
        const input = document.getElementById(inputId);
        const icon  = document.getElementById(iconId);
        if (input.type === 'password') {
            input.type = 'text'; icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password'; icon.className = 'fas fa-eye';
        }
    }

    // ── Modal suppression ─────────────────────────────────────
    function confirmDelete() {
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
    }

    // ── Validation mise à jour profil (JS uniquement) ─────────
    function validateUpdate() {
        let valid = true;
        const prenom = document.getElementById('upd-prenom').value.trim();
        const tel    = document.getElementById('upd-tel').value.trim();

        const errPrenom = document.getElementById('err-upd-prenom');
        if (prenom.length === 0) { errPrenom.style.display = 'block'; valid = false; }
        else                      { errPrenom.style.display = 'none'; }

        const errTel = document.getElementById('err-upd-tel');
        if (tel.length > 0 && !/^[0-9]{8}$/.test(tel)) {
            errTel.style.display = 'block'; valid = false;
        } else { errTel.style.display = 'none'; }

        return valid;
    }

    // ── Validation changement de mot de passe (JS uniquement) ─
    function validatePassword() {
        let valid = true;

        const oldPass     = document.getElementById('old_pass').value;
        const newPass     = document.getElementById('new_pass').value;
        const confirmPass = document.getElementById('confirm_pass').value;

        const errOld     = document.getElementById('err-old-pass');
        const errNew     = document.getElementById('err-new-pass');
        const errConfirm = document.getElementById('err-confirm-pass');

        // Ancien mdp obligatoire
        if (oldPass.length === 0) { errOld.style.display = 'block'; valid = false; }
        else                       { errOld.style.display = 'none'; }

        // Nouveau mdp min 6
        if (newPass.length < 6) { errNew.style.display = 'block'; valid = false; }
        else                     { errNew.style.display = 'none'; }

        // Confirmation identique
        if (newPass !== confirmPass) { errConfirm.style.display = 'block'; valid = false; }
        else                          { errConfirm.style.display = 'none'; }

        return valid;
    }

    // ── Barre de force nouveau mdp ────────────────────────────
    function checkStrengthProfile(v) {
        const bar   = document.getElementById('profileStrength');
        const label = document.getElementById('profileStrengthLabel');
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

    // ── Auto-dismiss toast ────────────────────────────────────
    setTimeout(() => {
        const n = document.getElementById('notif');
        if (n) n.style.display = 'none';
    }, 4000);

    // ── Parcours : marquer une étape ─────────────────────────
    function markJStep(key) {
        fetch('/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/JourneyController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'mark_step', step: key })
        })
        .then(r => r.json())
        .then(d => { if (d.success) location.reload(); })
        .catch(() => {});
    }

    function jResetProfile() {
        if (!confirm('Réinitialiser le parcours ?')) return;
        fetch('/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/JourneyController.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'reset' })
        })
        .then(() => location.reload())
        .catch(() => location.reload());
    }

    // ── Timer session en cours ────────────────────────────────
    (function() {
        const start = Date.now();
        const el = document.getElementById('liveTimer');
        if (!el) return;
        setInterval(() => {
            const s = Math.floor((Date.now() - start) / 1000);
            const mm = String(Math.floor(s / 60)).padStart(2, '0');
            const ss = String(s % 60).padStart(2, '0');
            el.textContent = mm + ':' + ss;
        }, 1000);
    })();

    // ── Historique local ──────────────────────────────────────
    function showLocalHist() {
        const box = document.getElementById('localHistBox');
        if (box.style.display !== 'none') { box.style.display = 'none'; return; }
        try {
            const hist = JSON.parse(localStorage.getItem('at_hist') || '[]');
            if (!hist.length) { box.innerHTML = '<p class="text-muted small mb-0">Aucun historique local.</p>'; }
            else {
                box.innerHTML = hist.slice(0, 10).map(h =>
                    `<div class="d-flex justify-content-between small py-1 border-bottom">
                        <span class="fw-semibold">${h.page}</span>
                        <span class="text-muted">${h.dur}s · ${new Date(h.ts).toLocaleTimeString()}</span>
                    </div>`
                ).join('');
            }
        } catch(e) { box.innerHTML = '<p class="text-muted small mb-0">Erreur de lecture.</p>'; }
        box.style.display = 'block';
    }
    </script>
</body>
</html>