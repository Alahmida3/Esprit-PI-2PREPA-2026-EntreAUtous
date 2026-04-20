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
                    <?php
                    // View front: récupère les entretiens via le controller (MVC)
                    require_once '../../config.php';
                    require_once '../../controller/EntretienController.php';
                    require_once '../../Models/Entretien.php';

                    $list = [];
                    if (isset($pdo) && $pdo) {
                        $controller = new EntretienController($pdo);
                        $list = $controller->listEntretiens();
                    }
                    ?>
                <div class="text-center mt-5">
                    <h2 class="section-heading text-uppercase">Mon Carnet d'Entretien</h2>
                    <h3 class="section-subheading text-muted">Historique des interventions sur votre véhicule.</h3>
                </div>
                <div class="row">
                    <?php if (!empty($list)) {
                        foreach ($list as $row) { ?>
                            <div class="col-lg-4 col-sm-6 mb-4">
                                <div class="maintenance-card card shadow-sm">
                                    <div class="icon-header text-center text-primary">
                                        <i class="fas fa-tools fa-4x"></i>
                                    </div>
                                    <div class="card-body p-4 text-center">
                                        <h4 class="fw-bold"><?php echo htmlspecialchars($row['type_intervention']); ?></h4>
                                        <p class="text-muted mb-1"><i class="fas fa-calendar-alt me-2"></i><?php echo htmlspecialchars($row['date_entretien']); ?></p>
                                        <p class="text-muted mb-3"><i class="fas fa-tachometer-alt me-2"></i><?php echo number_format((float)$row['kilometrage'],0,',','.'); ?> km</p>
                                        <hr>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="price-badge"><?php echo htmlspecialchars($row['statut']); ?></span>
                                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 text-uppercase fw-bold btn-details"
                                                data-id="<?php echo htmlspecialchars($row['id_entretien']); ?>"
                                                data-voiture="<?php echo htmlspecialchars($row['id_voiture']); ?>"
                                                data-type="<?php echo htmlspecialchars($row['type_intervention']); ?>"
                                                data-date="<?php echo htmlspecialchars($row['date_entretien']); ?>"
                                                data-km="<?php echo htmlspecialchars($row['kilometrage']); ?>"
                                                data-statut="<?php echo htmlspecialchars($row['statut']); ?>"
                                                data-prochaine="<?php echo htmlspecialchars($row['prochaine_echeance']); ?>"
                                                data-kmprochain="<?php echo htmlspecialchars($row['km_prochain']); ?>"
                                                data-obs="<?php echo htmlspecialchars($row['observations']); ?>"
                                            >Détails</button>
                                            <!-- bouton facture -->
        <a href="liste_factures.php?entretien=<?php echo $row['id_entretien']; ?>" 
           class="btn btn-warning btn-sm rounded-pill px-3">
            <i class="fas fa-file-invoice"></i> Facture
        </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }
                    } else { ?>
                        <div class="col-12"><p class="text-muted">Aucun entretien trouvé.</p></div>
                    <?php } ?>
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

        <!-- Modal Détails Entretien -->
        <div class="modal fade" id="modalDetailsEntretien" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Détails de l'entretien</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <ul class="list-unstyled mb-0">
                            <li><strong>Type :</strong> <span id="md-type"></span></li>
                            <li><strong>Date :</strong> <span id="md-date"></span></li>
                            <li><strong>Kilométrage :</strong> <span id="md-km"></span></li>
                            <li><strong>Statut :</strong> <span id="md-statut"></span></li>
                            <li><strong>Prochaine échéance :</strong> <span id="md-prochaine"></span></li>
                            <li><strong>KM prochain :</strong> <span id="md-kmprochain"></span></li>
                            <li><strong>ID Voiture :</strong> <span id="md-voiture"></span></li>
                            <li><strong>Observations :</strong> <div id="md-obs" class="text-muted"></div></li>
                        </ul>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../../assets/front/js/scripts.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var modalEl = document.getElementById('modalDetailsEntretien');
                var modal = new bootstrap.Modal(modalEl);

                document.querySelectorAll('.btn-details').forEach(function(btn){
                    btn.addEventListener('click', function(e){
                        var dataset = e.currentTarget.dataset;
                        document.getElementById('md-type').textContent = dataset.type || '';
                        document.getElementById('md-date').textContent = dataset.date || '';
                        document.getElementById('md-km').textContent = dataset.km ? Number(dataset.km).toLocaleString('fr-FR') + ' km' : '';
                        document.getElementById('md-statut').textContent = dataset.statut || '';
                        document.getElementById('md-prochaine').textContent = dataset.prochaine || '';
                        document.getElementById('md-kmprochain').textContent = dataset.kmprochain || '';
                        document.getElementById('md-voiture').textContent = dataset.voiture || '';
                        document.getElementById('md-obs').textContent = dataset.obs || '—';
                        modal.show();
                    });
                });
            });
        </script>
    </body>
</html>