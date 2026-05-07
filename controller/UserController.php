<?php
// controller/UserController.php
require_once __DIR__ . '/../models/db.php';

session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path'     => '/autout/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// ── LOGOUT ────────────────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => '/autout/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    header("Location: /autout/views/front/login.php");
    exit();
}

// ── SUPPRIMER COMPTE ─────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete_account') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /autout/views/front/login.php");
        exit();
    }
    $id = (int)$_SESSION['user_id'];
    $pdo->prepare("DELETE FROM client WHERE id_client = ?")->execute([$id]);
    session_unset();
    session_destroy();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => '/autout/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    header("Location: /autout/views/front/register.php?success=deleted");
    exit();
}

// ── DISPATCH POST ─────────────────────────────────────────────
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'register':        register($pdo);        break;
        case 'login':           login($pdo);           break;
        case 'update_profile':  update_profile($pdo);  break;
        case 'change_password': change_password($pdo); break;
    }
}

// ══════════════════════════════════════════════════════════════
//  REGISTER  — corrigé : face_image sauvegardé APRÈS l'INSERT
// ══════════════════════════════════════════════════════════════
function register($pdo) {
    $nom    = trim($_POST['nom']        ?? '');
    $prenom = trim($_POST['prenom']     ?? '');
    $email  = trim($_POST['email']      ?? '');
    $tel    = trim($_POST['telephone']  ?? '');
    $adr    = trim($_POST['adresse']    ?? '');
    $pass   = $_POST['mot_de_passe']    ?? '';

    // ── Validations ───────────────────────────────────────────
    if (empty($nom) || empty($prenom) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        header("Location: /autout/views/front/register.php?error=invalid_data");
        exit();
    }
    if (!preg_match('/^[0-9]{8}$/', $tel)) {
        header("Location: /autout/views/front/register.php?error=invalid_phone");
        exit();
    }
    $check = $pdo->prepare("SELECT id_client FROM client WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        header("Location: /autout/views/front/register.php?error=email_exists");
        exit();
    }

    // ── Insertion client ──────────────────────────────────────
    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $pdo->prepare(
        "INSERT INTO client (nom, prenom, email, telephone, mot_de_passe, adresse, date_inscription)
         VALUES (?,?,?,?,?,?,NOW())"
    )->execute([$nom, $prenom, $email, $tel, $hash, $adr]);

    // ✅ On récupère l'ID APRÈS l'INSERT
    $newId = (int)$pdo->lastInsertId();

    // ── Face ID (optionnel) : sauvegarde MAINTENANT qu'on a l'ID ─
    $faceB64 = $_POST['face_image'] ?? '';
    if (!empty($faceB64) && strlen($faceB64) > 1000) {
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $faceB64);
        $imageData = str_replace(' ', '+', $imageData);
        $imageRaw  = base64_decode($imageData);

        if ($imageRaw && strlen($imageRaw) > 500) {
            $signature = computeImageSignature($imageRaw);
            $miniature = base64_encode(resizeImage($imageRaw, 64, 64));

            // Vérifie si la table client_face existe avant d'insérer
            try {
                $pdo->prepare(
                    "INSERT INTO client_face (id_client, signature, miniature, created_at)
                     VALUES (?, ?, ?, NOW())
                     ON DUPLICATE KEY UPDATE
                         signature  = VALUES(signature),
                         miniature  = VALUES(miniature),
                         updated_at = NOW()"
                )->execute([$newId, $signature, $miniature]);
            } catch (PDOException $e) {
                // Table pas encore créée : ignorer silencieusement
                error_log("client_face insert failed: " . $e->getMessage());
            }
        }
    }

    // ── Initialiser le parcours utilisateur ───────────────────
    try {
        $pdo->prepare("INSERT IGNORE INTO user_journey (id_client) VALUES (?)")->execute([$newId]);
    } catch (PDOException $e) {
        error_log("user_journey insert failed: " . $e->getMessage());
    }

    // Rediriger avec indicateur face=1 si visage enregistré
    $faceParam = (!empty($faceB64) && strlen($faceB64) > 1000) ? '&face=1' : '';
    header("Location: /autout/views/front/login.php?success=registered" . $faceParam);
    exit();
}

// ══════════════════════════════════════════════════════════════
//  LOGIN
// ══════════════════════════════════════════════════════════════
function login($pdo) {
    $email = trim($_POST['email']        ?? '');
    $pass  = trim($_POST['mot_de_passe'] ?? '');

    // Admin hardcodé
    if ($email === 'admin@garage.com' && $pass === 'admin123') {
        $_SESSION['role']   = 'admin';
        $_SESSION['prenom'] = 'Admin';
        $_SESSION['email']  = $email;
        session_regenerate_id(true);
        header("Location: /autout/views/back/admin.php");
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($pass, $user['mot_de_passe'])) {
        $_SESSION['user_id']   = $user['id_client'];
        $_SESSION['role']      = 'client';
        $_SESSION['prenom']    = $user['prenom']    ?? '';
        $_SESSION['nom']       = $user['nom']       ?? '';
        $_SESSION['email']     = $user['email']     ?? '';
        $_SESSION['telephone'] = $user['telephone'] ?? '';
        $_SESSION['adresse']   = $user['adresse']   ?? '';
        $_SESSION['user']      = $user['prenom'];
        session_regenerate_id(true);
        header("Location: /autout/views/front/home.php");
    } else {
        header("Location: /autout/views/front/login.php?error=failed");
    }
    exit();
}

// ══════════════════════════════════════════════════════════════
//  UPDATE PROFILE
// ══════════════════════════════════════════════════════════════
function update_profile($pdo) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /autout/views/front/login.php"); exit();
    }
    $id     = (int)$_SESSION['user_id'];
    $prenom = trim($_POST['prenom']    ?? '');
    $nom    = trim($_POST['nom']       ?? '');
    $tel    = trim($_POST['telephone'] ?? '');
    $adr    = trim($_POST['adresse']   ?? '');

    if (!empty($tel) && !preg_match('/^[0-9]{8}$/', $tel)) {
        header("Location: /autout/views/front/profile.php?error=invalid_phone"); exit();
    }

    $ok = $pdo->prepare("UPDATE client SET prenom=?, nom=?, telephone=?, adresse=? WHERE id_client=?")
               ->execute([$prenom, $nom, $tel, $adr, $id]);

    if ($ok) {
        $_SESSION['prenom']    = $prenom;
        $_SESSION['nom']       = $nom;
        $_SESSION['telephone'] = $tel;
        $_SESSION['adresse']   = $adr;
        $_SESSION['user']      = $prenom;
        header("Location: /autout/views/front/profile.php?success=updated");
    } else {
        header("Location: /autout/views/front/profile.php?error=update_failed");
    }
    exit();
}

// ══════════════════════════════════════════════════════════════
//  CHANGER MOT DE PASSE
// ══════════════════════════════════════════════════════════════
function change_password($pdo) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /autout/views/front/login.php"); exit();
    }
    $id      = (int)$_SESSION['user_id'];
    $old     = $_POST['old_password']     ?? '';
    $new     = $_POST['new_password']     ?? '';

    $stmt = $pdo->prepare("SELECT mot_de_passe FROM client WHERE id_client = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($old, $row['mot_de_passe'])) {
        header("Location: /autout/views/front/profile.php?error=wrong_password"); exit();
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $ok   = $pdo->prepare("UPDATE client SET mot_de_passe = ? WHERE id_client = ?")->execute([$hash, $id]);
    header("Location: /autout/views/front/profile.php?" . ($ok ? 'success=password' : 'error=password_failed'));
    exit();
}

// ══════════════════════════════════════════════════════════════
//  HELPERS IMAGE — pHash 8×8 pour comparaison faciale
// ══════════════════════════════════════════════════════════════
function computeImageSignature(string $imageRaw): string {
    $img = @imagecreatefromstring($imageRaw);
    if (!$img) return str_repeat('0', 64);

    $small = imagecreatetruecolor(8, 8);
    imagecopyresampled($small, $img, 0, 0, 0, 0, 8, 8, imagesx($img), imagesy($img));
    imagedestroy($img);

    $pixels = [];
    for ($y = 0; $y < 8; $y++) {
        for ($x = 0; $x < 8; $x++) {
            $rgb      = imagecolorat($small, $x, $y);
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

function resizeImage(string $raw, int $w, int $h): string {
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