<?php
require_once __DIR__ . '/../models/piece_model.php';
require_once __DIR__ . '/../models/vente_model.php';

class PieceController {
    public function index($message = null) {
        $pieces = piece::getAll();
        $search = isset($_GET['search']) ? $_GET['search'] : '';
        $category = isset($_GET['category']) ? $_GET['category'] : '';
        
        // Apply filters
        if (!empty($search)) {
            $pieces = array_filter($pieces, function($piece) use ($search) {
                return stripos($piece['nom_piece'], $search) !== false || 
                       stripos($piece['reference'], $search) !== false ||
                       stripos($piece['description'], $search) !== false;
            });
        }
        if (!empty($category)) {
            $pieces = array_filter($pieces, function($piece) use ($category) {
                return $piece['categorie'] === $category;
            });
        }
        
        // Get all categories
        $categories = array_unique(array_column(piece::getAll(), 'categorie'));
        sort($categories);
        
        include __DIR__ . '/../views/Front_back/PieceView.php';
    }

    public function buy($id_piece, $quantite = 1) {
        if (!is_numeric($id_piece) || !is_numeric($quantite) || $quantite <= 0) {
            return "Invalid input.";
        }
        try {
            Vente::buyPiece($id_piece, $quantite);
            return "Purchase successful!";
        } catch (Exception $e) {
            return "Purchase failed: " . $e->getMessage();
        }
    }
}
?>