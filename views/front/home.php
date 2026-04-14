<?php
session_start();

// sécurité simple
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$prenom = $_SESSION['prenom'] ?? "Client";
$email = $_SESSION['email'] ?? "";
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>EntreAuTout - Home</title>

    <!-- TEMPLATE FRONT -->
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js"></script>
</head>

<body id="page-top">

<!-- NAVBAR (comme Agency) -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">

        <a class="navbar-brand" href="#page-top">
            <img src="../../assets/back/images/logo.png" height="40">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
            Menu <i class="fas fa-bars ms-1"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">

                <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="#portfolio">Garage</a></li>
                <li class="nav-item"><a class="nav-link" href="#about">Diagnostic</a></li>
                <li class="nav-item"><a class="nav-link" href="#team">Messagerie</a></li>
                <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="../../controller/logout.php">Déconnexion</a>
                </li>
                <li class="nav-item">
                     <a class="nav-link" href="profile.php">Mon Profil</a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- MASTHEAD (WELCOME AREA) -->
<header class="masthead">
    <div class="container">

        <div class="masthead-subheading">
            Bienvenue <?= htmlspecialchars($prenom) ?> 👋
        </div>

        <div class="masthead-heading text-uppercase">
            EntreAuTout - Votre voiture, notre priorité
        </div>

        <a class="btn btn-primary btn-xl text-uppercase" href="#services">
            Explorer mes services
        </a>

    </div>
</header>

<!-- SERVICES -->
<section class="page-section" id="services">
    <div class="container text-center">
        <h2 class="section-heading text-uppercase">Mes Services</h2>

        <div class="row text-center mt-4">

            <div class="col-md-4">
                <i class="fas fa-car fa-3x"></i>
                <h4>Véhicule</h4>
                <p>Gestion de vos véhicules</p>
            </div>

            <div class="col-md-4">
                <i class="fas fa-wrench fa-3x"></i>
                <h4>Garage</h4>
                <p>Suivi des réparations</p>
            </div>

            <div class="col-md-4">
                <i class="fas fa-bolt fa-3x"></i>
                <h4>Diagnostic</h4>
                <p>Analyse intelligente</p>
            </div>

        </div>
    </div>
</section>

<!-- PORTFOLIO (modules cliquables MAIS sans action) -->
<section class="page-section bg-light" id="portfolio">
    <div class="container text-center">
        <h2 class="section-heading text-uppercase">Modules</h2>

        <div class="row mt-4">

            <div class="col-md-4">
                <a href="#" class="btn btn-dark w-100 mb-3">Pièces Auto</a>
            </div>

            <div class="col-md-4">
                <a href="#" class="btn btn-dark w-100 mb-3">Rendez-vous</a>
            </div>

            <div class="col-md-4">
                <a href="#" class="btn btn-dark w-100 mb-3">Historique</a>
            </div>

        </div>
    </div>
</section>

<!-- ABOUT -->
<section class="page-section" id="about">
    <div class="container text-center">
        <h2 class="section-heading text-uppercase">À propos</h2>
        <p class="text-muted">
            EntreAuTout vous permet de gérer votre voiture facilement avec un espace client intelligent.
        </p>
    </div>
</section>

<!-- TEAM -->
<section class="page-section bg-light" id="team">
    <div class="container text-center">
        <h2 class="section-heading text-uppercase">Support</h2>
        <p>Messagerie avec l’équipe technique bientôt disponible.</p>
    </div>
</section>

<!-- CONTACT -->
<section class="page-section" id="contact">
    <div class="container text-center">
        <h2>Contact</h2>
        <p>Email: support@entreautout.com</p>
    </div>
</section>

<!-- FOOTER -->
<footer class="footer py-4">
    <div class="container text-center">
        <small>© EntreAuTout 2026</small>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../assets/front/js/scripts.js"></script>

</body>
</html>