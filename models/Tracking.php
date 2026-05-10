<?php
// models/Tracking.php — Modèle Suivi des visites (user_tracking)

require_once __DIR__ . '/db.php';

class Tracking
{
    /** Enregistre une visite. */
    public static function record(int $userId, string $page, int $durationSeconds): bool
    {
        global $pdo;
        $page = substr(trim($page), 0, 100);
        $durationSeconds = max(0, min(7200, $durationSeconds));
        return $pdo->prepare(
            'INSERT INTO user_tracking (id_client, page, duration_s, visited_at) VALUES (?, ?, ?, NOW())'
        )->execute([$userId, $page, $durationSeconds]);
    }

    /** Statistiques globales (admin). */
    public static function globalStats(): array
    {
        global $pdo;
        $global = $pdo->query(
            'SELECT COUNT(*) as total_visits, COUNT(DISTINCT id_client) as unique_users,
                    ROUND(AVG(duration_s)) as avg_s, SUM(duration_s) as total_s
             FROM user_tracking'
        )->fetch();

        $byDay = $pdo->query(
            'SELECT DATE(visited_at) as day, COUNT(*) as visits, SUM(duration_s) as total_s
             FROM user_tracking WHERE visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY DATE(visited_at) ORDER BY day ASC'
        )->fetchAll();

        $topPages = $pdo->query(
            'SELECT page, COUNT(*) as visits, ROUND(AVG(duration_s)) as avg_s
             FROM user_tracking GROUP BY page ORDER BY visits DESC LIMIT 8'
        )->fetchAll();

        $topClients = $pdo->query(
            'SELECT c.prenom, c.nom, c.email,
                    COUNT(t.id) as visits, SUM(t.duration_s) as total_s,
                    MAX(t.visited_at) as last_visit
             FROM user_tracking t JOIN client c ON c.id_client = t.id_client
             GROUP BY t.id_client ORDER BY visits DESC LIMIT 10'
        )->fetchAll();

        return compact('global', 'byDay', 'topPages', 'topClients');
    }

    /** Statistiques personnelles d'un client (30 derniers jours). */
    public static function userStats(int $userId): array
    {
        global $pdo;

        $pages = $pdo->prepare(
            'SELECT page, COUNT(*) as v, SUM(duration_s) as s
             FROM user_tracking WHERE id_client = ?
             AND visited_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
             GROUP BY page ORDER BY v DESC LIMIT 6'
        );
        $pages->execute([$userId]);

        $byDay = $pdo->prepare(
            'SELECT DATE(visited_at) as day, COUNT(*) as v, SUM(duration_s) as s
             FROM user_tracking WHERE id_client = ?
             AND visited_at >= DATE_SUB(NOW(), INTERVAL 14 DAY)
             GROUP BY DATE(visited_at) ORDER BY day ASC'
        );
        $byDay->execute([$userId]);

        $global = $pdo->prepare(
            'SELECT COUNT(*) as total_visits, SUM(duration_s) as total_s,
                    MAX(visited_at) as last_visit
             FROM user_tracking WHERE id_client = ?'
        );
        $global->execute([$userId]);

        return [
            'pages'  => $pages->fetchAll(),
            'by_day' => $byDay->fetchAll(),
            'global' => $global->fetch(),
        ];
    }
}
