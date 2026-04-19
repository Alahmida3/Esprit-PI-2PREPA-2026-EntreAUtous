<?php
if (!class_exists('RendezVous')) {
    class RendezVous {
        private $idRDV;
        private $dateRDV;
        private $heureRDV;
        private $type_serviceRDV;
        private $statutRDV;
        private $idVehicule;
        private $idclientRDV;
        private $descriptionRDV; 

        public function __construct($idRDV, $dateRDV, $heureRDV, $type_serviceRDV, $statutRDV, $idVehicule, $idclientRDV , $descriptionRDV) {
            $this->idRDV = $idRDV;
            $this->dateRDV = $dateRDV;
            $this->heureRDV = $heureRDV;
            $this->type_serviceRDV = $type_serviceRDV;
            $this->statutRDV = $statutRDV;
            $this->idVehicule = $idVehicule;
            $this->idclientRDV = $idclientRDV;
            $this->descriptionRDV = $descriptionRDV;
        }

        // Getters
        public function getIdRDV() { return $this->idRDV; }
        public function getDateRDV() { return $this->dateRDV; }
        public function getHeureRDV() { return $this->heureRDV; }
        public function getTypeServiceRDV() { return $this->type_serviceRDV; }
        public function getStatutRDV() { return $this->statutRDV; }
        public function getIdVehicule() { return $this->idVehicule; }
        public function getIdClientRDV() { return $this->idclientRDV; }
        public function getDescriptionRDV() { return $this->descriptionRDV; }
    }
}
?>