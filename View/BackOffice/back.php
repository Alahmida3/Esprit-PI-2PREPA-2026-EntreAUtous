<?php
require_once __DIR__ . '/../../config/connexion.php';

$garages = [];
$openGarages = [];
$services = [];
$editGarage = null;
$editService = null;
$errorMessage = '';
$successMessage = '';

require_once __DIR__ . '/../../Controller/MonModuleController.php';

try {
    $pdo = getPDOConnection();
    $model = new MonModule($pdo);
    $controller = new MonModuleController($model);
    

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_garage'])) {
        $result = $controller->ajouter($_POST);
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_garage'])) {
        $result = $controller->supprimer($_POST['delete_id'] ?? '');
        $successMessage = $result['successMessage'];
        $errorMessage = $result['errorMessage'];
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['load_edit'])) {
        $edit_id = trim($_POST['edit_id'] ?? '');

        if (empty($edit_id)) {
            $errorMessage = 'ID Garage à modifier est requis.';
        } elseif (strlen($edit_id) > 5) {
            $errorMessage = 'L\'ID ne doit pas dépasser 5 caractères.';
        } else {
            $editGarage = $controller->getGarageById($edit_id);
            if (!$editGarage) {
                $errorMessage = 'Aucun garage trouvé avec cet ID.';
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

    $garages = $controller->getGarages();   
    $openGarages = $controller->getOpenGarages();
    $services = $controller->getServices();
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
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
        .toolbar button {
            border: none;
            border-radius: 10px;
            padding: 12px 18px;
            color: #fff;
            cursor: pointer;
            font-weight: 600;
        }
        .toolbar button.add { background: #10b981; }
        .toolbar button.delete { background: #ef4444; }
        .toolbar button.edit { background: #f59e0b; }
        .toolbar button.view { background: #3b82f6; }
        .toolbar button.service-tab { background: #8b5cf6; }
        .toolbar button.service-tab:hover { background: #7c3aed; }
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
        </div>
        <div class="garage-card" id="garage-card" style="display: none; margin-top: 24px; >
            <div class="toolbar">
                <button type="button" class="add">AJOUTER</button>
                <button type="button" class="delete">SUPPRIMER</button>
                <button type="button" class="edit">MODIFIER</button>
                <button type="button" class="view">AFFICHER</button>
                <button type="button" class="service-tab" id="service-tab-btn" onclick="showServiceTab(event)">SERVICES</button>
            </div>
            <form method="post" action="" style="margin-bottom: 20px; display: none;" id="add-form">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <input type="text" name="id_garage" placeholder="ID Garage" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="id_responsable" placeholder="ID Responsable" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="nom_garage" placeholder="Nom Garage" maxlength="15" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="adresse" placeholder="Adresse" maxlength="15" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="email" name="email" placeholder="Email" maxlength="15" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="telephone" placeholder="Téléphone" maxlength="8" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="time" name="heure_ouv" placeholder="Heure Ouverture" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="time" name="heure_fer" placeholder="Heure Fermeture" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                </div>
                <button type="submit" name="add_garage" style="padding: 12px 24px; background: #10b981; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Ajouter Garage</button>
            </form>
            <form method="post" action="" style="margin-bottom: 20px; display: none;" id="delete-form">
                <div style="display: grid; grid-template-columns: minmax(200px, 1fr); gap: 16px; margin-bottom: 16px; max-width: 420px;">
                    <input type="text" name="delete_id" placeholder="ID Garage à supprimer" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                </div>
                <button type="submit" name="delete_garage" style="padding: 12px 24px; background: #ef4444; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Supprimer Garage</button>
            </form>
            <form method="post" action="" style="margin-bottom: 20px; display: none;" id="edit-load-form">
                <div style="display: grid; grid-template-columns: minmax(200px, 1fr); gap: 16px; margin-bottom: 16px; max-width: 420px;">
                    <input type="text" name="edit_id" placeholder="ID Garage à modifier" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                </div>
                <button type="submit" name="load_edit" style="padding: 12px 24px; background: #f59e0b; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Charger Garage</button>
            </form>
            <form method="post" action="" style="margin-bottom: 20px; display: <?php echo $editGarage ? 'block' : 'none'; ?>;" id="edit-form">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
                    <input type="text" name="id_garage" value="<?php echo $editGarage ? htmlspecialchars($editGarage['id_garage'], ENT_QUOTES, 'UTF-8') : ''; ?>" readonly placeholder="ID Garage" maxlength="5" style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #0f172a; color: #e2e8f0;">
                    <input type="text" name="id_responsable" value="<?php echo $editGarage ? htmlspecialchars($editGarage['id_responsable'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="ID Responsable" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="nom_garage" value="<?php echo $editGarage ? htmlspecialchars($editGarage['nom_garage'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Nom Garage" maxlength="15" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="text" name="adresse" value="<?php echo $editGarage ? htmlspecialchars($editGarage['adresse'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Adresse" maxlength="15" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
                    <input type="email" name="email" value="<?php echo $editGarage ? htmlspecialchars($editGarage['email'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Email" maxlength="15" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
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
                            <th>ID Garage</th>
                            <th>ID Responsable</th>
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
                            <td><?php echo htmlspecialchars($garage['id_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?php echo htmlspecialchars($garage['id_responsable'], ENT_QUOTES, 'UTF-8'); ?></td>
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
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
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
    <div class="toolbar">
        <button class="ajouter_s">AJOUTER_S</button>
        <button class="supprimer_s">SUPPRIMER_S</button>
        <button class="modifier_s">MODIFIER_S</button>
    </div>
    <form method="post" action="" style="margin-bottom: 20px; display: none;" id="add-service-form">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 16px;">
            <input type="text" name="id_service" placeholder="ID Service" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
            <input type="text" name="id_garage" placeholder="ID Garage" maxlength="5" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
            <input type="text" name="nom_service" placeholder="Nom Service" maxlength="10" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
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
            <input type="text" name="nom_service" value="<?php echo $editService ? htmlspecialchars($editService['nom_service'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Nom Service" maxlength="10" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
            <input type="text" name="prix" value="<?php echo $editService ? htmlspecialchars($editService['prix'], ENT_QUOTES, 'UTF-8') : ''; ?>" placeholder="Prix" maxlength="10" required style="padding: 12px; border: 1px solid var(--dasher-border); border-radius: 8px; background: #111827; color: #e2e8f0;">
        </div>
        <button type="submit" name="edit_service" style="padding: 12px 24px; background: #3b82f6; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Modifier Service</button>
    </form>

    <!-- TABLEAU CORRIGÉ -->
    <div id="service-table-wrapper" style="overflow-x: auto; width: 100%; margin-top: 16px;">
        <table id="service-table" style="width: 100%; border-collapse: collapse; color: #e2e8f0;">
            <thead>
                <tr>
                    <th style="padding: 12px 16px; border: 1px solid #374151; background: #1f2937; text-align: left; font-weight: 600; color: #93c5fd;">ID Service</th>
                    <th style="padding: 12px 16px; border: 1px solid #374151; background: #1f2937; text-align: left; font-weight: 600; color: #93c5fd;">ID Garage</th>
                    <th style="padding: 12px 16px; border: 1px solid #374151; background: #1f2937; text-align: left; font-weight: 600; color: #93c5fd;">Nom Service</th>
                    <th style="padding: 12px 16px; border: 1px solid #374151; background: #1f2937; text-align: left; font-weight: 600; color: #93c5fd;">Prix</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="4" style="padding: 12px 16px; border: 1px solid #374151; text-align: center; color: #9ca3af;">Aucun service disponible.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $service): ?>
                        <tr style="background: #111827;" onmouseover="this.style.background='#1f2937'" onmouseout="this.style.background='#111827'">
                            <td style="padding: 12px 16px; border: 1px solid #374151;"><?php echo htmlspecialchars($service['id_service'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 12px 16px; border: 1px solid #374151;"><?php echo htmlspecialchars($service['id_garage'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 12px 16px; border: 1px solid #374151;"><?php echo htmlspecialchars($service['nom_service'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td style="padding: 12px 16px; border: 1px solid #374151;"><?php echo htmlspecialchars($service['prix'], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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

            showGarageTab();
            <?php if (isset($_POST['load_edit_service']) || isset($_POST['edit_service']) || isset($_POST['add_service']) || isset($_POST['delete_service'])): ?>
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
</body>
</html>
