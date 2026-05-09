<?php
require_once __DIR__ . '/Controller/PieceController.php';
require_once __DIR__ . '/Controller/CartController.php';
require_once __DIR__ . '/Controller/PageController.php';
require_once __DIR__ . '/Controller/AdminController.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$pieceController = new PieceController();
$cartController  = new CartController();
$pageController  = new PageController();
$adminController = new AdminController();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if ($action == 'index') {
    $pieceController->index();
} elseif ($action == 'add_to_cart') {
    $id_piece = isset($_POST['id_piece']) ? $_POST['id_piece'] : null;
    $quantite  = isset($_POST['quantite']) ? $_POST['quantite'] : 1;
    if ($id_piece) {
        $result = $cartController->addToCart($id_piece, $quantite);
        $_SESSION['cart_message'] = $result['message'];
        $_SESSION['cart_success'] = $result['success'];
    }
    header('Location: index.php');
    exit;
} elseif ($action == 'cart') {
    include __DIR__ . '/views/Front_back/CartView.php';
} elseif ($action == 'checkout') {
    $result = $cartController->checkout();
    $_SESSION['payment_message'] = $result['message'];
    $_SESSION['payment_success'] = $result['success'];
    header('Location: index.php?action=payment_confirmation');
    exit;
} elseif ($action == 'payment_confirmation') {
    include __DIR__ . '/views/Front_back/PaymentConfirmationView.php';
} elseif ($action == 'remove_from_cart') {
    $id_piece = isset($_POST['id_piece']) ? $_POST['id_piece'] : null;
    if ($id_piece) $cartController->removeFromCart($id_piece);
    header('Location: index.php?action=cart');
    exit;
} elseif ($action == 'update_cart') {
    $id_piece = isset($_POST['id_piece']) ? $_POST['id_piece'] : null;
    $quantite  = isset($_POST['quantite']) ? $_POST['quantite'] : 1;
    if ($id_piece) $cartController->updateQuantity($id_piece, $quantite);
    header('Location: index.php?action=cart');
    exit;
} elseif ($action == 'clear_cart') {
    $cartController->clearCart();
    header('Location: index.php?action=cart');
    exit;
} elseif ($action == 'about') {
    $pageController->about();
} elseif ($action == 'team') {
    $pageController->team();
} elseif ($action == 'services') {
    $pageController->services();
} elseif ($action == 'admin') {
    $adminController->dashboard();
} elseif ($action == 'add_piece') {
    $adminController->addPiece();
} elseif ($action == 'edit_piece') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) $adminController->editPiece($id);
} elseif ($action == 'delete_piece') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) $adminController->deletePiece($id);
} elseif ($action == 'edit_vente') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) $adminController->editVente($id);
} elseif ($action == 'delete_vente') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    if ($id) $adminController->deleteVente($id);

// ─── Nouvelle route : export PDF des ventes d'une pièce ───────────────────
} elseif ($action == 'export_pdf_vente') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : null;
    if ($id) {
        $adminController->exportVentePDF($id);
    } else {
        $_SESSION['error'] = "ID de pièce manquant pour l'export PDF.";
        header('Location: index.php?action=admin');
        exit;
    }
}
?>
