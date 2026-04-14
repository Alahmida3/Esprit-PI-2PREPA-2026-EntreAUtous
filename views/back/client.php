<?php
session_start();

if (!isset($_SESSION['role'])) {
    header("Location: /web/views/front/login.php");
    exit();
}

$nom = $_SESSION['name'] ?? 'Admin';


// 🔵 CONNEXION BASE DE DONNÉES
try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=garage_db;charset=utf8",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("SELECT * FROM client");
    $stmt->execute();
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Erreur connexion DB : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Module Client</title>

    <link href="/web/assets/back/css/theme.css" rel="stylesheet">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.4.1/css/all.css">
</head>

<body>

<div class="dash">

    <!-- SIDEBAR -->
    <?php include __DIR__ . "/../../partials/sidebarhtml"; ?>

    <div class="dash-app">

        <!-- HEADER -->
        <?php include __DIR__ . "/../../partials/headbar.html"; ?>

        <main class="dash-content">

            <div class="container-fluid">

                <!-- HEADER BOX -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card spur-card bg-primary text-white">
                            <div class="card-body">
                                <h3 class="card-title text-white">Gestion des Clients</h3>
                                <p class="card-text mb-0">Module CRUD Clients - Base garage_db</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE CLIENTS -->
                <div class="row">
                    <div class="col-12">
                        <div class="card spur-card">

                            <div class="card-header d-flex justify-content-between align-items-center">
                                <div class="spur-card-title">Liste des Clients</div>
                                <input type="text" class="form-control w-25" placeholder="Rechercher...">
                            </div>

                            <div class="card-body p-0">

                                <div class="table-responsive">

                                    <table class="table table-hover mb-0">

                                        <thead class="table-dark">
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom</th>
                                                <th>Prénom</th>
                                                <th>Email</th>
                                                <th>Téléphone</th>
                                                <th>Adresse</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>

                                        <tbody>

                                        <?php if (!empty($clients)): ?>
                                            <?php foreach ($clients as $c): ?>
                                                <tr>
                                                    <td><?= $c['id_client'] ?></td>
                                                    <td><?= htmlspecialchars($c['nom']) ?></td>
                                                    <td><?= htmlspecialchars($c['prenom']) ?></td>
                                                    <td><?= htmlspecialchars($c['email']) ?></td>
                                                    <td><?= htmlspecialchars($c['telephone']) ?></td>
                                                    <td><?= htmlspecialchars($c['adresse']) ?></td>

                                                    <td>
                                                        <button class="btn btn-sm btn-info text-white">
                                                            <i class="fas fa-eye"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-warning text-white">
                                                            <i class="fas fa-edit"></i>
                                                        </button>

                                                        <button class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="7" class="text-center">Aucun client trouvé</td>
                                            </tr>
                                        <?php endif; ?>

                                        </tbody>

                                    </table>

                                </div>

                            </div>

                        </div>
                    </div>
                </div>

            </div>

        </main>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>

<script>
    $(document).ready(function () {
        $('.menu-toggle').click(function (e) {
            e.preventDefault();
            $('.dash').toggleClass('dash-compact');
        });
    });
</script>

</body>
</html>