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

   
   public function getPaymentData($idEntretien, $clientId) {
    // Vérifie bien que idclient et matriculevoiture sont les noms dans ta BDD
    $sql = "SELECT e.*, f.montant_ttc, f.ref_facture, v.marqueV 
            FROM entre e
            INNER JOIN facture f ON e.id_entretien = f.entretien
            INNER JOIN vehicule v ON e.Matricule = v.matriculevoiture 
            WHERE e.id_entretien = ? 
              AND v.idclient = ? 
              AND f.mode_paiement = 'Carte Bancaire'
              AND e.deleted_at IS NULL";
              
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute([(int)$idEntretien, (int)$clientId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
    /**
     * Traite le paiement : valide la carte, puis met à jour le statut
     * de l'entretien en 'paye' et de la facture en 'Payée'.
     * @return array ['success'=>bool, 'error'=>string|null]
     */
    public function processPayment($idEntretien, $cardData, $clientId) {
    // 1. Valider la carte (Luhn, etc.)
    $val = $this->validateCardData($cardData);
    if (!$val['success']) return $val;

    // 2. Vérifier la propriété (Sécurité : est-ce que cet entretien appartient au client ?)
    $entretien = $this->getPaymentData($idEntretien, $clientId);
    if (!$entretien) {
        return ['success' => false, 'error' => "Entretien invalide ou accès refusé."];
    }

    try {
        $this->pdo->beginTransaction();

        // 3. Statut -> 'paye'
        $stmtEnt = $this->pdo->prepare(
            "UPDATE entre SET statut = 'paye' WHERE id_entretien = ? AND deleted_at IS NULL"
        );
        $stmtEnt->execute([$idEntretien]);

        // 4. Etat facture -> 'Payée'
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