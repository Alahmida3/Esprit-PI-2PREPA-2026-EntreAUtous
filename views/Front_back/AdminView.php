<?php
require_once __DIR__ . '/../../models/db.php'; 
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback si variables non injectées par le contrôleur
if (!isset($pieces)) {
    $conn = new connexion();
    $pdo  = $conn->conx;
    $pieces = $pdo->query("SELECT * FROM piece")->fetchAll(PDO::FETCH_ASSOC);
    $ventes = $pdo->query("SELECT v.*, p.nom_piece, p.prix FROM vente v JOIN piece p ON v.id_piece = p.id_piece ORDER BY v.date_vente DESC")->fetchAll(PDO::FETCH_ASSOC);
    $totalPieces     = $pdo->query("SELECT COUNT(*) FROM piece")->fetchColumn();
    $totalVentes     = $pdo->query("SELECT COUNT(*) FROM vente")->fetchColumn();
    $totalStock      = $pdo->query("SELECT COALESCE(SUM(quantite_stock),0) FROM piece")->fetchColumn();
   // Chiffre d'affaires total
$chiffreAffaires = $pdo->query("SELECT COALESCE(SUM(v.quantite * p.prix),0) FROM vente v JOIN piece p ON v.id_piece = p.id_piece")->fetchColumn();

// Pièce la plus vendue
$topPiece = $pdo->query("SELECT p.nom_piece, SUM(v.quantite) as total_qte FROM vente v JOIN piece p ON v.id_piece = p.id_piece GROUP BY v.id_piece ORDER BY total_qte DESC LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Ventes 7 derniers jours
$ventesParJour = $pdo->query("SELECT DATE(date_vente) as jour, SUM(v.quantite * p.prix) as ca_jour FROM vente v JOIN piece p ON v.id_piece = p.id_piece WHERE date_vente >= DATE_SUB(NOW(), INTERVAL 7 DAY) GROUP BY DATE(date_vente) ORDER BY jour ASC")->fetchAll(PDO::FETCH_ASSOC);

// Top 5 pièces CA
$topPieces = $pdo->query("SELECT p.nom_piece, SUM(v.quantite * p.prix) as ca_piece FROM vente v JOIN piece p ON v.id_piece = p.id_piece GROUP BY v.id_piece ORDER BY ca_piece DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);

// Stock faible
$stockFaible = $pdo->query("SELECT COUNT(*) FROM piece WHERE quantite_stock < 5")->fetchColumn();
}

// Paramètres de tri courants
$sortPiece  = isset($_GET['sort_piece'])  ? $_GET['sort_piece']  : 'id_piece';
$orderPiece = isset($_GET['order_piece']) ? $_GET['order_piece'] : 'asc';
$sortVente  = isset($_GET['sort_vente'])  ? $_GET['sort_vente']  : 'date_vente';
$orderVente = isset($_GET['order_vente']) ? $_GET['order_vente'] : 'desc';

// Helper : lien de tri
function sortLink($col, $currentSort, $currentOrder, $type) {
    $newOrder = ($currentSort === $col && $currentOrder === 'asc') ? 'desc' : 'asc';
    $icon = '';
    if ($currentSort === $col) {
        $icon = $currentOrder === 'asc' ? ' ↑' : ' ↓';
    }
    $params = http_build_query(array_merge($_GET, ["sort_{$type}" => $col, "order_{$type}" => $newOrder]));
    return '<a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?' . $params . '" style="color:inherit;text-decoration:none;">' . $icon . '</a>';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Panel Admin – Gestion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        :root {
            --dark-bg:      #0f1419;
            --darker-bg:    #1a1f2e;
            --card-bg:      #1e2632;
            --border-color: #2a3647;
            --text-primary: #f5f5f5;
            --text-secondary:#c0c4cb;
            --accent-red:   #e74c3c;
            --accent-blue:  #3498db;
            --accent-green: #27ae60;
            --accent-orange:#fd7e14;
            --accent-purple:#9b59b6;
        }
        body { padding-top: 80px; background-color: var(--dark-bg); color: var(--text-primary); }

        /* ── Sidebar ── */
        .sidebar { background: linear-gradient(135deg, var(--darker-bg) 0%, #1a1f2e 100%);
                   color: white; padding: 20px 0; position: fixed; left: 0; top: 0;
                   height: 100vh; width: 250px; overflow-y: auto;
                   border-right: 1px solid var(--border-color); box-shadow: 2px 0 8px rgba(0,0,0,.3); }
        .sidebar h5 { padding: 15px 20px; border-bottom: 1px solid var(--border-color);
                      color: var(--text-primary); font-weight: 600; }
        .sidebar a  { display: block; padding: 12px 20px; color: var(--text-secondary);
                      text-decoration: none; transition: all .3s; border-left: 3px solid transparent; }
        .sidebar a:hover, .sidebar a.active {
            background-color: rgba(52,152,219,.15); color: var(--accent-blue);
            border-left-color: var(--accent-blue); }
        .sidebar hr { border-color: var(--border-color); margin: 15px 0; }

        /* ── Main ── */
        .main-content { margin-left: 250px; padding: 30px; background-color: var(--dark-bg); }
        .main-content h1 { color: var(--text-primary); font-weight: 600; margin-bottom: 25px; }
        .main-content .text-muted { color: var(--text-secondary) !important; }

        /* ── Stat cards (ligne 1 : basiques) ── */
        .stat-card { background: linear-gradient(135deg, var(--card-bg) 0%, rgba(30,38,50,.6) 100%);
                     padding: 22px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.3);
                     margin-bottom: 20px; text-align: center; border: 1px solid var(--border-color);
                     transition: transform .3s, box-shadow .3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 8px 20px rgba(0,0,0,.4); }
        .stat-card i  { font-size: 2.2rem; margin-bottom: 8px; }
        .stat-card h5 { color: var(--text-secondary); margin: 8px 0; font-size: .9rem; }
        .stat-number  { font-size: 2rem; font-weight: bold; color: var(--text-primary); }

        /* ── Stat cards avancées (ligne 2) ── */
        .adv-stat { background: var(--card-bg); border: 1px solid var(--border-color);
                    border-radius: 10px; padding: 16px 20px; display: flex;
                    align-items: center; gap: 16px; margin-bottom: 16px; }
        .adv-stat .icon-box { width: 48px; height: 48px; border-radius: 10px;
                              display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
        .adv-stat .label  { font-size: .78rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing:.05em; }
        .adv-stat .value  { font-size: 1.25rem; font-weight: 700; color: var(--text-primary); margin-top: 2px; }

        /* ── Graphiques ── */
        .chart-card { background: var(--card-bg); border: 1px solid var(--border-color);
                      border-radius: 12px; padding: 22px; margin-bottom: 24px; }
        .chart-card h6 { color: var(--text-secondary); font-size: .8rem; text-transform: uppercase;
                         letter-spacing: .06em; margin-bottom: 16px; }

        /* ── Table cards ── */
        .table-card { background: linear-gradient(135deg, var(--card-bg) 0%, rgba(30,38,50,.6) 100%);
                      border-radius: 12px; padding: 25px; margin-top: 20px;
                      box-shadow: 0 4px 12px rgba(0,0,0,.3); border: 1px solid var(--border-color); }
        .table-card h5 { margin-bottom: 20px; border-bottom: 2px solid var(--accent-blue);
                         padding-bottom: 12px; color: var(--text-primary); font-weight: 600; }

        /* ── Form card ── */
        .form-card { background: linear-gradient(135deg, var(--card-bg) 0%, rgba(30,38,50,.6) 100%);
                     border-radius: 12px; padding: 25px; margin-top: 20px;
                     box-shadow: 0 4px 12px rgba(0,0,0,.3); border: 1px solid var(--border-color); }
        .form-card h5 { margin-bottom: 20px; border-bottom: 2px solid var(--accent-green);
                        padding-bottom: 12px; color: var(--text-primary); font-weight: 600; }
        .form-card label, .form-card .form-label { color: var(--text-primary); font-weight: 500; }
        .form-card .form-control {
            background-color: rgba(0,0,0,.2); border: 1px solid var(--border-color); color: var(--text-primary); }
        .form-card .form-control:focus {
            background-color: rgba(0,0,0,.3); border-color: var(--accent-blue);
            color: var(--text-primary); box-shadow: 0 0 0 .2rem rgba(52,152,219,.25); }
        .form-card .form-control::placeholder { color: var(--text-secondary); }

        /* ── Tables ── */
        table { font-size: .88rem; color: var(--text-primary); }
        thead th { background-color: rgba(52,152,219,.15); color: #fff;
                   border-color: var(--border-color); font-weight: 600;
                   text-transform: uppercase; font-size: .82rem; }
        thead th a { color: #fff !important; }
        tbody tr { border-color: var(--border-color); transition: background-color .2s; }
        tbody tr:hover { background-color: rgba(52,152,219,.08); }
        tbody td { color: #fff !important; vertical-align: middle; border-color: var(--border-color); }

        /* ── Badges ── */
        .badge { font-size: .78rem; padding: 4px 9px; border-radius: 6px; font-weight: 500; }
        .badge.bg-success { background-color: var(--accent-green)  !important; }
        .badge.bg-warning { background-color: var(--accent-orange) !important; color: #fff; }
        .badge.bg-danger  { background-color: var(--accent-red)    !important; }

        /* ── Alerts ── */
        .alert-success { background-color: rgba(39,174,96,.15);  border: 1px solid rgba(39,174,96,.3);  color: var(--text-primary); }
        .alert-danger  { background-color: rgba(231,76,60,.15);  border: 1px solid rgba(231,76,60,.3);  color: var(--text-primary); }

        /* ── Modals ── */
        .modal-content { background-color: var(--card-bg); border: 1px solid var(--border-color);
                         box-shadow: 0 4px 20px rgba(0,0,0,.5); }
        .modal-header  { border-bottom: 1px solid var(--border-color); background-color: rgba(52,152,219,.1); }
        .modal-header .modal-title { color: var(--text-primary); font-weight: 600; }
        .modal-body   { color: var(--text-primary); }
        .modal-body label  { color: var(--text-primary); font-weight: 500; margin-bottom: 6px; }
        .modal-body .form-control {
            background-color: rgba(0,0,0,.2); border: 1px solid var(--border-color); color: var(--text-primary); }
        .modal-body .form-control:focus {
            background-color: rgba(0,0,0,.3); border-color: var(--accent-blue);
            color: var(--text-primary); box-shadow: 0 0 0 .2rem rgba(52,152,219,.25); }
        .btn-close { filter: invert(1); }

        /* ── Buttons ── */
        .btn-success  { background-color: var(--accent-green); border-color: var(--accent-green); color:#fff; font-weight:500; }
        .btn-success:hover { background:#229954; border-color:#229954; box-shadow:0 4px 12px rgba(39,174,96,.3); }
        .btn-primary  { background-color: var(--accent-blue); border-color: var(--accent-blue); color:#fff; font-weight:500; }
        .btn-primary:hover  { background:#2980b9; border-color:#2980b9; box-shadow:0 4px 12px rgba(52,152,219,.3); }
        .btn-danger   { background-color: var(--accent-red); border-color: var(--accent-red); color:#fff; font-weight:500; }
        .btn-danger:hover   { background:#c0392b; border-color:#c0392b; box-shadow:0 4px 12px rgba(231,76,60,.3); }
        .btn-secondary{ background-color: var(--text-secondary); border-color: var(--text-secondary); color: var(--dark-bg); font-weight:500; }
        .btn-warning  { background-color: var(--accent-orange); border-color: var(--accent-orange); color:#fff; font-weight:500; }
        .btn-warning:hover { background:#e86c00; border-color:#e86c00; }
        .btn-action   { padding: 4px 9px; font-size: .82rem; margin: 2px; }

        /* ── Pagination ── */
        .vpg-btn { min-width:34px; height:34px; padding:0 9px; border-radius:7px;
                   border:1px solid var(--border-color); background: var(--card-bg); color: var(--text-secondary);
                   font-size:.83rem; font-weight:500; cursor:pointer; transition:all .15s;
                   display:inline-flex; align-items:center; justify-content:center; }
        .vpg-btn:hover:not(:disabled) { border-color: var(--accent-blue); color: var(--accent-blue); }
        .vpg-btn.active { background: var(--accent-blue); border-color: var(--accent-blue); color:#fff; font-weight:700; pointer-events:none; }
        .vpg-btn:disabled { opacity:.3; cursor:default; }
        .vpg-dots { color:#475569; font-size:.88rem; padding:0 4px; }

        /* ── Sort icons ── */
        th.sortable { cursor:pointer; user-select:none; }
        th.sortable:hover { background-color: rgba(52,152,219,.25); }
        .sort-icon { font-size:.7rem; margin-left:4px; opacity:.6; }
        th.sorted   .sort-icon { opacity:1; color: var(--accent-blue); }
    </style>
</head>
<body>

<!-- ═══════════════════════════ SIDEBAR ═══════════════════════════ -->
<div class="sidebar">
    <h5><i class="fas fa-cog"></i> Admin Panel</h5>
    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=admin" class="active"></i> Dashboard</a>
    <a href="#stats-section"   onclick="scrollToSection('stats-section')">  <i class="fas fa-chart-line"></i>  Statistiques</a>
    <a href="#pieces-section"  onclick="scrollToSection('pieces-section')"> <i class="fas fa-cogs"></i>         Gestion Pièces</a>
    <a href="#ventes-section"  onclick="scrollToSection('ventes-section')"> <i class="fas fa-chart-bar"></i>    Historique Ventes</a>
    <a href="#fraud-section"   onclick="scrollToSection('fraud-section')">  <i class="fas fa-shield-alt" style="color:#e74c3c"></i>  <span style="color:#e74c3c">Détecteur de Fraude</span></a>
    <a href="#stock-section"   onclick="scrollToSection('stock-section')">  <i class="fas fa-boxes" style="color:#fd7e14"></i>  <span style="color:#fd7e14">Alertes Stock</span></a>
    <hr>
    
    <hr>
    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php" style="color:#fd7e14;border-left-color:#fd7e14;">
    <i class="fas fa-tachometer-alt" style="color:#fd7e14;"></i> Admin Principal
</a>
<a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php"><i class="fas fa-arrow-left"></i> Retour au Site</a>

</div>

<script>function scrollToSection(id){var el=document.getElementById(id);if(el)el.scrollIntoView({behavior:'smooth'});}</script>

<!-- ═══════════════════════════ MAIN ═══════════════════════════ -->
<div class="main-content">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1><i class="fas fa-tachometer-alt"></i> Panel Administrateur</h1>
        <span class="text-muted"><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i'); ?></span>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle"></i> <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- ── Stats basiques ── -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fas fa-cube" style="color:var(--accent-orange)"></i>
                <h5>Total Pièces</h5>
                <div class="stat-number"><?php echo $totalPieces; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fas fa-shopping-cart" style="color:var(--accent-green)"></i>
                <h5>Total Ventes</h5>
                <div class="stat-number"><?php echo $totalVentes; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fas fa-warehouse" style="color:var(--accent-blue)"></i>
                <h5>Stock Total</h5>
                <div class="stat-number"><?php echo $totalStock; ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="fas fa-euro-sign" style="color:var(--accent-purple)"></i>
                <h5>Chiffre d'Affaires</h5>
                <div class="stat-number" style="font-size:1.4rem"><?php echo number_format($chiffreAffaires,2,',',' '); ?> €</div>
            </div>
        </div>
    </div>

    <!-- ── Statistiques avancées ── -->
    <div id="stats-section" class="row mb-3">
        <!-- Carte top pièce + stock faible -->
        <div class="col-md-4">
            <div class="adv-stat mb-3">
                <div class="icon-box" style="background:rgba(155,89,182,.15);color:var(--accent-purple)"><i class="fas fa-trophy"></i></div>
                <div>
                    <div class="label">Pièce la plus vendue</div>
                    <div class="value"><?php echo $topPiece ? htmlspecialchars($topPiece['nom_piece']) : '—'; ?></div>
                    <?php if ($topPiece): ?><small style="color:var(--text-secondary)"><?php echo $topPiece['total_qte']; ?> unité(s) vendue(s)</small><?php endif; ?>
                </div>
            </div>
            <div class="adv-stat">
                <div class="icon-box" style="background:rgba(231,76,60,.15);color:var(--accent-red)"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <div class="label">Stock faible (< 5)</div>
                    <div class="value" style="color:<?php echo $stockFaible > 0 ? 'var(--accent-red)' : 'var(--accent-green)'; ?>">
                        <?php echo $stockFaible; ?> pièce(s)
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique ventes 7 jours -->
        <div class="col-md-4">
            <div class="chart-card" style="padding:16px;">
                <h6><i class="fas fa-chart-line"></i> Ventes – 7 derniers jours</h6>
                <canvas id="chartJours" height="140"></canvas>
            </div>
        </div>

        <!-- Graphique top 5 pièces CA -->
        <div class="col-md-4">
            <div class="chart-card" style="padding:16px;">
                <h6><i class="fas fa-chart-bar"></i> Top 5 Pièces – CA (€)</h6>
                <canvas id="chartTop5" height="140"></canvas>
            </div>
        </div>
    </div>

    <!-- ── Formulaire ajout pièce ── -->
    <div class="form-card" id="add-piece-form">
        <h5><i class="fas fa-plus-circle"></i> Ajouter une Nouvelle Pièce</h5>
        <form method="POST" action="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=add_piece">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nom de la Pièce *</label>
                    <input type="text" class="form-control" name="nom_piece" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Référence *</label>
                    <input type="text" class="form-control" name="reference" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Catégorie *</label>
                    <input type="text" class="form-control" name="categorie" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Prix *</label>
                    <input type="number" class="form-control" name="prix" step="0.01" required>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Quantité Stock *</label>
                    <input type="number" class="form-control" name="quantite_stock" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Fournisseur</label>
                    <input type="text" class="form-control" name="fourniseur">
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Image URL</label>
                    <input type="text" class="form-control" name="image">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea class="form-control" name="description" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Ajouter la Pièce</button>
        </form>
    </div>

    <!-- ══════════════ TABLE PIÈCES ══════════════ -->
    <div class="table-card" id="pieces-section">
        <h5><i class="fas fa-table"></i> Gestion des Pièces</h5>

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
            <span id="pieces-info" style="font-size:.82rem;color:#94a3b8;"></span>
            <input type="text" id="pieces-search" placeholder="Filtrer par nom..." oninput="piecesFilter()"
                style="background:#1e2632;border:1px solid #2a3647;color:#f5f5f5;border-radius:8px;padding:7px 14px;font-size:.85rem;width:220px;outline:none;">
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <?php
                        $colsPiece = [
                            'id_piece'       => 'ID',
                            'nom_piece'      => 'Nom',
                            'reference'      => 'Référence',
                            'categorie'      => 'Catégorie',
                            'prix'           => 'Prix',
                            'quantite_stock' => 'Stock',
                            'fourniseur'     => 'Fournisseur',
                        ];
                        foreach ($colsPiece as $col => $label):
                            $isSorted = $sortPiece === $col;
                            $newOrder = ($isSorted && $orderPiece === 'asc') ? 'desc' : 'asc';
                            $icon = $isSorted ? ($orderPiece === 'asc' ? '↑' : '↓') : '⇅';
                            $params = http_build_query(array_merge($_GET, ['sort_piece'=>$col,'order_piece'=>$newOrder,'action'=>'admin']));
                        ?>
                        <th class="sortable <?php echo $isSorted ? 'sorted' : ''; ?>">
                            <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?<?php echo $params; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo $label; ?> <span class="sort-icon"><?php echo $icon; ?></span>
                            </a>
                        </th>
                        <?php endforeach; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="pieces-tbody">
                    <?php if (count($pieces) > 0): ?>
                        <?php foreach ($pieces as $piece): ?>
                        <tr class="piece-row" data-nom="<?php echo strtolower(htmlspecialchars($piece['nom_piece'])); ?>">
                            <td><?php echo $piece['id_piece']; ?></td>
                            <td><?php echo htmlspecialchars($piece['nom_piece']); ?></td>
                            <td><?php echo htmlspecialchars($piece['reference']); ?></td>
                            <td><?php echo htmlspecialchars($piece['categorie']); ?></td>
                            <td><?php echo number_format($piece['prix'], 2, ',', ' '); ?> €</td>
                            <td>
                                <span class="badge bg-<?php echo $piece['quantite_stock'] > 10 ? 'success' : ($piece['quantite_stock'] > 0 ? 'warning' : 'danger'); ?>">
                                    <?php echo $piece['quantite_stock']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($piece['fourniseur'] ?? '—'); ?></td>
                            <td>
                                <a href="#" onclick="editPiece(<?php echo $piece['id_piece']; ?>)" class="btn btn-primary btn-action">
                                    <i class="fas fa-edit"></i>
                                </a>
                               <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=export_pdf_vente&id=<?php echo $piece['id_piece']; ?>"
                                   class="btn btn-warning btn-action" title="Exporter ventes en PDF" target="_blank">
                                    <i class="fas fa-file-pdf"></i>
                                </a>
                                <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=delete_piece&id=<?php echo $piece['id_piece']; ?>"
                                   class="btn btn-danger btn-action"
                                   onclick="return confirm('Supprimer cette pièce et toutes ses ventes ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="8" class="text-center text-muted">Aucune pièce trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="pieces-pagination" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;flex-wrap:wrap;"></div>
    </div>

    <!-- Pagination pièces JS -->
    <script>
    (function(){
        var PER = 10, cur = 1, filtered = [];
        function all(){ return Array.from(document.querySelectorAll('.piece-row')); }
        function piecesFilter(){
            var q = document.getElementById('pieces-search').value.toLowerCase().trim();
            filtered = all().filter(function(r){ return !q || r.dataset.nom.includes(q); });
            cur = 1; render();
        }
        window.piecesFilter = piecesFilter;
        function render(){
            var rows = filtered.length ? filtered : all();
            var total = rows.length, totalPages = Math.max(1, Math.ceil(total/PER));
            cur = Math.min(cur, totalPages);
            var start = (cur-1)*PER, end = start+PER;
            all().forEach(function(r){ r.style.display='none'; });
            rows.forEach(function(r,i){ r.style.display=(i>=start&&i<end)?'':'none'; });
            document.getElementById('pieces-info').textContent =
                total===0?'Aucune pièce':'Affichage '+(start+1)+'-'+Math.min(end,total)+' sur '+total;
            var pg = document.getElementById('pieces-pagination'); pg.innerHTML='';
            if(totalPages<=1) return;
            function mkBtn(lbl,p,active,disabled){
                var b=document.createElement('button'); b.className='vpg-btn'+(active?' active':'');
                b.textContent=lbl; b.disabled=disabled;
                if(!active&&!disabled) b.onclick=function(){ cur=p; render(); }; return b;
            }
            function mkDots(){ var s=document.createElement('span'); s.className='vpg-dots'; s.textContent='…'; return s; }
            pg.appendChild(mkBtn('‹',cur-1,false,cur===1));
            var prev=null;
            for(var p=1;p<=totalPages;p++){
                if(p===1||p===totalPages||(p>=cur-2&&p<=cur+2)){
                    if(prev!==null&&p-prev>1) pg.appendChild(mkDots());
                    pg.appendChild(mkBtn(p,p,p===cur,false)); prev=p;
                }
            }
            pg.appendChild(mkBtn('›',cur+1,false,cur===totalPages));
        }
        document.addEventListener('DOMContentLoaded',function(){ filtered=all(); render(); });
    })();
    </script>

    <!-- ══════════════ TABLE VENTES ══════════════ -->
    <div class="table-card" id="ventes-section">
        <h5><i class="fas fa-history"></i> Historique des Ventes</h5>

        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:14px;">
            <span id="ventes-info" style="font-size:.82rem;color:#94a3b8;"></span>
            <input type="text" id="ventes-search" placeholder="Filtrer par pièce..." oninput="ventesFilter()"
                style="background:#1e2632;border:1px solid #2a3647;color:#f5f5f5;border-radius:8px;padding:7px 14px;font-size:.85rem;width:220px;outline:none;">
        </div>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <?php
                        $colsVente = [
                            'id'         => 'ID Vente',
                            'nom_piece'  => 'Pièce',
                            'quantite'   => 'Quantité',
                            'date_vente' => 'Date',
                        ];
                        foreach ($colsVente as $col => $label):
                            $isSorted = $sortVente === $col;
                            $newOrder = ($isSorted && $orderVente === 'asc') ? 'desc' : 'asc';
                            $icon = $isSorted ? ($orderVente === 'asc' ? '↑' : '↓') : '⇅';
                            $params = http_build_query(array_merge($_GET, ['sort_vente'=>$col,'order_vente'=>$newOrder,'action'=>'admin']));
                        ?>
                        <th class="sortable <?php echo $isSorted ? 'sorted' : ''; ?>">
                            <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?<?php echo $params; ?>" style="color:inherit;text-decoration:none;">
                                <?php echo $label; ?> <span class="sort-icon"><?php echo $icon; ?></span>
                            </a>
                        </th>
                        <?php endforeach; ?>
                        <th>Sous-total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="ventes-tbody">
                    <?php if (count($ventes) > 0): ?>
                        <?php foreach ($ventes as $vente): ?>
                        <tr class="vente-row" data-nom="<?php echo strtolower(htmlspecialchars($vente['nom_piece'])); ?>">
                            <td><?php echo $vente['id']; ?></td>
                            <td><?php echo htmlspecialchars($vente['nom_piece']); ?></td>
                            <td><span class="badge bg-primary"><?php echo $vente['quantite']; ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($vente['date_vente'])); ?></td>
                            <td style="color:var(--accent-green) !important;font-weight:600;">
                                <?php echo number_format($vente['quantite'] * $vente['prix'], 2, ',', ' '); ?> €
                            </td>
                            <td>
                                <a href="#" onclick="editVente(<?php echo $vente['id']; ?>, <?php echo $vente['quantite']; ?>, '<?php echo $vente['date_vente']; ?>')"
                                   class="btn btn-primary btn-action"><i class="fas fa-edit"></i></a>
                                <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=delete_vente&id=<?php echo $vente['id']; ?>"
                                   class="btn btn-danger btn-action"
                                   onclick="return confirm('Supprimer cette vente et restaurer le stock ?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                    <tr><td colspan="6" class="text-center text-muted">Aucune vente trouvée</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div id="ventes-pagination" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:14px;flex-wrap:wrap;"></div>
    </div>

    <!-- Pagination ventes JS -->
    <script>
    (function(){
        var PER=10, cur=1, filtered=[];
        function all(){ return Array.from(document.querySelectorAll('.vente-row')); }
        function ventesFilter(){
            var q=document.getElementById('ventes-search').value.toLowerCase().trim();
            filtered=all().filter(function(r){ return !q||r.dataset.nom.includes(q); });
            cur=1; render();
        }
        window.ventesFilter=ventesFilter;
        function render(){
            var rows=filtered.length?filtered:all();
            var total=rows.length, totalPages=Math.max(1,Math.ceil(total/PER));
            cur=Math.min(cur,totalPages);
            var start=(cur-1)*PER, end=start+PER;
            all().forEach(function(r){ r.style.display='none'; });
            rows.forEach(function(r,i){ r.style.display=(i>=start&&i<end)?'':'none'; });
            document.getElementById('ventes-info').textContent =
                total===0?'Aucune vente':'Affichage '+(start+1)+'-'+Math.min(end,total)+' sur '+total;
            var pg=document.getElementById('ventes-pagination'); pg.innerHTML='';
            if(totalPages<=1) return;
            function mkBtn(lbl,p,active,disabled){
                var b=document.createElement('button'); b.className='vpg-btn'+(active?' active':'');
                b.textContent=lbl; b.disabled=disabled;
                if(!active&&!disabled) b.onclick=function(){ cur=p; render(); }; return b;
            }
            function mkDots(){ var s=document.createElement('span'); s.className='vpg-dots'; s.textContent='…'; return s; }
            pg.appendChild(mkBtn('‹',cur-1,false,cur===1));
            var prev=null;
            for(var p=1;p<=totalPages;p++){
                if(p===1||p===totalPages||(p>=cur-2&&p<=cur+2)){
                    if(prev!==null&&p-prev>1) pg.appendChild(mkDots());
                    pg.appendChild(mkBtn(p,p,p===cur,false)); prev=p;
                }
            }
            pg.appendChild(mkBtn('›',cur+1,false,cur===totalPages));
        }
        document.addEventListener('DOMContentLoaded',function(){ filtered=all(); render(); });
    })();
    </script>


    <!-- ══════════════ ALERTES STOCK ══════════════ -->
    <div class="table-card" id="stock-section" style="margin-top:30px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h4 style="margin:0;color:#fd7e14;">
                <i class="fas fa-boxes"></i> Alertes Stock Automatiques
            </h4>
            <div id="stock-badge" style="display:none;background:#e74c3c;color:#fff;padding:5px 14px;border-radius:20px;font-weight:bold;font-size:.85rem;"></div>
        </div>

        <!-- Stats rapides -->
        <div id="stock-stats" style="display:none;gap:15px;flex-wrap:wrap;margin-bottom:20px;" class="d-flex">
            <div style="flex:1;min-width:140px;background:#1a1f2e;border-radius:10px;padding:15px;text-align:center;border:1px solid #2a3647;">
                <div id="st-bas" style="font-size:2rem;font-weight:bold;color:#fd7e14;">-</div>
                <div style="font-size:.75rem;color:#c0c4cb;margin-top:4px;">Stock bas</div>
            </div>
            <div style="flex:1;min-width:140px;background:#1a1f2e;border-radius:10px;padding:15px;text-align:center;border:1px solid #2a3647;">
                <div id="st-rupture" style="font-size:2rem;font-weight:bold;color:#e74c3c;">-</div>
                <div style="font-size:.75rem;color:#c0c4cb;margin-top:4px;">En rupture</div>
            </div>
            <div style="flex:1;min-width:140px;background:#1a1f2e;border-radius:10px;padding:15px;text-align:center;border:1px solid #2a3647;">
                <div id="st-normal" style="font-size:2rem;font-weight:bold;color:#27ae60;">-</div>
                <div style="font-size:.75rem;color:#c0c4cb;margin-top:4px;">Stock normal</div>
            </div>
            <div style="flex:1;min-width:140px;background:#1a1f2e;border-radius:10px;padding:15px;text-align:center;border:1px solid #2a3647;">
                <div id="st-total" style="font-size:2rem;font-weight:bold;color:#c0c4cb;">-</div>
                <div style="font-size:.75rem;color:#c0c4cb;margin-top:4px;">Total pièces</div>
            </div>
        </div>

        <!-- Email status -->
        <div id="stock-email-status" style="display:none;padding:12px 16px;border-radius:8px;border:1px solid;margin-bottom:15px;font-weight:600;font-size:.9rem;"></div>

        <!-- Loading -->
        <div id="stock-loading" style="text-align:center;padding:30px;">
            <div class="spinner-border" style="color:#fd7e14;" role="status"></div>
            <p style="margin-top:12px;color:#c0c4cb;">Vérification du stock en cours...</p>
        </div>

        <!-- Tableau pièces bas -->
        <div id="stock-result" style="display:none;">
            <h6 style="color:#c0c4cb;text-transform:uppercase;font-size:.8rem;margin-bottom:12px;">
                <i class="fas fa-exclamation-triangle" style="color:#fd7e14;"></i> Pièces à commander
            </h6>
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:.9rem;">
                    <thead>
                        <tr style="border-bottom:2px solid #2a3647;">
                            <th style="padding:10px;text-align:left;color:#c0c4cb;">Pièce</th>
                            <th style="padding:10px;text-align:left;color:#c0c4cb;">Référence</th>
                            <th style="padding:10px;text-align:center;color:#c0c4cb;">Stock</th>
                            <th style="padding:10px;text-align:center;color:#c0c4cb;">Statut</th>
                            
                        </tr>
                    </thead>
                    <tbody id="stock-tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- Aucune alerte -->
        <div id="stock-ok" style="display:none;text-align:center;padding:30px;color:#27ae60;">
            <i class="fas fa-check-circle" style="font-size:3rem;margin-bottom:12px;display:block;"></i>
            <strong>Tout le stock est suffisant !</strong>
            <p style="color:#c0c4cb;margin-top:8px;font-size:.9rem;">Aucune pièce en dessous du seuil de 5 unités.</p>
        </div>

        <!-- Erreur -->
        <div id="stock-error" style="display:none;" class="alert alert-danger mt-3">
            <i class="fas fa-exclamation-circle"></i> <span id="stock-error-msg"></span>
        </div>
    </div>

    <script>
    // Lancement automatique à l'ouverture de la page admin
    document.addEventListener('DOMContentLoaded', function() {
        verifierStock();
    });

    function verifierStock() {
        document.getElementById('stock-loading').style.display  = 'block';
        document.getElementById('stock-result').style.display   = 'none';
        document.getElementById('stock-ok').style.display       = 'none';
        document.getElementById('stock-error').style.display    = 'none';
        document.getElementById('stock-stats').style.display    = 'none';
        document.getElementById('stock-email-status').style.display = 'none';

        fetch('ai_stock_alert.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({})
        })
        .then(r => r.json())
        .then(data => {
            document.getElementById('stock-loading').style.display = 'none';

            if (!data.success) {
                document.getElementById('stock-error-msg').textContent = data.error;
                document.getElementById('stock-error').style.display   = 'block';
                return;
            }

            // Stats
            const ss = data.stats_stock;
            document.getElementById('st-bas').textContent     = data.pieces_bas.length;
            document.getElementById('st-rupture').textContent = ss.rupture;
            document.getElementById('st-normal').textContent  = ss.normal;
            document.getElementById('st-total').textContent   = ss.total_pieces;
            document.getElementById('stock-stats').style.display = 'flex';

            if (data.pieces_bas.length === 0) {
                document.getElementById('stock-ok').style.display = 'block';
                return;
            }

            // Badge nombre alertes
            const badge = document.getElementById('stock-badge');
            badge.textContent = data.pieces_bas.length + ' alerte(s)';
            badge.style.display = 'inline-block';

            // Remplir tableau
            const tbody = document.getElementById('stock-tbody');
            tbody.innerHTML = '';
            data.pieces_bas.forEach(p => {
                const isRupture = p.quantite_stock == 0;
                const badge2 = isRupture
                    ? '<span style="background:#e74c3c;color:#fff;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:bold;">RUPTURE</span>'
                    : '<span style="background:#fd7e14;color:#fff;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:bold;">CRITIQUE</span>';
                const bg = isRupture ? 'rgba(231,76,60,.08)' : 'rgba(253,126,20,.06)';
                tbody.innerHTML += `<tr style="background:${bg};border-bottom:1px solid #2a3647;">
                    <td style="padding:10px;font-weight:600;color:#f5f5f5;">${p.nom_piece}</td>
                    <td style="padding:10px;color:#c0c4cb;">${p.reference || '-'}</td>
                    <td style="padding:10px;text-align:center;font-size:1.3rem;font-weight:bold;color:#e74c3c;">${p.quantite_stock}</td>
                    <td style="padding:10px;text-align:center;">${badge2}</td>

                </tr>`;
            });
            document.getElementById('stock-result').style.display = 'block';

            // Statut email
            const emailEl = document.getElementById('stock-email-status');
            if (data.email_envoye) {
                emailEl.innerHTML = '<i class="fas fa-envelope"></i> ✅ Email d\'alerte envoyé avec succès !';
                emailEl.style.cssText = 'display:block;padding:12px 16px;border-radius:8px;border:1px solid #27ae60;background:rgba(39,174,96,.15);color:#27ae60;font-weight:600;font-size:.9rem;';
            } else if (data.pieces_bas.length > 0) {
                emailEl.innerHTML = '<i class="fas fa-envelope"></i> ⚠️ Stock bas détecté. ' + (data.email_erreur || 'Email non envoyé — vérifiez la config SMTP.');
                emailEl.style.cssText = 'display:block;padding:12px 16px;border-radius:8px;border:1px solid #fd7e14;background:rgba(253,126,20,.15);color:#fd7e14;font-weight:600;font-size:.9rem;';
            }
        })
        .catch(err => {
            document.getElementById('stock-loading').style.display = 'none';
            document.getElementById('stock-error-msg').textContent = 'Erreur réseau : ' + err.message;
            document.getElementById('stock-error').style.display   = 'block';
        });
    }
    </script>
</div><!-- /main-content -->

<!-- ══════════════ MODAL EDIT PIÈCE ══════════════ -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Éditer une Pièce</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editForm">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Nom de la Pièce</label><input type="text" class="form-control" name="nom_piece" id="edit_nom_piece" required></div>
                        <div class="col-md-6 mb-3"><label>Référence</label><input type="text" class="form-control" name="reference" id="edit_reference" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Catégorie</label><input type="text" class="form-control" name="categorie" id="edit_categorie" required></div>
                        <div class="col-md-6 mb-3"><label>Prix</label><input type="number" class="form-control" name="prix" id="edit_prix" step="0.01" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label>Quantité Stock</label><input type="number" class="form-control" name="quantite_stock" id="edit_quantite_stock" required></div>
                        <div class="col-md-6 mb-3"><label>Fournisseur</label><input type="text" class="form-control" name="fourniseur" id="edit_fourniseur"></div>
                    </div>
                    <div class="mb-3"><label>Image URL</label><input type="text" class="form-control" name="image" id="edit_image"></div>
                    <div class="mb-3"><label>Description</label><textarea class="form-control" name="description" id="edit_description" rows="3"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════ MODAL EDIT VENTE ══════════════ -->
<div class="modal fade" id="editVenteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-edit"></i> Modifier la Vente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" id="editVenteForm">
                <div class="modal-body">
                    <div class="mb-3"><label>Quantité</label><input type="number" class="form-control" name="quantite" id="edit_vente_quantite" min="1" required></div>
                    <div class="mb-3"><label>Date de Vente</label><input type="datetime-local" class="form-control" name="date_vente" id="edit_vente_date" required></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ══════════════ SCRIPTS ══════════════ -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Données PHP → JS pour graphiques ──────────────────────────────────────
const ventesParJour = <?php echo json_encode($ventesParJour ?? []); ?>;
const topPieces     = <?php echo json_encode($topPieces ?? []); ?>;
const pieces        = <?php echo json_encode($pieces); ?>;

// ── Graphique : ventes 7 derniers jours ───────────────────────────────────
(function(){
    var labels = ventesParJour.map(function(v){ return v.jour; });
    var data   = ventesParJour.map(function(v){ return parseFloat(v.ca_jour); });
    new Chart(document.getElementById('chartJours'), {
        type: 'line',
        data: {
            labels: labels.length ? labels : ['Aucune donnée'],
            datasets: [{
                label: 'CA (€)',
                data: data.length ? data : [0],
                borderColor: '#3498db',
                backgroundColor: 'rgba(52,152,219,.15)',
                tension: 0.35,
                fill: true,
                pointBackgroundColor: '#3498db',
                pointRadius: 4
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color:'#94a3b8', font:{size:10} }, grid: { color:'rgba(255,255,255,.05)' } },
                y: { ticks: { color:'#94a3b8', font:{size:10} }, grid: { color:'rgba(255,255,255,.05)' } }
            }
        }
    });
})();

// ── Graphique : top 5 pièces CA ───────────────────────────────────────────
(function(){
    var labels = topPieces.map(function(p){ return p.nom_piece; });
    var data   = topPieces.map(function(p){ return parseFloat(p.ca_piece); });
    var colors = ['#3498db','#27ae60','#fd7e14','#9b59b6','#e74c3c'];
    new Chart(document.getElementById('chartTop5'), {
        type: 'bar',
        data: {
            labels: labels.length ? labels : ['Aucune donnée'],
            datasets: [{
                label: 'CA (€)',
                data: data.length ? data : [0],
                backgroundColor: colors,
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color:'#94a3b8', font:{size:9}, maxRotation:30 }, grid: { color:'rgba(255,255,255,.05)' } },
                y: { ticks: { color:'#94a3b8', font:{size:9} }, grid: { color:'rgba(255,255,255,.05)' } }
            }
        }
    });
})();

// ── Modal edit pièce ──────────────────────────────────────────────────────
function editPiece(id) {
    const p = pieces.find(function(x){ return x.id_piece == id; });
    if (!p) return;
    document.getElementById('edit_nom_piece').value      = p.nom_piece;
    document.getElementById('edit_reference').value      = p.reference;
    document.getElementById('edit_categorie').value      = p.categorie;
    document.getElementById('edit_prix').value           = p.prix;
    document.getElementById('edit_quantite_stock').value = p.quantite_stock;
    document.getElementById('edit_fourniseur').value     = p.fourniseur || '';
    document.getElementById('edit_image').value          = p.image || '';
    document.getElementById('edit_description').value    = p.description || '';
   document.getElementById('editForm').action = '/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=edit_piece&id=' + id;
    new bootstrap.Modal(document.getElementById('editModal')).show();
}

// ── Modal edit vente ──────────────────────────────────────────────────────
function editVente(id, quantite, date_vente) {
    document.getElementById('edit_vente_quantite').value = quantite;
    const d   = new Date(date_vente.replace(' ', 'T'));
    const pad = function(n){ return String(n).padStart(2,'0'); };
    document.getElementById('edit_vente_date').value =
        d.getFullYear() + '-' + pad(d.getMonth()+1) + '-' + pad(d.getDate()) +
        'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    document.getElementById('editVenteForm').action = '/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=edit_vente&id=' + id;
    new bootstrap.Modal(document.getElementById('editVenteModal')).show();
}
</script>
</body>
</html>