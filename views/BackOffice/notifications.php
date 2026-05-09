<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../Controller/MonModuleController.php';

$notifications = [];
$successMessage = '';

try {
    
    $model = new MonModule($pdo);
    $controller = new MonModuleController($model);

    // Marquer une notification comme lue
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marquer_lu'])) {
        $controller->marquerNotifLue((int)$_POST['notif_lu_id']);
        $successMessage = 'Notification marquée comme lue.';
    }

    // Marquer toutes comme lues
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tout_marquer_lu'])) {
        $allNotifs = $controller->getNotifications();
        foreach ($allNotifs as $n) {
            if (!$n['lu']) $controller->marquerNotifLue((int)$n['id']);
        }
        $successMessage = 'Toutes les notifications ont été marquées comme lues.';
    }

    $notifications = $controller->getNotifications();

} catch (PDOException $e) {
    $errorMessage = 'Erreur : ' . $e->getMessage();
}

$nonLues = array_filter($notifications, fn($n) => $n['lu'] == 0);

// Grouper par date
$grouped = [];
foreach ($notifications as $n) {
    $date = date('d/m/Y', strtotime($n['date_notif']));
    $grouped[$date][] = $n;
}
?>
<!DOCTYPE html>
<html lang="fr" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historique Notifications | EntreAuTous</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Montserrat:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        :root {
            --bg: #0f172a;
            --sidebar: #1e293b;
            --border: #334155;
            --card: #111827;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: #f8fafc;
            min-height: 100vh;
        }
        .sidebar {
            width: 250px;
            background: var(--sidebar);
            position: fixed;
            top: 0; left: 0; bottom: 0;
            border-right: 1px solid var(--border);
            padding: 20px;
        }
        .sidebar h3 { margin-bottom: 30px; color: #10b981; font-family: 'Montserrat', sans-serif; }
        .sidebar nav a {
            display: flex; align-items: center; gap: 10px;
            margin-bottom: 15px; color: #94a3b8;
            text-decoration: none; font-size: 14px;
            padding: 8px 10px; border-radius: 8px;
        }
        .sidebar nav a:hover { background: rgba(255,255,255,0.05); color: #fff; }
        .sidebar nav a.active { color: #fff; font-weight: 600; background: rgba(108,99,255,0.15); }
        .main { margin-left: 250px; padding: 40px; }

        .page-header {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px 32px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        .stats-row {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 24px;
        }
        .stat-box {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-box .num { font-size: 32px; font-weight: 700; margin-bottom: 4px; }
        .stat-box .lbl { font-size: 13px; color: #94a3b8; }

        .filter-bar {
            display: flex; gap: 10px; flex-wrap: wrap;
            margin-bottom: 24px; align-items: center;
        }
        .filter-bar a {
            padding: 8px 16px; border-radius: 8px; text-decoration: none;
            font-size: 13px; font-weight: 600;
            border: 1px solid var(--border);
            color: #94a3b8; background: var(--card);
            transition: all 0.15s;
        }
        .filter-bar a.active, .filter-bar a:hover {
            background: #6c63ff; color: white; border-color: #6c63ff;
        }

        .date-label {
            font-size: 12px; font-weight: 600; color: #64748b;
            text-transform: uppercase; letter-spacing: 0.06em;
            margin: 20px 0 10px;
        }
        .date-label:first-of-type { margin-top: 0; }

        .notif-item {
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 18px; margin-bottom: 8px; border-radius: 10px;
            border: 1px solid var(--border);
            transition: opacity 0.2s;
        }
        .notif-item.lu { opacity: 0.5; background: #0f172a; }
        .notif-item.nonlu { background: #1e293b; }

        .notif-left { display: flex; align-items: center; gap: 12px; flex: 1; }
        .notif-icon {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; flex-shrink: 0;
        }
        .notif-action { font-weight: 600; font-size: 13px; margin-bottom: 2px; }
        .notif-msg { font-size: 13px; color: #cbd5e1; }
        .notif-time { font-size: 11px; color: #64748b; margin-top: 2px; }

        .notif-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }
        .badge-type {
            font-size: 11px; padding: 3px 10px; border-radius: 20px;
            font-weight: 600;
        }

        .btn-lu {
            padding: 5px 12px; background: var(--card); color: #94a3b8;
            border: 1px solid var(--border); border-radius: 6px;
            cursor: pointer; font-size: 12px;
        }
        .btn-lu:hover { background: #334155; }

        .btn-primary {
            padding: 10px 20px; background: #6c63ff; color: white;
            border: none; border-radius: 8px; cursor: pointer;
            font-size: 13px; font-weight: 600; text-decoration: none;
            display: inline-block;
        }
        .btn-secondary {
            padding: 10px 20px; background: var(--card); color: #94a3b8;
            border: 1px solid var(--border); border-radius: 8px;
            cursor: pointer; font-size: 13px; font-weight: 600;
            text-decoration: none; display: inline-block;
        }
        .success-msg {
            background: rgba(16,185,129,0.14); color: #a7f3d0;
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: 10px; padding: 14px 18px; margin-bottom: 20px;
        }
        .empty { text-align: center; padding: 60px 20px; color: #64748b; }
        .empty i { font-size: 48px; display: block; margin-bottom: 16px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h3>EntreAuTous</h3>
    <nav>
        <a href="back.php"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
        <a href="back.php"><i class="bi bi-house-door"></i> Liste Garages</a>
        <a href="notifications.php" class="active"><i class="bi bi-bell-fill"></i> Notifications
            <?php if(count($nonLues) > 0): ?>
                <span style="background:#ef4444; color:white; border-radius:50%; padding:1px 7px; font-size:11px; margin-left:auto;">
                    <?= count($nonLues) ?>
                </span>
            <?php endif; ?>
        </a>
    </nav>
</div>

<div class="main">

    <!-- Header -->
    <div class="page-header">
        <div>
            <h1 style="font-size:22px; font-weight:700; margin-bottom:4px;">🔔 Historique des notifications</h1>
            <p style="color:#94a3b8; font-size:14px;">Toutes les actions effectuées sur les garages et services</p>
        </div>
        <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <a href="back.php" class="btn-secondary">← Retour au dashboard</a>
            <?php if(count($nonLues) > 0): ?>
                <form method="post" action="" style="margin:0;">
                    <button type="submit" name="tout_marquer_lu" class="btn-primary">
                        ✓ Tout marquer comme lu
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <?php if(!empty($successMessage)): ?>
        <div class="success-msg"><?= htmlspecialchars($successMessage) ?></div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-box">
            <div class="num" style="color:#f8fafc;"><?= count($notifications) ?></div>
            <div class="lbl">Total notifications</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#ef4444;"><?= count($nonLues) ?></div>
            <div class="lbl">Non lues</div>
        </div>
        <div class="stat-box">
            <div class="num" style="color:#10b981;"><?= count($notifications) - count($nonLues) ?></div>
            <div class="lbl">Lues</div>
        </div>
    </div>

    <!-- Filtres -->
    <?php $filtre = $_GET['filtre'] ?? 'tout'; ?>
    <div class="filter-bar">
        <a href="?filtre=tout"       class="<?= $filtre === 'tout'         ? 'active' : '' ?>">Tout</a>
        <a href="?filtre=ajout"      class="<?= $filtre === 'ajout'        ? 'active' : '' ?>">✅ Ajouts</a>
        <a href="?filtre=suppression"class="<?= $filtre === 'suppression'  ? 'active' : '' ?>">🗑️ Suppressions</a>
        <a href="?filtre=modification"class="<?= $filtre === 'modification'? 'active' : '' ?>">✏️ Modifications</a>
        <a href="?filtre=nonlu"      class="<?= $filtre === 'nonlu'        ? 'active' : '' ?>">🔴 Non lues</a>
    </div>

    <!-- Liste groupée par date -->
    <?php
    $affichees = 0;
    foreach ($grouped as $date => $notifsDuJour):
        // Filtrer
        $filtered = array_filter($notifsDuJour, function($n) use ($filtre) {
            if ($filtre === 'tout') return true;
            if ($filtre === 'nonlu') return !$n['lu'];
            return strtolower($n['type_action']) === $filtre;
        });
        if (empty($filtered)) continue;
        $affichees += count($filtered);
    ?>
        <div class="date-label">
            <i class="bi bi-calendar3" style="margin-right:6px;"></i><?= $date ?>
        </div>

        <?php foreach($filtered as $notif):
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
            $bgBadge = match($notif['type_action']) {
                'AJOUT'        => 'rgba(16,185,129,0.15)',
                'SUPPRESSION'  => 'rgba(239,68,68,0.15)',
                'MODIFICATION' => 'rgba(245,158,11,0.15)',
                default        => 'rgba(100,116,139,0.15)'
            };
            $heure = date('H:i', strtotime($notif['date_notif']));
        ?>
        <div class="notif-item <?= $notif['lu'] ? 'lu' : 'nonlu' ?>"
             style="border-left: 3px solid <?= $couleur ?>;">

            <div class="notif-left">
                <div class="notif-icon" style="background:<?= $bgBadge ?>;">
                    <?= $icone ?>
                </div>
                <div>
                    <div class="notif-action" style="color:<?= $couleur ?>;">
                        <?= htmlspecialchars($notif['type_action']) ?>
                        <span style="color:#64748b; font-weight:400; margin-left:6px; font-size:12px;">
                            <?= htmlspecialchars($notif['type_entite']) ?>
                        </span>
                    </div>
                    <div class="notif-msg"><?= htmlspecialchars($notif['message']) ?></div>
                    <div class="notif-time"><i class="bi bi-clock" style="margin-right:4px;"></i><?= $heure ?></div>
                </div>
            </div>

            <div class="notif-right">
                <span class="badge-type" style="background:<?= $bgBadge ?>; color:<?= $couleur ?>;">
                    <?= htmlspecialchars($notif['type_entite']) ?>
                </span>
                <?php if(!$notif['lu']): ?>
                    <form method="post" action="notifications.php" style="margin:0;">
                        <input type="hidden" name="notif_lu_id" value="<?= $notif['id'] ?>">
                        <button type="submit" name="marquer_lu" class="btn-lu">Lu</button>
                    </form>
                <?php else: ?>
                    <span style="font-size:12px; color:#64748b;">✓ Lu</span>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endforeach; ?>

    <?php if($affichees === 0): ?>
        <div class="empty">
            <i class="bi bi-bell-slash"></i>
            <p style="font-size:16px; margin-bottom:8px;">Aucune notification trouvée</p>
            <p style="font-size:13px;">Essaie un autre filtre ou effectue une action sur les garages/services.</p>
        </div>
    <?php endif; ?>

</div>
</body>
</html>