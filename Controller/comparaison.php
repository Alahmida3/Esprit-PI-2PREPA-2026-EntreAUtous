<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

define('GROQ_API_KEY', 'xxxxx');

$host = 'localhost';
$dbname = 'gestion-garage';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
} catch (Exception $e) {
    echo json_encode(['error' => 'Erreur base de données']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$nom1 = trim($input['garage1'] ?? '');
$nom2 = trim($input['garage2'] ?? '');

if (empty($nom1) || empty($nom2)) {
    echo json_encode(['error' => 'Sélectionnez 2 garages']);
    exit;
}

// Récupérer infos garage 1
$stmt = $pdo->prepare("SELECT * FROM garages WHERE nom_garage = ?");
$stmt->execute([$nom1]);
$g1 = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupérer infos garage 2
$stmt = $pdo->prepare("SELECT * FROM garages WHERE nom_garage = ?");
$stmt->execute([$nom2]);
$g2 = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$g1 || !$g2) {
    echo json_encode(['error' => 'Garage introuvable']);
    exit;
}

// Récupérer services garage 1
$stmt = $pdo->prepare("SELECT nom_service, prix FROM services WHERE id_garage = ?");
$stmt->execute([$g1['id-garage']]);
$services1 = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupérer services garage 2
$stmt = $pdo->prepare("SELECT nom_service, prix FROM services WHERE id_garage = ?");
$stmt->execute([$g2['id-garage']]);
$services2 = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculs statistiques
$nb1 = count($services1);
$nb2 = count($services2);
$prix1 = $nb1 > 0 ? array_sum(array_column($services1, 'prix')) / $nb1 : 0;
$prix2 = $nb2 > 0 ? array_sum(array_column($services2, 'prix')) / $nb2 : 0;

$services1_txt = implode(', ', array_map(fn($s) => "{$s['nom_service']} ({$s['prix']} DT)", $services1));
$services2_txt = implode(', ', array_map(fn($s) => "{$s['nom_service']} ({$s['prix']} DT)", $services2));

// Prompt pour Groq
$prompt = "Compare ces 2 garages automobiles en Tunisie et génère un rapport professionnel en français :

GARAGE 1 : {$g1['nom_garage']}
- Adresse : {$g1['adresse']}
- Horaires : {$g1['heure-ouv']} → {$g1['heure_fer']}
- Nombre de services : $nb1
- Services : " . ($services1_txt ?: 'Aucun service') . "
- Prix moyen : " . number_format($prix1, 2) . " DT

GARAGE 2 : {$g2['nom_garage']}
- Adresse : {$g2['adresse']}
- Horaires : {$g2['heure-ouv']} → {$g2['heure_fer']}
- Nombre de services : $nb2
- Services : " . ($services2_txt ?: 'Aucun service') . "
- Prix moyen : " . number_format($prix2, 2) . " DT

Génère un rapport avec :
1. Comparaison des services
2. Comparaison des prix
3. Comparaison des horaires
4. Recommandation finale : lequel est le meilleur et pourquoi
Sois concis et professionnel.";

$data = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => 'Tu es un expert en analyse de garages automobiles. Tu génères des rapports comparatifs professionnels en français.'],
        ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens' => 600
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . GROQ_API_KEY
]);

$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);

if (!isset($result['choices'][0]['message']['content'])) {
    echo json_encode(['error' => 'Erreur IA : ' . json_encode($result)]);
    exit;
}

echo json_encode([
    'rapport' => $result['choices'][0]['message']['content'],
    'stats' => [
        'garage1' => ['nom' => $g1['nom_garage'], 'nb_services' => $nb1, 'prix_moyen' => round($prix1, 2)],
        'garage2' => ['nom' => $g2['nom_garage'], 'nb_services' => $nb2, 'prix_moyen' => round($prix2, 2)],
    ]
]);
?>