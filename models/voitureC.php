<?php
class Voiture{
    private $idVoiture;
    private $marqueVoiture;
    private $kilometrage;
    private $dateAjoutVoiture;
    private $imageVoiture;
    private $matriculeVoiture;
    private $nomClient;
    private $idClient;

    public function __construct($id = null, $marque = null, $kilometrage = null, $dateAjoutVoiture = null, $imageVoiture = null, $matriculeVoiture = null, $nomClient = null, $idClient = null) {
        $this->idVoiture = $id;
        $this->marqueVoiture = $marque;
        $this->kilometrage = $kilometrage;
        $this->dateAjoutVoiture = $dateAjoutVoiture;
        $this->imageVoiture = $imageVoiture;
        $this->matriculeVoiture = $matriculeVoiture;
        $this->nomClient = $nomClient;
        $this->idClient = $idClient;
    }

    // Getters
    public function getId() { return $this->idVoiture; }
    public function getMarqueVoiture() { return $this->marqueVoiture; }
    public function getKilometrage() { return $this->kilometrage; }
    public function getMatriculeVoiture() { return $this->matriculeVoiture; }
    public function getDateAjoutVoiture() { return $this->dateAjoutVoiture; }
    public function getImageVoiture() { return $this->imageVoiture; }
    public function getNomClient() { return $this->nomClient; }
    public function getIdClient() { return $this->idClient; }

    // Setters
    public function setId($id) { $this->idVoiture = $id; }
    public function setMarqueVoiture($marque) { $this->marqueVoiture = $marque; }
    public function setKilometrage($kilometrage) { $this->kilometrage = $kilometrage; }
    public function setMatriculeVoiture($matricule) { $this->matriculeVoiture = $matricule; }
    public function setDateAjoutVoiture($dateAjout) { $this->dateAjoutVoiture = $dateAjout; }
    public function setImageVoiture($image) { $this->imageVoiture = $image; }
    public function setNomClient($nomClient) { $this->nomClient = $nomClient; }
    public function setIdClient($idClient) { $this->idClient = $idClient; }
}
?>