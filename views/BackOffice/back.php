<?php
require_once __DIR__ . '/../../models/db.php';

$garages = [];
$openGarages = [];
$services = [];
$editGarage = null;
$editService = null;
$garageSearchTerm = '';
$serviceSearchTerm = '';
$serviceSortOrder = '';
$errorMessage = '';
$successMessage = '';

require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../Controller/MonModuleController.php';

try {
    $model = new MonModule($pdo);
    $controller = new MonModuleController($model);

    // ✅ Marquer notification comme lue
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marquer_lu'])) {
        $controller->marquerNotifLue((int)$_POST['notif_lu_id']);
    }

    $exportType = trim($_GET['export_stats'] ?? '');
    $stats = [];
    $serviceCountsByGarage = [];

    if ($exportType !== '') {
        $stats = $controller->getStatistics();
        $serviceCountsByGarage = $controller->getServiceCountsByGarage();

        if ($exportType === 'excel') {
            header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
            header('Content-Disposition: attachment; filename="statistiques_garages_services.xls"');
            echo "\xEF\xBB\xBF";
            echo "Clé\tValeur\n";
            echo "Total garages\t{$stats['total_garages']}\n";
            echo "Total services\t{$stats['total_services']}\n";
            echo "Prix moyen des services\t" . number_format($stats['average_service_price'], 2, '.', ',') . "\n\n";
            echo "ID Garage\tNom Garage\tNombre de services\n";
            foreach ($serviceCountsByGarage as $row) {
                echo htmlspecialchars($row['id_garage'], ENT_QUOTES, 'UTF-8') . "\t" . htmlspecialchars($row['nom_garage'], ENT_QUOTES, 'UTF-8') . "\t" . htmlspecialchars($row['service_count'], ENT_QUOTES, 'UTF-8') . "\n";
            }
            exit;
        }

        if ($exportType === 'pdf') {
            echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8"><title>Statistiques PDF</title><style>body{font-family:Arial,sans-serif;background:#f6f7fb;color:#111;margin:0;padding:20px;}h1,h2{margin:0 0 16px;}table{width:100%;border-collapse:collapse;margin-top:16px;}td,th{border:1px solid #ddd;padding:12px;text-align:left;}th{background:#111827;color:#fff;}</style></head><body>';
            echo '<h1>Statistiques des garages et services</h1>';
            echo '<p><strong>Total garages :</strong> ' . $stats['total_garages'] . '</p>';
            echo '<p><strong>Total services :</strong> ' . $stats['total_services'] . '</p>';
            echo '<p><strong>Prix moyen des services :</strong> ' . number_format($stats['average_service_price'], 2, '.', ',') . ' DT</p>';
            echo '<table><thead><tr><th>ID Garage</th><th>Nom Garage</th><th>Nombre de services</th></tr></thead><tbody>';
            foreach ($serviceCountsByGarage as $row) {
                echo '<tr><td>' . htmlspecialchars($row['id_garage'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($row['nom_garage'], ENT_QUOTES, 'UTF-8') . '</td><td>' . htmlspecialchars($row['service_count'], ENT_QUOTES, 'UTF-8') . '</td></tr>';
            }
            echo '</tbody></table>';
            echo '<script>window.print();</script>';
            echo '</body></html>';
            exit;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_garage'])) {
        $result = $controller->ajouter($_POST);
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_garage'])) {
        $result = $controller->supprimer($_POST['delete_nom'] ?? '');
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_edit'])) {
        $edit_nom = trim($_POST['edit_nom'] ?? '');

        if (empty($edit_nom)) {
            $errorMessage = 'Nom Garage à modifier est requis.';
        } elseif (strlen($edit_nom) > 100) {
            $errorMessage = 'Le nom du garage ne doit pas dépasser 100 caractères.';
        } else {
            $editGarage = $controller->getGarageByName($edit_nom);
            if (!$editGarage) {
                $errorMessage = 'Aucun garage trouvé avec ce nom.';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_garage'])) {
        $result = $controller->modifier($_POST);
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];

        if ($errorMessage) {
            $editGarage = [
                'id_garage' => trim($_POST['id_garage'] ?? ''),
                'id_responsable' => trim($_POST['id_responsable'] ?? ''),
                'nom_garage' => trim($_POST['nom_garage'] ?? ''),
                'adresse' => trim($_POST['adresse'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'telephone' => trim($_POST['telephone'] ?? ''),
                'heure_ouv' => trim($_POST['heure_ouv'] ?? ''),
                'heure_fer' => trim($_POST['heure_fer'] ?? ''),
            ];
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
        $result = $controller->ajouter_s($_POST);
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_service'])) {
        $result = $controller->supprimer_s($_POST['delete_service_id'] ?? '');
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_garage'])) {
        $garageSearchTerm = trim($_POST['search_garage_name'] ?? '');
        if ($garageSearchTerm === '') {
            $errorMessage = 'Veuillez saisir un nom de garage à rechercher.';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_service'])) {
        $serviceSearchTerm = trim($_POST['search_service_name'] ?? '');
        if ($serviceSearchTerm === '') {
            $errorMessage = 'Veuillez saisir un nom de service à rechercher.';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sort_service'])) {
        $serviceSortOrder = strtoupper(trim($_POST['sort_service'] ?? 'ASC'));
        if ($serviceSortOrder !== 'ASC' && $serviceSortOrder !== 'DESC') {
            $serviceSortOrder = 'ASC';
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_garage_search'])) {
        $garageSearchTerm = '';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_service_search'])) {
        $serviceSearchTerm = '';
        $serviceSortOrder = '';
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_edit_service'])) {
        $edit_id = trim($_POST['edit_service_id'] ?? '');

        if (empty($edit_id)) {
            $errorMessage = 'ID Service à modifier est requis.';
        } elseif (strlen($edit_id) > 5) {
            $errorMessage = 'L\'ID Service ne doit pas dépasser 5 caractères.';
        } else {
            $editService = $controller->getServiceById($edit_id);
            if (!$editService) {
                $errorMessage = 'Aucun service trouvé avec cet ID.';
            }
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_service'])) {
        $result = $controller->modifier_s($_POST);
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];

        if ($errorMessage) {
            $editService = [
                'id_service' => trim($_POST['id_service'] ?? ''),
                'id_garage' => trim($_POST['id_garage'] ?? ''),
                'nom_service' => trim($_POST['nom_service'] ?? ''),
                'prix' => trim($_POST['prix'] ?? ''),
            ];
        }
    }

    $garages = $garageSearchTerm !== '' ? $controller->searchGaragesByName($garageSearchTerm) : $controller->getGarages();
    $openGarages = $controller->getOpenGarages();
    if ($serviceSearchTerm !== '') {
        $services = $controller->searchServicesByName($serviceSearchTerm);
    } elseif ($serviceSortOrder !== '') {
        $services = $controller->getServicesSortedByPrice($serviceSortOrder);
    } else {
        $services = $controller->getServices();
    }

    $stats = $controller->getStatistics();
    $serviceCountsByGarage = $controller->getServiceCountsByGarage();
    $notifications = $controller->getNotifications();
} catch (PDOException $e) {
    $errorMessage = 'Erreur base de données : ' . $e->getMessage();
}


if (empty($garages)) {
    $garages[] = [
        'id_garage' => 'Aucun enregistrement',
        'id_responsable' => '-',
        'nom_garage' => '-',
        'adresse' => '-',
        'email' => '-',
        'telephone' => '-',
        'heure_ouv' => '-',
        'heure_fer' => '-',
    ];
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Garage Expert | Dashboard</title>
    <link rel="stylesheet" href="assets/css/theme">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --dasher-bg: #0f172a;
            --dasher-sidebar: #1e293b;
            --dasher-border: #334155;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--dasher-bg);
            color: #f8fafc;
            margin: 0;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: var(--dasher-sidebar);
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            border-right: 1px solid var(--dasher-border);
            padding: 20px;
        }
        .sidebar h3 { margin-bottom: 30px; color: #10b981; }
        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #94a3b8;
            text-decoration: none;
            cursor: pointer;
        }
        .sidebar nav a.active {
            color: #fff;
            font-weight: 600;
        }
        .main-content {
            margin-left: 250px;
            padding: 40px;
            width: calc(100% - 250px);
            min-height: 100vh;
        }
        .welcome-card, .garage-card {
            background: #111827;
            border: 1px solid var(--dasher-border);
            border-radius: 16px;
            padding: 32px;
            margin-bottom: 24px;
        }
        .welcome-card { background: linear-gradient(90deg, #d4145a 0%, #fbb03b 100%); color: #0f172a; }
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            color: #e2e8f0;
        }
        thead th {
            text-align: left;
            padding: 14px 12px;
            border-bottom: 1px solid var(--dasher-border);
            font-weight: 700;
        }
        tbody tr { border-bottom: 1px solid var(--dasher-border); }
        tbody td {
            padding: 14px 12px;
            vertical-align: middle;
        }
        tbody tr:hover { background: rgba(255, 255, 255, 0.04); }
        .row-actions { display: flex; gap: 10px; }
        .row-actions button {
            border: none;
            background: transparent;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            font-size: 1.1rem;
        }
        .error-message {
            margin-bottom: 20px;
            padding: 16px;
            background: rgba(239, 68, 68, 0.14);
            color: #fecaca;
            border: 1px solid rgba(239, 68, 68, 0.3);
            border-radius: 12px;
        }
        .success-message {
            margin-bottom: 20px;
            padding: 16px;
            background: rgba(16, 185, 129, 0.14);
            color: #a7f3d0;
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 12px;
        }
        /* FORCE OVERRIDE */
#garage-card .toolbar button,
#service-card .toolbar button {
    border: 2px solid transparent !important;
    border-radius: 10px !important;
    padding: 12px 20px !important;
    color: #ffffff !important;
    font-family: 'Montserrat', sans-serif !important;
    font-weight: 700 !important;
    font-size: 13px !important;
    letter-spacing: 1.5px !important;
    background-color: #64748b !important;
}
#garage-card .toolbar button.add,
#service-card .toolbar button.ajouter_s    { background-color: #10b981 !important; }
#garage-card .toolbar button.delete,
#service-card .toolbar button.supprimer_s  { background-color: #ef4444 !important; }
#garage-card .toolbar button.edit,
#service-card .toolbar button.modifier_s   { background-color: #f59e0b !important; }
#garage-card .toolbar button.view          { background-color: #3b82f6 !important; }
#garage-card .toolbar button.service-tab   { background-color: #8b5cf6 !important; }
    </style>
</head>
<body>
    <div class="sidebar">
        <h3>EntreAuTous</h3>
        <nav>
            <a href="#" id="dashboard-nav"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
            <a href="#" class="active" id="garages-nav" onclick="showGarageTab(event)"><i class="bi bi-house-door"></i> Liste Garages</a>
        </nav>
    </div>
    <div class="main-content">
        <div class="welcome-card">
            <h1>Bonjour, Admin 👋</h1>
         <p style="opacity: 0.9;">Voici la page de gestion des garages.</p>
            <div style="display:flex; gap:10px; margin-top:15px;">
                <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/FrontOffice/front.php" style="
                    background:white; color:#d4145a; padding:8px 18px;
                    border-radius:8px; text-decoration:none; font-weight:700; font-size:13px;">
                    🔍 Voir les Garages (Front)
                </a>
                <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php" style="
                    background:rgba(255,255,255,0.2); color:white; padding:8px 18px;
                    border-radius:8px; text-decoration:none; font-weight:700; font-size:13px;">
                     🏠 Retour Accueil
                </a>
            </div>
        </div>

        <!-- ✅ NOTIFICATIONS APERÇU — 3 dernières seulement -->
        <?php
        $nonLues     = array_filter($notifications ?? [], fn($n) => $n['lu'] == 0);
        $recentes    = array_slice($notifications ?? [], 0, 3);
        $totalNonLu  = count($nonLues);
        ?>
        <div style="background:#111827; border:1px solid #334155; border-radius:16px; padding:24px; margin-bottom:24px;">

            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <h2 style="margin:0; color:#fff;">🔔 Notifications récentes</h2>
                    <?php if($totalNonLu > 0): ?>
                        <span style="background:#ef4444; color:white; border-radius:50%; padding:2px 10px; font-size:13px; font-weight:700;">
                            <?= $totalNonLu ?>
                        </span>
                    <?php endif; ?>
                </div>
                <a href="notifications.php" style="
                    padding:8px 16px; background:#6c63ff; color:#fff;
                    border-radius:8px; text-decoration:none; font-size:13px; font-weight:600;">
                    Voir tout l'historique →
                </a>
            </div>

            <?php if(empty($recentes)): ?>
                <p style="color:#64748b;">Aucune notification pour le moment.</p>
            <?php else: ?>
                <?php foreach($recentes as $notif): ?>
                    <?php
                        $couleur = match($notif['type_action']) {
                            'AJOUT'        => '#10b981',
                            'SUPPRESSION'  => '#ef4444',
                            'MODIFICATION' => '#f59e0b',
                            default        => '#64748b'
                        };
                        $icone = match($notif['type_action']) {
                            'AJOUT'        => '✅',
                            'SUPPRESSION'  => '🗑️',
                            'MODIFICATION' => '✏️',
                            default        => '🔔'
                        };
                    ?>
                    <div style="
                        display:flex; align-items:center; justify-content:space-between;
                        padding:12px 16px; margin-bottom:8px; border-radius:10px;
                        background:<?= $notif['lu'] ? '#0f172a' : '#1e293b' ?>;
                        border-left:4px solid <?= $couleur ?>;
                        opacity:<?= $notif['lu'] ? '0.6' : '1' ?>;">
                        <div>
                            <span style="margin-right:8px;"><?= $icone ?></span>
                            <strong style="color:<?= $couleur ?>;"><?= htmlspecialchars($notif['type_action']) ?></strong>
                            <span style="color:#94a3b8; margin:0 6px;">—</span>
                            <span style="color:#e2e8f0;"><?= htmlspecialchars($notif['message']) ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:12px; flex-shrink:0;">
                            <small style="color:#64748b;"><?= $notif['date_notif'] ?></small>
                            <?php if(!$notif['lu']): ?>
                                <form method="post" action="" style="margin:0;">
                                    <input type="hidden" name="notif_lu_id" value="<?= $notif['id'] ?>">
                                    <button type="submit" name="marquer_lu" style="padding:4px 10px; background:#334155; color:#94a3b8; border:none; border-radius:6px; cursor:pointer; font-size:12px;">Lu</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <?php if(count($notifications) > 3): ?>
                    <div style="text-align:center; margin-top:12px;">
                        <a href="notifications.php" style="color:#6c63ff; font-size:13px; text-decoration:none;">
                            + <?= count($notifications) - 3 ?> autres notifications dans l'historique
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

<div class="garage-card" id="garage-card" style="display: none; margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
        <div class="toolbar" style="flex: 1 1 auto; min-width: 360px; display: flex; flex-wrap: wrap; gap: 12px;">
            <button type="button" class="add">AJOUTER</button>
            <button type="button" class="delete">SUPPRIMER</button>
            <button type="button" class="edit">MODIFIER</button>
            <button type="button" class="view">AFFICHER</button>
            <button type="button" class="service-tab" id="service-tab-btn" onclick="showServiceTab(event)">SERVICES</button>
            <button type="button" onclick="document.getElementById('modal-comparaison').style.display='flex'"
    style="background:linear-gradient(135deg,#8b5cf6,#ec4899) !important; color:white; border:none;
    padding:12px 20px; border-radius:10px; cursor:pointer; font-weight:700; font-size:13px; letter-spacing:1px;">
    🔍 COMPARER
</button>
        </div>
        <form method="post" action="" style="display: flex; align-items: center; gap: 10px;" id="search-garage-form">
            <input type="text" name="search_garage_name" placeholder="Rechercher Nom Garage" value="<?php echo htmlspecialchars($garageSearchTerm, ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0; min-width: 220px;">
            <button type="submit" name="search_garage" style="padding: 12px 20px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Recherche</button>
            <button type="submit" name="reset_garage_search" title="Retour à la liste" style="padding: 12px 14px; background: #64748b; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">
                <i class="bi bi-arrow-counterclockwise"></i>
            </button>
        </form>
    </div>
            <form method="post" action="" style="margin-bottom: 20px; display: none;" id="add-form">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <input type="text" name="nom_garage" placeholder="Nom Garage" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="adresse" placeholder="Adresse" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="email" name="email" placeholder="Email" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="telephone" placeholder="Téléphone" maxlength="8" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="time" name="heure_ouv" placeholder="Heure Ouverture" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="time" name="heure_fer" placeholder="Heure Fermeture" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                </div>
                <button type="submit" name="add_garage" style="padding: 12px 24px; background: #10b981; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Ajouter Garage</button>
            </form>
            <form method="post" action="" style="margin-bottom: 20px; display: none;" id="delete-form">
                <div style="display: grid; grid-template-columns: minmax(200px, 1fr); gap: 16px; margin-bottom: 16px; max-width: 420px;">
                    <input type="text" name="delete_nom" placeholder="Nom Garage à supprimer" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                </div>
                <button type="submit" name="delete_garage" style="padding: 12px 24px; background: #ef4444; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Supprimer Garage</button>
            </form>
            <form method="post" action="" style="margin-bottom: 20px; display: none;" id="edit-load-form">
                <div style="display: grid; grid-template-columns: minmax(200px, 1fr); gap: 16px; margin-bottom: 16px; max-width: 420px;">
                    <input type="text" name="edit_nom" placeholder="Nom Garage à modifier" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                </div>
                <button type="submit" name="load_edit" style="padding: 12px 24px; background: #f59e0b; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Charger Garage</button>
            </form>
            <form method="post" action="" style="margin-bottom: 20px; display: <?php echo $editGarage ? 'block' : 'none'; ?>;" id="edit-form">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <input type="text" name="nom_garage" value="<?php echo $editGarage ? htmlspecialchars($editGarage['nom_garage'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Nom Garage" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="adresse" value="<?php echo $editGarage ? htmlspecialchars($editGarage['adresse'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Adresse" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="email" name="email" value="<?php echo $editGarage ? htmlspecialchars($editGarage['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Email" maxlength="100" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="telephone" value="<?php echo $editGarage ? htmlspecialchars($editGarage['telephone'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Téléphone" maxlength="8" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="time" name="heure_ouv" value="<?php echo $editGarage ? htmlspecialchars($editGarage['heure_ouv'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Heure Ouverture" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="time" name="heure_fer" value="<?php echo $editGarage ? htmlspecialchars($editGarage['heure_fer'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Heure Fermeture" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                </div>
                <button type="submit" name="edit_garage" style="padding: 12px 24px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Modifier Garage</button>
            </form>
            <?php if ($successMessage): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <?php if ($errorMessage): ?>
                <div class="error-message"><?php echo nl2br(htmlspecialchars($errorMessage, ENT_QUOTES, 'UTF-8')); ?></div>
            <?php endif; ?>
            <div id="garage-table-wrapper">
                <table id="garage-table">
                    <thead>
                        <tr>
                            <th>Nom Garage</th>
                            <th>Adresse</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Heure Ouv</th>
                            <th>Heure Fer</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($garages as $garage): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($garage['nom_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($garage['adresse'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($garage['email'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($garage['telephone'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($garage['heure_ouv'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($garage['heure_fer'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="row-actions">
                                <button title="Modifier"><i class="bi bi-pencil-square"></i></button>
                                <button title="Supprimer"><i class="bi bi-trash"></i></button>
                                <button title="Afficher"><i class="bi bi-eye"></i></button>
                                <button title="Résumé IA" onclick="genererResume('<?php echo htmlspecialchars($garage['id_garage'], ENT_QUOTES, 'UTF-8'); ?>')" style="background:#6c63ff; color:white; border:none; padding:5px 8px; border-radius:6px; cursor:pointer;">🤖</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="pagination-controls" style="display: flex; align-items: center; justify-content: space-between; margin-top: 12px; gap: 12px;">
                <button type="button" id="garage-prev" style="padding: 10px 16px; background: #64748b; color: #fff; border: none; border-radius: 8px; cursor: pointer;">Précédent</button>
                <span id="garage-page-info" style="color: #cbd5e1;">Page 1 / 1</span>
                <button type="button" id="garage-next" style="padding: 10px 16px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer;">Suivant</button>
            </div>
            </div>
        </div>

            <div class="open-garage-card" id="open-section" style="display: none; margin-top: 24px;">
                <h2 style="margin-bottom: 16px; color: #fff;">Garages ouverts maintenant</h2>
                <table>
                    <thead>
                        <tr>
                            <th>ID Garage</th>
                            <th>Nom Garage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($openGarages)): ?>
                            <tr>
                                <td colspan="2">Aucun garage ouvert en ce moment.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($openGarages as $garage): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($garage['id_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                    <td><?php echo htmlspecialchars($garage['nom_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <div style="margin-top: 16px;">
                    <button class="btn btn-secondary" id="close-open-section" style="padding: 12px 18px; border:none; border-radius: 10px; cursor:pointer;">Retour à la liste</button>
                </div>
            </div>

            <div class="garage-card" id="service-card" style="display: none; margin-top: 24px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h2 style="margin: 0; color: #fff;">Liste Services</h2>
        <button type="button" id="back-to-garage" onclick="showGarageTab(event)" style="padding: 10px 16px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer;">Retour Garages</button>
    </div>
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; margin-bottom: 16px;">
        <div class="toolbar" style="flex: 1 1 auto; min-width: 360px; display: flex; flex-wrap: wrap; gap: 12px;">
            <button class="ajouter_s">AJOUTER_S</button>
            <button class="supprimer_s">SUPPRIMER_S</button>
            <button class="modifier_s">MODIFIER_S</button>
            <button type="button" class="statistiques_s" id="show-stats-button" style="background: #8b5cf6;">STATISTIQUES</button>
        </div>
        <div id="stats-modal" style="display: none; position: fixed; inset: 0; background: rgba(0, 0, 0, 0.6); z-index: 999; align-items: center; justify-content: center;">
            <div style="background: #0f172a; border-radius: 20px; padding: 24px; width: min(900px, 95%); max-height: 90vh; overflow-y: auto; position: relative; color: #f8fafc;">
                <button id="close-stats-modal" type="button" style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; color: #f8fafc; font-size: 1.4rem; cursor: pointer;">×</button>
                <h2 style="margin-top: 0;">Statistiques</h2>
                <p style="margin-bottom: 12px; color: #cbd5e1;"><strong>Total garages :</strong> <?php echo isset($stats['total_garages']) ? intval($stats['total_garages']) : 0; ?></p>
                <p style="margin-bottom: 12px; color: #cbd5e1;"><strong>Total services :</strong> <?php echo isset($stats['total_services']) ? intval($stats['total_services']) : 0; ?></p>
                <p style="margin-bottom: 16px; color: #cbd5e1;"><strong>Prix moyen des services :</strong> <?php echo isset($stats['average_service_price']) ? number_format((float) $stats['average_service_price'], 2, '.', ',') : '0.00'; ?> DT</p>
                <!-- GRAPHIQUE CHART.JS -->
                <canvas id="statsChart" style="max-height:220px; margin:20px 0;"></canvas>
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                    <thead>
                        <tr style="background: #111827; color: #fff;">
                            <th style="padding: 12px; border: 1px solid #334155; text-align: left;">ID Garage</th>
                            <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Nom Garage</th>
                            <th style="padding: 12px; border: 1px solid #334155; text-align: left;">Nombre services</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($serviceCountsByGarage as $row): ?>
                            <tr>
                                <td style="padding: 12px; border: 1px solid #334155;"><?php echo htmlspecialchars($row['id_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="padding: 12px; border: 1px solid #334155;"><?php echo htmlspecialchars($row['nom_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                                <td style="padding: 12px; border: 1px solid #334155;"><?php echo htmlspecialchars($row['service_count'], ENT_QUOTES, 'UTF-8'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                    <a href="?export_stats=excel" style="padding: 12px 18px; background: #10b981; color: #fff; text-decoration: none; border-radius: 10px;">Export Excel</a>
                    <a href="?export_stats=pdf" style="padding: 12px 18px; background: #3b82f6; color: #fff; text-decoration: none; border-radius: 10px;">Export PDF</a>
                </div>
            </div>
        </div>
        <form method="post" action="" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;" id="search-service-form">
            <input type="text" name="search_service_name" placeholder="Rechercher Nom Service" value="<?php echo htmlspecialchars($serviceSearchTerm, ENT_QUOTES, 'UTF-8'); ?>" maxlength="100" style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0; min-width: 220px;">
            <button type="submit" name="search_service" style="padding: 12px 20px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Recherche</button>
            <button type="submit" name="sort_service" value="ASC" style="padding: 12px 20px; background: #10b981; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Trier prix ↑</button>
            <button type="submit" name="sort_service" value="DESC" style="padding: 12px 20px; background: #f59e0b; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Trier prix ↓</button>
            <button type="submit" name="reset_service_search" title="Retour à la liste des services" style="width: 44px; height: 44px; display: inline-flex; align-items: center; justify-content: center; background: #64748b; color: #fff; border: none; border-radius: 8px; cursor: pointer;">
                <i class="bi bi-arrow-counterclockwise" style="font-size: 1.1rem;"></i>
            </button>
        </form>
    </div>
    <form method="post" action="" style="margin-bottom: 20px; display: none;" id="add-service-form">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <input type="text" name="id_garage" placeholder="ID Garage" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
            <input type="text" name="nom_service" placeholder="Nom Service" maxlength="200" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
            <input type="text" name="prix" placeholder="Prix" maxlength="10" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
        </div>
        <button type="submit" name="add_service" style="padding: 12px 24px; background: #10b981; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Ajouter Service</button>
    </form>
    <form method="post" action="" style="margin-bottom: 20px; display: none;" id="delete-service-form">
        <div style="display: grid; grid-template-columns: minmax(200px, 1fr); gap: 16px; margin-bottom: 16px; max-width: 420px;">
            <input type="text" name="delete_service_id" placeholder="ID Service à supprimer" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
        </div>
        <button type="submit" name="delete_service" style="padding: 12px 24px; background: #ef4444; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Supprimer Service</button>
    </form>
    <form method="post" action="" style="margin-bottom: 20px; display: none;" id="edit-load-service-form">
        <div style="display: grid; grid-template-columns: minmax(200px, 1fr); gap: 16px; margin-bottom: 16px; max-width: 420px;">
            <input type="text" name="edit_service_id" placeholder="ID Service à modifier" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
        </div>
        <button type="submit" name="load_edit_service" style="padding: 12px 24px; background: #f59e0b; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Charger Service</button>
    </form>
    <form method="post" action="" style="margin-bottom: 20px; display: <?php echo $editService ? 'block' : 'none'; ?>;" id="edit-service-form">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <input type="text" name="id_service" value="<?php echo $editService ? htmlspecialchars($editService['id_service'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly placeholder="ID Service" maxlength="5" style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #0f172a; color: #e2e8f0;">
            <input type="text" name="id_garage" value="<?php echo $editService ? htmlspecialchars($editService['id_garage'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="ID Garage" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
            <input type="text" name="nom_service" value="<?php echo $editService ? htmlspecialchars($editService['nom_service'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Nom Service" maxlength="200" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
            <input type="text" name="prix" value="<?php echo $editService ? htmlspecialchars($editService['prix'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Prix" maxlength="10" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
        </div>
        <button type="submit" name="edit_service" style="padding: 12px 24px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Modifier Service</button>
    </form>

    <!-- TABLEAU CORRIGÉ -->
    <div id="service-table-wrapper" style="overflow-x: auto; width: 100%; margin-top: 16px;">
        <table id="service-table" style="width: 100%; border-collapse: collapse; color: #e2e8f0;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; border: 1px solid #374151; background: #1f2937; text-align: left; font-weight: 600; color: #93c5fd;">Nom Service</th>
                    <th style="padding: 12px 16px; border: 1px solid #374151; background: #1f2937; text-align: left; font-weight: 600; color: #93c5fd;">Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="3" style="padding: 12px 16px; border: 1px solid #374151; text-align: center; color: #9ca3af;">Aucun service disponible.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <tr style="background: #111827;" onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">
                            <td style="padding: 12px 16px; border: 1px solid #374151;"><?php echo htmlspecialchars($service['nom_service'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 12px 16px; border: 1px solid #374151;"><?php echo htmlspecialchars($service['prix'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <div class="pagination-controls" style="display: flex; align-items: center; justify-content: space-between; margin-top: 12px; gap: 12px;">
            <button type="button" id="service-prev" style="padding: 10px 16px; background: #64748b; color: #fff; border: none; border-radius: 8px; cursor: pointer;">Précédent</button>
            <span id="service-page-info" style="color: #cbd5e1;">Page 1 / 1</span>
            <button type="button" id="service-next" style="padding: 10px 16px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer;">Suivant</button>
        </div>
    </div>
</div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const garagesNav = document.getElementById('garages-nav');
            const garageCard = document.getElementById('garage-card');
            const serviceCard = document.getElementById('service-card');
            const openSection = document.getElementById('open-section');
            const garageTableWrapper = document.getElementById('garage-table-wrapper');
            const addServiceForm = document.getElementById('add-service-form');
            const deleteServiceForm = document.getElementById('delete-service-form');
            const editLoadServiceForm = document.getElementById('edit-load-service-form');
            const editServiceForm = document.getElementById('edit-service-form');

            function activateTab(tab) {
                if (garagesNav) garagesNav.classList.remove('active');
                if (tab) tab.classList.add('active');
            }

            function hideAllServiceForms() {
                if (addServiceForm) addServiceForm.style.display = 'none';
                if (deleteServiceForm) deleteServiceForm.style.display = 'none';
                if (editLoadServiceForm) editLoadServiceForm.style.display = 'none';
                if (editServiceForm) editServiceForm.style.display = 'none';
            }

            function showGarageTab(event) {
                if (event) event.preventDefault();
                activateTab(garagesNav);
                garageCard.style.display = 'block';
                serviceCard.style.display = 'none';
                openSection.style.display = 'none';
                garageTableWrapper.style.display = 'block';
                hideAllServiceForms();
            }

            function showServiceTab(event) {
    if (event) event.preventDefault();
    activateTab(null);

    // Cacher les autres sections
    const garageCard = document.getElementById('garage-card');
    const openSection = document.getElementById('open-section');
    const garageTableWrapper = document.getElementById('garage-table-wrapper');

    if (garageCard) garageCard.style.display = 'none';
    if (openSection) openSection.style.display = 'none';
    if (garageTableWrapper) garageTableWrapper.style.display = 'none';

    // Afficher le bloc service
    const serviceCard = document.getElementById('service-card');
    if (serviceCard) {
        serviceCard.style.display = 'block';
    } else {
        console.error('service-card introuvable dans le DOM');
    }

    hideAllServiceForms();
}

            function setupPagination(tableId, prevButtonId, nextButtonId, pageInfoId, rowsPerPage = 5) {
                const table = document.getElementById(tableId);
                const prevButton = document.getElementById(prevButtonId);
                const nextButton = document.getElementById(nextButtonId);
                const pageInfo = document.getElementById(pageInfoId);
                if (!table || !prevButton || !nextButton || !pageInfo) return;

                const rows = Array.from(table.querySelectorAll('tbody tr'));
                let currentPage = 1;
                const totalPages = Math.max(1, Math.ceil(rows.length / rowsPerPage));

                const updatePagination = () => {
                    const start = (currentPage - 1) * rowsPerPage;
                    const end = start + rowsPerPage;
                    rows.forEach((row, index) => {
                        row.style.display = index >= start && index < end ? '' : 'none';
                    });
                    pageInfo.textContent = `Page ${currentPage} / ${totalPages}`;
                    prevButton.disabled = currentPage <= 1;
                    nextButton.disabled = currentPage >= totalPages;
                };

                prevButton.addEventListener('click', () => {
                    if (currentPage > 1) {
                        currentPage -= 1;
                        updatePagination();
                    }
                });
                nextButton.addEventListener('click', () => {
                    if (currentPage < totalPages) {
                        currentPage += 1;
                        updatePagination();
                    }
                });

                updatePagination();
            }

            function setupBackStatisticsModal() {
                const showStatsButton = document.getElementById('show-stats-button');
                const statsModal = document.getElementById('stats-modal');
                const closeStatsModal = document.getElementById('close-stats-modal');
                if (!showStatsButton || !statsModal || !closeStatsModal) return;
                showStatsButton.addEventListener('click', () => {
                    statsModal.style.display = 'flex';
                });
                showStatsButton.addEventListener('click', () => {
    statsModal.style.display = 'flex';
    
    // Données PHP vers JS
    const labels = [<?php echo implode(',', array_map(fn($r) => '"' . addslashes($r['nom_garage']) . ' (' . $r['id_garage'] . ')"', $serviceCountsByGarage)); ?>];
    const data = [<?php echo implode(',', array_map(fn($r) => $r['service_count'], $serviceCountsByGarage)); ?>];
    const stats = {
        garages: <?php echo $stats['total_garages'] ?? 0; ?>,
        services: <?php echo $stats['total_services'] ?? 0; ?>,
        prix: <?php echo number_format($stats['average_service_price'] ?? 0, 2, '.', ''); ?>
    };

    // Détruire ancien graphique si existe
    if (window.statsChartInstance) window.statsChartInstance.destroy();

    const ctx = document.getElementById('statsChart').getContext('2d');
    window.statsChartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Nombre de services',
                    data: data,
                    backgroundColor: 'rgba(99, 102, 241, 0.8)',
                    borderRadius: 8,
                },
                {
                    label: 'Référence (total services)',
                    data: labels.map(() => stats.services),
                    type: 'line',
                    borderColor: '#f59e0b',
                    backgroundColor: 'transparent',
                    pointRadius: 0,
                    borderDash: [5, 5],
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#e2e8f0' } }
            },
            scales: {
                x: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' } },
                y: { ticks: { color: '#94a3b8' }, grid: { color: '#1e293b' }, beginAtZero: true }
            }
        }
    });
});
                closeStatsModal.addEventListener('click', () => {
                    statsModal.style.display = 'none';
                });
                statsModal.addEventListener('click', (event) => {
                    if (event.target === statsModal) {
                        statsModal.style.display = 'none';
                    }
                });
            }

            window.showGarageTab = showGarageTab;
            window.showServiceTab = showServiceTab;

            if (garagesNav) {
                garagesNav.addEventListener('click', showGarageTab);
            }

            const addBtn = document.querySelector('.add');
            const deleteBtn = document.querySelector('.delete');
            const editBtn = document.querySelector('.edit');
            const viewBtn = document.querySelector('.view');
            const closeOpenSectionBtn = document.getElementById('close-open-section');

            if (addBtn) {
                addBtn.addEventListener('click', function() {
                    const addForm = document.getElementById('add-form');
                    const deleteForm = document.getElementById('delete-form');
                    const editLoadForm = document.getElementById('edit-load-form');
                    const editForm = document.getElementById('edit-form');
                    if (deleteForm) deleteForm.style.display = 'none';
                    if (editLoadForm) editLoadForm.style.display = 'none';
                    if (editForm) editForm.style.display = 'none';
                    if (addForm) {
                        addForm.style.display = addForm.style.display === 'none' ? 'block' : 'none';
                    }
                });
            }

            if (deleteBtn) {
                deleteBtn.addEventListener('click', function() {
                    const addForm = document.getElementById('add-form');
                    const deleteForm = document.getElementById('delete-form');
                    const editLoadForm = document.getElementById('edit-load-form');
                    const editForm = document.getElementById('edit-form');
                    if (addForm) addForm.style.display = 'none';
                    if (editLoadForm) editLoadForm.style.display = 'none';
                    if (editForm) editForm.style.display = 'none';
                    if (deleteForm) {
                        deleteForm.style.display = deleteForm.style.display === 'none' ? 'block' : 'none';
                    }
                });
            }

            if (editBtn) {
                editBtn.addEventListener('click', function() {
                    const addForm = document.getElementById('add-form');
                    const deleteForm = document.getElementById('delete-form');
                    const editLoadForm = document.getElementById('edit-load-form');
                    const editForm = document.getElementById('edit-form');
                    if (addForm) addForm.style.display = 'none';
                    if (deleteForm) deleteForm.style.display = 'none';
                    if (openSection) openSection.style.display = 'none';
                    if (editLoadForm) {
                        editLoadForm.style.display = editLoadForm.style.display === 'none' ? 'block' : 'none';
                    }
                    if (editForm && !<?php echo $editGarage ? 'true' : 'false'; ?>) {
                        editForm.style.display = 'none';
                    }
                });
            }

            if (viewBtn) {
                viewBtn.addEventListener('click', function() {
                    const addForm = document.getElementById('add-form');
                    const deleteForm = document.getElementById('delete-form');
                    const editLoadForm = document.getElementById('edit-load-form');
                    const editForm = document.getElementById('edit-form');
                    if (addForm) addForm.style.display = 'none';
                    if (deleteForm) deleteForm.style.display = 'none';
                    if (editLoadForm) editLoadForm.style.display = 'none';
                    if (editForm) editForm.style.display = 'none';
                    if (garageTableWrapper) garageTableWrapper.style.display = 'none';
                    if (openSection) openSection.style.display = 'block';
                });
            }

            if (closeOpenSectionBtn) {
                closeOpenSectionBtn.addEventListener('click', function() {
                    if (openSection) openSection.style.display = 'none';
                    if (garageTableWrapper) garageTableWrapper.style.display = 'block';
                });
            }

            const serviceTabBtn = document.getElementById('service-tab-btn');
            const backToGarageBtn = document.getElementById('back-to-garage');
            const addServiceBtn = document.querySelector('.ajouter_s');
            const deleteServiceBtn = document.querySelector('.supprimer_s');
            const editServiceBtn = document.querySelector('.modifier_s');

            if (serviceTabBtn) {
                serviceTabBtn.addEventListener('click', showServiceTab);
            }

            if (backToGarageBtn) {
                backToGarageBtn.addEventListener('click', showGarageTab);
            }

            if (addServiceBtn) {
                addServiceBtn.addEventListener('click', function() {
                    if (addServiceForm) addServiceForm.style.display = 'block';
                    if (deleteServiceForm) deleteServiceForm.style.display = 'none';
                    if (editLoadServiceForm) editLoadServiceForm.style.display = 'none';
                    if (editServiceForm) editServiceForm.style.display = 'none';
                });
            }

            if (deleteServiceBtn) {
                deleteServiceBtn.addEventListener('click', function() {
                    if (addServiceForm) addServiceForm.style.display = 'none';
                    if (deleteServiceForm) deleteServiceForm.style.display = 'block';
                    if (editLoadServiceForm) editLoadServiceForm.style.display = 'none';
                    if (editServiceForm) editServiceForm.style.display = 'none';
                });
            }

            if (editServiceBtn) {
                editServiceBtn.addEventListener('click', function() {
                    if (addServiceForm) addServiceForm.style.display = 'none';
                    if (deleteServiceForm) deleteServiceForm.style.display = 'none';
                    if (editLoadServiceForm) editLoadServiceForm.style.display = 'block';
                    if (editServiceForm && !<?php echo $editService ? 'true' : 'false'; ?>) {
                        editServiceForm.style.display = 'none';
                    }
                });
            }

            setupPagination('garage-table', 'garage-prev', 'garage-next', 'garage-page-info', 5);
            setupPagination('service-table', 'service-prev', 'service-next', 'service-page-info', 5);
            setupBackStatisticsModal();

            showGarageTab();
            <?php if (isset($_POST['load_edit_service']) || isset($_POST['edit_service']) || isset($_POST['add_service']) || isset($_POST['delete_service']) || isset($_POST['search_service']) || isset($_POST['sort_service']) || isset($_POST['reset_service_search'])): ?>
            showServiceTab();
            <?php if ($editService): ?>
                if (editLoadServiceForm) editLoadServiceForm.style.display = 'block';
                if (editServiceForm) editServiceForm.style.display = 'block';
            <?php elseif (isset($_POST['add_service'])): ?>
                if (addServiceForm) addServiceForm.style.display = 'block';
            <?php elseif (isset($_POST['delete_service'])): ?>
                if (deleteServiceForm) deleteServiceForm.style.display = 'block';
            <?php endif; ?>
        <?php else: ?>
            showGarageTab();
        <?php endif; ?>

    });
</script>

<!-- MODAL RESUME IA -->
<div id="modal-resume" style="
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%; background:rgba(0,0,0,0.7);
    z-index:99999; align-items:center; justify-content:center;">
    
    <div style="
        background:#1e293b; border-radius:20px; padding:30px;
        max-width:500px; width:90%; border:1px solid #334155;
        box-shadow:0 20px 60px rgba(0,0,0,0.5);">
        
        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h3 style="color:white; margin:0;">🤖 Résumé IA du Garage</h3>
            <button onclick="fermerModal()" style="
                background:#ef4444; color:white; border:none;
                width:30px; height:30px; border-radius:50%;
                cursor:pointer; font-size:16px;">✕</button>
        </div>

        <!-- Contenu -->
        <div id="resume-contenu" style="
            background:#0f172a; border-radius:12px; padding:20px;
            color:#e2e8f0; font-size:15px; line-height:1.7;
            min-height:80px;">
            <div id="resume-loading" style="color:#94a3b8; text-align:center;">
                ✍️ Génération en cours...
            </div>
            <div id="resume-texte" style="display:none;"></div>
        </div>

        <!-- Footer -->
        <div style="margin-top:15px; text-align:right;">
            <button onclick="fermerModal()" style="
                background:#6c63ff; color:white; border:none;
                padding:10px 25px; border-radius:8px; cursor:pointer;">
                Fermer
            </button>
        </div>
    </div>
</div>

<script>
function genererResume(idGarage) {
    // Ouvrir le modal
    const modal = document.getElementById('modal-resume');
    modal.style.display = 'flex';
    document.getElementById('resume-loading').style.display = 'block';
    document.getElementById('resume-texte').style.display = 'none';

    // Appel au fichier resume.php
    fetch('/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/resume.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id_garage: idGarage })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('resume-loading').style.display = 'none';
        const texte = document.getElementById('resume-texte');
        texte.style.display = 'block';
        
        if (data.resume) {
            texte.innerHTML = '📝 ' + data.resume.replace(/\n/g, '<br>');
        } else {
            texte.innerHTML = '❌ ' + (data.error || 'Erreur inconnue');
        }
    })
    .catch(() => {
        document.getElementById('resume-loading').style.display = 'none';
        document.getElementById('resume-texte').style.display = 'block';
        document.getElementById('resume-texte').innerHTML = '❌ Erreur de connexion';
    });
}

function fermerModal() {
    document.getElementById('modal-resume').style.display = 'none';
}

// Fermer en cliquant dehors
document.getElementById('modal-resume').addEventListener('click', function(e) {
    if (e.target === this) fermerModal();
});
</script>

<!-- MODAL COMPARAISON IA -->
<div id="modal-comparaison" style="
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%; background:rgba(0,0,0,0.8);
    z-index:99999; align-items:center; justify-content:center;">

    <div style="
        background:#0f172a; border-radius:20px; padding:30px;
        max-width:750px; width:95%; max-height:90vh; overflow-y:auto;
        border:1px solid #334155; box-shadow:0 20px 60px rgba(0,0,0,0.6);">

        <!-- Header -->
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <div>
                <h3 style="color:white; margin:0;">🔍 Comparaison IA de Garages</h3>
                <p style="color:#94a3b8; font-size:13px; margin:5px 0 0;">
                    Sélectionnez 2 garages à comparer
                </p>
            </div>
            <button onclick="document.getElementById('modal-comparaison').style.display='none'" style="
                background:#334155; color:white; border:none;
                width:35px; height:35px; border-radius:50%; cursor:pointer; font-size:16px;">✕
            </button>
        </div>

        <!-- Sélection des 2 garages -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:20px;">
            <div>
                <label style="color:#94a3b8; font-size:13px; display:block; margin-bottom:8px;">
                    🏪 Garage 1
                </label>
                <select id="select-garage1" style="
                    width:100%; padding:10px; border-radius:8px;
                    background:#1e293b; color:white; border:1px solid #334155; font-size:14px;">
                    <option value="">-- Choisir --</option>
                    <?php foreach($controller->getGarages() as $g): ?>
                        <option value="<?= htmlspecialchars($g['nom_garage']) ?>">
                            <?= htmlspecialchars($g['nom_garage']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="color:#94a3b8; font-size:13px; display:block; margin-bottom:8px;">
                    🏪 Garage 2
                </label>
                <select id="select-garage2" style="
                    width:100%; padding:10px; border-radius:8px;
                    background:#1e293b; color:white; border:1px solid #334155; font-size:14px;">
                    <option value="">-- Choisir --</option>
                    <?php foreach($controller->getGarages() as $g): ?>
                        <option value="<?= htmlspecialchars($g['nom_garage']) ?>">
                            <?= htmlspecialchars($g['nom_garage']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Bouton Comparer -->
        <button onclick="lancerComparaison()" style="
            width:100%; background:linear-gradient(135deg,#8b5cf6,#ec4899);
            color:white; border:none; padding:14px; border-radius:10px;
            cursor:pointer; font-weight:700; font-size:15px; margin-bottom:20px;">
            ⚡ Lancer la comparaison IA
        </button>

        <!-- Stats visuelles -->
        <div id="stats-comparaison" style="display:none; margin-bottom:20px;">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                <div id="stat-g1" style="background:#1e293b; border-radius:12px; padding:15px; text-align:center;">
                </div>
                <div id="stat-g2" style="background:#1e293b; border-radius:12px; padding:15px; text-align:center;">
                </div>
            </div>
        </div>

        <!-- Résultat IA -->
        <div id="resultat-comparaison" style="
            background:#0f172a; border:1px solid #334155; border-radius:12px;
            padding:20px; min-height:60px; display:none;">
            <div id="loading-comparaison" style="color:#94a3b8; text-align:center; display:none;">
                ✍️ L'IA analyse les 2 garages...
            </div>
            <div id="texte-comparaison" style="color:#e2e8f0; font-size:14px; line-height:1.8;"></div>
        </div>
    </div>
</div>

<script>
function lancerComparaison() {
    const g1 = document.getElementById('select-garage1').value;
    const g2 = document.getElementById('select-garage2').value;

    if (!g1 || !g2) {
        alert('Veuillez sélectionner 2 garages !');
        return;
    }
    if (g1 === g2) {
        alert('Veuillez sélectionner 2 garages différents !');
        return;
    }

    // Afficher loading
    document.getElementById('resultat-comparaison').style.display = 'block';
    document.getElementById('loading-comparaison').style.display = 'block';
    document.getElementById('texte-comparaison').innerHTML = '';
    document.getElementById('stats-comparaison').style.display = 'none';

    fetch('/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/comparaison.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ garage1: g1, garage2: g2 })
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading-comparaison').style.display = 'none';

        if (data.error) {
            document.getElementById('texte-comparaison').innerHTML =
                '<span style="color:#ef4444;">❌ ' + data.error + '</span>';
            return;
        }

        // Afficher stats
        const s = data.stats;
        document.getElementById('stats-comparaison').style.display = 'block';
        document.getElementById('stat-g1').innerHTML = `
            <div style="color:#8b5cf6; font-weight:700; font-size:15px; margin-bottom:8px;">
                🏪 ${s.garage1.nom}
            </div>
            <div style="color:#e2e8f0; font-size:13px;">📦 ${s.garage1.nb_services} services</div>
            <div style="color:#f59e0b; font-size:13px;">💰 Prix moyen: ${s.garage1.prix_moyen} DT</div>`;
        document.getElementById('stat-g2').innerHTML = `
            <div style="color:#ec4899; font-weight:700; font-size:15px; margin-bottom:8px;">
                🏪 ${s.garage2.nom}
            </div>
            <div style="color:#e2e8f0; font-size:13px;">📦 ${s.garage2.nb_services} services</div>
            <div style="color:#f59e0b; font-size:13px;">💰 Prix moyen: ${s.garage2.prix_moyen} DT</div>`;

        // Afficher rapport IA
        document.getElementById('texte-comparaison').innerHTML =
            '🤖 ' + data.rapport.replace(/\n/g, '<br>');
    })
    .catch(() => {
        document.getElementById('loading-comparaison').style.display = 'none';
        document.getElementById('texte-comparaison').innerHTML =
            '<span style="color:#ef4444;">❌ Erreur de connexion</span>';
    });
}
</script>

</body>
</html>