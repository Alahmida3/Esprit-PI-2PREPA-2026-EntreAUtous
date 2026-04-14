<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>entreAUtous - Mes Factures</title>
        <link rel="icon" type="image/x-icon" href="../../assets/front/assets/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="../../assets/front/css/styles.css" rel="stylesheet" />
        
        <style>
            /* Design Liste de Documents */
            .invoice-item {
                background: #fff;
                border-radius: 10px;
                transition: all 0.2s ease-in-out;
                border-left: 5px solid #ffc800; /* Rappel du jaune Agency */
            }
            .invoice-item:hover {
                background: #fdfdfd;
                box-shadow: 0 5px 15px rgba(0,0,0,0.08) !important;
                transform: scale(1.01);
            }
            .pdf-icon {
                color: #dc3545; /* Rouge pour le côté PDF */
                font-size: 2rem;
            }
            #mainNav {
                background-color: #212529 !important;
            }
            .btn-download {
                border-radius: 50px;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 0.8rem;
            }
        </style>
    </head>
    <body id="page-top" class="bg-light">
        <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
            <div class="container">
                <a class="navbar-brand" href="#page-top">entreAUtous</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                    Menu <i class="fas fa-bars ms-1"></i>
                </button>
                <div class="collapse navbar-collapse" id="navbarResponsive">
                    <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                        <li class="nav-item"><a class="nav-link" href="historique_entretien.php">Entretien</a></li>
                        <li class="nav-item"><a class="nav-link active text-primary" href="liste_factures.php">Factures</a></li>
                        <li class="nav-item"><a class="nav-link text-warning" href="#">Aide</a></li>
                        <li class="nav-item"><a class="nav-link text-danger" href="#">Déconnexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <section class="page-section">
            <div class="container">
                <div class="text-center mt-5 mb-5">
                    <h2 class="section-heading text-uppercase">Mes Documents</h2>
                    <h3 class="section-subheading text-muted">Consultez et téléchargez vos factures au format PDF.</h3>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        
                        <div class="invoice-item card shadow-sm mb-3 p-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-file-pdf pdf-icon"></i>
                                </div>
                                <div class="col">
                                    <h5 class="mb-0 fw-bold">Facture #FAC-2026-001</h5>
                                    <small class="text-muted">Intervention : Vidange Moteur | Date : 15 Mars 2026</small>
                                </div>
                                <div class="col-auto text-end">
                                    <div class="fw-bold fs-5 me-3">120.000 TND</div>
                                </div>
                                <div class="col-auto">
                                    <a href="#" class="btn btn-primary btn-download px-4">
                                        <i class="fas fa-download me-2"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="invoice-item card shadow-sm mb-3 p-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-file-pdf pdf-icon"></i>
                                </div>
                                <div class="col">
                                    <h5 class="mb-0 fw-bold">Facture #FAC-2026-002</h5>
                                    <small class="text-muted">Intervention : Freins & Disques | Date : 02 Février 2026</small>
                                </div>
                                <div class="col-auto text-end">
                                    <div class="fw-bold fs-5 me-3">315.000 TND</div>
                                </div>
                                <div class="col-auto">
                                    <a href="#" class="btn btn-primary btn-download px-4">
                                        <i class="fas fa-download me-2"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="invoice-item card shadow-sm mb-3 p-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <i class="fas fa-file-pdf pdf-icon"></i>
                                </div>
                                <div class="col">
                                    <h5 class="mb-0 fw-bold">Facture #FAC-2026-003</h5>
                                    <small class="text-muted">Intervention : Révision Générale | Date : 10 Janvier 2026</small>
                                </div>
                                <div class="col-auto text-end">
                                    <div class="fw-bold fs-5 me-3">450.000 TND</div>
                                </div>
                                <div class="col-auto">
                                    <a href="#" class="btn btn-primary btn-download px-4">
                                        <i class="fas fa-download me-2"></i> Télécharger
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <footer class="footer py-4 bg-white">
            <div class="container text-center">
                <div class="text-muted small">Copyright &copy; entreAUtous 2026</div>
            </div>
        </footer>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../../assets/front/js/scripts.js"></script>
    </body>
</html>