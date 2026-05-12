<?php
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path' => '/Esprit-PI-2PREPA-2026-EntreAUtous/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Journey & Tracking
try { require_once __DIR__ . '/../../models/db.php'; } catch(Exception $e) { $pdo = null; }
$isLoggedIn = isset($_SESSION['user']);
$userName   = $isLoggedIn ? $_SESSION['user'] : "";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Entre AuTout - Tout pour votre voiture" />
    <meta name="author" content="Equipe 6" />
    <title>Entre AuTout - Accueil</title>
    <link rel="icon" type="image/x-icon" href="../../assets/front/assets/favicon.ico" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" />
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <style>
        /* ── Dropdown Services ── */
        .dropdown-menu { border-radius: 12px; border: none; box-shadow: 0 10px 40px rgba(0,0,0,0.15); padding: 10px; min-width: 220px; }
        .dropdown-item { border-radius: 8px; padding: 10px 14px; font-size: 14px; transition: background .15s; }
        .dropdown-item:hover { background: #f0f4ff; color: #0d6efd; }
        .dropdown-item .icon-wrap { width: 32px; height: 32px; border-radius: 8px; display: inline-flex; align-items: center; justify-content: center; margin-right: 10px; font-size: 14px; }
        .dropdown-divider { margin: 6px 0; }
        .nav-item.dropdown .nav-link::after { margin-left: 4px; }

        /* ── Section À Propos ── */
        #about { padding: 80px 0; background: #fff; }
        .about-badge { display: inline-block; background: #e8f0fe; color: #0d6efd; font-size: 12px; font-weight: 600; padding: 4px 14px; border-radius: 20px; letter-spacing: .05em; margin-bottom: 16px; }
        .about-stat { text-align: center; padding: 20px; border-radius: 12px; background: #f8f9fa; }
        .about-stat .stat-number { font-size: 2rem; font-weight: 700; color: #0d6efd; }
        .about-stat .stat-label { font-size: 13px; color: #6c757d; margin: 0; }
        .about-img-wrap { position: relative; }
        .about-img-wrap img { border-radius: 16px; width: 100%; object-fit: cover; max-height: 380px; }
        .about-img-badge {
            position: absolute; bottom: -16px; right: -16px;
            background: #0d6efd; color: white;
            border-radius: 12px; padding: 14px 20px;
            font-weight: 700; font-size: 14px;
            box-shadow: 0 8px 24px rgba(13,110,253,0.35);
        }

        /* ── Cards modules (section services) ── */
        .module-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.07); transition: transform .25s, box-shadow .25s; height: 100%; }
        .module-card:hover { transform: translateY(-6px); box-shadow: 0 12px 32px rgba(0,0,0,0.13); }
        .module-icon { width: 64px; height: 64px; border-radius: 16px; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; font-size: 24px; }

        /* ── Section modules intégration (placeholder) ── */
        #modules-integration { background: #f8f9fa; padding: 80px 0; }
        .integration-card { border: 2px dashed #dee2e6; border-radius: 16px; padding: 30px 20px; text-align: center; transition: border-color .2s, background .2s; cursor: default; }
        .integration-card:hover { border-color: #0d6efd; background: #f0f4ff; }
        .integration-card .int-icon { font-size: 2rem; margin-bottom: 10px; }
        .integration-card .int-badge { font-size: 11px; padding: 2px 10px; border-radius: 20px; }
    </style>
</head>
<body id="page-top">

    <!-- ══ NAVBAR ══════════════════════════════════════════════ -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">Entre AuTout</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarResponsive" aria-controls="navbarResponsive"
                    aria-expanded="false" aria-label="Toggle navigation">
                Menu <i class="fas fa-bars ms-1"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">

                    <!-- Services → dropdown avec tous les modules -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navServices"
                           role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Services
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navServices">
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="#services">
                                    <span class="icon-wrap bg-primary-subtle text-primary"><i class="fas fa-th-large"></i></span>
                                    Tous les services
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/FrontOffice/front.php">
                                    <span class="icon-wrap bg-success-subtle text-success"><i class="fas fa-building"></i></span>
                                    Garages
                                    <span class="ms-auto badge bg-secondary int-badge">Module Rayen</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="/Esprit-PI-2PREPA-2026-EntreAUtous/">
                                    <span class="icon-wrap bg-warning-subtle text-warning"><i class="fas fa-shopping-cart"></i></span>
                                    Vente de Pièces
                                    <span class="ms-auto badge bg-secondary int-badge">Module Amen </span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="GestionVehicule.php">
                                    <span class="icon-wrap bg-danger-subtle text-danger"><i class="fas fa-car"></i></span>
                                    Véhicules
                                    <span class="ms-auto badge bg-secondary int-badge">Module Ela</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="liste_entretien.php">
                                    <span class="icon-wrap bg-info-subtle text-info"><i class="fas fa-microchip"></i></span>
                                    Entretien
                                    <span class="ms-auto badge bg-secondary int-badge">Module Asma</span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="front.php">
                                    <span class="icon-wrap bg-purple-subtle" style="background:#f3e8ff;color:#7c3aed"><i class="fas fa-envelope"></i></span>
                                    Messagerie
                                    <span class="ms-auto badge bg-secondary int-badge">Module Mohamed </span>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex align-items-center" href="profile.php">
                                    <span class="icon-wrap bg-primary-subtle text-primary"><i class="fas fa-user"></i></span>
                                    Mon Compte
                                    <span class="ms-auto badge bg-primary int-badge">Module Insaf</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- À Propos (anciennement Véhicules) -->
                    <li class="nav-item"><a class="nav-link" href="#about">À Propos</a></li>

                    <!-- Équipe -->
                    <li class="nav-item"><a class="nav-link" href="#team">Équipe</a></li>

                    <!-- Profil / Connexion -->
                    <?php if ($isLoggedIn): ?>
                        <li class="nav-item">
                            <a class="nav-link btn btn-primary text-white ms-lg-3 px-3" href="profile.php">
                                <i class="fas fa-user-circle me-1"></i>
                                <?= htmlspecialchars($userName) ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </nav>

    <!-- ══ HERO ════════════════════════════════════════════════ -->
    <header class="masthead" style="background-image: linear-gradient(rgba(0,0,0,0.55), rgba(0,0,0,0.55)), url('../../assets/front/img/voiture.jpg') !important; background-size: cover !important; background-position: center !important;">
        <div class="container">
            <div class="masthead-subheading">Bienvenue chez Entre AuTout !</div>
            <div class="masthead-heading text-uppercase">L'innovation au service de votre auto</div>
            <a class="btn btn-primary btn-xl text-uppercase me-2" href="#services">Nos Services</a>
            <a class="btn btn-outline-light btn-xl text-uppercase" href="#about">À Propos</a>
        </div>
    </header>

    <!-- ══ SECTION SERVICES / MODULES ══════════════════════════ -->
    <section class="page-section" id="services">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-heading text-uppercase">Nos Modules</h2>
                <h3 class="section-subheading text-muted">Tout ce dont vous avez besoin pour votre véhicule en un seul endroit.</h3>
            </div>
            <div class="row g-4 justify-content-center">

                <div class="col-lg-4 col-md-6">
                    <div class="card module-card p-4 text-center">
                        <div class="module-icon bg-success-subtle text-success">
                            <i class="fas fa-building"></i>
                        </div>
                        <h5 class="fw-bold">Garages & Rendez-vous</h5>
                        <p class="text-muted small">Trouvez les meilleurs garages près de chez vous et réservez votre créneau en ligne.</p>
                        <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/FrontOffice/front.php" class="btn btn-outline-success btn-sm mt-2">Accéder</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card module-card p-4 text-center">
                        <div class="module-icon bg-warning-subtle text-warning">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h5 class="fw-bold">Vente de Pièces</h5>
                        <p class="text-muted small">Achetez vos pièces de rechange certifiées en ligne avec livraison rapide à domicile.</p>
                        <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/" class="btn btn-outline-warning btn-sm mt-2">Accéder</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card module-card p-4 text-center">
                        <div class="module-icon bg-danger-subtle text-danger">
                            <i class="fas fa-car"></i>
                        </div>
                        <h5 class="fw-bold">Gestion Véhicules</h5>
                        <p class="text-muted small">Gérez votre parc automobile, consultez les fiches techniques et l'historique de vos véhicules.</p>
                        <a href="GestionVehicule.php" class="btn btn-outline-danger btn-sm mt-2">Accéder</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card module-card p-4 text-center">
                        <div class="module-icon bg-info-subtle text-info">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <h5 class="fw-bold">Entretien</h5>
                        <p class="text-muted small">Analysez l'état de votre véhicule grâce à notre module de Entretien intelligent.</p>
                        <a href="liste_entretien.php" class="btn btn-outline-info btn-sm mt-2">Accéder</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card module-card p-4 text-center">
                        <div class="module-icon" style="background:#f3e8ff;color:#7c3aed;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5 class="fw-bold">Messagerie</h5>
                        <p class="text-muted small">Communiquez directement avec les garages et les techniciens de notre plateforme.</p>
                        <a href="#" class="btn btn-sm mt-2" style="border:1px solid #7c3aed;color:#7c3aed;">Accéder</a>
                    </div>
                </div>

                <div class="col-lg-4 col-md-6">
                    <div class="card module-card p-4 text-center">
                        <div class="module-icon bg-primary-subtle text-primary">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <h5 class="fw-bold">Mon Compte</h5>
                        <p class="text-muted small">Gérez votre profil, votre sécurité et accédez à tous vos services personnalisés.</p>
                        <a href="<?= $isLoggedIn ? 'profile.php' : 'login.php' ?>" class="btn btn-outline-primary btn-sm mt-2">
                            <?= $isLoggedIn ? 'Mon Profil' : 'Se connecter' ?>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ══ SECTION À PROPOS ═════════════════════════════════════ -->
    <section id="about" class="page-section bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-heading text-uppercase">À Propos</h2>
                <h3 class="section-subheading text-muted">Découvrez qui nous sommes et ce qui nous anime.</h3>
            </div>

            <div class="row align-items-center g-5 mb-5">
                <!-- Image -->
                <div class="col-lg-5">
                    <div class="about-img-wrap">
                        <img src="../../assets/front/img/voiture.jpg" alt="Entre AuTout"
                             onerror="this.style.background='linear-gradient(135deg,#0d6efd,#6610f2)';this.style.height='320px';this.removeAttribute('src')">
                        <div class="about-img-badge">
                            <div style="font-size:1.5rem;">🚗</div>
                            <div>Depuis 2024</div>
                        </div>
                    </div>
                </div>

                <!-- Texte -->
                <div class="col-lg-7">
                    <span class="about-badge text-uppercase">Notre Histoire</span>
                    <h3 class="fw-bold mb-3">Une plateforme conçue par des passionnés de l'automobile</h3>
                    <p class="text-muted mb-3">
                        <strong>Entre AuTout</strong> est né d'un projet académique porté par 6 étudiants passionnés.
                        Notre mission : centraliser tous les services liés à l'automobile en une seule plateforme
                        moderne, intuitive et accessible à tous.
                    </p>
                    <p class="text-muted mb-4">
                        De la gestion de votre profil client à la vente de pièces en ligne, en passant par
                        la prise de rendez-vous en garage et le diagnostic intelligent, nous couvrons
                        l'intégralité du cycle de vie de votre véhicule.
                    </p>

                    <!-- Stats -->
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="about-stat">
                                <div class="stat-number">6</div>
                                <p class="stat-label">Modules</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="about-stat">
                                <div class="stat-number">6</div>
                                <p class="stat-label">Développeurs</p>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="about-stat">
                                <div class="stat-number">1</div>
                                <p class="stat-label">Plateforme</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valeurs -->
            <div class="row g-4 mt-2">
                <div class="col-md-4 text-center">
                    <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                        <i class="fas fa-shield-alt fa-2x text-primary mb-3"></i>
                        <h5 class="fw-bold">Fiabilité</h5>
                        <p class="text-muted small mb-0">Des services vérifiés et des données sécurisées pour vous offrir une expérience de confiance.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                        <i class="fas fa-bolt fa-2x text-warning mb-3"></i>
                        <h5 class="fw-bold">Rapidité</h5>
                        <p class="text-muted small mb-0">Une interface fluide et des réponses instantanées pour gagner du temps au quotidien.</p>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="p-4 bg-white rounded-3 shadow-sm h-100">
                        <i class="fas fa-users fa-2x text-success mb-3"></i>
                        <h5 class="fw-bold">Proximité</h5>
                        <p class="text-muted small mb-0">Un lien direct entre les clients et les professionnels de l'automobile de votre région.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ══ SECTION ÉQUIPE ═══════════════════════════════════════ -->
    <section class="page-section" id="team">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="section-heading text-uppercase">Notre Équipe</h2>
                <h3 class="section-subheading text-muted">Les 6 développeurs derrière Entre AuTout.</h3>
            </div>
            <div class="row g-4">
                <?php
                $team = [
                    ['name' => 'Insaf Ben Ahmed',    'role' => 'Module Client & Utilisateur',  'icon' => 'fa-user-circle',       'color' => 'primary'],
                    ['name' => 'Ela Ben Hmida',         'role' => 'Module Véhicules',              'icon' => 'fa-car',                'color' => 'danger'],
                    ['name' => 'Asma Ayachi',  'role' => 'Module Entretien',             'icon' => 'fa-microchip',          'color' => 'info'],
                    ['name' => 'Rayen ben salem',     'role' => 'Module Garages',                'icon' => 'fa-building',           'color' => 'success'],
                    ['name' => 'Amen Naghmouchi',           'role' => 'Module Vente de Pièces',        'icon' => 'fa-shopping-cart',      'color' => 'warning'],
                    ['name' => 'Mohamed Adel Makni',     'role' => 'Module Messagerie',             'icon' => 'fa-envelope',           'color' => 'secondary'],
                ];
                foreach ($team as $m): ?>
                <div class="col-lg-4 col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 p-4 text-center h-100" style="transition:transform .25s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                        <div class="mx-auto mb-3 rounded-circle d-flex align-items-center justify-content-center text-white fw-bold"
                             style="width:60px;height:60px;background:var(--bs-<?= $m['color'] ?>);font-size:1.3rem;">
                            <i class="fas <?= $m['icon'] ?>"></i>
                        </div>
                        <h5 class="fw-bold mb-1"><?= htmlspecialchars($m['name']) ?></h5>
                        <span class="badge bg-<?= $m['color'] ?>-subtle text-<?= $m['color'] ?> mb-2" style="font-size:11px;padding:4px 10px;border-radius:20px;">
                            <?= htmlspecialchars($m['role']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- ══ FOOTER ══════════════════════════════════════════════ -->
    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-lg-start">Copyright &copy; Entre AuTout 2026</div>
                <div class="col-lg-4 my-3 my-lg-0 text-center">
                    <a class="btn btn-dark btn-social mx-2" href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a class="link-dark text-decoration-none me-3" href="#">Privacy Policy</a>
                    <a class="link-dark text-decoration-none" href="#">Terms of Use</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Journey Widget -->
    <?php if (isset($pdo) && isset($_SESSION['user_id'])) include __DIR__ . '/journey_widget.php'; ?>

    <!-- Tracking JS -->
    <script src="../../assets/front/js/tracking.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/front/js/scripts.js"></script>
</body>
</html>