<?php
/* ═══════════════════════════════════════════════════════════
 *  VIEW ONLY — le formulaire soumet vers entretien_action.php
 *  fieldInvalid() → EntretienController::fieldInvalid() (static)
 * ═══════════════════════════════════════════════════════════ */
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../controller/EntretienController.php';

if (!$pdo) die("Erreur de connexion à la base de données.");

$controller       = new EntretienController($pdo);
$viewData         = $controller->getAjouterData();
$matricules       = $viewData['matricules'];
$validationErrors = $viewData['errors'];
$old              = $viewData['old'];
$error            = '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
  <title>Ajouter un Entretien</title>
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
  <div>
<div id="content" class="position-relative h-100">
<div class="custom-container">

        <!-- Breadcrumb -->
        <div class="row mb-4">
          <div class="col-12">
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php" class="text-muted">Accueil</a></li>
                <li class="breadcrumb-item"><a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/listeentretiens.php" class="text-muted">Entretiens</a></li>
                <li class="breadcrumb-item active" aria-current="page">Ajouter un entretien</li>
              </ol>
            </nav>
          </div>
        </div>

        <?php if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>'; ?>
        <?php if (!empty($validationErrors)): ?>
          <div class="alert alert-danger">
            <strong>Erreurs de validation :</strong>
            <ul class="mb-0 mt-2">
              <?php foreach ($validationErrors as $ve): ?>
                <li><?php echo htmlspecialchars($ve); ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="row g-6">

          <!-- Colonne formulaire -->
          <div class="col-xl-8 col-12">
            <div class="card card-lg">
              <div class="card-body">

                <!-- En-tête -->
                <div class="d-flex align-items-center gap-3 mb-6 pb-5 border-bottom">
                  <div class="icon-shape icon-lg rounded-circle bg-primary-darker text-primary-lighter">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                      stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <path d="M3 21h4l13 -13a1.5 1.5 0 0 0 -4 -4l-13 13v4" />
                      <path d="M14.5 5.5l4 4" /><path d="M12 8l-5 -5l-4 4l5 5" />
                      <path d="M7 8l-1.5 1.5" /><path d="M16 12l5 5l-4 4l-5 -5" />
                      <path d="M16 17l-1.5 1.5" />
                    </svg>
                  </div>
                  <div>
                    <h5 class="mb-0">Ajouter un entretien</h5>
                    <small class="text-muted">Remplissez les informations ci-dessous</small>
                  </div>
                </div>

                <form action="/Esprit-PI-2PREPA-2026-EntreAUtous/controller/entretien_action.php?action=add" method="POST" id="entretienForm">

                  <!-- Matricule — liste déroulante depuis table vehicule -->
                  <div class="mb-5">
                    <label for="Matricule" class="form-label fw-medium">
                      Matricule du véhicule <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php echo EntretienController::fieldInvalid($validationErrors, 'matricule'); ?>" id="Matricule" name="Matricule">
                      <option value="">-- Sélectionner un véhicule --</option>
                      <?php foreach ($matricules as $m): ?>
                        <option value="<?php echo htmlspecialchars($m['matriculevoiture']); ?>"
                          <?php echo (isset($old['Matricule']) && $old['Matricule'] === $m['matriculevoiture']) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($m['matriculevoiture']); ?>
                        </option>
                      <?php endforeach; ?>
                    </select>
                    <div class="form-text">Sélectionnez le véhicule concerné par l'entretien.</div>
                  </div>

                  <!-- Date entretien -->
                  <div class="mb-5">
                    <label for="date_entretien" class="form-label fw-medium">
                      Date entretien <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control <?php echo EntretienController::fieldInvalid($validationErrors, 'date'); ?>" id="date_entretien" name="date_entretien"
                      value="<?php echo isset($old['date_entretien']) ? htmlspecialchars($old['date_entretien']) : ''; ?>">
                  </div>

                  <!-- Kilométrage -->
                  <div class="mb-5">
                    <label for="kilometrage" class="form-label fw-medium">
                      Kilométrage <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control <?php echo EntretienController::fieldInvalid($validationErrors, 'km'); ?>" id="kilometrage" name="kilometrage"
                      value="<?php echo isset($old['kilometrage']) ? htmlspecialchars($old['kilometrage']) : ''; ?>"
                      placeholder="Ex: 120000">
                  </div>

                  <!-- Type intervention -->
                  <div class="mb-5">
                    <label for="type_intervention" class="form-label fw-medium">Type d'intervention</label>
                    <input type="text" class="form-control <?php echo EntretienController::fieldInvalid($validationErrors, 'intervention'); ?>" id="type_intervention" name="type_intervention"
                      value="<?php echo isset($old['type_intervention']) ? htmlspecialchars($old['type_intervention']) : ''; ?>"
                      placeholder="Ex: Vidange, Freinage, Révision...">
                  </div>

                  <!-- Observations -->
                  <div class="mb-5">
                    <label for="observations" class="form-label fw-medium">Observations</label>
                    <textarea class="form-control <?php echo EntretienController::fieldInvalid($validationErrors, 'observation'); ?>" id="observations" name="observations" rows="4"
                      placeholder="Notes du mécanicien..."><?php echo isset($old['observations']) ? htmlspecialchars($old['observations']) : ''; ?></textarea>
                  </div>

                  <!-- Statut -->
                  <div class="mb-5">
                    <label for="statut" class="form-label fw-medium">
                      Statut <span class="text-danger">*</span>
                    </label>
                    <select class="form-select <?php echo EntretienController::fieldInvalid($validationErrors, 'statut'); ?>" id="statut" name="statut">
                      <option value="">-- Sélectionner --</option>
                      <option value="planifie"  <?php echo (isset($old['statut']) && $old['statut'] === 'planifie') ? 'selected' : ''; ?>>Planifié</option>
                      <option value="en_cours"  <?php echo (isset($old['statut']) && $old['statut'] === 'en_cours') ? 'selected' : ''; ?>>En cours</option>
                      <option value="termine"   <?php echo (isset($old['statut']) && $old['statut'] === 'termine') ? 'selected' : ''; ?>>Terminé</option>
                      <option value="annule"    <?php echo (isset($old['statut']) && $old['statut'] === 'annule') ? 'selected' : ''; ?>>Annulé</option>
                    </select>
                  </div>

                  <!-- Prochaine échéance -->
                  <div class="mb-5">
                    <label for="prochaine_echeance" class="form-label fw-medium">Prochaine échéance</label>
                    <input type="text" class="form-control <?php echo EntretienController::fieldInvalid($validationErrors, 'prochaine'); ?>" id="prochaine_echeance" name="prochaine_echeance"
                      value="<?php echo isset($old['prochaine_echeance']) ? htmlspecialchars($old['prochaine_echeance']) : ''; ?>">
                  </div>

                  <!-- KM prochain -->
                  <div class="mb-6">
                    <label for="km_prochain" class="form-label fw-medium">Kilométrage prochaine visite</label>
                    <input type="text" class="form-control <?php echo EntretienController::fieldInvalid($validationErrors, 'prochain'); ?>" id="km_prochain" name="km_prochain"
                      value="<?php echo isset($old['km_prochain']) ? htmlspecialchars($old['km_prochain']) : ''; ?>"
                      placeholder="Ex: 150000">
                  </div>

                  <!-- Boutons -->
                  <div class="d-flex flex-wrap gap-3 pt-4 border-top">
                    <button type="submit" name="submit_add" class="btn btn-primary d-flex align-items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" /><path d="M5 12l5 5l10 -10" />
                      </svg>
                      Valider
                    </button>
                    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/listeentretiens.php" class="btn btn-outline-secondary d-flex align-items-center gap-2">
                      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M18 6l-12 12" /><path d="M6 6l12 12" />
                      </svg>
                      Annuler
                    </a>
                  </div>

                  <!-- Retour liste -->
                  <div class="mt-5 pt-2">
                    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/listeentretiens.php" class="d-flex align-items-center gap-2 text-muted small text-decoration-none">
                      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M5 12l14 0" /><path d="M5 12l6 6" /><path d="M5 12l6 -6" />
                      </svg>
                      Retourner à la liste des entretiens
                    </a>
                  </div>

                </form>

              </div>
            </div>
          </div>

          <!-- Colonne aide -->
          <div class="col-xl-4 col-12">

            <div class="card card-lg mb-5">
              <div class="card-body">
                <h6 class="mb-4 d-flex align-items-center gap-2">
                  <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-primary">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
                    <path d="M12 8h.01" /><path d="M11 12h1v4h1" />
                  </svg>
                  Guide de saisie
                </h6>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3">
                  <li class="d-flex align-items-start gap-2">
                    <span class="badge bg-primary-subtle text-primary mt-1">1</span>
                    <span class="small text-muted">Sélectionnez le matricule du véhicule</span>
                  </li>
                  <li class="d-flex align-items-start gap-2">
                    <span class="badge bg-primary-subtle text-primary mt-1">2</span>
                    <span class="small text-muted">Saisissez la date et le kilométrage actuel</span>
                  </li>
                  <li class="d-flex align-items-start gap-2">
                    <span class="badge bg-primary-subtle text-primary mt-1">3</span>
                    <span class="small text-muted">Précisez le type d'intervention effectuée</span>
                  </li>
                  <li class="d-flex align-items-start gap-2">
                    <span class="badge bg-primary-subtle text-primary mt-1">4</span>
                    <span class="small text-muted">Indiquez la prochaine échéance et le statut</span>
                  </li>
                </ul>
              </div>
            </div>

            <div class="card card-lg">
              <div class="card-body">
                <h6 class="mb-4">Légende des statuts</h6>
                <div class="d-flex flex-column gap-3">
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-warning-emphasis bg-warning-subtle">Planifié</span>
                    <span class="small text-muted">Programmé, pas encore eu lieu</span>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-info-emphasis bg-info-subtle">En cours</span>
                    <span class="small text-muted">En train de se dérouler</span>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-success-emphasis bg-success-subtle">Terminé</span>
                    <span class="small text-muted">Effectué avec succès</span>
                  </div>
                  <div class="d-flex align-items-center gap-3">
                    <span class="badge text-danger-emphasis bg-danger-subtle">Annulé</span>
                    <span class="small text-muted">Annulé ou reporté</span>
                  </div>
                </div>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>


  <script>
  // Scroll automatique vers les erreurs de validation
  document.addEventListener('DOMContentLoaded', function () {
    const alertErr = document.querySelector('.alert-danger');
    if (alertErr) {
      alertErr.scrollIntoView({ behavior: 'smooth', block: 'center' });
      alertErr.focus();
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