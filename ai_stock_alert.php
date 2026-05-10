<?php
/**
 * ai_stock_alert.php
 * Vérifie les pièces avec stock <= 5 et envoie un email d'alerte.
 */

// Capturer toutes les erreurs PHP et les retourner en JSON
ob_start();
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['success' => false, 'error' => "PHP Error: $errstr (line $errline)"]);
    exit;
});

if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/models/db.php';

// Charger PHPMailer seulement si disponible
$phpmailerDispo = file_exists(__DIR__ . '/PHPMailer/PHPMailer.php');
if ($phpmailerDispo) {
    require_once __DIR__ . '/PHPMailer/PHPMailer.php';
    require_once __DIR__ . '/PHPMailer/SMTP.php';
    require_once __DIR__ . '/PHPMailer/Exception.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$EMAIL_EXPEDITEUR   = "naghmouchiamine291@gmail.com";    // ← Votre Gmail
$EMAIL_MOT_DE_PASSE   = "ebszcldlvfjjtkoq";         // ← Mot de passe app 16 car. sans espaces
$EMAIL_DESTINATAIRE   = "naghmouchiamine291@gmail.com";    // ← Email qui reçoit l'alerte
$EMAIL_NOM_EXPEDITEUR = "Alertes Stock Auto";

define('SEUIL_STOCK', 5); // ← Seuil : alerte si stock <= 5

function jsonError(string $msg, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $msg]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonError('Méthode non autorisée.', 405);

// ── 1. Récupérer pièces avec stock bas ────────────────────────
try {
    $conn = new connexion();
    $pdo  = $conn->conx;

    $stmt = $pdo->prepare("
        SELECT id_piece, nom_piece, reference, quantite_stock, prix
        FROM piece
        WHERE quantite_stock <= :seuil
        ORDER BY quantite_stock ASC
    ");
    $stmt->execute([':seuil' => SEUIL_STOCK]);
    $piecesBas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Stats globales stock
    $statsStock = $pdo->query("
        SELECT
            COUNT(*) as total_pieces,
            SUM(CASE WHEN quantite_stock = 0 THEN 1 ELSE 0 END) as rupture,
            SUM(CASE WHEN quantite_stock <= 5 AND quantite_stock > 0 THEN 1 ELSE 0 END) as critique,
            SUM(CASE WHEN quantite_stock > 5 THEN 1 ELSE 0 END) as normal
        FROM piece
    ")->fetch(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    jsonError('Erreur base de données : ' . $e->getMessage(), 500);
}

// ── 2. Préparer résultat ──────────────────────────────────────
$emailEnvoye = false;
$emailErreur = '';

// ── 3. Envoyer email si pièces en stock bas ───────────────────
if (!empty($piecesBas) && $phpmailerDispo) {
    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $EMAIL_EXPEDITEUR;
        $mail->Password   = $EMAIL_MOT_DE_PASSE;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($EMAIL_EXPEDITEUR, $EMAIL_NOM_EXPEDITEUR);
        $mail->addAddress($EMAIL_DESTINATAIRE);
        $mail->Subject = '📦 ALERTE STOCK BAS — ' . count($piecesBas) . ' pièce(s) à commander';
        $mail->isHTML(true);
        $mail->Body = construireEmailStock($piecesBas, $statsStock);

        $mail->send();
        $emailEnvoye = true;
    } catch (Exception $e) {
        $emailErreur = $e->getMessage();
    }
}

// ── 4. Réponse JSON ───────────────────────────────────────────
if (!$phpmailerDispo && !empty($piecesBas)) {
    $emailErreur = 'PHPMailer non trouvé. Placez le dossier PHPMailer/ à la racine du projet.';
}

ob_clean();
echo json_encode([
    'success'      => true,
    'pieces_bas'   => $piecesBas,
    'stats_stock'  => $statsStock,
    'seuil'        => SEUIL_STOCK,
    'email_envoye' => $emailEnvoye,
    'email_erreur' => $emailErreur
]);
exit;


// ══════════════════════════════════════════════════
//  📧 Corps de l'email HTML
// ══════════════════════════════════════════════════
function construireEmailStock(array $pieces, array $stats): string {
    $date = date('d/m/Y à H:i');
    $nb   = count($pieces);

    $lignes = '';
    foreach ($pieces as $p) {
        if ($p['quantite_stock'] == 0) {
            $badge = "<span style='background:#e74c3c;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:bold;'>RUPTURE</span>";
            $bg    = '#fff5f5';
        } else {
            $badge = "<span style='background:#fd7e14;color:#fff;padding:3px 10px;border-radius:12px;font-size:12px;font-weight:bold;'>CRITIQUE</span>";
            $bg    = '#fffbf5';
        }
        $lignes .= "
        <tr style='background:{$bg};'>
            <td style='padding:12px;border-bottom:1px solid #eee;font-weight:600;color:#333;'>{$p['nom_piece']}</td>
            <td style='padding:12px;border-bottom:1px solid #eee;color:#666;'>{$p['reference']}</td>
            <td style='padding:12px;border-bottom:1px solid #eee;text-align:center;font-weight:bold;color:#e74c3c;font-size:18px;'>{$p['quantite_stock']}</td>
            <td style='padding:12px;border-bottom:1px solid #eee;text-align:center;'>{$badge}</td>
            <td style='padding:12px;border-bottom:1px solid #eee;text-align:right;color:#27ae60;font-weight:600;'>{$p['prix']} DT</td>
        </tr>";
    }

    return "<!DOCTYPE html><html><body style='font-family:Arial,sans-serif;background:#f0f0f0;padding:20px;'>
    <div style='max-width:650px;margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,.1);'>

        <!-- Header -->
        <div style='background:linear-gradient(135deg,#fd7e14,#e55a00);padding:25px;text-align:center;'>
            <h1 style='color:#fff;margin:0;font-size:22px;'>📦 Alerte Stock Bas</h1>
            <p style='color:rgba(255,255,255,.9);margin:8px 0 0;font-size:14px;'>{$date}</p>
        </div>

        <!-- Résumé chiffres -->
        <div style='display:flex;padding:20px 25px;gap:15px;border-bottom:1px solid #eee;background:#fffbf5;'>
            <div style='flex:1;text-align:center;background:#fff;border-radius:8px;padding:15px;border:1px solid #eee;'>
                <div style='font-size:28px;font-weight:bold;color:#fd7e14;'>{$nb}</div>
                <div style='font-size:12px;color:#888;margin-top:4px;'>Pièces à commander</div>
            </div>
            <div style='flex:1;text-align:center;background:#fff;border-radius:8px;padding:15px;border:1px solid #eee;'>
                <div style='font-size:28px;font-weight:bold;color:#e74c3c;'>{$stats['rupture']}</div>
                <div style='font-size:12px;color:#888;margin-top:4px;'>En rupture (0 stock)</div>
            </div>
            <div style='flex:1;text-align:center;background:#fff;border-radius:8px;padding:15px;border:1px solid #eee;'>
                <div style='font-size:28px;font-weight:bold;color:#27ae60;'>{$stats['normal']}</div>
                <div style='font-size:12px;color:#888;margin-top:4px;'>Stock normal</div>
            </div>
        </div>

        <!-- Tableau pièces -->
        <div style='padding:20px 25px;'>
            <h3 style='color:#333;margin:0 0 15px;font-size:15px;'>Liste des pièces à commander</h3>
            <table style='width:100%;border-collapse:collapse;font-size:14px;'>
                <thead>
                    <tr style='background:#f5f5f5;'>
                        <th style='padding:10px;text-align:left;color:#555;border-bottom:2px solid #eee;'>Pièce</th>
                        <th style='padding:10px;text-align:left;color:#555;border-bottom:2px solid #eee;'>Référence</th>
                        <th style='padding:10px;text-align:center;color:#555;border-bottom:2px solid #eee;'>Stock</th>
                        <th style='padding:10px;text-align:center;color:#555;border-bottom:2px solid #eee;'>Statut</th>
                        <th style='padding:10px;text-align:right;color:#555;border-bottom:2px solid #eee;'>Prix</th>
                    </tr>
                </thead>
                <tbody>{$lignes}</tbody>
            </table>
        </div>

        <!-- Conseil -->
        <div style='margin:0 25px 20px;padding:15px;background:#f0fff4;border-left:4px solid #27ae60;border-radius:4px;'>
            <strong style='color:#27ae60;'>Conseil :</strong>
            <span style='color:#444;font-size:13px;'> Passez commande dès maintenant pour éviter toute rupture de stock.</span>
        </div>

        <!-- Footer -->
        <div style='padding:15px;text-align:center;background:#333;'>
            <p style='color:#aaa;margin:0;font-size:12px;'>Alertes Stock — Pieces Auto © {$date}</p>
        </div>
    </div></body></html>";
}