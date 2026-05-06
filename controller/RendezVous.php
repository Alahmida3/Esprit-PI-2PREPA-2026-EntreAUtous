<?php
require_once(__DIR__ . '/../config.php');

class RendezVousC {
    public function ajouter($rdv) {
    $db = config::getConnexion();
    
    try {
        $db->beginTransaction();

        // ====================== RÉCUPÉRATION ID_GARAGE ======================
        $idGarage = null;

        // Option 1 : Essayer de récupérer depuis la table vehicule
        $stmt = $db->prepare("SELECT id_garage FROM vehicule WHERE idVehicule = ?");
        $stmt->execute([$rdv->getIdVehicule()]);
        $idGarage = $stmt->fetchColumn();

        // Option 2 : Si pas de garage dans vehicule → on peut le récupérer autrement (ex: via formulaire)
        if (!$idGarage && isset($_POST['id_garage']) && !empty($_POST['id_garage'])) {
            $idGarage = (int)$_POST['id_garage'];
        }

        if (!$idGarage) {
            throw new Exception("Impossible de déterminer le garage pour ce véhicule. Veuillez sélectionner un garage.");
        }

        // ====================== VÉRIFICATION CRÉNEAU ======================
        $check = $db->prepare("
            SELECT gr.id_garage_rdv 
            FROM garage_rdv gr
            JOIN rendezvous r ON r.idRDV = gr.idRDV
            WHERE gr.id_garage = ? 
              AND gr.dateRDV = ? 
              AND gr.heureRDV = ?
              AND r.statutRDV NOT IN ('annulé', 'cancelled')
        ");
        $check->execute([
            $idGarage, 
            $rdv->getDateRDV(), 
            $rdv->getHeureRDV()
        ]);

        if ($check->fetch()) {
            throw new Exception("Ce créneau est déjà réservé dans ce garage.");
        }

        // ====================== INSERTION RDV ======================
        $sql = "INSERT INTO rendezvous 
                (dateRDV, heureRDV, type_serviceRDV, statutRDV, 
                 idVehicule, idclientRDV, descriptionRDV) 
                VALUES 
                (:date, :heure, :typeService, :statut, :idVehicule, :idClient, :description)";

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

        $newIdRDV = $db->lastInsertId();

        // ====================== INSERTION DANS TABLE INTERMÉDIAIRE ======================
        $sqlJunction = "INSERT INTO garage_rdv 
                        (id_garage, idRDV, idVehicule, matricule_vehicule, dateRDV, heureRDV) 
                        VALUES (?, ?, ?, ?, ?, ?)";

        $db->prepare($sqlJunction)->execute([
            $idGarage,
            $newIdRDV,
            $rdv->getIdVehicule(),
            $rdv->getMatriculeVoiture() ?? '', 
            $rdv->getDateRDV(),
            $rdv->getHeureRDV()
        ]);

        $db->commit();

        return $newIdRDV;

    } catch (Exception $e) {
        $db->rollBack();
        die('Erreur lors de l\'ajout du rendez-vous: ' . $e->getMessage());
    }
}
    // Les autres méthodes restent inchangées pour le moment
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

    public function getAll() {
        $sql = "SELECT * FROM rendezvous ORDER BY idRDV DESC";
        $db = config::getConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

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