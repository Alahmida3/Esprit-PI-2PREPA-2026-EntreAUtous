<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="Carnet d'entretien digital" />
        <title>entreAUtous - Mon Historique</title>
        <link rel="icon" type="image/x-icon" href="../../assets/front/assets/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <link href="../../assets/front/css/styles.css" rel="stylesheet" />
        
        <style>
            /* Styles personnalisés pour l'affichage en cartes */
            .maintenance-card {
                border: none;
                border-radius: 15px;
                transition: transform 0.3s;
                overflow: hidden;
                background-color: #fff;
            }
            .maintenance-card:hover {
                transform: translateY(-5px);
            }
            .icon-header {
                padding: 30px;
                background-color: #f8f9fa;
                border-bottom: 4px solid #ffc800; /* Jaune caractéristique de la template Agency */
            }
            .price-badge {
                font-size: 1.1rem;
                font-weight: bold;
                color: #212529;
            }
            #mainNav {
                background-color: #212529 !important; /* Force la navbar en sombre */
            }
        </style>
    </head>
    <body id="page-top">
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="#page-top">entreAUtous</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu
                    <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link active text-primary" href="historique_entretien.php">Entretien</a></li>
                        <li class="nav-item"><a class="nav-link" href="liste_factures.php">Factures</a></li>
                        <li class="nav-item"><a class="nav-link text-warning" href="#">Aide</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="#">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <section class="page-section bg-light" id="portfolio">
            <div class="container">
                <div class="text-center mt-5">
                    <h2 class="section-heading text-uppercase">Mon Carnet d'Entretien</h2>
                    <h3 class="section-subheading text-muted">Historique des interventions sur votre véhicule.</h3>
                </div>
                <div class="row">
                    
                    <div class="col-lg-4 col-sm-6 mb-4">
                        <div class="maintenance-card card shadow-sm">
                            <div class="icon-header text-center text-primary">
                                <i class="fas fa-oil-can fa-4x"></i>
                            </div>
                            <div class="card-body p-4 text-center">
                                <h4 class="fw-bold">Vidange Moteur</h4>
                                <p class="text-muted mb-1"><i class="fas fa-calendar-alt me-2"></i>15 Mars 2026</p>
                                <p class="text-muted mb-3"><i class="fas fa-tachometer-alt me-2"></i>95,100 km</p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-badge">120.000 TND</span>
                                    <a href="#" class="btn btn-primary btn-sm rounded-pill px-3 text-uppercase fw-bold">Détails</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <div class="maintenance-card card shadow-sm">
                            <div class="icon-header text-center text-success">
                                <i class="fas fa-tools fa-4x"></i>
                            </div>
                            <div class="card-body p-4 text-center">
                                <h4 class="fw-bold">Freins & Disques</h4>
                                <p class="text-muted mb-1"><i class="fas fa-calendar-alt me-2"></i>02 Février 2026</p>
                                <p class="text-muted mb-3"><i class="fas fa-tachometer-alt me-2"></i>92,000 km</p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-badge">315.000 TND</span>
                                    <a href="#" class="btn btn-primary btn-sm rounded-pill px-3 text-uppercase fw-bold">Détails</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-sm-6 mb-4">
                        <div class="maintenance-card card shadow-sm">
                            <div class="icon-header text-center text-warning">
                                <i class="fas fa-car-side fa-4x"></i>
                            </div>
                            <div class="card-body p-4 text-center">
                                <h4 class="fw-bold">Révision Générale</h4>
                                <p class="text-muted mb-1"><i class="fas fa-calendar-alt me-2"></i>10 Janvier 2026</p>
                                <p class="text-muted mb-3"><i class="fas fa-tachometer-alt me-2"></i>90,500 km</p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-badge">450.000 TND</span>
                                    <a href="#" class="btn btn-primary btn-sm rounded-pill px-3 text-uppercase fw-bold">Détails</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <footer class="footer py-4 bg-white">
            <div class="container">
                <div class="row align-items-center text-center">
                    <div class="col-lg-12 text-muted">Copyright &copy; entreAUtous 2026</div>
                </div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../../assets/front/js/scripts.js"></script>
    </body>
</html>