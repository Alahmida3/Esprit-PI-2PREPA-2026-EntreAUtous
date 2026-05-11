<?php
ob_start();
require_once(__DIR__ . '/../../models/db.php');
require_once(__DIR__ . '/../../Controller/RendezVous.php');
require_once(__DIR__ . '/../../models/rendezvousC.php');

// Activer les erreurs PDO pour le débogage
$pdo = config::getConnexion();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$controller = new RendezVousC();
$isEdit = false;
$rdv_a_modifier = null;
$vehiculePreselectionne = null;

if (isset($_GET['vehicle']) && is_numeric($_GET['vehicle'])) {
    $vehiculePreselectionne = (int)$_GET['vehicle'];
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $isEdit = true;
    $rdv_a_modifier = $controller->getRdvById((int)$_GET['id']);
    if (!$rdv_a_modifier) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ Rendez-vous introuvable.']);
            exit();
        }
        header("Location: GestionVehicule.php");
        exit();
    }
    // Charger le garage associé à ce RDV depuis la table garage
    try {
        $pdo = config::getConnexion();
        $stmtG = $pdo->prepare("SELECT `id-garage` AS id_garage, nom_garage FROM garages WHERE idRDV = :idRDV LIMIT 1");
        $stmtG->execute(['idRDV' => (int)$_GET['id']]);
        $garageRow = $stmtG->fetch(PDO::FETCH_ASSOC);
        if ($garageRow) {
            $rdv_a_modifier['id_garage']  = $garageRow['id_garage'];
            $rdv_a_modifier['nom_garage'] = $garageRow['nom_garage'];
        }
    } catch (Exception $e) {
        // Colonne idRDV absente ou autre erreur — on continue sans garage présélectionné
    }
}

// ====================== ROUTE DÉDIÉE MODIFICATION AJAX ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'update_rdv') {
    ob_clean();
    header('Content-Type: application/json');
    try {
        $pdo = config::getConnexion();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $idRDV        = isset($_POST['idRDV']) && is_numeric($_POST['idRDV']) ? (int)$_POST['idRDV'] : 0;
        $id_client    = (int)($_POST['id_client']    ?? 0);
        $id_vehicule  = (int)($_POST['id_vehicule']  ?? 0);
        $date_rdv     = trim($_POST['date_rdv']      ?? '');
        $heure_rdv    = trim($_POST['heure_rdv']     ?? '');
        $type_service = trim($_POST['type_service']  ?? '');
        $description  = trim($_POST['description']   ?? '');
        $statut       = trim($_POST['statut']        ?? 'En attente');
        $id_garage    = (int)($_POST['id_garage']    ?? 0);

        if (!$idRDV)        throw new Exception("ID du rendez-vous manquant.");
        if (!$date_rdv)     throw new Exception("La date est obligatoire.");
        if (!$heure_rdv)    throw new Exception("L'heure est obligatoire.");
        if (!$type_service) throw new Exception("Le type de service est obligatoire.");

        // UPDATE sans id_garage d'abord, puis avec si la colonne existe
        try {
            $stmt = $pdo->prepare("UPDATE rendezvous SET
                idclientRDV     = :id_client,
                idVehicule      = :id_vehicule,
                dateRDV         = :date_rdv,
                heureRDV        = :heure_rdv,
                type_serviceRDV = :type_service,
                descriptionRDV  = :description,
                statutRDV       = :statut
                WHERE idRDV     = :idRDV");
            $stmt->execute([
                'id_client'    => $id_client,
                'id_vehicule'  => $id_vehicule,
                'date_rdv'     => $date_rdv,
                'heure_rdv'    => $heure_rdv,
                'type_service' => $type_service,
                'description'  => $description,
                'statut'       => $statut,
                'idRDV'        => $idRDV
            ]);
        } catch (PDOException $eG) {
            $stmt = $pdo->prepare("UPDATE rendezvous SET
                idclientRDV     = :id_client,
                idVehicule      = :id_vehicule,
                dateRDV         = :date_rdv,
                heureRDV        = :heure_rdv,
                type_serviceRDV = :type_service,
                descriptionRDV  = :description,
                statutRDV       = :statut
                WHERE idRDV     = :idRDV");
            $stmt->execute([
                'id_client'    => $id_client,
                'id_vehicule'  => $id_vehicule,
                'date_rdv'     => $date_rdv,
                'heure_rdv'    => $heure_rdv,
                'type_service' => $type_service,
                'description'  => $description,
                'statut'       => $statut,
                'idRDV'        => $idRDV
            ]);
        }

        echo json_encode(['success' => true, 'message' => 'Rendez-vous modifié avec succès !']);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit();
}

// ====================== ROUTE POUR SAUVEGARDER UN RDV PRÉDIT ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'save_predicted') {
    header('Content-Type: application/json');
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            throw new Exception("Données JSON invalides");
        }
        
        $id_client = (int)($input['id_client'] ?? 0);
        $id_vehicule = (int)($input['id_vehicule'] ?? 0);
        $date_rdv = trim($input['date_prochain'] ?? '');
        $heure_rdv = trim($input['heure_rdv'] ?? '10:00'); // Heure par défaut
        $type_service = trim($input['type_service'] ?? '');
        $description = trim($input['description'] ?? 'RDV recommandé automatiquement');
        $statut = 'En attente';
        
        if (!$id_client || !$id_vehicule || !$date_rdv || !$type_service) {
            throw new Exception("Tous les champs obligatoires doivent être remplis.");
        }
        
        // Vérifier si un RDV existe déjà à cette date
        $stmt = $pdo->prepare("SELECT idRDV FROM rendezvous WHERE idVehicule = :id_vehicule AND dateRDV = :date_rdv");
        $stmt->execute(['id_vehicule' => $id_vehicule, 'date_rdv' => $date_rdv]);
        
        if ($stmt->rowCount() > 0) {
            throw new Exception("Un rendez-vous existe déjà pour ce véhicule à cette date.");
        }
        
        // Insertion du RDV prédit
        $stmt = $pdo->prepare("INSERT INTO rendezvous (idclientRDV, idVehicule, dateRDV, heureRDV, type_serviceRDV, descriptionRDV, statutRDV)
            VALUES (:id_client, :id_vehicule, :date_rdv, :heure_rdv, :type_service, :description, :statut)");
        
        $stmt->execute([
            'id_client' => $id_client,
            'id_vehicule' => $id_vehicule,
            'date_rdv' => $date_rdv,
            'heure_rdv' => $heure_rdv,
            'type_service' => $type_service,
            'description' => $description,
            'statut' => $statut
        ]);
        
        $newId = $pdo->lastInsertId();
        
        echo json_encode([
            'success' => true,
            'message' => '✅ Rendez-vous prédit enregistré avec succès !',
            'idRDV' => $newId,
            'date' => $date_rdv
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => '❌ ' . $e->getMessage()
        ]);
    }
    exit();
}

// ====================== TRAITEMENT PRINCIPAL POST ======================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_GET['action'])) {
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    try {
        $pdo = config::getConnexion();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Récupération des données POST
        $id_client    = (int)($_POST['id_client'] ?? 0);
        $id_vehicule  = (int)($_POST['id_vehicule'] ?? 0);
        $date_rdv     = trim($_POST['date_rdv'] ?? '');
        $heure_rdv    = trim($_POST['heure_rdv'] ?? '');
        $type_service = trim($_POST['type_service'] ?? '');
        $description  = trim($_POST['description'] ?? '');
        $statut       = trim($_POST['statut'] ?? 'En attente');
        $id_garage    = (int)($_POST['id_garage'] ?? 0);
        $idRDV        = isset($_POST['idRDV']) && is_numeric($_POST['idRDV']) ? (int)$_POST['idRDV'] : null;

        // Log pour débogage (optionnel, à retirer en production)
        error_log("Données reçues - client:$id_client, vehicule:$id_vehicule, date:$date_rdv, heure:$heure_rdv, service:$type_service");

        // Validation des champs obligatoires
        $isEditPost = ($idRDV !== null && $idRDV > 0);
        if ($isEditPost) {
            // Mode modification : date, heure, service suffisent
            if (!$date_rdv || !$heure_rdv || !$type_service) {
                throw new Exception("Date, heure et type de service sont obligatoires.");
            }
        } else {
            // Mode ajout : id_client, id_vehicule, date, heure et service sont requis (description optionnelle)
            if (!$id_client || !$id_vehicule || !$date_rdv || !$heure_rdv || !$type_service) {
                throw new Exception("Tous les champs obligatoires doivent être remplis.");
            }
        }
        
        // Validation de la date (seulement en mode ajout)
        if (!$isEditPost && $date_rdv < date('Y-m-d')) {
            throw new Exception("La date ne peut pas être dans le passé.");
        }

        // Vérifier si un RDV existe déjà à cette date pour ce véhicule (sauf si c'est une modification)
        if (!$idRDV) {
            $stmt = $pdo->prepare("SELECT idRDV FROM rendezvous WHERE idVehicule = :id_vehicule AND dateRDV = :date_rdv");
            $stmt->execute(['id_vehicule' => $id_vehicule, 'date_rdv' => $date_rdv]);
            if ($stmt->rowCount() > 0) {
                throw new Exception("Un rendez-vous existe déjà pour ce véhicule à cette date.");
            }
        }

        // Enregistrement ou mise à jour
        if ($idRDV) {
            // Vérifier si la colonne id_garage existe dans rendezvous
            try {
                $stmt = $pdo->prepare("UPDATE rendezvous SET 
                    idclientRDV = :id_client, 
                    idVehicule = :id_vehicule, 
                    dateRDV = :date_rdv, 
                    heureRDV = :heure_rdv, 
                    type_serviceRDV = :type_service, 
                    descriptionRDV = :description, 
                    statutRDV = :statut
                    WHERE idRDV = :idRDV");
                $stmt->execute([
                    'id_client' => $id_client,
                    'id_vehicule' => $id_vehicule,
                    'date_rdv' => $date_rdv,
                    'heure_rdv' => $heure_rdv,
                    'type_service' => $type_service,
                    'description' => $description,
                    'statut' => $statut,
                    'idRDV' => $idRDV
                ]);
            } catch (PDOException $eGarage) {
                // Si id_garage n'existe pas dans rendezvous, on ignore ce champ
                $stmt = $pdo->prepare("UPDATE rendezvous SET 
                    idclientRDV = :id_client, 
                    idVehicule = :id_vehicule, 
                    dateRDV = :date_rdv, 
                    heureRDV = :heure_rdv, 
                    type_serviceRDV = :type_service, 
                    descriptionRDV = :description, 
                    statutRDV = :statut 
                    WHERE idRDV = :idRDV");
                $stmt->execute([
                    'id_client' => $id_client,
                    'id_vehicule' => $id_vehicule,
                    'date_rdv' => $date_rdv,
                    'heure_rdv' => $heure_rdv,
                    'type_service' => $type_service,
                    'description' => $description,
                    'statut' => $statut,
                    'idRDV' => $idRDV
                ]);
            }
            $successMsg = "Rendez-vous modifié avec succès !";
            $isNewRdv = false;
        } else {
            $stmt = $pdo->prepare("INSERT INTO rendezvous (idclientRDV, idVehicule, dateRDV, heureRDV, type_serviceRDV, descriptionRDV, statutRDV)
                VALUES (:id_client, :id_vehicule, :date_rdv, :heure_rdv, :type_service, :description, :statut)");
            
            $stmt->execute([
                'id_client' => $id_client,
                'id_vehicule' => $id_vehicule,
                'date_rdv' => $date_rdv,
                'heure_rdv' => $heure_rdv,
                'type_service' => $type_service,
                'description' => $description,
                'statut' => $statut
            ]);
            $successMsg = "Rendez-vous ajouté avec succès !";
            $isNewRdv = true;
        }

        // Récupération des infos véhicule
        $infoVeh = null;
        try {
            $stmtVeh = $pdo->prepare("SELECT marqueV, kilometrageV, matriculevoiture FROM vehicule WHERE idVehicule = :id");
            $stmtVeh->execute(['id' => $id_vehicule]);
            $infoVeh = $stmtVeh->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur récupération véhicule: " . $e->getMessage());
        }

        // Récupération du nom du client
        $nomClient = '';
        try {
            $stmtCli = $pdo->prepare("SELECT CONCAT(nom, ' ', prenom) AS nomclient FROM client WHERE id_client = :id");
            $stmtCli->execute(['id' => $id_client]);
            $nomClient = $stmtCli->fetchColumn() ?: '';
        } catch (Exception $e) {
            error_log("Erreur récupération client: " . $e->getMessage());
        }

        // ====================== ESTIMATION ET PRÉDICTION ======================
        // Les cURL internes deadlockent sur XAMPP (Apache monothread).
        // On supprime le cURL : le JS appellera directement predict_next_rdv.php et Estimertemps.php.
        $estimation = null;
        $prediction = null;

        if ($isAjax) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => $successMsg,
                'type_service' => $type_service,
                'marque' => $infoVeh['marqueV'] ?? '',
                'matricule' => $infoVeh['matriculevoiture'] ?? '',
                'kilometrage' => (int)($infoVeh['kilometrageV'] ?? 0),
                'nom_client' => $nomClient,
                'date_rdv' => $date_rdv,
                'heure_rdv' => $heure_rdv,
                'description' => $description,
                'estimation' => $estimation,
                'prediction' => $prediction,
                'id_client' => $id_client,
                'id_vehicule' => $id_vehicule
            ]);
            exit();
        }

        header("Location: GestionVehicule.php?success=" . urlencode($successMsg));
        exit();

    } catch (Exception $e) {
        error_log("Erreur traitement RDV: " . $e->getMessage());
        
        if ($isAjax) {
            ob_clean();
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => '❌ ' . $e->getMessage()]);
            exit();
        }
        $errorMsg = $e->getMessage();
    }
}

// ====================== DONNÉES POUR LE RENDER ======================
try {
    $pdo = config::getConnexion();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $clients   = $pdo->query("SELECT id_client, CONCAT(nom, ' ', prenom) AS nomclient FROM client ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
    $vehicules = $pdo->query("SELECT idVehicule, marqueV, matriculevoiture, kilometrageV FROM vehicule ORDER BY marqueV")->fetchAll(PDO::FETCH_ASSOC);
    $services  = $pdo->query("SELECT nom_service FROM services ORDER BY nom_service")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Erreur récupération données: " . $e->getMessage());
    $clients = $vehicules = $services = [];
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// CSS complet (identique à votre version originale)
$sharedCSS = '
<style>
.rdv-form-wrap { transition: opacity .35s ease, transform .35s ease; }
.rdv-form-wrap.fade-out { opacity:0; transform:translateY(-14px); pointer-events:none; }

.rdv-confirm-card { display:none; opacity:0; transform:translateY(20px);
    transition: opacity .45s ease .1s, transform .45s ease .1s; }
.rdv-confirm-card.rdv-visible { display:block; opacity:1; transform:translateY(0); }

.cc-inner {
    background: linear-gradient(135deg,#1a1a2e 0%,#16213e 60%,#0f3460 100%);
    border-radius:16px; overflow:hidden; color:#fff;
}
.cc-top { background:#ffc107; padding:20px 26px 16px; }
.cc-badge {
    display:inline-flex; align-items:center; gap:8px;
    background:rgba(0,0,0,.12); border-radius:50px;
    padding:4px 14px; font-size:.75rem; font-weight:700;
    color:#333; text-transform:uppercase; letter-spacing:.08em;
}
.cc-top h4 { margin:8px 0 0; font-weight:800; color:#212529; font-size:1.05rem; }
.cc-body { padding:20px 24px; }

.est-bubble {
    background:rgba(255,193,7,.12); border:2px solid rgba(255,193,7,.3);
    border-radius:14px; padding:20px; text-align:center; margin-bottom:18px;
    position:relative; overflow:hidden;
}
.est-bubble::before {
    content:""; position:absolute; inset:0;
    background:radial-gradient(circle at 50% 0%,rgba(255,193,7,.18) 0%,transparent 70%);
}
.est-big { font-size:3.8rem; font-weight:900; color:#ffc107; line-height:1; display:block; }
.est-lbl { font-size:.75rem; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.1em; }
.est-rng { font-size:.9rem; color:rgba(255,255,255,.72); margin-top:5px; }
.est-bdg { display:inline-block; font-size:.68rem; font-weight:700; text-transform:uppercase;
    letter-spacing:.06em; padding:2px 10px; border-radius:50px; margin-top:8px; }
.bdg-s { background:rgba(40,167,69,.22);  color:#6fcf97; border:1px solid #6fcf9755; }
.bdg-m { background:rgba(255,193,7,.22);  color:#ffc107; border:1px solid #ffc10755; }
.bdg-c { background:rgba(220,53,69,.22);  color:#f87171; border:1px solid #f8717155; }

.cc-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
.cc-item { background:rgba(255,255,255,.06); border-radius:10px; padding:10px 13px;
    border-left:3px solid rgba(255,193,7,.5); }
.cc-item .l { font-size:.66rem; color:rgba(255,255,255,.42); text-transform:uppercase;
    letter-spacing:.08em; display:block; margin-bottom:2px; }
.cc-item .v { font-size:.88rem; font-weight:600; color:#fff; }

.cc-expl { background:rgba(255,255,255,.05); border-radius:10px; padding:11px 13px;
    font-size:.83rem; color:rgba(255,255,255,.68); border-left:3px solid #ffc107; margin-bottom:10px; }
.cc-cons { font-size:.8rem; color:rgba(255,193,7,.8); display:flex; align-items:flex-start; gap:8px; }
.cc-src  { font-size:.68rem; color:rgba(255,255,255,.32); text-align:right; margin-top:10px; }

.cc-bar-wrap { background:rgba(255,255,255,.08); border-radius:4px; height:4px; margin-top:14px; overflow:hidden; }
.cc-bar      { height:100%; background:#ffc107; width:100%; }
.cc-cdown    { font-size:.73rem; color:rgba(255,255,255,.38); text-align:center; margin-top:5px; }

.btn-retour {
    background:rgba(255,255,255,.09); border:1px solid rgba(255,255,255,.18); color:#fff;
    border-radius:8px; padding:9px 22px; font-size:.87rem; font-weight:600;
    cursor:pointer; transition:background .2s; width:100%; margin-top:8px;
    text-align:center; display:block;
}
.btn-retour:hover { background:rgba(255,255,255,.17); }

.est-spinner { text-align:center; padding:24px 0; }
.est-spinner .spinner-border { color:#ffc107; width:2.4rem; height:2.4rem; }
.est-spinner p { color:rgba(255,255,255,.55); margin-top:10px; font-size:.88rem; }

/* Carte de prédiction */
.prediction-card {
    position: fixed;
    bottom: 30px;
    right: 30px;
    z-index: 1100;
    max-width: 420px;
    animation: slideInUp 0.4s ease-out;
    box-shadow: 0 20px 35px -8px rgba(0,0,0,0.2);
    border-radius: 20px;
    overflow: hidden;
}

@keyframes slideInUp {
    from { opacity: 0; transform: translateY(100px); }
    to { opacity: 1; transform: translateY(0); }
}

.prediction-card .card-header {
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    color: #ffc107;
    padding: 16px 20px;
    font-weight: bold;
    border-bottom: 2px solid #ffc107;
}

.prediction-card .card-body {
    background: linear-gradient(135deg, #1e1e3a 0%, #1a1a2e 100%);
    color: white;
    padding: 20px;
}

.prediction-card .badge-priority {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.7rem;
    font-weight: bold;
    margin-bottom: 12px;
}

.prediction-badge-critical { background: #dc3545; color: white; }
.prediction-badge-haute { background: #ff9800; color: #1a1a2e; }
.prediction-badge-moyenne { background: #ffc107; color: #1a1a2e; }
.prediction-badge-basse { background: #28a745; color: white; }

.prediction-date {
    font-size: 1rem;
    font-weight: bold;
    color: #ffc107;
    margin: 8px 0;
    padding: 8px 12px;
    background: rgba(255,193,7,0.1);
    border-radius: 12px;
    text-align: center;
}

.prediction-km {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 10px;
    margin-top: 12px;
    text-align: center;
    font-size: 0.85rem;
}

.prediction-btn-confirm {
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    color: white;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: bold;
    transition: transform 0.2s;
}

.prediction-btn-confirm:hover { transform: translateY(-2px); }

.prediction-btn-cancel {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: #ddd;
    padding: 10px 20px;
    border-radius: 12px;
    font-weight: bold;
}

.prediction-btn-cancel:hover { background: rgba(255,255,255,0.2); color: white; }

/* Style pour le champ heure dans le modal de prédiction */
.prediction-time {
    background: rgba(255,255,255,0.05);
    border-radius: 10px;
    padding: 10px;
    margin: 10px 0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.prediction-time input {
    background: rgba(255,255,255,0.1);
    border: 1px solid rgba(255,255,255,0.2);
    color: white;
    padding: 6px 12px;
    border-radius: 8px;
    width: auto;
}
</style>';

echo $sharedCSS;

// Fonction renderForm
function renderForm($clients, $vehicules, $services, $isEdit, $rdv_a_modifier, $vehiculePreselectionne, $prefix) {
    $idRDVVal = $isEdit ? (int)($rdv_a_modifier['idRDV'] ?? 0) : '';
    $dateVal  = $isEdit ? htmlspecialchars($rdv_a_modifier['dateRDV'] ?? '') : '';
    $heureVal = $isEdit ? htmlspecialchars(substr($rdv_a_modifier['heureRDV'] ?? '', 0, 5)) : '';
    $descVal  = $isEdit ? htmlspecialchars($rdv_a_modifier['descriptionRDV'] ?? '') : '';
    $statut   = $isEdit ? htmlspecialchars($rdv_a_modifier['statutRDV'] ?? 'En attente') : 'En attente';
    $idClientSel  = $isEdit ? ($rdv_a_modifier['idclientRDV'] ?? 0) : 0;
    $idVehSel     = $isEdit ? ($rdv_a_modifier['idVehicule'] ?? 0) : ($vehiculePreselectionne ?? 0);
    $serviceSel   = $isEdit ? ($rdv_a_modifier['type_serviceRDV'] ?? '') : '';

    // Récupérer nom client et info véhicule pour affichage readonly
    $nomClientDisplay = '';
    $vehiculeDisplay  = '';
    if ($isEdit) {
        foreach ($clients as $c) {
            if ($c['id_client'] == $idClientSel) { $nomClientDisplay = $c['nomclient']; break; }
        }
        foreach ($vehicules as $v) {
            if ($v['idVehicule'] == $idVehSel) {
                $vehiculeDisplay = htmlspecialchars($v['matriculevoiture'] ?? '') . ' — ' . htmlspecialchars($v['marqueV'] ?? '');
                break;
            }
        }
    }

    // Récupérer les garages
    $garages = [];
    try {
        $pdo2 = config::getConnexion();
        $garages = $pdo2->query("SELECT `id-garage` AS id_garage, nom_garage FROM garages ORDER BY nom_garage")->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) { /* table garage absente */ }

    $idGarageSel = $isEdit ? (int)($rdv_a_modifier['id_garage'] ?? 0) : 0;

    // ===== MODE MODIFICATION : style image 2 (planifier) =====
    if ($isEdit):
?>
<style>
.rdv-edit-wrap { font-family: inherit; }
.rdv-edit-wrap .rdv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem; }
.rdv-edit-wrap .rdv-grid.full { grid-template-columns: 1fr; }
.rdv-edit-wrap .rdv-group label { display: block; font-weight: 700; font-size: 0.95rem; margin-bottom: 0.4rem; color: #222; }
.rdv-edit-wrap .rdv-readonly-field {
    display: flex; align-items: center; gap: 0.5rem;
    background: #f5f5f5; border: 1px solid #e0e0e0;
    border-radius: 8px; padding: 0.6rem 0.9rem;
    font-size: 0.95rem; color: #444;
}
.rdv-edit-wrap .rdv-readonly-field .ico { font-size: 1.1rem; }
.rdv-edit-wrap .rdv-group input,
.rdv-edit-wrap .rdv-group select,
.rdv-edit-wrap .rdv-group textarea {
    width: 100%; border: 1.5px solid #ddd; border-radius: 8px;
    padding: 0.6rem 0.9rem; font-size: 0.95rem; color: #333;
    background: #fff; outline: none; transition: border-color .2s;
}
.rdv-edit-wrap .rdv-group input:focus,
.rdv-edit-wrap .rdv-group select:focus,
.rdv-edit-wrap .rdv-group textarea:focus {
    border-color: #FFC107; box-shadow: 0 0 0 3px rgba(255,193,7,.15);
}
.rdv-edit-wrap .rdv-group select.garage-sel { border: 2px solid #FFC107; }
.rdv-edit-wrap .rdv-statut-box {
    background: #f5f5f5; border: 1px solid #e0e0e0;
    border-radius: 8px; padding: 0.6rem 0.9rem;
    font-size: 0.95rem; color: #666;
}
@media(max-width:576px){ .rdv-edit-wrap .rdv-grid { grid-template-columns:1fr; } }
</style>

<div class="rdv-edit-wrap">
<form id="<?= $prefix ?>_form" novalidate>
    <input type="hidden" name="idRDV"       value="<?= $idRDVVal ?>">
    <input type="hidden" name="id_client"   value="<?= (int)$idClientSel ?>">
    <input type="hidden" name="id_vehicule" value="<?= (int)$idVehSel ?>">
    <input type="hidden" name="statut"      value="<?= $statut ?>">

    <!-- Ligne 1 : Client | Garage -->
    <div class="rdv-grid">
        <div class="rdv-group">
            <label>Client</label>
            <div class="rdv-readonly-field"><span class="ico">👤</span><span><?= htmlspecialchars($nomClientDisplay) ?></span></div>
        </div>
        <div class="rdv-group">
            <label>Garage</label>
            <select name="id_garage" class="garage-sel">
                <option value="">-- Choisir le garage --</option>
                <?php foreach ($garages as $g): ?>
                    <option value="<?= (int)$g['id_garage'] ?>"
                        <?= ((int)$idGarageSel > 0 && (int)$idGarageSel == (int)$g['id_garage']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($g['nom_garage']) ?>
                    </option>
                <?php endforeach; ?>
                <?php if (empty($garages) && !empty($rdv_a_modifier['id_garage'])): ?>
                    <option value="<?= (int)$rdv_a_modifier['id_garage'] ?>" selected>
                        <?= htmlspecialchars($rdv_a_modifier['nom_garage'] ?? 'Garage #'.(int)$rdv_a_modifier['id_garage']) ?>
                    </option>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <!-- Ligne 2 : Véhicule | Date -->
    <div class="rdv-grid">
        <div class="rdv-group">
            <label>Véhicule</label>
            <div class="rdv-readonly-field"><span class="ico">🚗</span><span><?= $vehiculeDisplay ?></span></div>
        </div>
        <div class="rdv-group">
            <label>Date du RDV</label>
            <input type="date" name="date_rdv" id="<?= $prefix ?>_date" value="<?= $dateVal ?>" required>
        </div>
    </div>

    <!-- Ligne 3 : Heure | Type de service -->
    <div class="rdv-grid">
        <div class="rdv-group">
            <label>Heure du RDV</label>
            <input type="time" name="heure_rdv" id="<?= $prefix ?>_heure" value="<?= $heureVal ?>" required>
        </div>
        <div class="rdv-group">
            <label>Type de Service</label>
            <select name="type_service" id="<?= $prefix ?>_service" required>
                <option value="">Choisir un service...</option>
                <?php foreach ($services as $s):
                    $sel2 = ($s['nom_service'] == $serviceSel) ? ' selected' : ''; ?>
                    <option value="<?= htmlspecialchars($s['nom_service']) ?>"<?= $sel2 ?>><?= htmlspecialchars($s['nom_service']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Ligne 4 : Statut (readonly) -->
    <div class="rdv-grid full">
        <div class="rdv-group">
            <label>Statut</label>
            <div class="rdv-statut-box"><?= $statut ?></div>
        </div>
    </div>

    <!-- Ligne 5 : Description -->
    <div class="rdv-grid full">
        <div class="rdv-group">
            <label>Description</label>
            <textarea name="description" id="<?= $prefix ?>_desc" rows="3" placeholder="Ajouter une note..."><?= $descVal ?></textarea>
        </div>
    </div>

</form>
</div>
<?php
    // ===== MODE AJOUT : style original =====
    else:
    echo '<form id="'.$prefix.'_form" novalidate>';
    echo '
    <div class="row g-3">
      <div class="col-12">
        <label class="form-label fw-bold">Client</label>
        <select name="id_client" id="'.$prefix.'_client" class="form-select" required>
          <option value="">Choisir un client...</option>';
    foreach ($clients as $c) {
        $sel = ($c['id_client'] == $idClientSel) ? ' selected' : '';
        echo '<option value="'.(int)$c['id_client'].'" data-nom="'.htmlspecialchars($c['nomclient']).'"'.$sel.'>'.(int)$c['id_client'].' — '.htmlspecialchars($c['nomclient']).'</option>';
    }
    echo '</select></div>
      <div class="col-12">
        <label class="form-label fw-bold">Nom du Client</label>
        <input type="text" id="'.$prefix.'_nom" class="form-control bg-light fw-semibold" readonly>
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Véhicule</label>
        <select name="id_vehicule" id="'.$prefix.'_veh" class="form-select" required>
          <option value="">Choisir un véhicule...</option>';
    foreach ($vehicules as $v) {
        $sel = ($v['idVehicule'] == $idVehSel) ? ' selected' : '';
        echo '<option value="'.(int)$v['idVehicule'].'" data-marque="'.htmlspecialchars($v['marqueV'] ?? '').'" data-km="'.(int)($v['kilometrageV'] ?? 0).'" data-mat="'.htmlspecialchars($v['matriculevoiture'] ?? '').'"'.$sel.'>'.htmlspecialchars($v['marqueV']).' — '.htmlspecialchars($v['matriculevoiture']).'</option>';
    }
    echo '</select></div>
      <div class="col-6">
        <label class="form-label fw-bold">Date du RDV</label>
        <input type="date" name="date_rdv" id="'.$prefix.'_date" class="form-control" value="'.$dateVal.'" required>
      </div>
      <div class="col-6">
        <label class="form-label fw-bold">Heure du RDV</label>
        <input type="time" name="heure_rdv" id="'.$prefix.'_heure" class="form-control" value="'.$heureVal.'" required>
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Type de Service</label>
        <select name="type_service" id="'.$prefix.'_service" class="form-select" required>
          <option value="">Choisir un service...</option>';
    foreach ($services as $s) {
        $sel = ($s['nom_service'] == $serviceSel) ? ' selected' : '';
        echo '<option value="'.htmlspecialchars($s['nom_service']).'"'.$sel.'>'.htmlspecialchars($s['nom_service']).'</option>';
    }
    echo '</select></div>
      <div class="col-12">
        <label class="form-label fw-bold">Statut</label>
        <input type="text" class="form-control bg-light" value="'.$statut.'" disabled>
        <input type="hidden" name="statut" value="'.$statut.'">
      </div>
      <div class="col-12">
        <label class="form-label fw-bold">Description / Notes</label>
        <textarea name="description" id="'.$prefix.'_desc" class="form-control" rows="3" placeholder="Détails supplémentaires..." required>'.$descVal.'</textarea>
      </div>
    </div>
    </form>';
    endif;
}

// Fonction renderConfirmCard
function renderConfirmCard($prefix) {
    echo '
    <div id="'.$prefix.'_card" class="rdv-confirm-card">
      <div class="cc-inner">
        <div class="cc-top">
          <span class="cc-badge"><i class="fas fa-check-circle"></i> Enregistré !</span>
          <h4 id="'.$prefix.'_msg">Rendez-vous ajouté avec succès !</h4>
        </div>
        <div class="cc-body">
          <div id="'.$prefix.'_spinner" class="est-spinner">
            <div class="spinner-border" role="status"></div>
            <p>Calcul du temps d\'intervention…</p>
          </div>
          <div id="'.$prefix.'_result" style="display:none;">
            <div class="est-bubble">
              <span class="est-big" id="'.$prefix.'_min">—</span>
              <span class="est-lbl">minutes estimées</span>
              <div class="est-rng" id="'.$prefix.'_rng"></div>
              <span class="est-bdg" id="'.$prefix.'_bdg"></span>
            </div>
            <div class="cc-grid">
              <div class="cc-item"><span class="l">Service</span><span class="v" id="'.$prefix.'_svc"></span></div>
              <div class="cc-item"><span class="l">Véhicule</span><span class="v" id="'.$prefix.'_vehv"></span></div>
              <div class="cc-item"><span class="l">Client</span><span class="v" id="'.$prefix.'_cli"></span></div>
              <div class="cc-item"><span class="l">Date &amp; Heure</span><span class="v" id="'.$prefix.'_dt"></span></div>
            </div>
            <div class="cc-expl" id="'.$prefix.'_expl"></div>
            <div class="cc-cons"><i class="fas fa-lightbulb" style="color:#ffc107;margin-top:2px;flex-shrink:0;"></i><span id="'.$prefix.'_cons"></span></div>
            <div class="cc-src" id="'.$prefix.'_src"></div>
          </div>
          
          <a href="GestionVehicule.php" class="btn-retour mt-2"><i class="fas fa-arrow-left me-2"></i>Retour à la gestion des véhicules</a>
        </div>
      </div>
    </div>';
}

if ($isAjax):
?>
<?php if ($isEdit): ?>
<!-- MODAL HEADER — style image 2 (blanc, titre jaune) -->
<div style="position:relative; text-align:center; padding:2rem 1.5rem 1.2rem; background:#fff; border-bottom:2px solid #f0f0f0;">
    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
    <h3 style="color:#FFC107; font-weight:800; font-size:1.5rem; text-transform:uppercase; letter-spacing:1px; margin:0;">
        📅 MODIFIER LE RENDEZ-VOUS
    </h3>
</div>
<!-- MODAL BODY -->
<div class="modal-body p-4">
    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>
<?php else: ?>
<!-- MODAL HEADER — style original (fond jaune) -->
<div class="modal-header border-0 text-center d-block py-4 bg-warning">
    <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal"></button>
    <h3 class="fw-bold text-uppercase text-dark mb-0" style="letter-spacing:.04em;font-size:1.05rem;">
        <i class="fas fa-calendar-plus me-2"></i> PLANIFIER UN RENDEZ-VOUS
    </h3>
</div>
<!-- MODAL BODY -->
<div class="modal-body p-4">
    <?php if (!empty($errorMsg)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
    <?php endif; ?>
<?php endif; ?>

    <div id="m_wrap" class="rdv-form-wrap">
        <?php renderForm($clients, $vehicules, $services, $isEdit, $rdv_a_modifier, $vehiculePreselectionne, 'm'); ?>
    </div>

    <?php renderConfirmCard('m'); ?>
</div>

<!-- MODAL FOOTER -->
<div class="modal-footer border-0 justify-content-center pb-4" id="m_footer">
    <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal">Annuler</button>
    <button type="button" id="m_btn" class="btn btn-warning fw-bold px-4 text-<?= $isEdit ? 'white' : 'dark' ?>" <?= $isEdit ? 'style="background:#FFC107;"' : '' ?>>
        <i class="fas <?= $isEdit ? 'fa-save' : 'fa-calendar-check' ?> me-1"></i>
        <?= $isEdit ? "💾 Enregistrer le RDV" : "ENREGISTRER LE RDV" ?>
    </button>
</div>

<!-- CARTE PREDICTION MODAL -->
<div id="predictionCardModal" class="prediction-card" style="display: none;">
    <div class="card-header">
        <i class="fas fa-robot me-2"></i> 🤖 Prédiction du prochain RDV
    </div>
    <div class="card-body">
        <div id="predictionContentModal"></div>
        <div class="prediction-time">
            <span><i class="fas fa-clock"></i> Heure souhaitée :</span>
            <input type="time" id="predictionHeure" value="10:00" step="1800">
        </div>
        <div class="d-flex gap-3 mt-3">
            <button class="prediction-btn-confirm flex-fill" id="confirmPredBtn">📅 Enregistrer ce RDV prédit</button>
            <button class="prediction-btn-cancel flex-fill" onclick="cancelPredictionModal()">Ignorer</button>
        </div>
    </div>
</div>

<script>
// ==================== SCRIPT CORRIGÉ ====================
(function(){
    var currentPrediction = null;
    var currentRdvData = null;

    // Mise à jour du nom client (mode ajout uniquement)
    var sc = document.getElementById('m_client');
    var sn = document.getElementById('m_nom');
    function upNom(){ if(sc && sc.selectedIndex>=0) sn.value = sc.options[sc.selectedIndex].getAttribute('data-nom')||''; }
    if(sc){ upNom(); sc.addEventListener('change', upNom); }

    // Validation du formulaire
    var isEditMode = <?= $isEdit ? 'true' : 'false' ?>;
    function validForm(){
        if (isEditMode) {
            // En mode modification : validation minimale
            var date = document.getElementById('m_date');
            var heure = document.getElementById('m_heure');
            var service = document.getElementById('m_service');
            console.log('[RDV Edit] date=', date ? date.value : 'NULL');
            console.log('[RDV Edit] heure=', heure ? heure.value : 'NULL');
            console.log('[RDV Edit] service=', service ? service.value : 'NULL');
            if (date && !date.value)    { alert('Veuillez saisir la date du RDV.');          return false; }
            if (heure && !heure.value)  { alert('Veuillez saisir l\'heure du RDV.');         return false; }
            if (service && !service.value){ alert('Veuillez choisir le type de service.');    return false; }
            return true;
        }
        var e=[];
        if(!document.getElementById('m_client').value)  e.push("Client");
        if(!document.getElementById('m_veh').value)     e.push("Véhicule");
        if(!document.getElementById('m_date').value)    e.push("Date");
        if(!document.getElementById('m_heure').value)   e.push("Heure");
        if(!document.getElementById('m_service').value) e.push("Type de service");
        if(!document.getElementById('m_desc').value.trim()) e.push("Description");
        if(e.length){ alert("Veuillez remplir :\n- "+e.join("\n- ")); return false; }
        var dateInput = document.getElementById('m_date').value;
        var today = new Date().toISOString().split('T')[0];
        if(dateInput < today){ alert("La date ne peut pas être dans le passé."); return false; }
        return true;
    }

    // Afficher la carte de prédiction (améliorée)
    function showPredictionCard(prediction, rdvData) {
        if (!prediction || !prediction.success || !prediction.recommandation) {
            console.warn("Prédiction invalide:", prediction);
            return;
        }

        currentPrediction = prediction;
        currentRdvData = rdvData;

        var rec = prediction.recommandation;
        var badgeClass = rec.niveau_urgence === 'élevé' ? 'haute' : 
                        (rec.niveau_urgence === 'basse' ? 'basse' : 'moyenne');

        var html = `
            <div class="badge-priority prediction-badge-${badgeClass}">
                📅 PROCHAIN RDV RECOMMANDÉ
            </div>
            <div class="fw-bold mb-2">${rec.message || 'Recommandation'}</div>
            <div class="prediction-date">
                <i class="fas fa-calendar-alt me-2"></i> ${rec.date_prochain || rec.date_formatee || 'Non spécifiée'}
            </div>
            <div class="prediction-km">
                <strong>Source :</strong> ${rec.source ? rec.source : '🤖 Groq IA'}
                ${rec.kilometrage_estime ? `<br><strong>Km estimé :</strong> ${rec.kilometrage_estime.toLocaleString()} km` : ''}
            </div>
            ${rec.conseil ? `<div class="mt-3 small text-warning">💡 ${rec.conseil}</div>` : ''}
        `;

        var contentDiv = document.getElementById('predictionContentModal');
        if(contentDiv) contentDiv.innerHTML = html;
        
        var modal = document.getElementById('predictionCardModal');
        if(modal) modal.style.display = 'block';
        
        // Pré-remplir l'heure avec celle du RDV actuel si disponible
        var heureInput = document.getElementById('predictionHeure');
        if(heureInput && rdvData && rdvData.heure_rdv) {
            heureInput.value = rdvData.heure_rdv;
        }
    }

    // Annuler la prédiction
    window.cancelPredictionModal = function() {
        document.getElementById('predictionCardModal').style.display = 'none';
        currentPrediction = null;
        currentRdvData = null;
    };

    // Enregistrer le RDV prédit (CORRIGÉ)
    document.getElementById('confirmPredBtn').addEventListener('click', async function() {
        if (!currentPrediction || !currentRdvData) {
            alert("Aucune prédiction à enregistrer");
            return;
        }
        
        var rec = currentPrediction.recommandation || {};
        var heureValue = document.getElementById('predictionHeure').value;
        var btn = this;
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enregistrement...';
        
        try {
            var response = await fetch('traitementRDV.php?action=save_predicted', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_vehicule: currentRdvData.id_vehicule,
                    id_client: currentRdvData.id_client,
                    date_prochain: rec.date_prochain || rec.date_formatee,
                    heure_rdv: heureValue,
                    type_service: rec.type_service || document.getElementById('m_service').value,
                    description: currentRdvData.description || 'Prochain RDV prédit automatiquement'
                })
            });
            
            var result = await response.json();
            
            if (result.success) {
                var cardBody = document.querySelector('#predictionCardModal .card-body');
                if(cardBody) {
                    cardBody.innerHTML = `
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 52px;"></i>
                            <p class="mt-3 fw-bold">✅ Prochain RDV enregistré avec succès !</p>
                            <p class="small text-white-50">Date : ${result.date || rec.date_formatee} à ${heureValue}</p>
                        </div>
                    `;
                }
                setTimeout(function() {
                    var modal = document.getElementById('predictionCardModal');
                    if(modal) modal.style.display = 'none';
                    // Recharger la page ou fermer le modal principal
                    location.reload();
                }, 2000);
            } else {
                alert(result.message || 'Erreur lors de l\'enregistrement');
                btn.disabled = false;
                btn.innerHTML = '📅 Enregistrer ce RDV prédit';
            }
        } catch (e) {
            console.error("Erreur:", e);
            alert('Erreur réseau : ' + e.message);
            btn.disabled = false;
            btn.innerHTML = '📅 Enregistrer ce RDV prédit';
        }
    });

    // Estimation de temps
    function getEstimation(svc, km, marque) {
        return fetch('Estimertemps.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'type_service=' + encodeURIComponent(svc) + '&kilometrage=' + km + '&marque=' + encodeURIComponent(marque)
        })
        .then(function(r){ return r.json(); })
        .catch(function(){
            return {
                success: true,
                duree_min: 60,
                fourchette: '45 à 90 min',
                explication: 'Estimation standard.',
                conseils: 'Vérifiez les niveaux.',
                niveau_complexite: 'Modéré',
                source: 'regles'
            };
        });
    }

    // Afficher la carte de confirmation
    function showConfirmationCard(rdv, est) {
        var footer = document.getElementById('m_footer');
        if(footer) footer.style.display = 'none';

        var wrap = document.getElementById('m_wrap');
        wrap.classList.add('fade-out');

        setTimeout(function(){
            wrap.style.display = 'none';

            document.getElementById('m_msg').textContent = rdv.message || 'Enregistré !';
            document.getElementById('m_svc').textContent = rdv.type_service || '—';
            document.getElementById('m_vehv').textContent = (rdv.marque||'') + (rdv.matricule ? ' · '+rdv.matricule : '');
            document.getElementById('m_cli').textContent = rdv.nom_client || '—';
            
            var ds = rdv.date_rdv ? new Date(rdv.date_rdv).toLocaleDateString('fr-FR',{day:'2-digit',month:'long',year:'numeric'}) : '—';
            document.getElementById('m_dt').textContent = ds + ' à ' + (rdv.heure_rdv||'—');

            document.getElementById('m_min').textContent = est.duree_min || '—';
            document.getElementById('m_rng').textContent = est.fourchette || '';
            document.getElementById('m_expl').textContent = est.explication || '';
            document.getElementById('m_cons').textContent = est.conseils || '';
            document.getElementById('m_src').textContent = est.source && est.source.includes('Groq') ? est.source : '📊 Estimation par règles métier';

            var bdg = document.getElementById('m_bdg');
            var niv = (est.niveau_complexite||'Modéré').toLowerCase();
            bdg.textContent = est.niveau_complexite||'Modéré';
            bdg.className = 'est-bdg '+(niv==='simple'?'bdg-s':niv==='complexe'?'bdg-c':'bdg-m');

            document.getElementById('m_spinner').style.display = 'none';
            document.getElementById('m_result').style.display = 'block';

            var card = document.getElementById('m_card');
            card.style.display = 'block';
            requestAnimationFrame(function(){ 
                requestAnimationFrame(function(){ 
                    card.classList.add('rdv-visible'); 
                }); 
            });
            
            // Appel direct JS vers predict_next_rdv.php
            if (rdv.id_vehicule && rdv.type_service) {
                fetch('predict_next_rdv.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'id_vehicule=' + rdv.id_vehicule +
                          '&type_service=' + encodeURIComponent(rdv.type_service) +
                          '&date_rdv=' + encodeURIComponent(rdv.date_rdv || new Date().toISOString().split('T')[0])
                })
                .then(function(r){ return r.json(); })
                .then(function(pred){
                    if (pred && pred.success && rdv.id_client) {
                        pred.id_client   = rdv.id_client;
                        pred.id_vehicule = rdv.id_vehicule;
                        showPredictionCard(pred, {
                            id_vehicule: rdv.id_vehicule,
                            id_client:   rdv.id_client,
                            description: rdv.description,
                            heure_rdv:   rdv.heure_rdv
                        });
                    }
                })
                .catch(function(e){ console.warn('Prédiction échouée:', e); });
            }
        }, 350);
    }

    // PRÉDICTION EN TEMPS RÉEL (optionnelle - désactivée pour éviter les doublons)
    // On laisse le bouton Enregistrer principal gérer l'affichage de la prédiction après enregistrement
    
    // Soumission principale
    document.getElementById('m_btn').addEventListener('click', function(){
        if(!validForm()) return;

        var btn = this;
        var form = document.getElementById('m_form');

        <?php if ($isEdit): ?>
        // Mode modification : envoi AJAX
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enregistrement…';

        fetch('traitementRDV.php?action=update_rdv', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(function(r){ return r.json(); })
        .then(function(res){
            if (res.success) {
                document.querySelector('.modal-body').innerHTML =
                    '<div class="text-center py-5">' +
                    '<i class="fas fa-check-circle text-success" style="font-size:52px;"></i>' +
                    '<p class="mt-3 fw-bold fs-5">✅ Rendez-vous modifié avec succès !</p>' +
                    '</div>';
                document.getElementById('m_footer').style.display = 'none';
                setTimeout(function(){ location.reload(); }, 1500);
            } else {
                alert(res.message || 'Erreur lors de la modification.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save me-1"></i> 💾 Enregistrer le RDV';
            }
        })
        .catch(function(err){
            alert('Erreur réseau : ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> 💾 Enregistrer le RDV';
        });
        <?php else: ?>
        // Mode ajout : fetch AJAX
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement…';

        fetch('traitementRDV.php', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(function(r){ return r.json(); })
        .then(function(rdv){
            if (!rdv.success) {
                alert(rdv.message || 'Erreur');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-calendar-check me-1"></i> ENREGISTRER LE RDV';
                return;
            }
            var svc = rdv.type_service || document.getElementById('m_service').value;
            var vo  = document.getElementById('m_veh');
            var km  = vo ? parseInt((vo.options[vo.selectedIndex]||{getAttribute:function(){return 0;}}).getAttribute('data-km')||0) : 0;
            var marque = rdv.marque || (vo ? (vo.options[vo.selectedIndex]||{getAttribute:function(){return '';}}).getAttribute('data-marque')||'' : '');
            getEstimation(svc, km, marque).then(function(est){ showConfirmationCard(rdv, est); });
        })
        .catch(function(err){
            alert('Erreur réseau : ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-calendar-check me-1"></i> ENREGISTRER LE RDV';
        });
        <?php endif; ?>
    });
})();
</script>
<?php exit(); endif; ?>

<!-- PAGE STANDALONE -->
<!DOCTYPE html>
<html lang="fr">
<head>
    <?php include_once __DIR__ . '/../../partials/head/head-meta.html'; ?>
    <title><?= $isEdit ? "Modifier" : "Planifier" ?> un Rendez-vous</title>
    <?php include_once __DIR__ . '/../../partials/head/head-links.html'; ?>
    <link href="/ProjetWeb/assets/Front office/css/styles.css" rel="stylesheet" />
    <style>
        body { background:#f8f9fa; }
        .page-wrap { max-width:720px; margin:40px auto; padding:0 16px; }
        .form-box { background:#fff; border-radius:14px; box-shadow:0 4px 24px rgba(0,0,0,.09); overflow:hidden; }
        .form-box-hd { background:#ffc107; padding:22px 28px; font-weight:800; font-size:1rem; text-transform:uppercase; letter-spacing:.05em; color:#212529; }
        .form-box-bd { padding:28px 28px 32px; }
        .form-label { font-weight:700; color:#333; }
        .btn-main { background:#ffc107; color:#212529; font-weight:700; border:none; padding:12px 36px; border-radius:8px; font-size:.95rem; cursor:pointer; transition:background .2s; }
        .btn-main:hover { background:#e0a800; }
    </style>
</head>
<body>
<div class="page-wrap">

    <!-- Formulaire -->
    <div id="sa_wrap" class="rdv-form-wrap">
        <div class="form-box">
            <div class="form-box-hd">
                <i class="fas <?= $isEdit ? 'fa-edit' : 'fa-calendar-plus' ?> me-2"></i>
                <?= $isEdit ? "Modifier le Rendez-vous" : "Planifier un Rendez-vous" ?>
            </div>
            <div class="form-box-bd">
                <?php if (!empty($errorMsg)): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($errorMsg) ?></div>
                <?php endif; ?>

                <?php renderForm($clients, $vehicules, $services, $isEdit, $rdv_a_modifier, $vehiculePreselectionne, 'sa'); ?>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <a href="GestionVehicule.php" class="btn btn-secondary px-4">Annuler</a>
                    <button type="button" id="sa_btn" class="btn-main">
                        <i class="fas fa-save me-2"></i><?= $isEdit ? "Enregistrer les modifications" : "Enregistrer le RDV" ?>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Carte confirmation -->
    <?php renderConfirmCard('sa'); ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* ==================== VERSION STANDALONE CORRIGÉE ==================== */

// Mise à jour du nom client
var sc = document.getElementById('sa_client');
var sn = document.getElementById('sa_nom');
function upNomSA(){ 
    if(sc && sc.selectedIndex>=0) {
        sn.value = sc.options[sc.selectedIndex].getAttribute('data-nom')||'';
    }
}
if(sc){ upNomSA(); sc.addEventListener('change', upNomSA); }

// Validation SA
function validSA(){
    var e=[];
    if(!document.getElementById('sa_client').value)  e.push("Client");
    if(!document.getElementById('sa_veh').value)     e.push("Véhicule");
    if(!document.getElementById('sa_date').value)    e.push("Date");
    if(!document.getElementById('sa_heure').value)   e.push("Heure");
    if(!document.getElementById('sa_service').value) e.push("Type de service");
    if(!document.getElementById('sa_desc').value.trim()) e.push("Description");
    if(e.length){ alert("Veuillez remplir :\n- "+e.join("\n- ")); return false; }
    if(document.getElementById('sa_date').value < new Date().toISOString().split('T')[0]){
        alert("La date ne peut pas être dans le passé."); 
        return false;
    }
    return true;
}

// Estimation SA
function getEstSA(svc, km, marque){
    return fetch('Estimertemps.php',{
        method:'POST',
        headers:{'Content-Type':'application/x-www-form-urlencoded'},
        body:'type_service='+encodeURIComponent(svc)+'&kilometrage='+km+'&marque='+encodeURIComponent(marque)
    })
    .then(function(r){ return r.json(); })
    .catch(function(){
        return {
            success: true,
            duree_min: 60,
            fourchette: '45 à 90 min',
            explication: 'Estimation locale.',
            conseils: 'Vérifiez les niveaux.',
            niveau_complexite: 'Modéré',
            source: 'regles'
        };
    });
}

// Variable pour stocker la prédiction en standalone
var currentPredictionSA = null;
var currentRdvDataSA = null;

// Afficher la carte prédiction (standalone)
function showPredictionCardSA(prediction, rdvData) {
    if (!prediction || !prediction.success || !prediction.recommandation) return;
    
    currentPredictionSA = prediction;
    currentRdvDataSA = rdvData;
    
    var rec = prediction.recommandation;
    var html = `
        <div style="position:fixed;bottom:30px;right:30px;z-index:1100;max-width:420px;background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);border-radius:20px;box-shadow:0 20px 35px -8px rgba(0,0,0,0.2);overflow:hidden;">
            <div style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 100%);color:#ffc107;padding:16px 20px;font-weight:bold;border-bottom:2px solid #ffc107;">
                <i class="fas fa-robot me-2"></i> 🤖 Prédiction du prochain RDV
            </div>
            <div style="background:linear-gradient(135deg,#1e1e3a 0%,#1a1a2e 100%);color:white;padding:20px;">
                <div class="fw-bold mb-2">${rec.message || 'Recommandation'}</div>
                <div style="font-size:1rem;font-weight:bold;color:#ffc107;margin:8px 0;padding:8px 12px;background:rgba(255,193,7,0.1);border-radius:12px;text-align:center;">
                    📅 ${rec.date_prochain || rec.date_formatee || 'Non spécifiée'}
                </div>
                <div style="background:rgba(255,255,255,0.05);border-radius:10px;padding:10px;margin:10px 0;display:flex;align-items:center;justify-content:space-between;">
                    <span><i class="fas fa-clock"></i> Heure :</span>
                    <input type="time" id="predictionHeureSA" value="10:00" step="1800" style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:white;padding:6px 12px;border-radius:8px;">
                </div>
                <div class="d-flex gap-3 mt-3">
                    <button id="confirmPredBtnSA" class="prediction-btn-confirm" style="flex:1;background:linear-gradient(135deg,#28a745,#20c997);border:none;color:white;padding:10px 20px;border-radius:12px;font-weight:bold;">📅 Enregistrer</button>
                    <button id="cancelPredBtnSA" class="prediction-btn-cancel" style="flex:1;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.2);color:#ddd;padding:10px 20px;border-radius:12px;">Ignorer</button>
                </div>
            </div>
        </div>
    `;
    
    // Supprimer l'ancienne carte si elle existe
    var oldCard = document.getElementById('predictionCardSA');
    if(oldCard) oldCard.remove();
    
    var div = document.createElement('div');
    div.id = 'predictionCardSA';
    div.innerHTML = html;
    document.body.appendChild(div);
    
    // Attacher les événements
    document.getElementById('confirmPredBtnSA').addEventListener('click', confirmPredictionSA);
    document.getElementById('cancelPredBtnSA').addEventListener('click', function() {
        var card = document.getElementById('predictionCardSA');
        if(card) card.remove();
        currentPredictionSA = null;
        currentRdvDataSA = null;
    });
}

function confirmPredictionSA() {
    if (!currentPredictionSA || !currentRdvDataSA) return;
    
    var rec = currentPredictionSA.recommandation || {};
    var heureValue = document.getElementById('predictionHeureSA').value;
    var btn = document.getElementById('confirmPredBtnSA');
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Enregistrement...';
    
    fetch('traitementRDV.php?action=save_predicted', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_vehicule: currentRdvDataSA.id_vehicule,
            id_client: currentRdvDataSA.id_client,
            date_prochain: rec.date_prochain || rec.date_formatee,
            heure_rdv: heureValue,
            type_service: rec.type_service || document.getElementById('sa_service').value,
            description: currentRdvDataSA.description || 'Prochain RDV prédit automatiquement'
        })
    })
    .then(function(r){ return r.json(); })
    .then(function(result){
        if (result.success) {
            var card = document.getElementById('predictionCardSA');
            if(card) {
                card.innerHTML = `
                    <div style="background:linear-gradient(135deg,#1e1e3a 0%,#1a1a2e 100%);color:white;padding:20px;text-align:center;border-radius:20px;">
                        <i class="fas fa-check-circle text-success" style="font-size: 52px;"></i>
                        <p class="mt-3 fw-bold">✅ Prochain RDV enregistré !</p>
                        <p class="small">Date : ${result.date || rec.date_formatee} à ${heureValue}</p>
                    </div>
                `;
            }
            setTimeout(function(){ 
                var cardElem = document.getElementById('predictionCardSA');
                if(cardElem) cardElem.remove();
                location.reload();
            }, 2000);
        } else {
            alert(result.message || 'Erreur');
            btn.disabled = false;
            btn.innerHTML = '📅 Enregistrer';
        }
    })
    .catch(function(err){
        alert('Erreur: ' + err.message);
        btn.disabled = false;
        btn.innerHTML = '📅 Enregistrer';
    });
}

// Afficher carte confirmation SA
function showCardSA(rdv, est){
    var wrap = document.getElementById('sa_wrap');
    wrap.classList.add('fade-out');
    setTimeout(function(){
        wrap.style.display = 'none';
        document.getElementById('sa_msg').textContent = rdv.message || 'Enregistré !';
        document.getElementById('sa_svc').textContent = rdv.type_service || '—';
        document.getElementById('sa_vehv').textContent = (rdv.marque||'') + (rdv.matricule ? ' · '+rdv.matricule : '');
        document.getElementById('sa_cli').textContent = rdv.nom_client || '—';
        var ds = rdv.date_rdv ? new Date(rdv.date_rdv).toLocaleDateString('fr-FR',{day:'2-digit',month:'long',year:'numeric'}) : '—';
        document.getElementById('sa_dt').textContent = ds + ' à ' + (rdv.heure_rdv||'—');
        document.getElementById('sa_min').textContent = est.duree_min || '—';
        document.getElementById('sa_rng').textContent = est.fourchette || '';
        document.getElementById('sa_expl').textContent = est.explication || '';
        document.getElementById('sa_cons').textContent = est.conseils || '';
        document.getElementById('sa_src').textContent = est.source && est.source.includes('Groq') ? est.source : '📊 Estimation par règles métier';
        
        var bdg = document.getElementById('sa_bdg');
        var niv = (est.niveau_complexite||'Modéré').toLowerCase();
        bdg.textContent = est.niveau_complexite||'Modéré';
        bdg.className = 'est-bdg '+(niv==='simple'?'bdg-s':niv==='complexe'?'bdg-c':'bdg-m');
        
        document.getElementById('sa_spinner').style.display = 'none';
        document.getElementById('sa_result').style.display = 'block';
        
        var card = document.getElementById('sa_card');
        card.style.display = 'block';
        requestAnimationFrame(function(){ 
            requestAnimationFrame(function(){ 
                card.classList.add('rdv-visible'); 
            }); 
        });
        
        // Appel direct JS vers predict_next_rdv.php (standalone)
        if (rdv.id_vehicule && rdv.type_service) {
            fetch('predict_next_rdv.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id_vehicule=' + rdv.id_vehicule +
                      '&type_service=' + encodeURIComponent(rdv.type_service) +
                      '&date_rdv=' + encodeURIComponent(rdv.date_rdv || new Date().toISOString().split('T')[0])
            })
            .then(function(r){ return r.json(); })
            .then(function(pred){
                if (pred && pred.success && rdv.id_client) {
                    pred.id_client   = rdv.id_client;
                    pred.id_vehicule = rdv.id_vehicule;
                    showPredictionCardSA(pred, {
                        id_vehicule: rdv.id_vehicule,
                        id_client:   rdv.id_client,
                        description: rdv.description
                    });
                }
            })
            .catch(function(e){ console.warn('Prédiction SA échouée:', e); });
        }
    }, 350);
}

// Bouton SA
document.getElementById('sa_btn').addEventListener('click', function(){
    if(!validSA()) return;
    
    var vo = document.getElementById('sa_veh').options[document.getElementById('sa_veh').selectedIndex];
    var marque = vo ? (vo.getAttribute('data-marque')||'') : '';
    var km = vo ? parseInt(vo.getAttribute('data-km')||0) : 0;
    var service = document.getElementById('sa_service').value;
    var btn = this;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement…';
    
    fetch('traitementRDV.php', {
        method:'POST',
        headers:{'X-Requested-With':'XMLHttpRequest'},
        body:new FormData(document.getElementById('sa_form'))
    })
    .then(function(r){ return r.json(); })
    .then(function(rdv){
        if(!rdv.success){ 
            alert(rdv.message||'Erreur'); 
            btn.disabled=false; 
            btn.innerHTML='<i class="fas fa-save me-2"></i><?= $isEdit ? "Enregistrer les modifications" : "Enregistrer le RDV" ?>'; 
            return; 
        }
        getEstSA(rdv.type_service||service, rdv.kilometrage||km, rdv.marque||marque)
            .then(function(est){ showCardSA(rdv, est); });
    })
    .catch(function(err){ 
        alert('Erreur réseau : '+err.message); 
        btn.disabled=false; 
        btn.innerHTML='<i class="fas fa-save me-2"></i><?= $isEdit ? "Enregistrer les modifications" : "Enregistrer le RDV" ?>'; 
    });
});
</script>
</body>
</html>