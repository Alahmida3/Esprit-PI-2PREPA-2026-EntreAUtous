<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Entretien.php';

class EntretienController {

    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function addEntretien(Entretien $e) {
        return $e->save($this->pdo);
    }

    public function listEntretiens() {
        return Entretien::findAll($this->pdo);
    }

    public function getEntretien($id) {
        return Entretien::findById($this->pdo, $id);
    }

    public function updateEntretien(Entretien $e) {
        return $e->update($this->pdo);
    }

    public function deleteEntretien($id) {
        return Entretien::delete($this->pdo, $id);
    }
}
?>