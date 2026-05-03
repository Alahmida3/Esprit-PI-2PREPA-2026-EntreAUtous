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
        .invoice-card-front { background: #fff; border-radius: 15px; border: 1px solid #e9ecef; box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 30px; position: relative; }
        .invoice-header { padding: 20px; border-bottom: 2px solid #ffc800; border-radius: 15px 15px 0 0; }
        .price-total-box { background-color: #f8f9fa; border: 1px solid #ffc800; padding: 15px; border-radius: 10px; text-align: center; }
        .status-badge { padding: 6px 15px; border-radius: 50px; font-size: 0.8rem; font-weight: bold; }
        .bg-payee { background-color: #d1e7dd; color: #0f5132; }
        .bg-attente { background-color: #fff3cd; color: #664d03; }
        .search-tool-bar { background: #fff; padding: 20px; border-radius: 12px; border: 1px solid #eee; margin-bottom: 30px; }
        .info-label { color: #6c757d; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; margin-bottom: 3px; }
        .btn-pdf { position: absolute; bottom: 15px; right: 20px; border-radius: 8px; font-size: 0.85rem; transition: 0.3s; }
        .btn-pdf:hover { background-color: #dc3545; color: white; }
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
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="searchRef" class="form-control border-start-0" placeholder="Référence...">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-calendar text-muted"></i></span>
                        <input type="text" id="searchDate" class="form-control border-start-0">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-sort text-muted"></i></span>
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

                       <div class="mt-3 text-end">
    <a href="../../controller/facture_action.php?action=pdf&id=<?= $f['id_facture'] ?>" 
       class="btn btn-outline-danger btn-sm">
        <i class="fas fa-file-pdf"></i> Télécharger PDF
    </a>
</div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <div class="alert alert-light text-center border shadow-sm">
                    <i class="fas fa-info-circle me-2"></i> Aucune facture disponible.
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination factures -->
        <div id="pagination-factures" class="d-flex justify-content-center align-items-center gap-3 mt-4 mb-3" style="display:none!important;">
          <button id="fac-prev" class="btn btn-outline-secondary btn-sm px-4">&#8249; Précédent</button>
          <span id="fac-page-info" style="font-size:.85rem;color:#6c757d;min-width:130px;text-align:center;"></span>
          <button id="fac-next" class="btn btn-warning btn-sm px-4">Suivant &#8250;</button>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchRef');
    const dateInput   = document.getElementById('searchDate');
    const sortSelect  = document.getElementById('sortFacture');
    const container   = document.getElementById('facturesContainer');
    const pagCont     = document.getElementById('pagination-factures');
    const btnPrev     = document.getElementById('fac-prev');
    const btnNext     = document.getElementById('fac-next');
    const pageInfo    = document.getElementById('fac-page-info');

    const PAR_PAGE  = 4;
    let pageCourante = 1;
    let visibleCards = [];

    function filtrerTrier() {
        const queryRef  = searchInput.value.toLowerCase();
        const queryDate = dateInput.value;
        const sortBy    = sortSelect.value;
        const allCards  = Array.from(container.getElementsByClassName('invoice-card-front'));

        // Filtre
        allCards.forEach(card => card.style.display = 'none');
        visibleCards = allCards.filter(card => {
            const matchRef  = card.dataset.ref.includes(queryRef);
            const matchDate = queryDate === '' || card.dataset.date === queryDate;
            return matchRef && matchDate;
        });

        // Tri
        visibleCards.sort((a, b) => {
            if (sortBy === 'price-asc')  return parseFloat(a.dataset.price) - parseFloat(b.dataset.price);
            if (sortBy === 'price-desc') return parseFloat(b.dataset.price) - parseFloat(a.dataset.price);
            return new Date(b.dataset.date) - new Date(a.dataset.date);
        });
        visibleCards.forEach(card => container.appendChild(card));

        pageCourante = 1;
        afficherPage();
    }

    function afficherPage() {
        const total      = visibleCards.length;
        const totalPages = Math.max(1, Math.ceil(total / PAR_PAGE));
        pageCourante     = Math.max(1, Math.min(pageCourante, totalPages));
        const debut      = (pageCourante - 1) * PAR_PAGE;
        const fin        = debut + PAR_PAGE;

        visibleCards.forEach((card, i) => {
            card.style.display = (i >= debut && i < fin) ? 'block' : 'none';
        });

        pageInfo.textContent = total > 0
            ? `${debut+1}–${Math.min(fin,total)} sur ${total}`
            : 'Aucun résultat';
        btnPrev.disabled = pageCourante <= 1;
        btnNext.disabled = pageCourante >= totalPages;

        if (total > PAR_PAGE) {
            pagCont.style.display = 'flex';
        } else {
            pagCont.style.display = 'none';
        }
    }

    btnPrev.addEventListener('click', () => { pageCourante--; afficherPage(); });
    btnNext.addEventListener('click', () => { pageCourante++; afficherPage(); });

    searchInput.addEventListener('input',  filtrerTrier);
    dateInput.addEventListener('change',   filtrerTrier);
    sortSelect.addEventListener('change',  filtrerTrier);

    // Init
    filtrerTrier();
});
</script>
</body>
</html>