<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

define('GROQ_API_KEY', 'xxxx');

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

$stmt = $pdo->query("
    SELECT s.nom_service, s.prix, g.nom_garage, g.adresse 
    FROM services s 
    JOIN garages g ON s.id_garage = g.`id-garage`
");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

$contexte = "Tu es un assistant pour le site EntreAuTous, un site de gestion de véhicules en Tunisie. ";
$contexte .= "Voici les services disponibles dans les garages :\n";
foreach ($services as $s) {
    $contexte .= "- {$s['nom_service']} au garage '{$s['nom_garage']}' ({$s['adresse']}) : {$s['prix']} DT\n";
}
$contexte .= "\nRéponds toujours en français, de façon courte et utile. ";
$contexte .= "Si le client cherche un service, recommande le moins cher ou le plus adapté.";

$input = json_decode(file_get_contents('php://input'), true);
$question = $input['message'] ?? '';

if (empty($question)) {
    echo json_encode(['error' => 'Message vide']);
    exit;
}

$data = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => $contexte],
        ['role' => 'user', 'content' => $question]
    ],
    'max_tokens' => 500
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
    echo json_encode(['response' => 'ERREUR: ' . json_encode($result)]);
    exit;
}

$reponse_ia = $result['choices'][0]['message']['content'];
echo json_encode(['response' => $reponse_ia]);
?>