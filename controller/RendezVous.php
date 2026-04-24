<?php
require_once(__DIR__ . '/../config.php');

class RendezVousC {
    public function ajouter($rdv) {
        $sql = "INSERT INTO rendezvous (dateRDV, heureRDV, type_serviceRDV, statutRDV, idVehicule, idclientRDV, descriptionRDV) 
                VALUES (:date, :heure, :typeService, :statut, :idVehicule, :idClient, :description)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'date'        => $rdv->getDateRDV(),
                'heure'       => $rdv->getHeureRDV(),
                'typeService' => $rdv->getTypeServiceRDV(),
                'statut'      => $rdv->getStatutRDV(),
                'idVehicule'  => $rdv->getIdVehicule(),
                'idClient'    => $rdv->getIdClientRDV(),
                'description' => $rdv->getDescriptionRDV()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }    

    public function modifier($id, $date, $heure, $typeService, $statut, $idVehicule, $idClient, $description) {
        $sql = "UPDATE rendezvous SET 
                    dateRDV = :date,
                    heureRDV = :heure,
                    type_serviceRDV = :typeService,
                    statutRDV = :statut,
                    idVehicule = :idVehicule,
                    idclientRDV = :idClient,
                    descriptionRDV = :description
                WHERE idRDV = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id'          => $id,
                'date'        => $date,
                'heure'       => $heure,
                'typeService' => $typeService,
                'statut'      => $statut,
                'idVehicule'  => $idVehicule,
                'idClient'    => $idClient,
                'description' => $description
            ]);
            return true;
        } catch (Exception $e) {
            die('Erreur modification: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer tous les rendez-vous
     */
    public function getAll() {
        $sql = "SELECT * FROM rendezvous ORDER BY idRDV DESC";
        $db = config::getConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupérer un rendez-vous par ID
     */
    public function getRdvById($id) {
        $sql = "SELECT * FROM rendezvous WHERE idRDV = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Supprimer un rendez-vous
     */
    public function delete($id) {
        $sql = "DELETE FROM rendezvous WHERE idRDV = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Afficher une ligne du tableau
     */
    public function afficher($rdv) {
    $r = (array)$rdv;
    $statut = ucfirst(strtolower($r['statutRDV'] ?? 'en attente'));

    echo '
    <tr>
        <td class="ps-4">
            <span class="text-info">#' . htmlspecialchars($r['idRDV']) . '</span>
        </td>
        <td>' . htmlspecialchars($r['dateRDV']) . '</td>
        <td>' . htmlspecialchars($r['heureRDV']) . '</td>
        <td>' . htmlspecialchars($r['type_serviceRDV']) . '</td>
        <td>' . $statut . '</td>
        <td><span class="text-white">VEH-' . htmlspecialchars($r['idVehicule']) . '</span></td>
        <td>' . htmlspecialchars($r['idclientRDV']) . '</td>
        <td>' . htmlspecialchars($r['descriptionRDV'] ?? 'Aucune description') . '</td>
        
       
        <td class="text-end pe-4">
            <a href="modifierRDV.php?id=' . $r['idRDV'] . '"
               class="btn btn-sm btn-outline-warning me-2">
                Modifier
            </a>
            <a href="?delete_id=' . $r['idRDV'] . '"
               class="btn btn-sm btn-outline-danger"
               onclick="return confirm(\'Voulez-vous vraiment supprimer le rendez-vous #' . $r['idRDV'] . ' ?\')">
                Supprimer RDV
            </a>
        </td>
    </tr>';
}
    
}
?>