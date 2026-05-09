<?php
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../models/piece_model.php';
require_once __DIR__ . '/../models/vente_model.php';

class CartController {
    
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }

    public function addToCart($id_piece, $quantite = 1) {
        $piece = piece::find($id_piece);
        
        if (!$piece) {
            return ['success' => false, 'message' => 'Pièce introuvable'];
        }
        
        if (!is_numeric($quantite) || $quantite <= 0) {
            return ['success' => false, 'message' => 'Quantité invalide'];
        }
        
        if ($piece['quantite_stock'] < $quantite) {
            return ['success' => false, 'message' => 'Stock insuffisant'];
        }
        
        // Check if item already in cart
        if (isset($_SESSION['cart'][$id_piece])) {
            $nouvelle_quantite = $_SESSION['cart'][$id_piece]['quantite'] + $quantite;
            if ($piece['quantite_stock'] < $nouvelle_quantite) {
                return ['success' => false, 'message' => 'Stock insuffisant pour cette quantité'];
            }
            $_SESSION['cart'][$id_piece]['quantite'] = $nouvelle_quantite;
        } else {
            $_SESSION['cart'][$id_piece] = [
                'id_piece' => $piece['id_piece'],
                'nom_piece' => $piece['nom_piece'],
                'reference' => $piece['reference'],
                'prix' => $piece['prix'],
                'quantite' => $quantite
            ];
        }
        
        return ['success' => true, 'message' => 'Article ajouté au panier'];
    }

    public function removeFromCart($id_piece) {
        if (isset($_SESSION['cart'][$id_piece])) {
            unset($_SESSION['cart'][$id_piece]);
            return ['success' => true, 'message' => 'Article supprimé du panier'];
        }
        return ['success' => false, 'message' => 'Article non trouvé dans le panier'];
    }

    public function updateQuantity($id_piece, $quantite) {
        if (!isset($_SESSION['cart'][$id_piece])) {
            return ['success' => false, 'message' => 'Article non trouvé'];
        }
        
        if (!is_numeric($quantite) || $quantite <= 0) {
            return $this->removeFromCart($id_piece);
        }
        
        $piece = piece::find($id_piece);
        if (!$piece || $piece['quantite_stock'] < $quantite) {
            return ['success' => false, 'message' => 'Quantité indisponible'];
        }
        
        $_SESSION['cart'][$id_piece]['quantite'] = $quantite;
        return ['success' => true, 'message' => 'Quantité mise à jour'];
    }

    public function viewCart() {
        $cart = $_SESSION['cart'];
        $total = 0;
        foreach ($cart as $item) {
            $total += $item['prix'] * $item['quantite'];
        }
        return compact('cart', 'total');
    }

    public function clearCart() {
        $_SESSION['cart'] = [];
        return ['success' => true, 'message' => 'Panier vidé'];
    }

    public function getCartCount() {
        return count($_SESSION['cart']);
    }

    public function getCartTotal() {
        $total = 0;
        foreach ($_SESSION['cart'] as $item) {
            $total += $item['prix'] * $item['quantite'];
        }
        return $total;
    }

    public function checkout() {
        if (empty($_SESSION['cart'])) {
            return ['success' => false, 'message' => 'Panier vide'];
        }

        try {
            $conn = new connexion();
            $pdo = $conn->conx;
            $pdo->beginTransaction();

            foreach ($_SESSION['cart'] as $item) {
                // Créer une vente pour chaque article
                $vente = new Vente(null, $item['id_piece'], $item['quantite']);
                $vente->save();

                // Mettre à jour le stock
                $piece = piece::find($item['id_piece']);
                if ($piece) {
                    $piece['quantite_stock'] -= $item['quantite'];
                    $stmt = $pdo->prepare("UPDATE piece SET quantite_stock=? WHERE id_piece=?");
                    $stmt->execute([$piece['quantite_stock'], $item['id_piece']]);
                }
            }

            $pdo->commit();
            $_SESSION['cart'] = [];
            return ['success' => true, 'message' => 'Commande enregistrée avec succès'];
        } catch (Exception $e) {
            $pdo->rollBack();
            return ['success' => false, 'message' => 'Erreur lors du paiement: ' . $e->getMessage()];
        }
    }
}
?>
