<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../model/voitureC.php');
class Vehicule extends Voiture {
    public function __construct(
        $id = null,
        $marque = null,
        $kilometrage = null,
        $dateAjoutVoiture = null,
        $imageVoiture = null,
        $matriculeVoiture = null,
        $nomClient = null,
        $idClient = null
    ) {
        parent::__construct($id, $marque, $kilometrage, $dateAjoutVoiture, $imageVoiture, $matriculeVoiture, $nomClient, $idClient);
    }
}

class VoitureC {

    function afficherVehicule() {
        $sql = "SELECT * FROM vehicule ORDER BY idVehicule DESC";
        $db  = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function ajouterVehicule($vehicule) {
        $sql = "INSERT INTO vehicule (marqueV, matriculevoiture, date_ajoutV, kilometrageV, imageVoiture, nomClient, idClient)
                VALUES (:marque, :matricule, :dateA, :km, :img, :nomC, :idC)";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'marque'    => $vehicule->getMarqueVoiture(),
                'matricule' => $vehicule->getMatriculeVoiture(),
                'dateA'     => $vehicule->getDateAjoutVoiture(),
                'km'        => $vehicule->getKilometrage(),
                'img'       => $vehicule->getImageVoiture(),
                'nomC'      => $vehicule->getNomClient(),
                'idC'       => $vehicule->getIdClient()
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'ajout: ' . $e->getMessage());
        }
    }

    function supprimerVehicule($id) {
        $sql = "DELETE FROM vehicule WHERE idVehicule = :id";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    function modifierVehicule($vehicule, $id) {
        $sql = "UPDATE vehicule SET
                marqueV          = :marque,
                matriculevoiture = :matricule,
                date_ajoutV      = :dateA,
                kilometrageV     = :km,
                imageVoiture     = :img,
                nomClient        = :nomC,
                idClient         = :idC
                WHERE idVehicule = :id";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'marque'    => $vehicule->getMarqueVoiture(),
                'matricule' => $vehicule->getMatriculeVoiture(),
                'dateA'     => $vehicule->getDateAjoutVoiture(),
                'km'        => $vehicule->getKilometrage(),
                'img'       => $vehicule->getImageVoiture(),
                'nomC'      => $vehicule->getNomClient(),
                'idC'       => $vehicule->getIdClient(),
                'id'        => $id
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    function getVehiculeById($id) {
        $sql = "SELECT * FROM vehicule WHERE idVehicule = :id";
        $db  = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    // ===== MÉTHODES POUR LE FILTRAGE (ajoutées une seule fois) =====

    function filtrerVehiculeParClient($idClient) {
        $sql = "SELECT * FROM vehicule WHERE idClient = :idClient ORDER BY idVehicule DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['idClient' => $idClient]);
            return $query;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function filtrerVehiculeParMarque($marque) {
        $sql = "SELECT * FROM vehicule WHERE marqueV = :marque ORDER BY idVehicule DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['marque' => $marque]);
            return $query;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    function getMarquesUniques() {
        $sql = "SELECT DISTINCT marqueV FROM vehicule ORDER BY marqueV";
        $db = config::getConnexion();
        try {
            return $db->query($sql);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>