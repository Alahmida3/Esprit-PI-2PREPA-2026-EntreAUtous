<?php
/* VUE UNIQUEMENT — aucun SQL, aucune logique métier */
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../Controller/FactureController.php';
$clientId = $_SESSION['user_id'] ?? 0;

$ctrl = new FactureController($pdo);
$data = $ctrl->getListeFront(
    clientId:    $clientId, // On passe l'ID client en premier paramètre
    entretienId: isset($_GET['entretien']) ? (int)$_GET['entretien'] : null,
    searchRef:   trim($_GET['ref']  ?? ''),
    searchDate:  trim($_GET['date'] ?? ''),
    sort:        $_GET['sort'] ?? 'recent',
    page:        (int)($_GET['page'] ?? 1),
    parPage:     4
);

$factures    = $data['factures'];
$page        = $data['page'];
$totalPages  = $data['totalPages'];
$total       = $data['total'];
$searchRef   = $data['searchRef'];
$searchDate  = $data['searchDate'];
$sort        = $data['sort'];
$entretienId = $data['entretienId'];

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>entreAUtous - Ma Facturation</title>
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        #mainNav { background-color: #212529 !important; }
        .invoice-card-front { background:#fff; border-radius:15px; border:1px solid #e9ecef; box-shadow:0 4px 15px rgba(0,0,0,0.05); margin-bottom:30px; }
        .invoice-header { padding:20px; border-bottom:2px solid #ffc800; border-radius:15px 15px 0 0; }
        .price-total-box { background-color:#f8f9fa; border:1px solid #ffc800; padding:15px; border-radius:10px; text-align:center; }
        .status-badge { padding:6px 15px; border-radius:50px; font-size:0.8rem; font-weight:bold; }
        .bg-payee   { background-color:#d1e7dd; color:#0f5132; }
        .bg-attente { background-color:#fff3cd; color:#664d03; }
        .search-tool-bar { background:#fff; padding:20px; border-radius:12px; border:1px solid #eee; margin-bottom:30px; }
        .info-label { color:#6c757d; font-size:0.75rem; text-transform:uppercase; font-weight:700; margin-bottom:3px; }
    </style>
</head>
<body class="pt-5">

<section class="page-section">
    <div class="container mt-5">

        <div class="mb-4">
            <a href="liste_entretien.php" class="text-dark text-decoration-none fw-bold">
                <i class="fas fa-arrow-left me-2"></i> Retour à mes entretiens
            </a>
        </div>

        <!-- Barre de filtres (formulaire GET → rechargement serveur) -->
        <form method="GET" action="liste_factures.php" class="search-tool-bar">
            <?php if ($entretienId): ?>
                <input type="hidden" name="entretien" value="<?= $entretienId ?>">
            <?php endif; ?>
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="ref" class="form-control border-start-0"
                               placeholder="Référence…" value="<?= htmlspecialchars($searchRef) ?>">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                        <input type="text" name="date" placeholder="AAAA-MM-JJ" class="form-control border-start-0"
                               value="<?= htmlspecialchars($searchDate) ?>">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-sort text-muted"></i></span>
                        <select name="sort" class="form-select border-start-0">
                            <option value="recent"     <?= $sort === 'recent'     ? 'selected' : '' ?>>Plus récentes</option>
                            <option value="price-desc" <?= $sort === 'price-desc' ? 'selected' : '' ?>>Prix : Élevé à Faible</option>
                            <option value="price-asc"  <?= $sort === 'price-asc'  ? 'selected' : '' ?>>Prix : Faible à Élevé</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-warning flex-fill">OK</button>
                    <?php if ($searchRef || $searchDate || $sort !== 'recent'): ?>
                        <a href="liste_factures.php<?= $entretienId ? '?entretien='.$entretienId : '' ?>"
                           class="btn btn-outline-secondary" title="Réinitialiser">✕</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <!-- Résultats -->
        <?php if ($total === 0): ?>
            <div class="alert alert-light text-center border shadow-sm">
                <i class="fas fa-info-circle me-2"></i>
                <?= ($searchRef || $searchDate) ? 'Aucune facture ne correspond à votre recherche.' : 'Aucune facture disponible.' ?>
            </div>
        <?php else: ?>
            <p class="text-muted small mb-3">
                <?= $total ?> facture(s) trouvée(s)
                <?php if ($totalPages > 1): ?> — page <?= $page ?>/<?= $totalPages ?><?php endif; ?>
            </p>
        <?php endif; ?>

        <div id="facturesContainer">
            <?php foreach ($factures as $f): ?>
                <div class="invoice-card-front">
                    <div class="invoice-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="info-label">Référence</span>
                            <h4 class="mb-0 fw-bold">#<?= htmlspecialchars($f['ref_facture']) ?></h4>
                        </div>
                        <span class="status-badge <?= $f['etat_paiement'] === 'Payée' ? 'bg-payee' : 'bg-attente' ?>">
                            <?= htmlspecialchars($f['etat_paiement']) ?>
                        </span>
                    </div>
                    <div class="card-body p-4 mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-4 border-end">
                                <div class="info-label">Émise le</div>
                                <div class="fw-bold"><?= date('d/m/Y', strtotime($f['date_emission'])) ?></div>
                                <div class="info-label mt-2">Mode</div>
                                <div><i class="fas fa-wallet me-1 text-warning"></i> <?= htmlspecialchars($f['mode_paiement']) ?></div>
                            </div>
                            <div class="col-md-4 text-center border-end">
                                <div class="info-label">Détails Taxes</div>
                                <div class="h5 mb-0"><?= htmlspecialchars($f['taux_tva']) ?> % TVA</div>
                                <small class="text-muted">HT: <?= number_format($f['montant_ht'], 3, '.', ' ') ?> TND</small>
                            </div>
                            <div class="col-md-4 ps-md-4">
                                <div class="price-total-box">
                                    <div class="info-label">Total TTC</div>
                                    <div class="h3 mb-0 fw-bold"><?= number_format($f['montant_ttc'], 3, '.', ' ') ?> TND</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3 text-end pe-3 pb-3">
                        <a href="../../Controller/facture_action.php?action=pdf&id=<?= $f['id_facture'] ?>"
                           class="btn btn-outline-danger btn-sm">
                            <i class="fas fa-file-pdf"></i> Télécharger PDF
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination serveur -->
        <?php if ($totalPages > 1): ?>
        <div class="d-flex justify-content-center align-items-center gap-3 mt-4 mb-3">
            <?php if ($page > 1): ?>
                <a href="<?= FactureController::pageUrl($page - 1, $entretienId, $searchRef, $searchDate, $sort) ?>"
                   class="btn btn-outline-secondary btn-sm px-4">&#8249; Précédent</a>
            <?php else: ?>
                <button class="btn btn-outline-secondary btn-sm px-4" disabled>&#8249; Précédent</button>
            <?php endif; ?>

            <span style="font-size:.85rem;color:#6c757d;min-width:130px;text-align:center;">
                <?= ($page - 1) * 4 + 1 ?>–<?= min($page * 4, $total) ?> sur <?= $total ?>
            </span>

            <?php if ($page < $totalPages): ?>
                <a href="<?= FactureController::pageUrl($page + 1, $entretienId, $searchRef, $searchDate, $sort) ?>"
                   class="btn btn-warning btn-sm px-4">Suivant &#8250;</a>
            <?php else: ?>
                <button class="btn btn-warning btn-sm px-4" disabled>Suivant &#8250;</button>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>