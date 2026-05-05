<?php
/**
 * Discussion.php — Modèle Discussion
 * Nouveau schéma : user_id (client) + garage_id
 * Hérite de Model (connexion PDO intégrée, sans config/Database.php)
 */
require_once __DIR__ . '/Model.php';

class Discussion extends Model {

    // ── CREATE ────────────────────────────────────────────────
    public static function create(int $user_id, int $garage_id, string $objet): int {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            INSERT INTO discussion (user_id, garage_id, objet, created_at)
            VALUES (:uid, :gid, :objet, NOW())
        ");
        $stmt->execute([':uid' => $user_id, ':gid' => $garage_id, ':objet' => trim($objet)]);
        return (int)$pdo->lastInsertId();
    }

    // ── READ ALL (admin) ──────────────────────────────────────
    public static function getAll(): array {
        $pdo  = self::db();
        $stmt = $pdo->query("
            SELECT
                d.id, d.objet, d.created_at, d.user_id, d.garage_id,
                u.nom                                               AS nom_user,
                g.nom_garages                                       AS nom_garage,
                (SELECT COUNT(*) FROM message m
                 WHERE m.discussion_id = d.id)                      AS nb_messages,
                (SELECT MAX(m2.date_envoi) FROM message m2
                 WHERE m2.discussion_id = d.id)                     AS dernier_message,
                (SELECT m3.contenu FROM message m3
                 WHERE m3.discussion_id = d.id
                 ORDER BY m3.date_envoi DESC LIMIT 1)               AS dernier_contenu
            FROM discussion d
            LEFT JOIN user    u ON u.id = d.user_id
            LEFT JOIN garages g ON g.id = d.garage_id
            ORDER BY COALESCE(dernier_message, d.created_at) DESC
        ");
        return $stmt->fetchAll();
    }

    // ── READ ONE ──────────────────────────────────────────────
    public static function getById(int $id): ?array {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            SELECT d.*, u.nom AS nom_user, g.nom_garages AS nom_garage
            FROM discussion d
            LEFT JOIN user    u ON u.id = d.user_id
            LEFT JOIN garages g ON g.id = d.garage_id
            WHERE d.id = :id
        ");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── READ par user (front) ─────────────────────────────────
    public static function getByUser(int $user_id): array {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            SELECT d.id, d.objet, d.created_at, d.garage_id,
                   g.nom_garages AS nom_garage,
                   (SELECT COUNT(*) FROM message m WHERE m.discussion_id = d.id)        AS nb_messages,
                   (SELECT MAX(m2.date_envoi) FROM message m2 WHERE m2.discussion_id=d.id) AS dernier_message,
                   (SELECT m3.contenu FROM message m3 WHERE m3.discussion_id = d.id
                    ORDER BY m3.date_envoi DESC LIMIT 1)                                AS dernier_contenu
            FROM discussion d
            LEFT JOIN garages g ON g.id = d.garage_id
            WHERE d.user_id = :uid
            ORDER BY COALESCE(dernier_message, d.created_at) DESC
        ");
        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetchAll();
    }

    // ── UPDATE ────────────────────────────────────────────────
    public static function update(int $id, int $user_id, int $garage_id, string $objet): bool {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            UPDATE discussion SET user_id=:uid, garage_id=:gid, objet=:objet WHERE id=:id
        ");
        return $stmt->execute([':uid'=>$user_id,':gid'=>$garage_id,':objet'=>trim($objet),':id'=>$id]);
    }

    // ── DELETE ────────────────────────────────────────────────
    public static function delete(int $id): bool {
        $pdo = self::db();
        $pdo->prepare("DELETE FROM message WHERE discussion_id=:id")->execute([':id'=>$id]);
        return $pdo->prepare("DELETE FROM discussion WHERE id=:id")->execute([':id'=>$id]);
    }

    // ── SEARCH ────────────────────────────────────────────────
    public static function search(string $q): array {
        $pdo  = self::db();
        $like = '%'.$q.'%';
        $stmt = $pdo->prepare("
            SELECT d.*, u.nom AS nom_user, g.nom_garages AS nom_garage,
                   (SELECT COUNT(*) FROM message m WHERE m.discussion_id=d.id) AS nb_messages
            FROM discussion d
            LEFT JOIN user    u ON u.id = d.user_id
            LEFT JOIN garages g ON g.id = d.garage_id
            WHERE d.objet LIKE :q1 OR u.nom LIKE :q2 OR g.nom_garages LIKE :q3
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like]);
        return $stmt->fetchAll();
    }

    // ── STATS ─────────────────────────────────────────────────
    public static function getStats(): array {
        $pdo = self::db();
        return [
            'total_discussions' => (int)$pdo->query("SELECT COUNT(*) FROM discussion")->fetchColumn(),
            'total_messages'    => (int)$pdo->query("SELECT COUNT(*) FROM message")->fetchColumn(),
            'total_users'       => (int)$pdo->query("SELECT COUNT(*) FROM user")->fetchColumn(),
            'total_garages'     => (int)$pdo->query("SELECT COUNT(*) FROM garages")->fetchColumn(),
            'today'             => (int)$pdo->query("SELECT COUNT(*) FROM discussion WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
            'messages_today'    => (int)$pdo->query("SELECT COUNT(*) FROM message WHERE DATE(date_envoi)=CURDATE()")->fetchColumn(),
        ];
    }
}
?>