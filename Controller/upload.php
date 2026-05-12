<?php
/**
 * upload.php — Endpoint upload fichiers (images + audio)
 * Placer dans : Controller/upload.php
 * Dossier de stockage : web_voiture/uploads/media/  (créé automatiquement)
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

function fail(string $msg): void {
    echo json_encode(['success' => false, 'message' => $msg]);
    exit;
}
function ok(array $data): void {
    echo json_encode(['success' => true] + $data);
    exit;
}

// ─── Chemin absolu robuste (fonctionne sous XAMPP Windows & Linux) ────────────
// upload.php est dans Controller/  →  on remonte d'un niveau = racine du projet
$projectRoot = dirname(__DIR__);                        // ex: C:\xampp\htdocs\web_voiture
$uploadDir   = $projectRoot . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'media' . DIRECTORY_SEPARATOR;
$uploadUrl   = '../../uploads/media/';   // URL relative depuis front.php

// ─── Créer les dossiers si nécessaire ────────────────────────────────────────
if (!is_dir($uploadDir)) {
    // Créer uploads/ puis uploads/media/ récursivement
    if (!@mkdir($uploadDir, 0755, true)) {
        // Si mkdir échoue, donner un message précis
        $parent = dirname($uploadDir);
        if (!is_writable(dirname($parent))) {
            fail('Impossible de créer le dossier uploads/media/ — vérifiez que le dossier '
                . dirname($parent) . ' est accessible en écriture par Apache/XAMPP.');
        }
        fail('Impossible de créer le dossier uploads/media/ — erreur système.');
    }
}

// Vérifier que le dossier est accessible en écriture
if (!is_writable($uploadDir)) {
    fail('Le dossier uploads/media/ existe mais n\'est pas accessible en écriture.');
}

// ─── Vérifier qu'un fichier est arrivé ───────────────────────────────────────
if (empty($_FILES['file'])) {
    fail('Aucun fichier reçu.');
}

$file = $_FILES['file'];
$type = $_POST['type'] ?? 'image'; // 'image' ou 'audio'

if ($file['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE   => 'Fichier trop grand (limite php.ini : upload_max_filesize)',
        UPLOAD_ERR_FORM_SIZE  => 'Fichier trop grand (limite formulaire)',
        UPLOAD_ERR_PARTIAL    => 'Upload partiel, réessayez',
        UPLOAD_ERR_NO_FILE    => 'Aucun fichier reçu',
        UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant sur le serveur',
        UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire sur le disque',
        UPLOAD_ERR_EXTENSION  => 'Upload bloqué par une extension PHP',
    ];
    fail($uploadErrors[$file['error']] ?? 'Erreur upload code ' . $file['error']);
}

// ─── Limites de taille ────────────────────────────────────────────────────────
$maxImage = 5  * 1024 * 1024; //  5 Mo
$maxAudio = 15 * 1024 * 1024; // 15 Mo

if ($type === 'image' && $file['size'] > $maxImage) fail('Image trop grande (max 5 Mo).');
if ($type === 'audio' && $file['size'] > $maxAudio) fail('Vocal trop long (max 15 Mo).');

// ─── Types MIME autorisés ─────────────────────────────────────────────────────
$allowedImage = ['image/jpeg','image/png','image/gif','image/webp'];
$allowedAudio = ['audio/webm','audio/ogg','audio/mp4','audio/mpeg',
                 'audio/wav','audio/x-m4a','application/ogg','video/webm']; // video/webm = Chrome WebM audio

$mime = mime_content_type($file['tmp_name']);

if ($type === 'image' && !in_array($mime, $allowedImage)) {
    fail('Format image non supporté (' . $mime . '). Utilisez JPG, PNG, GIF ou WEBP.');
}
if ($type === 'audio' && !in_array($mime, $allowedAudio)) {
    // Certains navigateurs déclarent webm comme video/webm — on accepte quand même
    $nameExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($nameExt, ['webm','ogg','mp4','m4a','wav','mp3'])) {
        fail('Format audio non supporté (' . $mime . ').');
    }
}

// ─── Extension de sortie ──────────────────────────────────────────────────────
$extMap = [
    'image/jpeg'      => 'jpg',  'image/png'   => 'png',
    'image/gif'       => 'gif',  'image/webp'  => 'webp',
    'audio/webm'      => 'webm', 'video/webm'  => 'webm',
    'audio/ogg'       => 'ogg',  'application/ogg' => 'ogg',
    'audio/mp4'       => 'mp4',  'audio/mpeg'  => 'mp3',
    'audio/wav'       => 'wav',  'audio/x-m4a' => 'm4a',
];
$ext = $extMap[$mime] ?? pathinfo($file['name'], PATHINFO_EXTENSION) ?: 'bin';

// ─── Nom de fichier unique & sécurisé ─────────────────────────────────────────
$filename = $type . '_' . uniqid('', true) . '.' . $ext;
$destPath = $uploadDir . $filename;

if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    fail('Impossible de sauvegarder le fichier sur le serveur (move_uploaded_file a échoué).');
}

// ─── Succès ───────────────────────────────────────────────────────────────────
ok([
    'path'     => $uploadUrl . $filename,
    'filename' => $filename,
    'mime'     => $mime,
    'size'     => $file['size'],
    'type'     => $type,
]);
?>