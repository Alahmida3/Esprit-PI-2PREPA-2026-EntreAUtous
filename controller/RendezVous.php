<?php
require_once(__DIR__ . '/../config.php');

class RendezVousC {

    /**
     * Récupérer tous les rendez-vous de la base de données
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
     * Récupérer un rendez-vous spécifique par son ID (pour la recherche)
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
     * Affiche une ligne de tableau (tr) avec les données du rendez-vous
     * Aligné sur 8 colonnes : ID, Date, Heure, Service, Statut, Véhicule, Client, Action
     */
    public function afficher($rdv) {
        // On s'assure que $rdv est un tableau
        $r = (array)$rdv;
        
        // Nettoyage du texte du statut
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
            <td class="text-end pe-4">
                <a href="?delete_id=' . $r['idRDV'] . '" 
                   class="btn btn-sm btn-outline-danger" 
                   onclick="return confirm(\'Voulez-vous vraiment supprimer le rendez-vous #' . $r['idRDV'] . ' ?\')">
                    Supprimer rendez vous
                </a>
            </td>
        </tr>';
    }

} // Fin de la classe
?>