<?php
/**
 * models/message.php
 * Utilise la connexion PDO globale $pdo de db.php
 * expediteur_type: 'user' = client, 'garage' = garagiste
 * type: 'text' | 'image' | 'audio'
 */
require_once __DIR__ . '/db.php';

class Message
{
    private static function pdo(): PDO {
        global $pdo;
        return $pdo;
    }

    // ── CREATE ────────────────────────────────────────────────
    public static function create(
        int    $discussion_id,
        int    $expediteur_id,
        string $contenu,
        string $expediteur_type = 'user',
        string $type            = 'text'
    ): int {
        $db   = self::pdo();
        $stmt = $db->prepare("
            INSERT INTO message (discussion_id, expediteur_id, expediteur_type, contenu, type, date_envoi)
            VALUES (:did, :eid, :etype, :contenu, :type, NOW())
        ");
        $stmt->execute([
            ':did'     => $discussion_id,
            ':eid'     => $expediteur_id,
            ':etype'   => in_array($expediteur_type, ['user','garage']) ? $expediteur_type : 'user',
            ':contenu' => $contenu,
            ':type'    => in_array($type, ['text','image','audio']) ? $type : 'text',
        ]);
        return (int)$db->lastInsertId();
    }

    // ── READ par discussion ───────────────────────────────────
    public static function getByDiscussion(int $discussion_id): array {
        $stmt = self::pdo()->prepare("
            SELECT m.*,
                CASE
                    WHEN m.expediteur_type='user'   THEN CONCAT(c.prenom,' ',c.nom)
                    WHEN m.expediteur_type='garage' THEN CONCAT(g.prenom,' ',g.nom)
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN client    c ON c.id_client    = m.expediteur_id AND m.expediteur_type='user'
            LEFT JOIN garagiste g ON g.id_garagiste = m.expediteur_id AND m.expediteur_type='garage'
            WHERE m.discussion_id = :did
            ORDER BY m.date_envoi ASC
        ");
        $stmt->execute([':did' => $discussion_id]);
        return $stmt->fetchAll();
    }

    // ── READ ALL ──────────────────────────────────────────────
    public static function getAll(int $limit = 100): array {
        $stmt = self::pdo()->prepare("
            SELECT m.*, d.objet AS sujet_discussion,
                CASE
                    WHEN m.expediteur_type='user'   THEN CONCAT(c.prenom,' ',c.nom)
                    WHEN m.expediteur_type='garage' THEN CONCAT(g.prenom,' ',g.nom)
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN discussion d ON d.id            = m.discussion_id
            LEFT JOIN client    c  ON c.id_client    = m.expediteur_id AND m.expediteur_type='user'
            LEFT JOIN garagiste g  ON g.id_garagiste = m.expediteur_id AND m.expediteur_type='garage'
            ORDER BY m.date_envoi DESC
            LIMIT :lim
        ");
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // ── READ ONE ──────────────────────────────────────────────
    public static function getById(int $id): ?array {
        $stmt = self::pdo()->prepare("
            SELECT m.*, d.objet AS sujet_discussion,
                CASE
                    WHEN m.expediteur_type='user'   THEN CONCAT(c.prenom,' ',c.nom)
                    WHEN m.expediteur_type='garage' THEN CONCAT(g.prenom,' ',g.nom)
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN discussion d ON d.id            = m.discussion_id
            LEFT JOIN client    c  ON c.id_client    = m.expediteur_id AND m.expediteur_type='user'
            LEFT JOIN garagiste g  ON g.id_garagiste = m.expediteur_id AND m.expediteur_type='garage'
            WHERE m.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch() ?: null;
    }

    // ── UPDATE ────────────────────────────────────────────────
    public static function update(int $id, string $contenu): bool {
        return self::pdo()->prepare("UPDATE message SET contenu=:contenu WHERE id=:id")
                          ->execute([':contenu' => trim($contenu), ':id' => $id]);
    }

    // ── DELETE ────────────────────────────────────────────────
    public static function delete(int $id): bool {
        return self::pdo()->prepare("DELETE FROM message WHERE id=:id")->execute([':id' => $id]);
    }

    // ── SEARCH ────────────────────────────────────────────────
    public static function search(string $q): array {
        $like = '%'.$q.'%';
        $stmt = self::pdo()->prepare("
            SELECT m.*, d.objet AS sujet_discussion,
                CASE
                    WHEN m.expediteur_type='user'   THEN CONCAT(c.prenom,' ',c.nom)
                    WHEN m.expediteur_type='garage' THEN CONCAT(g.prenom,' ',g.nom)
                    ELSE 'Inconnu'
                END AS nom_expediteur
            FROM message m
            LEFT JOIN discussion d ON d.id            = m.discussion_id
            LEFT JOIN client    c  ON c.id_client    = m.expediteur_id AND m.expediteur_type='user'
            LEFT JOIN garagiste g  ON g.id_garagiste = m.expediteur_id AND m.expediteur_type='garage'
            WHERE m.contenu LIKE :q1
               OR CONCAT(c.prenom,' ',c.nom) LIKE :q2
               OR CONCAT(g.prenom,' ',g.nom) LIKE :q3
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([':q1'=>$like, ':q2'=>$like, ':q3'=>$like]);
        return $stmt->fetchAll();
    }
}
?>