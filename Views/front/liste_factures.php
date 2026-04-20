<?php
require_once '../../config.php';
require_once '../../controller/FactureController.php';

// Initialisation du contrôleur (C de MVC)
$controller = new FactureController($pdo);

$entretienId = isset($_GET['entretien']) ? (int)$_GET['entretien'] : null;

// Le contrôleur décide quelles données récupérer
if ($entretienId) {
    $factures = $controller->listByEntretien($entretienId);
} else {
    $factures = $controller->listAllFactures();
}
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
        .invoice-card-front { background: #fff; border-radius: 15px; border: 1px solid #e9ecef; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; }
        .invoice-header { padding: 20px; border-bottom: 2px solid #ffc800; border-radius: 15px 15px 0 0; }
        .price-total-box { background-color: #f8f9fa; border: 1px solid #ffc800; padding: 15px; border-radius: 10px; text-align: center; }
        .status-badge { padding: 6px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; }
        .bg-payee { background-color: #d1e7dd; color: #0f5132; }
        .bg-attente { background-color: #fff3cd; color: #664d03; }
        .search-tool-bar { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 30px; }
        .info-label { color: #6c757d; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 3px; }
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

        <div class="search-tool-bar">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchRef" class="form-control border-start-0" placeholder="Rechercher par référence...">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-filter text-muted"></i></span>
                        <select id="sortFacture" class="form-select border-start-0">
                            <option value="recent">Plus récentes</option>
                            <option value="price-desc">Prix : Élevé à Faible</option>
                            <option value="price-asc">Prix : Faible à Élevé</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div id="facturesContainer">
            <?php if (!empty($factures)): ?>
                <?php foreach ($factures as $f): ?>
                    
                    <div class="invoice-card-front" 
                         data-ref="<?= strtolower(htmlspecialchars($f['ref_facture'])) ?>" 
                         data-date="<?= $f['date_emission'] ?>" 
                         data-price="<?= $f['montant_ttc'] ?>">
                        
                        <div class="invoice-header d-flex justify-content-between align-items-center">
                            <div>
                                <span class="info-label">Référence</span>
                                <h4 class="mb-0 fw-bold">#<?= htmlspecialchars($f['ref_facture']) ?></h4>
                            </div>
                            <span class="status-badge <?= ($f['etat_paiement'] == 'Payée') ? 'bg-payee' : 'bg-attente' ?>">
                                <?= htmlspecialchars($f['etat_paiement']) ?>
                            </span>
                        </div>

                        <div class="card-body p-4">
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
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-light text-center border shadow-sm">
                    <i class="fas fa-info-circle me-2"></i> Aucune facture disponible.
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchRef');
    const sortSelect = document.getElementById('sortFacture');
    const container = document.getElementById('facturesContainer');
    
    function updateList() {
        const query = searchInput.value.toLowerCase();
        const cards = Array.from(container.getElementsByClassName('invoice-card-front'));

        cards.forEach(card => {
            card.style.display = card.dataset.ref.includes(query) ? "block" : "none";
        });

        const sortBy = sortSelect.value;
        const visibleCards = cards.filter(c => c.style.display !== "none");

        visibleCards.sort((a, b) => {
            if (sortBy === 'price-asc') return a.dataset.price - b.dataset.price;
            if (sortBy === 'price-desc') return b.dataset.price - a.dataset.price;
            return new Date(b.dataset.date) - new Date(a.dataset.date);
        });

        visibleCards.forEach(card => container.appendChild(card));
    }

    searchInput.addEventListener('input', updateList);
    sortSelect.addEventListener('change', updateList);
});
</script>
</body>
</html>