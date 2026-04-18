<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../controller/Vehicule.php');

$message     = '';
$messageType = '';

// ===== TRAITEMENT AJOUT =====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nomImage = "default.jpg";
        if (isset($_FILES['imageVoiture']) && $_FILES['imageVoiture']['error'] == 0) {
            $ext      = pathinfo($_FILES['imageVoiture']['name'], PATHINFO_EXTENSION);
            $nomImage = time() . "_" . uniqid() . "." . $ext;
            $targetDir = __DIR__ . '/../../assets/img/';

            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            if (!move_uploaded_file($_FILES['imageVoiture']['tmp_name'], $targetDir . $nomImage)) {
                throw new Exception("Erreur lors de l'upload de l'image.");
            }
        }

        // Récupérer nomClient depuis le select si le champ hidden est vide
        $idClient  = (int)($_POST['idClient'] ?? 0);
        $nomClient = trim($_POST['nomClient'] ?? '');
        if (empty($nomClient) && $idClient > 0) {
            $pdo  = config::getConnexion();
            $stmt = $pdo->prepare("SELECT nomclient FROM user WHERE id_client = :id");
            $stmt->execute(['id' => $idClient]);
            $row       = $stmt->fetch(PDO::FETCH_ASSOC);
            $nomClient = $row ? $row['nomclient'] : 'Client';
        }

        $vehicule = new Vehicule(
            null,
            $_POST['marque']          ?? '',
            (float)($_POST['kilometrage'] ?? 0),
            $_POST['date_ajout']      ?? date('Y-m-d'),
            $nomImage,
            $_POST['immatriculation'] ?? '',
            $nomClient,
            $idClient
        );

        $vehiculeC = new VoitureC();
        $vehiculeC->ajouterVehicule($vehicule);

        // Pas de header() redirect — afficher le message directement
        $message     = "✅ Véhicule ajouté avec succès !";
        $messageType = "success";

    } catch (PDOException $e) {
        $message     = "❌ Erreur base de données : " . $e->getMessage();
        $messageType = "danger";
    } catch (Exception $e) {
        $message     = "❌ Erreur : " . $e->getMessage();
        $messageType = "danger";
    }
}

// ===== RÉCUPÉRER CLIENTS =====
try {
    $pdo     = config::getConnexion();
    $stmt    = $pdo->query("SELECT id_client, nomclient FROM user ORDER BY nomclient");
    $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $clients = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include_once __DIR__ . '/../../partials/head/head-meta.html'; ?>
    <title>Ajouter un Véhicule - Smart Supply</title>
    <?php include_once __DIR__ . '/../../partials/head/head-links.html'; ?>
    <link href="/ProjetWeb/assets/Front office/css/styles.css" rel="stylesheet" />
    <style>
        .masthead {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 40vh;
        }
        .masthead .masthead-subheading {
            font-size: 2rem;
        }
        .form-container {
            margin-top: 40px;
            margin-bottom: 60px;
        }
        .alert-fixed {
            position: fixed;
            top: 80px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
        }
    </style>
</head>
<body id="page-top">

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">
                <img src="assets/img/navbar-logo.svg" alt="Logo" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                Menu <i class="fas fa-bars ms-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link active" href="FrontOfficeVehicule.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="GestionVehicule.php">Véhicule</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Message flottant -->
    <?php if ($message !== ''): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-fixed alert-dismissible fade show shadow" role="alert">
        <strong><?php echo htmlspecialchars($message); ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Masthead -->
    <header class="masthead">
        <div class="container text-center">
            <div class="masthead-subheading">Bienvenue chez EntreAutous</div>
            <div class="masthead-heading text-uppercase">Ajoutez votre véhicule</div>
        </div>
    </header>

    <!-- Formulaire d'ajout -->
    <div class="container form-container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <form method="POST" enctype="multipart/form-data" class="bg-light p-4 rounded shadow">
                    <div class="mb-3">
                        <label class="form-label">Marque *</label>
                        <input type="text" name="marque" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Immatriculation *</label>
                        <input type="text" name="immatriculation" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date d'ajout</label>
                        <input type="date" name="date_ajout" class="form-control" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kilométrage *</label>
                        <input type="number" name="kilometrage" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Client *</label>
                        <select name="idClient" id="clientSelect" class="form-control" required>
                            <option value="">-- Sélectionner un client --</option>
                            <?php foreach ($clients as $client): ?>
                                <option value="<?php echo $client['id_client']; ?>"
                                        data-nom="<?php echo htmlspecialchars($client['nomclient']); ?>">
                                    <?php echo htmlspecialchars($client['nomclient']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="nomClient" id="nomClientHidden">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image du véhicule</label>
                        <input type="file" name="imageVoiture" class="form-control" accept="image/*">
                        <small class="text-muted">Format: JPG, PNG, GIF.</small>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Ajouter le véhicule</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/Front office/js/scripts.js"></script>
    <script>
        // Remplir automatiquement le hidden nomClient
        document.getElementById('clientSelect').addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            document.getElementById('nomClientHidden').value = selected.getAttribute('data-nom') || '';
        });

        // Auto-fermer l'alerte après 4s
        setTimeout(function () {
            const alerts = document.querySelectorAll('.alert-fixed');
            alerts.forEach(a => a.classList.remove('show'));
        }, 4000);
    </script>
</body>
</html>