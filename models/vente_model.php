<?php
require_once __DIR__ . '/../models/db.php';

class Vente {
    public $id;
    public $id_piece;
    public $quantite;
    public $date_vente;

    public function __construct($id = null, $id_piece = null, $quantite = null, $date_vente = null) {
        $this->id = $id;
        $this->id_piece = $id_piece;
        $this->quantite = $quantite;
        $this->date_vente = $date_vente ?: date('Y-m-d H:i:s');
    }

    public function save() {
        $conn = new connexion();
        $pdo = $conn->conx;
        $stmt = $pdo->prepare("INSERT INTO vente (id_piece, quantite, date_vente) VALUES (?, ?, ?)");
        $stmt->execute([$this->id_piece, $this->quantite, $this->date_vente]);
        $this->id = $pdo->lastInsertId();
    }

    public static function buyPiece($id_piece, $quantite) {
        $conn = new connexion();
        $pdo = $conn->conx;
        $pdo->beginTransaction();
        try {
            $piece = piece::find($id_piece);
            if (!$piece) {
                throw new Exception("Pièce introuvable");
            }
            if ($piece->quantite_stock < $quantite) {
                throw new Exception("Stock insuffisant");
            }
            $piece->quantite_stock -= $quantite;
            $piece->save();
            $vente = new Vente(null, $id_piece, $quantite);
            $vente->save();
            $pdo->commit();
            return true;
        } catch (Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
?>