<?php
/**
 * historique_entretien.php — VUE UNIQUEMENT
 * ─────────────────────────────────────────────────────────────
 * Responsabilité : afficher l'historique des entretiens et factures.
 * Les actions de restauration passent par entretien_action.php
 * et facture_action.php — AUCUN traitement BDD ici.
 */
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../controller/EntretienController.php';

$controller = new EntretienController($pdo);

// ── Toutes les données viennent du controller ──────────────
$viewData = $controller->getHistoriqueData($_GET);
extract($viewData);

// $actifsEnt, $supprEnt, $actifsFac, $supprFac,
// $message, $badges, $labels, $etatClasses
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Historique des Entretiens</title>
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
    .table tbody td, .table tbody th { color: #e2e8f0 !important; border-color: #334155 !important; }
    /* ── Lignes corbeille (supprimées) ── */
    .table-danger {
      --bs-table-bg: rgba(220,38,38,.12) !important;
      --bs-table-color: #fca5a5 !important;
      --bs-table-hover-bg: rgba(220,38,38,.22) !important;
      --bs-table-hover-color: #fca5a5 !important;
      --bs-table-striped-bg: rgba(220,38,38,.12) !important;
      --bs-table-striped-color: #fca5a5 !important;
      --bs-table-border-color: rgba(220,38,38,.2) !important;
      background-color: rgba(220,38,38,.12) !important;
      color: #fca5a5 !important;
    }
    .table-danger > td,
    .table-danger > th,
    .table-danger td,
    .table-danger th { color: #fca5a5 !important; background-color: transparent !important; border-color: rgba(220,38,38,.2) !important; }
    .table-danger .text-muted,
    .table-danger small { color: #f87171 !important; }
    .table-hover .table-danger:hover > td,
    .table-hover .table-danger:hover > th,
    .table-hover .table-danger:hover td,
    .table-hover .table-danger:hover th { color: #fca5a5 !important; background-color: rgba(220,38,38,.22) !important; }




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
<div>
<div id="content" class="position-relative h-100">
<div class="custom-container">

            <div class="d-flex justify-content-between align-items-center mb-4">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php" class="text-muted">Accueil</a></li>
                        <li class="breadcrumb-item active">Historique</li>
                    </ol>
                </nav>
                <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/listeentretiens.php" class="btn btn-outline-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" />
                    </svg>
                    Retour à la liste des entretiens
                </a>
            </div>

            <?php if ($message) echo $message; ?>

            <!-- Onglets -->
            <ul class="nav nav-tabs mb-5" id="histTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-ent-actifs">
                        <i class="ti ti-tools me-1"></i> Entretiens actifs
                        <span class="badge bg-primary ms-1"><?= count($actifsEnt) ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ent-suppr">
                        <i class="ti ti-trash me-1"></i> Entretiens supprimés
                        <span class="badge bg-danger ms-1"><?= count($supprEnt) ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fac-actifs">
                        <i class="ti ti-file-invoice me-1"></i> Factures actives
                        <span class="badge bg-success ms-1"><?= count($actifsFac) ?></span>
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-fac-suppr">
                        <i class="ti ti-trash me-1"></i> Factures supprimées
                        <span class="badge bg-danger ms-1"><?= count($supprFac) ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content">

                <!-- ── Tab : Entretiens actifs ──────────────────────── -->
                <div class="tab-pane fade show active" id="tab-ent-actifs">
                    <div class="card card-lg">
                        <div class="card-body">
                            <h5 class="mb-5">Entretiens actifs</h5>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" class="form-control hist-search" data-tab="ent-actifs" placeholder="Rechercher…">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                        <input type="text" class="form-control hist-date" data-tab="ent-actifs" placeholder="Date (AAAA-MM-JJ)">
                                    </div>
                                </div>
                                <div class="col-md-auto ms-auto">
                                    <button class="btn btn-sm btn-outline-secondary hist-reset" data-tab="ent-actifs">
                                        <i class="ti ti-x"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="table-ent-actifs">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>Matricule</th><th>Date</th><th>KM</th><th>Type</th><th>Statut</th><th>Prochaine échéance</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($actifsEnt)): foreach ($actifsEnt as $r): ?>
                                            <?php $bc = $badges[$r['statut']] ?? 'bg-secondary'; $lb = $labels[$r['statut']] ?? $r['statut']; ?>
                                            <tr data-search="<?= strtolower(htmlspecialchars($r['Matricule'] . ' ' . $r['type_intervention'])) ?>"
                                                data-date="<?= $r['date_entretien'] ?>">
                                                <td><span class="text-muted small">#<?= $r['id_entretien'] ?></span></td>
                                                <td><span class="badge" style="background:#ffc800;color:#212529;"><?= htmlspecialchars($r['Matricule']) ?></span></td>
                                                <td><?= $r['date_entretien'] ?></td>
                                                <td><?= number_format($r['kilometrage'], 0, ',', ' ') ?> km</td>
                                                <td><?= htmlspecialchars($r['type_intervention'] ?? '—') ?></td>
                                                <td><span class="badge <?= $bc ?>"><?= $lb ?></span></td>
                                                <td><?= $r['prochaine_echeance'] ?: '—' ?></td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="7" class="text-center text-muted py-4">Aucun entretien actif.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Tab : Entretiens supprimés ──────────────────── -->
                <div class="tab-pane fade" id="tab-ent-suppr">
                    <div class="card card-lg">
                        <div class="card-body">
                            <h5 class="mb-5 text-danger"><i class="ti ti-trash me-2"></i>Entretiens supprimés (corbeille)</h5>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" class="form-control hist-search" data-tab="ent-suppr" placeholder="Rechercher…">
                                    </div>
                                </div>
                                <div class="col-md-auto ms-auto">
                                    <button class="btn btn-sm btn-outline-secondary hist-reset" data-tab="ent-suppr">
                                        <i class="ti ti-x"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="table-ent-suppr">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>Matricule</th><th>Date</th><th>KM</th><th>Type</th><th>Statut</th><th>Supprimé le</th><th>Action</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($supprEnt)): foreach ($supprEnt as $r): ?>
                                            <?php $bc = $badges[$r['statut']] ?? 'bg-secondary'; $lb = $labels[$r['statut']] ?? $r['statut']; ?>
                                            <tr class="table-danger"
                                                data-search="<?= strtolower(htmlspecialchars($r['Matricule'] . ' ' . $r['type_intervention'])) ?>">
                                                <td><span class="text-muted small">#<?= $r['id_entretien'] ?></span></td>
                                                <td><span class="badge" style="background:#ffc800;color:#212529;"><?= htmlspecialchars($r['Matricule']) ?></span></td>
                                                <td><?= $r['date_entretien'] ?></td>
                                                <td><?= number_format($r['kilometrage'], 0, ',', ' ') ?> km</td>
                                                <td><?= htmlspecialchars($r['type_intervention'] ?? '—') ?></td>
                                                <td><span class="badge <?= $bc ?>"><?= $lb ?></span></td>
                                                <td><small class="text-muted"><?= $r['deleted_at'] ?></small></td>
                                                <td>
                                                    <form method="POST" action="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/entretien_action.php?action=restore_ent"
                                                          onsubmit="return confirm('Restaurer cet entretien ?');" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $r['id_entretien'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                <path d="M9 11l-4 4l4 4m-4-4h11a4 4 0 0 0 0 -8h-1"/>
                                                            </svg>
                                                            Restaurer
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="8" class="text-center text-muted py-4">Aucun entretien supprimé.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Tab : Factures actives ───────────────────────── -->
                <div class="tab-pane fade" id="tab-fac-actifs">
                    <div class="card card-lg">
                        <div class="card-body">
                            <h5 class="mb-5">Factures actives</h5>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" class="form-control hist-search" data-tab="fac-actifs" placeholder="Référence / matricule…">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-calendar"></i></span>
                                        <input type="text" class="form-control hist-date" data-tab="fac-actifs" placeholder="Date (AAAA-MM-JJ)">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-arrows-sort"></i></span>
                                        <select class="form-select hist-sort" data-tab="fac-actifs">
                                            <option value="none">Tri par défaut</option>
                                            <option value="price-asc">Prix croissant</option>
                                            <option value="price-desc">Prix décroissant</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-auto ms-auto">
                                    <button class="btn btn-sm btn-outline-secondary hist-reset" data-tab="fac-actifs">
                                        <i class="ti ti-x"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="table-fac-actifs">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>Référence</th><th>Matricule</th><th>Date</th><th>HT</th><th>TVA</th><th>TTC</th><th>Mode</th><th>État</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($actifsFac)): foreach ($actifsFac as $f): ?>
                                            <?php $ep = $f['etat_paiement']; ?>
                                            <tr data-search="<?= strtolower(htmlspecialchars($f['ref_facture'] . ' ' . ($f['Matricule'] ?? ''))) ?>"
                                                data-date="<?= $f['date_emission'] ?>"
                                                data-price="<?= $f['montant_ttc'] ?>">
                                                <td><span class="text-muted small">#<?= $f['id_facture'] ?></span></td>
                                                <td><strong><?= htmlspecialchars($f['ref_facture']) ?></strong></td>
                                                <td><span class="badge" style="background:#ffc800;color:#212529;"><?= htmlspecialchars($f['Matricule'] ?? '—') ?></span></td>
                                                <td><?= $f['date_emission'] ?></td>
                                                <td><?= number_format($f['montant_ht'], 3, '.', ' ') ?> TND</td>
                                                <td><?= $f['taux_tva'] ?>%</td>
                                                <td><strong><?= number_format($f['montant_ttc'], 3, '.', ' ') ?> TND</strong></td>
                                                <td><?= htmlspecialchars($f['mode_paiement']) ?></td>
                                                <td><span class="badge <?= $etatClasses[$ep] ?? 'bg-secondary' ?>"><?= htmlspecialchars($ep) ?></span></td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="9" class="text-center text-muted py-4">Aucune facture active.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── Tab : Factures supprimées ────────────────────── -->
                <div class="tab-pane fade" id="tab-fac-suppr">
                    <div class="card card-lg">
                        <div class="card-body">
                            <h5 class="mb-5 text-danger"><i class="ti ti-trash me-2"></i>Factures supprimées (corbeille)</h5>
                            <div class="row g-2 mb-3 align-items-center">
                                <div class="col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text"><i class="ti ti-search"></i></span>
                                        <input type="text" class="form-control hist-search" data-tab="fac-suppr" placeholder="Référence / matricule…">
                                    </div>
                                </div>
                                <div class="col-md-auto ms-auto">
                                    <button class="btn btn-sm btn-outline-secondary hist-reset" data-tab="fac-suppr">
                                        <i class="ti ti-x"></i> Réinitialiser
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="table-fac-suppr">
                                    <thead class="table-light">
                                        <tr><th>#</th><th>Référence</th><th>Matricule</th><th>Date</th><th>TTC</th><th>État</th><th>Supprimée le</th><th>Action</th></tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($supprFac)): foreach ($supprFac as $f): ?>
                                            <?php $ep = $f['etat_paiement']; ?>
                                            <tr class="table-danger">
                                                <td><span class="text-muted small">#<?= $f['id_facture'] ?></span></td>
                                                <td><strong><?= htmlspecialchars($f['ref_facture']) ?></strong></td>
                                                <td><span class="badge" style="background:#ffc800;color:#212529;"><?= htmlspecialchars($f['Matricule'] ?? '—') ?></span></td>
                                                <td><?= $f['date_emission'] ?></td>
                                                <td><strong><?= number_format($f['montant_ttc'], 3, '.', ' ') ?> TND</strong></td>
                                                <td><span class="badge <?= $etatClasses[$ep] ?? 'bg-secondary' ?>"><?= htmlspecialchars($ep) ?></span></td>
                                                <td><small class="text-muted"><?= $f['deleted_at'] ?></small></td>
                                                <td>
                                                    <form method="POST" action="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/facture_action.php?action=restore"
                                                          onsubmit="return confirm('Restaurer cette facture ?');" class="d-inline">
                                                        <input type="hidden" name="id" value="<?= $f['id_facture'] ?>">
                                                        <button type="submit" class="btn btn-sm btn-success d-flex align-items-center gap-1">
                                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                                <path d="M9 11l-4 4l4 4m-4-4h11a4 4 0 0 0 0 -8h-1"/>
                                                            </svg>
                                                            Restaurer
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; else: ?>
                                            <tr><td colspan="8" class="text-center text-muted py-4">Aucune facture supprimée.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- end tab-content -->
        </div><!-- end custom-container -->
    </div><!-- end #content -->
</div>
<script>
(function () {
    function parseDate(str) {
        str = (str || '').trim();
        if (!str) return null;
        if (/^\d{4}-\d{2}-\d{2}$/.test(str)) return str;
        const m = str.match(/^(\d{2})[\/\-](\d{2})[\/\-](\d{4})$/);
        return m ? m[3] + '-' + m[2] + '-' + m[1] : null;
    }

    function applyFilter(tabId) {
        const table = document.getElementById('table-' + tabId);
        if (!table) return;
        const tbody   = table.querySelector('tbody');
        const rows    = Array.from(tbody.querySelectorAll('tr[data-search]'));
        const searchEl = document.querySelector('.hist-search[data-tab="' + tabId + '"]');
        const dateEl   = document.querySelector('.hist-date[data-tab="'   + tabId + '"]');
        const sortEl   = document.querySelector('.hist-sort[data-tab="'   + tabId + '"]');
        const query  = searchEl ? searchEl.value.trim().toLowerCase() : '';
        const qDate  = parseDate(dateEl ? dateEl.value : '');
        const sortBy = sortEl ? sortEl.value : 'none';

        rows.forEach(row => {
            const ok = (!query || row.dataset.search.includes(query))
                    && (!qDate  || row.dataset.date === qDate);
            row.style.display = ok ? '' : 'none';
        });

        if (sortBy !== 'none') {
            const visible = rows.filter(r => r.style.display !== 'none');
            visible.sort((a, b) => {
                const pa = parseFloat(a.dataset.price || 0);
                const pb = parseFloat(b.dataset.price || 0);
                return sortBy === 'price-asc' ? pa - pb : pb - pa;
            });
            visible.forEach(r => tbody.appendChild(r));
        }

        const noResId = 'no-res-' + tabId;
        let noResEl = document.getElementById(noResId);
        const hasVisible = rows.some(r => r.style.display !== 'none');
        if (!hasVisible) {
            if (!noResEl) {
                noResEl = document.createElement('tr');
                noResEl.id = noResId;
                noResEl.innerHTML = '<td colspan="10" class="text-center text-muted py-4"><i class="ti ti-search me-2"></i>Aucun résultat.</td>';
                tbody.appendChild(noResEl);
            }
        } else if (noResEl) {
            noResEl.remove();
        }
    }

    ['ent-actifs', 'ent-suppr', 'fac-actifs', 'fac-suppr'].forEach(tabId => {
        document.querySelectorAll('.hist-search[data-tab="' + tabId + '"]').forEach(el => el.addEventListener('input',  () => applyFilter(tabId)));
        document.querySelectorAll('.hist-date[data-tab="'   + tabId + '"]').forEach(el => el.addEventListener('input',  () => applyFilter(tabId)));
        document.querySelectorAll('.hist-sort[data-tab="'   + tabId + '"]').forEach(el => el.addEventListener('change', () => applyFilter(tabId)));
        document.querySelectorAll('.hist-reset[data-tab="'  + tabId + '"]').forEach(el => el.addEventListener('click', () => {
            const s = document.querySelector('.hist-search[data-tab="' + tabId + '"]');
            const d = document.querySelector('.hist-date[data-tab="'   + tabId + '"]');
            const o = document.querySelector('.hist-sort[data-tab="'   + tabId + '"]');
            if (s) s.value = '';
            if (d) d.value = '';
            if (o) o.value = 'none';
            applyFilter(tabId);
        }));
    });
})();
</script>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js"></script>
<script src="/Esprit-PI-2PREPA-2026-EntreAUtous/assets/back/js/vendors/sidebarnav.js"></script>
</body>
</html>