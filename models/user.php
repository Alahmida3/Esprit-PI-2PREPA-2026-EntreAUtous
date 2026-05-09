<?php
// models/User.php — Modèle Client (CRUD + authentification)
// Toutes les requêtes SQL liées à la table `client` passent ici.

require_once __DIR__ . '/db.php';

class User
{
    // ── Lecture ──────────────────────────────────────────────────

    /** Retourne un client par son ID, ou false. */
    public static function getById(int $id): array|false
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM client WHERE id_client = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Liste tous les clients (pour l'admin). */
    public static function getAll(): array
    {
        global $pdo;
        return $pdo->query(
            'SELECT id_client, nom, prenom, email, telephone, adresse, date_inscription
             FROM client ORDER BY date_inscription DESC'
        )->fetchAll();
    }

    /** Nombre total de clients (avec recherche optionnelle). */
    public static function count(string $search = ''): int
    {
        global $pdo;
        if ($search === '') {
            return (int) $pdo->query('SELECT COUNT(*) FROM client')->fetchColumn();
        }
        $like = "%$search%";
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM client
             WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?'
        );
        $stmt->execute([$like, $like, $like, $like]);
        return (int) $stmt->fetchColumn();
    }

    /** Liste paginée + filtrée + triée (pour l'admin). */
    public static function paginate(
        int    $offset,
        int    $limit,
        string $search = '',
        string $sort   = 'date_inscription',
        string $order  = 'DESC'
    ): array {
        global $pdo;

        // Whitelist tri
        $allowed = ['nom', 'prenom', 'email', 'telephone', 'date_inscription'];
        if (!in_array($sort, $allowed, true)) $sort = 'date_inscription';
        $order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        if ($search === '') {
            $stmt = $pdo->prepare(
                "SELECT id_client, nom, prenom, email, telephone, adresse, date_inscription
                 FROM client ORDER BY $sort $order LIMIT $limit OFFSET $offset"
            );
            $stmt->execute();
        } else {
            $like = "%$search%";
            $stmt = $pdo->prepare(
                "SELECT id_client, nom, prenom, email, telephone, adresse, date_inscription
                 FROM client
                 WHERE nom LIKE ? OR prenom LIKE ? OR email LIKE ? OR telephone LIKE ?
                 ORDER BY $sort $order LIMIT $limit OFFSET $offset"
            );
            $stmt->execute([$like, $like, $like, $like]);
        }
        return $stmt->fetchAll();
    }

    // ── Authentification ─────────────────────────────────────────

    /** Vérifie email + mot de passe. Retourne le tableau client ou false. */
    public static function login(string $email, string $password): array|false
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT * FROM client WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        return ($user && password_verify($password, $user['mot_de_passe'])) ? $user : false;
    }

    // ── Écriture ─────────────────────────────────────────────────

    /** Inscription : retourne le nouvel ID ou false. */
    public static function register(array $data): int|false
    {
        global $pdo;
        $stmt = $pdo->prepare(
            'INSERT INTO client (nom, prenom, email, telephone, mot_de_passe, adresse, date_inscription)
             VALUES (?, ?, ?, ?, ?, ?, NOW())'
        );
        $ok = $stmt->execute([
            $data['nom'],
            $data['prenom'],
            $data['email'],
            $data['telephone'],
            password_hash($data['mot_de_passe'], PASSWORD_BCRYPT),
            $data['adresse'],
        ]);
        return $ok ? (int) $pdo->lastInsertId() : false;
    }

    /** Mise à jour du profil (nom, prénom, téléphone, adresse). */
    public static function updateProfile(int $id, array $data): bool
    {
        global $pdo;
        return $pdo->prepare(
            'UPDATE client SET prenom = ?, nom = ?, telephone = ?, adresse = ? WHERE id_client = ?'
        )->execute([$data['prenom'], $data['nom'], $data['telephone'], $data['adresse'], $id]);
    }

    /** Changement de mot de passe (vérifie l'ancien d'abord). */
    public static function changePassword(int $id, string $old, string $new): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT mot_de_passe FROM client WHERE id_client = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($old, $row['mot_de_passe'])) {
            return false;
        }
        return $pdo->prepare('UPDATE client SET mot_de_passe = ? WHERE id_client = ?')
                   ->execute([password_hash($new, PASSWORD_BCRYPT), $id]);
    }

    /** Suppression d'un compte. */
    public static function delete(int $id): bool
    {
        global $pdo;
        return $pdo->prepare('DELETE FROM client WHERE id_client = ?')->execute([$id]);
    }

    /** Vérifie si un email est déjà utilisé. */
    public static function emailExists(string $email): bool
    {
        global $pdo;
        $stmt = $pdo->prepare('SELECT id_client FROM client WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetchColumn() !== false;
    }
}
