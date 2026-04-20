<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Entretien.php';

class EntretienController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    private function validateEntretienData($data, $requireId = false) {
        $errors = [];

        // Validation ID Entretien (optionnelle pour création, requise pour mise à jour)
        if ($requireId) {
            if (!isset($data['id_entretien']) || $data['id_entretien'] === '') {
                $errors[] = "L'ID Entretien est obligatoire.";
            } elseif (!is_numeric($data['id_entretien']) || $data['id_entretien'] <= 0) {
                $errors[] = "L'ID Entretien doit être un nombre positif.";
            }
        }

        // Validation ID Voiture
        if (!isset($data['id_voiture']) || empty($data['id_voiture'])) {
            $errors[] = "L'ID Voiture est obligatoire.";
        } elseif (!is_numeric($data['id_voiture']) || $data['id_voiture'] <= 0) {
            $errors[] = "L'ID Voiture doit être un nombre positif.";
        }

        // Validation Date entretien
        if (!isset($data['date_entretien']) || empty($data['date_entretien'])) {
            $errors[] = "La date d'entretien est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_entretien'])) {
            $errors[] = "La date doit être au format YYYY-MM-DD.";
        } else {
            $dateParts = explode('-', $data['date_entretien']);
            if (!checkdate($dateParts[1], $dateParts[2], $dateParts[0])) {
                $errors[] = "La date n'est pas valide.";
            }
        }

        // Validation Kilométrage
        if (!isset($data['kilometrage']) || $data['kilometrage'] === '') {
            $errors[] = "Le kilométrage est obligatoire.";
        } elseif (!is_numeric($data['kilometrage']) || $data['kilometrage'] < 0) {
            $errors[] = "Le kilométrage doit être un nombre positif ou nul.";
        }

        // Validation Statut
        if (!isset($data['statut']) || empty($data['statut'])) {
            $errors[] = "Le statut est obligatoire.";
        }

        // Validation KM prochain (optionnel)
        if (isset($data['km_prochain']) && !empty($data['km_prochain'])) {
            if (!is_numeric($data['km_prochain']) || $data['km_prochain'] < 0) {
                $errors[] = "Le kilométrage prochain doit être un nombre positif.";
            }
        }

        return $errors;
    }

    public function addEntretien(Entretien $e) {
        // Validation des données
        $data = [
            'id_entretien' => $e->getId(),
            'id_voiture' => $e->getIdVoiture(),
            'date_entretien' => $e->getDate(),
            'kilometrage' => $e->getKm(),
            'statut' => $e->getStatut(),
            'km_prochain' => $e->getKmProchain()
        ];

        $errors = $this->validateEntretienData($data, false); // Validation pour ajout (ID non requis)
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $result = $e->save($this->pdo);
        return $result ? ['success' => true] : ['success' => false, 'errors' => ['Erreur lors de l\'enregistrement.']];
    }

    public function listEntretiens() {
        return Entretien::findAll($this->pdo);
    }

    public function getEntretien($id) {
        return Entretien::findById($this->pdo, $id);
    }

    public function updateEntretien(Entretien $e) {
        // Validation des données
        $data = [
            'id_entretien' => $e->getId(),
            'id_voiture' => $e->getIdVoiture(),
            'date_entretien' => $e->getDate(),
            'kilometrage' => $e->getKm(),
            'statut' => $e->getStatut(),
            'km_prochain' => $e->getKmProchain()
        ];

        $errors = $this->validateEntretienData($data, true); // Validation pour modification (ID requis)
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $result = $e->update($this->pdo);
        return $result ? ['success' => true] : ['success' => false, 'errors' => ['Erreur lors de la mise à jour.']];
    }

    public function deleteEntretien($id) {
        return Entretien::delete($this->pdo, $id);
    }
}
?>