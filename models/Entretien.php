<?php
require_once __DIR__ . '/../models/db.php';

class Entretien {
    private $id_entretien;
    private $Matricule;
    private $date_entretien;
    private $kilometrage;
    private $type_intervention;
    private $observations;
    private $statut;
    private $prochaine_echeance;
    private $km_prochain;

    public function __construct($id=null, $matricule=null, $date=null, $km=null, $type=null, $obs=null, $stat=null, $pdate=null, $pkm=null){
        $this->id_entretien       = $id;
        $this->Matricule          = $matricule;
        $this->date_entretien     = $date;
        $this->kilometrage        = $km;
        $this->type_intervention  = $type;
        $this->observations       = $obs;
        $this->statut             = $stat;
        $this->prochaine_echeance = $pdate;
        $this->km_prochain        = $pkm;
    }

    // Getters
    public function getId()         { return $this->id_entretien; }
    public function getMatricule()  { return $this->Matricule; }
    public function getDate()       { return $this->date_entretien; }
    public function getKm()         { return $this->kilometrage; }
    public function getType()       { return $this->type_intervention; }
    public function getObs()        { return $this->observations; }
    public function getStatut()     { return $this->statut; }
    public function getProchaine()  { return $this->prochaine_echeance; }
    public function getKmProchain() { return $this->km_prochain; }

    // Setters
    public function setId($v)          { $this->id_entretien = $v; }
    public function setMatricule($v)   { $this->Matricule = $v; }
    public function setDate($v)        { $this->date_entretien = $v; }
    public function setKm($v)          { $this->kilometrage = $v; }
    public function setType($v)        { $this->type_intervention = $v; }
    public function setObs($v)         { $this->observations = $v; }
    public function setStatut($v)      { $this->statut = $v; }
    public function setProchaine($v)   { $this->prochaine_echeance = $v; }
    public function setKmProchain($v)  { $this->km_prochain = $v; }

    // ── Lecture ────────────────────────────────────────────────

    /** Entretiens actifs (non supprimés) */
    public static function findAll($pdo) {
        $stmt = $pdo->query("SELECT * FROM entre WHERE deleted_at IS NULL ORDER BY date_entretien DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Entretiens supprimés (corbeille) */
    public static function findDeleted($pdo) {
        $stmt = $pdo->query("SELECT * FROM entre WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Un entretien par ID (actif ou supprimé) */
    public static function findById($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM entre WHERE id_entretien = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Tous les matricules de la table vehicule */
    public static function getAllMatricules($pdo) {
        $stmt = $pdo->query("SELECT matriculevoiture FROM vehicule ORDER BY matriculevoiture ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ── Écriture ───────────────────────────────────────────────

    public function save($pdo) {
        $sql = "INSERT INTO entre (Matricule, date_entretien, kilometrage, type_intervention, observations, statut, prochaine_echeance, km_prochain)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $result = $stmt->execute([
            $this->Matricule,
            $this->date_entretien,
            $this->kilometrage,
            $this->type_intervention ?? '',
            $this->observations      ?? '',
            $this->statut,
            ($this->prochaine_echeance !== '' && $this->prochaine_echeance !== null ? $this->prochaine_echeance : null),
            ($this->km_prochain        !== '' && $this->km_prochain        !== null ? $this->km_prochain        : null),
        ]);
        if ($result) $this->id_entretien = $pdo->lastInsertId();
        return $result;
    }

    public function update($pdo) {
        $sql = "UPDATE entre SET Matricule=?, date_entretien=?, kilometrage=?, type_intervention=?, observations=?, statut=?, prochaine_echeance=?, km_prochain=? WHERE id_entretien=?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $this->Matricule,
            $this->date_entretien,
            $this->kilometrage,
            $this->type_intervention ?? '',
            $this->observations      ?? '',
            $this->statut,
            ($this->prochaine_echeance !== '' && $this->prochaine_echeance !== null ? $this->prochaine_echeance : null),
            ($this->km_prochain        !== '' && $this->km_prochain        !== null ? $this->km_prochain        : null),
            $this->id_entretien
        ]);
    }

    /** Soft delete : marque deleted_at */
    public static function delete($pdo, $id) {
        $stmt = $pdo->prepare("UPDATE entre SET deleted_at = NOW() WHERE id_entretien = ?");
        return $stmt->execute([$id]);
    }

    /** Restaurer un entretien supprimé */
    public static function restore($pdo, $id) {
        $stmt = $pdo->prepare("UPDATE entre SET deleted_at = NULL WHERE id_entretien = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Liste filtrée : statut et/ou date, avec tri.
     */
    public static function findFiltered($pdo, $statut = null, $date = null, $sort = 'recent') {
        $sql    = "SELECT * FROM entre WHERE deleted_at IS NULL";
        $params = [];
        if ($statut !== null && $statut !== '') {
            $sql    .= " AND statut = ?";
            $params[] = $statut;
        }
        if ($date !== null && $date !== '') {
            $sql    .= " AND date_entretien = ?";
            $params[] = $date;
        }
        $sql .= ($sort === 'oldest') ? " ORDER BY date_entretien ASC" : " ORDER BY date_entretien DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Nombre d'entretiens actifs par statut.
     */
    public static function countByStatut($pdo) {
        $stmt = $pdo->query(
            "SELECT statut, COUNT(*) AS nb FROM entre WHERE deleted_at IS NULL GROUP BY statut"
        );
        $rows   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $counts = ['planifie' => 0, 'en_cours' => 0, 'termine' => 0, 'annule' => 0];
        $total  = 0;
        foreach ($rows as $r) {
            if (array_key_exists($r['statut'], $counts)) {
                $counts[$r['statut']] = (int)$r['nb'];
            }
            $total += (int)$r['nb'];
        }
        $counts['total'] = $total;
        return $counts;
    }
    public static function findByClient($pdo, $clientId) {
    $sql = "SELECT e.* FROM entre e 
            INNER JOIN vehicule v ON e.Matricule = v.matriculevoiture
            WHERE v.idclient = ? AND e.deleted_at IS NULL 
            ORDER BY e.date_entretien DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([(int)$clientId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    
}
?>