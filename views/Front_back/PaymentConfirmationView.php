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
    <title>Confirmation de Paiement - Pièces Auto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 100px;
        }
        .confirmation-card {
            text-align: center;
            padding: 60px 20px;
        }
        .success-icon {
            color: #28a745;
            font-size: 5rem;
            margin-bottom: 20px;
        }
        .error-icon {
            color: #dc3545;
            font-size: 5rem;
            margin-bottom: 20px;
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
                <li class="nav-item"><a class="nav-link text-warning" href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php">Produits</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=about">About Us</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=team">Team</a></li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=cart">
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

<section class="page-section" id="confirmation">
    <div class="container">
        <?php if (isset($_SESSION['payment_success']) && $_SESSION['payment_success']): ?>
            <div class="confirmation-card">
                <i class="fas fa-check-circle success-icon"></i>
                <h2 class="text-success mb-3">Paiement Confirmé !</h2>
                <p class="lead text-muted mb-4">
                    <?php echo htmlspecialchars($_SESSION['payment_message']); ?>
                </p>
                <p class="text-muted">
                    Votre commande a été enregistrée dans notre système et le stock a été mis à jour.
                </p>
                <div class="mt-4">
                    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-bag"></i> Continuer vos Achats
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="confirmation-card">
                <i class="fas fa-times-circle error-icon"></i>
                <h2 class="text-danger mb-3">Erreur de Paiement</h2>
                <p class="lead text-muted mb-4">
                    <?php echo htmlspecialchars($_SESSION['payment_message'] ?? 'Une erreur est survenue'); ?>
                </p>
                <div class="mt-4">
                    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=cart" class="btn btn-warning btn-lg">
                        <i class="fas fa-arrow-left"></i> Retour au Panier
                    </a>
                    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php" class="btn btn-secondary btn-lg ms-2">
                        <i class="fas fa-home"></i> Accueil
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Nettoyer les messages de session
unset($_SESSION['payment_message']);
unset($_SESSION['payment_success']);
?>
