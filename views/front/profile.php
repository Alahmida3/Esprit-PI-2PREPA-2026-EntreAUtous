<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

$prenom = $_SESSION['prenom'] ?? "Client";
$email = $_SESSION['email'] ?? "";
$role = $_SESSION['role'] ?? "client";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mon Profil - EntreAuTout</title>

    <!-- Agency Template CSS -->
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />

    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js"></script>
</head>

<body id="page-top">

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">

        <a class="navbar-brand" href="home.php">
            EntreAuTout
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
            Menu <i class="fas fa-bars ms-1"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">

                <li class="nav-item"><a class="nav-link" href="home.php#services">Services</a></li>
                <li class="nav-item"><a class="nav-link" href="home.php#portfolio">Modules</a></li>
                <li class="nav-item"><a class="nav-link" href="home.php#contact">Contact</a></li>

                <li class="nav-item">
                    <a class="nav-link text-danger" href="../../controller/logout.php">Déconnexion</a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<!-- HEADER -->
<header class="masthead">
    <div class="container">

        <div class="masthead-subheading">
            Mon Profil 👤
        </div>

        <div class="masthead-heading text-uppercase">
            <?= htmlspecialchars($prenom) ?>
        </div>

        <p class="text-light">
            Espace personnel du client
        </p>

    </div>
</header>

<!-- PROFILE SECTION -->
<section class="page-section">
    <div class="container">

        <div class="text-center mb-5">
            <h2 class="section-heading text-uppercase">Informations personnelles</h2>
        </div>

        <div class="row justify-content-center">

            <div class="col-md-6">

                <div class="card shadow-lg border-0">
                    <div class="card-body text-center">

                        <i class="fas fa-user-circle fa-5x mb-3"></i>

                        <h4><?= htmlspecialchars($prenom) ?></h4>
                        <p class="text-muted"><?= htmlspecialchars($email) ?></p>

                        <hr>

                        <p><strong>Rôle :</strong> <?= htmlspecialchars($role) ?></p>

                        <a href="edit_profile.php" class="btn btn-dark mt-3">
                            Modifier Profil
                        </a>

                    </div>
                </div>

            </div>

        </div>

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