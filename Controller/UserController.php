<?php
// controller/UserController.php
require_once __DIR__ . '/../models/db.php';

session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path' => '/Esprit-PI-2PREPA-2026-EntreAUtous/',
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
        'path' => '/Esprit-PI-2PREPA-2026-EntreAUtous/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php");
    exit();
}

// ── SUPPRIMER COMPTE ─────────────────────────────────────────
if (isset($_GET['action']) && $_GET['action'] === 'delete_account') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php");
        exit();
    }
    $id = (int)$_SESSION['user_id'];
    $pdo->prepare("DELETE FROM client WHERE id_client = ?")->execute([$id]);
    session_unset();
    session_destroy();
    setcookie(session_name(), '', [
        'expires'  => time() - 3600,
        'path'     => '/Esprit-PI-2PREPA-2026-EntreAUtous/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/register.php?success=deleted");
    exit();
}

// ── DISPATCH POST ─────────────────────────────────────────────
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'register':           register($pdo);           break;
        case 'login':              login($pdo);              break;
        case 'update_profile':     update_profile($pdo);     break;
        case 'change_password':    change_password($pdo);    break;
        // ── Actions admin : gestion des garagistes ──────────
        case 'add_garagiste':      add_garagiste($pdo);      break;
        case 'toggle_garagiste':   toggle_garagiste($pdo);   break;
        case 'delete_garagiste':   delete_garagiste($pdo);   break;
    }
}

// ══════════════════════════════════════════════════════════════
//  REGISTER  — inchangé
// ══════════════════════════════════════════════════════════════
function register($pdo) {
    $nom    = trim($_POST['nom']        ?? '');
    $prenom = trim($_POST['prenom']     ?? '');
    $email  = trim($_POST['email']      ?? '');
    $tel    = trim($_POST['telephone']  ?? '');
    $adr    = trim($_POST['adresse']    ?? '');
    $pass   = $_POST['mot_de_passe']    ?? '';

    if (empty($nom) || empty($prenom) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/register.php?error=invalid_data");
        exit();
    }
    if (!preg_match('/^[0-9]{8}$/', $tel)) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/register.php?error=invalid_phone");
        exit();
    }
    $check = $pdo->prepare("SELECT id_client FROM client WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/register.php?error=email_exists");
        exit();
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $pdo->prepare(
        "INSERT INTO client (nom, prenom, email, telephone, mot_de_passe, adresse, date_inscription)
         VALUES (?,?,?,?,?,?,NOW())"
    )->execute([$nom, $prenom, $email, $tel, $hash, $adr]);

    $newId = (int)$pdo->lastInsertId();

    $faceB64 = $_POST['face_image'] ?? '';
    if (!empty($faceB64) && strlen($faceB64) > 1000) {
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $faceB64);
        $imageData = str_replace(' ', '+', $imageData);
        $imageRaw  = base64_decode($imageData);

        if ($imageRaw && strlen($imageRaw) > 500) {
            $signature = computeImageSignature($imageRaw);
            $miniature = base64_encode(resizeImage($imageRaw, 64, 64));

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
                error_log("client_face insert failed: " . $e->getMessage());
            }
        }
    }

    try {
        $pdo->prepare("INSERT IGNORE INTO user_journey (id_client) VALUES (?)")->execute([$newId]);
    } catch (PDOException $e) {
        error_log("user_journey insert failed: " . $e->getMessage());
    }

    $faceParam = (!empty($faceB64) && strlen($faceB64) > 1000) ? '&face=1' : '';
    header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php?success=registered" . $faceParam);
    exit();
}

// ══════════════════════════════════════════════════════════════
//  LOGIN  — supporte admin (hardcodé) + garagiste (BDD) + client
// ══════════════════════════════════════════════════════════════
function login($pdo) {
    $email = trim($_POST['email']        ?? '');
    $pass  = trim($_POST['mot_de_passe'] ?? '');

    // ── 1. Admin hardcodé ─────────────────────────────────────
    if ($email === 'admin@garage.com' && $pass === 'admin123') {
        $_SESSION['role']   = 'admin';
        $_SESSION['prenom'] = 'Admin';
        $_SESSION['email']  = $email;
        session_regenerate_id(true);
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php");
        exit();
    }

    // ── 2. Garagiste (table garagiste) ────────────────────────
    $stmtG = $pdo->prepare("SELECT * FROM garagiste WHERE email = ? AND actif = 1");
    $stmtG->execute([$email]);
    $garagiste = $stmtG->fetch(PDO::FETCH_ASSOC);

    if ($garagiste && password_verify($pass, $garagiste['mot_de_passe'])) {
        $_SESSION['role']          = 'garagiste';
        $_SESSION['garagiste_id']  = $garagiste['id_garagiste'];
        $_SESSION['prenom']        = $garagiste['prenom'];
        $_SESSION['nom']           = $garagiste['nom'];
        $_SESSION['email']         = $garagiste['email'];
        session_regenerate_id(true);
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/dashboard_garagiste.php");
        exit();
    }

    // Garagiste existant mais désactivé
    $stmtGInactif = $pdo->prepare("SELECT id_garagiste FROM garagiste WHERE email = ? AND actif = 0");
    $stmtGInactif->execute([$email]);
    if ($stmtGInactif->rowCount() > 0) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php?error=account_disabled");
        exit();
    }

    // ── 3. Client normal ──────────────────────────────────────
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
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/home.php");
    } else {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php?error=failed");
    }
    exit();
}

// ══════════════════════════════════════════════════════════════
//  ADD GARAGISTE  — réservé admin
// ══════════════════════════════════════════════════════════════
function add_garagiste($pdo) {
    // Seul l'admin peut créer un garagiste
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php");
        exit();
    }

    $email  = trim($_POST['g_email']  ?? '');
    $pass   = trim($_POST['g_pass']   ?? '');
    $nom    = trim($_POST['g_nom']    ?? '');
    $prenom = trim($_POST['g_prenom'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php?tab=garagistes&error=invalid_data");
        exit();
    }

    // Vérifier unicité email
    $check = $pdo->prepare("SELECT id_garagiste FROM garagiste WHERE email = ?");
    $check->execute([$email]);
    if ($check->rowCount() > 0) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php?tab=garagistes&error=email_exists");
        exit();
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $pdo->prepare(
        "INSERT INTO garagiste (email, mot_de_passe, nom, prenom, actif, cree_par_admin)
         VALUES (?, ?, ?, ?, 1, ?)"
    )->execute([$email, $hash, $nom, $prenom, $_SESSION['email']]);

    header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php?tab=garagistes&success=garagiste_added");
    exit();
}

// ══════════════════════════════════════════════════════════════
//  TOGGLE GARAGISTE  — activer / désactiver
// ══════════════════════════════════════════════════════════════
function toggle_garagiste($pdo) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php");
        exit();
    }

    $id = (int)($_POST['g_id'] ?? 0);
    if ($id <= 0) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php?tab=garagistes&error=invalid_id");
        exit();
    }

    // Inverse l'état actif
    $pdo->prepare("UPDATE garagiste SET actif = IF(actif=1, 0, 1) WHERE id_garagiste = ?")
        ->execute([$id]);

    header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php?tab=garagistes&success=toggled");
    exit();
}

// ══════════════════════════════════════════════════════════════
//  DELETE GARAGISTE
// ══════════════════════════════════════════════════════════════
function delete_garagiste($pdo) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php");
        exit();
    }

    $id = (int)($_POST['g_id'] ?? 0);
    if ($id <= 0) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php?tab=garagistes&error=invalid_id");
        exit();
    }

    $pdo->prepare("DELETE FROM garagiste WHERE id_garagiste = ?")->execute([$id]);
    header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/back/admin.php?tab=garagistes&success=garagiste_deleted");
    exit();
}

// ══════════════════════════════════════════════════════════════
//  UPDATE PROFILE  — inchangé
// ══════════════════════════════════════════════════════════════
function update_profile($pdo) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php"); exit();
    }
    $id     = (int)$_SESSION['user_id'];
    $prenom = trim($_POST['prenom']    ?? '');
    $nom    = trim($_POST['nom']       ?? '');
    $tel    = trim($_POST['telephone'] ?? '');
    $adr    = trim($_POST['adresse']   ?? '');

    if (!empty($tel) && !preg_match('/^[0-9]{8}$/', $tel)) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/profile.php?error=invalid_phone"); exit();
    }

    $ok = $pdo->prepare("UPDATE client SET prenom=?, nom=?, telephone=?, adresse=? WHERE id_client=?")
               ->execute([$prenom, $nom, $tel, $adr, $id]);

    if ($ok) {
        $_SESSION['prenom']    = $prenom;
        $_SESSION['nom']       = $nom;
        $_SESSION['telephone'] = $tel;
        $_SESSION['adresse']   = $adr;
        $_SESSION['user']      = $prenom;
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/profile.php?success=updated");
    } else {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/profile.php?error=update_failed");
    }
    exit();
}

// ══════════════════════════════════════════════════════════════
//  CHANGER MOT DE PASSE  — inchangé
// ══════════════════════════════════════════════════════════════
function change_password($pdo) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/login.php"); exit();
    }
    $id  = (int)$_SESSION['user_id'];
    $old = $_POST['old_password'] ?? '';
    $new = $_POST['new_password'] ?? '';

    $stmt = $pdo->prepare("SELECT mot_de_passe FROM client WHERE id_client = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row || !password_verify($old, $row['mot_de_passe'])) {
        header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/profile.php?error=wrong_password"); exit();
    }

    $hash = password_hash($new, PASSWORD_BCRYPT);
    $ok   = $pdo->prepare("UPDATE client SET mot_de_passe = ? WHERE id_client = ?")->execute([$hash, $id]);
    header("Location: /Esprit-PI-2PREPA-2026-EntreAUtous/views/front/profile.php?" . ($ok ? 'success=password' : 'error=password_failed'));
    exit();
}

// ══════════════════════════════════════════════════════════════
//  HELPERS IMAGE — inchangés
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