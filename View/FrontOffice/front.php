<?php
require_once __DIR__ . '/../../config/connexion.php';
require_once __DIR__ . '/../../Model/MonModule.php';
require_once __DIR__ . '/../../Controller/MonModuleController.php';

$garageServices = [];
try {
    $pdo = getPDOConnection();
    $model = new MonModule($pdo);
    $controller = new MonModuleController($model);
    $garageServices = $controller->getGarageServices();
} catch (PDOException $e) {
    $garageServices = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Gestion de Garage - Entretien et Lavage de Véhicules" />
    <title>Garage Expert - Entretien & Lavage</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
    <link href="assets/css/styles.css" rel="stylesheet" />
</head>
<body id="page-top">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">EntreAuTous</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="fas fa-bars ms-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Véhicules</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">À propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <header class="masthead">
        <div class="container">
            <div class="masthead-subheading">Bienvenue dans votre espace auto !</div>
            <div class="masthead-heading text-uppercase">Entretien, Réparation & Lavage</div>
            <a class="btn btn-primary btn-xl text-uppercase" href="#services">Catalogue Services</a>
        </div>
    </header>

    <section class="page-section" id="services">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Nos Services</h2>
                <h3 class="section-subheading text-muted">Nous prenons soin de votre véhicule de A à Z.</h3>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-tools fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">Réparation Auto</h4>
                    <p class="text-muted">Diagnostic complet, mécanique générale et remplacement de pièces d'origine pour assurer la longévité de votre moteur.</p>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-car-wash fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">Lavage & Esthétique</h4>
                    <p class="text-muted">Nettoyage haute pression, aspiration intérieure et polissage de carrosserie pour un véhicule éclatant comme au premier jour.</p>
                </div>
                <div class="col-md-4">
                    <span class="fa-stack fa-4x">
                        <i class="fas fa-circle fa-stack-2x text-primary"></i>
                        <i class="fas fa-clipboard-check fa-stack-1x fa-inverse"></i>
                    </span>
                    <h4 class="my-3">Suivi d'Entretien</h4>
                    <p class="text-muted">Planification de vos vidanges et contrôles techniques. Consultez votre historique de maintenance en un clic.</p>
                </div>
            </div>
            <div class="text-center mt-5">
                <button class="btn btn-secondary btn-xl text-uppercase" id="catalogueBtn">CATALOGUE</button>
            </div>
        </div>
    </section>

    <section class="page-section bg-light" id="portfolio" style="display: none;">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Spécialités par Véhicules</h2>
                <h3 class="section-subheading text-muted">Une expertise adaptée à chaque type de carrosserie.</h3>
                <button class="btn btn-secondary btn-sm text-uppercase mt-3" id="backToServicesBtn">Retour aux Services</button>
                <button class="btn btn-primary btn-sm text-uppercase mt-3" id="afficherListeBtn">Afficher Liste</button>
            </div>
            <div class="row" id="garage-list-section" style="display:none; margin-top:40px;">
                <div class="col-12">
                    <div class="card p-4 shadow-sm" style="background:#fff; color:#212529; border-radius:16px;">
                        <h3 class="section-heading text-uppercase">Liste des garages et services</h3>
                        <p class="section-subheading text-muted">Chaque garage affiche au moins deux services disponibles.</p>
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID Garage</th>
                                        <th>Nom Garage</th>
                                        <th>Adresse</th>
                                        <th>Heure ouverture</th>
                                        <th>Heure fermeture</th>
                                        <th>Services</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($garageServices)): ?>
                                        <tr>
                                            <td colspan="6">Aucun garage disponible.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($garageServices as $garage): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($garage['id_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($garage['nom_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($garage['adresse'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($garage['heure_ouv'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($garage['heure_fer'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(implode(' / ', $garage['services']), ENT_QUOTES, 'UTF-8'); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-sm-6 mb-4">
                    <div class="portfolio-item">
                        <a class="portfolio-link" data-bs-toggle="modal" href="#portfolioModal1">
                            <div class="portfolio-hover">
                                <div class="portfolio-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/vehicules/citadine.jpg" alt="Petite citadine" />
                        </a>
                        <div class="portfolio-caption">
                            <div class="portfolio-caption-heading">Citadines</div>
                            <div class="portfolio-caption-subheading text-muted">Entretien Rapide</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 mb-4">
                    <div class="portfolio-item">
                        <a class="portfolio-link" data-bs-toggle="modal" href="#portfolioModal2">
                            <div class="portfolio-hover">
                                <div class="portfolio-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/vehicules/suv.jpg" alt="Véhicule SUV" />
                        </a>
                        <div class="portfolio-caption">
                            <div class="portfolio-caption-heading">SUV & Familiales</div>
                            <div class="portfolio-caption-subheading text-muted">Check-up Complet</div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-sm-6 mb-4">
                    <div class="portfolio-item">
                        <a class="portfolio-link" data-bs-toggle="modal" href="#portfolioModal3">
                            <div class="portfolio-hover">
                                <div class="portfolio-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                            </div>
                            <img class="img-fluid" src="assets/img/vehicules/utilitaire.jpg" alt="Camionnette utilitaire" />
                        </a>
                        <div class="portfolio-caption">
                            <div class="portfolio-caption-heading">Professionnels</div>
                            <div class="portfolio-caption-subheading text-muted">Maintenance Flotte</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-lg-start">Copyright &copy; Garage Expert 2024</div>
                <div class="col-lg-4 my-3 my-lg-0">
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a class="link-dark text-decoration-none me-3" href="#!">Politique de confidentialité</a>
                    <a class="link-dark text-decoration-none" href="#!">Conditions d'utilisation</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/scripts.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const catalogueBtn = document.getElementById('catalogueBtn');
            const servicesSection = document.getElementById('services');
            const portfolioSection = document.getElementById('portfolio');

            catalogueBtn.addEventListener('click', function(event) {
                event.preventDefault();

                servicesSection.style.display = 'none';
                portfolioSection.style.display = 'block';
                catalogueBtn.style.display = 'none';
            });

            const backToServicesBtn = document.getElementById('backToServicesBtn');
            const afficherListeBtn = document.getElementById('afficherListeBtn');
            const garageListSection = document.getElementById('garage-list-section');

            backToServicesBtn.addEventListener('click', function(event) {
                event.preventDefault();

                servicesSection.style.display = 'block';
                portfolioSection.style.display = 'none';
                garageListSection.style.display = 'none';
                catalogueBtn.style.display = 'inline-block';
                servicesSection.scrollIntoView({ behavior: 'smooth' });
            });

            afficherListeBtn.addEventListener('click', function(event) {
                event.preventDefault();

                servicesSection.style.display = 'none';
                portfolioSection.style.display = 'block';
                garageListSection.style.display = 'block';
                catalogueBtn.style.display = 'none';
                garageListSection.scrollIntoView({ behavior: 'smooth' });
            });
        });
    </script>
</body>
</html>