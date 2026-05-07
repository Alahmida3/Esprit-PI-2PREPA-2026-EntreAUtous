<?php
// controller/FaceController.php
// ─────────────────────────────────────────────────────────────
//  3 actions :
//   • face_enroll          → enrôlement par email (page login, onglet "Enregistrer")
//   • face_enroll_session  → enrôlement depuis le profil (session active, pas besoin d'email)
//   • face_login           → connexion par reconnaissance faciale
// ─────────────────────────────────────────────────────────────

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path'     => '/autout/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

require_once __DIR__ . '/../models/db.php';

$raw    = file_get_contents('php://input');
$body   = json_decode($raw, true);
$action = $body['action'] ?? '';

switch ($action) {
    case 'face_enroll':         faceEnroll($pdo, $body);        break;
    case 'face_enroll_session': faceEnrollSession($pdo, $body); break;
    case 'face_login':          faceLogin($pdo, $body);         break;
    default:
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
}

// ══════════════════════════════════════════════════════════════
//  ENRÔLEMENT PAR EMAIL  (onglet "Enregistrer" de login.php)
// ══════════════════════════════════════════════════════════════
function faceEnroll($pdo, $body) {
    $email    = filter_var(trim($body['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $imageB64 = $body['image'] ?? '';

    if (!$email || empty($imageB64)) {
        echo json_encode(['success' => false, 'message' => 'Email ou image manquant.']);
        return;
    }

    $stmt = $pdo->prepare("SELECT id_client FROM client WHERE email = ?");
    $stmt->execute([$email]);
    $client = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$client) {
        echo json_encode(['success' => false, 'message' => 'Aucun compte trouvé pour cet email.']);
        return;
    }

    echo json_encode(storeFace($pdo, (int)$client['id_client'], $imageB64));
}

// ══════════════════════════════════════════════════════════════
//  ENRÔLEMENT DEPUIS LE PROFIL  (session active, pas d'email)
// ══════════════════════════════════════════════════════════════
function faceEnrollSession($pdo, $body) {
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Non authentifié.']);
        return;
    }

    $imageB64 = $body['image'] ?? '';
    if (empty($imageB64)) {
        echo json_encode(['success' => false, 'message' => 'Image manquante.']);
        return;
    }

    echo json_encode(storeFace($pdo, (int)$_SESSION['user_id'], $imageB64));
}

// ══════════════════════════════════════════════════════════════
//  STOCKAGE COMMUN  (appelé par les deux fonctions ci-dessus)
// ══════════════════════════════════════════════════════════════
function storeFace($pdo, int $id_client, string $imageB64): array {
    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageB64);
    $imageData = str_replace(' ', '+', $imageData);
    $imageRaw  = base64_decode($imageData);

    if (!$imageRaw || strlen($imageRaw) < 500) {
        return ['success' => false, 'message' => 'Image invalide ou trop petite.'];
    }

    $signature = computeImageSignature($imageRaw);
    $miniature = base64_encode(resizeImage($imageRaw, 64, 64));

    try {
        $pdo->prepare(
            "INSERT INTO client_face (id_client, signature, miniature, created_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                 signature  = VALUES(signature),
                 miniature  = VALUES(miniature),
                 updated_at = NOW()"
        )->execute([$id_client, $signature, $miniature]);
    } catch (PDOException $e) {
        error_log("storeFace error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur serveur lors de la sauvegarde.'];
    }

    return ['success' => true, 'message' => 'Visage enregistré avec succès !'];
}

// ══════════════════════════════════════════════════════════════
//  CONNEXION PAR VISAGE
// ══════════════════════════════════════════════════════════════
function faceLogin($pdo, $body) {
    $imageB64 = $body['image'] ?? '';
    if (empty($imageB64)) {
        echo json_encode(['success' => false, 'message' => 'Image manquante.']);
        return;
    }

    $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageB64);
    $imageData = str_replace(' ', '+', $imageData);
    $imageRaw  = base64_decode($imageData);

    if (!$imageRaw) {
        echo json_encode(['success' => false, 'message' => 'Image invalide.']);
        return;
    }

    $signatureInput = computeImageSignature($imageRaw);

    // Récupérer tous les visages en base
    try {
        $all = $pdo->query(
            "SELECT cf.id_client, cf.signature, c.prenom, c.nom, c.email
             FROM client_face cf
             JOIN client c ON c.id_client = cf.id_client"
        )->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Table client_face introuvable. Exécutez migration.sql.']);
        return;
    }

    if (empty($all)) {
        echo json_encode(['success' => false, 'message' => 'Aucun visage enregistré en base. Inscrivez-vous d\'abord.']);
        return;
    }

    // Trouver le meilleur match
    $bestMatch = null;
    $bestScore = PHP_INT_MAX;
    $THRESHOLD = 25;   // ← ajuster si trop strict (monter à 30) ou trop permissif (baisser à 18)

    foreach ($all as $row) {
        $dist = hammingDistance($signatureInput, $row['signature']);
        if ($dist < $bestScore) {
            $bestScore = $dist;
            $bestMatch = $row;
        }
    }

    if ($bestMatch && $bestScore <= $THRESHOLD) {
        // ── Connexion réussie ─────────────────────────────────
        $uid  = (int)$bestMatch['id_client'];
        $stmt = $pdo->prepare("SELECT * FROM client WHERE id_client = ?");
        $stmt->execute([$uid]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id_client'];
        $_SESSION['role']      = 'client';
        $_SESSION['user']      = $user['prenom'];
        $_SESSION['prenom']    = $user['prenom'];
        $_SESSION['nom']       = $user['nom'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['telephone'] = $user['telephone'] ?? '';
        $_SESSION['adresse']   = $user['adresse']   ?? '';

        echo json_encode([
            'success'  => true,
            'message'  => 'Visage reconnu ! Bienvenue ' . $user['prenom'] . ' !',
            'redirect' => '/autout/views/front/home.php',
            'score'    => $bestScore,
        ]);
    } else {
        // ── Visage non reconnu ────────────────────────────────
        echo json_encode([
            'success' => false,
            'message' => 'Visage non reconnu. Assurez-vous d\'avoir enregistré votre Face ID lors de l\'inscription, ou utilisez la connexion classique.',
            'score'   => $bestScore ?? -1,
        ]);
    }
}

// ══════════════════════════════════════════════════════════════
//  HELPERS IMAGE
// ══════════════════════════════════════════════════════════════

/** pHash 8×8 : signature perceptuelle de l'image */
function computeImageSignature(string $imageRaw): string {
    $img = @imagecreatefromstring($imageRaw);
    if (!$img) return str_repeat('0', 64);

    $small = imagecreatetruecolor(8, 8);
    imagecopyresampled($small, $img, 0, 0, 0, 0, 8, 8, imagesx($img), imagesy($img));
    imagedestroy($img);

    $pixels = [];
    for ($y = 0; $y < 8; $y++) {
        for ($x = 0; $x < 8; $x++) {
            $rgb      = imagecolorat($small, $x, $y);
            $pixels[] = (int)(
                0.299 * (($rgb >> 16) & 0xFF) +
                0.587 * (($rgb >> 8)  & 0xFF) +
                0.114 * ( $rgb        & 0xFF)
            );
        }
    }
    imagedestroy($small);

    $avg  = array_sum($pixels) / 64;
    $hash = '';
    foreach ($pixels as $p) $hash .= ($p >= $avg) ? '1' : '0';
    return $hash;
}

/** Distance de Hamming entre deux hashes binaires */
function hammingDistance(string $a, string $b): int {
    if (strlen($a) !== strlen($b)) return PHP_INT_MAX;
    $d = 0;
    for ($i = 0, $l = strlen($a); $i < $l; $i++) {
        if ($a[$i] !== $b[$i]) $d++;
    }
    return $d;
}

/** Redimensionner une image en JPEG */
function resizeImage(string $raw, int $w, int $h): string {
    $src = @imagecreatefromstring($raw);
    if (!$src) return $raw;
    $dst = imagecreatetruecolor($w, $h);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
    imagedestroy($src);
    ob_start();
    imagejpeg($dst, null, 70);
    imagedestroy($dst);
    return ob_get_clean();
}