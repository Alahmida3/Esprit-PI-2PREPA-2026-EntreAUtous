<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../controller/Vehicule.php');
require_once(__DIR__ . '/../../controller/rendezvous.php');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include_once __DIR__ . '/../../partials/head/head-meta.html'; ?>
    <title>Ajouter un Véhicule - Smart Supply</title>
    <?php include_once __DIR__ . '/../../partials/head/head-links.html'; ?>
    <link href="/ProjetWeb/assets/Front office/css/styles.css" rel="stylesheet" />
    
</head>
<body id="page-top">

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">
                <img src="assets/img/navbar-logo.svg" alt="Logo" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                Menu <i class="fas fa-bars ms-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="FrontOfficeVehicule.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="GestionVehicule.php">Véhicule</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Message flottant -->
    
    <!-- Masthead -->
    <header class="masthead">
        <div class="container text-center">
            <div class="masthead-subheading">Bienvenue chez EntreAutous</div>
            <div class="masthead-heading text-uppercase">Ajoutez votre véhicule</div>
        </div>
    </header>

    <!-- Formulaire d'ajout -->
    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/Front office/js/scripts.js"></script>
    <script>
        // Remplir automatiquement le hidden nomClient
        document.getElementById('clientSelect').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            document.getElementById('nomClientHidden').value = selected.getAttribute('data-nom') || '';
        });

        // Auto-fermer l'alerte après 4s
        setTimeout(function () {
            const alerts = document.querySelectorAll('.alert-fixed');
            alerts.forEach(a => a.classList.remove('show'));
        }, 4000);
    </script>
</body>
</html>