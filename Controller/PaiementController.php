<?php



class PaiementController
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    // ── Validation des données carte (côté serveur) ───────────

    /**
     * Valide les données de carte bancaire reçues du formulaire.
     * @param  array  $data  Contient card_number, card_name, card_expiry, card_cvv
     * @return array  ['success'=>bool, 'error'=>string|null]
     */
    public function validateCardData(array $data): array
    {
        $number = preg_replace('/\s+/', '', $data['card_number'] ?? '');
        $name   = trim($data['card_name']   ?? '');
        $expiry = trim($data['card_expiry'] ?? '');
        $cvv    = trim($data['card_cvv']    ?? '');

        
        if (!preg_match('/^\d{13,19}$/', $number)) {
            return ['success' => false, 'error' => 'Numéro de carte invalide.'];
        }

        // Algorithme de Luhn
        if (!$this->luhnCheck($number)) {
            return ['success' => false, 'error' => 'Numéro de carte invalide (contrôle Luhn échoué).'];
        }

       
        if (empty($name) || !preg_match("/^[A-Za-zÀ-ÖØ-öø-ÿ '\-]{2,50}$/u", $name)) {
            return ['success' => false, 'error' => 'Nom du titulaire invalide.'];
        }

       
        if (!preg_match('/^(\d{2})\/(\d{2})$/', $expiry, $m)) {
            return ['success' => false, 'error' => "Date d'expiration invalide (format MM/AA attendu)."];
        }
        $mois = (int)$m[1];
        $an   = (int)('20' . $m[2]);
        if ($mois < 1 || $mois > 12) {
            return ['success' => false, 'error' => "Mois d'expiration invalide."];
        }
        $now = new DateTime();
        $exp = new DateTime("$an-$mois-01");
        $exp->modify('last day of this month');
        if ($exp < $now) {
            return ['success' => false, 'error' => 'Carte expirée.'];
        }

        
        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            return ['success' => false, 'error' => 'CVV invalide (3 ou 4 chiffres attendus).'];
        }

        return ['success' => true, 'error' => null];
    }

   
    public function getPaymentData(int $idEntretien): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.id_entretien, e.Matricule, e.type_intervention, e.statut,
                   v.marqueV,
                   f.ref_facture, f.montant_ttc, f.taux_tva, f.mode_paiement
            FROM entre e
            JOIN vehicule v ON e.Matricule = v.matriculevoiture
            INNER JOIN facture f ON f.entretien = e.id_entretien
                                AND f.mode_paiement = 'Carte Bancaire'
                                AND f.deleted_at IS NULL
            WHERE e.id_entretien = ? AND e.deleted_at IS NULL
            LIMIT 1
        ");
        $stmt->execute([$idEntretien]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || $row['statut'] !== 'termine') {
            return null;
        }

        return $row;
    }

    /**
     * Traite le paiement : valide la carte, puis met à jour le statut
     * de l'entretien en 'paye' et de la facture en 'Payée'.
     * @return array ['success'=>bool, 'error'=>string|null]
     */
    public function processPayment(int $idEntretien, array $cardData): array
    {
        // 1. Valider la carte
        $validation = $this->validateCardData($cardData);
        if (!$validation['success']) {
            return $validation;
        }

        // 2. Vérifier que l'entretien est bien dans l'état attendu
        $entretien = $this->getPaymentData($idEntretien);
        if (!$entretien) {
            return ['success' => false, 'error' => "Entretien invalide ou non éligible au paiement."];
        }

        try {
            $this->pdo->beginTransaction();

            // 3. Mettre à jour le statut de l'entretien → 'paye'
            $stmtEnt = $this->pdo->prepare(
                "UPDATE entre SET statut = 'paye' WHERE id_entretien = ? AND deleted_at IS NULL"
            );
            $stmtEnt->execute([$idEntretien]);

            // 4. Mettre à jour l'état de la facture → 'Payée'
            $stmtFac = $this->pdo->prepare(
                "UPDATE facture
                 SET etat_paiement = 'Payée'
                 WHERE entretien = ?
                   AND mode_paiement = 'Carte Bancaire'
                   AND deleted_at IS NULL"
            );
            $stmtFac->execute([$idEntretien]);

            $this->pdo->commit();
            return ['success' => true, 'error' => null];

        } catch (Exception $e) {
            $this->pdo->rollBack();
            return ['success' => false, 'error' => 'Erreur lors du traitement du paiement.'];
        }
    }

    // ── Algorithme de Luhn (vérification numéro de carte) ─────

    private function luhnCheck(string $number): bool
    {
        $sum    = 0;
        $alt    = false;
        $digits = str_split(strrev($number));
        foreach ($digits as $d) {
            $n = (int)$d;
            if ($alt) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
            $alt  = !$alt;
        }
        return $sum % 10 === 0;
    }
}
?>