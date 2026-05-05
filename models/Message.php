<?php
/**
 * Message.php — Modèle Message
 * expediteur_id peut être un user ou un garage (différencié par expediteur_type)
 * Hérite de Model (connexion PDO intégrée)
 */
require_once __DIR__ . '/Model.php';

class Message extends Model {

    // ── CREATE ────────────────────────────────────────────────
    /**
     * @param string $expediteur_type  'user' ou 'garage'
     */
    public static function create(int $discussion_id, int $expediteur_id, string $contenu, string $expediteur_type = 'user'): int {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            INSERT INTO message (discussion_id, expediteur_id, expediteur_type, contenu, date_envoi)
            VALUES (:did, :eid, :etype, :contenu, NOW())
        ");
        $stmt->execute([
            ':did'    => $discussion_id,
            ':eid'    => $expediteur_id,
            ':etype'  => $expediteur_type,
            ':contenu'=> trim($contenu),
        ]);
        return (int)$pdo->lastInsertId();
    }

    // ── READ par discussion ───────────────────────────────────
    public static function getByDiscussion(int $discussion_id): array {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            SELECT
                m.*,
                CASE
                    WHEN m.expediteur_type = 'user'   THEN u.nom
                    WHEN m.expediteur_type = 'garage'  THEN g.nom_garages
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN user    u ON u.id = m.expediteur_id AND m.expediteur_type = 'user'
            LEFT JOIN garages g ON g.id = m.expediteur_id AND m.expediteur_type = 'garage'
            WHERE m.discussion_id = :did
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute([':did' => $discussion_id]);
        return $stmt->fetchAll();
    }

    // ── READ ALL (admin) ──────────────────────────────────────
    public static function getAll(int $limit = 100): array {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            SELECT
                m.*,
                d.objet AS sujet_discussion,
                CASE
                    WHEN m.expediteur_type = 'user'   THEN u.nom
                    WHEN m.expediteur_type = 'garage'  THEN g.nom_garages
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN discussion d ON d.id = m.discussion_id
            LEFT JOIN user    u ON u.id = m.expediteur_id AND m.expediteur_type = 'user'
            LEFT JOIN garages g ON g.id = m.expediteur_id AND m.expediteur_type = 'garage'
            ORDER BY m.date_envoi DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── READ ONE ──────────────────────────────────────────────
    public static function getById(int $id): ?array {
        $pdo  = self::db();
        $stmt = $pdo->prepare("
            SELECT m.*, d.objet AS sujet_discussion,
                CASE
                    WHEN m.expediteur_type='user'   THEN u.nom
                    WHEN m.expediteur_type='garage'  THEN g.nom_garages
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN discussion d ON d.id=m.discussion_id
            LEFT JOIN user    u ON u.id=m.expediteur_id AND m.expediteur_type='user'
            LEFT JOIN garages g ON g.id=m.expediteur_id AND m.expediteur_type='garage'
            WHERE m.id=:id
        ");
        $stmt->execute([':id'=>$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── UPDATE ────────────────────────────────────────────────
    public static function update(int $id, string $contenu): bool {
        $pdo  = self::db();
        $stmt = $pdo->prepare("UPDATE message SET contenu=:contenu WHERE id=:id");
        return $stmt->execute([':contenu'=>trim($contenu),':id'=>$id]);
    }

    // ── DELETE ────────────────────────────────────────────────
    public static function delete(int $id): bool {
        $pdo  = self::db();
        return $pdo->prepare("DELETE FROM message WHERE id=:id")->execute([':id'=>$id]);
    }

    // ── SEARCH ────────────────────────────────────────────────
    public static function search(string $q): array {
        $pdo  = self::db();
        $like = '%'.$q.'%';
        $stmt = $pdo->prepare("
            SELECT m.*, d.objet AS sujet_discussion,
                CASE
                    WHEN m.expediteur_type='user'   THEN u.nom
                    WHEN m.expediteur_type='garage'  THEN g.nom_garages
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN discussion d ON d.id=m.discussion_id
            LEFT JOIN user    u ON u.id=m.expediteur_id AND m.expediteur_type='user'
            LEFT JOIN garages g ON g.id=m.expediteur_id AND m.expediteur_type='garage'
            WHERE m.contenu LIKE :q1 OR u.nom LIKE :q2 OR g.nom_garages LIKE :q3
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([':q1'=>$like,':q2'=>$like,':q3'=>$like]);
        return $stmt->fetchAll();
    }
}
?>