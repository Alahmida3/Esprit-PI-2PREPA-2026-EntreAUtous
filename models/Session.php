<?php
// models/Session.php — Helper session centralisé

class Session
{
    private const COOKIE_PARAMS = [
        'lifetime' => 60 * 60 * 24 * 7,
        'path'     => '/autout/',
        'secure'   => false,
        'httponly' => true,
        'samesite' => 'Strict',
    ];

    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params(self::COOKIE_PARAMS);
            session_start();
        }
    }

    public static function loginAs(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id']   = $user['id_client'];
        $_SESSION['role']      = 'client';
        $_SESSION['user']      = $user['prenom'] ?? '';
        $_SESSION['prenom']    = $user['prenom'] ?? '';
        $_SESSION['nom']       = $user['nom']    ?? '';
        $_SESSION['email']     = $user['email']  ?? '';
        $_SESSION['telephone'] = $user['telephone'] ?? '';
        $_SESSION['adresse']   = $user['adresse']   ?? '';
    }

    public static function loginAsAdmin(string $email): void
    {
        session_regenerate_id(true);
        $_SESSION['role']   = 'admin';
        $_SESSION['prenom'] = 'Admin';
        $_SESSION['email']  = $email;
    }

    public static function destroy(): void
    {
        session_unset();
        session_destroy();
        setcookie(session_name(), '', [
            'expires'  => time() - 3600,
            'path'     => '/autout/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
    }

    public static function isClient(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public static function isAdmin(): bool
    {
        return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
    }

    public static function requireClient(string $redirect = '/autout/views/front/login.php'): void
    {
        if (!self::isClient()) {
            header("Location: $redirect");
            exit;
        }
    }

    public static function requireAdmin(string $redirect = '/autout/views/front/login.php'): void
    {
        if (!self::isAdmin()) {
            header("Location: $redirect");
            exit;
        }
    }
}
