<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

define('GROQ_API_KEY', 'xxxxxx'); // même clé que chat.php

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

// Récupérer l'ID du garage depuis la requête
$input = json_decode(file_get_contents('php://input'), true);
$id_garage = $input['id_garage'] ?? '';

if (empty($id_garage)) {
    echo json_encode(['error' => 'ID garage manquant']);
    exit;
}

// Récupérer les infos du garage
$stmt = $pdo->prepare("SELECT * FROM garages WHERE `id-garage` = ?");
$stmt->execute([$id_garage]);
$garage = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$garage) {
    echo json_encode(['error' => 'Garage non trouvé']);
    exit;
}

// Récupérer les services du garage
$stmt2 = $pdo->prepare("SELECT nom_service, prix FROM services WHERE id_garage = ?");
$stmt2->execute([$id_garage]);
$services = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Construire le prompt pour Groq
$services_texte = '';
foreach ($services as $s) {
    $services_texte .= "- {$s['nom_service']} : {$s['prix']} DT\n";
}

$prompt = "Génère un résumé professionnel et court (2-3 phrases max) en français pour ce garage :
Nom : {$garage['nom_garage']}
Adresse : {$garage['adresse']}
Heure ouverture : {$garage['heure-ouv']}
Heure fermeture : {$garage['heure_fer']}
Services proposés :
$services_texte
Le résumé doit être attractif pour les clients.";

// Appel Groq
$data = [
    'model' => 'llama-3.3-70b-versatile',
    'messages' => [
        ['role' => 'system', 'content' => 'Tu es un assistant qui génère des descriptions professionnelles de garages automobiles en français.'],
        ['role' => 'user', 'content' => $prompt]
    ],
    'max_tokens' => 200
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

$resume = $result['choices'][0]['message']['content'];
echo json_encode(['resume' => $resume]);
?>