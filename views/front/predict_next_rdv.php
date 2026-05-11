<?php
/**
 * predict_next_rdv.php — Prédiction du prochain RDV via Groq AI (100% IA)
 */
require_once(__DIR__ . '/api.php');

require_once(__DIR__ . '/../../models/db.php');

// Capturer tout output PHP parasite (warnings, notices) pour ne retourner que du JSON propre
ob_start();
header('Content-Type: application/json; charset=utf-8');

// ─── Route : Sauvegarder un RDV prédit ───────────────────────────────────────
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
            'description'  => $data['description']   ?? 'RDV prédit par Groq IA',
        ]);
        ob_clean();
        echo json_encode(['success' => true, 'message' => '✅ RDV enregistré.', 'date' => $data['date_prochain']]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ─── Route principale : Prédiction ───────────────────────────────────────────
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
        $vehicule = $stmt->fetch(PDO::FETCH_ASSOC);

        // Fallback si la jointure sur 'user' échoue (table peut s'appeler 'client')
        if (empty($vehicule)) {
            $stmt2 = $pdo->prepare("SELECT v.marqueV, v.modeleV, v.kilometrageV, v.anneeV,
                                          CONCAT(COALESCE(c.nom,''), ' ', COALESCE(c.prenom,'')) AS nom_client
                                   FROM vehicule v
                                   LEFT JOIN client c ON v.idclientV = c.id_client
                                   WHERE v.idVehicule = :id LIMIT 1");
            $stmt2->execute(['id' => $idVehicule]);
            $vehicule = $stmt2->fetch(PDO::FETCH_ASSOC) ?: [];
        }

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

function predictWithGroq(int $idVehicule, string $service, string $dateCourante): array
{
    $donnees    = getVehiculeData($idVehicule);
    $vehicule   = $donnees['vehicule'];
    $historique = $donnees['historique'];

    $marque = $vehicule['marqueV']    ?? 'Marque inconnue';
    $modele = $vehicule['modeleV']    ?? '';
    $km     = (int)($vehicule['kilometrageV'] ?? 0);
    $annee  = $vehicule['anneeV']     ?? '';

    $vehiculeDesc = trim("$marque $modele");
    if ($annee)  $vehiculeDesc .= ", $annee";
    if ($km > 0) $vehiculeDesc .= ", $km km";

    $historiqueTexte = 'Aucun historique.';
    if (!empty($historique)) {
        $lignes = array_map(fn($r) => "- {$r['dateRDV']} : {$r['type_serviceRDV']} ({$r['statutRDV']})", $historique);
        $historiqueTexte = implode("\n", $lignes);
    }

    $systemPrompt = "Tu es un expert en maintenance automobile avec 20 ans d'expérience. "
                  . "Tu analyses l'historique d'entretien d'un véhicule pour prédire la date optimale du prochain rendez-vous. "
                  . "Tu réponds UNIQUEMENT avec un objet JSON valide, sans texte autour, sans balises markdown.";

    $userPrompt = "Prédit le prochain RDV pour :\n"
                . "Véhicule : $vehiculeDesc\n"
                . "Service : $service\n"
                . "Date RDV actuel : $dateCourante\n"
                . "Historique :\n$historiqueTexte\n\n"
                . "Réponds UNIQUEMENT avec ce JSON :\n"
                . '{"date_prochain":"YYYY-MM-DD","type_service":"<service>","message":"<1-2 phrases>","niveau_urgence":"<basse|moyenne|élevée>","kilometrage_prevu":<entier>,"intervalle_jours":<entier>,"conseils":"<1 conseil>"}';

    $payload = json_encode([
        'model'       => GROQ_MODEL,
        'max_tokens'  => 512,
        'temperature' => 0.2,
        'messages'    => [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user',   'content' => $userPrompt],
        ]
    ]);

    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_POSTFIELDS     => $payload,
        // ✅ Fix SSL pour XAMPP local
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 0,
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || $httpCode !== 200) {
        error_log("[predict_next_rdv] Erreur: $curlError HTTP:$httpCode Réponse:$response");
        return fallbackPrediction($dateCourante, $service);
    }

    $body    = json_decode($response, true);
    $content = trim($body['choices'][0]['message']['content'] ?? '');
    $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
    $content = preg_replace('/\s*```$/', '', trim($content));

    $prediction = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE || empty($prediction['date_prochain'])) {
        error_log("[predict_next_rdv] JSON invalide: $content");
        return fallbackPrediction($dateCourante, $service);
    }

    if ($prediction['date_prochain'] <= $dateCourante) {
        $prediction['date_prochain'] = date('Y-m-d', strtotime($dateCourante . ' +90 days'));
    }

    return [
        'date_prochain'     => $prediction['date_prochain'],
        'type_service'      => $prediction['type_service']      ?? $service,
        'message'           => $prediction['message']           ?? 'Prochain entretien recommandé.',
        'niveau_urgence'    => $prediction['niveau_urgence']    ?? 'moyenne',
        'kilometrage_prevu' => $prediction['kilometrage_prevu'] ?? null,
        'intervalle_jours'  => $prediction['intervalle_jours']  ?? 90,
        'conseils'          => $prediction['conseils']          ?? '',
        'source'            => '🤖 Groq IA',
    ];
}

function fallbackPrediction(string $dateCourante, string $service): array
{
    $s = mb_strtolower($service);
    $jours = match(true) {
        str_contains($s, 'vidange')       => 180,
        str_contains($s, 'révision')      => 365,
        str_contains($s, 'pneu')          => 270,
        str_contains($s, 'frein')         => 365,
        str_contains($s, 'courroie')      => 730,
        str_contains($s, 'climatisation') => 365,
        default                           => 90,
    };
    return [
        'date_prochain'     => date('Y-m-d', strtotime("$dateCourante +$jours days")),
        'type_service'      => $service,
        'message'           => "Prochain « $service » estimé dans $jours jours.",
        'niveau_urgence'    => 'moyenne',
        'kilometrage_prevu' => null,
        'intervalle_jours'  => $jours,
        'conseils'          => "Respectez les intervalles d'entretien du constructeur.",
        'source'            => '📊 Standard (IA indisponible)',
    ];
}

$resultat = predictWithGroq($id_vehicule, $type_service, $date_actuelle);
ob_clean(); // vider tout output parasite PHP
echo json_encode(['success' => true, 'recommandation' => $resultat], JSON_UNESCAPED_UNICODE);
exit;