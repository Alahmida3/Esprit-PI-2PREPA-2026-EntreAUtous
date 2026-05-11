<?php
require_once __DIR__ . '/../models/db.php';

class Facture {
    private $id_facture;
    private $ref_facture;
    private $date_emission;
    private $montant_ht;
    private $taux_tva;
    private $montant_ttc;
    private $mode_paiement;
    private $etat_paiement;
    private $entretien;

    public function __construct($id=null,$ref='',$date=null,$ht=0,$tva=0,$ttc=0,$mode='',$etat='',$entretien=null){
        $this->id_facture    = $id;
        $this->ref_facture   = $ref;
        $this->date_emission = $date;
        $this->montant_ht    = $ht;
        $this->taux_tva      = $tva;
        $this->montant_ttc   = $ttc;
        $this->mode_paiement = $mode;
        $this->etat_paiement = $etat;
        $this->entretien     = $entretien;
    }

    // Getters
    public function getId()        { return $this->id_facture; }
    public function getRef()       { return $this->ref_facture; }
    public function getDate()      { return $this->date_emission; }
    public function getHt()        { return $this->montant_ht; }
    public function getTva()       { return $this->taux_tva; }
    public function getTtc()       { return $this->montant_ttc; }
    public function getMode()      { return $this->mode_paiement; }
    public function getEtat()      { return $this->etat_paiement; }
    public function getEntretien() { return $this->entretien; }

    // ── CRUD ───────────────────────────────────────────────────

    public function save($pdo) {
        if ($this->id_facture) {
            $sql  = "UPDATE facture SET ref_facture=?,date_emission=?,montant_ht=?,taux_tva=?,montant_ttc=?,mode_paiement=?,etat_paiement=?,entretien=? WHERE id_facture=?";
            $stmt = $pdo->prepare($sql);
            return $stmt->execute([
                $this->ref_facture,$this->date_emission,$this->montant_ht,
                $this->taux_tva,$this->montant_ttc,$this->mode_paiement,
                $this->etat_paiement,$this->entretien,$this->id_facture
            ]);
        } else {
            $sql  = "INSERT INTO facture (ref_facture,date_emission,montant_ht,taux_tva,montant_ttc,mode_paiement,etat_paiement,entretien) VALUES (?,?,?,?,?,?,?,?)";
            $stmt = $pdo->prepare($sql);
            $res  = $stmt->execute([
                $this->ref_facture,$this->date_emission,$this->montant_ht,
                $this->taux_tva,$this->montant_ttc,$this->mode_paiement,
                $this->etat_paiement,$this->entretien
            ]);
            if ($res) $this->id_facture = $pdo->lastInsertId();
            return $res;
        }
    }

    public static function findByEntretien($pdo, $entretienId) {
        $stmt = $pdo->prepare(
            "SELECT f.* FROM facture f
             INNER JOIN entre e ON f.entretien = e.id_entretien
             WHERE f.entretien = ? AND f.deleted_at IS NULL AND e.deleted_at IS NULL
             ORDER BY f.date_emission DESC"
        );
        $stmt->execute([$entretienId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findAll($pdo) {
        $stmt = $pdo->query("
            SELECT f.*, e.type_intervention, e.Matricule
            FROM facture f
            JOIN entre e ON f.entretien = e.id_entretien
            WHERE f.deleted_at IS NULL AND e.deleted_at IS NULL
            ORDER BY f.date_emission DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findDeleted($pdo) {
        $stmt = $pdo->query("
            SELECT f.*, e.type_intervention, e.Matricule
            FROM facture f
            JOIN entre e ON f.entretien = e.id_entretien
            WHERE f.deleted_at IS NOT NULL
            ORDER BY f.deleted_at DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById($pdo, $id) {
        $stmt = $pdo->prepare(
            "SELECT f.*, e.type_intervention, e.Matricule
             FROM facture f
             LEFT JOIN entre e ON f.entretien = e.id_entretien
             WHERE f.id_facture = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function delete($pdo, $id) {
        $stmt = $pdo->prepare("UPDATE facture SET deleted_at = NOW() WHERE id_facture = ?");
        return $stmt->execute([$id]);
    }

    public static function restore($pdo, $id) {
        $check = $pdo->prepare(
            "SELECT e.deleted_at FROM facture f
             JOIN entre e ON f.entretien = e.id_entretien
             WHERE f.id_facture = ?"
        );
        $check->execute([$id]);
        $row = $check->fetch(PDO::FETCH_ASSOC);
        if (!$row || $row['deleted_at'] !== null) return 'entretien_supprime';
        $stmt = $pdo->prepare("UPDATE facture SET deleted_at = NULL WHERE id_facture = ?");
        return $stmt->execute([$id]);
    }

    public static function restoreByEntretien($pdo, $idEntretien) {
        $stmt = $pdo->prepare("UPDATE facture SET deleted_at = NULL WHERE entretien = ?");
        return $stmt->execute([$idEntretien]);
    }

    public static function findFiltered($pdo, $entretienId=null, $date=null, $sort='recent') {
        $sql    = "SELECT f.* FROM facture f INNER JOIN entre e ON f.entretien=e.id_entretien WHERE f.deleted_at IS NULL AND e.deleted_at IS NULL";
        $params = [];
        if ($entretienId) { $sql .= " AND f.entretien=?";      $params[] = $entretienId; }
        if ($date)        { $sql .= " AND f.date_emission=?";  $params[] = $date; }
        switch ($sort) {
            case 'price-asc':  $sql .= " ORDER BY f.montant_ttc ASC";  break;
            case 'price-desc': $sql .= " ORDER BY f.montant_ttc DESC"; break;
            default:           $sql .= " ORDER BY f.date_emission DESC"; break;
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public static function findFiltreeFront($pdo, ?int $entretienId, string $searchRef, string $searchDate, string $sort): array
    {
        $sql    = "SELECT f.* FROM facture f
                   INNER JOIN entre e ON f.entretien = e.id_entretien
                   WHERE f.deleted_at IS NULL AND e.deleted_at IS NULL";
        $params = [];

        if ($entretienId) {
            $sql .= " AND f.entretien = ?";
            $params[] = $entretienId;
        }
        if ($searchRef !== '') {
            $sql .= " AND f.ref_facture LIKE ?";
            $params[] = '%' . $searchRef . '%';
        }
        if ($searchDate !== '') {
            $sql .= " AND f.date_emission = ?";
            $params[] = $searchDate;
        }

        switch ($sort) {
            case 'price-asc':  $sql .= " ORDER BY f.montant_ttc ASC";   break;
            case 'price-desc': $sql .= " ORDER BY f.montant_ttc DESC";  break;
            default:           $sql .= " ORDER BY f.date_emission DESC"; break;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
   public static function findFiltreeFrontByClient($pdo, $clientId, $searchRef = '', $searchDate = '', $sort = 'recent') {
    $sql = "SELECT f.* FROM facture f
            INNER JOIN entre e ON f.entretien = e.id_entretien
            INNER JOIN vehicule v ON e.Matricule = v.matriculevoiture
            WHERE v.idclient = ? 
              AND f.deleted_at IS NULL 
              AND e.deleted_at IS NULL";
    
    $params = [(int)$clientId];

    if ($searchRef !== '') {
        $sql .= " AND f.ref_facture LIKE ?";
        $params[] = '%' . $searchRef . '%';
    }
    if ($searchDate !== '') {
        $sql .= " AND f.date_emission = ?";
        $params[] = $searchDate;
    }

    // Gestion du tri
    switch ($sort) {
        case 'price-asc':  $sql .= " ORDER BY f.montant_ttc ASC";  break;
        case 'price-desc': $sql .= " ORDER BY f.montant_ttc DESC"; break;
        default:           $sql .= " ORDER BY f.date_emission DESC"; break;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}
?>