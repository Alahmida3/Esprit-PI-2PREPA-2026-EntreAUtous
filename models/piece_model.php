<?php
require_once __DIR__ . '/../models/db.php';

class piece {
    protected $id_piece;
    protected $nom_piece;
    protected $reference;
    protected $description;
    protected $prix;
    public $quantite_stock;
    protected $categorie;
    protected $image;
    protected $fourniseur;
    protected $date_ajout;

    public function __construct($id = null) {
        if ($id) {
            $this->load($id);
        }
    }

    public function load($id) {
        $conn = new connexion();
        $pdo = $conn->conx;
        $stmt = $pdo->prepare("SELECT * FROM piece WHERE id_piece = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            $this->id_piece = $data['id_piece'];
            $this->nom_piece = $data['nom_piece'];
            $this->reference = $data['reference'];
            $this->description = $data['description'];
            $this->prix = $data['prix'];
            $this->quantite_stock = $data['quantite_stock'];
            $this->categorie = $data['categorie'];
            $this->image = $data['image'];
            $this->fourniseur = $data['fourniseur'];
            $this->date_ajout = $data['date_ajout'];
        }
    }

    public static function getAll() {
        $conn = new connexion();
        $pdo = $conn->conx;
        $stmt = $pdo->query("SELECT * FROM piece");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function find($id) {
        $conn = new connexion();
        $pdo = $conn->conx;
        $stmt = $pdo->prepare("SELECT * FROM piece WHERE id_piece = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public function save() {
        $conn = new connexion();
        $pdo = $conn->conx;
        if ($this->id_piece) {
            $stmt = $pdo->prepare(
                "UPDATE piece SET nom_piece=?, reference=?, description=?, prix=?, quantite_stock=?, categorie=?, image=?, fourniseur=?, date_ajout=? WHERE id_piece=?"
            );
            $stmt->execute([
                $this->nom_piece,
                $this->reference,
                $this->description,
                $this->prix,
                $this->quantite_stock,
                $this->categorie,
                $this->image,
                $this->fourniseur,
                $this->date_ajout,
                $this->id_piece
            ]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO piece (nom_piece, reference, description, prix, quantite_stock, categorie, image, fourniseur, date_ajout) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->execute([
                $this->nom_piece,
                $this->reference,
                $this->description,
                $this->prix,
                $this->quantite_stock,
                $this->categorie,
                $this->image,
                $this->fourniseur,
                $this->date_ajout
            ]);
            $this->id_piece = $pdo->lastInsertId();
        }
    }
}
?>