<?php
// controller/JourneyController.php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
session_set_cookie_params(['lifetime'=>60*60*24*7,'path'=>'/autout/','secure'=>false,'httponly'=>true,'samesite'=>'Strict']);
session_start();
if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false,'message'=>'Non authentifié']); exit(); }
require_once __DIR__ . '/../models/db.php';

$uid  = (int)$_SESSION['user_id'];
$body = json_decode(file_get_contents('php://input'), true) ?? [];
$act  = $body['action'] ?? '';
$ALLOWED = ['step_profile','step_diagnostic','step_garage','step_vehicle','step_rdv','step_message'];

try {
    $pdo->prepare("INSERT IGNORE INTO user_journey (id_client) VALUES (?)")->execute([$uid]);
} catch(PDOException $e) {
    echo json_encode(['success'=>false,'message'=>'Table user_journey manquante. Exécutez migration_journey_tracking.sql']); exit();
}

switch ($act) {
    case 'mark_step': {
        $step = $body['step'] ?? '';
        if (!in_array($step, $ALLOWED, true)) { echo json_encode(['success'=>false,'message'=>'Étape invalide.']); exit(); }
        $pdo->prepare("UPDATE user_journey SET `$step`=1, score=(step_profile+step_diagnostic+step_garage+step_vehicle+step_rdv+step_message) WHERE id_client=? AND `$step`=0")->execute([$uid]);
        $r = $pdo->prepare("SELECT * FROM user_journey WHERE id_client=?"); $r->execute([$uid]);
        echo json_encode(['success'=>true,'journey'=>$r->fetch(PDO::FETCH_ASSOC)]);
        break;
    }
    case 'get': {
        $r = $pdo->prepare("SELECT * FROM user_journey WHERE id_client=?"); $r->execute([$uid]);
        echo json_encode(['success'=>true,'journey'=>$r->fetch(PDO::FETCH_ASSOC)]);
        break;
    }
    case 'reset': {
        $pdo->prepare("UPDATE user_journey SET step_profile=0,step_diagnostic=0,step_garage=0,step_vehicle=0,step_rdv=0,step_message=0,score=0 WHERE id_client=?")->execute([$uid]);
        echo json_encode(['success'=>true]);
        break;
    }
    default: echo json_encode(['success'=>false,'message'=>'Action inconnue.']);
}