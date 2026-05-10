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
    <title>Services - Pièces Auto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 100px;
        }
        .services-section {
            padding: 60px 0;
        }
        .service-card {
            text-align: center;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .service-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }
        .service-icon {
            font-size: 3rem;
            color: #007bff;
            margin-bottom: 15px;
        }
        .service-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        .service-description {
            color: #666;
            line-height: 1.6;
        }
        .navigation-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 40px;
            flex-wrap: wrap;
        }
        .navigation-buttons a {
            padding: 10px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary-nav {
            background-color: #007bff;
            color: white;
        }
        .btn-primary-nav:hover {
            background-color: #0056b3;
            color: white;
        }
        .btn-secondary-nav {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary-nav:hover {
            background-color: #5a6268;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">🚗 Auto Parts</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php?action=services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=about">À Propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=team">Équipe</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="index.php?action=cart">
                            <i class="fas fa-shopping-cart"></i> Panier
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="bg-light py-5">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Nos Services</h1>
            <p class="lead text-muted">Découvrez tous les services que nous offrons pour vos pièces automobiles</p>
        </div>
    </section>

    <!-- Services Section -->
    <section class="services-section">
        <div class="container">
            <div class="row">
                <!-- Service 1 -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-truck-fast"></i>
                        </div>
                        <div class="service-title">Livraison Rapide</div>
                        <div class="service-description">
                            Livraison en 24-48h pour toutes vos commandes
                        </div>
                    </div>
                </div>

                <!-- Service 2 -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-headset"></i>
                        </div>
                        <div class="service-title">Support Client 24/7</div>
                        <div class="service-description">
                            Notre équipe est disponible 24h/24 pour répondre à vos questions
                        </div>
                    </div>
                </div>

                <!-- Service 3 -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div class="service-title">Garantie Produit</div>
                        <div class="service-description">
                            Tous nos produits sont garantis authentiques et de qualité supérieure
                        </div>
                    </div>
                </div>

                <!-- Service 4 -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div class="service-title">Installation Expert</div>
                        <div class="service-description">
                            Service d'installation professionnel disponible dans nos ateliers
                        </div>
                    </div>
                </div>

                <!-- Service 5 -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <div class="service-title">Recherche de Pièces</div>
                        <div class="service-description">
                            Aide pour trouvez les pièces compatibles avec votre véhicule
                        </div>
                    </div>
                </div>

                <!-- Service 6 -->
                <div class="col-md-6 col-lg-4">
                    <div class="service-card">
                        <div class="service-icon">
                            <i class="fas fa-recycle"></i>
                        </div>
                        <div class="service-title">Échange & Retour</div>
                        <div class="service-description">
                            Politique de retour simple et remboursement garanti dans les 30 jours
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Navigation Buttons -->
    <section class="bg-light py-5">
        <div class="container">
            <div class="navigation-buttons">
                <a href="index.php?action=about" class="btn-secondary-nav">← À Propos</a>
                <a href="index.php" class="btn-primary-nav">🏠 Accueil</a>
                <a href="index.php?action=team" class="btn-secondary-nav">Équipe →</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2026 Auto Parts. Tous droits réservés.</p>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
