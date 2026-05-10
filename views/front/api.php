<?php
/**
 * api_groq.php — Configuration API Groq (partagée par tous les fichiers IA)
 * Placez ce fichier dans le même dossier que Estimertemps.php et predict_next_rdv.php
 */
define('GROQ_API_KEY', 'XXXX'); // 🔑 Votre clé Groq ici
define('GROQ_MODEL',   'llama-3.3-70b-versatile');
define('GROQ_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
function callGroqAPI($messages) {
    $payload = json_encode([
        'model' => GROQ_MODEL,
        'messages' => $messages,
        'temperature' => 0.7
    ]);

    $ch = curl_init(GROQ_API_URL);
    curl_setopt_array($ch, [
         CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . GROQ_API_KEY,
        ],
        CURLOPT_POSTFIELDS     => $payload,
        // --- CORRECTIF INDISPENSABLE POUR TON WAMP/XAMPP ---
        CURLOPT_SSL_VERIFYPEER => false, 
        CURLOPT_SSL_VERIFYHOST => false,
        // --------------------------------------------------
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) return "Erreur : " . $error;
    
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? 'Erreur de réponse';
}

?>
