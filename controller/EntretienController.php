<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Models/Entretien.php';

class EntretienController {

    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

    // ── Validation ─────────────────────────────────────────────
    private function validateEntretienData($data, $requireId = false) {
        $errors = [];

        // ── ID (update uniquement) ─────────────────────────────
        if ($requireId) {
            if (!isset($data['id_entretien']) || trim((string)$data['id_entretien']) === '')
                $errors[] = "L'ID Entretien est obligatoire.";
            elseif (!ctype_digit((string)$data['id_entretien']) || (int)$data['id_entretien'] <= 0)
                $errors[] = "L'ID Entretien doit être un entier positif.";
        }

        // ── Matricule ──────────────────────────────────────────
        if (!isset($data['Matricule']) || trim($data['Matricule']) === '')
            $errors[] = "Le matricule du véhicule est obligatoire.";

        // ── Date entretien (obligatoire) ───────────────────────
        if (!isset($data['date_entretien']) || trim($data['date_entretien']) === '') {
            $errors[] = "La date d'entretien est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_entretien'])) {
            $errors[] = "La date d'entretien doit être au format AAAA-MM-JJ.";
        } else {
            [$y, $m, $d] = explode('-', $data['date_entretien']);
            if (!checkdate((int)$m, (int)$d, (int)$y))
                $errors[] = "La date d'entretien n'est pas une date calendaire valide.";
        }

        // ── Kilométrage (obligatoire, entier >= 0) ─────────────
        if (!isset($data['kilometrage']) || trim((string)$data['kilometrage']) === '') {
            $errors[] = "Le kilométrage est obligatoire.";
        } elseif (!ctype_digit((string)$data['kilometrage'])) {
            $errors[] = "Le kilométrage doit être un nombre entier positif ou nul (chiffres uniquement).";
        } elseif ((int)$data['kilometrage'] < 0) {
            $errors[] = "Le kilométrage ne peut pas être négatif.";
        }

        // ── Type d'intervention (obligatoire, texte) ───────────
        if (!isset($data['type_intervention']) || trim($data['type_intervention']) === '') {
            $errors[] = "Le type d'intervention est obligatoire.";
        } elseif (mb_strlen(trim($data['type_intervention'])) < 3) {
            $errors[] = "Le type d'intervention doit contenir au moins 3 caractères.";
        } elseif (mb_strlen(trim($data['type_intervention'])) > 150) {
            $errors[] = "Le type d'intervention ne doit pas dépasser 150 caractères.";
        }

        // ── Observations (optionnel, longueur max) ─────────────
        if (!empty($data['observations']) && mb_strlen(trim($data['observations'])) > 500) {
            $errors[] = "Les observations ne doivent pas dépasser 500 caractères.";
        }

        // ── Statut (obligatoire, valeur contrôlée) ─────────────
        $statutsValides = ['planifie', 'en_cours', 'termine', 'annule'];
        if (!isset($data['statut']) || trim($data['statut']) === '') {
            $errors[] = "Le statut est obligatoire.";
        } elseif (!in_array($data['statut'], $statutsValides, true)) {
            $errors[] = "Le statut doit être : Planifié, En cours, Terminé ou Annulé.";
        }

        // ── Prochaine échéance (optionnelle, date valide) ──────
        if (!empty($data['prochaine_echeance'])) {
            if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['prochaine_echeance'])) {
                $errors[] = "La prochaine échéance doit être au format AAAA-MM-JJ.";
            } else {
                [$y, $m, $d] = explode('-', $data['prochaine_echeance']);
                if (!checkdate((int)$m, (int)$d, (int)$y))
                    $errors[] = "La prochaine échéance n'est pas une date calendaire valide.";
                elseif (isset($data['date_entretien']) && !empty($data['date_entretien'])
                        && $data['prochaine_echeance'] <= $data['date_entretien'])
                    $errors[] = "La prochaine échéance doit être postérieure à la date d'entretien.";
            }
        }

        // ── KM prochain (optionnel, entier > kilométrage actuel) ─
        if (!empty($data['km_prochain'])) {
            if (!ctype_digit((string)$data['km_prochain'])) {
                $errors[] = "Le kilométrage de la prochaine visite doit être un entier positif (chiffres uniquement).";
            } elseif ((int)$data['km_prochain'] <= 0) {
                $errors[] = "Le kilométrage de la prochaine visite doit être supérieur à 0.";
            } elseif (!empty($data['kilometrage']) && ctype_digit((string)$data['kilometrage'])
                      && (int)$data['km_prochain'] <= (int)$data['kilometrage']) {
                $errors[] = "Le kilométrage de la prochaine visite doit être supérieur au kilométrage actuel (" . $data['kilometrage'] . " km).";
            }
        }

        return $errors;
    }

    /**
     * Nettoie les champs optionnels : chaîne vide → null
     * pour éviter les erreurs MySQL sur les colonnes DATE/INT.
     */
    private function sanitizeOptional(&$data) {
        foreach (['prochaine_echeance', 'km_prochain'] as $field) {
            if (isset($data[$field]) && trim($data[$field]) === '') {
                $data[$field] = null;
            }
        }
    }

    // ── CRUD ───────────────────────────────────────────────────

    public function addEntretien(Entretien $e) {
        $data = [
            'id_entretien'       => $e->getId(),
            'Matricule'          => $e->getMatricule(),
            'date_entretien'     => $e->getDate(),
            'kilometrage'        => $e->getKm(),
            'type_intervention'  => $e->getType(),
            'observations'       => $e->getObs(),
            'statut'             => $e->getStatut(),
            'prochaine_echeance' => $e->getProchaine(),
            'km_prochain'        => $e->getKmProchain()
        ];
        $this->sanitizeOptional($data);
        $e->setProchaine($data['prochaine_echeance']);
        $e->setKmProchain($data['km_prochain']);
        $errors = $this->validateEntretienData($data, false);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];
        $result = $e->save($this->pdo);
        return $result ? ['success' => true] : ['success' => false, 'errors' => ["Erreur lors de l'enregistrement."]];
    }

    public function listEntretiens()        { return Entretien::findAll($this->pdo); }
    public function listDeleted()           { return Entretien::findDeleted($this->pdo); }
    public function getEntretien($id)       { return Entretien::findById($this->pdo, $id); }
    public function getAllMatricules()       { return Entretien::getAllMatricules($this->pdo); }

    public function updateEntretien(Entretien $e) {
        $data = [
            'id_entretien'       => $e->getId(),
            'Matricule'          => $e->getMatricule(),
            'date_entretien'     => $e->getDate(),
            'kilometrage'        => $e->getKm(),
            'type_intervention'  => $e->getType(),
            'observations'       => $e->getObs(),
            'statut'             => $e->getStatut(),
            'prochaine_echeance' => $e->getProchaine(),
            'km_prochain'        => $e->getKmProchain()
        ];
        $this->sanitizeOptional($data);
        $e->setProchaine($data['prochaine_echeance']);
        $e->setKmProchain($data['km_prochain']);
        $errors = $this->validateEntretienData($data, true);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];
        $result = $e->update($this->pdo);
        return $result ? ['success' => true] : ['success' => false, 'errors' => ['Erreur lors de la mise à jour.']];
    }

    public function deleteEntretien($id)    { return Entretien::delete($this->pdo, $id); }
    public function restoreEntretien($id)   { return Entretien::restore($this->pdo, $id); }

    // ── Filtrage / Stats ───────────────────────────────────────

    /**
     * Liste filtrée par statut et/ou date d'entretien.
     * @param string|null $statut  Valeur de statut (planifie, en_cours, termine, annule) ou null
     * @param string|null $date    Date au format YYYY-MM-DD ou null
     * @param string      $sort    'recent' | 'oldest'
     */
    public function listFiltered($statut = null, $date = null, $sort = 'recent') {
        return Entretien::findFiltered($this->pdo, $statut, $date, $sort);
    }

    /**
     * Retourne le nombre d'entretiens par statut (actifs uniquement).
     * @return array ['planifie' => int, 'en_cours' => int, 'termine' => int, 'annule' => int, 'total' => int]
     */
    public function getStatsByStatut() {
        return Entretien::countByStatut($this->pdo);
    }

    // ── Point d'entrée centralisé (appelé par entretien_action.php) ──

    /**
     * Traite toutes les actions CRUD déclenchées par les views.
     * Retourne un tableau ['success'=>bool, 'errors'=>[], 'redirect'=>string|null]
     */
    public function handleRequest($action, $data) {
        switch ($action) {

            case 'add':
                $e = new Entretien(
                    null,
                    $data['Matricule']           ?? '',
                    $data['date_entretien']      ?? '',
                    $data['kilometrage']         ?? '',
                    $data['type_intervention']   ?? '',
                    $data['observations']        ?? '',
                    $data['statut']              ?? '',
                    $data['prochaine_echeance']  ?? '',
                    $data['km_prochain']         ?? ''
                );
                $res = $this->addEntretien($e);
                $res['redirect'] = $res['success'] ? 'listeentretiens.php?success=1' : null;
                return $res;

            case 'update':
                $e = new Entretien(
                    $data['id_entretien']        ?? null,
                    $data['Matricule']           ?? '',
                    $data['date_entretien']      ?? '',
                    $data['kilometrage']         ?? '',
                    $data['type_intervention']   ?? '',
                    $data['observations']        ?? '',
                    $data['statut']              ?? '',
                    $data['prochaine_echeance']  ?? '',
                    $data['km_prochain']         ?? ''
                );
                $res = $this->updateEntretien($e);
                $res['redirect'] = $res['success'] ? 'listeentretiens.php?updated=1' : null;
                return $res;

            case 'delete':
                $ok = $this->deleteEntretien($data['id'] ?? 0);
                return ['success' => (bool)$ok, 'redirect' => 'listeentretiens.php?deleted=1'];

            case 'restore_ent':
                $ok = $this->restoreEntretien($data['id'] ?? 0);
                return ['success' => (bool)$ok, 'redirect' => 'historique_entretien.php?restored_ent=1'];

            default:
                return ['success' => false, 'errors' => ['Action inconnue.'], 'redirect' => null];
        }
    }
}
?>