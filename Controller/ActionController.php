<?php
// controller/ActionController.php
// ══════════════════════════════════════════════════════════════
//  Endpoint JSON unique pour toutes les actions AJAX :
//
//  FACE :
//    face_enroll          → enrôle par email (login.php)
//    face_enroll_session  → enrôle depuis session (profile.php)
//    face_login           → connexion faciale
//
//  JOURNEY :
//    journey_mark         → marque une étape
//    journey_get          → récupère le parcours
//    journey_reset        → réinitialise le parcours
//
//  TRACKING :
//    tracking_track       → enregistre une visite
//    tracking_stats       → stats globales (admin)
//    tracking_my_stats    → stats personnelles (client)
//
//  SIGNATURE :
//    signature_save       → sauvegarde une signature
//    signature_get        → récupère la signature
//    signature_check      → vérifie l'existence
// ══════════════════════════════════════════════════════════════

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

session_set_cookie_params([
    'lifetime' => 60*60*24*7,
    'path'     => '/autout/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

require_once __DIR__ . '/../models/User.php';   // inclut db.php + toutes les classes

$body   = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $body['action'] ?? '';

// ── Dispatcher ────────────────────────────────────────────────
switch ($action) {

    // ── FACE ──────────────────────────────────────────────────
    case 'face_enroll':
        $email = filter_var(trim($body['email'] ?? ''), FILTER_VALIDATE_EMAIL);
        $img   = $body['image'] ?? '';
        if (!$email || empty($img)) { out(['success'=>false,'message'=>'Email ou image manquant.']); }
        $s = $pdo->prepare("SELECT id_client FROM client WHERE email=?");
        $s->execute([$email]);
        $c = $s->fetch();
        if (!$c) { out(['success'=>false,'message'=>'Aucun compte pour cet email.']); }
        out(Face::store($pdo, (int)$c['id_client'], $img));
        break;

    case 'face_enroll_session':
        requireAuth();
        $img = $body['image'] ?? '';
        if (empty($img)) { out(['success'=>false,'message'=>'Image manquante.']); }
        out(Face::store($pdo, (int)$_SESSION['user_id'], $img));
        break;

    case 'face_login':
        $img = $body['image'] ?? '';
        if (empty($img)) { out(['success'=>false,'message'=>'Image manquante.']); }
        $result = Face::identify($pdo, $img);
        if ($result['success']) {
            $user = $result['user'];
            session_regenerate_id(true);
            $_SESSION = [
                'user_id'   => $user['id_client'],
                'role'      => 'client',
                'user'      => $user['prenom'],
                'prenom'    => $user['prenom'],
                'nom'       => $user['nom'],
                'email'     => $user['email'],
                'telephone' => $user['telephone'] ?? '',
                'adresse'   => $user['adresse']   ?? '',
            ];
            out(['success'=>true,'message'=>'Bienvenue '.$user['prenom'].' !',
                 'redirect'=>'/autout/views/front/home.php','score'=>$result['score']]);
        } else {
            out($result);
        }
        break;

    // ── JOURNEY ───────────────────────────────────────────────
    case 'journey_mark':
        requireAuth();
        $step = $body['step'] ?? '';
        $j = Journey::markStep($pdo, (int)$_SESSION['user_id'], $step);
        out($j ? ['success'=>true,'journey'=>$j] : ['success'=>false,'message'=>'Étape invalide.']);
        break;

    case 'journey_get':
        requireAuth();
        $j = Journey::getOrCreate($pdo, (int)$_SESSION['user_id']);
        out(['success'=>true,'journey'=>$j]);
        break;

    case 'journey_reset':
        requireAuth();
        Journey::reset($pdo, (int)$_SESSION['user_id']);
        out(['success'=>true]);
        break;

    // ── TRACKING ──────────────────────────────────────────────
    case 'tracking_track':
        if (!isset($_SESSION['user_id'])) { out(['success'=>false]); }
        $ok = Tracking::track($pdo, (int)$_SESSION['user_id'],
            $body['page'] ?? 'unknown', (int)($body['duration'] ?? 0));
        out(['success'=>$ok]);
        break;

    case 'tracking_stats':
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            http_response_code(403); out(['success'=>false]);
        }
        try { out(['success'=>true] + Tracking::globalStats($pdo)); }
        catch (PDOException $e) { out(['success'=>false,'message'=>$e->getMessage()]); }
        break;

    case 'tracking_my_stats':
        requireAuth();
        try { out(['success'=>true] + Tracking::myStats($pdo, (int)$_SESSION['user_id'])); }
        catch (PDOException $e) { out(['success'=>false,'message'=>$e->getMessage()]); }
        break;

    // ── SIGNATURE ─────────────────────────────────────────────
    case 'signature_save':
        requireAuth();
        out(Signature::save($pdo, (int)$_SESSION['user_id'], $body['signature_b64'] ?? '', $body['context'] ?? 'profile_update'));
        break;

    case 'signature_get':
        requireAuth();
        out(Signature::get($pdo, (int)$_SESSION['user_id']));
        break;

    case 'signature_check':
        requireAuth();
        $target = isset($body['id_client']) ? (int)$body['id_client'] : (int)$_SESSION['user_id'];
        if ($target !== (int)$_SESSION['user_id'] && ($_SESSION['role'] ?? '') !== 'admin') {
            http_response_code(403); out(['success'=>false]);
        }
        out(['success'=>true,'has_signature'=>Signature::exists($pdo,$target)]);
        break;

    default:
        out(['success'=>false,'message'=>'Action inconnue.']);
}

// ── Helpers ───────────────────────────────────────────────────
function out(array $data): never { echo json_encode($data); exit(); }

function requireAuth(): void
{
    if (!isset($_SESSION['user_id'])) {
        http_response_code(401);
        out(['success'=>false,'message'=>'Non authentifié.']);
    }
}