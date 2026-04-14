<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Test Simple</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .container { max-width: 800px; background: white; padding: 20px; border: 1px solid #ddd; }
        input, select, textarea { width: 100%; padding: 8px; margin: 5px 0; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
<div class="container">
    <h1>Test Form Simple</h1>
    
    <?php
    echo "PHP fonctionne!<br>";
    echo "Config: " . (file_exists('../../config.php') ? "existe" : "n'existe pas") . "<br>";
    
    // Traiter le formulaire
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_add'])) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Données reçues :</strong><br>";
        echo "ID Entretien: " . htmlspecialchars($_POST['id_entretien'] ?? '') . "<br>";
        echo "ID Voiture: " . htmlspecialchars($_POST['id_voiture'] ?? '') . "<br>";
        echo "Date: " . htmlspecialchars($_POST['date_entretien'] ?? '') . "<br>";
        echo "KM: " . htmlspecialchars($_POST['kilometrage'] ?? '') . "<br>";
        echo "</div>";
        
        // Essayer la connexion BD
        try {
            require_once '../../config.php';
            if ($pdo) {
                echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
                echo "✓ Connexion BD réussie<br>";
                
                // Essayer d'insérer
                require_once '../../controller/EntretienController.php';
                require_once '../../Models/Entretien.php';
                
                $controller = new EntretienController($pdo);
                $entretien = new Entretien(
                    $_POST['id_entretien'],
                    $_POST['id_voiture'],
                    $_POST['date_entretien'],
                    $_POST['kilometrage'],
                    $_POST['type_intervention'] ?? '',
                    $_POST['observations'] ?? '',
                    $_POST['statut'],
                    $_POST['prochaine_echeance'] ?? '',
                    $_POST['km_prochain'] ?? ''
                );
                
                if ($controller->addEntretien($entretien)) {
                    echo "✓ Entretien ajouté avec succès !<br>";
                } else {
                    echo "❌ Erreur lors de l'ajout (pas d'exception)<br>";
                }
                echo "</div>";
            } else {
                echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
                echo "❌ Pas de connexion BD<br>";
                echo "</div>";
            }
        } catch (Exception $e) {
            echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
            echo "❌ Erreur : " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "</div>";
        }
    }
    ?>
    
    <form method="POST">
        <input type="number" name="id_entretien" placeholder="ID Entretien" required>
        <input type="number" name="id_voiture" placeholder="ID Voiture" required>
        <input type="date" name="date_entretien" required>
        <input type="number" name="kilometrage" placeholder="Kilométrage" required>
        <input type="text" name="type_intervention" placeholder="Type intervention">
        <textarea name="observations" rows="3" placeholder="Observations"></textarea>
        <select name="statut" required>
            <option value="">Statut</option>
            <option value="planifie">Planifié</option>
            <option value="en_cours">En cours</option>
            <option value="termine">Terminé</option>
        </select>
        <input type="date" name="prochaine_echeance">
        <input type="number" name="km_prochain" placeholder="KM prochain">
        <button type="submit" name="submit_add">Ajouter</button>
    </form>
</div>
</body>
</html>