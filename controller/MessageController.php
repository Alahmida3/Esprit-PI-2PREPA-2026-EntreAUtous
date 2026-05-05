<?php
/**
 * MessageController.php — Contrôleur Messagerie
 * Gère toutes les requêtes AJAX (GET/POST) du module messagerie.
 * Point d'entrée : controller/MessageController.php?action=xxx
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ── Chargement des modèles (Model.php contient la DB) ─────────
require_once __DIR__ . '/../models/Discussion.php';
require_once __DIR__ . '/../models/Message.php';

// ── Helper réponse JSON ───────────────────────────────────────
function respond(bool $success, $data = null, string $message = ''): void {
    $payload = ['success' => $success];
    if ($data    !== null) $payload['data']    = $data;
    if ($message !== '')   $payload['message'] = $message;
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Helper validation chaîne ──────────────────────────────────
function requirePost(string $key, int $minLen = 1): ?string {
    $val = trim($_POST[$key] ?? '');
    return (mb_strlen($val) >= $minLen) ? $val : null;
}

$action = trim($_REQUEST['action'] ?? '');

try {
    switch ($action) {

        // ════════════════════════════════════════════════════
        //  DISCUSSIONS
        // ════════════════════════════════════════════════════

        case 'get_discussions':
            respond(true, Discussion::getAll());

        case 'get_discussion':
            $id   = (int)($_GET['id'] ?? 0);
            $disc = $id ? Discussion::getById($id) : null;
            $disc ? respond(true, $disc) : respond(false, null, 'Discussion introuvable.');

        case 'get_discussions_user':
            $uid = (int)($_GET['user_id'] ?? 0);
            if (!$uid) respond(false, null, 'user_id manquant.');
            respond(true, Discussion::getByUser($uid));

        case 'create_discussion':
            $user_id   = (int)($_POST['user_id']   ?? 0);
            $garage_id = (int)($_POST['garage_id'] ?? 0);
            $objet     = requirePost('objet', 3);
            if (!$user_id)   respond(false, null, 'Utilisateur non sélectionné.');
            if (!$garage_id) respond(false, null, 'Garage non sélectionné.');
            if (!$objet)     respond(false, null, 'Objet trop court (min 3 caractères).');
            $id = Discussion::create($user_id, $garage_id, $objet);
            respond(true, ['id' => $id], 'Discussion créée avec succès.');

        case 'update_discussion':
            $id        = (int)($_POST['id']        ?? 0);
            $user_id   = (int)($_POST['user_id']   ?? 0);
            $garage_id = (int)($_POST['garage_id'] ?? 0);
            $objet     = requirePost('objet', 3);
            if (!$id || !$user_id || !$garage_id || !$objet)
                respond(false, null, 'Données invalides ou incomplètes.');
            Discussion::update($id, $user_id, $garage_id, $objet);
            respond(true, null, 'Discussion mise à jour.');

        case 'delete_discussion':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) respond(false, null, 'ID manquant.');
            Discussion::delete($id);
            respond(true, null, 'Discussion supprimée.');

        case 'search_discussions':
            $q = trim($_GET['q'] ?? '');
            respond(true, Discussion::search($q));

        case 'get_stats':
            respond(true, Discussion::getStats());

        // ════════════════════════════════════════════════════
        //  MESSAGES
        // ════════════════════════════════════════════════════

        case 'get_messages':
            $did = (int)($_GET['discussion_id'] ?? 0);
            if (!$did) respond(false, null, 'discussion_id manquant.');
            respond(true, Message::getByDiscussion($did));

        case 'get_all_messages':
            respond(true, Message::getAll());

        case 'get_message':
            $id  = (int)($_GET['id'] ?? 0);
            $msg = $id ? Message::getById($id) : null;
            $msg ? respond(true, $msg) : respond(false, null, 'Message introuvable.');

        case 'create_message':
            $did   = (int)($_POST['discussion_id']  ?? 0);
            $eid   = (int)($_POST['expediteur_id']  ?? 0);
            $etype = in_array($_POST['expediteur_type'] ?? '', ['user','garage'])
                        ? $_POST['expediteur_type'] : 'user';
            $contenu = requirePost('contenu', 2);
            if (!$did)     respond(false, null, 'Discussion non sélectionnée.');
            if (!$eid)     respond(false, null, 'Expéditeur non défini.');
            if (!$contenu) respond(false, null, 'Message trop court (min 2 caractères).');
            if (mb_strlen(trim($_POST['contenu'] ?? '')) > 1000)
                           respond(false, null, 'Message trop long (max 1000 caractères).');
            $id  = Message::create($did, $eid, $_POST['contenu'], $etype);
            $msg = Message::getById($id);
            respond(true, $msg, 'Message envoyé.');

        case 'update_message':
            $id      = (int)($_POST['id'] ?? 0);
            $contenu = requirePost('contenu', 2);
            if (!$id || !$contenu) respond(false, null, 'Données invalides.');
            Message::update($id, $_POST['contenu']);
            respond(true, null, 'Message modifié.');

        case 'delete_message':
            $id = (int)($_POST['id'] ?? 0);
            if (!$id) respond(false, null, 'ID manquant.');
            Message::delete($id);
            respond(true, null, 'Message supprimé.');

        case 'search_messages':
            $q = trim($_GET['q'] ?? '');
            respond(true, Message::search($q));

        // ════════════════════════════════════════════════════
        //  LISTES (selects dynamiques)
        // ════════════════════════════════════════════════════

        case 'get_users':
            $pdo   = Discussion::db();    // db() est protected → on passe par un helper
            // ↓ on appelle directement PDO via le modèle public
            $users = $pdo->query("SELECT id, nom FROM user ORDER BY nom")->fetchAll();
            respond(true, $users);

        case 'get_garages':
            $pdo     = Discussion::db();
            $garages = $pdo->query("SELECT id, nom_garages FROM garages ORDER BY nom_garages")->fetchAll();
            respond(true, $garages);

        default:
            respond(false, null, "Action inconnue : '$action'.");
    }

} catch (PDOException $e) {
    respond(false, null, 'Erreur base de données : ' . $e->getMessage());
} catch (Exception $e) {
    respond(false, null, 'Erreur serveur : ' . $e->getMessage());
}
?>