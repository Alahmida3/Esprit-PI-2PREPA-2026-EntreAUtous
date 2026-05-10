<?php
/* ═══════════════════════════════════════════════════════════
 *  VIEW ONLY — aucune logique métier ici.
 *  Toutes les actions (delete, restore…) passent par
 *  controller/entretien_action.php
 * ═══════════════════════════════════════════════════════════ */
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../controller/EntretienController.php';

if (!$pdo) die("Erreur de connexion à la base de données.");

$controller = new EntretienController($pdo);

// ── UNE seule ligne de logique : tout vient du controller ──
$viewData = $controller->getListeBackData($_GET, 5);
extract($viewData);

// $list, $stats, $filterStatut, $filterDate, $filterSort,
// $pageCourante, $totalPages, $totalItems,
// $facturesParEntretien, $message, $badges, $labels

// Calcul de l'offset uniquement pour l'affichage de la plage (ex: 1–5 sur 12)
$parPage = 5;
$offset  = ($pageCourante - 1) * $parPage;
?>


<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Liste des Entretiens - Dasher</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700;800&display=swap" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css" />
  <link rel="stylesheet" href="/Esprit-PI-2PREPA-2026-EntreAUtous/assets/back/css/theme.css" />
  <script>
    if (localStorage.getItem('sidebarExpanded') === 'false') {
      document.documentElement.classList.add('collapsed');
      document.documentElement.classList.remove('expanded');
    } else {
      document.documentElement.classList.remove('collapsed');
      document.documentElement.classList.add('expanded');
    }
  </script>
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
    .page-content, .custom-container { padding: 24px; background: #0f172a !important; }
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
    .table { color: #e2e8f0 !important; border-color: #334155 !important; }
    .table thead th { background: #0f172a !important; color: #94a3b8 !important; border-color: #334155 !important; }
    .table tbody tr { border-color: #334155 !important; }
    .table tbody tr:hover { background: rgba(99,102,241,.08) !important; }
    .table tbody td, .table tbody th { color: #e2e8f0 !important; border-color: #334155 !important; }
    .table-light { background: #0f172a !important; color: #94a3b8 !important; }
    .alert-danger { background: #450a0a !important; border-color: #dc2626 !important; color: #fca5a5 !important; }
    .alert-success { background: #052e16 !important; border-color: #16a34a !important; color: #86efac !important; }
    h5, h6, small { color: #e2e8f0 !important; }
    .input-group-text { background: #1e293b !important; border-color: #334155 !important; color: #94a3b8 !important; }
    .icon-shape { background: #334155 !important; }
    .modal-content { background: #1e293b !important; border-color: #334155 !important; color: #e2e8f0 !important; }
    .modal-header { border-color: #334155 !important; color: #e2e8f0 !important; }
    .modal-title { color: #e2e8f0 !important; }
    .modal-content .bg-light { background: #0f172a !important; border: 1px solid #334155 !important; }
    .modal-content .bg-light small, .modal-content .bg-light .text-muted { color: #94a3b8 !important; }
    .modal-content .bg-light strong { color: #e2e8f0 !important; }
    .modal-content .btn-close { filter: invert(1) grayscale(1) brightness(2); }
    .modal-footer { border-color: #334155 !important; }
    .page-link { background: #1e293b !important; border-color: #334155 !important; color: #e2e8f0 !important; }
    .page-item.active .page-link { background: #6366f1 !important; border-color: #6366f1 !important; }
    .page-item.disabled .page-link { background: #0f172a !important; color: #475569 !important; }
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
      <div id="content" class="position-relative h-100">

      <div class="custom-container">

        <!-- Breadcrumb -->
        <div class="row mb-4">
          <div class="col-12">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php" class="text-muted">Accueil</a></li>
                <li class="breadcrumb-item active" aria-current="page">Entretiens</li>
              </ol>
            </nav>
          </div>
        </div>

        <?php if ($message) echo $message; ?>

        <!-- ══ Statistiques par statut ══════════════════════════ -->
        <div class="row g-3 mb-5">
          <div class="col-6 col-md-3">
            <a href="?statut=planifie" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #ffc107 !important;">
                <div class="fs-2 fw-bold text-warning"><?php echo $stats['planifie']; ?></div>
                <small class="text-muted">Planifiés</small>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="?statut=en_cours" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #0dcaf0 !important;">
                <div class="fs-2 fw-bold text-info"><?php echo $stats['en_cours']; ?></div>
                <small class="text-muted">En cours</small>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="?statut=termine" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #198754 !important;">
                <div class="fs-2 fw-bold text-success"><?php echo $stats['termine']; ?></div>
                <small class="text-muted">Terminés</small>
              </div>
            </a>
          </div>
          <div class="col-6 col-md-3">
            <a href="?statut=annule" class="text-decoration-none">
              <div class="card border-0 shadow-sm text-center p-3" style="border-left:4px solid #dc3545 !important;">
                <div class="fs-2 fw-bold text-danger"><?php echo $stats['annule']; ?></div>
                <small class="text-muted">Annulés</small>
              </div>
            </a>
          </div>
        </div>

        <div class="row mb-6 g-6">
          <div class="col-12">
            <div class="card card-lg">
              <div class="card-body">

                <!-- Titre + Boutons -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                  <div class="d-flex align-items-center gap-3">
                    <div class="icon-shape icon-lg rounded-circle bg-primary-darker text-primary-lighter">
                      <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4"/>
                        <path d="M14.5 5.5l4 4"/><path d="M12 8l-5 -5l-4 4l5 5"/>
                        <path d="M7 8l-1.5 1.5"/><path d="M16 12l5 5l-4 4l-5 -5"/><path d="M16 17l-1.5 1.5"/>
                      </svg>
                    </div>
                    <div>
                      <h5 class="mb-0">Liste des Entretiens</h5>
                      <small class="text-muted">
                        <?php echo $stats['total']; ?> entretien(s) au total
                        <?php if ($filterStatut || $filterDate): ?> — <span class="text-primary fw-semibold">filtre actif</span><?php endif; ?>
                      </small>
                    </div>
                  </div>
                  <div class="d-flex gap-2">
                    <button type="button" id="btn-analyse-ia" class="btn btn-dark d-flex align-items-center gap-2"
                            style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);border:1px solid #e94560;">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="#e94560" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M9.5 2a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/>
                        <path d="M14.5 17a2.5 2.5 0 1 1 0 5 2.5 2.5 0 0 1 0-5z"/>
                        <path d="M4.5 14.5a3 3 0 1 1 0 6 3 3 0 0 1 0-6z"/>
                        <path d="M14.5 4.5l-10 10M9.5 4.5l5 3-3 3.5"/>
                      </svg>
                      <span style="color:#e94560;font-weight:600;">Analyse IA</span>
                    </button>
                    <a href="historique_entretien.php" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                      <i class="ti ti-history"></i> Historique
                    </a>
                    <a href="ajouter_entretien.php" class="btn btn-primary d-flex align-items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                        <path d="M12 5l0 14"/><path d="M5 12l14 0"/>
                      </svg>
                      Ajouter un entretien
                    </a>
                  </div>
                </div>

                <!-- ══ Filtres ══════════════════════════════════════ -->
                <form method="GET" action="listeentretiens.php" class="row g-3 mb-4 align-items-end">
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Statut</label>
                    <select class="form-select" name="statut">
                      <option value="">Tous les statuts</option>
                      <option value="planifie" <?php echo $filterStatut === 'planifie' ? 'selected' : ''; ?>>Planifié</option>
                      <option value="en_cours" <?php echo $filterStatut === 'en_cours' ? 'selected' : ''; ?>>En cours</option>
                      <option value="termine"  <?php echo $filterStatut === 'termine'  ? 'selected' : ''; ?>>Terminé</option>
                      <option value="annule"   <?php echo $filterStatut === 'annule'   ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Date d'entretien</label>
                    <input type="text" class="form-control" name="date" value="<?php echo htmlspecialchars($filterDate); ?>">
                  </div>
                  <div class="col-md-3">
                    <label class="form-label small fw-semibold text-muted">Trier par date</label>
                    <select class="form-select" name="sort">
                      <option value="recent" <?php echo $filterSort === 'recent' ? 'selected' : ''; ?>>Plus récent</option>
                      <option value="oldest" <?php echo $filterSort === 'oldest' ? 'selected' : ''; ?>>Plus ancien</option>
                    </select>
                  </div>
                  <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-fill">Filtrer</button>
                    <?php if ($filterStatut || $filterDate || $filterSort !== 'recent'): ?>
                      <a href="listeentretiens.php" class="btn btn-outline-secondary" title="Réinitialiser">✕</a>
                    <?php endif; ?>
                  </div>
                </form>

                <!-- Raccourcis rapides par statut -->
                <div class="d-flex gap-2 mb-4 flex-wrap">
                  <a href="listeentretiens.php" class="btn btn-sm <?php echo !$filterStatut ? 'btn-dark' : 'btn-outline-secondary'; ?>">
                    Tous <span class="badge bg-secondary ms-1"><?php echo $stats['total']; ?></span>
                  </a>
                  <a href="?statut=planifie" class="btn btn-sm <?php echo $filterStatut === 'planifie' ? 'btn-warning' : 'btn-outline-warning'; ?>">
                    Planifiés <span class="badge ms-1"><?php echo $stats['planifie']; ?></span>
                  </a>
                  <a href="?statut=en_cours" class="btn btn-sm <?php echo $filterStatut === 'en_cours' ? 'btn-info' : 'btn-outline-info'; ?>">
                    En cours <span class="badge ms-1"><?php echo $stats['en_cours']; ?></span>
                  </a>
                  <a href="?statut=termine" class="btn btn-sm <?php echo $filterStatut === 'termine' ? 'btn-success' : 'btn-outline-success'; ?>">
                    Terminés <span class="badge ms-1"><?php echo $stats['termine']; ?></span>
                  </a>
                  <a href="?statut=annule" class="btn btn-sm <?php echo $filterStatut === 'annule' ? 'btn-danger' : 'btn-outline-danger'; ?>">
                    Annulés <span class="badge ms-1"><?php echo $stats['annule']; ?></span>
                  </a>
                </div>

                <!-- ══ Tableau ══════════════════════════════════════ -->
                <div class="table-responsive">
                  <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Matricule</th>
                        <th>Date entretien</th>
                        <th>Kilométrage</th>
                        <th>Type intervention</th>
                        <th>Statut</th>
                        <th>Prochaine échéance</th>
                        <th>KM prochain</th>
                        <th class="text-end">Actions</th>
                      </tr>
                    </thead>
                    <tbody id="entretien-tbody">
                      <?php if (!empty($list)): ?>
                        <?php foreach ($list as $row): ?>
                          <?php
                            $statut            = $row['statut'];
                            $badgeClass        = $badges[$statut] ?? 'bg-secondary';
                            $label             = $labels[$statut] ?? $statut;
                            $facturesEntretien = $facturesParEntretien[$row['id_entretien']] ?? [];
                            $aFacture          = !empty($facturesEntretien);
                            $facture           = $aFacture ? $facturesEntretien[0] : null;
                          ?>
                          <tr data-row="1">
                            <td><?php echo htmlspecialchars($row['Matricule']); ?></td>
                            <td><?php echo htmlspecialchars($row['date_entretien']); ?></td>
                            <td class="col-km" data-km="<?php echo (int)$row['kilometrage']; ?>"><?php echo number_format($row['kilometrage'], 0, ',', ' '); ?> km</td>
                            <td class="col-type" data-type="<?php echo htmlspecialchars(strtolower(trim($row['type_intervention'] ?? ''))); ?>"><?php echo htmlspecialchars($row['type_intervention'] ?? ''); ?></td>
                            <td><span class="badge <?php echo $badgeClass; ?>"><?php echo $label; ?></span></td>
                            <td><?php echo !empty($row['prochaine_echeance']) ? htmlspecialchars($row['prochaine_echeance']) : '—'; ?></td>
                            <td><?php echo !empty($row['km_prochain']) ? number_format($row['km_prochain'], 0, ',', ' ') . ' km' : '—'; ?></td>
                            <td class="text-end">
                              <div class="d-flex justify-content-end gap-2 flex-wrap">

                                <!-- Modifier -->
                                <a href="modifier_entretien.php?id=<?php echo $row['id_entretien']; ?>"
                                  class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1">
                                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                    <path d="M7 7h-1a2 2 0 0 0 -2 2v9a2 2 0 0 0 2 2h9a2 2 0 0 0 2 -2v-1"/>
                                    <path d="M20.385 6.585a2.1 2.1 0 0 0 -2.97 -2.97l-8.415 8.385v3h3l8.385 -8.415z"/>
                                    <path d="M16 5l3 3"/>
                                  </svg>
                                  Modifier
                                </a>

                                <!-- Facture -->
                                <?php if ($aFacture): ?>
                                  <button type="button"
                                    class="btn btn-sm btn-outline-success d-flex align-items-center gap-1 btn-voir-facture"
                                    data-id-facture="<?php echo $facture['id_facture']; ?>"
                                    data-ref="<?php echo htmlspecialchars($facture['ref_facture']); ?>"
                                    data-date="<?php echo htmlspecialchars($facture['date_emission']); ?>"
                                    data-ht="<?php echo $facture['montant_ht']; ?>"
                                    data-tva="<?php echo $facture['taux_tva']; ?>"
                                    data-ttc="<?php echo $facture['montant_ttc']; ?>"
                                    data-mode="<?php echo htmlspecialchars($facture['mode_paiement']); ?>"
                                    data-etat="<?php echo htmlspecialchars($facture['etat_paiement']); ?>"
                                    data-entretien="<?php echo $row['id_entretien']; ?>">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                      <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                      <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                      <path d="M9 15l2 2l4 -4"/>
                                    </svg>
                                    Voir facture
                                  </button>
                                <?php else: ?>
                                  <a href="ajouter_facture.php?entretien=<?php echo $row['id_entretien']; ?>"
                                    class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                      <path d="M14 3v4a1 1 0 0 0 1 1h4"/>
                                      <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2z"/>
                                      <path d="M12 11l0 4"/><path d="M10 13l4 0"/>
                                    </svg>
                                    Ajouter facture
                                  </a>
                                <?php endif; ?>

                                <!-- Supprimer -->
                                <form method="POST" action="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/entretien_action.php?action=delete"
                                  onsubmit="return confirm('Supprimer cet entretien ?');" class="d-inline">
                                  <input type="hidden" name="id" value="<?php echo $row['id_entretien']; ?>">
                                  <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                      <path d="M4 7l16 0"/><path d="M10 11l0 6"/><path d="M14 11l0 6"/>
                                      <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                      <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                    </svg>
                                    Supprimer
                                  </button>
                                </form>

                              </div>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      <?php else: ?>
                        <tr>
                          <td colspan="8" class="text-center text-muted py-5">
                            <?php if ($filterStatut || $filterDate): ?>
                              Aucun entretien ne correspond aux filtres sélectionnés.<br>
                              <a href="listeentretiens.php" class="btn btn-outline-secondary btn-sm mt-3">Voir tous</a>
                            <?php else: ?>
                              Aucun entretien trouvé.<br>
                              <a href="ajouter_entretien.php" class="btn btn-primary btn-sm mt-3">Ajouter le premier</a>
                            <?php endif; ?>
                          </td>
                        </tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>

                <!-- ══ Pagination ══════════════════════════════════ -->
                <?php if ($totalPages > 1): ?>
                <nav class="mt-4 pt-3 border-top d-flex justify-content-between align-items-center">
                  <small class="text-muted">
                    <?php echo $offset + 1; ?>–<?php echo min($offset + $parPage, $totalItems); ?>
                    sur <?php echo $totalItems; ?> entretien(s)
                    <?php if ($filterStatut || $filterDate): ?>(filtrés)<?php endif; ?>
                  </small>
                  <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?php echo $pageCourante <= 1 ? 'disabled' : ''; ?>">
                      <a class="page-link" href="<?php echo EntretienController::paginationUrl($pageCourante - 1, $filterStatut, $filterDate, $filterSort); ?>">‹</a>
                    </li>
                    <?php
                    $debut = max(1, $pageCourante - 2);
                    $fin   = min($totalPages, $pageCourante + 2);
                    if ($debut > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif;
                    for ($p = $debut; $p <= $fin; $p++): ?>
                      <li class="page-item <?php echo $p === $pageCourante ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo EntretienController::paginationUrl($p, $filterStatut, $filterDate, $filterSort); ?>"><?php echo $p; ?></a>
                      </li>
                    <?php endfor;
                    if ($fin < $totalPages): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                    <li class="page-item <?php echo $pageCourante >= $totalPages ? 'disabled' : ''; ?>">
                      <a class="page-link" href="<?php echo EntretienController::paginationUrl($pageCourante + 1, $filterStatut, $filterDate, $filterSort); ?>">›</a>
                    </li>
                  </ul>
                </nav>
                <?php else: ?>
                <div class="mt-4 pt-3 border-top">
                  <small class="text-muted"><?php echo $totalItems; ?> entretien(s) au total</small>
                </div>
                <?php endif; ?>

              </div>
            </div>
          </div>
        </div>

      </div><!-- end custom-container -->
    </div><!-- end #content -->
  </div>

  <!-- ===== MODAL Facture ===== -->
  <div class="modal fade" id="modalVoirFacture" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title">Détails de la Facture</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body px-5 py-4">
          <div id="mode-consultation">
            <div class="row g-4 mb-4">
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Référence</small><strong id="view-ref"></strong></div></div>
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Date d'émission</small><strong id="view-date"></strong></div></div>
              <div class="col-md-4"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Montant HT</small><strong id="view-ht"></strong></div></div>
              <div class="col-md-4"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Taux TVA</small><strong id="view-tva"></strong></div></div>
              <div class="col-md-4"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Montant TTC</small><strong id="view-ttc" class="text-success"></strong></div></div>
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">Mode paiement</small><strong id="view-mode"></strong></div></div>
              <div class="col-md-6"><div class="bg-light rounded-3 p-4"><small class="text-muted d-block mb-1">État paiement</small><strong id="view-etat"></strong></div></div>
            </div>
          </div>
          <div id="mode-modification" style="display:none;">
            <form id="form-modifier-facture">
              <input type="hidden" id="edit-id-facture" name="id_facture">
              <input type="hidden" id="edit-entretien"  name="entretien">
              <div class="row g-3">
                <div class="col-md-6"><label class="form-label fw-medium">Référence</label><input type="text" class="form-control" id="edit-ref" name="ref_facture"></div>
                <div class="col-md-6"><label class="form-label fw-medium">Date d'émission</label><input type="text" class="form-control" id="edit-date" name="date_emission"></div>
                <div class="col-md-4"><label class="form-label fw-medium">Montant HT</label><input type="text" class="form-control" id="edit-ht" name="montant_ht"></div>
                <div class="col-md-4"><label class="form-label fw-medium">Taux TVA</label>
                  <select class="form-select" id="edit-tva" name="taux_tva">
                    <option value="19">19%</option><option value="13">13%</option><option value="7">7%</option>
                  </select>
                </div>
                <div class="col-md-4"><label class="form-label fw-medium">Montant TTC</label><input type="text" class="form-control" id="edit-ttc" name="montant_ttc"></div>
                <div class="col-md-6"><label class="form-label fw-medium">Mode paiement</label>
                  <select class="form-select" id="edit-mode" name="mode_paiement">
                    <option>Espèces</option><option>Carte Bancaire</option><option>Chèque</option><option>Virement</option>
                  </select>
                </div>
                <div class="col-md-6"><label class="form-label fw-medium">État paiement</label>
                  <select class="form-select" id="edit-etat" name="etat_paiement">
                    <option>En attente</option><option>Payée</option><option>Annulée</option>
                  </select>
                </div>
              </div>
              <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Sauvegarder</button>
                <button type="button" class="btn btn-outline-secondary" id="btn-annuler-modif">Annuler</button>
              </div>
            </form>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0 justify-content-between">
          <a href="listeentretiens.php" class="btn btn-outline-secondary">← Retour à la liste</a>
          <div class="d-flex gap-2" id="btns-consultation">
            <button type="button" class="btn btn-warning" id="btn-ouvrir-modif">Modifier</button>
            <button type="button" class="btn btn-danger"  id="btn-supprimer-facture">Supprimer facture</button>
          </div>
        </div>
      </div>
    </div>
  </div>


  <!-- ===== MODAL ANALYSE IA (Claude API) ===== -->
  <div class="modal fade" id="modalAnalyseIA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content" style="background:#0d1117;border:1px solid #30363d;border-radius:16px;color:#e6edf3;">

        <div class="modal-header border-0 pb-0 pt-4 px-4">
          <div class="d-flex align-items-center gap-3">
            <div style="width:42px;height:42px;border-radius:10px;background:linear-gradient(135deg,#e94560,#c23152);display:flex;align-items:center;justify-content:center;">
              <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                stroke="#fff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 2a1 1 0 0 1 .993 .883l.007 .117v1a1 1 0 0 1 -1.993 .117l-.007 -.117v-1a1 1 0 0 1 1 -1z"/>
                <path d="M3.5 9.5a8.5 8.5 0 1 0 14.357 -4.57"/>
                <path d="M12 12m-3 0a3 3 0 1 0 6 0a3 3 0 1 0 -6 0"/>
              </svg>
            </div>
            <div>
              <h5 class="modal-title mb-0" style="color:#e6edf3;font-weight:700;font-size:1.2rem;">
                Analyse IA — Tableau de bord écologique
              </h5>
              <small style="color:#8b949e;">Propulsé par Claude (Anthropic)</small>
            </div>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body px-4 pb-4 pt-3">

          <!-- État : chargement -->
          <div id="ia-loading" style="display:none;text-align:center;padding:60px 0;">
            <div class="spinner-border" style="color:#e94560;width:3rem;height:3rem;" role="status"></div>
            <p style="color:#8b949e;margin-top:16px;font-size:.9rem;">Claude analyse vos données…</p>
          </div>

          <!-- État : erreur -->
          <div id="ia-error" style="display:none;background:rgba(233,69,96,.1);border:1px solid rgba(233,69,96,.4);
               border-radius:10px;padding:20px;color:#e94560;font-size:.9rem;"></div>

          <!-- État : résultats -->
          <div id="ia-result" style="display:none;">

            <!-- KPI cards -->
            <div class="row g-3 mb-4" id="ia-scores"></div>

            <!-- Répartition -->
            <div class="row g-3 mb-4">
              <div class="col-lg-6">
                <div style="background:#161b22;border:1px solid #30363d;border-radius:12px;padding:20px;">
                  <p style="color:#8b949e;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">
                    Répartition des interventions
                  </p>
                  <div id="ia-types-chart"></div>
                </div>
              </div>
              <div class="col-lg-6">
                <div style="background:#161b22;border:1px solid #30363d;border-radius:12px;padding:20px;">
                  <p style="color:#8b949e;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:14px;">
                    Kilométrage moyen par type
                  </p>
                  <div id="ia-km-chart"></div>
                </div>
              </div>
            </div>

            <!-- Recommandations IA -->
            <div style="background:#161b22;border:1px solid #30363d;border-radius:12px;padding:20px;">
              <p style="color:#8b949e;font-size:.7rem;text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;">
                Recommandations intelligentes — Claude
              </p>
              <div id="ia-recommandations"
                   style="color:#c9d1d9;font-size:.88rem;line-height:1.8;white-space:pre-wrap;"></div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  (function () {

    /* ── Scraping DOM ─────────────────────────────────────────── */
    function scrapeTableau() {
      const rows = document.querySelectorAll('#entretien-tbody tr[data-row]');
      const data = [];
      rows.forEach(tr => {
        const typeEl = tr.querySelector('.col-type');
        const kmEl   = tr.querySelector('.col-km');
        if (!typeEl || !kmEl) return;
        const type = (typeEl.dataset.type || '').trim();
        const km   = parseInt(kmEl.dataset.km, 10) || 0;
        if (type) data.push({ type, km });
      });
      return data;
    }

    /* ── Stats locales ───────────────────────────────────────── */
    function computeStats(data) {
      const cats = {};
      data.forEach(({ type, km }) => {
        const key = type.toLowerCase();
        if (!cats[key]) cats[key] = { count: 0, kmTotal: 0 };
        cats[key].count++;
        cats[key].kmTotal += km;
      });
      return cats;
    }

    /* ── Graphiques à barres ─────────────────────────────────── */
    const BAR_COLORS = ['#e94560','#1f6feb','#238636','#d29922','#8957e5','#0dcaf0','#fd7e14','#20c997'];
    function barChart(containerId, items) {
      const max = Math.max(...items.map(i => i.val), 1);
      document.getElementById(containerId).innerHTML = items.map((item, i) => `
        <div class="d-flex align-items-center gap-2 mb-2">
          <div style="width:110px;font-size:.72rem;color:#c9d1d9;white-space:nowrap;overflow:hidden;
                      text-overflow:ellipsis;" title="${item.label}">${item.label}</div>
          <div style="flex:1;background:#21262d;border-radius:6px;height:16px;overflow:hidden;">
            <div style="width:${Math.round(item.val/max*100)}%;background:${BAR_COLORS[i % BAR_COLORS.length]};
                        height:100%;border-radius:6px;transition:width .6s ease;"></div>
          </div>
          <div style="width:56px;font-size:.72rem;color:#8b949e;text-align:right;">${item.disp}</div>
        </div>
      `).join('');
    }

    function renderCharts(cats) {
      const entries = Object.entries(cats).sort((a, b) => b[1].count - a[1].count);
      barChart('ia-types-chart', entries.map(([k, v]) => ({
        label: k, val: v.count, disp: v.count + ' ×'
      })));
      barChart('ia-km-chart', entries.map(([k, v]) => ({
        label: k,
        val: v.count ? Math.round(v.kmTotal / v.count) : 0,
        disp: v.count ? Math.round(v.kmTotal / v.count).toLocaleString('fr-FR') + ' km' : '—'
      })));
    }

    /* ── KPI cards ───────────────────────────────────────────── */
    function renderScores(data, cats) {
      const total    = data.length;
      const kmMoyen  = total ? Math.round(data.reduce((s, r) => s + r.km, 0) / total) : 0;
      const nbCats   = Object.keys(cats).length;
      const dominant = Object.entries(cats).sort((a, b) => b[1].count - a[1].count)[0];

      const cards = [
        { icon:'🔧', label:'Interventions analysées', value: total,
          sub: nbCats + ' type(s) détecté(s)', color:'#1f6feb', bg:'rgba(31,111,235,.15)', border:'rgba(31,111,235,.4)' },
        { icon:'🏎️', label:'Kilométrage moyen', value: kmMoyen.toLocaleString('fr-FR') + ' km',
          sub:'Moyenne sur les entretiens visibles', color:'#d29922', bg:'rgba(210,153,34,.15)', border:'rgba(210,153,34,.4)' },
        { icon:'📊', label:'Intervention dominante', value: dominant ? dominant[0] : '—',
          sub: dominant ? dominant[1].count + ' occurrence(s)' : '', color:'#e94560', bg:'rgba(233,69,96,.15)', border:'rgba(233,69,96,.4)' },
        { icon:'🤖', label:'Analyse', value:'Claude AI',
          sub:'Recommandations personnalisées', color:'#8957e5', bg:'rgba(137,87,229,.15)', border:'rgba(137,87,229,.4)' },
      ];

      document.getElementById('ia-scores').innerHTML = cards.map(cd => `
        <div class="col-6 col-lg-3">
          <div style="background:${cd.bg};border:1px solid ${cd.border};border-radius:12px;padding:16px;">
            <div style="font-size:1.4rem;margin-bottom:6px;">${cd.icon}</div>
            <div style="font-size:1.15rem;font-weight:700;color:${cd.color};
                        overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${cd.value}</div>
            <div style="font-size:.7rem;color:#8b949e;margin-top:4px;">${cd.label}</div>
            <div style="font-size:.65rem;color:#6e7681;margin-top:2px;">${cd.sub}</div>
          </div>
        </div>
      `).join('');
    }

    /* ── Appel API Claude ────────────────────────────────────── */
    async function callGroq(data) {
      const resume = data.map(r => `- ${r.type} à ${r.km.toLocaleString('fr-FR')} km`).join('\n');

      const prompt = `Tu es un expert en maintenance automobile et en écologie.
Voici les données d'entretien d'un parc de véhicules (${data.length} entrées) :

${resume}

Analyse ces données et fournis :
1. **Impact écologique estimé** : calcule une estimation du CO₂ économisé grâce à ces entretiens (base : vidange/filtre optimise la consommation de 5%, pneus 6%, climatisation 8%) exprimé en kg de CO₂.
2. **Tendances détectées** : identifie les types d'intervention les plus fréquents et ce que ça révèle sur l'état du parc.
3. **3 recommandations concrètes** : conseils précis et personnalisés basés sur ces données pour réduire la consommation et améliorer la durée de vie des véhicules.
4. **Alerte si nécessaire** : signale toute anomalie (ex: freinage trop fréquent, kilométrage très élevé entre entretiens).

Réponds de manière structurée, professionnelle et concise. Utilise des emojis pertinents pour la lisibilité.`;

      const response = await fetch('/Esprit-PI-2PREPA-2026-EntreAUtous/controller/groq_proxy.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          messages: [{ role: 'user', content: prompt }]
        })
      });

      if (!response.ok) {
        const err = await response.json().catch(() => ({}));
        const msg = typeof err.error === 'string' ? err.error
                  : typeof err.error === 'object' ? JSON.stringify(err.error)
                  : `Erreur HTTP ${response.status}`;
        throw new Error(msg);
      }

      const json = await response.json();
      if (!json.success) throw new Error(json.error || 'Erreur proxy Groq.');
      return json.text;
    }

    /* ── Init ────────────────────────────────────────────────── */
    document.getElementById('btn-analyse-ia').addEventListener('click', async function () {
      const data = scrapeTableau();
      if (!data.length) {
        alert("Aucune donnée dans le tableau pour effectuer l'analyse.");
        return;
      }

      const modal   = new bootstrap.Modal(document.getElementById('modalAnalyseIA'));
      const loading = document.getElementById('ia-loading');
      const result  = document.getElementById('ia-result');
      const errDiv  = document.getElementById('ia-error');

      loading.style.display = 'block';
      result.style.display  = 'none';
      errDiv.style.display  = 'none';

      modal.show();

      const cats = computeStats(data);
      renderScores(data, cats);
      renderCharts(cats);

      try {
        const texte = await callGroq(data);
        document.getElementById('ia-recommandations').textContent = texte;
        loading.style.display = 'none';
        result.style.display  = 'block';
      } catch (err) {
        loading.style.display = 'none';
        errDiv.style.display  = 'block';
        errDiv.innerHTML = `<strong>Erreur IA Groq :</strong> ${err.message}`;
      }
    });

  })();
  </script>

  <!-- Libs JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js"></script>
  <!-- Theme JS -->
  <script src="/Esprit-PI-2PREPA-2026-EntreAUtous/assets/back/js/theme.min.js"></script>
  <script src="/Esprit-PI-2PREPA-2026-EntreAUtous/assets/back/js/vendors/sidebarnav.js"></script>
  <script>
  (function () {
    let currentFactureId = null, currentEntretienId = null;

    document.addEventListener('click', function (e) {
      const btn = e.target.closest('.btn-voir-facture');
      if (!btn) return;
      currentFactureId   = btn.dataset.idFacture;
      currentEntretienId = btn.dataset.entretien;
      document.getElementById('view-ref').textContent  = btn.dataset.ref  || '—';
      document.getElementById('view-date').textContent = btn.dataset.date || '—';
      document.getElementById('view-ht').textContent   = btn.dataset.ht  ? parseFloat(btn.dataset.ht).toFixed(3)  + ' TND' : '—';
      document.getElementById('view-tva').textContent  = btn.dataset.tva ? btn.dataset.tva + '%' : '—';
      document.getElementById('view-ttc').textContent  = btn.dataset.ttc ? parseFloat(btn.dataset.ttc).toFixed(3) + ' TND' : '—';
      document.getElementById('view-mode').textContent = btn.dataset.mode || '—';
      document.getElementById('view-etat').textContent = btn.dataset.etat || '—';
      document.getElementById('edit-id-facture').value = currentFactureId;
      document.getElementById('edit-entretien').value  = currentEntretienId;
      document.getElementById('edit-ref').value  = btn.dataset.ref  || '';
      document.getElementById('edit-date').value = btn.dataset.date || '';
      document.getElementById('edit-ht').value   = btn.dataset.ht   || '';
      document.getElementById('edit-tva').value  = btn.dataset.tva  || '19';
      document.getElementById('edit-ttc').value  = btn.dataset.ttc  || '';
      document.getElementById('edit-mode').value = btn.dataset.mode || 'Espèces';
      document.getElementById('edit-etat').value = btn.dataset.etat || 'En attente';
      afficherConsultation();
      new bootstrap.Modal(document.getElementById('modalVoirFacture')).show();
    });

    function afficherConsultation() {
      document.getElementById('mode-consultation').style.display = 'block';
      document.getElementById('mode-modification').style.display = 'none';
      document.getElementById('btns-consultation').style.display = 'flex';
    }
    function afficherModification() {
      document.getElementById('mode-consultation').style.display = 'none';
      document.getElementById('mode-modification').style.display = 'block';
      document.getElementById('btns-consultation').style.display = 'none';
    }
    document.getElementById('btn-ouvrir-modif').addEventListener('click', afficherModification);
    document.getElementById('btn-annuler-modif').addEventListener('click', afficherConsultation);

    document.getElementById('edit-ht').addEventListener('input',   computeTTC);
    document.getElementById('edit-tva').addEventListener('change', computeTTC);
    function computeTTC() {
      const ht = parseFloat(document.getElementById('edit-ht').value) || 0;
      const tv = parseFloat(document.getElementById('edit-tva').value) || 0;
      document.getElementById('edit-ttc').value = (ht * (1 + tv / 100)).toFixed(3);
    }

    document.getElementById('form-modifier-facture').addEventListener('submit', function (e) {
      e.preventDefault();
      fetch('/Esprit-PI-2PREPA-2026-EntreAUtous/controller/facture_action.php?action=update', { method:'POST', body: new FormData(this) })
        .then(r => r.json()).then(res => {
          if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalVoirFacture')).hide(); window.location.href = 'listeentretiens.php?fact_ok=1'; }
          else alert('Erreur modification facture.');
        });
    });

    document.getElementById('btn-supprimer-facture').addEventListener('click', function () {
      if (!confirm('Supprimer cette facture ?')) return;
      const fd = new FormData(); fd.append('id', currentFactureId);
      fetch('/Esprit-PI-2PREPA-2026-EntreAUtous/controller/facture_action.php?action=delete', { method:'POST', body: fd })
        .then(r => r.json()).then(res => {
          if (res.success) { bootstrap.Modal.getInstance(document.getElementById('modalVoirFacture')).hide(); window.location.href = 'listeentretiens.php?deleted=1'; }
          else alert('Erreur suppression.');
        });
    });
  })();
  </script>
    </div>
  </div>
</body>
</html>