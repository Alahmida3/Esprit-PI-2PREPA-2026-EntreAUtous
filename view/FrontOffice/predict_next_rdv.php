<?php

require_once(__DIR__ . '/../../config.php');
header('Content-Type: application/json; charset=utf-8');

if (isset($_GET['action']) && $_GET['action'] === 'save') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['id_client']) || empty($data['id_vehicule']) || empty($data['date_prochain'])) {
        echo json_encode(['success' => false, 'message' => 'Données manquantes.']);
        exit;
    }
    try {
        $pdo  = config::getConnexion();
        $stmt = $pdo->prepare("INSERT INTO rendezvous (idclientRDV, idVehicule, dateRDV, heureRDV, type_serviceRDV, descriptionRDV, statutRDV)
                               VALUES (:id_client, :id_vehicule, :date_rdv, :heure_rdv, :type_service, :description, 'En attente')");
        $stmt->execute([
            'id_client'    => (int)$data['id_client'],
            'id_vehicule'  => (int)$data['id_vehicule'],
            'date_rdv'     => $data['date_prochain'],
            'heure_rdv'    => $data['heure_rdv']    ?? '09:00',
            'type_service' => $data['type_service']  ?? 'Entretien',
            'description'  => $data['description']   ?? 'RDV prédit',
        ]);
        echo json_encode(['success' => true, 'message' => '✅ RDV enregistré.', 'date' => $data['date_prochain']]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

$id_vehicule   = (int)($_POST['id_vehicule']  ?? 0);
$type_service  = trim($_POST['type_service']  ?? '');
$date_actuelle = trim($_POST['date_rdv']      ?? date('Y-m-d'));

if (!$id_vehicule) {
    echo json_encode(['success' => false, 'message' => 'Identifiant véhicule manquant.']);
    exit;
}

function getVehiculeData(int $idVehicule): array
{
    try {
        $pdo = config::getConnexion();

        $stmt = $pdo->prepare("SELECT v.marqueV, v.modeleV, v.kilometrageV, v.anneeV,
                                      CONCAT(u.nomclient, ' ', COALESCE(u.prenomclient,'')) AS nom_client
                               FROM vehicule v
                               LEFT JOIN user u ON v.idclientV = u.id_client
                               WHERE v.idVehicule = :id LIMIT 1");
        $stmt->execute(['id' => $idVehicule]);
        $vehicule = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        $stmtH = $pdo->prepare("SELECT dateRDV, type_serviceRDV, statutRDV
                                 FROM rendezvous
                                 WHERE idVehicule = :id
                                 ORDER BY dateRDV DESC LIMIT 10");
        $stmtH->execute(['id' => $idVehicule]);
        $historique = $stmtH->fetchAll(PDO::FETCH_ASSOC) ?: [];

        return ['vehicule' => $vehicule, 'historique' => $historique];
    } catch (Exception $e) {
        error_log("[predict_next_rdv] getVehiculeData: " . $e->getMessage());
        return ['vehicule' => [], 'historique' => []];
    }
}

function predictNextRdv(int $idVehicule, string $service, string $dateCourante): array
{
    $donnees    = getVehiculeData($idVehicule);
    $vehicule   = $donnees['vehicule'];
    $historique = $donnees['historique'];

    $km = (int)($vehicule['kilometrageV'] ?? 0);
    
    // Analyse de l'historique pour affiner la prédiction
    $dernierRdv = !empty($historique) ? $historique[0] : null;
    $servicesAnterieurs = array_column($historique, 'type_serviceRDV');
    
    return fallbackPrediction($dateCourante, $service, $km, $dernierRdv, $servicesAnterieurs);
}

function fallbackPrediction(string $dateCourante, string $service, int $kilometrage = 0, ?array $dernierRdv = null, array $servicesAnterieurs = []): array
{
    $s = mb_strtolower($service);
    
    // Déterminer l'intervalle en fonction du type de service
    $jours = match(true) {
        str_contains($s, 'vidange')       => 180,
        str_contains($s, 'révision')      => 365,
        str_contains($s, 'pneu')          => 270,
        str_contains($s, 'frein')         => 365,
        str_contains($s, 'courroie')      => 730,
        str_contains($s, 'climatisation') => 365,
        str_contains($s, 'entretien')     => 180,
        str_contains($s, 'diagnostic')    => 90,
        default                           => 90,
    };
    
    // Ajuster en fonction de l'historique si disponible
    $messageAjustement = '';
    $niveauUrgence = 'moyenne';
    
    if ($dernierRdv && !empty($dernierRdv['dateRDV'])) {
        $dernierDate = new DateTime($dernierRdv['dateRDV']);
        $dateActuelle = new DateTime($dateCourante);
        $interval = $dateActuelle->diff($dernierDate)->days;
        
        // Si le dernier RDV du même type était récent, ajuster
        if (in_array($service, $servicesAnterieurs) && $interval < $jours / 2) {
            $jours = max(30, $jours - 60);
            $messageAjustement = " (ajusté car le même service a été effectué récemment)";
            $niveauUrgence = 'basse';
        } elseif ($interval > $jours * 1.5) {
            $niveauUrgence = 'élevée';
            $messageAjustement = " (urgence élevée - dépassement recommandé)";
        }
    }
    
    // Estimation du kilométrage futur
    $kmPrevu = $kilometrage > 0 ? $kilometrage + ($jours * 50) : null; // ~50 km/jour en moyenne
    
    // Conseils personnalisés
    $conseils = match(true) {
        str_contains($s, 'vidange') => "Pensez à vérifier le niveau d'huile régulièrement.",
        str_contains($s, 'frein')   => "Faites vérifier l'usure des plaquettes à chaque révision.",
        str_contains($s, 'pneu')    => "Vérifiez la pression des pneus tous les mois.",
        str_contains($s, 'courroie')=> "La courroie de distribution est critique - ne dépassez pas l'intervalle.",
        default                     => "Respectez les intervalles d'entretien du constructeur.",
    };
    
    return [
        'date_prochain'     => date('Y-m-d', strtotime("$dateCourante +$jours days")),
        'type_service'      => $service,
        'message'           => "Prochain « $service » estimé dans $jours jours.$messageAjustement",
        'niveau_urgence'    => $niveauUrgence,
        'kilometrage_prevu' => $kmPrevu,
        'intervalle_jours'  => $jours,
        'conseils'          => $conseils,
        'source'            => '📊 Prédiction standard',
    ];
}

$resultat = predictNextRdv($id_vehicule, $type_service, $date_actuelle);
echo json_encode(['success' => true, 'recommandation' => $resultat], JSON_UNESCAPED_UNICODE);
exit;