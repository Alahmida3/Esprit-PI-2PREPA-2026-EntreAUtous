<?php
/**
 * groq_proxy.php — Proxy serveur pour l'API Groq.
 * La clé API reste côté serveur, jamais exposée au navigateur.
 * Appelé par le JS de listeentretiens.php via fetch().
 */

// ── Clé API 
define('GROQ_API_KEY', 'XXXX');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
define('GROQ_MODEL',   'llama-3.3-70b-versatile'); // gratuit, rapide, capable



// ── Headers CORS (appel depuis la même origine) ────────────────
header('Content-Type: application/json; charset=utf-8');

// ── Lecture du corps de la requête ─────────────────────────────
$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (!$input || empty($input['messages'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Corps de requête invalide ou messages manquants.']);
    exit;
}

// ── Construction du payload Groq (format OpenAI-compatible) ────
$payload = json_encode([
    'model'       => GROQ_MODEL,
    'max_tokens'  => 1024,
    'temperature' => 0.7,
    'messages'    => $input['messages'],
]);

// ── Appel cURL vers Groq ───────────────────────────────────────
// Téléchargement du certificat CA si absent (fix SSL local WAMP/XAMPP)
$cacertPath = __DIR__ . '/cacert.pem';
if (!file_exists($cacertPath)) {
    $cacertUrl = 'https://curl.se/ca/cacert.pem';
    $pem = @file_get_contents($cacertUrl);
    if ($pem) file_put_contents($cacertPath, $pem);
}

$ch = curl_init(GROQ_API_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 30,
    CURLOPT_HTTPHEADER     => [
        'Authorization: Bearer ' . GROQ_API_KEY,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS     => $payload,
    // Fix SSL local (WAMP/XAMPP)
    CURLOPT_SSL_VERIFYPEER => file_exists($cacertPath),
    CURLOPT_SSL_VERIFYHOST => file_exists($cacertPath) ? 2 : 0,
    CURLOPT_CAINFO         => file_exists($cacertPath) ? $cacertPath : null,
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr  = curl_error($ch);
curl_close($ch);

// ── Gestion des erreurs ────────────────────────────────────────
if ($curlErr) {
    http_response_code(502);
    echo json_encode(['error' => 'Erreur réseau : ' . $curlErr]);
    exit;
}

if ($httpCode !== 200) {
    // Extraire le message d'erreur lisible depuis la réponse Groq
    $errData = json_decode($response, true);
    $errMsg  = $errData['error']['message'] ?? $errData['error'] ?? "Erreur Groq HTTP $httpCode";
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'error' => $errMsg]);
    exit;
}

// ── Extraction du texte de la réponse ─────────────────────────
$data = json_decode($response, true);
$text = $data['choices'][0]['message']['content'] ?? '';

echo json_encode(['success' => true, 'text' => $text]);
?>