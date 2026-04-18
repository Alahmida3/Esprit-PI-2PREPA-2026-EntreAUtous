<?php
if (!class_exists('RendezVous')) {
    class RendezVous {
        public $id_rdv;
        public $id_vehicule;
        public $marque_vehicule;
        public $immatriculation;
        public $date_rdv;
        public $heure_rdv;
        public $type_service;
        public $statut;
        public $description;

        public function __construct($id_rdv, $id_vehicule, $marque_vehicule, $immatriculation, $date_rdv, $heure_rdv, $type_service, $statut, $description = "") {
            $this->id_rdv = $id_rdv;
            $this->id_vehicule = $id_vehicule;
            $this->marque_vehicule = $marque_vehicule;
            $this->immatriculation = $immatriculation;
            $this->date_rdv = $date_rdv;
            $this->heure_rdv = $heure_rdv;
            $this->type_service = $type_service;
            $this->statut = $statut;
            $this->description = $description;
        }

        public function getIdRdv() { return $this->id_rdv; }
        public function getIdVehicule() { return $this->id_vehicule; }
        public function getMarqueVehicule() { return $this->marque_vehicule; }
        public function getImmatriculation() { return $this->immatriculation; }
        public function getDateRdv() { return $this->date_rdv; }
        public function getHeureRdv() { return $this->heure_rdv; }
        public function getTypeService() { return $this->type_service; }
        public function getStatut() { return $this->statut; }
        public function getDescription() { return $this->description; }

        public function setIdRdv($id_rdv) { $this->id_rdv = $id_rdv; }
        public function setIdVehicule($id_vehicule) { $this->id_vehicule = $id_vehicule; }
        public function setMarqueVehicule($marque_vehicule) { $this->marque_vehicule = $marque_vehicule; }
        public function setImmatriculation($immatriculation) { $this->immatriculation = $immatriculation; }
        public function setDateRdv($date_rdv) { $this->date_rdv = $date_rdv; }
        public function setHeureRdv($heure_rdv) { $this->heure_rdv = $heure_rdv; }
        public function setTypeService($type_service) { $this->type_service = $type_service; }
        public function setStatut($statut) { $this->statut = $statut; }
        public function setDescription($description) { $this->description = $description; }
    }
}
?>