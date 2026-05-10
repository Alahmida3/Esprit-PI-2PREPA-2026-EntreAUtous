<?php
/**
 * Estimertemps.php — Estimation du temps de main d'œuvre via Groq AI (100% IA)
 */
require_once(__DIR__ . '/api.php');
header('Content-Type: application/json; charset=utf-8');

$type_service = trim($_POST['type_service'] ?? '');
$kilometrage  = (int)($_POST['kilometrage']  ?? 0);
$marque       = trim($_POST['marque']        ?? '');
$annee        = trim($_POST['annee']         ?? '');
$modele       = trim($_POST['modele']        ?? '');

if (empty($type_service)) {
    echo json_encode(['success' => false, 'message' => 'Le type de service est obligatoire.']);
    exit;
}

function estimerAvecGroq(string $service, int $km, string $marque, string $annee, string $modele): array
{
    $vehiculeDesc = trim("$marque $modele") ?: 'véhicule non précisé';
    if ($annee)  $vehiculeDesc .= ", année $annee";
    if ($km > 0) $vehiculeDesc .= ", $km km";

    $systemPrompt = "Tu es un expert mécanicien automobile avec 20 ans d'expérience. "
                  . "Tu estimes les temps de main d'œuvre avec précision. "
                  . "Tu réponds UNIQUEMENT avec un objet JSON valide, sans texte autour, sans balises markdown.";

    $userPrompt = "Estime le temps de main d'œuvre pour :\n"
                . "- Service : $service\n"
                . "- Véhicule : $vehiculeDesc\n\n"
                . "Réponds UNIQUEMENT avec ce JSON :\n"
                . '{"duree_min":<entier>,"fourchette":"<ex:45-75 min>","niveau_complexite":"<Simple|Modéré|Complexe>","explication":"<2-3 phrases>","conseils":"<1 conseil>","type_service_normalise":"<nom standardisé>"}';

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
        error_log("[Estimertemps] Erreur: $curlError HTTP:$httpCode");
        return fallbackEstimation($service, $marque);
    }

    $body    = json_decode($response, true);
    $content = trim($body['choices'][0]['message']['content'] ?? '');
    $content = preg_replace('/^```(?:json)?\s*/i', '', $content);
    $content = preg_replace('/\s*```$/', '', trim($content));

    $estimation = json_decode($content, true);

    if (json_last_error() !== JSON_ERROR_NONE || !isset($estimation['duree_min'])) {
        error_log("[Estimertemps] JSON invalide: $content");
        return fallbackEstimation($service, $marque);
    }

    return [
        'success'                => true,
        'duree_min'              => (int)$estimation['duree_min'],
        'fourchette'             => $estimation['fourchette']             ?? "{$estimation['duree_min']}-" . ($estimation['duree_min'] + 30) . " min",
        'niveau_complexite'      => $estimation['niveau_complexite']      ?? 'Modéré',
        'explication'            => $estimation['explication']            ?? '',
        'conseils'               => $estimation['conseils']               ?? '',
        'type_service_normalise' => $estimation['type_service_normalise'] ?? $service,
        'source'                 => '🤖 Groq IA',
    ];
}

function fallbackEstimation(string $service, string $marque): array
{
    $s = mb_strtolower($service);
   $duree = match(true) {
    str_contains($s, 'lavage')        => 40,
    str_contains($s, 'vidange')       => 60,
    str_contains($s, 'frein')         => 90,
    str_contains($s, 'pneu')          => 45,
    str_contains($s, 'révision')      => 120,
    str_contains($s, 'courroie')      => 150,
    str_contains($s, 'climatisation') => 75,
    default                           => 90,
};
    return [
        'success'                => true,
        'duree_min'              => $duree,
        'fourchette'             => "$duree-" . ($duree + 30) . " min",
        'niveau_complexite'      => 'Modéré',
        'explication'            => "Estimation standard pour « $service »" . ($marque ? " sur $marque" : '') . ".",
        'conseils'               => 'Prévoir un véhicule de remplacement si besoin.',
        'type_service_normalise' => $service,
        'source'                 => '📊 Standard (IA indisponible)',
    ];
}

echo json_encode(
    estimerAvecGroq($type_service, $kilometrage, $marque, $annee, $modele),
    JSON_UNESCAPED_UNICODE
);
exit;