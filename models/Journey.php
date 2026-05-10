<?php
// models/Journey.php — Modèle Parcours Guidé (user_journey)

require_once __DIR__ . '/db.php';

class Journey
{
    public const STEPS = [
        'step_profile',
        'step_diagnostic',
        'step_garage',
        'step_vehicle',
        'step_rdv',
        'step_message',
    ];

    /** Récupère (ou crée) le parcours d'un client. */
    public static function getOrCreate(int $userId): array
    {
        global $pdo;
        $pdo->prepare('INSERT IGNORE INTO user_journey (id_client) VALUES (?)')->execute([$userId]);
        $stmt = $pdo->prepare('SELECT * FROM user_journey WHERE id_client = ?');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    /** Marque une étape comme complétée (idempotent). */
    public static function markStep(int $userId, string $step): array|false
    {
        if (!in_array($step, self::STEPS, true)) return false;
        global $pdo;

        $scoreExpr = implode('+', self::STEPS);
        $pdo->prepare(
            "UPDATE user_journey SET `$step` = 1, score = ($scoreExpr)
             WHERE id_client = ? AND `$step` = 0"
        )->execute([$userId]);

        return self::getOrCreate($userId);
    }

    /** Auto-valide step_profile si téléphone + adresse sont renseignés. */
    public static function autoValidateProfile(int $userId, array $journey): array
    {
        global $pdo;
        if ($journey['step_profile']) return $journey;

        $stmt = $pdo->prepare('SELECT telephone, adresse FROM client WHERE id_client = ?');
        $stmt->execute([$userId]);
        $u = $stmt->fetch();

        if (!empty($u['telephone']) && !empty($u['adresse'])) {
            $pdo->prepare(
                'UPDATE user_journey SET step_profile = 1, score = score + 1
                 WHERE id_client = ? AND step_profile = 0'
            )->execute([$userId]);
            $journey['step_profile'] = 1;
            $journey['score']++;
        }
        return $journey;
    }

    /** Réinitialise toutes les étapes d'un client. */
    public static function reset(int $userId): void
    {
        global $pdo;
        $cols = implode('=0,', self::STEPS) . '=0';
        $pdo->prepare("UPDATE user_journey SET $cols, score = 0 WHERE id_client = ?")
            ->execute([$userId]);
    }
}