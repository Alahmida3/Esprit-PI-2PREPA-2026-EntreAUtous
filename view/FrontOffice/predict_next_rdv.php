<?php
require_once(__DIR__ . '/../../config.php');
header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ====================== SAUVEGARDE DU PROCHAIN RDV ======================
// ====================== SAUVEGARDE DU PROCHAIN RDV ======================
if (isset($_GET['action']) && $_GET['action'] === 'save') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // === DEBUG ===
    error_log("=== SAVE NEXT RDV DEBUG ===");
    error_log("Data received: " . json_encode($data));
    error_log("POST data: " . json_encode($_POST)); // au cas où

    $id_vehicule   = (int)($data['id_vehicule'] ?? 0);
    $id_client     = (int)($data['id_client'] ?? 0);
    $date_prochain = trim($data['date_prochain'] ?? '');
    $type_service  = trim($data['type_service'] ?? '');
    $description   = trim($data['description'] ?? 'Prochain RDV prédit automatiquement');

    if (!$id_vehicule || !$id_client || empty($date_prochain) || empty($type_service)) {
        echo json_encode([
            'success' => false, 
            'message' => 'Données manquantes',
            'debug'   => ['received' => $data]
        ]);
        exit();
    }

    try {
        $pdo = config::getConnexion();

        // Check doublon
        $stmtCheck = $pdo->prepare("SELECT idRDV FROM rendezvous 
                                    WHERE idVehicule = :id_vehicule 
                                    AND type_serviceRDV = :type_service
                                    AND dateRDV >= CURDATE() LIMIT 1");
        $stmtCheck->execute([
            'id_vehicule' => $id_vehicule,
            'type_service' => $type_service
        ]);
        
        if ($stmtCheck->fetch()) {
            echo json_encode(['success' => true, 'message' => 'Un RDV futur existe déjà.']);
            exit();
        }

        $stmt = $pdo->prepare("INSERT INTO rendezvous 
            (idclientRDV, idVehicule, dateRDV, heureRDV, type_serviceRDV, descriptionRDV, statutRDV) 
            VALUES (:id_client, :id_vehicule, :date_rdv, '09:00:00', :type_service, :description, 'En attente')");

        $stmt->execute([
            'id_client'    => $id_client,
            'id_vehicule'  => $id_vehicule,
            'date_rdv'     => $date_prochain,
            'type_service' => $type_service,
            'description'  => $description
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Prochain RDV enregistré avec succès',
            'date'    => $date_prochain,
            'id_rdv'  => $pdo->lastInsertId()
        ]);

    } catch (Exception $e) {
        error_log("Erreur save next RDV: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
    }
    exit();
}

// ====================== PRÉDICTION INTELLIGENTE ======================
$id_vehicule   = (int)($_POST['id_vehicule'] ?? 0);
$type_service  = trim($_POST['type_service'] ?? '');
$date_actuelle = $_POST['date_rdv'] ?? date('Y-m-d');

if (empty($type_service) || $id_vehicule <= 0) {
    echo json_encode(['success' => false, 'message' => 'Type de service et véhicule requis']);
    exit();
}

try {
    $pdo = config::getConnexion();

    // Historique spécifique véhicule + service
    $stmt = $pdo->prepare("
        SELECT dateRDV 
        FROM rendezvous 
        WHERE idVehicule = :id_vehicule 
          AND type_serviceRDV = :type_service 
          AND dateRDV IS NOT NULL
        ORDER BY dateRDV ASC
    ");
    $stmt->execute(['id_vehicule' => $id_vehicule, 'type_service' => $type_service]);
    $dates = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if (count($dates) >= 2) {
        $intervals = [];
        for ($i = 1; $i < count($dates); $i++) {
            $d1 = new DateTime($dates[$i-1]);
            $d2 = new DateTime($dates[$i]);
            $diff = $d1->diff($d2)->days;
            if ($diff > 0) $intervals[] = $diff;
        }

        $interval_moyen = array_sum($intervals) / count($intervals);
        $dernier_rdv = new DateTime(end($dates));
        $date_prochain = (clone $dernier_rdv)->modify('+' . round($interval_moyen) . ' days');
        $source = 'historique_vehicule';
        $message = "dans environ " . round($interval_moyen / 30, 1) . " mois (historique véhicule)";

    } else {
        // Moyenne globale du service
        $stmtGlobal = $pdo->prepare("
            WITH intervals AS (
                SELECT DATEDIFF(dateRDV, LAG(dateRDV) OVER (PARTITION BY idVehicule, type_serviceRDV ORDER BY dateRDV)) as inter
                FROM rendezvous 
                WHERE type_serviceRDV = :type_service
            )
            SELECT AVG(inter) as moy FROM intervals WHERE inter IS NOT NULL AND inter > 0
        ");
        $stmtGlobal->execute(['type_service' => $type_service]);
        $moy = (float)($stmtGlobal->fetchColumn() ?: 90);

        $date_prochain = (new DateTime($date_actuelle))->modify('+' . round($moy) . ' days');
        $source = 'moyenne_globale';
        $message = "dans environ " . round($moy / 30, 1) . " mois (moyenne globale du service)";
    }

    $jours_restants = (new DateTime())->diff($date_prochain)->days;
    $niveau_urgence = $jours_restants <= 30 ? 'élevé' : ($jours_restants <= 90 ? 'moyen' : 'basse');

    echo json_encode([
        'success' => true,
        'recommandation' => [
            'date_prochain'      => $date_prochain->format('Y-m-d'),
            'message'            => $message,
            'niveau_urgence'     => $niveau_urgence,
            'conseil'            => 'Vérifiez les niveaux de fluides, filtres et usure générale.',
            'source'             => $source,
            'nombre_rdv_passes'  => count($dates)
        ]
    ]);

} catch (Exception $e) {
    $date_prochain = (new DateTime($date_actuelle))->modify('+90 days');
    echo json_encode([
        'success' => true,
        'recommandation' => [
            'date_prochain'  => $date_prochain->format('Y-m-d'),
            'message'        => 'dans 3 mois (estimation par défaut)',
            'niveau_urgence' => 'moyen',
            'conseil'        => 'Vérifiez les niveaux de fluides, filtres et usure générale.',
            'source'         => 'fallback'
        ]
    ]);
}