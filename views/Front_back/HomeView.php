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
    <title>Accueil - Pièces Auto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .hero-section {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            padding: 40px 20px;
        }
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .hero-section p {
            font-size: 1.3rem;
            margin-bottom: 30px;
        }
        .btn-large {
            padding: 15px 40px;
            font-size: 1.1rem;
            margin: 10px;
            border-radius: 50px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn-large:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .navbar {
            background: rgba(0,0,0,0.3) !important;
            backdrop-filter: blur(10px);
        }
        .navbar-brand {
            font-size: 1.5rem;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand text-white" href="index.php">
                <i class="fas fa-cogs"></i> Auto Parts Pro
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php">Accueil</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php?action=services">Services</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php?action=about">À Propos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php?action=team">Équipe</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="index.php?action=cart">
                            <i class="fas fa-shopping-cart"></i> Panier
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link badge bg-danger" href="index.php?action=admin">
                            <i class="fas fa-lock"></i> Admin
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div>
            <h1><i class="fas fa-car"></i> Auto Parts Pro</h1>
            <p>Votre magasin de pièces automobiles de confiance</p>
            <div>
                <a href="PieceView.php" class="btn btn-light btn-large">
                    <i class="fas fa-shopping-bag"></i> Voir les Pièces
                </a>
                <a href="index.php?action=services" class="btn btn-outline-light btn-large">
                    <i class="fas fa-briefcase"></i> Nos Services
                </a>
            </div>
            <hr class="bg-white border-2 my-4" style="max-width: 100px; margin-left: auto; margin-right: auto;">
            <p class="small">
                <a href="index.php?action=admin" class="text-white text-decoration-none">
                    <i class="fas fa-cog"></i> Accès Administrateur
                </a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
