<?php
session_start();
// Vérification si l'utilisateur est connecté (optionnel pour la home)
$isLoggedIn = isset($_SESSION['user']);
$userName = $isLoggedIn ? $_SESSION['user'] : "";
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Gestion de Garage - Tout pour votre voiture" />
        <meta name="author" content="Equipe 6" />
        <title>Entre Autout - Accueil</title>
        <link rel="icon" type="image/x-icon" href="../../assets/front/assets/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    </head>
    <body id="page-top">
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="#page-top">Entre AuTout</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
<div class="collapse navbar-collapse" id="navbarResponsive">
    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
        <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
        <li class="nav-item"><a class="nav-link" href="#portfolio">Véhicules</a></li>
        <li class="nav-item"><a class="nav-link" href="#team">Équipe</a></li>
        <?php if($isLoggedIn): ?>
            <li class="nav-item"><a class="nav-link btn btn-primary text-white ms-lg-3" href="profile.php">Mon Profil</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
        <?php endif; ?>
    </ul>
</div>
            </div>
        </nav>
       <header class="masthead" style="background-image: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('../../assets/front/img/voiture.jpg') !important; background-size: cover !important; background-position: center !important;">
    <div class="container">
        <div class="masthead-subheading">Bienvenue chez Entre AuTout !</div>
        <div class="masthead-heading text-uppercase">L'innovation au service de votre auto</div>
        <a class="btn btn-primary btn-xl text-uppercase" href="#services">Découvrir</a>
    </div>
</header>
        <section class="page-section" id="services">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase">Nos Modules</h2>
                    <h3 class="section-subheading text-muted">Tout ce dont vous avez besoin pour votre véhicule en un seul clic.</h3>
                </div>
                <div class="row text-center">
                    <div class="col-md-4">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-car fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Lavage & Garage</h4>
                        <p class="text-muted">Trouvez les meilleurs garages de la région et réservez votre créneau pour un lavage complet.</p>
                    </div>
                    <div class="col-md-4">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-shopping-cart fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Vente de Pièces</h4>
                        <p class="text-muted">Achetez vos pièces de rechange certifiées en ligne avec livraison rapide.</p>
                    </div>
                    <div class="col-md-4">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-microchip fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Diagnostic</h4>
                        <p class="text-muted">Analysez l'état de votre véhicule grâce à notre module de diagnostic intelligent.</p>
                    </div>
                </div>
            </div>
        </section>
    <section class="page-section bg-light" id="team">
    <div class="container">
        <div class="text-center">
            <h2 class="section-heading text-uppercase">Notre Équipe Entre AuTout</h2>
            <h3 class="section-subheading text-muted">Les experts derrière votre sécurité routière.</h3>
        </div>
        <div class="row">
            <?php
            $team = [
                ['name' => 'Insaf Ben Ahmed', 'role' => 'Responsable Relation Client'],
                ['name' => 'Asma Ayachi', 'role' => 'Directrice Technique (CTO) '],
                ['name' => 'Mohamed Adel Makni', 'role' => 'Expert Diagnostic Systèmes'],
                ['name' => 'Amen Naghmouchi', 'role' => 'Chef d\'Atelier'],
                ['name' => 'Ela Hmida', 'role' => 'Responsable Marketing Digital'],
                ['name' => 'Rayen Ben Selem', 'role' => 'Ingénieur Maintenance Auto']
            ];
            foreach($team as $member): ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="team-member text-center p-3">
                    <i class="fas fa-user-circle fa-5x mb-3 text-secondary"></i>
                    <h4><?= $member['name'] ?></h4>
                    <p class="text-muted"><?= $member['role'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
        <footer class="footer py-4">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-4 text-lg-start">Copyright &copy; Garage Web 2026</div>
                    <div class="col-lg-4 my-3 my-lg-0">
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                    <div class="col-lg-4 text-lg-end">
                        <a class="link-dark text-decoration-none me-3" href="#!">Privacy Policy</a>
                        <a class="link-dark text-decoration-none" href="#!">Terms of Use</a>
                    </div>
                </div>
            </div>

        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../../assets/front/js/scripts.js"></script>
    </body>
</html>