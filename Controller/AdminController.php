<?php
require_once __DIR__ . '/../models/db.php';


class AdminController {
    
    public function dashboard() {
        $conn = new connexion();
        $pdo = $conn->conx;
        
        // ── Tri pièces ──────────────────────────────────────────────────────
        $sortPiece  = isset($_GET['sort_piece'])  ? $_GET['sort_piece']  : 'id_piece';
        $orderPiece = isset($_GET['order_piece']) && $_GET['order_piece'] === 'desc' ? 'DESC' : 'ASC';
        $allowedPieceCols = ['id_piece','nom_piece','reference','categorie','prix','quantite_stock','fourniseur'];
        if (!in_array($sortPiece, $allowedPieceCols)) $sortPiece = 'id_piece';

        $stmt = $pdo->query("SELECT * FROM piece ORDER BY $sortPiece $orderPiece");
        $pieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Tri ventes ───────────────────────────────────────────────────────
        $sortVente  = isset($_GET['sort_vente'])  ? $_GET['sort_vente']  : 'date_vente';
        $orderVente = isset($_GET['order_vente']) && $_GET['order_vente'] === 'asc' ? 'ASC' : 'DESC';
        $allowedVenteCols = ['id','nom_piece','quantite','date_vente'];
        if (!in_array($sortVente, $allowedVenteCols)) $sortVente = 'date_vente';
        $sortVenteSQL = ($sortVente === 'nom_piece') ? "p.nom_piece" : "v.$sortVente";

        $stmt = $pdo->query("SELECT v.*, p.nom_piece, p.prix FROM vente v
                             JOIN piece p ON v.id_piece = p.id_piece
                             ORDER BY $sortVenteSQL $orderVente");
        $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // ── Stats de base ────────────────────────────────────────────────────
        $totalPieces = $pdo->query("SELECT COUNT(*) FROM piece")->fetchColumn();
        $totalVentes = $pdo->query("SELECT COUNT(*) FROM vente")->fetchColumn();
        $totalStock  = $pdo->query("SELECT COALESCE(SUM(quantite_stock),0) FROM piece")->fetchColumn();

        // ── Stats avancées ───────────────────────────────────────────────────
        // Chiffre d'affaires total
        $stmt = $pdo->query("SELECT COALESCE(SUM(v.quantite * p.prix),0) as ca
                             FROM vente v JOIN piece p ON v.id_piece = p.id_piece");
        $chiffreAffaires = $stmt->fetchColumn();

        // Pièce la plus vendue
        $stmt = $pdo->query("SELECT p.nom_piece, SUM(v.quantite) as total_qte
                             FROM vente v JOIN piece p ON v.id_piece = p.id_piece
                             GROUP BY v.id_piece ORDER BY total_qte DESC LIMIT 1");
        $topPiece = $stmt->fetch(PDO::FETCH_ASSOC);

        // Ventes des 7 derniers jours (pour graphique)
        $stmt = $pdo->query("SELECT DATE(date_vente) as jour, COUNT(*) as nb_ventes,
                             SUM(v.quantite * p.prix) as ca_jour
                             FROM vente v JOIN piece p ON v.id_piece = p.id_piece
                             WHERE date_vente >= DATE_SUB(NOW(), INTERVAL 7 DAY)
                             GROUP BY DATE(date_vente) ORDER BY jour ASC");
        $ventesParJour = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Top 5 pièces par chiffre d'affaires
        $stmt = $pdo->query("SELECT p.nom_piece, SUM(v.quantite) as total_qte,
                             SUM(v.quantite * p.prix) as ca_piece
                             FROM vente v JOIN piece p ON v.id_piece = p.id_piece
                             GROUP BY v.id_piece ORDER BY ca_piece DESC LIMIT 5");
        $topPieces = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stock faible (< 5)
        $stmt = $pdo->query("SELECT COUNT(*) FROM piece WHERE quantite_stock < 5");
        $stockFaible = $stmt->fetchColumn();

        include __DIR__ . '/../views/Front_back/AdminView.php';
    }
    
    public function addPiece() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = new connexion();
            $pdo = $conn->conx;
            
            $stmt = $pdo->prepare("INSERT INTO piece (nom_piece, reference, description, prix, quantite_stock, categorie, image, fourniseur, date_ajout) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->execute([
                $_POST['nom_piece'] ?? '',
                $_POST['reference'] ?? '',
                $_POST['description'] ?? '',
                $_POST['prix'] ?? 0,
                $_POST['quantite_stock'] ?? 0,
                $_POST['categorie'] ?? '',
                $_POST['image'] ?? '',
                $_POST['fourniseur'] ?? ''
            ]);
            
            $_SESSION['message'] = "Pièce ajoutée avec succès!";
            header('Location: index.php?action=admin');
            exit;
        }
    }
    
    public function editPiece($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = new connexion();
            $pdo = $conn->conx;
            
            $stmt = $pdo->prepare("UPDATE piece SET nom_piece=?, reference=?, description=?, prix=?, quantite_stock=?, categorie=?, image=?, fourniseur=? 
                                   WHERE id_piece=?");
            $stmt->execute([
                $_POST['nom_piece'] ?? '',
                $_POST['reference'] ?? '',
                $_POST['description'] ?? '',
                $_POST['prix'] ?? 0,
                $_POST['quantite_stock'] ?? 0,
                $_POST['categorie'] ?? '',
                $_POST['image'] ?? '',
                $_POST['fourniseur'] ?? '',
                $id
            ]);
            
            $_SESSION['message'] = "Pièce modifiée avec succès!";
            header('Location: index.php?action=admin');
            exit;
        }
    }
    
    public function deletePiece($id) {
        $conn = new connexion();
        $pdo = $conn->conx;
        
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("DELETE FROM vente WHERE id_piece = ?");
            $stmt->execute([$id]);
            $stmt = $pdo->prepare("DELETE FROM piece WHERE id_piece = ?");
            $stmt->execute([$id]);
            $pdo->commit();
            $_SESSION['message'] = "Pièce supprimée avec succès!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur lors de la suppression!";
        }
        
        header('Location: index.php?action=admin');
        exit;
    }

    public function editVente($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $conn = new connexion();
            $pdo = $conn->conx;

            $nouvelle_quantite = isset($_POST['quantite']) ? (int)$_POST['quantite'] : null;
            $nouvelle_date     = isset($_POST['date_vente']) ? $_POST['date_vente'] : null;

            if (!$nouvelle_quantite || $nouvelle_quantite <= 0) {
                $_SESSION['error'] = "Quantité invalide.";
                header('Location: index.php?action=admin');
                exit;
            }

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("SELECT * FROM vente WHERE id = ?");
                $stmt->execute([$id]);
                $vente = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$vente) throw new Exception("Vente introuvable.");

                $difference = $nouvelle_quantite - $vente['quantite'];

                if ($difference > 0) {
                    $stmt = $pdo->prepare("SELECT quantite_stock FROM piece WHERE id_piece = ?");
                    $stmt->execute([$vente['id_piece']]);
                    $piece = $stmt->fetch(PDO::FETCH_ASSOC);
                    if (!$piece || $piece['quantite_stock'] < $difference)
                        throw new Exception("Stock insuffisant pour augmenter la quantité.");
                }

                $stmt = $pdo->prepare("UPDATE piece SET quantite_stock = quantite_stock - ? WHERE id_piece = ?");
                $stmt->execute([$difference, $vente['id_piece']]);

                $stmt = $pdo->prepare("UPDATE vente SET quantite = ?, date_vente = ? WHERE id = ?");
                $stmt->execute([$nouvelle_quantite, $nouvelle_date, $id]);

                $pdo->commit();
                $_SESSION['message'] = "Vente modifiée avec succès!";
            } catch (Exception $e) {
                $pdo->rollBack();
                $_SESSION['error'] = "Erreur : " . $e->getMessage();
            }

            header('Location: index.php?action=admin');
            exit;
        }
    }

    public function deleteVente($id) {
        $conn = new connexion();
        $pdo = $conn->conx;

        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("SELECT * FROM vente WHERE id = ?");
            $stmt->execute([$id]);
            $vente = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$vente) throw new Exception("Vente introuvable.");

            $stmt = $pdo->prepare("UPDATE piece SET quantite_stock = quantite_stock + ? WHERE id_piece = ?");
            $stmt->execute([$vente['quantite'], $vente['id_piece']]);

            $stmt = $pdo->prepare("DELETE FROM vente WHERE id = ?");
            $stmt->execute([$id]);

            $pdo->commit();
            $_SESSION['message'] = "Vente supprimée et stock restauré avec succès!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = "Erreur lors de la suppression : " . $e->getMessage();
        }

        header('Location: index.php?action=admin');
        exit;
    }

    /**
     * Export PDF des ventes d'une pièce donnée.
     * Utilise une génération HTML→print-friendly sans dépendance externe.
     * Pour une vraie lib PDF, remplacer par mPDF/TCPDF.
     */
    public function exportVentePDF($id_piece) {
        $conn = new connexion();
        $pdo = $conn->conx;

        // Infos pièce
        $stmt = $pdo->prepare("SELECT * FROM piece WHERE id_piece = ?");
        $stmt->execute([$id_piece]);
        $piece = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$piece) {
            $_SESSION['error'] = "Pièce introuvable.";
            header('Location: index.php?action=admin');
            exit;
        }

        // Ventes de cette pièce
        $stmt = $pdo->prepare("SELECT v.*, p.nom_piece, p.prix, p.reference
                               FROM vente v JOIN piece p ON v.id_piece = p.id_piece
                               WHERE v.id_piece = ?
                               ORDER BY v.date_vente DESC");
        $stmt->execute([$id_piece]);
        $ventes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Stats de la pièce
        $stmt = $pdo->prepare("SELECT COUNT(*) as nb_ventes, COALESCE(SUM(quantite),0) as total_qte
                               FROM vente WHERE id_piece = ?");
        $stmt->execute([$id_piece]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $totalCA = $stats['total_qte'] * $piece['prix'];

        include __DIR__ . '/../views/Front_back/PdfVenteView.php';
        exit;
    }
}
?>
