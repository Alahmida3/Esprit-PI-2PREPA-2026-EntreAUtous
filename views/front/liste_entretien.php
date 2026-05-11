<?php
/* VUE UNIQUEMENT — aucun SQL, aucune logique métier */
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../Controller/EntretienController.php';
$clientId = $_SESSION['user_id'] ?? 0;
$ctrl = new EntretienController($pdo);
$data = $ctrl->getListeFront((int)($_GET['page'] ?? 1), 6, $clientId);

// Variables prêtes à l'affichage
$list          = $data['list'];
$facturesCarte = $data['facturesCarte'];
$page          = $data['page'];
$totalPages    = $data['totalPages'];
$total         = $data['total'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>entreAUtous - Mon Historique</title>
    <link rel="icon" type="image/x-icon" href="../../assets/front/assets/favicon.ico" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" />
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <style>
        .maintenance-card { border:none; border-radius:15px; transition:transform 0.3s; overflow:hidden; background:#fff; }
        .maintenance-card:hover { transform:translateY(-5px); }
        .icon-header { padding:30px; background-color:#f8f9fa; border-bottom:4px solid #ffc800; }
        .price-badge { font-size:1.1rem; font-weight:bold; color:#212529; }
        #mainNav { background-color:#212529 !important; }
        .matricule-tag { font-size:0.78rem; background:#ffc800; color:#212529; border-radius:20px; padding:2px 10px; font-weight:700; display:inline-block; margin-bottom:6px; }
    </style>
</head>
<body id="page-top">

<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
    <div class="container">
        <a class="navbar-brand" href="#page-top">entreAUtous</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
            Menu <i class="fas fa-bars ms-1"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                <li class="nav-item"><a class="nav-link" href="home.php"><i class="fas fa-home me-1"></i> Accueil</a></li>
                <li class="nav-item"><a class="nav-link active text-primary" href="liste_entretien.php">Entretien</a></li>
                <li class="nav-item"><a class="nav-link" href="liste_factures.php">Factures</a></li>
            </ul>
        </div>
    </div>
</nav>

<section class="page-section bg-light" id="portfolio">
    <div class="container">

        <?php if (isset($_GET['paiement_ok'])): ?>
        <div class="alert alert-success text-center mx-auto mt-4" style="max-width:500px;border-radius:12px;">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Paiement confirmé !</strong> Votre entretien a été marqué comme payé avec succès.
        </div>
        <?php elseif (isset($_GET['paiement_err'])): ?>
        <div class="alert alert-danger text-center mx-auto mt-4" style="max-width:500px;border-radius:12px;">
            <i class="fas fa-exclamation-circle me-2"></i>
            Cet entretien n'est pas disponible pour le paiement en ligne.
        </div>
        <?php endif; ?>

        <div class="text-center mt-5">
            <h2 class="section-heading text-uppercase">Mon Carnet d'Entretien</h2>
            <h3 class="section-subheading text-muted">Historique des interventions sur votre véhicule.</h3>
        </div>

        <div class="row" id="cards-container">
            <?php if (!empty($list)): ?>
                <?php foreach ($list as $row): ?>
                    <?php
                    $statutLabel = ['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé','paye'=>'✓ Payé'];
                    $sColor      = ['planifie'=>'#ffc800','en_cours'=>'#0dcaf0','termine'=>'#6c757d','annule'=>'#dc3545','paye'=>'#198754'];
                    $sl      = $row['statut'];
                    $slLabel = $statutLabel[$sl] ?? ucfirst($sl);
                    $slColor = $sColor[$sl]      ?? '#aaa';
                    ?>
                    <div class="col-lg-4 col-sm-6 mb-4">
                        <div class="maintenance-card card shadow-sm">
                            <div class="icon-header text-center text-primary">
                                <i class="fas fa-tools fa-4x"></i>
                                <div class="matricule-tag mt-3">
                                    <i class="fas fa-car me-1"></i><?= htmlspecialchars($row['Matricule']) ?>
                                </div>
                            </div>
                            <div class="card-body p-4 text-center">
                                <h4 class="fw-bold"><?= htmlspecialchars($row['type_intervention'] ?? '—') ?></h4>
                                <p class="text-muted mb-1"><i class="fas fa-calendar-alt me-2"></i><?= htmlspecialchars($row['date_entretien']) ?></p>
                                <p class="text-muted mb-3"><i class="fas fa-tachometer-alt me-2"></i><?= number_format((float)$row['kilometrage'], 0, ',', '.') ?> km</p>
                                <hr>
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                    <span class="price-badge" style="color:<?= $slColor ?>;font-weight:700;"><?= $slLabel ?></span>

                                    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 text-uppercase fw-bold btn-details"
                                        data-id="<?= $row['id_entretien'] ?>"
                                        data-matricule="<?= htmlspecialchars($row['Matricule']) ?>"
                                        data-type="<?= htmlspecialchars($row['type_intervention'] ?? '') ?>"
                                        data-date="<?= htmlspecialchars($row['date_entretien']) ?>"
                                        data-km="<?= htmlspecialchars($row['kilometrage']) ?>"
                                        data-statut="<?= htmlspecialchars($row['statut']) ?>"
                                        data-prochaine="<?= htmlspecialchars($row['prochaine_echeance'] ?? '') ?>"
                                        data-kmprochain="<?= htmlspecialchars($row['km_prochain'] ?? '') ?>"
                                        data-obs="<?= htmlspecialchars($row['observations'] ?? '') ?>">
                                        Détails
                                    </button>

                                    <a href="liste_factures.php?entretien=<?= $row['id_entretien'] ?>"
                                        class="btn btn-warning btn-sm rounded-pill px-3">
                                        <i class="fas fa-file-invoice"></i> Facture
                                    </a>

                                    <?php if ($row['statut'] === 'termine' && !empty($facturesCarte[$row['id_entretien']])): ?>
                                    <a href="banque_secure.php?id=<?= $row['id_entretien'] ?>"
                                        class="btn btn-sm rounded-pill px-3 fw-bold"
                                        style="background:linear-gradient(135deg,#198754,#20c997);color:#fff;border:none;">
                                        <i class="fas fa-lock me-1"></i> Payer en ligne
                                    </a>
                                    <?php endif; ?>

                                    <?php if ($row['statut'] === 'paye'): ?>
                                    <span class="btn btn-sm rounded-pill px-3 fw-bold"
                                        style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;cursor:default;">
                                        <i class="fas fa-check-circle me-1"></i> Payé
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><p class="text-muted text-center mt-5">Aucun entretien trouvé.</p></div>
            <?php endif; ?>
        </div>

        <!-- Pagination serveur -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-5 mb-3">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="btn btn-outline-secondary btn-sm px-4">&#8249; Précédent</a>
            <?php else: ?>
                <button class="btn btn-outline-secondary btn-sm px-4" disabled>&#8249; Précédent</button>
            <?php endif; ?>

            <span style="font-size:.9rem;color:#6c757d;min-width:120px;text-align:center;">
                Page <?= $page ?> / <?= $totalPages ?>
            </span>

            <?php if ($page < $totalPages): ?>
                <a href="?page=<?= $page + 1 ?>" class="btn btn-primary btn-sm px-4">Suivant &#8250;</a>
            <?php else: ?>
                <button class="btn btn-primary btn-sm px-4" disabled>Suivant &#8250;</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<footer class="footer py-4 bg-white">
    <div class="container text-center">
        <div class="text-muted">Copyright &copy; entreAUtous 2026</div>
    </div>
</footer>

<!-- Modal Détails -->
<div class="modal fade" id="modalDetailsEntretien" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'entretien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="list-unstyled mb-0">
                    <li><strong>Matricule :</strong> <span id="md-matricule" class="badge" style="background:#ffc800;color:#212529;"></span></li>
                    <li class="mt-2"><strong>Type :</strong> <span id="md-type"></span></li>
                    <li><strong>Date :</strong> <span id="md-date"></span></li>
                    <li><strong>Kilométrage :</strong> <span id="md-km"></span></li>
                    <li><strong>Statut :</strong> <span id="md-statut"></span></li>
                    <li><strong>Prochaine échéance :</strong> <span id="md-prochaine"></span></li>
                    <li><strong>KM prochain :</strong> <span id="md-kmprochain"></span></li>
                    <li><strong>Observations :</strong> <div id="md-obs" class="text-muted mt-1"></div></li>
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
    const modal = new bootstrap.Modal(document.getElementById('modalDetailsEntretien'));
    document.getElementById('cards-container').addEventListener('click', function(e) {
        const btn = e.target.closest('.btn-details');
        if (!btn) return;
        const d = btn.dataset;
        document.getElementById('md-matricule').textContent  = d.matricule  || '—';
        document.getElementById('md-type').textContent       = d.type       || '—';
        document.getElementById('md-date').textContent       = d.date       || '—';
        document.getElementById('md-km').textContent         = d.km ? Number(d.km).toLocaleString('fr-FR') + ' km' : '—';
        document.getElementById('md-statut').textContent     = d.statut     || '—';
        document.getElementById('md-prochaine').textContent  = d.prochaine  || '—';
        document.getElementById('md-kmprochain').textContent = d.kmprochain || '—';
        document.getElementById('md-obs').textContent        = d.obs        || '—';
        modal.show();
    });
});
</script>
</body>
</html>