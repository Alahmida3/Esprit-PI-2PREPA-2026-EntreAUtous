<?php
// models/Face.php — Modèle Reconnaissance Faciale (client_face)
// Algorithme : pHash 8×8 + distance de Hamming

require_once __DIR__ . '/db.php';

class Face
{
    private const THRESHOLD = 25; // distance max pour valider une correspondance

    /** Enregistre ou met à jour le visage d'un client. */
    public static function store(int $userId, string $imageB64): array
    {
        $imageRaw = self::decodeB64($imageB64);
        if (!$imageRaw || strlen($imageRaw) < 500) {
            return ['success' => false, 'message' => 'Image invalide ou trop petite.'];
        }

        global $pdo;
        $signature = self::pHash($imageRaw);
        $miniature = base64_encode(self::resize($imageRaw, 64, 64));

        $pdo->prepare(
            'INSERT INTO client_face (id_client, signature, miniature, created_at)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE signature = VALUES(signature),
                                     miniature = VALUES(miniature),
                                     updated_at = NOW()'
        )->execute([$userId, $signature, $miniature]);

        return ['success' => true, 'message' => 'Visage enregistré avec succès !'];
    }

    /** Tente d'identifier un visage parmi tous les enregistrements. */
    public static function identify(string $imageB64): array|false
    {
        $imageRaw = self::decodeB64($imageB64);
        if (!$imageRaw) return false;

        global $pdo;
        $all = $pdo->query(
            'SELECT cf.id_client, cf.signature, c.prenom, c.nom, c.email
             FROM client_face cf JOIN client c ON c.id_client = cf.id_client'
        )->fetchAll();

        if (empty($all)) return false;

        $input     = self::pHash($imageRaw);
        $bestScore = PHP_INT_MAX;
        $bestMatch = null;

        foreach ($all as $row) {
            $dist = self::hamming($input, $row['signature']);
            if ($dist < $bestScore) {
                $bestScore = $dist;
                $bestMatch = $row;
            }
        }

        if ($bestMatch && $bestScore <= self::THRESHOLD) {
            $bestMatch['score'] = $bestScore;
            return $bestMatch;
        }
        return false;
    }

    // ── Helpers privés ───────────────────────────────────────────

    private static function decodeB64(string $b64): string|false
    {
        $clean = preg_replace('/^data:image\/\w+;base64,/', '', $b64);
        $clean = str_replace(' ', '+', $clean);
        return base64_decode($clean);
    }

    /** pHash 8×8 : signature perceptuelle (64 bits binaire). */
    private static function pHash(string $raw): string
    {
        $img = @imagecreatefromstring($raw);
        if (!$img) return str_repeat('0', 64);

        $small = imagecreatetruecolor(8, 8);
        imagecopyresampled($small, $img, 0, 0, 0, 0, 8, 8, imagesx($img), imagesy($img));
        imagedestroy($img);

        $pixels = [];
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $rgb = imagecolorat($small, $x, $y);
                $pixels[] = (int)(
                    0.299 * (($rgb >> 16) & 0xFF) +
                    0.587 * (($rgb >> 8)  & 0xFF) +
                    0.114 * ( $rgb        & 0xFF)
                );
            }
        }
        imagedestroy($small);

        $avg  = array_sum($pixels) / 64;
        $hash = '';
        foreach ($pixels as $p) $hash .= ($p >= $avg) ? '1' : '0';
        return $hash;
    }

    /** Distance de Hamming entre deux hashes binaires. */
    private static function hamming(string $a, string $b): int
    {
        if (strlen($a) !== strlen($b)) return PHP_INT_MAX;
        $d = 0;
        for ($i = 0, $l = strlen($a); $i < $l; $i++) {
            if ($a[$i] !== $b[$i]) $d++;
        }
        return $d;
    }

    /** Redimensionne une image brute en JPEG. */
    private static function resize(string $raw, int $w, int $h): string
    {
        $src = @imagecreatefromstring($raw);
        if (!$src) return $raw;
        $dst = imagecreatetruecolor($w, $h);
        imagecopyresampled($dst, $src, 0, 0, 0, 0, $w, $h, imagesx($src), imagesy($src));
        imagedestroy($src);
        ob_start();
        imagejpeg($dst, null, 70);
        imagedestroy($dst);
        return ob_get_clean();
    }
}
