<?php
$host = 'localhost';
$user = 'root';
$password = '';
$dbname = 'gestion-garage';

$garages = [];
$errorMessage = '';
$successMessage = '';

try {
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Handle add garage
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_garage'])) {
        $id_garage = trim($_POST['id_garage']);
        $id_responsable = trim($_POST['id_responsable']);
        $nom_garage = trim($_POST['nom_garage']);
        $adresse = trim($_POST['adresse']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);
        $heure_ouv = trim($_POST['heure_ouv']);
        $heure_fer = trim($_POST['heure_fer']);

        $errors = [];

        if (empty($id_garage) || strlen($id_garage) > 5) {
            $errors[] = 'ID Garage est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($id_responsable) || strlen($id_responsable) > 5) {
            $errors[] = 'ID Responsable est requis et ne doit pas dépasser 5 caractères.';
        }
        if (empty($nom_garage) || strlen($nom_garage) > 15) {
            $errors[] = 'Nom Garage est requis et ne doit pas dépasser 15 caractères.';
        }
        if (empty($adresse) || strlen($adresse) > 15) {
            $errors[] = 'Adresse est requise et ne doit pas dépasser 15 caractères.';
        }
        if (empty($email) || strlen($email) > 15 || !str_contains($email, '@')) {
            $errors[] = 'Email est requis, doit contenir \'@\' et ne doit pas dépasser 15 caractères.';
        }
        if (empty($telephone) || strlen($telephone) > 8) {
            $errors[] = 'Téléphone est requis et ne doit pas dépasser 8 caractères.';
        }
        if (empty($heure_ouv)) {
            $errors[] = 'Heure Ouverture est requise.';
        }
        if (empty($heure_fer)) {
            $errors[] = 'Heure Fermeture est requise.';
        }

        if (!empty($errors)) {
            $errorMessage = implode("\n", $errors);
        } else {
            try {
                $insertSql = "INSERT INTO garages (`id-garage`, `id-responsable`, `nom_garage`, `adresse`, `email`, `telephone`, `heure-ouv`, `heure_fer`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($insertSql);
                $stmt->execute([$id_garage, $id_responsable, $nom_garage, $adresse, $email, $telephone, $heure_ouv, $heure_fer]);
                $successMessage = 'Garage ajouté avec succès.';
            } catch (PDOException $e) {
                $errorMessage = 'Erreur lors de l\'ajout : ' . $e->getMessage();
            }
        }
    }

    $sql = "SELECT `id-garage` AS `id_garage`, `id-responsable` AS `id_responsable`, `nom_garage`, `adresse` AS `adresse`, `email`, `telephone`, `heure-ouv` AS `heure_ouv`, `heure_fer` AS `heure_fer` FROM `garages`";
    $stmt = $pdo->query($sql);
    $garages = $stmt->fetchAll();
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
        .sidebar nav div {
            margin-bottom: 15px;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sidebar nav div.active { color: #fff; font-weight: 600; }
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
        <h3>Dasher</h3>
        <nav>
            <div><i class="bi bi-grid-1x2-fill"></i> Dashboard</div>
            <div class="active"><i class="bi bi-house-door"></i> Liste Garages</div>
            <div><i class="bi bi-people"></i> Responsables</div>
        </nav>
    </div>
    <div class="main-content">
        <div class="welcome-card">
            <h1>Bonjour, Admin 👋</h1>
            <p style="opacity: 0.9;">Voici la page de gestion des garages.</p>
        </div>
        <div class="garage-card">
            <div class="toolbar">
                <button class="add">AJOUTER</button>
                <button class="delete">SUPPRIMER</button>
                <button class="edit">MODIFIER</button>
                <button class="view">AFFICHER</button>
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
            <?php if ($successMessage): ?>
                <div class="success-message"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <table>
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
    </div>
    <script>
        document.querySelector('.add').addEventListener('click', function() {
            const form = document.getElementById('add-form');
            form.style.display = form.style.display === 'none' ? 'block' : 'none';
        });

        document.getElementById('add-form').addEventListener('submit', function(e) {
            const email = document.querySelector('input[name="email"]').value;
            if (!email.includes('@')) {
                alert('L\'email doit contenir le symbole @.');
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
