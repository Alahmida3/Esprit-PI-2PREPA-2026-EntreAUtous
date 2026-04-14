<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Test Ajouter Entretien</title>
    <style>
        body { font-family: Arial; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; background: white; padding: 20px; border-radius: 5px; }
        h1 { color: #333; }
        .form-group { margin: 15px 0; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 3px; }
        button { background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer; }
        button:hover { background: #0056b3; }
        .alert { padding: 10px; margin: 10px 0; border-radius: 3px; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>
<div class="container">
    <h1>Ajouter un Entretien</h1>
    
    <?php
    // Test simple sans includes complexes
    require_once '../../config.php';
    require_once '../../controller/EntretienController.php';
    require_once '../../Models/Entretien.php';
    
    $message = '';
    
    if (!$pdo) {
        $message = '<div class="alert alert-danger">❌ ERREUR : Pas de connexion à la base de données</div>';
    } else {
        $message = '<div class="alert alert-success">✓ Connexion BD réussie</div>';
        
        // Traiter le formulaire
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_add'])) {
            try {
                $controller = new EntretienController($pdo);
                $entretien = new Entretien(
                    $_POST['id_entretien'],
                    $_POST['id_voiture'],
                    $_POST['date_entretien'],
                    $_POST['kilometrage'],
                    $_POST['type_intervention'],
                    $_POST['observations'],
                    $_POST['statut'],
                    $_POST['prochaine_echeance'],
                    $_POST['km_prochain']
                );
                
                if ($controller->addEntretien($entretien)) {
                    $message = '<div class="alert alert-success">✓ Entretien ajouté avec succès !</div>';
                } else {
                    $message = '<div class="alert alert-danger">❌ Erreur lors de l\'ajout</div>';
                }
            } catch (Exception $e) {
                $message = '<div class="alert alert-danger">❌ Erreur : ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }
    echo $message;
    ?>
    
    <form method="POST">
        <div class="form-group">
            <label for="id_entretien">ID Entretien *</label>
            <input type="number" id="id_entretien" name="id_entretien" required>
        </div>

        <div class="form-group">
            <label for="id_voiture">ID Voiture *</label>
            <input type="number" id="id_voiture" name="id_voiture" required>
        </div>

        <div class="form-group">
            <label for="date_entretien">Date entretien *</label>
            <input type="date" id="date_entretien" name="date_entretien" required>
        </div>

        <div class="form-group">
            <label for="kilometrage">Kilométrage *</label>
            <input type="number" id="kilometrage" name="kilometrage" required>
        </div>

        <div class="form-group">
            <label for="type_intervention">Type d'intervention</label>
            <input type="text" id="type_intervention" name="type_intervention">
        </div>

        <div class="form-group">
            <label for="observations">Observations</label>
            <textarea id="observations" name="observations" rows="4"></textarea>
        </div>

        <div class="form-group">
            <label for="statut">Statut *</label>
            <select id="statut" name="statut" required>
                <option value="">-- Sélectionner --</option>
                <option value="planifie">Planifié</option>
                <option value="en_cours">En cours</option>
                <option value="termine">Terminé</option>
                <option value="annule">Annulé</option>
            </select>
        </div>

        <div class="form-group">
            <label for="prochaine_echeance">Prochaine échéance</label>
            <input type="date" id="prochaine_echeance" name="prochaine_echeance">
        </div>

        <div class="form-group">
            <label for="km_prochain">KM prochain</label>
            <input type="number" id="km_prochain" name="km_prochain">
        </div>

        <button type="submit" name="submit_add">Ajouter</button>
    </form>
</div>
</body>
</html>