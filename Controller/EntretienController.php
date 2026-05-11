<?php
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../Models/Entretien.php';

class EntretienController {

    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

   
    public static function fieldInvalid(array $errors, string $keyword): string {
        foreach ($errors as $e) {
            if (mb_stripos($e, $keyword) !== false) return 'is-invalid';
        }
        return '';
    }

   
    public static function paginationUrl(int $page, string $statut, string $date, string $sort): string {
        $params = array_filter(['page' => $page, 'statut' => $statut, 'date' => $date, 'sort' => $sort]);
        return 'listeentretiens.php?' . http_build_query($params);
    }

   

    private function validateEntretienData($data, $requireId = false) {
        $errors = [];
        if ($requireId) {
            if (!isset($data['id_entretien']) || trim((string)$data['id_entretien']) === '')
                $errors[] = "L'ID Entretien est obligatoire.";
            elseif (!ctype_digit((string)$data['id_entretien']) || (int)$data['id_entretien'] <= 0)
                $errors[] = "L'ID Entretien doit être un entier positif.";
        }
        if (!isset($data['Matricule']) || trim($data['Matricule']) === '')
            $errors[] = "Le matricule du véhicule est obligatoire.";
        if (!isset($data['date_entretien']) || trim($data['date_entretien']) === '') {
            $errors[] = "La date d'entretien est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_entretien'])) {
            $errors[] = "La date d'entretien doit être au format AAAA-MM-JJ.";
        } else {
            [$y, $m, $d] = explode('-', $data['date_entretien']);
            if (!checkdate((int)$m, (int)$d, (int)$y))
                $errors[] = "La date d'entretien n'est pas une date calendaire valide.";
        }
        if (!isset($data['kilometrage']) || trim((string)$data['kilometrage']) === '') {
            $errors[] = "Le kilométrage est obligatoire.";
        } elseif (!ctype_digit((string)$data['kilometrage'])) {
            $errors[] = "Le kilométrage doit être un nombre entier positif ou nul.";
        } elseif ((int)$data['kilometrage'] < 0) {
            $errors[] = "Le kilométrage ne peut pas être négatif.";
        }
        if (!isset($data['type_intervention']) || trim($data['type_intervention']) === '') {
            $errors[] = "Le type d'intervention est obligatoire.";
        } elseif (mb_strlen(trim($data['type_intervention'])) < 3) {
            $errors[] = "Le type d'intervention doit contenir au moins 3 caractères.";
        } elseif (mb_strlen(trim($data['type_intervention'])) > 150) {
            $errors[] = "Le type d'intervention ne doit pas dépasser 150 caractères.";
        }
        if (!empty($data['observations']) && mb_strlen(trim($data['observations'])) > 500) {
            $errors[] = "Les observations ne doivent pas dépasser 500 caractères.";
        }
        $statutsValides = ['planifie', 'en_cours', 'termine', 'annule'];
        if (!isset($data['statut']) || trim($data['statut']) === '') {
            $errors[] = "Le statut est obligatoire.";
        } elseif (!in_array($data['statut'], $statutsValides, true)) {
            $errors[] = "Le statut doit être : Planifié, En cours, Terminé ou Annulé.";
        }
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
        if (!empty($data['km_prochain'])) {
            if (!ctype_digit((string)$data['km_prochain'])) {
                $errors[] = "Le kilométrage de la prochaine visite doit être un entier positif.";
            } elseif ((int)$data['km_prochain'] <= 0) {
                $errors[] = "Le kilométrage de la prochaine visite doit être supérieur à 0.";
            } elseif (!empty($data['kilometrage']) && ctype_digit((string)$data['kilometrage'])
                      && (int)$data['km_prochain'] <= (int)$data['kilometrage']) {
                $errors[] = "Le kilométrage de la prochaine visite doit être supérieur au kilométrage actuel (" . $data['kilometrage'] . " km).";
            }
        }
        return $errors;
    }

    private function sanitizeOptional(&$data) {
        foreach (['prochaine_echeance', 'km_prochain'] as $field) {
            if (isset($data[$field]) && trim($data[$field]) === '') {
                $data[$field] = null;
            }
        }
    }

    

    public function addEntretien(Entretien $e) {
        $data = [
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

    public function listEntretiens()      { return Entretien::findAll($this->pdo); }
    public function listDeleted()         { return Entretien::findDeleted($this->pdo); }
    public function getEntretien($id)     { return Entretien::findById($this->pdo, $id); }
    public function getAllMatricules()    { return Entretien::getAllMatricules($this->pdo); }
    public function deleteEntretien($id) { return Entretien::delete($this->pdo, $id); }
    public function restoreEntretien($id){ return Entretien::restore($this->pdo, $id); }

    public function listFiltered($statut = null, $date = null, $sort = 'recent') {
        return Entretien::findFiltered($this->pdo, $statut, $date, $sort);
    }

    public function getStatsByStatut() {
        return Entretien::countByStatut($this->pdo);
    }

   
    public function getListeFront(int $page = 1, int $parPage = 6, int $clientId = 0): array
{
    // On utilise la méthode filtrée par client au lieu de findAll()
    $tous = Entretien::findByClient($this->pdo, $clientId);
    
    $total = count($tous);
    $totalPages = max(1, (int)ceil($total / $parPage));
    $page = max(1, min($page, $totalPages));
    $offset = ($page - 1) * $parPage;
    $list = array_slice($tous, $offset, $parPage);

    $facturesCarte = [];
    if (!empty($list)) {
        $ids = array_column($list, 'id_entretien');
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        // On garde la logique de vérification des paiements par carte
        $stmt = $this->pdo->prepare(
            "SELECT entretien FROM facture 
             WHERE entretien IN ($placeholders) 
               AND mode_paiement = 'Carte Bancaire' 
               AND deleted_at IS NULL"
        );
        $stmt->execute($ids);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $facturesCarte[$row['entretien']] = true;
        }
    }

    return compact('list', 'facturesCarte', 'page', 'totalPages', 'total');
}

    // ── Vue BACK : listeentretiens.php ─────────────────────────

    /**
     * Prépare TOUTES les données pour la vue back listeentretiens.php.
     *

     * @param  array $get   Typiquement $_GET
     * @param  int   $parPage Nombre de lignes par page (défaut 5)
     * @return array {
   
     * }
     */
    public function getListeBackData(array $get, int $parPage = 5): array
    {
        
        $filterStatut = isset($get['statut']) ? trim($get['statut']) : '';
        $filterDate   = isset($get['date'])   ? trim($get['date'])   : '';
        $filterSort   = isset($get['sort'])   ? trim($get['sort'])   : 'recent';

        // ── 2. Données filtrées ────────────────────────────────
        $allList    = $this->listFiltered(
            $filterStatut !== '' ? $filterStatut : null,
            $filterDate   !== '' ? $filterDate   : null,
            $filterSort
        );
        $stats      = $this->getStatsByStatut();
        $totalItems = count($allList);

        // ── 3. Pagination ──────────────────────────────────────
        $totalPages   = max(1, (int)ceil($totalItems / $parPage));
        $pageCourante = max(1, min((int)($get['page'] ?? 1), $totalPages));
        $offset       = ($pageCourante - 1) * $parPage;
        $list         = array_slice($allList, $offset, $parPage);

        // ── 4. Factures liées aux entretiens de la page ────────
        $facturesParEntretien = [];
        if (!empty($list)) {

            foreach ($list as $row) {
                $stmt = $this->pdo->prepare(
                    "SELECT * FROM facture
                     WHERE entretien = ? AND deleted_at IS NULL
                     ORDER BY date_emission DESC"
                );
                $stmt->execute([$row['id_entretien']]);
                $facturesParEntretien[$row['id_entretien']] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }

    
        $message = '';
        if (isset($get['success']))     $message = '<div class="alert alert-success alert-dismissible fade show">Entretien ajouté avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        elseif (isset($get['updated'])) $message = '<div class="alert alert-success alert-dismissible fade show">Entretien modifié avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        elseif (isset($get['deleted'])) $message = '<div class="alert alert-success alert-dismissible fade show">Entretien supprimé avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        elseif (isset($get['fact_ok'])) $message = '<div class="alert alert-success alert-dismissible fade show">Facture enregistrée avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';


        $badges = [
            'planifie' => 'text-warning-emphasis bg-warning-subtle',
            'en_cours' => 'text-info-emphasis bg-info-subtle',
            'termine'  => 'text-success-emphasis bg-success-subtle',
            'annule'   => 'text-danger-emphasis bg-danger-subtle',
        ];
        $labels = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine'  => 'Terminé',
            'annule'   => 'Annulé',
        ];

        return compact(
            'list', 'stats', 'filterStatut', 'filterDate', 'filterSort',
            'pageCourante', 'totalPages', 'totalItems',
            'facturesParEntretien', 'message',
            'badges', 'labels'
        );
    }

    // ── Vue BACK : historique_entretien.php ────────────────────

    /**
     * Prépare TOUTES les données pour la vue historique_entretien.php.
     *
     * Récupère les entretiens actifs/supprimés et les factures actives/supprimées,
     * construit le message flash, et fournit les helpers d'affichage.
     * La vue n'a plus qu'à afficher ce tableau.
     *
     * @param  array $get   Typiquement $_GET
     * @return array {
     *   actifsEnt, supprEnt, actifsFac, supprFac,
     *   message, badges, labels, etatClasses
     * }
     */
    public function getHistoriqueData(array $get): array
    {
        // ── 1. Données ─────────────────────────────────────────
        $actifsEnt = $this->listEntretiens();
        $supprEnt  = $this->listDeleted();

        // Factures : on interroge directement la BDD (FactureController
        // peut ne pas être chargé dans ce contexte).
        $stmtFacActifs = $this->pdo->query(
            "SELECT f.*, e.Matricule FROM facture f
             LEFT JOIN entre e ON e.id_entretien = f.entretien
             WHERE f.deleted_at IS NULL
             ORDER BY f.date_emission DESC"
        );
        $actifsFac = $stmtFacActifs->fetchAll(PDO::FETCH_ASSOC);

        $stmtFacSuppr = $this->pdo->query(
            "SELECT f.*, e.Matricule FROM facture f
             LEFT JOIN entre e ON e.id_entretien = f.entretien
             WHERE f.deleted_at IS NOT NULL
             ORDER BY f.deleted_at DESC"
        );
        $supprFac = $stmtFacSuppr->fetchAll(PDO::FETCH_ASSOC);

        // ── 2. Message flash ───────────────────────────────────
        $message = '';
        if (isset($get['restored_ent'])) {
            $message = '<div class="alert alert-success alert-dismissible fade show">Entretien restauré avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } elseif (isset($get['restored_fac'])) {
            $message = '<div class="alert alert-success alert-dismissible fade show">Facture restaurée avec succès. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        } elseif (isset($get['err_fac_parent'])) {
            $message = '<div class="alert alert-danger alert-dismissible fade show">Impossible de restaurer cette facture : l\'entretien parent est encore supprimé. <button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
        }

        // ── 3. Helpers d'affichage ─────────────────────────────
        $badges = [
            'planifie' => 'text-warning-emphasis bg-warning-subtle',
            'en_cours' => 'text-info-emphasis bg-info-subtle',
            'termine'  => 'text-success-emphasis bg-success-subtle',
            'annule'   => 'text-danger-emphasis bg-danger-subtle',
        ];
        $labels = [
            'planifie' => 'Planifié',
            'en_cours' => 'En cours',
            'termine'  => 'Terminé',
            'annule'   => 'Annulé',
        ];
        $etatClasses = [
            'Payée'      => 'text-success-emphasis bg-success-subtle',
            'En attente' => 'text-warning-emphasis bg-warning-subtle',
            'Annulée'    => 'text-danger-emphasis bg-danger-subtle',
        ];

        return compact(
            'actifsEnt', 'supprEnt', 'actifsFac', 'supprFac',
            'message', 'badges', 'labels', 'etatClasses'
        );
    }

    // ── Données pour modifier_entretien.php ────────────────────

    public function getModifierData(int $id): array
    {
        session_start();
        $errors    = $_SESSION['entretien_errors'] ?? [];
        $old       = $_SESSION['entretien_post']   ?? [];
        unset($_SESSION['entretien_errors'], $_SESSION['entretien_post']);

        $entretien = $this->getEntretien($id);
        if (!empty($old) && $entretien) {
            $entretien = array_merge($entretien, $old);
        }
        $matricules = $this->getAllMatricules();

        return compact('entretien', 'matricules', 'errors');
    }

    // ── Données pour ajouter_entretien.php ─────────────────────

    public function getAjouterData(): array
    {
        session_start();
        $errors = $_SESSION['entretien_errors'] ?? [];
        $old    = $_SESSION['entretien_post']   ?? [];
        unset($_SESSION['entretien_errors'], $_SESSION['entretien_post']);
        $matricules = $this->getAllMatricules();

        return compact('matricules', 'errors', 'old');
    }

    // ── Routeur d'actions ──────────────────────────────────────

    public function handleRequest($action, $data) {
        switch ($action) {
            case 'add':
                $e = new Entretien(
                    null,
                    $data['Matricule']          ?? '',
                    $data['date_entretien']     ?? '',
                    $data['kilometrage']        ?? '',
                    $data['type_intervention']  ?? '',
                    $data['observations']       ?? '',
                    $data['statut']             ?? '',
                    $data['prochaine_echeance'] ?? '',
                    $data['km_prochain']        ?? ''
                );
                $res = $this->addEntretien($e);
                $res['redirect'] = $res['success'] ? 'listeentretiens.php?success=1' : null;
                return $res;

            case 'update':
                $e = new Entretien(
                    $data['id_entretien']       ?? null,
                    $data['Matricule']          ?? '',
                    $data['date_entretien']     ?? '',
                    $data['kilometrage']        ?? '',
                    $data['type_intervention']  ?? '',
                    $data['observations']       ?? '',
                    $data['statut']             ?? '',
                    $data['prochaine_echeance'] ?? '',
                    $data['km_prochain']        ?? ''
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