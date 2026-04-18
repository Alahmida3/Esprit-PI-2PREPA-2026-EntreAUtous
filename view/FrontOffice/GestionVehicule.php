<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/../../controller/Vehicule.php');

$message     = '';
$messageType = '';

// ===== TRAITEMENT MODIFICATION =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'modifier') {
    try {
        $id = (int)($_POST['idVehicule'] ?? 0);
        if ($id <= 0) throw new Exception("ID véhicule invalide.");

        $vehiculeC      = new VoitureC();
        $ancienVehicule = $vehiculeC->getVehiculeById($id);
        if (!$ancienVehicule) throw new Exception("Véhicule introuvable.");

        $nomImage = $ancienVehicule['imageVoiture'];
        if (isset($_FILES['imageVoiture']) && $_FILES['imageVoiture']['error'] == 0) {
            $ext      = pathinfo($_FILES['imageVoiture']['name'], PATHINFO_EXTENSION);
            $nomImage = time() . "_" . uniqid() . "." . $ext;
            $targetDir = __DIR__ . '/../../assets/img/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            if (!move_uploaded_file($_FILES['imageVoiture']['tmp_name'], $targetDir . $nomImage))
                throw new Exception("Erreur upload image.");
        }

        $idClient  = (int)($_POST['idClient'] ?? 0);
        $nomClient = trim($_POST['nomClient'] ?? '');
        if (empty($nomClient) && $idClient > 0) {
            $pdo  = config::getConnexion();
            $stmt = $pdo->prepare("SELECT nomclient FROM user WHERE id_client = :id");
            $stmt->execute(['id' => $idClient]);
            $row       = $stmt->fetch(PDO::FETCH_ASSOC);
            $nomClient = $row ? $row['nomclient'] : '';
        }

        $vehicule = new Vehicule(
            $id,
            $_POST['marque']          ?? '',
            (float)($_POST['kilometrage'] ?? 0),
            $_POST['date_ajout']      ?? date('Y-m-d'),
            $nomImage,
            $_POST['immatriculation'] ?? '',
            $nomClient,
            $idClient
        );

        $vehiculeC->modifierVehicule($vehicule, $id);
        $message     = "✅ Véhicule modifié avec succès !";
        $messageType = "success";

    } catch (Exception $e) {
        $message     = "❌ Erreur modification : " . $e->getMessage();
        $messageType = "danger";
    }
}

// ===== TRAITEMENT AJOUT VEHICULE =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'ajouter') {
    try {
        $nomImage = "default.jpg";
        if (isset($_FILES['imageVoiture']) && $_FILES['imageVoiture']['error'] == 0) {
            $ext      = pathinfo($_FILES['imageVoiture']['name'], PATHINFO_EXTENSION);
            $nomImage = time() . "_" . uniqid() . "." . $ext;
            $targetDir = __DIR__ . '/../../assets/img/';
            if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
            if (!move_uploaded_file($_FILES['imageVoiture']['tmp_name'], $targetDir . $nomImage))
                throw new Exception("Erreur upload image.");
        }

        $idClient = (int)($_POST['idClient'] ?? 0);
        if ($idClient <= 0) {
            throw new Exception("Veuillez sélectionner un client valide.");
        }
        $nomClient = trim($_POST['nomClient'] ?? '');
        if (empty($nomClient)) {
            $pdo  = config::getConnexion();
            $stmt = $pdo->prepare("SELECT nomclient FROM user WHERE id_client = :id");
            $stmt->execute(['id' => $idClient]);
            $row  = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) throw new Exception("Client introuvable en base de données.");
            $nomClient = $row['nomclient'];
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
        
        // Vérifier si c'est une requête AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            // Retourner une réponse JSON pour AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'message' => '✅ Véhicule ajouté avec succès !']);
            exit;
        } else {
            $message     = "✅ Véhicule ajouté avec succès !";
            $messageType = "success";
        }

    } catch (Exception $e) {
        // Vérifier si c'est une requête AJAX
        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
        
        if ($isAjax) {
            // Retourner une réponse JSON pour AJAX
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ Erreur : ' . $e->getMessage()]);
            exit;
        } else {
            $message     = "❌ Erreur : " . $e->getMessage();
            $messageType = "danger";
        }
    }
}

// ===== TRAITEMENT SUPPRESSION =====
if (isset($_GET['delete_id'])) {
    try {
        $vehiculeC = new VoitureC();
        $vehiculeC->supprimerVehicule((int)$_GET['delete_id']);
        $message     = "✅ Véhicule supprimé avec succès !";
        $messageType = "success";
    } catch (Exception $e) {
        $message     = "❌ Erreur suppression : " . $e->getMessage();
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

// ===== RÉCUPÉRER LES MARQUES UNIQUES POUR LE FILTRE =====
$vehiculeC = new VoitureC();
$marques = $vehiculeC->getMarquesUniques();

// ===== TRAITEMENT DU FILTRE (UNIQUEMENT PAR MARQUE) =====
// ===== TRAITEMENT DU FILTRE PAR CLIENT =====
$filtreActif  = false;
$filtreType   = '';
$filtreValeur = '';

if (isset($_POST['search_client']) && !empty($_POST['filtre_client'])) {
    $idClientFiltre = (int)$_POST['filtre_client'];
    $vehicules      = $vehiculeC->filtrerVehiculeParClient($idClientFiltre);
    $filtreActif    = true;
    $filtreType     = 'client';
    // Trouver le nom du client sélectionné
    foreach ($clients as $c) {
        if ($c['id_client'] == $idClientFiltre) {
            $filtreValeur = $c['nomclient'];
            break;
        }
    }
} else {
    $vehicules = $vehiculeC->afficherVehicule();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include_once __DIR__ . '/../../partials/head/head-meta.html'; ?>
    <title>Gestion des Véhicules - Smart Supply</title>
    <?php include_once __DIR__ . '/../../partials/head/head-links.html'; ?>
    <link href="/ProjetWeb/assets/Front office/css/styles.css" rel="stylesheet" />
    <style>
        .modal-header-custom { border-bottom:none; text-align:center; display:block; padding-top:2rem; }
        .modal-title-custom  { color:#FFC107; font-weight:bold; text-transform:uppercase; letter-spacing:1px; }
        .alert-fixed         { position:fixed; top:90px; right:20px; z-index:9999; min-width:300px; }
        .vehicle-card        { transition:all 0.3s ease; }
        .vehicle-card:hover  { transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.1); }
    </style>
</head>
<body id="page-top">

    <nav class="navbar navbar-expand-lg navbar-dark fixed-top bg-dark" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">
                <img src="../assets/Front office/assets/img/navbar-logo.svg" alt="Logo" />
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive">
                Menu <i class="fas fa-bars ms-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link" href="FrontOfficeVehicule.php">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Portfolio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">About</a></li>
                    <li class="nav-item"><a class="nav-link active" href="GestionVehicule.php">Véhicule</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <?php if ($message !== ''): ?>
    <div class="alert alert-<?php echo $messageType; ?> alert-fixed alert-dismissible fade show shadow" role="alert">
        <strong><?php echo htmlspecialchars($message); ?></strong>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <section class="page-section bg-light" id="portfolio" style="margin-top:80px; padding:40px 0;">
        <div class="container">
            <div class="text-center mb-2">
                <h2 style="font-size: 2rem; font-weight: bold; margin-bottom: 0.5rem;">Gestion des Véhicules</h2>
                <p class="text-muted fst-italic">Aperçu de vos véhicules actuels.</p>
            </div>

            <!-- ===== RECHERCHE ET AJOUT SUR LA MÊME LIGNE ===== -->
            <!-- ===== RECHERCHE ET AJOUT ===== -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <form action="" method="POST" class="d-flex gap-2">
        <select name="filtre_client" class="form-select" style="width: 250px;">
            <option value="">-- Tous les clients --</option>
            <?php foreach ($clients as $c): ?>
                <option value="<?php echo (int)$c['id_client']; ?>"
                    <?php echo (isset($_POST['search_client']) && $_POST['filtre_client'] == $c['id_client']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($c['nomclient']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" name="search_client" class="btn btn-primary">
            <i class="fas fa-search"></i> Rechercher
        </button>
    </form>
    <button class="btn btn-warning btn-sm" onclick="openAjoutModal()">
                    <i class="fas fa-plus"></i> Ajouter un véhicule
                </button>

    
</div>
                
                <!-- Bouton Ajouter à droite - Appelle formulaireVehicule.php -->
                
            </div>

            <!-- Message de filtre actif -->
            <?php if ($filtreActif && $filtreType =='client'): ?>
            <div class="alert alert-info mb-4 py-2">
                <i class="fas fa-filter"></i> Affichage des véhicules du client <strong><?php echo htmlspecialchars($filtreValeur); ?></strong>
                <a href="GestionVehicule.php" class="alert-link ms-2"> Réinitialiser</a>
            </div>
            <?php endif; ?>

            <!-- ===== AFFICHAGE DES VÉHICULES ===== -->
            <div class="row g-4">
                <?php if ($vehicules && $vehicules->rowCount() > 0): ?>
                    <?php while ($vehicule = $vehicules->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col-md-4">
                        <div class="card h-100 shadow-sm text-center border-0 vehicle-card">
                            <img src="/ProjetWeb/assets/img/<?php echo htmlspecialchars($vehicule['imageVoiture'] ?? 'default.jpg'); ?>"
                                 class="card-img-top"
                                 style="height:250px; object-fit:cover;">
                            <div class="card-body pt-3">
                                <h5 class="card-title fw-bold"><?php echo htmlspecialchars($vehicule['marqueV'] ?? ''); ?></h5>
                                <p class="mb-1 fst-italic">
                                    <strong>Matricule :</strong> <?php echo htmlspecialchars($vehicule['matriculevoiture'] ?? ''); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Client :</strong> <?php echo htmlspecialchars($vehicule['nomclient'] ?? ''); ?>
                                </p>
                                <p class="mb-1">
                                    <strong>Date d'ajout :</strong> <?php echo htmlspecialchars($vehicule['date_ajoutV'] ?? ''); ?>
                                </p>
                                <p class="mb-3">
                                    <strong>Kilométrage :</strong> <?php echo number_format($vehicule['kilometrageV'] ?? 0, 0, ',', ' '); ?> km
                                </p>
                                <div class="d-flex justify-content-center gap-2">
                                    <button class="btn btn-warning btn-sm"
                                            data-bs-toggle="modal" data-bs-target="#modalModifierVehicule"
                                            onclick="editVehicule(<?php echo htmlspecialchars(json_encode($vehicule)); ?>)">
                                        <i class="fas fa-edit"></i> Modifier
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="openRDVModal(<?php echo $vehicule['idVehicule']; ?>)">
                                        <i class="fas fa-calendar-check"></i> RDV
                                    </button>
                                    <a href="?delete_id=<?php echo (int)$vehicule['idVehicule']; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Supprimer ce véhicule ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12 text-center">
                        <p class="text-muted">
                            <?php echo $filtreActif ? 'Aucun véhicule trouvé pour cette marque.' : 'Aucun véhicule trouvé.'; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- ===== MODAL AJOUT (chargé dynamiquement depuis formulaireVehicule.php) ===== -->
    <div class="modal fade" id="modalVehicule" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header-custom">
                <h2 class="modal-title-custom">Ajouter un Véhicule</h2>
                <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
            </div>
            <div id="modalContentAjout">
                </div>
        </div>
    </div>
</div>

    <!-- ===== MODAL MODIFICATION ===== -->
    <div class="modal fade" id="modalModifierVehicule" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header-custom">
                    <h2 class="modal-title-custom">Modifier le Véhicule</h2>
                    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form method="POST" enctype="multipart/form-data" action="GestionVehicule.php">
                        <input type="hidden" name="action" value="modifier">
                        <input type="hidden" name="idVehicule" id="edit_idVehicule">
                        <div class="mb-3">
                            <label class="form-label">Marque *</label>
                            <input type="text" name="marque" id="edit_marque" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Immatriculation *</label>
                            <input type="text" name="immatriculation" id="edit_immatriculation" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date d'ajout *</label>
                            <input type="date" name="date_ajout" id="edit_date_ajout" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kilométrage (km) *</label>
                            <input type="number" name="kilometrage" id="edit_kilometrage" class="form-control" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Client *</label>
                            <select name="idClient" id="edit_idClient" class="form-control" required>
                                <option value="">-- Sélectionner un client --</option>
                                <?php foreach ($clients as $client): ?>
                                    <option value="<?php echo $client['id_client']; ?>" data-nom="<?php echo htmlspecialchars($client['nomclient']); ?>">
                                        <?php echo htmlspecialchars($client['nomclient']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <input type="hidden" name="nomClient" id="edit_nomClientHidden">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nouvelle image (optionnel)</label>
                            <input type="file" name="imageVoiture" class="form-control" accept="image/*">
                            <small class="text-muted">Laissez vide pour garder l'image actuelle.</small>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-warning">Enregistrer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL RDV ===== -->
    <div class="modal fade" id="modalRDV" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header">
                    <h5 class="modal-title">Prendre un rendez-vous</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="modalContentRDV">
                    <div class="text-center">Chargement...</div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer py-4">
        <div class="container text-center">Copyright &copy; Smart Supply 2026</div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="../assets/Front office/js/scripts.js"></script>
    <script>
        function editVehicule(vehicule) {
            document.getElementById('edit_idVehicule').value      = vehicule.idVehicule;
            document.getElementById('edit_marque').value          = vehicule.marqueV;
            document.getElementById('edit_immatriculation').value = vehicule.matriculevoiture || '';
            document.getElementById('edit_date_ajout').value      = vehicule.date_ajoutV;
            document.getElementById('edit_kilometrage').value     = vehicule.kilometrageV;

            const select = document.getElementById('edit_idClient');
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].value == vehicule.idclient) {
                    select.selectedIndex = i;
                    document.getElementById('edit_nomClientHidden').value = select.options[i].getAttribute('data-nom') || '';
                    break;
                }
            }
        }

        document.getElementById('edit_idClient').addEventListener('change', function () {
            document.getElementById('edit_nomClientHidden').value = this.options[this.selectedIndex].getAttribute('data-nom') || '';
        });

        setTimeout(function () {
            document.querySelectorAll('.alert-fixed').forEach(a => a.classList.remove('show'));
        }, 4000);
        
        function openRDVModal(idVehicule) {
            const modalEl      = document.getElementById('modalRDV');
            const contentEl    = document.getElementById('modalContentRDV');
            const myModal      = new bootstrap.Modal(modalEl);

            // Afficher le spinner pendant le chargement
            contentEl.innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Chargement du formulaire...</p>
                </div>`;

            myModal.show();

            // Charger formulaireRDV.php dans le modal
            fetch('formulaireRDV.php?id=' + idVehicule, {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur HTTP ' + response.status);
                return response.text();
            })
            .then(html => {
                contentEl.innerHTML = html;

                // Exécuter les scripts injectés par formulaireRDV.php
                contentEl.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    newScript.textContent = oldScript.textContent;
                    document.body.appendChild(newScript);
                    document.body.removeChild(newScript);
                });
            })
            .catch(err => {
                contentEl.innerHTML = `
                    <div class="alert alert-danger m-3">
                        <strong>❌ Erreur de chargement du formulaire.</strong><br>
                        <small>${err.message}</small>
                    </div>`;
            });
        }
        
        function openAjoutModal() {
            const modalEl      = document.getElementById('modalVehicule');
            const contentEl    = document.getElementById('modalContentAjout');
            const myModal      = new bootstrap.Modal(modalEl);

            // Afficher le spinner pendant le chargement
            contentEl.innerHTML = `
                <div class="text-center p-4">
                    <div class="spinner-border text-warning" role="status"></div>
                    <p class="mt-2 text-muted">Chargement du formulaire...</p>
                </div>`;

            myModal.show();

            // Charger formulaireVehicule.php dans le modal
            fetch('formulaireVehicule.php', {
                method: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                if (!response.ok) throw new Error('Erreur HTTP ' + response.status);
                return response.text();
            })
            .then(html => {
                contentEl.innerHTML = html;

                // Exécuter les scripts injectés par formulaireVehicule.php
                contentEl.querySelectorAll('script').forEach(oldScript => {
                    const newScript = document.createElement('script');
                    newScript.textContent = oldScript.textContent;
                    document.body.appendChild(newScript);
                    document.body.removeChild(newScript);
                });
            })
            .catch(err => {
                contentEl.innerHTML = `
                    <div class="alert alert-danger m-3">
                        <strong>❌ Erreur de chargement du formulaire.</strong><br>
                        <small>${err.message}</small>
                    </div>`;
            });
        }
    </script>
</body>
</html>