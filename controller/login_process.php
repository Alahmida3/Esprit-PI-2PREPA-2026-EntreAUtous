<?php
session_start();

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if ($email === "admin@entreautout.com" && $password === "admin123") {

    $_SESSION['role'] = "employe";
    $_SESSION['prenom'] = "Admin";
    $_SESSION['email'] = $email;

    header("Location: /web/views/back/client.php");
    exit;
}

// CLIENT
$_SESSION['role'] = "client";
$_SESSION['prenom'] = "Client";
$_SESSION['email'] = $email;

header("Location: /web/views/front/home.php");
exit;