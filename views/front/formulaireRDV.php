<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once(__DIR__ . '/../../models/db.php');

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
          && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

try {
    $pdo = config::getConnexion();

    // ID client depuis la session
    $id_client_session = (int)($_SESSION['user_id'] ?? 0);

    $id_vehicule_pre = isset($_GET['id_vehicule']) ? (int)$_GET['id_vehicule'] : 0;

    if ($id_vehicule_pre > 0) {
        $stmtV = $pdo->prepare("SELECT idVehicule, matriculevoiture, marqueV, kilometrageV, idclient FROM vehicule WHERE idVehicule = ?");
        $stmtV->execute([$id_vehicule_pre]);
        $vehiculePre = $stmtV->fetch(PDO::FETCH_ASSOC);
        $id_client = $vehiculePre ? (int)$vehiculePre['idclient'] : $id_client_session;
    } else {
        $id_client   = $id_client_session;
        $vehiculePre = null;
    }

    $stmtClient = $pdo->prepare("SELECT id_client, nom, prenom FROM `client` WHERE id_client = ?");
    $stmtClient->execute([$id_client]);
    $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

    // Fallback session si client non trouvé en BDD
    if (!$client) {
        $client = [
            'id_client' => $id_client_session,
            'nom'       => $_SESSION['nom']    ?? '',
            'prenom'    => $_SESSION['prenom'] ?? '',
        ];
    }

    $stmtVeh = $pdo->prepare("SELECT idVehicule, matriculevoiture, marqueV, kilometrageV FROM vehicule WHERE idclient = ?");
    $stmtVeh->execute([$id_client]);
    $vehicules = $stmtVeh->fetchAll(PDO::FETCH_ASSOC);

    $services = $pdo->query("SELECT nom_service FROM services ORDER BY nom_service")->fetchAll(PDO::FETCH_ASSOC);
    $garages  = $pdo->query("SELECT `id-garage` AS id_garage, nom_garage FROM garages ORDER BY nom_garage")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Erreur de base de données : " . $e->getMessage());
}
?>
<?php if (!$isAjax): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Planifier un Rendez-vous</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php endif; ?>

<style>
.form-container {
    background: #fff;
    border-radius: 12px;
    max-width: 900px;
    margin: 20px auto;
    padding: 30px;
}
.form-title { color: #ffc107; font-weight: 800; text-transform: uppercase; }
.btn-save { background-color: #ffc107; color: #212529; font-weight: bold; }
.btn-save:hover { background-color: #e0a800; }
.client-readonly {
    background-color: #f0f0f0;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 10px 14px;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* ===== CARD ESTIMATION ===== */
#card-estimation {
    display: none;
    background: linear-gradient(135deg, #1a1a2e 0%, #16213e 60%, #0f3460 100%);
    border-radius: 16px;
    overflow: hidden;
    color: #fff;
    margin-top: 30px;
}
#card-estimation.show { display: block; animation: fadeInUp 0.4s ease; }

.est-top { background: #ffc107; padding: 16px 24px; }
.est-top h5 { margin: 0; font-weight: 800; color: #212529; font-size: 1rem; }
.est-body { padding: 24px; }
.est-big-num { font-size: 4rem; font-weight: 900; color: #ffc107; line-height: 1; }
.est-lbl { font-size: .75rem; color: rgba(255,255,255,.5); text-transform: uppercase; letter-spacing: .1em; }
.est-range { font-size: .95rem; color: rgba(255,255,255,.75); margin-top: 6px; }
.est-expl { background: rgba(255,255,255,.07); border-left: 3px solid #ffc107; border-radius: 8px; padding: 10px 14px; font-size: .85rem; color: rgba(255,255,255,.7); margin-top: 14px; }
.est-src { font-size: .7rem; color: rgba(255,255,255,.35); text-align: right; margin-top: 10px; }

/* ===== CARD PRÉDICTION ===== */
#card-prediction {
    display: none;
    background: linear-gradient(135deg, #1e1e3a 0%, #1a1a2e 100%);
    border: 2px solid #ffc107;
    border-radius: 16px;
    overflow: hidden;
    color: #fff;
    margin-top: 20px;
}
#card-prediction.show { display: block; animation: fadeInUp 0.5s ease 0.2s both; }

.pred-header {
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    border-bottom: 2px solid #ffc107;
    padding: 14px 20px;
    font-weight: bold;
    color: #ffc107;
}
.pred-body { padding: 20px; }
.pred-date {
    font-size: 1.1rem;
    font-weight: bold;
    color: #ffc107;
    background: rgba(255,193,7,0.12);
    border-radius: 10px;
    padding: 10px 14px;
    text-align: center;
    margin: 10px 0;
}
.pred-meta {
    background: rgba(255,255,255,.06);
    border-radius: 10px;
    padding: 10px 14px;
    font-size: .85rem;
    color: rgba(255,255,255,.7);
    margin-top: 10px;
}
.badge-urgence {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: .72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 10px;
}
.urgence-eleve  { background: #dc3545; color: #fff; }
.urgence-moyen  { background: #ffc107; color: #1a1a2e; }
.urgence-basse  { background: #28a745; color: #fff; }

.pred-heure-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: rgba(255,255,255,.07);
    border-radius: 10px;
    padding: 10px 14px;
    margin-top: 12px;
}
.pred-heure-row input[type="time"] {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.25);
    color: #fff;
    padding: 6px 12px;
    border-radius: 8px;
}
.pred-btns { display: flex; gap: 12px; margin-top: 14px; }
.btn-pred-save {
    flex: 1;
    background: linear-gradient(135deg, #28a745, #20c997);
    border: none;
    color: #fff;
    padding: 10px;
    border-radius: 10px;
    font-weight: bold;
    cursor: pointer;
    transition: transform .2s;
}
.btn-pred-save:hover { transform: translateY(-2px); }
.btn-pred-ignore {
    flex: 1;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.2);
    color: #ccc;
    padding: 10px;
    border-radius: 10px;
    cursor: pointer;
}
.btn-pred-ignore:hover { background: rgba(255,255,255,.16); color: #fff; }

/* ===== SUCCESS FINAL ===== */
#card-success {
    display: none;
    background: linear-gradient(135deg, #1a1a2e, #16213e);
    border-radius: 16px;
    padding: 40px;
    text-align: center;
    color: #fff;
    margin-top: 30px;
}
#card-success.show { display: block; animation: fadeInUp 0.4s ease; }
.success-icon { font-size: 4rem; color: #28a745; margin-bottom: 16px; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<div class="container">
  <div class="form-container">
    <h2 class="text-center form-title mb-4">
      <i class="fas fa-calendar-plus me-2"></i>Planifier un Rendez-vous
    </h2>

    <div id="msg-global" class="alert" style="display:none;"></div>

    <!-- ===== FORMULAIRE ===== -->
    <div id="form-wrap">
      <form id="formRDV" novalidate>
        <input type="hidden" name="id_client" value="<?= $client['id_client'] ?>">

        <div class="row g-4">

          <!-- Client -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Client</label>
            <div class="client-readonly">
              <i class="fas fa-user text-warning"></i>
              <?= htmlspecialchars($client['nom'] . ' ' . $client['prenom']) ?>
            </div>
          </div>

          <!-- Garage -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Garage</label>
            <select name="id_garage" id="id_garage" class="form-select" required>
              <option value="">-- Choisir le garage --</option>
              <?php foreach($garages as $g): ?>
                <option value="<?= $g['id_garage'] ?>">
                  <?= htmlspecialchars($g['nom_garage']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Véhicule -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Véhicule</label>
            <?php if (count($vehicules) === 0): ?>
              <div class="alert alert-warning py-2">Aucun véhicule enregistré pour ce client.</div>
              <input type="hidden" name="id_vehicule" value="">
            <?php elseif (count($vehicules) === 1 || $id_vehicule_pre > 0): ?>
              <?php $vShow = $vehiculePre ?? $vehicules[0]; ?>
              <div class="client-readonly">
                <i class="fas fa-car text-warning"></i>
                <?= htmlspecialchars($vShow['matriculevoiture']) ?> — <?= htmlspecialchars($vShow['marqueV'] ?? '') ?>
              </div>
              <input type="hidden" name="id_vehicule"
                     id="id_vehicule_hidden"
                     value="<?= $vShow['idVehicule'] ?>"
                     data-marque="<?= htmlspecialchars($vShow['marqueV'] ?? '') ?>"
                     data-km="<?= (int)($vShow['kilometrageV'] ?? 0) ?>">
            <?php else: ?>
              <select name="id_vehicule" id="id_vehicule" class="form-select" required>
                <option value="">Choisir un véhicule...</option>
                <?php foreach($vehicules as $v): ?>
                  <option value="<?= $v['idVehicule'] ?>"
                          data-marque="<?= htmlspecialchars($v['marqueV'] ?? '') ?>"
                          data-km="<?= (int)($v['kilometrageV'] ?? 0) ?>">
                    <?= htmlspecialchars($v['matriculevoiture']) ?> — <?= htmlspecialchars($v['marqueV'] ?? '') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
          </div>

          <!-- Date -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Date du RDV</label>
            <input type="date" name="date_rdv" id="date_rdv" class="form-control" required
                   min="<?= date('Y-m-d') ?>">
          </div>

          <!-- Heure -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Heure du RDV</label>
            <input type="time" name="heure_rdv" id="heure_rdv" class="form-control" required>
          </div>

          <!-- Service -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Type de Service</label>
            <select name="type_service" id="type_service" class="form-select" required>
              <option value="">Choisir un service...</option>
              <?php foreach($services as $s): ?>
                <option value="<?= htmlspecialchars($s['nom_service']) ?>">
                  <?= htmlspecialchars($s['nom_service']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <!-- Statut -->
          <div class="col-md-6">
            <label class="form-label fw-bold">Statut</label>
            <input type="text" class="form-control" value="En attente" disabled>
            <input type="hidden" name="statut" value="En attente">
          </div>

          <!-- Description -->
          <div class="col-12">
            <label class="form-label fw-bold">Description</label>
            <textarea name="description" id="description" class="form-control" rows="4"></textarea>
          </div>

          <!-- Boutons -->
          <div class="col-12 text-center mt-4">
            <?php if ($isAjax): ?>
              <button type="button" class="btn btn-secondary me-3"
                      onclick="bootstrap.Modal.getInstance(document.getElementById('modalRDV')).hide()">
                Annuler
              </button>
            <?php else: ?>
              <a href="GestionVehicule.php" class="btn btn-secondary me-3">Annuler</a>
            <?php endif; ?>
            <button type="submit" id="btn-enregistrer" class="btn btn-save px-5">
              <i class="fas fa-save me-2"></i>Enregistrer le RDV
            </button>
          </div>
        </div>
      </form>
    </div>

    <!-- ===== CARD ESTIMATION ===== -->
    <div id="card-estimation">
      <div class="est-top">
        <h5><i class="fas fa-clock me-2"></i>Estimation du temps d'intervention</h5>
      </div>
      <div class="est-body">
        <div class="d-flex align-items-center gap-4">
          <div class="text-center">
            <div class="est-big-num" id="est-minutes">—</div>
            <div class="est-lbl">minutes</div>
            <div class="est-range" id="est-fourchette"></div>
          </div>
          <div class="flex-grow-1">
            <h5 class="fw-bold mb-1" id="est-service" style="color:#ffc107;"></h5>
            <div class="est-expl" id="est-explication"></div>
          </div>
        </div>
        <div class="est-src" id="est-source"></div>
      </div>
    </div>

    <!-- ===== CARD PRÉDICTION PROCHAIN RDV ===== -->
    <div id="card-prediction">
      <div class="pred-header">
        <i class="fas fa-robot me-2"></i>🤖 Prédiction du prochain RDV
      </div>
      <div class="pred-body">
        <div id="pred-badge-urgence"></div>
        <div class="fw-bold mb-1" id="pred-message"></div>
        <div class="pred-date" id="pred-date">
          <i class="fas fa-calendar-alt me-2"></i><span id="pred-date-val">—</span>
        </div>
        <div class="pred-meta">
          <i class="fas fa-info-circle me-1" style="color:#ffc107;"></i>
          <span id="pred-conseil"></span>
        </div>
        <div class="pred-meta mt-2">
          Source : <strong id="pred-source"></strong>
        </div>
        <div class="pred-heure-row">
          <span><i class="fas fa-clock me-1"></i> Heure souhaitée :</span>
          <input type="time" id="pred-heure" value="10:00" step="1800">
        </div>
        <div class="pred-btns">
          <button class="btn-pred-save" id="btn-save-pred">
            <i class="fas fa-calendar-check me-1"></i> Enregistrer ce RDV prédit
          </button>
          <button class="btn-pred-ignore" id="btn-ignore-pred">
            Ignorer
          </button>
        </div>
        <div id="pred-msg" class="mt-2" style="display:none;"></div>
      </div>
    </div>

    <!-- ===== CARD SUCCÈS FINAL ===== -->
    <div id="card-success">
      <div class="success-icon"><i class="fas fa-check-circle"></i></div>
      <h4 class="fw-bold mb-2" style="color:#ffc107;">RDV prédit enregistré !</h4>
      <p id="success-date" class="mb-3" style="color:rgba(255,255,255,.75);"></p>
      <a href="GestionVehicule.php" class="btn btn-warning fw-bold px-4">
        <i class="fas fa-arrow-left me-2"></i>Retour à la gestion
      </a>
    </div>

  </div><!-- /form-container -->
</div><!-- /container -->

<script>
(function () {
    // ===== Variables globales =====
    var savedRdvData = null; // données du RDV enregistré (pour la prédiction)

    // ===== Helpers =====
    function getVehiculeData() {
        var hidden = document.getElementById('id_vehicule_hidden');
        if (hidden) return { id: parseInt(hidden.value), km: parseInt(hidden.getAttribute('data-km') || 0), marque: hidden.getAttribute('data-marque') || '' };
        var sel = document.getElementById('id_vehicule');
        if (sel && sel.value) {
            var opt = sel.selectedOptions[0];
            return { id: parseInt(sel.value), km: parseInt(opt.getAttribute('data-km') || 0), marque: opt.getAttribute('data-marque') || '' };
        }
        return { id: 0, km: 0, marque: '' };
    }

    function showMsg(type, text) {
        var el = document.getElementById('msg-global');
        el.className = 'alert alert-' + type;
        el.textContent = text;
        el.style.display = 'block';
        if (type === 'success') setTimeout(function () { el.style.display = 'none'; }, 4000);
    }

    // ===== Afficher card estimation =====
    function showEstimation(est, service) {
        document.getElementById('est-minutes').textContent    = est.duree_min   || '—';
        document.getElementById('est-fourchette').textContent = est.fourchette  || '';
        document.getElementById('est-service').textContent    = service;
        document.getElementById('est-explication').textContent= est.explication || '';
        document.getElementById('est-source').textContent     = est.source === 'ia' ? '🤖 Estimation par IA Claude' : '📊 Estimation par règles métier';
        document.getElementById('card-estimation').classList.add('show');
    }

    // ===== Résoudre le chemin de base (fonctionne en modal et en page directe) =====
    var BASE_PATH = (function() {
        // On récupère le dossier de la page courante (GestionVehicule.php ou formulaireRDV.php)
        var path = window.location.pathname;
        return path.substring(0, path.lastIndexOf('/') + 1);
    })();

    // ===== Appel estimation =====
    async function getEstimation(service, km, marque) {
        try {
            var res = await fetch(BASE_PATH + 'Estimertemps.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'type_service=' + encodeURIComponent(service) + '&kilometrage=' + (km||0) + '&marque=' + encodeURIComponent(marque||'')
            });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return await res.json();
        } catch (e) {
            console.warn('Estimation fallback:', e);
            return { duree_min: 60, fourchette: '45 à 90 minutes', explication: 'Estimation par défaut', source: 'default' };
        }
    }

    // ===== Fallback prédiction (si Groq inaccessible) =====
    function buildFallbackPrediction(type_service, date_rdv) {
        var s = (type_service || '').toLowerCase();
        var jours = 90;
        if (s.indexOf('vidange')       !== -1) jours = 180;
        else if (s.indexOf('vision')   !== -1) jours = 365;
        else if (s.indexOf('pneu')     !== -1) jours = 270;
        else if (s.indexOf('frein')    !== -1) jours = 365;
        else if (s.indexOf('courroie') !== -1) jours = 730;
        else if (s.indexOf('climat')   !== -1) jours = 365;
        else if (s.indexOf('turbo')    !== -1) jours = 180;
        else if (s.indexOf('moteur')   !== -1) jours = 365;

        var base = date_rdv ? new Date(date_rdv) : new Date();
        base.setDate(base.getDate() + jours);
        var dateStr = base.toISOString().split('T')[0];

        return {
            success: true,
            recommandation: {
                date_prochain:     dateStr,
                type_service:      type_service,
                message:           'Prochain entretien recommandé dans ' + jours + ' jours selon les standards constructeur.',
                niveau_urgence:    jours <= 90 ? 'élevée' : (jours <= 270 ? 'moyenne' : 'basse'),
                kilometrage_prevu: null,
                intervalle_jours:  jours,
                conseils:          "Respectez les intervalles d'entretien du constructeur pour éviter toute panne.",
                source:            '📊 Estimation standard'
            }
        };
    }

    // ===== Appel prédiction =====
    async function getPrediction(id_vehicule, type_service, date_rdv) {
        try {
            var body = 'id_vehicule=' + encodeURIComponent(id_vehicule)
                     + '&type_service=' + encodeURIComponent(type_service)
                     + '&date_rdv=' + encodeURIComponent(date_rdv);
            var res = await fetch(BASE_PATH + 'predict_next_rdv.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: body
            });

            // Lire la réponse brute pour détecter les erreurs PHP
            var rawText = await res.text();
            console.log('[Prédiction] Réponse brute:', rawText.substring(0, 300));

            // Extraire uniquement le JSON (ignore le HTML d'erreur PHP avant)
            var jsonMatch = rawText.match(/\{[\s\S]*\}/);
            if (!jsonMatch) throw new Error('Réponse non-JSON: ' + rawText.substring(0, 100));

            var data = JSON.parse(jsonMatch[0]);
            console.log('[Prédiction] JSON parsé:', data);

            if (data && data.success) return data;
            throw new Error(data.message || 'Réponse invalide');

        } catch (e) {
            console.warn('[Prédiction] Erreur, utilisation du fallback:', e.message);
            // Afficher quand même une prédiction standard
            return buildFallbackPrediction(type_service, date_rdv);
        }
    }

    // ===== Afficher card prédiction =====
    function showPrediction(pred) {
        console.log('[showPrediction] données reçues:', JSON.stringify(pred));
        // Accepter les deux formats : {success, recommandation} ou {success, prediction: {recommandation}}
        if (!pred) { console.warn('[showPrediction] pred est null'); return; }
        if (!pred.success) { console.warn('[showPrediction] success=false, message:', pred.message); return; }

        // Normaliser : traitementRDV renvoie pred.prediction = {success, recommandation}
        var rec = pred.recommandation || (pred.prediction && pred.prediction.recommandation) || null;
        if (!rec) { console.warn('[showPrediction] recommandation introuvable dans:', pred); return; }

        // Badge urgence — normaliser les variantes (élevé / élevée / haute)
        var niv = (rec.niveau_urgence || 'moyenne').toLowerCase().replace('ée', 'e');
        var urgenceClass = (niv === 'élevé' || niv === 'haute' || niv === 'high') ? 'urgence-eleve'
                         : (niv === 'basse' || niv === 'low')                     ? 'urgence-basse'
                         : 'urgence-moyen';
        var badgeEl = document.getElementById('pred-badge-urgence');
        badgeEl.innerHTML = '<span class="badge-urgence ' + urgenceClass + '">⚡ Urgence : ' + (rec.niveau_urgence || 'moyenne') + '</span>';

        document.getElementById('pred-message').textContent  = rec.message   || '';
        document.getElementById('pred-date-val').textContent = rec.date_prochain || '—';
        document.getElementById('pred-conseil').textContent  = rec.conseils  || rec.conseil || '';
        document.getElementById('pred-source').textContent   = rec.source || '🤖 Groq IA';

        document.getElementById('card-prediction').classList.add('show');
    }

    // ===== Submit formulaire =====
    document.getElementById('formRDV').addEventListener('submit', async function (e) {
        e.preventDefault();
        if (!this.checkValidity()) { this.reportValidity(); return; }

        var btn = document.getElementById('btn-enregistrer');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...';

        try {
            var resp = await fetch('traitementRDV.php', {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(this)
            });

            var result = await resp.json();
            if (!result.success) throw new Error(result.message || 'Erreur inconnue');

            // RDV enregistré ✅
            showMsg('success', '✅ Rendez-vous enregistré avec succès !');

            // Récupérer données véhicule
            var veh = getVehiculeData();
            var service = document.getElementById('type_service').value;
            var date_rdv = document.getElementById('date_rdv').value;
            var id_client = document.querySelector('input[name="id_client"]').value;

            // Sauvegarder pour le bouton prédiction
            savedRdvData = {
                id_client:  id_client,
                id_vehicule: veh.id || result.id_vehicule,
                type_service: service,
                date_rdv: date_rdv
            };

            // Cacher le formulaire proprement
            document.getElementById('form-wrap').style.opacity = '0';
            document.getElementById('form-wrap').style.transition = 'opacity .3s ease';
            setTimeout(function () { document.getElementById('form-wrap').style.display = 'none'; }, 300);

            // 1️⃣ Appeler l'estimation directement (traitementRDV ne fait plus le cURL interne)
            var est = await getEstimation(service, veh.km || result.kilometrage, veh.marque || result.marque);

            // 2️⃣ Afficher estimation
            showEstimation(est, service);

            // 3️⃣ Appeler la prédiction directement depuis le JS
            var predVehiculeId = veh.id || result.id_vehicule;
            var pred = null;
            if (predVehiculeId) {
                pred = await getPrediction(predVehiculeId, service, date_rdv);
            }

            // 4️⃣ Afficher prédiction (toujours afficher, fallback intégré dans getPrediction)
            showPrediction(pred);

            // Réinitialiser le bouton
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer le RDV';

        } catch (err) {
            showMsg('danger', '❌ ' + err.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-2"></i>Enregistrer le RDV';
        }
    });

    // ===== Bouton enregistrer RDV prédit =====
    document.getElementById('btn-save-pred').addEventListener('click', async function () {
        if (!savedRdvData) return;

        var heure = document.getElementById('pred-heure').value || '10:00';
        var dateVal = document.getElementById('pred-date-val').textContent;
        var btn = this;

        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Enregistrement...';

        try {
            var resp = await fetch('traitementRDV.php?action=save_predicted', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id_client:    savedRdvData.id_client,
                    id_vehicule:  savedRdvData.id_vehicule,
                    date_prochain: dateVal,
                    heure_rdv:    heure,
                    type_service: savedRdvData.type_service,
                    description:  'Prochain RDV prédit automatiquement'
                })
            });

            var result = await resp.json();
            if (!result.success) throw new Error(result.message);

            // Cacher prediction card
            document.getElementById('card-prediction').style.display = 'none';

            // Afficher succès final
            document.getElementById('success-date').textContent = '📅 Prévu le ' + dateVal + ' à ' + heure;
            document.getElementById('card-success').classList.add('show');

        } catch (err) {
            var predMsg = document.getElementById('pred-msg');
            predMsg.className = 'alert alert-danger mt-2';
            predMsg.textContent = '❌ ' + err.message;
            predMsg.style.display = 'block';
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-calendar-check me-1"></i> Enregistrer ce RDV prédit';
        }
    });

    // ===== Bouton ignorer prédiction =====
    document.getElementById('btn-ignore-pred').addEventListener('click', function () {
        document.getElementById('card-prediction').style.display = 'none';
    });

})();
</script>

<?php if (!$isAjax): ?>
</body>
</html>
<?php endif; ?>