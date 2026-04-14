<?php
session_start();

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=garage_db;charset=utf8",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Erreur connexion DB : " . $e->getMessage());
}

// données
$nom = trim($_POST['nom']);
$prenom = trim($_POST['prenom']);
$email = trim($_POST['email']);
$telephone = trim($_POST['telephone']);
$adresse = trim($_POST['adresse']);
$password = $_POST['password'];

// ---------------- VALIDATIONS ----------------

// champs obligatoires
if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    die("❌ Champs obligatoires manquants !");
}

// email valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    die("❌ Email invalide !");
}

// mot de passe min 6
if (strlen($password) < 6) {
    die("❌ Mot de passe trop court !");
}

// telephone = 8 chiffres EXACTEMENT
if (!preg_match("/^[0-9]{8}$/", $telephone)) {
    die("❌ Téléphone invalide (8 chiffres requis) !");
}

// email déjà utilisé
$check = $pdo->prepare("SELECT id_client FROM client WHERE email = ?");
$check->execute([$email]);

if ($check->rowCount() > 0) {
    die("❌ Email déjà utilisé !");
}

// ---------------- INSERT ----------------

$mot_de_passe = password_hash($password, PASSWORD_DEFAULT);

$sql = "INSERT INTO client (nom, prenom, email, telephone, adresse, mot_de_passe)
        VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $pdo->prepare($sql);
$stmt->execute([$nom, $prenom, $email, $telephone, $adresse, $mot_de_passe]);

header("Location: ../views/front/login.php");
exit;