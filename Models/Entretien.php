<?php
require_once __DIR__ . '/../config.php'; // Assurez-vous que le chemin est correct

class Entretien {
    private $id_entretien;
    private $id_voiture;
    private $date_entretien;
    private $kilometrage;
    private $type_intervention;
    private $observations;
    private $statut;
    private $prochaine_echeance;
    private $km_prochain;

    public function __construct($id=null, $idv=null, $date=null, $km=null, $type=null, $obs=null, $stat=null, $pdate=null, $pkm=null){
        $this->id_entretien = $id;
        $this->id_voiture = $idv;
        $this->date_entretien = $date;
        $this->kilometrage = $km;
        $this->type_intervention = $type;
        $this->observations = $obs;
        $this->statut = $stat;
        $this->prochaine_echeance = $pdate;
        $this->km_prochain = $pkm;
    }

    // Getters
    public function getId(){ return $this->id_entretien; }
    public function getIdVoiture(){ return $this->id_voiture; }
    public function getDate(){ return $this->date_entretien; }
    public function getKm(){ return $this->kilometrage; }
    public function getType(){ return $this->type_intervention; }
    public function getObs(){ return $this->observations; }
    public function getStatut(){ return $this->statut; }
    public function getProchaine(){ return $this->prochaine_echeance; }
    public function getKmProchain(){ return $this->km_prochain; }

    // Setters
    public function setId($id){ $this->id_entretien = $id; }
    public function setIdVoiture($idv){ $this->id_voiture = $idv; }
    public function setDate($date){ $this->date_entretien = $date; }
    public function setKm($km){ $this->kilometrage = $km; }
    public function setType($type){ $this->type_intervention = $type; }
    public function setObs($obs){ $this->observations = $obs; }
    public function setStatut($stat){ $this->statut = $stat; }
    public function setProchaine($pdate){ $this->prochaine_echeance = $pdate; }
    public function setKmProchain($pkm){ $this->km_prochain = $pkm; }

    // Méthodes CRUD statiques
    public static function findAll($pdo) {
        $stmt = $pdo->query("SELECT * FROM entre ORDER BY date_entretien DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function findById($pdo, $id) {
        $stmt = $pdo->prepare("SELECT * FROM entre WHERE id_entretien = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function save($pdo) {
        if ($this->id_entretien) {
            // Si id_entretien est fourni, l'insérer
            $sql = "INSERT INTO entre (id_entretien, id_voiture, date_entretien, kilometrage, type_intervention, observations, statut, prochaine_echeance, km_prochain) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $this->id_entretien,
                $this->id_voiture,
                $this->date_entretien,
                $this->kilometrage,
                $this->type_intervention,
                $this->observations,
                $this->statut,
                $this->prochaine_echeance,
                $this->km_prochain
            ]);
        } else {
            // Sinon, laisser auto-increment
            $sql = "INSERT INTO entre (id_voiture, date_entretien, kilometrage, type_intervention, observations, statut, prochaine_echeance, km_prochain) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $this->id_voiture,
                $this->date_entretien,
                $this->kilometrage,
                $this->type_intervention,
                $this->observations,
                $this->statut,
                $this->prochaine_echeance,
                $this->km_prochain
            ]);
            if ($result) {
                $this->id_entretien = $pdo->lastInsertId();
            }
        }
        return $result;
    }

    public function update($pdo) {
        $sql = "UPDATE entre SET id_voiture=?, date_entretien=?, kilometrage=?, type_intervention=?, observations=?, statut=?, prochaine_echeance=?, km_prochain=? WHERE id_entretien=?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            $this->id_voiture,
            $this->date_entretien,
            $this->kilometrage,
            $this->type_intervention,
            $this->observations,
            $this->statut,
            $this->prochaine_echeance,
            $this->km_prochain,
            $this->id_entretien
        ]);
    }

    public static function delete($pdo, $id) {
        $stmt = $pdo->prepare("DELETE FROM entre WHERE id_entretien=?");
        return $stmt->execute([$id]);
    }
}
?>