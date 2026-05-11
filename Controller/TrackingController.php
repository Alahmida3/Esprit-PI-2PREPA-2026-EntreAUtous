<?php
// controller/TrackingController.php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
session_set_cookie_params(['lifetime'=>60*60*24*7,'path'=>'/Esprit-PI-2PREPA-2026-EntreAUtous/','secure'=>false,'httponly'=>true,'samesite'=>'Strict']);
session_start();

require_once __DIR__ . '/../models/db.php';

$body = json_decode(file_get_contents('php://input'), true) ?? [];
$act  = $body['action'] ?? 'track';

// ── TRACK : enregistrer une visite (utilisateurs connectés uniquement) ──
if ($act === 'track') {
    if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit(); }
    $uid      = (int)$_SESSION['user_id'];
    $page     = substr(trim($body['page'] ?? 'unknown'), 0, 100);
    $duration = max(0, min(7200, (int)($body['duration'] ?? 0)));
    if (empty($page)) { echo json_encode(['success'=>false]); exit(); }
    try {
        $pdo->prepare("INSERT INTO user_tracking (id_client,page,duration_s,visited_at) VALUES(?,?,?,NOW())")->execute([$uid,$page,$duration]);
        echo json_encode(['success'=>true]);
    } catch(PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Table manquante. Exécutez migration_journey_tracking.sql']);
    }
    exit();
}

// ── STATS ADMIN : statistiques globales ─────────────────────
if ($act === 'stats') {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403); echo json_encode(['success'=>false]); exit();
    }
    try {
        $byDay = $pdo->query(
            "SELECT DATE(visited_at) as day, COUNT(*) as visits, SUM(duration_s) as total_s
             FROM user_tracking WHERE visited_at >= DATE_SUB(NOW(),INTERVAL 30 DAY)
             GROUP BY DATE(visited_at) ORDER BY day ASC"
        )->fetchAll(PDO::FETCH_ASSOC);

        $topPages = $pdo->query(
            "SELECT page, COUNT(*) as visits, ROUND(AVG(duration_s)) as avg_s
             FROM user_tracking GROUP BY page ORDER BY visits DESC LIMIT 8"
        )->fetchAll(PDO::FETCH_ASSOC);

        $topClients = $pdo->query(
            "SELECT c.prenom, c.nom, c.email,
                    COUNT(t.id) as visits, SUM(t.duration_s) as total_s,
                    MAX(t.visited_at) as last_visit
             FROM user_tracking t JOIN client c ON c.id_client=t.id_client
             GROUP BY t.id_client ORDER BY visits DESC LIMIT 10"
        )->fetchAll(PDO::FETCH_ASSOC);

        $global = $pdo->query(
            "SELECT COUNT(*) as total_visits, COUNT(DISTINCT id_client) as unique_users,
                    ROUND(AVG(duration_s)) as avg_s, SUM(duration_s) as total_s
             FROM user_tracking"
        )->fetch(PDO::FETCH_ASSOC);

        echo json_encode(['success'=>true,'by_day'=>$byDay,'top_pages'=>$topPages,'top_clients'=>$topClients,'global'=>$global]);
    } catch(PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Erreur: '.$e->getMessage()]);
    }
    exit();
}

// ── STATS USER : stats personnelles ─────────────────────────
if ($act === 'my_stats') {
    if (!isset($_SESSION['user_id'])) { echo json_encode(['success'=>false]); exit(); }
    $uid = (int)$_SESSION['user_id'];
    try {
        $pages = $pdo->prepare(
            "SELECT page, COUNT(*) as visits, SUM(duration_s) as total_s
             FROM user_tracking WHERE id_client=?
             AND visited_at >= DATE_SUB(NOW(),INTERVAL 30 DAY)
             GROUP BY page ORDER BY visits DESC LIMIT 6"
        ); $pages->execute([$uid]);

        $byDay = $pdo->prepare(
            "SELECT DATE(visited_at) as day, COUNT(*) as visits, SUM(duration_s) as total_s
             FROM user_tracking WHERE id_client=?
             AND visited_at >= DATE_SUB(NOW(),INTERVAL 14 DAY)
             GROUP BY DATE(visited_at) ORDER BY day ASC"
        ); $byDay->execute([$uid]);

        $global = $pdo->prepare(
            "SELECT COUNT(*) as total_visits, SUM(duration_s) as total_s, MAX(visited_at) as last_visit
             FROM user_tracking WHERE id_client=?"
        ); $global->execute([$uid]);

        echo json_encode([
            'success'  => true,
            'pages'    => $pages->fetchAll(PDO::FETCH_ASSOC),
            'by_day'   => $byDay->fetchAll(PDO::FETCH_ASSOC),
            'global'   => $global->fetch(PDO::FETCH_ASSOC),
        ]);
    } catch(PDOException $e) {
        echo json_encode(['success'=>false,'message'=>'Erreur: '.$e->getMessage()]);
    }
    exit();
}

echo json_encode(['success'=>false,'message'=>'Action inconnue.']);