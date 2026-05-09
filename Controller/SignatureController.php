<?php
// controller/SignatureController.php
// ─────────────────────────────────────────────────────────────
//  Sauvegarde et récupération de la signature utilisateur
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

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit();
}

require_once __DIR__ . '/../models/db.php';

$uid  = (int)$_SESSION['user_id'];
$raw  = file_get_contents('php://input');
$body = json_decode($raw, true);
$action = $body['action'] ?? '';

switch ($action) {

    // ── Sauvegarder / mettre à jour ───────────────────────────
    case 'save': {
        $b64     = $body['signature_b64'] ?? '';
        $context = in_array($body['context'] ?? '', ['register','profile_update','action'])
                   ? $body['context'] : 'profile_update';

        if (empty($b64) || strlen($b64) < 100) {
            echo json_encode(['success' => false, 'message' => 'Signature vide ou invalide.']);
            exit();
        }

        // Nettoyer le data URI si présent
        $clean = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
        $clean = str_replace(' ', '+', $clean);

        // Valider que c'est bien du base64 PNG/JPEG
        $decoded = base64_decode($clean, true);
        if (!$decoded || strlen($decoded) < 200) {
            echo json_encode(['success' => false, 'message' => 'Image invalide.']);
            exit();
        }

        $pdo->prepare(
            "INSERT INTO client_signature (id_client, signature_b64, context, created_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE
                signature_b64 = VALUES(signature_b64),
                context       = VALUES(context),
                updated_at    = NOW()"
        )->execute([$uid, $clean, $context]);

        echo json_encode(['success' => true, 'message' => 'Signature sauvegardée.']);
        break;
    }

    // ── Récupérer la signature de l'utilisateur ───────────────
    case 'get': {
        $stmt = $pdo->prepare("SELECT signature_b64, context, updated_at FROM client_signature WHERE id_client = ?");
        $stmt->execute([$uid]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            echo json_encode([
                'success'       => true,
                'signature_b64' => $row['signature_b64'],
                'context'       => $row['context'],
                'updated_at'    => $row['updated_at'],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Aucune signature enregistrée.']);
        }
        break;
    }

    // ── Vérifier si une signature existe (admin / autre module) ─
    case 'check': {
        $targetId = isset($body['id_client']) ? (int)$body['id_client'] : $uid;
        // Seul un admin peut vérifier un autre utilisateur
        if ($targetId !== $uid && (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin')) {
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit();
        }
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM client_signature WHERE id_client = ?");
        $stmt->execute([$targetId]);
        echo json_encode(['success' => true, 'has_signature' => $stmt->fetchColumn() > 0]);
        break;
    }

    default:
        echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
}