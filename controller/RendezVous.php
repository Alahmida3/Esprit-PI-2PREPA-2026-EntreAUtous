<?php
if (!class_exists('RendezVousC')) {
    class RendezVousC {
        private $rendezvousList = [];
        
        public function __construct() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (!isset($_SESSION['rendezvousList'])) {
                $_SESSION['rendezvousList'] = [
                    new RendezVous("RDV-001", "VEH-001", "Peugeot 3008", "205 TUN 1234", "2026-04-20", "10:00", "Réparation", "confirmé", "Problème de freins avant"),
                    new RendezVous("RDV-002", "VEH-002", "Renault Clio", "156 TUN 7890", "2026-04-22", "14:30", "Entretien", "en attente", "Vidange et révision annuelle")
                ];
            }
            $this->rendezvousList = &$_SESSION['rendezvousList'];
        }
        
        public function getAll() {
            return $this->rendezvousList;
        }
        
        public function getRdvDataForJS() {
            $rdvData = [];
            foreach($this->rendezvousList as $rdv) {
                $rdvData[$rdv->getIdRdv()] = [
                    'id_rdv' => $rdv->getIdRdv(),
                    'id_vehicule' => $rdv->getIdVehicule(),
                    'marque_vehicule' => $rdv->getMarqueVehicule(),
                    'immatriculation' => $rdv->getImmatriculation(),
                    'date_rdv' => $rdv->getDateRdv(),
                    'heure_rdv' => $rdv->getHeureRdv(),
                    'type_service' => $rdv->getTypeService(),
                    'statut' => $rdv->getStatut(),
                    'description' => $rdv->getDescription()
                ];
            }
            return json_encode($rdvData);
        }
        
        public function afficher($rendezvous) {
            echo("
            <tr>
                <td class='ps-4 fw-bold text-primary'>".$rendezvous->getIdRdv()."</td>
                <td><span class='badge bg-info-subtle text-info'>".$rendezvous->getIdVehicule()."</span></td>
                <td>".$rendezvous->getMarqueVehicule()."</td>
                <td><span class='badge bg-secondary-subtle text-secondary'>".$rendezvous->getImmatriculation()."</span></td>
                <td>".$this->formatDate($rendezvous->getDateRdv())."</td>
                <td>".$rendezvous->getHeureRdv()."</td>
                <td><span class='badge bg-primary-subtle text-primary'>".$rendezvous->getTypeService()."</span></td>
                <td>".$this->getStatutBadge($rendezvous->getStatut())."</td>
                <td style='max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;'>".($rendezvous->getDescription() ?: '-')."</td>
                <td class='text-end pe-4'>
                    <div class='d-flex justify-content-end gap-2'>
                        <button class='btn btn-sm btn-slate' onclick='openEditModal(\"".$rendezvous->getIdRdv()."\")'>
                            <i class='bi bi-pencil-square me-1'></i> Modifier
                        </button>
                        <button class='btn btn-sm btn-orange' onclick='deleteRendezVous(\"".$rendezvous->getIdRdv()."\")'>
                            <i class='bi bi-trash me-1'></i> Supprimer
                        </button>
                    </div>
                </td>
             </tr>
            ");
        }
        
        public function add($rdv) {
            $this->rendezvousList[] = $rdv;
            $_SESSION['rendezvousList'] = $this->rendezvousList;
        }
        
        public function update($old_id, $newRdv) {
            foreach($this->rendezvousList as $key => $rdv) {
                if($rdv->getIdRdv() === $old_id) {
                    $this->rendezvousList[$key] = $newRdv;
                    break;
                }
            }
            $_SESSION['rendezvousList'] = $this->rendezvousList;
        }
        
        public function delete($id) {
            foreach($this->rendezvousList as $key => $rdv) {
                if($rdv->getIdRdv() === $id) {
                    unset($this->rendezvousList[$key]);
                    break;
                }
            }
            $this->rendezvousList = array_values($this->rendezvousList);
            $_SESSION['rendezvousList'] = $this->rendezvousList;
        }
        
        public function handleRequest() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePost();
            }
            if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
                $this->delete($_GET['id']);
                header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
                exit();
            }
        }
        
        private function handlePost() {
            $mode = $_POST['mode'];
            $id_rdv = $_POST['id_rdv'];
            $id_vehicule = $_POST['id_vehicule'];
            $marque_vehicule = $_POST['marque_vehicule'];
            $immatriculation = $_POST['immatriculation'];
            $date_rdv = $_POST['date_rdv'];
            $heure_rdv = $_POST['heure_rdv'];
            $type_service = $_POST['type_service'];
            $statut = $_POST['statut'];
            $description = $_POST['description'];

            $nouveauRdv = new RendezVous($id_rdv, $id_vehicule, $marque_vehicule, $immatriculation, $date_rdv, $heure_rdv, $type_service, $statut, $description);

            if ($mode === 'add') {
                $this->add($nouveauRdv);
            } elseif ($mode === 'edit') {
                $old_id = $_POST['old_id_rdv'];
                $this->update($old_id, $nouveauRdv);
            }
            
            header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
            exit();
        }
        
        private function formatDate($dateStr) {
            $date = new DateTime($dateStr);
            return $date->format('d/m/Y');
        }
        
        private function getStatutBadge($statut) {
            switch($statut) {
                case 'en attente':
                    return '<span class="badge" style="background-color: #f59e0b; color: #000;">En attente</span>';
                case 'confirmé':
                    return '<span class="badge" style="background-color: #10b981; color: #fff;">Confirmé</span>';
                case 'annulé':
                    return '<span class="badge" style="background-color: #ef4444; color: #fff;">Annulé</span>';
                default:
                    return '<span class="badge bg-secondary">'.$statut.'</span>';
            }
        }
    }
}
?>