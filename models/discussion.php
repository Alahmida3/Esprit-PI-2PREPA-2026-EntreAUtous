<?php
/**
 * models/discussion.php
 * Utilise la connexion PDO globale $pdo de db.php
 * Tables réelles : client (id_client, prenom, nom) | garagiste (id_garagiste, prenom, nom)
 */
require_once __DIR__ . '/db.php';

class Discussion
{
    private static function pdo(): PDO {
        global $pdo;
        return $pdo;
    }

    // ── CREATE ────────────────────────────────────────────────
    public static function create(int $user_id, int $garage_id, string $objet): int {
        $db   = self::pdo();
        $stmt = $db->prepare("
            INSERT INTO discussion (user_id, garage_id, objet, created_at)
            VALUES (:uid, :gid, :objet, NOW())
        ");
        $stmt->execute([':uid' => $user_id, ':gid' => $garage_id, ':objet' => trim($objet)]);
        return (int)$db->lastInsertId();
    }

    // ── READ ALL ──────────────────────────────────────────────
    public static function getAll(): array {
        $stmt = self::pdo()->query("
            SELECT
                d.id, d.objet, d.created_at, d.user_id, d.garage_id,
                CONCAT(c.prenom,' ',c.nom)  AS nom_user,
                CONCAT(g.prenom,' ',g.nom)  AS nom_garage,
                (SELECT COUNT(*) FROM message m WHERE m.discussion_id = d.id)            AS nb_messages,
                (SELECT MAX(m2.date_envoi)  FROM message m2 WHERE m2.discussion_id=d.id) AS dernier_message,
                (SELECT m3.contenu FROM message m3 WHERE m3.discussion_id=d.id
                 ORDER BY m3.date_envoi DESC LIMIT 1)                                    AS dernier_contenu
            FROM discussion d
            LEFT JOIN client    c ON c.id_client    = d.user_id
            LEFT JOIN garagiste g ON g.id_garagiste = d.garage_id
            ORDER BY COALESCE(dernier_message, d.created_at) DESC
        ");
        return $stmt->fetchAll();
    }

    // ── READ ONE ──────────────────────────────────────────────
    public static function getById(int $id): ?array {
        $stmt = self::pdo()->prepare("
            SELECT d.*,
                CONCAT(c.prenom,' ',c.nom) AS nom_user,
                CONCAT(g.prenom,' ',g.nom) AS nom_garage
            FROM discussion d
            LEFT JOIN client    c ON c.id_client    = d.user_id
            LEFT JOIN garagiste g ON g.id_garagiste = d.garage_id
            WHERE d.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ── READ par client ───────────────────────────────────────
    public static function getByUser(int $user_id): array {
        $stmt = self::pdo()->prepare("
            SELECT d.id, d.objet, d.created_at, d.garage_id,
                CONCAT(g.prenom,' ',g.nom)  AS nom_garage,
                (SELECT COUNT(*) FROM message m WHERE m.discussion_id=d.id)             AS nb_messages,
                (SELECT MAX(m2.date_envoi) FROM message m2 WHERE m2.discussion_id=d.id) AS dernier_message,
                (SELECT m3.contenu FROM message m3 WHERE m3.discussion_id=d.id
                 ORDER BY m3.date_envoi DESC LIMIT 1)                                   AS dernier_contenu
            FROM discussion d
            LEFT JOIN garagiste g ON g.id_garagiste = d.garage_id
            WHERE d.user_id = :uid
            ORDER BY COALESCE(dernier_message, d.created_at) DESC
        ");
        $stmt->execute([':uid' => $user_id]);
        return $stmt->fetchAll();
    }

    // ── READ par garagiste ────────────────────────────────────
    public static function getByGarage(int $garage_id): array {
        $stmt = self::pdo()->prepare("
            SELECT d.id, d.objet, d.created_at, d.user_id,
                CONCAT(c.prenom,' ',c.nom)  AS nom_user,
                (SELECT COUNT(*) FROM message m WHERE m.discussion_id=d.id)             AS nb_messages,
                (SELECT MAX(m2.date_envoi) FROM message m2 WHERE m2.discussion_id=d.id) AS dernier_message,
                (SELECT m3.contenu FROM message m3 WHERE m3.discussion_id=d.id
                 ORDER BY m3.date_envoi DESC LIMIT 1)                                   AS dernier_contenu
            FROM discussion d
            LEFT JOIN client c ON c.id_client = d.user_id
            WHERE d.garage_id = :gid
            ORDER BY COALESCE(dernier_message, d.created_at) DESC
        ");
        $stmt->execute([':gid' => $garage_id]);
        return $stmt->fetchAll();
    }

    // ── UPDATE ────────────────────────────────────────────────
    public static function update(int $id, int $user_id, int $garage_id, string $objet): bool {
        return self::pdo()->prepare(
            "UPDATE discussion SET user_id=:uid, garage_id=:gid, objet=:objet WHERE id=:id"
        )->execute([':uid'=>$user_id, ':gid'=>$garage_id, ':objet'=>trim($objet), ':id'=>$id]);
    }

    // ── DELETE ────────────────────────────────────────────────
    public static function delete(int $id): bool {
        $db = self::pdo();
        $db->prepare("DELETE FROM message     WHERE discussion_id=:id")->execute([':id'=>$id]);
        return $db->prepare("DELETE FROM discussion WHERE id=:id")->execute([':id'=>$id]);
    }

    // ── SEARCH ────────────────────────────────────────────────
    public static function search(string $q): array {
        $like = '%'.$q.'%';
        $stmt = self::pdo()->prepare("
            SELECT d.*,
                CONCAT(c.prenom,' ',c.nom) AS nom_user,
                CONCAT(g.prenom,' ',g.nom) AS nom_garage,
                (SELECT COUNT(*) FROM message m WHERE m.discussion_id=d.id) AS nb_messages
            FROM discussion d
            LEFT JOIN client    c ON c.id_client    = d.user_id
            LEFT JOIN garagiste g ON g.id_garagiste = d.garage_id
            WHERE d.objet LIKE :q1
               OR CONCAT(c.prenom,' ',c.nom) LIKE :q2
               OR CONCAT(g.prenom,' ',g.nom) LIKE :q3
            ORDER BY d.created_at DESC
        ");
        $stmt->execute([':q1'=>$like, ':q2'=>$like, ':q3'=>$like]);
        return $stmt->fetchAll();
    }

    // ── STATS ─────────────────────────────────────────────────
    public static function getStats(): array {
        $db = self::pdo();
        return [
            'total_discussions' => (int)$db->query("SELECT COUNT(*) FROM discussion")->fetchColumn(),
            'total_messages'    => (int)$db->query("SELECT COUNT(*) FROM message")->fetchColumn(),
            'total_users'       => (int)$db->query("SELECT COUNT(*) FROM client")->fetchColumn(),
            'total_garages'     => (int)$db->query("SELECT COUNT(*) FROM garagiste WHERE actif=1")->fetchColumn(),
            'today'             => (int)$db->query("SELECT COUNT(*) FROM discussion WHERE DATE(created_at)=CURDATE()")->fetchColumn(),
            'messages_today'    => (int)$db->query("SELECT COUNT(*) FROM message WHERE DATE(date_envoi)=CURDATE()")->fetchColumn(),
        ];
    }
}
?>