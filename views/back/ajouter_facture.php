<?php
/* VIEW ONLY — le formulaire soumet vers facture_action.php?action=create */
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../controller/FactureController.php';
if (!$pdo) die("Erreur de connexion à la base de données.");

session_start();
$validationErrors = $_SESSION['facture_errors'] ?? [];
$old              = $_SESSION['facture_post']   ?? [];
unset($_SESSION['facture_errors'], $_SESSION['facture_post']);


// Entretien lié (GET ou retour POST)
$entId = $old['entretien'] ?? (isset($_GET['entretien']) ? (int)$_GET['entretien'] : '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Ajouter une Facture</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
  <link rel="stylesheet" href="/Esprit-PI-2PREPA-2026-EntreAUtous/assets/back/css/theme.css" />
  <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-accent: #6366f1;
            --header-h: 64px;
        }

        /* ── Layout ── */
        body { font-family: 'Public Sans', sans-serif; background: #f1f5f9; }
        .layout-wrapper { display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            position: fixed; left: 0; top: 0; height: 100vh;
            z-index: 1040; display: flex; flex-direction: column;
            transition: width .25s ease;
            overflow: hidden;
        }
        .sidebar-brand {
            display: flex; align-items: center; gap: 10px;
            padding: 20px 20px 16px;
            border-bottom: 1px solid rgba(255,255,255,.08);
            text-decoration: none;
        }
        .sidebar-brand .brand-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: var(--sidebar-accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; color: #fff; font-size: 1rem; flex-shrink: 0;
        }
        .sidebar-brand .brand-name {
            font-weight: 700; color: #fff; font-size: 1.15rem; white-space: nowrap;
        }
        .sidebar-brand .brand-badge {
            font-size: 9px; background: rgba(99,102,241,.25); color: #a5b4fc;
            border-radius: 4px; padding: 1px 6px; margin-left: 4px; white-space: nowrap;
        }

        /* Nav sections */
        .sidebar-nav { flex: 1; overflow-y: auto; padding: 12px 0; }
        .sidebar-nav::-webkit-scrollbar { width: 4px; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: rgba(255,255,255,.1); border-radius: 4px; }
        .nav-section-label {
            font-size: 10px; font-weight: 700; letter-spacing: .08em;
            color: rgba(255,255,255,.35); text-transform: uppercase;
            padding: 16px 20px 6px;
        }
        .sidebar-link {
            display: flex; align-items: center; gap: 12px;
            padding: 9px 20px; color: rgba(255,255,255,.65);
            text-decoration: none; font-size: .875rem; font-weight: 500;
            border-radius: 0; transition: background .15s, color .15s;
            position: relative; white-space: nowrap;
        }
        .sidebar-link:hover { background: rgba(255,255,255,.06); color: #fff; }
        .sidebar-link.active { background: rgba(99,102,241,.2); color: #a5b4fc; }
        .sidebar-link.active::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0;
            width: 3px; background: var(--sidebar-accent); border-radius: 0 3px 3px 0;
        }
        .sidebar-link .s-icon {
            width: 20px; height: 20px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
        }
        .sidebar-link .s-badge {
            margin-left: auto; font-size: 10px; padding: 1px 7px; border-radius: 20px;
        }

        /* Collapse sub-menu */
        .sidebar-collapse .sub-nav { padding-left: 52px; }
        .sidebar-collapse .sub-nav .sidebar-link { font-size: .82rem; padding: 6px 16px 6px 0; color: rgba(255,255,255,.5); }
        .sidebar-collapse .sub-nav .sidebar-link:hover { color: #fff; background: transparent; }
        .sidebar-collapse .sub-nav .sidebar-link.active { color: #a5b4fc; background: transparent; }

        /* Sidebar bottom */
        .sidebar-footer {
            padding: 14px 20px;
            border-top: 1px solid rgba(255,255,255,.08);
        }
        .sidebar-footer .admin-info { display: flex; align-items: center; gap: 10px; }
        .sidebar-footer .admin-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: var(--sidebar-accent);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; color: #fff; font-size: .8rem; flex-shrink: 0;
        }
        .sidebar-footer .admin-name { font-size: .82rem; font-weight: 600; color: #fff; }
        .sidebar-footer .admin-role { font-size: .72rem; color: rgba(255,255,255,.4); }
        .sidebar-footer .logout-btn {
            margin-left: auto; color: rgba(255,255,255,.4); text-decoration: none;
            font-size: 1rem; transition: color .15s;
        }
        .sidebar-footer .logout-btn:hover { color: #f87171; }

        /* ── Main content ── */
        .main-area { margin-left: var(--sidebar-width); flex: 1; display: flex; flex-direction: column; }

        /* ── Topbar ── */
        .topbar {
            height: var(--header-h); background: #fff;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 24px; position: sticky; top: 0; z-index: 1030;
        }
        .topbar-left { display: flex; align-items: center; gap: 8px; }
        .topbar-title { font-size: 1.05rem; font-weight: 700; color: #1e293b; }
        .topbar-sub { font-size: .75rem; color: #94a3b8; }
        .topbar-right { display: flex; align-items: center; gap: 10px; }
        .topbar-icon-btn {
            width: 36px; height: 36px; border-radius: 50%;
            border: 1px solid #e2e8f0; background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; color: #64748b; font-size: 1rem; transition: background .15s;
            position: relative; text-decoration: none;
        }
        .topbar-icon-btn:hover { background: #f1f5f9; color: #1e293b; }
        .topbar-icon-btn .notif-dot {
            position: absolute; top: 4px; right: 4px;
            width: 8px; height: 8px; background: #ef4444;
            border: 2px solid #fff; border-radius: 50%;
        }

        /* ── Page content ── */
        .page-content { padding: 24px; flex: 1; }

        /* ── Cards stats ── */
        .stat-card {
            background: #fff; border-radius: 14px;
            padding: 20px; display: flex; align-items: center; gap: 16px;
            box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #f1f5f9;
            transition: box-shadow .2s;
        }
        .stat-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; flex-shrink: 0; }
        .stat-label { font-size: .72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .04em; margin-bottom: 2px; }
        .stat-value { font-size: 1.75rem; font-weight: 800; color: #1e293b; line-height: 1; }
        .stat-delta { font-size: .72rem; color: #22c55e; font-weight: 600; margin-top: 2px; }

        /* ── Table ── */
        .data-card { background: #fff; border-radius: 14px; box-shadow: 0 1px 3px rgba(0,0,0,.06); border: 1px solid #f1f5f9; overflow: hidden; }
        .data-card-header { padding: 18px 20px 14px; border-bottom: 1px solid #f1f5f9; }
        .table-hover tbody tr:hover { background: rgba(99,102,241,.04); }
        .page-item.active .page-link { background: #6366f1; border-color: #6366f1; }
        .page-link { border-radius: 8px !important; margin: 0 2px; }
        th { font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; vertical-align: middle; }

        /* ── Misc ── */
        .bg-gradient-mixed { background: linear-gradient(135deg,#6366f1 0%,#3b82f6 100%) !important; }
        .filter-bar { background: rgba(99,102,241,.05); border-radius: 12px; padding: 14px 16px; }
        .search-wrapper { position: relative; }
        .search-wrapper .ti-search { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .search-wrapper input { padding-left: 30px; }
        .toast-notif { position: fixed; top: 70px; right: 20px; z-index: 9999; min-width: 280px; }
        #backToTop { position: fixed; bottom: 25px; right: 25px; display: none; z-index: 999; border-radius: 50%; width: 42px; height: 42px; }

        [data-bs-theme="dark"] .topbar,
        [data-bs-theme="dark"] .sidebar { border-color: #1e293b; }
        [data-bs-theme="dark"] .stat-card,
        [data-bs-theme="dark"] .data-card { background: #1e293b; border-color: #334155; }
        [data-bs-theme="dark"] .topbar { background: #0f172a; }

        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-area { margin-left: 0; }
        }
    </style>

  <style>
    body { background: #0f172a !important; color: #e2e8f0 !important; }
    .main-area { background: #0f172a !important; }
    .page-content { padding: 24px; background: #0f172a !important; }
    .card { background: #1e293b !important; border-color: #334155 !important; color: #e2e8f0 !important; }
    .card-header { background: #1e293b !important; border-color: #334155 !important; color: #e2e8f0 !important; }
    .card-body { background: #1e293b !important; }
    .form-control, .form-select { background: #0f172a !important; border-color: #334155 !important; color: #e2e8f0 !important; }
    .form-control:focus, .form-select:focus { background: #1e293b !important; border-color: #6366f1 !important; color: #e2e8f0 !important; }
    .form-label, label { color: #cbd5e1 !important; }
    .text-muted { color: #94a3b8 !important; }
    .border-bottom, .border-top { border-color: #334155 !important; }
    .breadcrumb-item a { color: #94a3b8 !important; }
    .breadcrumb-item.active { color: #e2e8f0 !important; }
    .table { color: #e2e8f0 !important; }
    .table thead { background: #1e293b !important; }
    .table-light { background: #1e293b !important; color: #e2e8f0 !important; }
    .alert-danger { background: #450a0a !important; border-color: #dc2626 !important; color: #fca5a5 !important; }
    .alert-success { background: #052e16 !important; border-color: #16a34a !important; color: #86efac !important; }
    h5, h6, small { color: #e2e8f0 !important; }
    .input-group-text { background: #1e293b !important; border-color: #334155 !important; color: #94a3b8 !important; }
    .nav-tabs .nav-link { color: #94a3b8 !important; }
    .nav-tabs .nav-link.active { background: #1e293b !important; color: #e2e8f0 !important; border-color: #334155 !important; }
    .tab-content { background: #1e293b !important; border: 1px solid #334155; border-top: none; padding: 16px; border-radius: 0 0 8px 8px; }
    .badge { opacity: 0.9; }
    .icon-shape { background: #334155 !important; }
  </style>
</head>

<body>
<div class="layout-wrapper">

<aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <a class="sidebar-brand" href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php">
            <div class="brand-icon">AT</div>
            <span class="brand-name">Entre AuTout</span>
            <span class="brand-badge">Admin</span>
        </a>

        <!-- Nav -->
        <nav class="sidebar-nav" id="sidebarNav">

            <!-- Tableau de bord -->
            <div class="nav-section-label">Principal</div>
            <a class="sidebar-link active" href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php">
                <span class="s-icon"><i class="ti ti-layout-dashboard"></i></span>
                Tableau de bord
            </a>

            <!-- Module Garages (Rayen) -->
            <div class="nav-section-label">Modules</div>
            <a class="sidebar-link" href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/BackOffice/back.php">
                <span class="s-icon"><i class="ti ti-building"></i></span>
                Garages & Services
                <span class="sidebar-badge" style="background:#6c63ff;">Rayen</span>
            </a>

            <!-- Module Véhicules (Ela) -->
            <a class="sidebar-link" href="#navVehicules" data-bs-toggle="collapse" aria-expanded="false">
                <span class="s-icon"><i class="ti ti-car"></i></span>
                Véhicules
                <span class="s-badge bg-danger-subtle text-danger">Ela</span>
            </a>
            <div class="collapse sidebar-collapse" id="navVehicules">
                <div class="sub-nav">
                    <a class="sidebar-link" href="Gestion_voitureBackend.php">Gestion des véhicules</a>
                    <a class="sidebar-link" href="Gestion_rendezVousBackend.php">Gestion des rendez-vous</a>
                </div>
            </div>

            <!-- Module Entretien (Asma) -->
            <a class="sidebar-link" href="#navEntretien" data-bs-toggle="collapse" aria-expanded="false">
                <span class="s-icon"><i class="ti ti-tools"></i></span>
                Entretien
                <span class="s-badge bg-info-subtle text-info">Asma</span>
            </a>
            <div class="collapse sidebar-collapse" id="navEntretien">
                <div class="sub-nav">
                    <a class="sidebar-link" href="#">Rendez-vous</a>
                    <a class="sidebar-link" href="#">Diagnostics</a>
                    <a class="sidebar-link" href="#">Historique</a>
                </div>
            </div>

            <!-- Module Vente de Pièces (Amen) -->
            <a class="sidebar-link" href="#navPieces" data-bs-toggle="collapse" aria-expanded="false">
                <span class="s-icon"><i class="ti ti-shopping-cart"></i></span>
                Vente de Pièces
                <span class="s-badge bg-warning-subtle text-warning">Amen</span>
            </a>
            <div class="collapse sidebar-collapse" id="navPieces">
                <div class="sub-nav">
                    <a class="sidebar-link" href="/Esprit-PI-2PREPA-2026-EntreAUtous/index.php?action=admin#fraud-section">Panel Admin</a>
                </div>
            </div>

            <!-- Module Messagerie (Mohamed) -->
            <a class="sidebar-link" href="#navMsg" data-bs-toggle="collapse" aria-expanded="false">
                <span class="s-icon"><i class="ti ti-message-circle"></i></span>
                Messagerie
                <span class="s-badge bg-primary-subtle text-primary">Mohamed</span>
                <span class="ms-1 badge bg-danger rounded-pill" style="font-size:9px;padding:2px 5px;"><?= $statMessages ?></span>
            </a>
            <div class="collapse sidebar-collapse" id="navMsg">
                <div class="sub-nav">
                    <a class="sidebar-link" href="#">Conversations</a>
                    <a class="sidebar-link" href="#">Messages signalés</a>
                </div>
            </div>

            <!-- Module Clients (Insaf) -->
            <a class="sidebar-link" href="#navClients" data-bs-toggle="collapse" aria-expanded="false">
                <span class="s-icon"><i class="ti ti-users"></i></span>
                Clients & Utilisateurs
                <span class="s-badge bg-secondary-subtle text-secondary">Insaf</span>
            </a>
            <div class="collapse sidebar-collapse show" id="navClients">
                <div class="sub-nav">
                    <a class="sidebar-link active" href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php">Liste des clients</a>
                    <a class="sidebar-link" href="#">Inscriptions récentes</a>
                    <a class="sidebar-link" href="#">Activité & Tracking</a>
                </div>
            </div>

            <!-- 👨‍🔧 Garagistes (Insaf / Admin) -->
            <a class="sidebar-link" href="#navGaragistes" data-bs-toggle="collapse" aria-expanded="<?= (($_GET['tab'] ?? '') === 'garagistes') ? 'true' : 'false' ?>">
                <span class="s-icon">🔧</span>
                Garagistes
                <?php if ($nbGaragistes > 0): ?>
                <span class="ms-auto badge bg-success rounded-pill" style="font-size:9px;"><?= $nbGaragistes ?></span>
                <?php endif; ?>
            </a>
            <div class="collapse sidebar-collapse <?= (($_GET['tab'] ?? '') === 'garagistes') ? 'show' : '' ?>" id="navGaragistes">
                <div class="sub-nav">
                    <a class="sidebar-link" href="admin.php?tab=garagistes#section-garagistes">Gérer les garagistes</a>
                </div>
            </div>

            <!-- Configuration -->
            <div class="nav-section-label">Système</div>
            <a class="sidebar-link" href="#">
                <span class="s-icon"><i class="ti ti-settings"></i></span>
                Configuration
            </a>
            <a class="sidebar-link" href="#">
                <span class="s-icon"><i class="ti ti-chart-bar"></i></span>
                Statistiques globales
            </a>

        </nav>

        <!-- Footer sidebar -->
        <div class="sidebar-footer">
            <div class="admin-info">
                <div class="admin-avatar">A</div>
                <div>
                    <div class="admin-name">Administrateur</div>
                    <div class="admin-role"><?= htmlspecialchars($_SESSION['email'] ?? 'admin@autout.tn') ?></div>
                </div>
                <a class="logout-btn" href="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/UserController.php?action=logout" title="Déconnexion">
                    <i class="ti ti-logout"></i>
                </a>
            </div>
        </div>
    </aside>

  <div class="main-area">
    <div class="page-content">
    <div id="db-wrapper">
<main id="page-content">
            <div class="header">
</div>

            <div class="container-fluid pt-10 pb-6">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-9">
                        
                        <!-- Bouton retour -->
                        <div class="mb-4">
                            <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/listeentretiens.php" 
                               class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M19 12H5M12 5l-7 7 7 7"/>
                                </svg>
                                Retour à la liste des entretiens
                            </a>
                        </div>

                        <div class="card border-0 mb-4 shadow-sm" style="background: linear-gradient(90deg, #23395b 0%, #4a90e2 100%); border-radius: 12px;">
                            <div class="card-body p-4 text-white">
                                <h3 class="fw-bold mb-1 text-white">📄 Nouvelle Facture</h3>
                                <p class="mb-0 opacity-75">Enregistrement des données de facturation.</p>
                            </div>
                        </div>

                        <div class="card shadow-sm border-0" style="border-radius: 12px;">
                            <div class="card-header bg-white border-bottom py-3">
                                <h5 class="mb-0 fw-bold text-dark">Informations de la Facture</h5>
                            </div>
                            <div class="card-body p-4">
                                <?php if (!empty($validationErrors)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert" id="validation-errors">
                                    <strong>Erreurs de validation :</strong>
                                    <ul class="mb-0 mt-2">
                                        <?php foreach ($validationErrors as $ve): ?>
                                            <li><?php echo htmlspecialchars($ve); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                                <?php endif; ?>
                                <form action="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/facture_action.php?action=create" method="POST">
                                    <div class="row g-4">
                                        <!-- id_facture is AUTO_INCREMENT in DB; do not provide it -->
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Référence Facture</label>
                                            <input type="text" class="form-control <?php echo FactureController::fieldInvalid($validationErrors, 'référence'); ?>" name="ref_facture" placeholder="Ex: FAC-001" value="<?php echo htmlspecialchars($old['ref_facture'] ?? ''); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Date d'émission</label>
                                            <input type="text" class="form-control <?php echo FactureController::fieldInvalid($validationErrors, 'date'); ?>" name="date_emission" value="<?php echo htmlspecialchars($old['date_emission'] ?? ''); ?>">
                                        </div>

                                        <!-- entretien passed as hidden field (foreign key) -->
                                        <input type="hidden" name="entretien" value="<?php echo htmlspecialchars($entId); ?>">

                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Montant HT</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control <?php echo FactureController::fieldInvalid($validationErrors, 'ht'); ?>" name="montant_ht" value="<?php echo htmlspecialchars($old['montant_ht'] ?? ''); ?>">
                                                <span class="input-group-text">TND</span>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Taux TVA (%)</label>
                                            <select class="form-select <?php echo FactureController::fieldInvalid($validationErrors, 'tva'); ?>" name="taux_tva">
                                                <option value="19" <?php echo (($old['taux_tva'] ?? '19') == '19') ? 'selected' : ''; ?>>19%</option>
                                                <option value="13" <?php echo (($old['taux_tva'] ?? '') == '13') ? 'selected' : ''; ?>>13%</option>
                                                <option value="7"  <?php echo (($old['taux_tva'] ?? '') == '7')  ? 'selected' : ''; ?>>7%</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label fw-semibold">Montant TTC</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control <?php echo FactureController::fieldInvalid($validationErrors, 'ttc'); ?>" name="montant_ttc" value="<?php echo htmlspecialchars($old['montant_ttc'] ?? ''); ?>">
                                                <span class="input-group-text">TND</span>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Mode de paiement</label>
                                            <select class="form-select <?php echo FactureController::fieldInvalid($validationErrors, 'mode'); ?>" name="mode_paiement">
                                                <option value="Espèces"       <?php echo (($old['mode_paiement'] ?? 'Espèces') === 'Espèces')       ? 'selected' : ''; ?>>Espèces</option>
                                                <option value="Carte Bancaire"<?php echo (($old['mode_paiement'] ?? '') === 'Carte Bancaire')        ? 'selected' : ''; ?>>Carte Bancaire</option>
                                                <option value="Chèque"        <?php echo (($old['mode_paiement'] ?? '') === 'Chèque')                ? 'selected' : ''; ?>>Chèque</option>
                                                <option value="Virement"      <?php echo (($old['mode_paiement'] ?? '') === 'Virement')              ? 'selected' : ''; ?>>Virement</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">État du paiement</label>
                                            <select class="form-select <?php echo FactureController::fieldInvalid($validationErrors, 'état'); ?>" name="etat_paiement">
                                                <option value="En attente"<?php echo (($old['etat_paiement'] ?? 'En attente') === 'En attente') ? 'selected' : ''; ?>>En attente</option>
                                                <option value="Payée"     <?php echo (($old['etat_paiement'] ?? '') === 'Payée')                ? 'selected' : ''; ?>>Payée</option>
                                                <option value="Annulée"   <?php echo (($old['etat_paiement'] ?? '') === 'Annulée')              ? 'selected' : ''; ?>>Annulée</option>
                                            </select>
                                        </div>

                                        <div class="col-12 mt-5 text-end border-top pt-4">
                                            <button type="reset" class="btn btn-outline-secondary px-4 me-2">Réinitialiser</button>
                                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">Enregistrer la Facture</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const alertErr = document.getElementById('validation-errors');
        if (alertErr) {
            alertErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
    </script>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js"></script>
<script src="/Esprit-PI-2PREPA-2026-EntreAUtous/assets/back/js/vendors/sidebarnav.js"></script>
</body>

</html>