<!DOCTYPE html>
<html lang="en">
<head>
    <?php include_once __DIR__ . '/../../partials/head/head-meta.html'; ?>
    <title>Planifier un Rendez-vous</title>
    <?php include_once __DIR__ . '/../../partials/head/head-links.html'; ?>
    <link href="/ProjetWeb/assets/Front office/css/styles.css" rel="stylesheet" />
    <style>
        body {
            background-color: #f8f9fa;
        }
        .form-container {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 50px auto;
            padding: 40px;
        }
        .form-title {
            color: #ffc107; /* Jaune comme sur ta capture */
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border: 1px solid #ced4da;
            padding: 12px;
            border-radius: 5px;
        }
        .form-control::placeholder {
            color: #adb5bd;
        }
        .btn-save {
            background-color: #ffc107;
            border: none;
            color: #fff;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 5px;
        }
        .btn-cancel {
            background-color: #6c757d;
            border: none;
            color: #fff;
            font-weight: bold;
            padding: 12px 30px;
            border-radius: 5px;
            margin-right: 10px;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="form-container">
            <h2 class="text-center form-title">Planifier un Rendez-vous</h2>
            
            <form action="traitementRDV.php" method="POST">
                <div class="row g-4">
                    <div class="col-12">
                        <label class="form-label">ID Rendez-vous</label>
                        <input type="text" name="id_rdv" class="form-control" placeholder="Ex: RDV-001" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">ID Client (Propriétaire)</label>
                        <input type="text" name="nom_client" class="form-control" placeholder="ID du client responsable" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">ID Véhicule</label>
                        <input type="text" name="id_vehicule" class="form-control" placeholder="Ex: 001" value="<?php echo isset($_GET['id_vehicule']) ? $_GET['id_vehicule'] : ''; ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Date du RDV</label>
                        <input type="date" name="date_rdv" class="form-control" value="2026-04-13" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Heure du RDV</label>
                        <input type="time" name="heure_rdv" class="form-control" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Type de Service</label>
                        <select name="type_service" class="form-select" required>
                            <option value="">Choisir un service...</option>
                            <option value="Vidange">Vidange et révision</option>
                            <option value="Freinage">Problème de freins</option>
                            <option value="Moteur">Diagnostic Moteur</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Statut</label>
                        <select name="statut" class="form-select">
                            <option value="En attente" selected>En attente</option>
                            <option value="Confirmé">Confirmé</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Description / Notes</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="Détails supplémentaires sur l'intervention..."></textarea>
                    </div>

                    <div class="col-12 text-center mt-5">
                        <a href="GestionVehicule.php" class="btn btn-cancel text-decoration-none">Annuler</a>
                        <button type="submit" class="btn btn-save text-uppercase">Enregistrer le RDV</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>