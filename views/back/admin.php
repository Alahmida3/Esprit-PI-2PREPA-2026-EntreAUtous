<?php 
session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../front/login.php");
    exit();
}

/**
 * Nettoie les fichiers HTML du template Dasher.
 * Remplace @@webRoot et redirige les vendors vers le CDN pour stopper les 404.
 */
function loadAndFix($path) {
    if (file_exists($path)) {
        $html = file_get_contents($path);
        // On remplace le tag du template par ton dossier assets corrigé
        $html = str_replace('@@webRoot', '../../assets/back', $html);
        
        // CORRECTION CRITIQUE : Si le template cherche dans node_modules, on le redirige vers le Web
        $html = str_replace('../../assets/back/node_modules', 'https://cdn.jsdelivr.net/npm', $html);
        return $html;
    }
    return "";
}
?>
<!DOCTYPE html>
<html lang="fr" data-bs-theme="light" class="expanded">
<head>
    <?php echo loadAndFix("../../partials/head/head-meta.html"); ?>
    <title>Dashboard | Dasher Admin Elyo</title>
    
    <link rel="stylesheet" href="../../assets/back/css/theme.css">
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.css">

    <?php echo loadAndFix("../../partials/head/head-links.html"); ?>

    <style>
        /* Styles pour correspondre exactement à la démo ThemeWagon */
        :root { --dasher-sidebar-width: 250px; }
        .bg-gradient-mixed { background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%) !important; }
        
        /* Force la sidebar à être visible comme sur le site */
        @media (min-width: 992px) {
            .navbar-vertical { display: block !important; width: var(--dasher-sidebar-width); position: fixed; left: 0; top: 0; height: 100vh; z-index: 1030; }
            .main-content-wrapper { margin-left: var(--dasher-sidebar-width); width: calc(100% - var(--dasher-sidebar-width)); }
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <?php echo loadAndFix("../../partials/sidebar-collapse.html"); ?>

        <main class="main-content-wrapper">
            <header class="header bg-white border-bottom p-3">
                <div class="container-fluid d-flex justify-content-between align-items-center">
                    <div class="navbar-brand fw-bold text-primary">GARAGE ELYO</div>
                    
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge bg-light text-dark d-none d-md-inline">Admin Connecté</span>
                        <a href="../front/login.php" class="btn btn-danger btn-sm px-3 shadow-sm">
                            <i class="ti ti-logout me-1"></i> Déconnexion
                        </a>
                    </div>
                </div>
            </header>

            <div class="p-4">
                <div class="row mb-5">
                    <div class="col-12">
                        <div class="bg-gradient-mixed p-6 p-lg-10 rounded-3 text-white shadow">
                            <h1 class="display-5 fw-bold">👋 Espace Administrateur</h1>
                            <p class="lead">Bienvenue sur le Dashboard officiel de votre système de gestion.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-4 mb-5">
                    <?php 
                    $stats = [
                        ['n' => 'Utilisateurs', 'v' => '1,250', 'i' => 'ti-users', 'c' => 'primary'],
                        ['n' => 'Garages', 'v' => '14', 'i' => 'ti-building', 'c' => 'success'],
                        ['n' => 'CA Mensuel', 'v' => '15.4k DT', 'i' => 'ti-wallet', 'c' => 'warning'],
                        ['n' => 'Support', 'v' => '3', 'i' => 'ti-headset', 'c' => 'danger']
                    ];
                    foreach($stats as $s): ?>
                    <div class="col-md-3">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <p class="text-muted small fw-bold mb-1"><?php echo $s['n']; ?></p>
                                        <h2 class="fw-bold mb-0"><?php echo $s['v']; ?></h2>
                                    </div>
                                    <div class="p-3 bg-<?php echo $s['c']; ?>-subtle text-<?php echo $s['c']; ?> rounded-3">
                                        <i class="ti <?php echo $s['i']; ?> fs-3"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-4">
                    <?php 
                    $modules = ['Utilisateurs', 'Garages', 'Messages', 'Véhicules', 'Diagnostic', 'Vente Pièces'];
                    foreach($modules as $m): ?>
                    <div class="col-md-4">
                        <div class="card h-100 border-0 shadow-sm">
                            <div class="card-body text-center p-5">
                                <div class="icon-shape bg-light rounded-circle mx-auto mb-4" style="width: 60px; height: 60px; line-height: 60px;">
                                    <i class="ti ti-settings fs-3 text-primary"></i>
                                </div>
                                <h5 class="fw-bold"><?php echo $m; ?></h5>
                                <button class="btn btn-outline-primary btn-sm mt-3 w-100">Gérer</button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simplebar@latest/dist/simplebar.min.js"></script>
    
    <?php echo loadAndFix("../../partials/scripts.html"); ?>
    <script src="../../assets/back/js/main.js"></script>

    <script>
        // Correction forcée si le JS local échoue
        document.addEventListener('DOMContentLoaded', function() {
            document.documentElement.classList.add('expanded');
            // Ajoute la classe de scroll de la sidebar si manquante
            const sb = document.querySelector('.navbar-vertical');
            if(sb) sb.setAttribute('data-simplebar', '');
        });
    </script>
</body>
</html>