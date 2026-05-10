<?php
/**
 * ai_diagnostic.php
 * Appelé en AJAX (fetch JS) — répond en JSON PHP.
 * Validations côté serveur + appel API Groq.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── Helpers ───────────────────────────────────────────────────────────────────
function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

// ── 1. Méthode ────────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonError('Méthode non autorisée.', 405);
}

// ── 2. Lecture du body (fetch envoie du JSON) ─────────────────────────────────
$input     = json_decode(file_get_contents('php://input'), true);
$problem   = isset($input['problem']) ? trim($input['problem']) : '';
$stockList = isset($input['stock'])   ? trim($input['stock'])   : '';

// ── 3. Validations serveur ────────────────────────────────────────────────────
if (empty($problem)) {
    jsonError('Veuillez décrire votre problème avant de lancer le diagnostic.');
}
if (mb_strlen($problem) < 10) {
    jsonError('Description trop courte (minimum 10 caractères).');
}
if (mb_strlen($problem) > 1000) {
    jsonError('Description trop longue (maximum 1000 caractères).');
}

// Nettoyage XSS
$problem = htmlspecialchars($problem, ENT_QUOTES, 'UTF-8');

// ── 4. Construction du prompt ─────────────────────────────────────────────────
$prompt = "Tu es un expert en mécanique automobile. Un client décrit son problème : \"$problem\"\n\n"
        . "Voici le stock de pièces disponibles :\n$stockList\n\n"
        . "Réponds en français avec :\n"
        . "1. Un diagnostic court et clair du problème (2-3 phrases max)\n"
        . "2. Une liste des IDs de pièces recommandées (juste les IDs séparés par des virgules, "
        . "sur une ligne qui commence par \"PIECES_IDS:\")\n\n"
        . "Exemple :\nVotre problème semble être lié aux freins.\nPIECES_IDS: 2, 5, 8";

// ── 5. Clé API Groq ───────────────────────────────────────────────────────────
// Stockez votre clé dans une variable d'environnement serveur (recommandé)
// ex: SetEnv GROQ_API_KEY gsk_xxxxxxxxxxxx  dans .htaccess ou vhost
$apiKey = "";

if (empty($apiKey)) {
    jsonError('Clé API non configurée. Contactez l\'administrateur.', 500);
}

// ── 6. Appel API Groq ─────────────────────────────────────────────────────────
$payload = [
    'model'       => 'llama-3.3-70b-versatile',
    'messages'    => [['role' => 'user', 'content' => $prompt]],
    'max_tokens'  => 500,
    'temperature' => 0.7
];

$ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST,           true);
curl_setopt($ch, CURLOPT_POSTFIELDS,     json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT,        30);

$rawResponse = curl_exec($ch);
$httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError   = curl_error($ch);
curl_close($ch);

// ── 7. Gestion erreurs réseau ─────────────────────────────────────────────────
if ($curlError) {
    jsonError('Erreur de connexion au service IA : ' . $curlError, 503);
}
if ($httpCode !== 200) {
    jsonError('Le service IA a retourné une erreur (HTTP ' . $httpCode . ').', 502);
}

// ── 8. Décodage réponse Groq ──────────────────────────────────────────────────
$apiResult = json_decode($rawResponse, true);

if (!isset($apiResult['choices'][0]['message']['content'])) {
    jsonError('Réponse invalide reçue du service IA.', 500);
}

$fullText = $apiResult['choices'][0]['message']['content'];

// ── 9. Retourner le texte brut au JS (le JS parse PIECES_IDS lui-même) ────────
echo json_encode([
    'success' => true,
    'text'    => $fullText
]);
exit;