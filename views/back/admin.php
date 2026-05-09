<?php
// views/back/admin.php
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path'     => '/autout/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../front/login.php");
    exit();
}

require_once __DIR__ . '/../../models/db.php';

// ── Action suppression ────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $pdo->prepare("DELETE FROM client WHERE id_client = ?")->execute([(int)$_GET['id']]);
    $params = http_build_query(array_filter([
        'page'   => $_GET['page']   ?? 1,
        'search' => $_GET['search'] ?? '',
        'sort'   => $_GET['sort']   ?? 'date_inscription',
        'order'  => $_GET['order']  ?? 'DESC',
        'success'=> 'deleted',
    ]));
    header("Location: admin.php?$params");
    exit();
}

// ══ PAGINATION + RECHERCHE + TRI ══════════════════════════════
$perPage     = 5;
$page        = max(1, (int)($_GET['page']   ?? 1));
$search      = trim($_GET['search'] ?? '');
$sort        = $_GET['sort']  ?? 'date_inscription';
$order       = strtoupper($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
$offset      = ($page - 1) * $perPage;

$allowedSort = ['nom', 'prenom', 'email', 'telephone', 'date_inscription'];
if (!in_array($sort, $allowedSort)) $sort = 'date_inscription';

$whereSQL  = '';
$params    = [];
if ($search !== '') {
    $whereSQL = "WHERE (nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?)";
    $like     = "%$search%";
    $params   = [$like, $like, $like, $like];
}

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM client $whereSQL");
$totalStmt->execute($params);

// ── Stats tracking ──────────────────────────────────────────
$trackStats    = ['total_visits'=>0,'unique_users'=>0,'avg_s'=>0];
$trackByDay    = [];
$trackTopPages = [];
$visitsToday   = 0;
$registByDay   = [];
$recentEvents  = [];
try {
    $tg = $pdo->query("SELECT COUNT(*) as total_visits, COUNT(DISTINCT id_client) as unique_users, ROUND(AVG(duration_s)) as avg_s FROM user_tracking");
    $trackStats = $tg->fetch(PDO::FETCH_ASSOC) ?: $trackStats;

    $td = $pdo->query("SELECT DATE(visited_at) as day, COUNT(*) as v FROM user_tracking WHERE visited_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(visited_at) ORDER BY day ASC");
    $rawByDay = $td->fetchAll(PDO::FETCH_ASSOC);
    $dayMap = [];
    foreach ($rawByDay as $r) $dayMap[$r['day']] = (int)$r['v'];
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $trackByDay[] = ['day' => $d, 'v' => $dayMap[$d] ?? 0];
    }

    $tp2q = $pdo->query("SELECT page, COUNT(*) as v, ROUND(AVG(duration_s)) as avg_s FROM user_tracking GROUP BY page ORDER BY v DESC LIMIT 6");
    $trackTopPages = $tp2q->fetchAll(PDO::FETCH_ASSOC);

    $vt = $pdo->query("SELECT COUNT(*) FROM user_tracking WHERE DATE(visited_at) = CURDATE()");
    $visitsToday = (int)$vt->fetchColumn();

    $ri = $pdo->query("SELECT DATE(date_inscription) as day, COUNT(*) as v FROM client WHERE date_inscription >= DATE_SUB(CURDATE(), INTERVAL 13 DAY) GROUP BY DATE(date_inscription)");
    $rawReg = $ri->fetchAll(PDO::FETCH_ASSOC);
    $regMap = [];
    foreach ($rawReg as $r) $regMap[$r['day']] = (int)$r['v'];
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $registByDay[] = ['day' => $d, 'v' => $regMap[$d] ?? 0];
    }

    $ev = $pdo->query("SELECT prenom, COALESCE(nom,'') as nom, email, date_inscription as ts FROM client WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 7 DAY) ORDER BY date_inscription DESC LIMIT 15");
    $recentEvents = $ev->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) { error_log('[admin.php tracking] ' . $e->getMessage()); }

$totalClients = (int)$totalStmt->fetchColumn();
$totalPages   = max(1, (int)ceil($totalClients / $perPage));
if ($page > $totalPages) $page = $totalPages;

$dataStmt = $pdo->prepare(
    "SELECT id_client, nom, prenom, email, telephone, adresse, date_inscription
     FROM client $whereSQL
     ORDER BY $sort $order
     LIMIT $perPage OFFSET $offset"
);
$dataStmt->execute($params);
$clients = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

// Stats modules (valeurs statiques / à brancher sur vos tables)
$statGarages  = 14;
$statVehicules = 87;
$statPieces   = 342;
$statMessages = 21;

function buildUrl(array $extra = []): string {
    $base = array_filter([
        'search' => $_GET['search'] ?? '',
        'sort'   => $_GET['sort']   ?? 'date_inscription',
        'order'  => $_GET['order']  ?? 'DESC',
        'page'   => $_GET['page']   ?? 1,
    ]);
    return 'admin.php?' . http_build_query(array_merge($base, $extra));
}

function sortIcon(string $col): string {
    global $sort, $order;
    if ($sort !== $col) return '<i class="ti ti-arrows-sort text-muted opacity-50 ms-1"></i>';
    return $order === 'ASC'
        ? '<i class="ti ti-arrow-up ms-1 text-primary"></i>'
        : '<i class="ti ti-arrow-down ms-1 text-primary"></i>';
}

function sortLink(string $col, string $label): string {
    global $sort, $order;
    $nextOrder = ($sort === $col && $order === 'ASC') ? 'DESC' : 'ASC';
    $url = buildUrl(['sort' => $col, 'order' => $nextOrder, 'page' => 1]);
    return "<a href=\"$url\" class=\"text-decoration-none text-dark d-flex align-items-center\">$label" . sortIcon($col) . "</a>";
}
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light" id="htmlRoot">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dashboard Admin | Entre AuTout</title>
    <link rel="stylesheet" href="../../assets/back/css/theme.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@300;400;500;600;700&display=swap">
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
</head>
<body>

<?php if (isset($_GET['success'])): ?>
<div class="toast-notif alert alert-success alert-dismissible shadow" id="toastMsg">
    <i class="ti ti-check me-2"></i>
    <?= $_GET['success'] === 'deleted' ? 'Client supprimé avec succès.' : 'Opération réussie !' ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="layout-wrapper">

    <!-- ══ SIDEBAR ══════════════════════════════════════════════ -->
    <aside class="sidebar" id="sidebar">

        <!-- Brand -->
        <a class="sidebar-brand" href="admin.php">
            <div class="brand-icon">AT</div>
            <span class="brand-name">Entre AuTout</span>
            <span class="brand-badge">Admin</span>
        </a>

        <!-- Nav -->
        <nav class="sidebar-nav" id="sidebarNav">

            <!-- Tableau de bord -->
            <div class="nav-section-label">Principal</div>
            <a class="sidebar-link active" href="admin.php">
                <span class="s-icon"><i class="ti ti-layout-dashboard"></i></span>
                Tableau de bord
            </a>

            <!-- Module Garages (Rayen) -->
            <div class="nav-section-label">Modules</div>
            <!-- Module Garages (Rayen) -->
           <a class="sidebar-link" href="/integration/user/views/BackOffice/back.php">
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
                    <a class="sidebar-link" href="#">Inventaire</a>
                    <a class="sidebar-link" href="#">Ajouter un véhicule</a>
                    <a class="sidebar-link" href="#">Marques & Modèles</a>
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
                    <a class="sidebar-link" href="#">Catalogue</a>
                    <a class="sidebar-link" href="#">Commandes</a>
                    <a class="sidebar-link" href="#">Stock</a>
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
                    <a class="sidebar-link active" href="admin.php">Liste des clients</a>
                    <a class="sidebar-link" href="#">Inscriptions récentes</a>
                    <a class="sidebar-link" href="#">Activité & Tracking</a>
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
                <a class="logout-btn" href="/autout/controller/UserController.php?action=logout" title="Déconnexion">
                    <i class="ti ti-logout"></i>
                </a>
            </div>
        </div>
    </aside>

    <!-- ══ MAIN ══════════════════════════════════════════════════ -->
    <div class="main-area">

        <!-- ── Topbar ── -->
        <header class="topbar">
            <div class="topbar-left">
                <!-- Toggle mobile -->
                <button class="topbar-icon-btn border-0 d-lg-none" onclick="document.getElementById('sidebar').classList.toggle('show')">
                    <i class="ti ti-menu-2"></i>
                </button>
                <div>
                    <div class="topbar-title">Tableau de bord</div>
                    <div class="topbar-sub"><?= date('l d F Y') ?></div>
                </div>
            </div>
            <div class="topbar-right">
                <!-- Thème -->
                <div class="dropdown">
                    <a class="topbar-icon-btn" data-bs-toggle="dropdown" href="#">
                        <i class="ti ti-sun" id="themeIconTop"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><button class="dropdown-item" onclick="setTheme('light')"><i class="ti ti-sun me-2"></i>Clair</button></li>
                        <li><button class="dropdown-item" onclick="setTheme('dark')"><i class="ti ti-moon me-2"></i>Sombre</button></li>
                    </ul>
                </div>

                <!-- Notifications -->
                <a class="topbar-icon-btn" href="#" title="Notifications">
                    <i class="ti ti-bell"></i>
                    <span class="notif-dot"></span>
                </a>

                <!-- Messagerie rapide -->
                <a class="topbar-icon-btn" href="#" title="Messages">
                    <i class="ti ti-message-circle"></i>
                    <?php if ($statMessages > 0): ?>
                    <span class="notif-dot"></span>
                    <?php endif; ?>
                </a>

                <!-- Profil -->
                <div class="dropdown">
                    <a class="d-flex align-items-center gap-2 text-decoration-none" data-bs-toggle="dropdown" href="#">
                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                             style="width:34px;height:34px;font-size:.8rem;">A</div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                        <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($_SESSION['email'] ?? 'admin@autout.tn') ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#"><i class="ti ti-user me-2"></i>Mon profil</a></li>
                        <li><a class="dropdown-item" href="#"><i class="ti ti-settings me-2"></i>Paramètres</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="/autout/controller/UserController.php?action=logout">
                            <i class="ti ti-logout me-2"></i>Déconnexion
                        </a></li>
                    </ul>
                </div>
            </div>
        </header>

        <!-- ── Page content ── -->
        <div class="page-content">

            <!-- Bannière de bienvenue -->
            <div class="bg-gradient-mixed p-4 rounded-3 text-white shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-1">👋 Espace Administrateur — Entre AuTout</h4>
                        <p class="mb-0 opacity-75 small">Vue d'ensemble de tous les modules de la plateforme automobile</p>
                    </div>
                    <span class="badge bg-white text-primary fs-6 fw-bold"><?= date('d/m/Y') ?></span>
                </div>
            </div>

            <!-- ── Stats Cards ── -->
            <div class="row g-3 mb-4">
                <?php
                $stats = [
                    ['label'=>'Clients inscrits',     'value'=>$totalClients,  'icon'=>'ti-users',          'color'=>'primary',  'delta'=>'↑ inscrits ce mois'],
                    ['label'=>'Garages actifs',        'value'=>$statGarages,   'icon'=>'ti-building',       'color'=>'success',  'delta'=>'Partenaires vérifiés'],
                    ['label'=>'Véhicules référencés',  'value'=>$statVehicules, 'icon'=>'ti-car',            'color'=>'danger',   'delta'=>'Dans l\'inventaire'],
                    ['label'=>'Pièces en stock',       'value'=>$statPieces,    'icon'=>'ti-shopping-cart',  'color'=>'warning',  'delta'=>'Référencées au catalogue'],
                    ['label'=>'Messages en attente',   'value'=>$statMessages,  'icon'=>'ti-message-circle', 'color'=>'info',     'delta'=>'À traiter'],
                    ['label'=>'Visites aujourd\'hui',  'value'=>$visitsToday,   'icon'=>'ti-activity',       'color'=>'secondary','delta'=>'Sessions actives'],
                ];
                foreach ($stats as $s): ?>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <div class="stat-card">
                        <div class="stat-icon bg-<?= $s['color'] ?>-subtle text-<?= $s['color'] ?>">
                            <i class="ti <?= $s['icon'] ?>"></i>
                        </div>
                        <div>
                            <div class="stat-label"><?= $s['label'] ?></div>
                            <div class="stat-value"><?= $s['value'] ?></div>
                            <div class="stat-delta"><?= $s['delta'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ── Graphiques : Activité ── -->
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <div class="data-card h-100">
                        <div class="data-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <h6 class="fw-bold mb-0"><i class="ti ti-chart-bar me-2 text-primary"></i>Activité — 14 derniers jours</h6>
                            <div class="d-flex gap-2">
                                <span class="badge bg-primary-subtle text-primary"><?= (int)($trackStats['total_visits']??0) ?> visites totales</span>
                                <span class="badge bg-success-subtle text-success"><?= count($recentEvents) ?> inscrits (7j)</span>
                            </div>
                        </div>
                        <div class="p-3">
                            <div class="d-flex gap-3 mb-2" style="font-size:.72rem;">
                                <span><span style="display:inline-block;width:10px;height:10px;background:#6366f1;border-radius:2px;margin-right:4px;"></span>Visites</span>
                                <span><span style="display:inline-block;width:10px;height:10px;background:#22c55e;border-radius:2px;margin-right:4px;"></span>Inscriptions</span>
                            </div>
                            <canvas id="actChart" height="90"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Événements récents -->
                <div class="col-lg-4">
                    <div class="data-card h-100">
                        <div class="data-card-header">
                            <h6 class="fw-bold mb-0"><i class="ti ti-clock me-2 text-warning"></i>Inscriptions récentes</h6>
                        </div>
                        <div style="max-height:280px;overflow-y:auto;">
                            <?php if (empty($recentEvents)): ?>
                            <div class="text-center text-muted py-4 small">Aucune inscription récente.</div>
                            <?php else: foreach ($recentEvents as $ev): ?>
                            <div class="d-flex align-items-center gap-3 px-4 py-3 border-bottom">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                                     style="width:34px;height:34px;font-size:.75rem;">
                                    <?= strtoupper(mb_substr($ev['prenom']??'?',0,1).mb_substr($ev['nom']??'',0,1)) ?>
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-semibold text-truncate" style="font-size:.82rem;"><?= htmlspecialchars($ev['prenom'].' '.$ev['nom']) ?></div>
                                    <div class="text-muted" style="font-size:.72rem;"><?= htmlspecialchars($ev['email']) ?></div>
                                </div>
                                <div class="text-muted flex-shrink-0" style="font-size:.7rem;"><?= date('d/m', strtotime($ev['ts'])) ?></div>
                            </div>
                            <?php endforeach; endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Tableau Clients ── -->
            <div class="data-card mb-5">
                <div class="data-card-header">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                        <h6 class="fw-bold mb-0"><i class="ti ti-users me-2 text-primary"></i>Gestion des Clients</h6>
                        <span class="badge bg-primary-subtle text-primary"><?= $totalClients ?> client(s)</span>
                    </div>

                    <!-- Filtres -->
                    <div class="filter-bar">
                        <form method="GET" action="admin.php" class="row g-2 align-items-end" id="filterForm">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold mb-1">Recherche</label>
                                <div class="search-wrapper">
                                    <i class="ti ti-search"></i>
                                    <input type="text" name="search" id="searchLive"
                                           class="form-control form-control-sm"
                                           placeholder="Nom, prénom, email, téléphone…"
                                           value="<?= htmlspecialchars($search) ?>"
                                           autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold mb-1">Trier par</label>
                                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <?php
                                    $sortOptions = ['date_inscription'=>'Date d\'inscription','nom'=>'Nom','prenom'=>'Prénom','email'=>'Email'];
                                    foreach ($sortOptions as $val => $label):
                                    ?>
                                    <option value="<?= $val ?>" <?= $sort === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small fw-semibold mb-1">Ordre</label>
                                <select name="order" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>↓ Décroissant</option>
                                    <option value="ASC"  <?= $order === 'ASC'  ? 'selected' : '' ?>>↑ Croissant</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-1">
                                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                                    <i class="ti ti-filter"></i> Filtrer
                                </button>
                                <a href="admin.php" class="btn btn-outline-secondary btn-sm" title="Réinitialiser">
                                    <i class="ti ti-x"></i>
                                </a>
                            </div>
                            <input type="hidden" name="page" value="1">
                        </form>
                        <?php if ($search): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Résultats pour "<strong><?= htmlspecialchars($search) ?></strong>" :
                                <span class="text-primary fw-bold"><?= $totalClients ?></span> trouvé(s)
                                <a href="admin.php" class="ms-2 badge bg-secondary text-white text-decoration-none">✕ Effacer</a>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">#</th>
                                <th><?= sortLink('prenom', 'Nom Complet') ?></th>
                                <th><?= sortLink('email', 'Email') ?></th>
                                <th><?= sortLink('telephone', 'Téléphone') ?></th>
                                <th>Adresse</th>
                                <th><?= sortLink('date_inscription', 'Inscription') ?></th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (empty($clients)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="ti ti-users-off fs-2 d-block mb-2 opacity-50"></i>
                                    <?= $search ? 'Aucun résultat pour cette recherche.' : 'Aucun client enregistré.' ?>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($clients as $c): ?>
                            <tr>
                                <td class="text-muted small ps-3"><?= $c['id_client'] ?></td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold"
                                             style="width:32px;height:32px;font-size:.75rem;">
                                            <?= strtoupper(mb_substr($c['prenom']??'?',0,1).mb_substr($c['nom']??'',0,1)) ?>
                                        </div>
                                        <?php
                                        $fullName = htmlspecialchars($c['prenom'].' '.$c['nom']);
                                        if ($search) {
                                            $fullName = preg_replace('/('.preg_quote(htmlspecialchars($search),'/').')/i',
                                                '<mark class="bg-warning rounded px-1">$1</mark>', $fullName);
                                        }
                                        echo "<span>$fullName</span>";
                                        ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $em = htmlspecialchars($c['email']);
                                    if ($search) $em = preg_replace('/('.preg_quote(htmlspecialchars($search),'/').')/i',
                                        '<mark class="bg-warning rounded px-1">$1</mark>', $em);
                                    echo $em;
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($c['telephone'] ?? '—') ?></td>
                                <td class="text-truncate" style="max-width:130px"><?= htmlspecialchars($c['adresse'] ?? '—') ?></td>
                                <td class="small text-muted">
                                    <?= $c['date_inscription'] ? date('d/m/Y', strtotime($c['date_inscription'])) : '—' ?>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-primary" title="Voir détails"
                                                onclick="showDetails(<?= htmlspecialchars(json_encode($c)) ?>)">
                                            <i class="ti ti-eye"></i>
                                        </button>
                                        <a class="btn btn-sm btn-outline-danger"
                                           href="<?= buildUrl(['action'=>'delete','id'=>$c['id_client']]) ?>"
                                           title="Supprimer"
                                           onclick="return confirm('Supprimer <?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?> ?')">
                                            <i class="ti ti-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="px-4 py-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Page <strong><?= $page ?></strong> / <?= $totalPages ?>
                        &nbsp;·&nbsp; <?= $totalClients ?> client(s) &nbsp;·&nbsp; <?= $perPage ?> par page
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildUrl(['page'=>1]) ?>"><i class="ti ti-chevrons-left"></i></a>
                            </li>
                            <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildUrl(['page'=>$page-1]) ?>"><i class="ti ti-chevron-left"></i></a>
                            </li>
                            <?php
                            $start = max(1, $page - 2); $end = min($totalPages, $page + 2);
                            if ($start > 1): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif;
                            for ($p = $start; $p <= $end; $p++): ?>
                            <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= buildUrl(['page'=>$p]) ?>"><?= $p ?></a>
                            </li>
                            <?php endfor;
                            if ($end < $totalPages): ?><li class="page-item disabled"><span class="page-link">…</span></li><?php endif; ?>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildUrl(['page'=>$page+1]) ?>"><i class="ti ti-chevron-right"></i></a>
                            </li>
                            <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                                <a class="page-link" href="<?= buildUrl(['page'=>$totalPages]) ?>"><i class="ti ti-chevrons-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
                <?php endif; ?>
            </div>

        </div><!-- /page-content -->
    </div><!-- /main-area -->
</div><!-- /layout-wrapper -->

<!-- Modal Détails Client -->
<div class="modal fade" id="detailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="ti ti-user me-2"></i>Détails du client</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<button id="backToTop" class="btn btn-primary shadow" onclick="window.scrollTo({top:0,behavior:'smooth'})">
    <i class="ti ti-arrow-up"></i>
</button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>

<script>
// ── Thème ─────────────────────────────────────────────────────
function setTheme(t) {
    document.getElementById('htmlRoot').setAttribute('data-bs-theme', t);
    document.getElementById('themeIconTop').className = t === 'dark' ? 'ti ti-sun' : 'ti ti-moon';
    localStorage.setItem('theme', t);
}
(function() {
    const t = localStorage.getItem('theme') || 'light';
    setTheme(t);
})();

// ── Recherche live ────────────────────────────────────────────
let debounceTimer;
document.getElementById('searchLive').addEventListener('input', function() {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        document.querySelector('input[name="page"]').value = 1;
        document.getElementById('filterForm').submit();
    }, 400);
});

// ── Modal détails ─────────────────────────────────────────────
function showDetails(c) {
    document.getElementById('detailBody').innerHTML = `
        <table class="table table-borderless">
            <tr><th width="40%">ID</th><td>${c.id_client}</td></tr>
            <tr><th>Nom complet</th><td>${c.prenom ?? ''} ${c.nom ?? ''}</td></tr>
            <tr><th>Email</th><td>${c.email ?? '—'}</td></tr>
            <tr><th>Téléphone</th><td>${c.telephone ?? '—'}</td></tr>
            <tr><th>Adresse</th><td>${c.adresse ?? '—'}</td></tr>
            <tr><th>Inscription</th><td>${c.date_inscription ?? '—'}</td></tr>
        </table>`;
    new bootstrap.Modal(document.getElementById('detailModal')).show();
}

// ── Graphique activité ────────────────────────────────────────
const trackDays = <?= json_encode(array_column($trackByDay, 'day')) ?>;
const trackV    = <?= json_encode(array_column($trackByDay, 'v')) ?>;
const registV   = <?= json_encode(array_column($registByDay, 'v')) ?>;

const labels = trackDays.map(d => {
    const [y,m,dd] = d.split('-');
    return `${dd}/${m}`;
});

new Chart(document.getElementById('actChart'), {
    type: 'bar',
    data: {
        labels,
        datasets: [
            {
                label: 'Visites',
                data: trackV,
                backgroundColor: 'rgba(99,102,241,.75)',
                borderRadius: 6,
                borderSkipped: false,
            },
            {
                label: 'Inscriptions',
                data: registV,
                backgroundColor: 'rgba(34,197,94,.7)',
                borderRadius: 6,
                borderSkipped: false,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 10 } } },
            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(0,0,0,.05)' } }
        }
    }
});

// ── Scroll top ────────────────────────────────────────────────
window.addEventListener('scroll', () => {
    document.getElementById('backToTop').style.display = window.scrollY > 300 ? 'flex' : 'none';
});

// ── Auto-dismiss toast ────────────────────────────────────────
setTimeout(() => {
    const t = document.getElementById('toastMsg');
    if (t) t.style.display = 'none';
}, 4000);
</script>
</body>
</html>