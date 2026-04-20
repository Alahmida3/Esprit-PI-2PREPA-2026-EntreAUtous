<?php
session_start();

// Protection : On vérifie si l'utilisateur est bien connecté
if (!isset($_SESSION['user_id']) && !isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

// On récupère les données de la session
$prenom = $_SESSION['prenom'] ?? "Utilisateur";
$nom = $_SESSION['nom'] ?? "";
$email = $_SESSION['email'] ?? "Non renseigné";
$tel = $_SESSION['telephone'] ?? "Non renseigné";
$adresse = $_SESSION['adresse'] ?? "Non renseignée";
$role = $_SESSION['role'] ?? "client";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Mon Profil - Entre AuTout</title>
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        .profile-header { background: #212529; padding: 60px 0; color: white; margin-bottom: 30px; }
        .edit-mode { display: none; } 
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .team-member-card { border-radius: 10px; transition: transform 0.3s; border: 1px solid #eee; background: white; }
        .team-member-card:hover { transform: translateY(-5px); }
    </style>
</head>
<body id="page-top">

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="home.php">Entre AuTout</a>
            <div class="ms-auto">
                <a class="btn btn-outline-light btn-sm" href="home.php"><i class="fas fa-home me-1"></i> Accueil</a>
            </div>
        </div>
    </nav>

    <header class="profile-header text-center mt-5">
        <div class="container">
            <i class="fas fa-user-circle fa-5x mb-3 text-primary"></i>
            <h1 class="display-4 fw-bold"><?= htmlspecialchars($prenom) ?></h1>
            <p class="lead">Gestion de votre compte personnel Entre AuTout</p>
        </div>
    </header>

    <div class="container mb-5">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="card card-custom h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="fw-bold mb-0">Mes Informations</h3>
                            <button class="btn btn-sm btn-primary" id="btnToggleEdit" onclick="toggleEdit()">
                                <i class="fas fa-edit me-1"></i> Modifier
                            </button>
                        </div>

                        <div id="viewMode">
                            <div class="mb-3">
                                <label class="text-muted small d-block">Nom Complet</label>
                                <span class="h5"><?= htmlspecialchars($prenom . " " . $nom) ?></span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Adresse Email</label>
                                <span class="h6"><?= htmlspecialchars($email) ?></span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Téléphone</label>
                                <span class="h6"><?= htmlspecialchars($tel) ?></span>
                            </div>
                            <div class="mb-3">
                                <label class="text-muted small d-block">Adresse de résidence</label>
                                <span class="h6"><?= htmlspecialchars($adresse) ?></span>
                            </div>
                            <hr>
                            <a href="../../controller/UserController.php?action=logout" class="btn btn-outline-danger w-100 mt-2">Déconnexion</a>
                        </div>

                        <div id="editMode" class="edit-mode">
                            <form action="../../controller/UserController.php" method="POST">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="mb-3">
                                    <input type="text" name="prenom" class="form-control" placeholder="Prénom" value="<?= htmlspecialchars($prenom) ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="nom" class="form-control" placeholder="Nom" value="<?= htmlspecialchars($nom) ?>">
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="telephone" class="form-control" placeholder="Téléphone" value="<?= htmlspecialchars($tel) ?>">
                                </div>
                                <div class="mb-3">
                                    <textarea name="adresse" class="form-control" rows="2" placeholder="Adresse"><?= htmlspecialchars($adresse) ?></textarea>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-success flex-grow-1 shadow-sm">Enregistrer</button>
                                    <button type="button" class="btn btn-light" onclick="toggleEdit()">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card card-custom h-100 bg-light border-0">
                    <div class="card-body p-4">
                        <h3 class="fw-bold mb-4">État de votre véhicule</h3>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="fas fa-microchip fa-2x text-success mb-2"></i>
                                    <p class="small text-muted mb-1">Moteur (PIC)</p>
                                    <span class="badge bg-success">Optimisé</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="fas fa-battery-three-quarters fa-2x text-warning mb-2"></i>
                                    <p class="small text-muted mb-1">Système Élec.</p>
                                    <span class="badge bg-warning">À surveiller</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 bg-white rounded shadow-sm text-center">
                                    <i class="fas fa-oil-can fa-2x text-success mb-2"></i>
                                    <p class="small text-muted mb-1">Maintenance</p>
                                    <span class="badge bg-success">À jour</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 p-3 bg-white rounded border-start border-primary border-4 shadow-sm">
                            <h6 class="fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Note du technicien</h6>
                            <p class="small mb-0 text-muted">Le dernier diagnostic Proteus indique un fonctionnement normal du contrôleur moteur. Prochain contrôle recommandé dans 5000km.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5 pt-5">
            <div class="col-12 text-center mb-5">
                <h2 class="section-heading text-uppercase fw-bold">Notre Équipe Experts</h2>
                <p class="text-muted">Les professionnels qui gèrent votre espace Entre AuTout</p>
            </div>
            <?php
            $team = [
                ['name' => 'Insaf Ben Ahmed', 'role' => 'Directrice Technique (CTO)', 'icon' => 'fa-user-gear'],
                ['name' => 'Asma Ayachi', 'role' => 'Responsable Relation Client', 'icon' => 'fa-headset'],
                ['name' => 'Mohamed Adel Makni', 'role' => 'Expert Diagnostic Systèmes', 'icon' => 'fa-laptop-code'],
                ['name' => 'Amen Naghmouchi', 'role' => 'Chef d\'Atelier', 'icon' => 'fa-screwdriver-wrench'],
                ['name' => 'Ela Hmida', 'role' => 'Responsable Design & Marketing', 'icon' => 'fa-palette'],
                ['name' => 'Rayen Ben Selem', 'role' => 'Ingénieur Maintenance Auto', 'icon' => 'fa-car-side']
            ];
            foreach($team as $m): ?>
            <div class="col-md-4 col-sm-6 mb-4">
                <div class="team-member-card p-4 text-center">
                    <div class="bg-dark text-primary rounded-circle mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                        <i class="fas <?= $m['icon'] ?> fa-xl"></i>
                    </div>
                    <h5 class="fw-bold mb-1"><?= $m['name'] ?></h5>
                    <p class="small text-muted mb-0"><?= $m['role'] ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="py-4 bg-dark text-white text-center mt-5">
        <div class="container"><small>Entre AuTout &copy; 2026 - Système de Gestion Automobile</small></div>
    </footer>

    <script>
        function toggleEdit() {
            const view = document.getElementById('viewMode');
            const edit = document.getElementById('editMode');
            const btn = document.getElementById('btnToggleEdit');
            
            if (edit.style.display === 'none' || edit.style.display === '') {
                edit.style.display = 'block';
                view.style.display = 'none';
                btn.innerHTML = '<i class="fas fa-times me-1"></i> Annuler';
                btn.className = "btn btn-sm btn-outline-secondary";
            } else {
                edit.style.display = 'none';
                view.style.display = 'block';
                btn.innerHTML = '<i class="fas fa-edit me-1"></i> Modifier';
                btn.className = "btn btn-sm btn-primary";
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>