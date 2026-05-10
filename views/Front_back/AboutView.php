<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>À Propos - Pièces Auto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 100px;
        }
        .about-section {
            padding: 60px 0;
        }
        .timeline {
            position: relative;
            padding: 0;
        }
        .timeline li {
            margin-bottom: 50px;
            position: relative;
        }
        .timeline-image {
            position: absolute;
            z-index: 100;
            width: 120px;
            height: 120px;
            left: 0;
            top: 0;
        }
        .timeline-image img {
            width: 100%;
            height: 100%;
            border-radius: 100%;
            object-fit: cover;
        }
        .timeline-panel {
            position: relative;
            width: 100%;
            padding: 0 0 0 180px;
        }
        .timeline-panel .timeline-heading {
            position: relative;
            top: 20px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav" style="background: #000000;">
    <div class="container">
        <a class="navbar-brand text-warning" href="index.php">
            <i class="fas fa-cogs"></i> Pièces Auto
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            Menu
            <i class="fas fa-bars ms-1"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                <li class="nav-item"><a class="nav-link text-warning" href="index.php">Produits</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="index.php?action=about">About Us</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="index.php?action=team">Team</a></li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="index.php?action=cart">
                        <i class="fas fa-shopping-cart"></i> Panier
                        <?php 
                            $_SESSION['cart'] = $_SESSION['cart'] ?? [];
                            if (count($_SESSION['cart']) > 0):
                        ?>
                            <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<section class="page-section about-section" id="about">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-heading text-uppercase">À Propos de Nous</h2>
            <h3 class="section-subheading text-muted">Découvrez notre histoire et nos valeurs</h3>
        </div>
        <ul class="timeline">
            <li>
                <div class="timeline-image">
                    <img class="rounded-circle img-fluid" src="assets/img/about/1.jpg" alt="2009-2011" />
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4>2009-2011</h4>
                        <h4 class="subheading">Nos Débuts Humbles</h4>
                    </div>
                    <div class="timeline-body">
                        <p class="text-muted">En 2009, nous avons lancé notre entreprise avec une simple vision : fournir des pièces automobiles de qualité à des prix concurrentiels. Nos premières années ont été marquées par une croissance modeste mais constante.</p>
                    </div>
                </div>
            </li>
            <li class="timeline-inverted">
                <div class="timeline-image">
                    <img class="rounded-circle img-fluid" src="assets/img/about/2.jpg" alt="2011-2015" />
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4>Mars 2011</h4>
                        <h4 class="subheading">Une Agence Est Née</h4>
                    </div>
                    <div class="timeline-body">
                        <p class="text-muted">Nous nous sommes transformés en une véritable entreprise, établissant nos locaux et développant notre réseau de fournisseurs. Notre engagement envers la qualité s'est consolidé.</p>
                    </div>
                </div>
            </li>
            <li>
                <div class="timeline-image">
                    <img class="rounded-circle img-fluid" src="assets/img/about/3.jpg" alt="2015-2020" />
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4>Décembre 2015</h4>
                        <h4 class="subheading">Transition vers les Services Complets</h4>
                    </div>
                    <div class="timeline-body">
                        <p class="text-muted">Nous avons étendu nos services pour inclure la consultation technique et l'assistance client 24/7. Notre expertise s'est élargit à différents types de véhicules.</p>
                    </div>
                </div>
            </li>
            <li class="timeline-inverted">
                <div class="timeline-image">
                    <img class="rounded-circle img-fluid" src="assets/img/about/4.jpg" alt="2020-2026" />
                </div>
                <div class="timeline-panel">
                    <div class="timeline-heading">
                        <h4>Juillet 2020</h4>
                        <h4 class="subheading">Expansion Numérique</h4>
                    </div>
                    <div class="timeline-body">
                        <p class="text-muted">Nous avons lancé notre plateforme e-commerce pour offrir une expérience d'achat en ligne fluide et sécurisée. Aujourd'hui, nous servons des milliers de clients satisfaits.</p>
                    </div>
                </div>
            </li>
            <li class="timeline-inverted">
                <div class="timeline-image">
                    <h4 class="text-center">
                        Faites Partie<br />
                        de Notre<br />
                        Histoire!
                    </h4>
                </div>
            </li>
        </ul>
    </div>
</section>

<div class="py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="text-center mb-5">
            <h3 class="section-heading text-uppercase">Nos Valeurs</h3>
        </div>
        <div class="row">
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-award fa-3x text-primary mb-3"></i>
                <h5>Qualité</h5>
                <p class="text-muted">Nous ne proposons que des pièces certifiées et testées pour garantir votre satisfaction.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-handshake fa-3x text-primary mb-3"></i>
                <h5>Fiabilité</h5>
                <p class="text-muted">Nos clients peuvent compter sur nous pour des livraisons rapides et un service irréprochable.</p>
            </div>
            <div class="col-md-4 text-center mb-4">
                <i class="fas fa-heart fa-3x text-primary mb-3"></i>
                <h5>Passion</h5>
                <p class="text-muted">Nous aimons ce que nous faisons et cela se voit dans notre engagement envers vous.</p>
            </div>
        </div>
    </div>
</div>

<div class="text-center py-5" style="background-color: #f8f9fa;">
    <div class="container">
        <div class="mb-3">
            <h4 class="mb-4">Naviguez vers</h4>
        </div>
        <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
            <a href="index.php" class="btn btn-primary btn-lg">
                <i class="fas fa-home"></i> Accueil
            </a>
            <a href="index.php?action=services" class="btn btn-info btn-lg">
                <i class="fas fa-briefcase"></i> Services
            </a>
            <a href="index.php?action=team" class="btn btn-success btn-lg">
                <i class="fas fa-users"></i> Équipe
            </a>
            <a href="index.php?action=cart" class="btn btn-warning btn-lg">
                <i class="fas fa-shopping-cart"></i> Panier
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
