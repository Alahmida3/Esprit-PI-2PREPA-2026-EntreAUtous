<?php
// controller/UserController.php
require_once '../models/db.php';
session_start();


// --- AJOUTEZ CE BLOC ICI ---
// On gère la déconnexion via l'URL (GET)
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_unset();    // Vide les variables de session
    session_destroy();  // Détruit la session
    header("Location: ../views/front/login.php");
    exit();
}
// ---------------------------

   // ... reste de votre code POST actuel (register, login, etc.)
if (isset($_POST['action'])) {
    if ($_POST['action'] == 'register') {
        register($pdo);
    } elseif ($_POST['action'] == 'login') {
        login($pdo);
    }
    elseif ($_POST['action'] == 'update_profile') {
        update_profile($pdo);
    }
}
function update_profile($pdo) {
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../views/front/login.php");
        exit();
    }

    $id = $_SESSION['user_id'];
    $prenom = htmlspecialchars($_POST['prenom']);
    $nom = htmlspecialchars($_POST['nom']);
    $tel = htmlspecialchars($_POST['telephone']);
    $adr = htmlspecialchars($_POST['adresse']);

    $sql = "UPDATE client SET prenom = ?, nom = ?, telephone = ?, adresse = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    
    if ($stmt->execute([$prenom, $nom, $tel, $adr, $id])) {
        // On met à jour la session pour que les changements soient visibles de suite
        $_SESSION['prenom'] = $prenom;
        $_SESSION['nom'] = $nom;
        $_SESSION['telephone'] = $tel;
        $_SESSION['adresse'] = $adr;
        
        header("Location: ../views/front/profile.php?success=updated");
    } else {
        header("Location: ../views/front/profile.php?error=update_failed");
    }
    exit();
}
function register($pdo) {
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $tel = htmlspecialchars($_POST['telephone']);
    $pass = password_hash($_POST['mot_de_passe'], PASSWORD_BCRYPT);
    $adr = htmlspecialchars($_POST['adresse']);

    // Contrôle de saisie simple
    if (empty($prenom) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($_POST['mot_de_passe']) < 6) {
        header("Location: ../views/front/register.php?error=invalid_data");
        exit();
    }

    $sql = "INSERT INTO client (prenom, email, telephone, mot_de_passe, adresse, date_inscription) VALUES (?, ?, ?, ?, ?, NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$prenom, $email, $tel, $pass, $adr]);

    header("Location: ../views/front/login.php?success=registered");
}

function login($pdo) {
    $email = $_POST['email'];
    $pass = $_POST['mot_de_passe'];

    // Vérification Admin (Identifiants fixes selon votre demande)
    if ($email == "admin@garage.com" && $pass == "admin123") {
        $_SESSION['user'] = "Admin";
        $_SESSION['role'] = "admin";
        header("Location: ../views/back/admin.php");
        exit();
    }

    // Vérification Client
    $stmt = $pdo->prepare("SELECT * FROM client WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['mot_de_passe'])) {
        $_SESSION['user'] = $user['prenom'];
        $_SESSION['role'] = 'client';
        header("Location: ../views/front/home.php");
    } else {
        header("Location: ../views/front/login.php?error=failed");
    }
}
?>