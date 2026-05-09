<?php
/**
 * views/front/partials/journey_widget.php
 * ──────────────────────────────────────────────────────────────
 * Widget flottant "Guided Journey" — à inclure dans home.php
 * et profile.php APRÈS session_start() et require db.php :
 *
 *   <?php
 *   require_once '../../models/db.php';
 *   include 'partials/journey_widget.php';
 *   ?>
 *
 * Affichage : uniquement si l'utilisateur est connecté.
 */
if (!isset($_SESSION['user_id'])) return;

$uid = (int)$_SESSION['user_id'];

// Créer la ligne si elle n'existe pas
try {
    $pdo->prepare("INSERT IGNORE INTO user_journey (id_client) VALUES (?)")->execute([$uid]);
    $jStmt = $pdo->prepare("SELECT * FROM user_journey WHERE id_client=?");
    $jStmt->execute([$uid]);
    $j = $jStmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    return; // Table pas encore créée → ne rien afficher
}

// Auto-valider step_profile si téléphone + adresse renseignés
if (!$j['step_profile']) {
    $pCheck = $pdo->prepare("SELECT telephone, adresse FROM client WHERE id_client=?");
    $pCheck->execute([$uid]);
    $pData = $pCheck->fetch(PDO::FETCH_ASSOC);
    if (!empty($pData['telephone']) && !empty($pData['adresse'])) {
        $pdo->prepare("UPDATE user_journey SET step_profile=1, score=score+1 WHERE id_client=? AND step_profile=0")->execute([$uid]);
        $j['step_profile'] = 1;
        $j['score']++;
    }
}

$steps = [
    ['key'=>'step_profile',  'icon'=>'👤', 'label'=>'Compléter le profil',   'tip'=>'Ajoutez votre téléphone et adresse.',     'url'=>'profile.php',    'color'=>'#6366f1'],
    ['key'=>'step_diagnostic','icon'=>'🔍', 'label'=>'Explorer les services',  'tip'=>'Visitez la section Services.',            'url'=>'#services',      'color'=>'#3b82f6'],
    ['key'=>'step_garage', 'icon'=>'🏢', 'label'=>'Consulter un garage', 'tip'=>'Découvrez nos garages partenaires.', 'url'=>'/Esprit-PI-2PREPA-2026-EntreAUtous/views/FrontOffice/front.php', 'color'=>'#10b981'],
    ['key'=>'step_vehicle',  'icon'=>'🚗', 'label'=>'Ajouter un véhicule',    'tip'=>'Enregistrez votre voiture.',              'url'=>'#',              'color'=>'#f59e0b'],
    ['key'=>'step_rdv',      'icon'=>'📅', 'label'=>'Prendre rendez-vous',    'tip'=>'Réservez un créneau en ligne.',           'url'=>'#',              'color'=>'#ef4444'],
    ['key'=>'step_message',  'icon'=>'✉️', 'label'=>'Envoyer un message',     'tip'=>'Contactez un technicien directement.',   'url'=>'#',              'color'=>'#8b5cf6'],
];

$total   = count($steps);
$score   = (int)($j['score'] ?? 0);
$pct     = $total > 0 ? round($score / $total * 100) : 0;

// Prochaine étape non faite
$nextStep = null;
foreach ($steps as $s) { if (!$j[$s['key']]) { $nextStep = $s; break; } }

// Niveau selon score
$levels = [
    ['min'=>0,'name'=>'Débutant',    'emoji'=>'🌱','color'=>'#94a3b8'],
    ['min'=>2,'name'=>'Explorateur', 'emoji'=>'🔍','color'=>'#3b82f6'],
    ['min'=>4,'name'=>'Conducteur',  'emoji'=>'🚗','color'=>'#f59e0b'],
    ['min'=>6,'name'=>'Expert',      'emoji'=>'🏆','color'=>'#10b981'],
];
$level = $levels[0];
foreach ($levels as $lv) { if ($score >= $lv['min']) $level = $lv; }
?>

<!-- ════════════════════════════════════════════════════════════
     WIDGET GUIDED JOURNEY — flottant bas-droite
════════════════════════════════════════════════════════════ -->
<div id="jWidget" style="
    position:fixed; bottom:24px; right:24px; z-index:1060;
    width:320px; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    filter:drop-shadow(0 12px 32px rgba(0,0,0,.18));
">

    <!-- ── Header cliquable ── -->
    <div id="jHeader" onclick="jToggle()" style="
        background:linear-gradient(135deg,#6366f1 0%,#3b82f6 100%);
        border-radius:16px; padding:14px 16px; cursor:pointer;
        display:flex; align-items:center; gap:12px; user-select:none;
        transition:opacity .15s;
    " onmouseover="this.style.opacity='.92'" onmouseout="this.style.opacity='1'">

        <!-- Icône niveau -->
        <div style="
            width:44px; height:44px; border-radius:12px;
            background:rgba(255,255,255,.2);
            display:flex; align-items:center; justify-content:center;
            font-size:20px; flex-shrink:0;
        "><?= $level['emoji'] ?></div>

        <!-- Texte + barre -->
        <div style="flex:1; min-width:0;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                <span style="font-weight:700; font-size:.9rem; color:white;">Mon Parcours</span>
                <span style="font-size:.72rem; color:rgba(255,255,255,.85); font-weight:600;"><?= $score ?>/<?= $total ?> · <?= $pct ?>%</span>
            </div>
            <!-- Barre de progression -->
            <div style="height:6px; background:rgba(255,255,255,.25); border-radius:99px; overflow:hidden;">
                <div id="jHeaderBar" style="height:100%; width:<?= $pct ?>%; background:white; border-radius:99px; transition:width .5s ease;"></div>
            </div>
        </div>

        <!-- Flèche -->
        <div id="jArrow" style="color:rgba(255,255,255,.7); font-size:12px; transition:transform .3s; flex-shrink:0;">▼</div>
    </div>

    <!-- ── Corps (accordéon) ── -->
    <div id="jBody" style="
        background:white; border:1px solid #e5e7eb; border-top:none;
        border-radius:0 0 16px 16px; overflow:hidden;
        max-height:0; transition:max-height .4s ease;
    ">
        <div style="padding:16px;">

            <?php if ($score >= $total): ?>
            <!-- Complété ! -->
            <div style="text-align:center; padding:12px 0;">
                <div style="font-size:2.5rem; margin-bottom:8px;">🎉</div>
                <div style="font-weight:700; color:#059669; font-size:.95rem;">Parcours complété !</div>
                <div style="font-size:.75rem; color:#9ca3af; margin-top:4px;">Vous maîtrisez la plateforme.</div>
            </div>

            <?php else: ?>
            <!-- Prochaine étape en vedette -->
            <?php if ($nextStep): ?>
            <div style="
                background:linear-gradient(135deg,rgba(99,102,241,.07),rgba(59,130,246,.07));
                border:1px solid rgba(99,102,241,.2); border-radius:12px;
                padding:12px 14px; margin-bottom:14px;
            ">
                <div style="font-size:.67rem; color:#6366f1; font-weight:700; letter-spacing:.06em; margin-bottom:4px;">⚡ PROCHAINE ÉTAPE</div>
                <div style="font-size:.88rem; font-weight:700; color:#1e293b; margin-bottom:3px;">
                    <?= $nextStep['icon'] ?> <?= htmlspecialchars($nextStep['label']) ?>
                </div>
                <div style="font-size:.75rem; color:#64748b; margin-bottom:10px;"><?= htmlspecialchars($nextStep['tip']) ?></div>
                <a href="<?= $nextStep['url'] ?>"
                   onclick="AT_Tracker && AT_Tracker.markStep('<?= $nextStep['key'] ?>')"
                   style="
                    display:inline-block; background:#6366f1; color:white;
                    text-decoration:none; padding:6px 16px; border-radius:8px;
                    font-size:.78rem; font-weight:700;
                   ">Commencer →</a>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <!-- Liste toutes les étapes -->
            <div id="jStepList">
            <?php foreach ($steps as $i => $s):
                $done = !empty($j[$s['key']]);
            ?>
            <div id="js_<?= $s['key'] ?>"
                 onclick="jStepClick('<?= $s['key'] ?>','<?= $s['url'] ?>')"
                 style="
                    display:flex; align-items:center; gap:10px;
                    padding:8px 10px; border-radius:10px; margin-bottom:4px;
                    background:<?= $done ? '#f0fdf4' : '#f8fafc' ?>;
                    border:1px solid <?= $done ? '#bbf7d0' : '#e2e8f0' ?>;
                    cursor:pointer; transition:all .18s;
                 "
                 onmouseover="this.style.borderColor='<?= $s['color'] ?>'; this.style.background='<?= $done ? '#f0fdf4' : '#f0f9ff' ?>'"
                 onmouseout="this.style.borderColor='<?= $done ? '#bbf7d0' : '#e2e8f0' ?>'; this.style.background='<?= $done ? '#f0fdf4' : '#f8fafc' ?>'"
                 title="<?= htmlspecialchars($s['tip']) ?>">

                <!-- Dot état -->
                <div style="
                    width:30px; height:30px; border-radius:50%; flex-shrink:0;
                    background:<?= $done ? '#16a34a' : '#e2e8f0' ?>;
                    color:<?= $done ? 'white' : '#94a3b8' ?>;
                    display:flex; align-items:center; justify-content:center;
                    font-size:<?= $done ? '13px' : '11px' ?>; font-weight:700;
                "><?= $done ? '✓' : ($i+1) ?></div>

                <!-- Label -->
                <div style="flex:1;">
                    <div style="
                        font-size:.8rem; font-weight:<?= $done ? 500 : 600 ?>;
                        color:<?= $done ? '#94a3b8' : '#1e293b' ?>;
                        text-decoration:<?= $done ? 'line-through' : 'none' ?>;
                    "><?= $s['icon'] ?> <?= htmlspecialchars($s['label']) ?></div>
                </div>

                <!-- Badge -->
                <span style="
                    font-size:.67rem; font-weight:700; padding:2px 8px; border-radius:99px;
                    background:<?= $done ? '#dcfce7' : '#f1f5f9' ?>;
                    color:<?= $done ? '#16a34a' : '#94a3b8' ?>;
                "><?= $done ? '✓' : ($i+1).'/'.$total ?></span>
            </div>
            <?php endforeach; ?>
            </div>

            <!-- Footer niveau + reset -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-top:12px; padding-top:10px; border-top:1px solid #f1f5f9;">
                <div style="display:flex; align-items:center; gap:6px;">
                    <span style="font-size:16px;"><?= $level['emoji'] ?></span>
                    <span style="font-size:.76rem; font-weight:700; color:<?= $level['color'] ?>;"><?= $level['name'] ?></span>
                </div>
                <button onclick="jReset()" style="font-size:.67rem; color:#cbd5e1; background:none; border:none; cursor:pointer; text-decoration:underline;">Réinitialiser</button>
            </div>
        </div>
    </div>
</div>

<script>
// ── Ouvrir/fermer le widget ───────────────────────────────────
var jOpen = localStorage.getItem('jOpen') !== 'false';
var jPct  = <?= $pct ?>;

function jToggle() {
    var body  = document.getElementById('jBody');
    var arrow = document.getElementById('jArrow');
    var isNowOpen = parseInt(body.style.maxHeight || '0') > 0;
    if (isNowOpen) {
        body.style.maxHeight = '0';
        arrow.style.transform = 'rotate(0deg)';
        localStorage.setItem('jOpen', 'false');
    } else {
        body.style.maxHeight = (body.scrollHeight + 300) + 'px';
        arrow.style.transform = 'rotate(180deg)';
        localStorage.setItem('jOpen', 'true');
    }
}

// Ouvrir si < 100% ET pas explicitement fermé
if (jOpen || (jPct < 100 && localStorage.getItem('jOpen') === null)) {
    setTimeout(function() {
        var body  = document.getElementById('jBody');
        var arrow = document.getElementById('jArrow');
        body.style.maxHeight = (body.scrollHeight + 300) + 'px';
        arrow.style.transform = 'rotate(180deg)';
    }, 700);
}

// ── Clic sur une étape ────────────────────────────────────────
function jStepClick(key, url) {
    // Marquer via tracking.js si disponible, sinon fetch direct
    if (window.AT_Tracker) {
        AT_Tracker.markStep(key, function(data) { if (data && data.journey) jUpdateUI(data.journey); });
    } else {
        fetch('/autout/controller/JourneyController.php', {
            method:'POST', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({action:'mark_step', step:key})
        }).then(function(r){return r.json();}).then(function(d){ if(d.journey) jUpdateUI(d.journey); }).catch(function(){});
    }
    if (url && url !== '#') setTimeout(function(){ window.location.href = url; }, 180);
}

// ── Réinitialiser ─────────────────────────────────────────────
function jReset() {
    if (!confirm('Réinitialiser le parcours ?')) return;
    fetch('/autout/controller/JourneyController.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({action:'reset'})
    }).then(function(){location.reload();}).catch(function(){location.reload();});
}

// ── Mettre à jour l'UI sans reload ───────────────────────────
function jUpdateUI(j) {
    var total = 6;
    var score = j.score || 0;
    var pct   = Math.round(score / total * 100);

    // Barre header
    var bar = document.getElementById('jHeaderBar');
    if (bar) bar.style.width = pct + '%';

    // Chaque étape
    var keys = ['step_profile','step_diagnostic','step_garage','step_vehicle','step_rdv','step_message'];
    keys.forEach(function(k, i) {
        var el = document.getElementById('js_' + k);
        if (!el) return;
        var done = j[k];
        el.style.background  = done ? '#f0fdf4' : '#f8fafc';
        el.style.borderColor = done ? '#bbf7d0' : '#e2e8f0';
        var dot  = el.querySelector('div');
        var lbl  = el.querySelectorAll('div')[2];  // label text
        var bdg  = el.querySelector('span');
        if (dot) { dot.style.background=done?'#16a34a':'#e2e8f0'; dot.style.color=done?'white':'#94a3b8'; dot.textContent=done?'✓':(i+1); }
        if (lbl) { lbl.style.color=done?'#94a3b8':'#1e293b'; lbl.style.textDecoration=done?'line-through':'none'; }
        if (bdg) { bdg.style.background=done?'#dcfce7':'#f1f5f9'; bdg.style.color=done?'#16a34a':'#94a3b8'; bdg.textContent=done?'✓':(i+1)+'/'+total; }
    });
}

// ── Explorer section = marquer step_explore ──────────────────
// Déclenché quand l'utilisateur scrolle vers #services
if (typeof IntersectionObserver !== 'undefined') {
    var servicesSection = document.getElementById('services');
    if (servicesSection) {
        var obs = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
                if (e.isIntersecting) {
                    jStepClick('step_diagnostic', '#');
                    obs.disconnect();
                }
            });
        }, { threshold: 0.3 });
        obs.observe(servicesSection);
    }
}
</script>