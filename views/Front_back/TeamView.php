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
    <title>Notre Équipe - Pièces Auto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 100px;
        }
        .team-section {
            padding: 60px 0;
        }
        .team-member {
            text-align: center;
            margin-bottom: 40px;
        }
        .team-member img {
            width: 200px;
            height: 200px;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .btn-social {
            display: inline-block;
            width: 45px;
            height: 45px;
            border-radius: 100%;
            text-align: center;
            line-height: 45px;
            background-color: #6c757d;
            color: white;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        .btn-social:hover {
            background-color: #667eea;
            transform: scale(1.1);
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
                <li class="nav-item"><a class="nav-link text-white" href="index.php">Produits</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="index.php?action=about">About Us</a></li>
                <li class="nav-item"><a class="nav-link text-white" href="index.php?action=team">Team</a></li>
                <li class="nav-item">
                    <a class="nav-link text-white" href="index.php?action=cart">
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

<section class="page-section bg-light team-section" id="team">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-heading text-uppercase">Notre Équipe Exceptionnelle</h2>
            <h3 class="section-subheading text-muted">Les experts qui font la différence</h3>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="team-member">
                    <img class="rounded-circle" src="assets/img/team/1.jpg" alt="Directeur Général" />
                    <h4>Parveen Anand</h4>
                    <p class="text-muted">Directeur Général</p>
                    <a class="btn-social" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a class="btn-social" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn-social" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="team-member">
                    <img class="rounded-circle" src="assets/img/team/2.jpg" alt="Responsable Ventes" />
                    <h4>Diana Petersen</h4>
                    <p class="text-muted">Responsable Ventes et Marketing</p>
                    <a class="btn-social" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a class="btn-social" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn-social" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="team-member">
                    <img class="rounded-circle" src="assets/img/team/3.jpg" alt="Responsable Technique" />
                    <h4>Larry Parker</h4>
                    <p class="text-muted">Responsable Technique et IT</p>
                    <a class="btn-social" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a class="btn-social" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn-social" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
        <div class="row mt-5">
            <div class="col-lg-8 mx-auto text-center">
                <p class="large text-muted">Notre équipe est composée de professionnels passionnés par l'automobile et déterminés à fournir le meilleur service pour que chaque véhicule continue à rouler en parfait état.</p>
            </div>
        </div>
    </div>
</section>

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
            <a href="index.php?action=about" class="btn btn-secondary btn-lg">
                <i class="fas fa-info-circle"></i> À Propos
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
