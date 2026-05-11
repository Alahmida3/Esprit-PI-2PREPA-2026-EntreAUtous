<?php
require_once __DIR__ . '/../models/db.php';
require_once __DIR__ . '/../Models/Facture.php';

class FactureController {
    private $pdo;

    public function __construct($pdo) { $this->pdo = $pdo; }

   
    public static function fieldInvalid(array $errors, string $keyword): string {
        foreach ($errors as $e) {
            if (mb_stripos($e, $keyword) !== false) return 'is-invalid';
        }
        return '';
    }

    /**
     * Construit l'URL de pagination de liste_factures.php
    
     */
    public static function pageUrl(int $p, ?int $eid, string $ref, string $date, string $sort): string {
        $params = array_filter(['page' => $p, 'entretien' => $eid, 'ref' => $ref, 'date' => $date, 'sort' => $sort]);
        return 'liste_factures.php?' . http_build_query($params);
    }

    
    private function validateFactureData($data, $requireId = false) {
        $errors = [];
        if ($requireId) {
            if (!isset($data['id_facture']) || $data['id_facture'] === '')
                $errors[] = "L'ID Facture est obligatoire.";
            elseif (!is_numeric($data['id_facture']) || $data['id_facture'] <= 0)
                $errors[] = "L'ID Facture doit être un nombre positif.";
        }
        if (!isset($data['ref_facture']) || trim($data['ref_facture']) === '')
            $errors[] = "La référence de la facture est obligatoire.";
        if (!isset($data['date_emission']) || empty($data['date_emission'])) {
            $errors[] = "La date d'émission est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_emission'])) {
            $errors[] = "La date doit être au format YYYY-MM-DD.";
        } else {
            $p = explode('-', $data['date_emission']);
            if (!checkdate($p[1], $p[2], $p[0])) $errors[] = "La date d'émission n'est pas valide.";
        }
        if (!isset($data['montant_ht']) || $data['montant_ht'] === '')
            $errors[] = "Le montant HT est obligatoire.";
        elseif (!is_numeric($data['montant_ht']) || $data['montant_ht'] < 0)
            $errors[] = "Le montant HT doit être un nombre positif ou nul.";
        $validTva = ['7', '13', '19'];
        if (!isset($data['taux_tva']) || !in_array((string)$data['taux_tva'], $validTva))
            $errors[] = "Le taux de TVA doit être 7%, 13% ou 19%.";
        if (!isset($data['montant_ttc']) || $data['montant_ttc'] === '')
            $errors[] = "Le montant TTC est obligatoire.";
        elseif (!is_numeric($data['montant_ttc']) || $data['montant_ttc'] < 0)
            $errors[] = "Le montant TTC doit être un nombre positif ou nul.";
        $validModes = ['Espèces', 'Carte Bancaire', 'Chèque', 'Virement'];
        if (!isset($data['mode_paiement']) || !in_array($data['mode_paiement'], $validModes))
            $errors[] = "Le mode de paiement est invalide.";
        $validEtats = ['En attente', 'Payée', 'Annulée'];
        if (!isset($data['etat_paiement']) || !in_array($data['etat_paiement'], $validEtats))
            $errors[] = "L'état du paiement est invalide.";
        if (!isset($data['entretien']) || !is_numeric($data['entretien']) || $data['entretien'] <= 0)
            $errors[] = "L'entretien associé est obligatoire.";
        return $errors;
    }

    

    public function addFacture(Facture $f) {
        $data = [
            'ref_facture'   => $f->getRef(),
            'date_emission' => $f->getDate(),
            'montant_ht'    => $f->getHt(),
            'taux_tva'      => $f->getTva(),
            'montant_ttc'   => $f->getTtc(),
            'mode_paiement' => $f->getMode(),
            'etat_paiement' => $f->getEtat(),
            'entretien'     => $f->getEntretien()
        ];
        $errors = $this->validateFactureData($data, false);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];
        $result = $f->save($this->pdo);
        return $result ? ['success' => true] : ['success' => false, 'errors' => ["Erreur lors de l'enregistrement."]];
    }

    public function updateFacture(Facture $f) {
        $data = [
            'id_facture'    => $f->getId(),
            'ref_facture'   => $f->getRef(),
            'date_emission' => $f->getDate(),
            'montant_ht'    => $f->getHt(),
            'taux_tva'      => $f->getTva(),
            'montant_ttc'   => $f->getTtc(),
            'mode_paiement' => $f->getMode(),
            'etat_paiement' => $f->getEtat(),
            'entretien'     => $f->getEntretien()
        ];
        $errors = $this->validateFactureData($data, true);
        if (!empty($errors)) return ['success' => false, 'errors' => $errors];
        $result = $f->save($this->pdo);
        return $result ? ['success' => true] : ['success' => false, 'errors' => ['Erreur lors de la mise à jour.']];
    }

    public function deleteFacture($id)       { return Facture::delete($this->pdo, $id); }
    public function restoreFacture($id)      { return Facture::restore($this->pdo, $id); }
    public function listByEntretien($id)     { return Facture::findByEntretien($this->pdo, $id); }
    public function getFacture($id)          { return Facture::findById($this->pdo, $id); }
    public function listAllFactures()        { return Facture::findAll($this->pdo); }
    public function listDeleted()            { return Facture::findDeleted($this->pdo); }
    public function listFiltered($eid,$d,$s) { return Facture::findFiltered($this->pdo,$eid,$d,$s); }

    // ── Vue FRONT : liste_factures.php ─────────────────────────
    /**
     * Prépare TOUTES les données pour la vue front liste_factures.php.
     * Gère tri, filtre par référence/date, et pagination côté serveur.
     *
     * @param int|null $entretienId  Filtre par entretien (optionnel)
     * @param string   $searchRef    Recherche sur la référence
     * @param string   $searchDate   Filtre date (YYYY-MM-DD)
     * @param string   $sort         'recent' | 'price-asc' | 'price-desc'
     * @param int      $page         Page courante
     * @param int      $parPage      Factures par page
     * @return array [factures, page, totalPages, total, searchRef, searchDate, sort, entretienId]
     */
   public function getListeFront(
    int    $clientId, // Ajout du paramètre obligatoire
    ?int   $entretienId = null,
    string $searchRef   = '',
    string $searchDate  = '',
    string $sort        = 'recent',
    int    $page        = 1,
    int    $parPage     = 4
): array {
    
    // On utilise la nouvelle méthode qui filtre par Client ET par les autres critères
    $toutes = Facture::findFiltreeFrontByClient(
        $this->pdo, 
        $clientId, 
        $searchRef, 
        $searchDate, 
        $sort
    );

    // 2. Pagination PHP
    $total      = count($toutes);
    $totalPages = max(1, (int)ceil($total / $parPage));
    $page       = max(1, min($page, $totalPages));
    $offset     = ($page - 1) * $parPage;
    $factures   = array_slice($toutes, $offset, $parPage);

    return compact('factures', 'page', 'totalPages', 'total', 'searchRef', 'searchDate', 'sort', 'entretienId');
}

   
    public function exportToPDF($id) {
        $factureData = $this->getFacture($id);
        if (!$factureData) {
            http_response_code(404);
            echo "Facture introuvable.";
            return;
        }
        $dompdfPath = __DIR__ . '/../libdompdf/dompdf/autoload.inc.php';
        if (!file_exists($dompdfPath)) {
            http_response_code(500);
            echo "Erreur : Dompdf introuvable à " . $dompdfPath;
            return;
        }
        require_once $dompdfPath;
        $f = $factureData;
        ob_start();
        require __DIR__ . '/../Views/front/facture_pdf.php';
        $html = ob_get_clean();
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream("Facture_" . ($f['ref_facture'] ?? $id) . ".pdf", ["Attachment" => true]);
    }
}
?>