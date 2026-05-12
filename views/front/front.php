<?php
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path'     => '/Esprit-PI-2PREPA-2026-EntreAUtous/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();
// Seul un client connecté peut accéder à la messagerie
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$currentUserId   = (int)$_SESSION['user_id'];
$currentUserName = htmlspecialchars(($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? ''));
$currentUserInit = strtoupper(substr(trim($_SESSION['prenom'] ?? $_SESSION['user'] ?? 'C'), 0, 1));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messagerie — AutoService</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;600;700;800;900&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════
   VARIABLES & RESET
═══════════════════════════════════════════════ */
:root {
  --red:       #e94560;
  --red-dk:    #c73652;
  --dark:      #1a1a2e;
  --dark2:     #16213e;
  --bg:        #f4f6fc;
  --white:     #ffffff;
  --border:    #e0e4eb;
  --txt:       #2d3748;
  --muted:     #718096;
  --radius:    12px;
  --shadow:    0 2px 16px rgba(0,0,0,.07);
  --shadow-lg: 0 8px 32px rgba(0,0,0,.12);
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Open Sans',sans-serif;background:var(--bg);color:var(--txt)}

/* ═══════════════════════════════════════════════
   NAVBAR
═══════════════════════════════════════════════ */
.navbar{background:var(--dark);padding:13px 0;position:sticky;top:0;z-index:1000;box-shadow:0 2px 20px rgba(0,0,0,.35)}
.nav-inner{max-width:1180px;margin:0 auto;padding:0 24px;display:flex;align-items:center;justify-content:space-between}
.brand{display:flex;align-items:center;gap:10px;text-decoration:none}
.brand-ico{width:38px;height:38px;background:var(--red);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:19px;color:#fff}
.brand-txt{font-family:'Raleway',sans-serif;font-size:19px;font-weight:800;color:#fff}
.brand-txt span{color:var(--red)}
.nav-links{display:flex;gap:4px}
.nav-links a{color:rgba(255,255,255,.65);text-decoration:none;font-family:'Raleway',sans-serif;font-size:13px;font-weight:700;padding:7px 13px;border-radius:7px;text-transform:uppercase;letter-spacing:.5px;transition:all .2s}
.nav-links a:hover,.nav-links a.active{background:rgba(255,255,255,.08);color:#fff}
.nav-links a.active{color:var(--red)}

/* ═══════════════════════════════════════════════
   HERO
═══════════════════════════════════════════════ */
.hero{background:linear-gradient(135deg,var(--dark) 0%,var(--dark2) 55%,#0f3460 100%);padding:52px 24px 36px;text-align:center;position:relative;overflow:hidden}
.hero::before{content:'';position:absolute;inset:0;background:url("data:image/svg+xml,%3Csvg width='60' height='60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='.025'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E")}
.hero-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(233,69,96,.15);border:1px solid rgba(233,69,96,.3);border-radius:20px;padding:5px 14px;font-size:12px;font-weight:700;color:var(--red);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px}
.hero h1{font-family:'Raleway',sans-serif;font-size:clamp(26px,4vw,40px);font-weight:900;color:#fff;margin-bottom:10px}
.hero h1 span{color:var(--red)}
.hero p{color:rgba(255,255,255,.6);font-size:14.5px;max-width:480px;margin:0 auto}

/* ═══════════════════════════════════════════════
   LAYOUT PRINCIPAL
═══════════════════════════════════════════════ */
.page{max-width:1180px;margin:0 auto;padding:28px 20px}

/* ═══════════════════════════════════════════════
   BARRE UTILISATEUR
═══════════════════════════════════════════════ */
.user-bar{background:var(--white);border-radius:var(--radius);padding:14px 20px;box-shadow:var(--shadow);margin-bottom:22px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.user-bar label{font-size:13px;font-weight:700;color:var(--muted);white-space:nowrap}
.sim-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,193,7,.1);border:1px solid rgba(255,193,7,.3);border-radius:7px;padding:3px 10px;font-size:11px;font-weight:700;color:#a07c00}
.sim-badge i{font-size:12px}
.user-select-wrap{position:relative;display:flex;align-items:center;gap:0}
.pill-av{width:28px;height:28px;border-radius:50% 0 0 50%;background:var(--red);color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;z-index:1;position:relative;left:1px}
.user-select{border:1.5px solid rgba(233,69,96,.3);border-radius:0 8px 8px 0;padding:6px 12px 6px 10px;font-size:13px;font-weight:700;font-family:'Open Sans',sans-serif;color:var(--red);background:rgba(233,69,96,.05);cursor:pointer;outline:none;transition:all .2s;min-width:160px}
.user-select:focus{border-color:var(--red);background:#fff}
.btn-new{margin-left:auto;background:var(--red);color:#fff;border:none;border-radius:9px;padding:9px 18px;font-family:'Raleway',sans-serif;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(233,69,96,.3);transition:all .2s}
.btn-new:hover{background:var(--red-dk);transform:translateY(-1px)}

/* ═══════════════════════════════════════════════
   ONGLETS PAGE
═══════════════════════════════════════════════ */
.page-tabs{display:flex;gap:4px;background:var(--white);border-radius:var(--radius);padding:5px;box-shadow:var(--shadow);margin-bottom:22px;width:fit-content}
.ptab{padding:8px 20px;border-radius:8px;border:none;background:transparent;font-family:'Raleway',sans-serif;font-size:13px;font-weight:700;color:var(--muted);cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s}
.ptab.active{background:var(--red);color:#fff;box-shadow:0 4px 14px rgba(233,69,96,.35)}
.ptab:hover:not(.active){background:var(--bg)}

/* ═══════════════════════════════════════════════
   LAYOUT MESSENGER
═══════════════════════════════════════════════ */
.messenger{display:grid;grid-template-columns:300px 1fr;gap:18px;height:610px}

/* ── Liste conversations ── */
.conv-panel{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);display:flex;flex-direction:column;overflow:hidden}
.cp-head{padding:16px 16px 12px;border-bottom:1px solid var(--border)}
.cp-head h4{font-family:'Raleway',sans-serif;font-size:15px;font-weight:800;margin-bottom:11px;display:flex;align-items:center;gap:7px}
.cp-head h4 i{color:var(--red)}
.search-box{position:relative}
.search-box input{width:100%;border:1.5px solid var(--border);border-radius:22px;padding:7px 12px 7px 34px;font-size:12.5px;font-family:'Open Sans',sans-serif;outline:none;transition:border .2s}
.search-box input:focus{border-color:var(--red)}
.search-box i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:13px}
.conv-list{flex:1;overflow-y:auto}
.conv-item{padding:12px 16px;border-bottom:1px solid var(--border);cursor:pointer;display:flex;align-items:center;gap:11px;transition:background .15s}
.conv-item:hover{background:#fafbff}
.conv-item.active{background:rgba(233,69,96,.05);border-left:3px solid var(--red)}
.c-av{width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--dark));display:flex;align-items:center;justify-content:center;font-family:'Raleway',sans-serif;font-size:16px;font-weight:800;color:#fff;flex-shrink:0}
.c-info{flex:1;min-width:0}
.c-name{font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.c-last{font-size:11.5px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-top:2px}
.c-meta{display:flex;flex-direction:column;align-items:flex-end;gap:3px;flex-shrink:0}
.c-time{font-size:10.5px;color:var(--muted)}
.c-badge{background:var(--red);color:#fff;border-radius:10px;padding:1px 7px;font-size:10px;font-weight:700}
.cp-foot{padding:11px 16px;border-top:1px solid var(--border)}
.btn-conv{width:100%;background:var(--red);color:#fff;border:none;border-radius:9px;padding:9px;font-family:'Raleway',sans-serif;font-size:13px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;box-shadow:0 4px 12px rgba(233,69,96,.28);transition:all .2s}
.btn-conv:hover{background:var(--red-dk)}

/* ── Fenêtre chat ── */
.chat-win{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);display:flex;flex-direction:column;overflow:hidden}
.chat-empty{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);background:#f8f9fc}
.chat-empty i{font-size:58px;opacity:.18;margin-bottom:12px}
.chat-empty p{font-size:15px;font-weight:700}
.chat-empty span{font-size:13px;opacity:.7}
.chat-active{display:none;flex-direction:column;flex:1;height:100%}
.chat-active.show{display:flex}
.chat-hd{padding:14px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:13px;background:var(--white)}
.chat-hd .hd-av{width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--red),var(--dark));display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:800;color:#fff;font-family:'Raleway',sans-serif}
.chat-hd .hd-name{font-family:'Raleway',sans-serif;font-size:14px;font-weight:800}
.chat-hd .hd-sub{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px}
.online-dot{width:7px;height:7px;background:#28a745;border-radius:50%;display:inline-block}
.hd-actions{margin-left:auto;display:flex;gap:7px}
.ic-btn{width:31px;height:31px;border-radius:7px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:13px;transition:all .2s}
.ic-btn.edit{background:rgba(255,193,7,.12);color:#c59b00}
.ic-btn.del {background:rgba(233,69,96,.1);color:var(--red)}
.ic-btn.translate{background:rgba(23,162,184,.12);color:#0e7a94}
.ic-btn:hover{transform:scale(1.1)}

/* ── Messages ── */
.msgs-area{flex:1;overflow-y:auto;padding:18px;background:#f8f9fc;display:flex;flex-direction:column;gap:9px}
.date-sep{text-align:center;font-size:11px;color:var(--muted);margin:5px 0;position:relative}
.date-sep::before,.date-sep::after{content:'';position:absolute;top:50%;width:32%;height:1px;background:var(--border)}
.date-sep::before{left:0} .date-sep::after{right:0}
.bubble-wrap{display:flex;flex-direction:column}
.bubble-wrap.sent{align-items:flex-end}
.bubble-wrap.recv{align-items:flex-start}
.bubble-sender{font-size:11px;font-weight:700;color:var(--muted);margin-bottom:3px;padding:0 10px}
.bubble{max-width:62%;padding:9px 13px 7px;border-radius:15px;font-size:13.5px;line-height:1.5;word-break:break-word;position:relative}
.bubble.sent{background:var(--red);color:#fff;border-bottom-right-radius:4px}
.bubble.recv{background:var(--white);color:var(--txt);border-bottom-left-radius:4px;box-shadow:0 2px 7px rgba(0,0,0,.06)}
.bubble-time{font-size:10px;margin-top:4px;opacity:.65;text-align:right}
.bub-acts{position:absolute;top:-26px;display:flex;gap:4px;opacity:0;pointer-events:none;transition:opacity 0.15s ease 0.6s}
.bubble.sent  .bub-acts{right:0}
.bubble.recv  .bub-acts{left:0}
.bubble:hover .bub-acts{opacity:1;pointer-events:auto;transition:opacity 0.15s ease 0s}
.bab{width:22px;height:22px;border-radius:5px;border:none;background:#fff;box-shadow:0 1px 5px rgba(0,0,0,.12);cursor:pointer;font-size:10px;color:var(--muted);display:flex;align-items:center;justify-content:center}
.bab:hover{color:var(--red)}

/* ── Bulle image ── */
.bubble-img{max-width:220px;border-radius:10px;display:block;cursor:pointer;margin-bottom:3px}
.bubble-img:hover{opacity:.9}

/* ── Bulle vocal ── */
.voice-player{display:flex;align-items:center;gap:8px;padding:4px 0}
.voice-play-btn{width:30px;height:30px;border-radius:50%;border:none;background:rgba(255,255,255,.25);color:inherit;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;transition:all .2s;flex-shrink:0}
.bubble.sent .voice-play-btn{background:rgba(255,255,255,.2);color:#fff}
.bubble.recv .voice-play-btn{background:var(--red);color:#fff}
.voice-play-btn:hover{transform:scale(1.1)}
.voice-waveform{flex:1;height:24px;display:flex;align-items:center;gap:2px;overflow:hidden}
.wave-bar{width:3px;border-radius:2px;background:currentColor;opacity:.5;flex-shrink:0}
.voice-duration{font-size:11px;opacity:.7;flex-shrink:0;min-width:28px}

/* ── Zone de traduction ── */
.translate-bar{padding:8px 14px;background:rgba(23,162,184,.06);border-bottom:1px solid rgba(23,162,184,.15);display:none;align-items:center;gap:8px;flex-wrap:wrap}
.translate-bar.show{display:flex}
.tr-label{font-size:11.5px;font-weight:700;color:#0e7a94;white-space:nowrap}
.tr-lang-btn{padding:4px 12px;border-radius:14px;border:1.5px solid rgba(23,162,184,.3);background:transparent;font-size:12px;font-weight:700;color:#0e7a94;cursor:pointer;transition:all .2s}
.tr-lang-btn.active,.tr-lang-btn:hover{background:#0e7a94;color:#fff;border-color:#0e7a94}
.tr-close{margin-left:auto;background:none;border:none;color:var(--muted);cursor:pointer;font-size:14px;padding:2px}

/* ── Notification temps réel ── */
.realtime-dot{width:8px;height:8px;background:#28a745;border-radius:50%;display:inline-block;animation:pulse-dot 2s infinite}
@keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}
.new-msg-notif{background:rgba(233,69,96,.08);border:1px solid rgba(233,69,96,.2);border-radius:8px;padding:6px 14px;font-size:12px;font-weight:700;color:var(--red);display:none;align-items:center;gap:6px;cursor:pointer;transition:all .2s;margin:0 18px}
.new-msg-notif.show{display:flex}
.new-msg-notif:hover{background:rgba(233,69,96,.12)}

/* ── Input zone ── */
.input-zone{padding:12px 18px;border-top:1px solid var(--border);background:var(--white)}

/* ── Suggestions IA ── */
.ai-suggestions{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:9px;min-height:0;transition:all .3s}
.ai-chip{padding:5px 12px;border-radius:16px;border:1.5px solid rgba(233,69,96,.25);background:rgba(233,69,96,.05);font-size:12px;font-weight:600;color:var(--red);cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:5px;animation:chipIn .2s ease}
@keyframes chipIn{from{opacity:0;transform:translateY(4px)}to{opacity:1;transform:translateY(0)}}
.ai-chip:hover{background:var(--red);color:#fff;border-color:var(--red)}
.ai-chip i{font-size:11px;opacity:.7}
.ai-chip.loading{opacity:.5;pointer-events:none}
.ai-label{font-size:10.5px;font-weight:700;color:var(--muted);display:flex;align-items:center;gap:4px;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px}
.ai-label i{color:var(--red)}

/* ── Barre d'outils input ── */
.input-toolbar{display:flex;align-items:center;gap:8px;margin-bottom:8px}
.tool-btn{width:32px;height:32px;border-radius:8px;border:1.5px solid var(--border);background:var(--bg);color:var(--muted);font-size:14px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .2s;position:relative}
.tool-btn:hover{border-color:var(--red);color:var(--red);background:rgba(233,69,96,.06)}
.tool-btn.active{background:var(--red);color:#fff;border-color:var(--red)}
.tool-btn input[type=file]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%}

/* ── Enregistrement vocal ── */
.recording-indicator{display:none;align-items:center;gap:8px;padding:8px 14px;background:rgba(233,69,96,.06);border-radius:10px;margin-bottom:8px}
.recording-indicator.show{display:flex}
.rec-dot{width:9px;height:9px;background:var(--red);border-radius:50%;animation:blink .8s infinite}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0}}
.rec-time{font-size:13px;font-weight:700;color:var(--red)}
.rec-cancel{margin-left:auto;background:none;border:none;color:var(--muted);cursor:pointer;font-size:13px;font-weight:700;padding:3px 8px;border-radius:6px;transition:all .2s}
.rec-cancel:hover{color:var(--red)}
.rec-send{background:var(--red);color:#fff;border:none;border-radius:7px;padding:5px 14px;font-size:12px;font-weight:700;cursor:pointer;transition:all .2s}
.rec-send:hover{background:var(--red-dk)}

/* ── Aperçu image avant envoi ── */
.img-preview-wrap{display:none;position:relative;margin-bottom:8px}
.img-preview-wrap.show{display:inline-block}
.img-preview{max-height:80px;border-radius:8px;border:2px solid var(--border)}
.img-preview-rm{position:absolute;top:-6px;right:-6px;width:18px;height:18px;border-radius:50%;background:var(--red);color:#fff;border:none;cursor:pointer;font-size:10px;display:flex;align-items:center;justify-content:center}

.input-row{display:flex;align-items:flex-end;gap:10px}
.input-wrap{flex:1}
.msg-input{width:100%;border:1.5px solid var(--border);border-radius:22px;padding:9px 17px;font-size:13.5px;font-family:'Open Sans',sans-serif;resize:none;min-height:42px;max-height:110px;outline:none;transition:border .2s}
.msg-input:focus{border-color:var(--red)}
.msg-input.is-invalid{border-color:var(--red)!important}
.char-count{font-size:11px;color:var(--muted);text-align:right;margin-top:3px}
.send-btn{width:42px;height:42px;border-radius:50%;background:var(--red);border:none;color:#fff;font-size:17px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(233,69,96,.32);transition:all .2s;flex-shrink:0}
.send-btn:hover{background:var(--red-dk);transform:scale(1.06)}

/* ═══════════════════════════════════════════════
   GRILLE DISCUSSIONS
═══════════════════════════════════════════════ */
.disc-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:16px}
.disc-card{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);padding:18px;transition:all .22s;border:1.5px solid transparent;cursor:pointer}
.disc-card:hover{box-shadow:var(--shadow-lg);border-color:rgba(233,69,96,.18);transform:translateY(-2px)}
.dc-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.dc-subj{font-family:'Raleway',sans-serif;font-size:14px;font-weight:800}
.dc-id{font-size:11px;color:var(--muted);background:var(--bg);border-radius:5px;padding:2px 8px}
.dc-garage{display:flex;align-items:center;gap:7px;margin-bottom:10px;font-size:13px;font-weight:600}
.dc-garage i{color:var(--red)}
.dc-foot{display:flex;align-items:center;justify-content:space-between}
.dc-count{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:4px}
.dc-acts{display:flex;gap:5px}
.dc-btn{width:28px;height:28px;border-radius:6px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s}
.dc-btn.chat{background:rgba(233,69,96,.08);color:var(--red)}
.dc-btn.edit{background:rgba(255,193,7,.1);color:#c59b00}
.dc-btn.del {background:rgba(233,69,96,.08);color:var(--red)}
.dc-btn:hover{transform:scale(1.1)}

/* ═══════════════════════════════════════════════
   MODAL
═══════════════════════════════════════════════ */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.48);z-index:2000;align-items:center;justify-content:center}
.overlay.show{display:flex;animation:fadeIn .2s}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.modal-box{background:#fff;border-radius:16px;width:100%;max-width:470px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:slideUp .25s}
@keyframes slideUp{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:translateY(0)}}
.m-head{padding:20px 22px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.m-head h5{font-family:'Raleway',sans-serif;font-size:16px;font-weight:800;margin:0;display:flex;align-items:center;gap:8px}
.m-head h5 i{color:var(--red)}
.m-close{width:30px;height:30px;border-radius:7px;border:none;background:var(--bg);cursor:pointer;font-size:15px;color:var(--muted);display:flex;align-items:center;justify-content:center;transition:all .2s}
.m-close:hover{background:rgba(233,69,96,.1);color:var(--red)}
.m-body{padding:20px 22px}
.m-foot{padding:15px 22px;border-top:1px solid var(--border);display:flex;gap:9px;justify-content:flex-end}

/* ── Champs formulaire ── */
.fg{margin-bottom:16px}
.flabel{display:block;font-size:11.5px;font-weight:700;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.finput{width:100%;border:1.5px solid var(--border);border-radius:9px;padding:9px 13px;font-size:13.5px;font-family:'Open Sans',sans-serif;color:var(--txt);background:var(--bg);outline:none;transition:border .2s,box-shadow .2s}
.finput:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(233,69,96,.1);background:#fff}
.finput.is-invalid{border-color:var(--red)!important;background:#fff9f9}
.finput.is-valid{border-color:#28a745}
.field-error{display:none;font-size:11.5px;color:var(--red);font-weight:600;margin-top:4px}

/* ── Boutons ── */
.btn-pri{background:var(--red);color:#fff;border:none;border-radius:9px;padding:10px 22px;font-family:'Raleway',sans-serif;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(233,69,96,.28);transition:all .2s}
.btn-pri:hover{background:var(--red-dk);transform:translateY(-1px)}
.btn-sec{background:var(--bg);color:var(--muted);border:1.5px solid var(--border);border-radius:9px;padding:10px 22px;font-family:'Raleway',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s}
.btn-sec:hover{border-color:var(--red);color:var(--red)}

/* ── Modal image plein écran ── */
.img-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9998;align-items:center;justify-content:center;cursor:zoom-out}
.img-modal-overlay.show{display:flex}
.img-modal-overlay img{max-width:92vw;max-height:88vh;border-radius:10px;box-shadow:0 20px 60px rgba(0,0,0,.5)}

/* ═══════════════════════════════════════════════
   TOAST
═══════════════════════════════════════════════ */
#toast-container{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:9px}
.toast-item{background:#fff;border-radius:11px;padding:12px 16px;box-shadow:0 8px 28px rgba(0,0,0,.14);display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600;min-width:240px;border-left:4px solid;animation:slideIn .3s}
.toast-success{border-color:#28a745}.toast-error{border-color:var(--red)}.toast-info{border-color:#17a2b8}
.toast-icon{font-size:16px}
.toast-success .toast-icon{color:#28a745}.toast-error .toast-icon{color:var(--red)}.toast-info .toast-icon{color:#17a2b8}
.toast-hide{animation:fadeOut .4s forwards}
@keyframes slideIn{from{opacity:0;transform:translateX(36px)}to{opacity:1;transform:translateX(0)}}
@keyframes fadeOut{to{opacity:0;transform:translateX(36px)}}

/* ── Spinner ── */
.loading-spinner{width:28px;height:28px;border:3px solid rgba(233,69,96,.2);border-top-color:var(--red);border-radius:50%;animation:spin .7s linear infinite;margin:30px auto}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Empty state ── */
.empty-state{text-align:center;padding:40px 20px;color:var(--muted)}
.empty-state i{font-size:42px;opacity:.22;display:block;margin-bottom:10px}
.empty-state p{font-size:14px;font-weight:600}

/* ── Traduction inline ── */
.bubble-translation{font-size:12px;font-style:italic;opacity:.8;margin-top:5px;padding-top:5px;border-top:1px solid rgba(255,255,255,.2)}
.bubble.recv .bubble-translation{border-top-color:rgba(0,0,0,.08);opacity:.65}

/* Responsive */
@media(max-width:860px){
  .messenger{grid-template-columns:1fr;height:auto}
  .conv-panel{height:280px}
  .chat-win{height:420px}
}
</style>
</head>
<body>

<!-- ════ NAVBAR ════ -->
<nav class="navbar">
  <div class="nav-inner">
    <a class="brand" href="#">
      <div class="brand-ico"><i class="bi bi-car-front"></i></div>
      <div class="brand-txt">Auto<span>Service</span></div>
    </a>
    <div class="nav-links">
      <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/front/home.php">Accueil</a>
      <a href="#" class="active">Messagerie</a>
      <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/front/profile.php">Mon Compte</a>
    </div>
  </div>
</nav>

<!-- ════ HERO ════ -->
<div class="hero">
  <div class="hero-badge"><i class="bi bi-chat-dots-fill"></i> Messagerie</div>
  <h1>Contactez votre <span>Garage</span></h1>
  <p>Posez vos questions, prenez rendez-vous et suivez vos échanges en temps réel.</p>
</div>

<!-- ════ PAGE ════ -->
<div class="page">

  <div class="user-bar">
    <label><i class="bi bi-person-circle" style="color:var(--red)"></i> Connecté en tant que :</label>
    <div class="user-select-wrap">
      <div class="pill-av" id="pill-av"><?= $currentUserInit ?></div>
      <span style="padding:6px 14px;font-size:13px;font-weight:700;color:var(--red)"><?= $currentUserName ?></span>
    </div>
    <a href="home.php" style="font-size:12px;color:var(--muted);text-decoration:none;margin-left:auto;display:flex;align-items:center;gap:4px"><i class="bi bi-arrow-left"></i> Retour</a>
    <button class="btn-new" onclick="openNewDiscModal()">
      <i class="bi bi-plus-lg"></i> Nouvelle Discussion
    </button>
  </div>

  <!-- Onglets -->
  <div class="page-tabs">
    <button class="ptab active" id="ptab-chat" onclick="switchTab('chat')">
      <i class="bi bi-chat-left-text"></i> Chat
    </button>
    <button class="ptab" id="ptab-grid" onclick="switchTab('grid')">
      <i class="bi bi-grid-3x3-gap"></i> Toutes les discussions
    </button>
  </div>

  <!-- ════ VUE CHAT ════ -->
  <div id="view-chat">
    <div class="messenger">

      <!-- Panel gauche -->
      <div class="conv-panel">
        <div class="cp-head">
          <h4><i class="bi bi-chat-dots"></i> Mes conversations</h4>
          <div class="search-box">
            <i class="bi bi-search"></i>
            <input type="text" id="conv-search" placeholder="Rechercher..." onkeyup="filterConvs(this.value)">
          </div>
        </div>
        <div class="conv-list" id="conv-list">
          <div class="loading-spinner"></div>
        </div>
        <div class="cp-foot">
          <button class="btn-conv" onclick="openNewDiscModal()">
            <i class="bi bi-plus-circle"></i> Nouvelle Discussion
          </button>
        </div>
      </div>

      <!-- Fenêtre chat -->
      <div class="chat-win">
        <div class="chat-empty" id="chat-empty">
          <i class="bi bi-chat-square-text"></i>
          <p>Sélectionnez une conversation</p>
          <span>ou créez-en une nouvelle</span>
        </div>
        <div class="chat-active" id="chat-active">
          <!-- Header -->
          <div class="chat-hd">
            <div class="hd-av" id="hd-av">?</div>
            <div>
              <div class="hd-name" id="hd-name">—</div>
              <div class="hd-sub">
                <span class="realtime-dot"></span>&nbsp;
                <span id="hd-sub">—</span>
              </div>
            </div>
            <div class="hd-actions">
              <button class="ic-btn translate" title="Traduire la conversation" onclick="toggleTranslateBar()"><i class="bi bi-translate"></i></button>
              <button class="ic-btn edit" title="Modifier la discussion" onclick="editCurrentDisc()"><i class="bi bi-pencil"></i></button>
              <button class="ic-btn del"  title="Supprimer la discussion" onclick="delCurrentDisc()"><i class="bi bi-trash"></i></button>
            </div>
          </div>

          <!-- Barre de traduction -->
          <div class="translate-bar" id="translate-bar">
            <span class="tr-label"><i class="bi bi-translate"></i> Traduire en :</span>
            <button class="tr-lang-btn active" id="tr-fr" onclick="setTranslateLang('fr')">🇫🇷 Français</button>
            <button class="tr-lang-btn" id="tr-ar" onclick="setTranslateLang('ar')">🇹🇳 Arabe</button>
            <button class="tr-lang-btn" id="tr-en" onclick="setTranslateLang('en')">🇬🇧 Anglais</button>
            <button class="tr-close" onclick="toggleTranslateBar()" title="Fermer"><i class="bi bi-x"></i></button>
          </div>

          <!-- Notification nouveaux messages -->
          <div class="new-msg-notif" id="new-msg-notif" onclick="scrollToBottom()">
            <i class="bi bi-arrow-down-circle"></i> Nouveaux messages — cliquer pour voir
          </div>

          <!-- Messages -->
          <div class="msgs-area" id="msgs-area"></div>

          <!-- Input zone -->
          <div class="input-zone">

            <!-- Indicateur d'enregistrement vocal -->
            <div class="recording-indicator" id="recording-indicator">
              <span class="rec-dot"></span>
              <span class="rec-time" id="rec-time">0:00</span>
              <span style="font-size:12px;color:var(--muted);margin-left:4px">Enregistrement en cours…</span>
              <button class="rec-cancel" onclick="cancelRecording()"><i class="bi bi-x"></i> Annuler</button>
              <button class="rec-send" onclick="stopAndSendVoice()"><i class="bi bi-send"></i> Envoyer</button>
            </div>

            <!-- Aperçu image -->
            <div class="img-preview-wrap" id="img-preview-wrap">
              <img id="img-preview" class="img-preview" src="" alt="">
              <button class="img-preview-rm" onclick="removeImgPreview()"><i class="bi bi-x"></i></button>
            </div>

            <!-- Suggestions IA -->
            <div id="ai-suggestions-area" style="display:none">
              <div class="ai-label"><i class="bi bi-stars"></i> Suggestions IA automobile</div>
              <div class="ai-suggestions" id="ai-chips"></div>
            </div>

            <!-- Barre d'outils -->
            <div class="input-toolbar">
              <button class="tool-btn" id="voice-btn" title="Message vocal" onclick="toggleVoiceRecording()">
                <i class="bi bi-mic"></i>
              </button>
              <button class="tool-btn" title="Envoyer une image" style="position:relative;overflow:hidden">
                <i class="bi bi-image"></i>
                <input type="file" accept="image/*" onchange="handleImageSelect(event)" title="Choisir une image">
              </button>
              <button class="tool-btn" id="ai-toggle-btn" title="Suggestions IA" onclick="toggleAISuggestions()">
                <i class="bi bi-stars"></i>
              </button>
            </div>

            <div class="input-row">
              <div class="input-wrap">
                <textarea class="msg-input" id="msg-input" rows="1"
                  placeholder="Écrire un message…"
                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg();}"
                  oninput="onMsgInput(this)"></textarea>
                <div class="char-count" id="char-count">0/1000</div>
              </div>
              <button class="send-btn" onclick="sendMsg()" title="Envoyer"><i class="bi bi-send"></i></button>
            </div>
            <div style="position:relative">
              <span class="field-error" id="msg-error" style="display:none"></span>
            </div>
          </div><!-- /input-zone -->
        </div><!-- /chat-active -->
      </div><!-- /chat-win -->
    </div><!-- /messenger -->
  </div><!-- /view-chat -->

  <!-- ════ VUE GRILLE ════ -->
  <div id="view-grid" style="display:none">
    <div class="disc-grid" id="disc-grid">
      <div class="loading-spinner" style="grid-column:1/-1"></div>
    </div>
  </div>

</div><!-- /page -->

<!-- ════ TOAST ════ -->
<div id="toast-container"></div>

<!-- ════ MODAL IMAGE PLEIN ÉCRAN ════ -->
<div class="img-modal-overlay" id="img-modal" onclick="closeModal('img-modal')">
  <img id="img-modal-src" src="" alt="">
</div>

<!-- ════ MODAL : Nouvelle / Modifier Discussion ════ -->
<div class="overlay" id="modal-disc">
  <div class="modal-box">
    <div class="m-head">
      <h5><i class="bi bi-chat-dots"></i> <span id="md-title">Nouvelle Discussion</span></h5>
      <button class="m-close" onclick="closeModal('modal-disc')"><i class="bi bi-x"></i></button>
    </div>
    <div class="m-body">
      <input type="hidden" id="md-id">
      <input type="hidden" id="md-uid">
      <div class="fg">
        <label class="flabel">Garage à contacter *</label>
        <select class="finput" id="md-garage">
          <option value="">— Choisir un garage —</option>
        </select>
        <span class="field-error" id="err-garage"></span>
      </div>
      <div class="fg">
        <label class="flabel">Sujet / Objet *</label>
        <input type="text" class="finput" id="md-objet" placeholder="Ex: Problème moteur, Rendez-vous…">
        <span class="field-error" id="err-objet"></span>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn-sec" onclick="closeModal('modal-disc')">Annuler</button>
      <button class="btn-pri" onclick="saveDisc()"><i class="bi bi-check-lg"></i> <span id="md-btn">Créer</span></button>
    </div>
  </div>
</div>

<!-- ════ MODAL : Modifier Message ════ -->
<div class="overlay" id="modal-edit-msg">
  <div class="modal-box">
    <div class="m-head">
      <h5><i class="bi bi-pencil"></i> Modifier le message</h5>
      <button class="m-close" onclick="closeModal('modal-edit-msg')"><i class="bi bi-x"></i></button>
    </div>
    <div class="m-body">
      <input type="hidden" id="em-id">
      <div class="fg">
        <label class="flabel">Contenu *</label>
        <textarea class="finput" id="em-contenu" style="min-height:90px"></textarea>
        <span class="field-error" id="em-error"></span>
      </div>
    </div>
    <div class="m-foot">
      <button class="btn-sec" onclick="closeModal('modal-edit-msg')">Annuler</button>
      <button class="btn-pri" onclick="saveEditMsg()"><i class="bi bi-check-lg"></i> Enregistrer</button>
    </div>
  </div>
</div>

<!-- ════ MODAL : Confirmation Suppression ════ -->
<div class="overlay" id="modal-del">
  <div class="modal-box" style="max-width:380px;text-align:center">
    <div class="m-head" style="border:none;padding-bottom:0">
      <h5 style="opacity:0">_</h5>
      <button class="m-close" onclick="closeModal('modal-del')"><i class="bi bi-x"></i></button>
    </div>
    <div class="m-body">
      <i class="bi bi-exclamation-triangle-fill" style="font-size:44px;color:var(--red);display:block;margin-bottom:12px"></i>
      <h5 style="font-family:'Raleway',sans-serif;font-weight:800;font-size:17px;margin-bottom:8px">Confirmer la suppression</h5>
      <p id="del-text" style="color:var(--muted);font-size:13px"></p>
    </div>
    <div class="m-foot" style="justify-content:center">
      <button class="btn-sec" onclick="closeModal('modal-del')">Annuler</button>
      <button class="btn-pri" id="del-confirm">Supprimer</button>
    </div>
  </div>
</div>

<!-- ════ SCRIPTS ════ -->

<script src="../../assets/js/api.js"></script>
<script src="../../assets/js/validation.js"></script>
<script>
'use strict';
const API_URL = '../../Controller/MessageController.php';
/* ════════════════════════════════════════════════════════════════
   CONFIGURATION
════════════════════════════════════════════════════════════════ */
// ⚠ Remplacer par votre clé API Gemini
const GEMINI_API_KEY = 'CLE_API';
const GEMINI_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${GEMINI_API_KEY}`;

/* ════════════════════════════════════════════════════════════════
   ÉTAT GLOBAL
════════════════════════════════════════════════════════════════ */
let allGarages   = [];
let allDiscs     = [];
let currentUid   = null;
let currentDid   = null;

// Nouveautés
let aiEnabled        = true;
let aiDebounce       = null;
let translateLang    = 'fr';
let translateActive  = false;
let translationsCache = {};  // { msgId_lang: texte traduit }
let pollingInterval  = null;
let lastMsgCount     = 0;
let pendingImgFile = null; // fichier image en attente (File object)

// Stockage des données audio indexées par ID de message (évite l'injection dans les attributs HTML)
const voiceDataMap = new Map();

// Vocal
let mediaRecorder    = null;
let audioChunks      = [];
let recTimerInterval = null;
let recSeconds       = 0;

/* ════════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', async () => {
    currentUid = <?= $currentUserId ?>;
    await loadGarages();
    await loadDiscs();

    const msgInput = document.getElementById('msg-input');
    const counter  = document.getElementById('char-count');
    bindMessageValidation(msgInput, counter);

    bindDiscussionValidation({
        objet:  document.getElementById('md-objet'),
        garage: document.getElementById('md-garage'),
    });

    document.querySelectorAll('.overlay').forEach(o =>
        o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); })
    );

    // Fermer modal image en cliquant en dehors
    document.getElementById('img-modal').addEventListener('click', () => closeModal('img-modal'));
});

/* ════════════════════════════════════════════════════════════════
   CHARGEMENT UTILISATEURS & GARAGES (identique à l'original)
════════════════════════════════════════════════════════════════ */
async function loadGarages() {
    const r = await apiGet('get_garages');
    if (!r.success) { toast('Impossible de charger les garages', 'error'); return; }
    allGarages = r.data;
    populateGarageSelect('md-garage');
}

function populateGarageSelect(selId) {
    const sel = document.getElementById(selId);
    if (!sel) return;
    sel.innerHTML = '<option value="">— Choisir un garage —</option>';
    allGarages.forEach(g =>
        sel.insertAdjacentHTML('beforeend', `<option value="${g.id}">${esc(g.nom_garages)}</option>`)
    );
}

async function loadDiscs() {
    if (!currentUid) return;
    const r = await apiGet('get_discussions_user', { user_id: currentUid });
    allDiscs = r.success ? r.data : [];
    renderConvList(allDiscs);
    renderDiscGrid(allDiscs);
}

/* ════════════════════════════════════════════════════════════════
   RENDU CONVERSATIONS (identique à l'original)
════════════════════════════════════════════════════════════════ */
function renderConvList(list) {
    const cl = document.getElementById('conv-list');
    if (!list.length) {
        cl.innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Aucune conversation</p></div>';
        return;
    }
    cl.innerHTML = list.map(d => {
        const name    = d.nom_garage || '—';
        const preview = d.dernier_contenu ? previewContent(d.dernier_contenu) : 'Aucun message';
        return `
        <div class="conv-item ${currentDid==d.id?'active':''}" id="ci-${d.id}" onclick="openConv(${d.id})">
          <div class="c-av">${name[0].toUpperCase()}</div>
          <div class="c-info">
            <div class="c-name">${esc(d.objet)}</div>
            <div class="c-last">${esc(name)} — ${preview}</div>
          </div>
          <div class="c-meta">
            <div class="c-time">${shortDate(d.dernier_message||d.created_at)}</div>
            ${d.nb_messages>0?`<div class="c-badge">${d.nb_messages}</div>`:''}
          </div>
        </div>`;
    }).join('');
}

/** Aperçu du contenu : gère les types spéciaux */
function previewContent(c) {
    if (!c) return 'Aucun message';
    if (c.startsWith('[IMG]')) return '📷 Photo';
    if (c.startsWith('[VOICE]')) return '🎤 Message vocal';
    return esc(c).substring(0, 40) + (c.length > 40 ? '…' : '');
}

function filterConvs(q) {
    const q2 = q.toLowerCase();
    const filtered = allDiscs.filter(d =>
        d.objet.toLowerCase().includes(q2) || (d.nom_garage||'').toLowerCase().includes(q2)
    );
    renderConvList(filtered);
}

function renderDiscGrid(list) {
    const dg = document.getElementById('disc-grid');
    if (!list.length) {
        dg.innerHTML = '<div class="empty-state" style="grid-column:1/-1"><i class="bi bi-chat-square-dots"></i><p>Aucune discussion</p></div>';
        return;
    }
    dg.innerHTML = list.map(d => `
        <div class="disc-card">
          <div class="dc-head">
            <div class="dc-subj">${esc(d.objet)}</div>
            <div class="dc-id">#${d.id}</div>
          </div>
          <div class="dc-garage"><i class="bi bi-shop"></i> ${esc(d.nom_garage||'—')}</div>
          <div class="dc-foot">
            <div class="dc-count"><i class="bi bi-chat-left-text" style="color:var(--red)"></i> ${d.nb_messages||0} msg</div>
            <div class="dc-acts" onclick="event.stopPropagation()">
              <button class="dc-btn chat" onclick="openConvSwitch(${d.id})" title="Ouvrir"><i class="bi bi-chat"></i></button>
              <button class="dc-btn edit" onclick="editDiscModal(${d.id})" title="Modifier"><i class="bi bi-pencil"></i></button>
              <button class="dc-btn del"  onclick="delDisc(${d.id})" title="Supprimer"><i class="bi bi-trash"></i></button>
            </div>
          </div>
        </div>`
    ).join('');
}

/* ════════════════════════════════════════════════════════════════
   OUVERTURE CONVERSATION
════════════════════════════════════════════════════════════════ */
async function openConv(id) {
    currentDid = id;
    stopPolling();
    translationsCache = {};

    document.querySelectorAll('.conv-item').forEach(el => el.classList.remove('active'));
    const ci = document.getElementById('ci-'+id);
    if (ci) ci.classList.add('active');

    const disc = allDiscs.find(d => d.id == id);
    if (disc) {
        const name = disc.nom_garage || '—';
        document.getElementById('hd-av').textContent   = name[0].toUpperCase();
        document.getElementById('hd-name').textContent = disc.objet;
        document.getElementById('hd-sub').textContent  = name;
    }
    document.getElementById('chat-empty').style.display = 'none';
    document.getElementById('chat-active').classList.add('show');

    await loadMessages(id);
    startPolling();
}

function openConvSwitch(id) { switchTab('chat'); openConv(id); }

/* ════════════════════════════════════════════════════════════════
   CHARGEMENT & RENDU MESSAGES
════════════════════════════════════════════════════════════════ */
async function loadMessages(discId, silent = false) {
    const area = document.getElementById('msgs-area');
    if (!silent) area.innerHTML = '<div class="loading-spinner"></div>';

    const r = await apiGet('get_messages', { discussion_id: discId });
    if (!r.success || !r.data.length) {
        area.innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Aucun message — soyez le premier !</p></div>';
        lastMsgCount = 0;
        return;
    }

    const wasAtBottom = isAtBottom(area);
    const newCount    = r.data.length;
    const hasNew      = silent && newCount > lastMsgCount;

    let lastDate = '';
    area.innerHTML = r.data.map(m => {
        const isSent  = m.expediteur_type === 'user' && m.expediteur_id == currentUid;
        const msgDate = m.date_envoi ? new Date(m.date_envoi).toLocaleDateString('fr-FR') : '';
        let sep = '';
        if (msgDate && msgDate !== lastDate) { lastDate = msgDate; sep = `<div class="date-sep">${msgDate}</div>`; }
        return sep + renderBubble(m, isSent);
    }).join('');

    lastMsgCount = newCount;

    // Réafficher les traductions en cache
    if (translateActive && translateLang !== 'fr') {
        r.data.forEach(m => {
            const key = `${m.id}_${translateLang}`;
            if (translationsCache[key]) showTranslation(m.id, translationsCache[key]);
        });
    }

    if (hasNew && !wasAtBottom) {
        document.getElementById('new-msg-notif').classList.add('show');
        toast('Nouveau message reçu', 'info');
    } else {
        document.getElementById('new-msg-notif').classList.remove('show');
        if (wasAtBottom || !silent) scrollToBottom();
    }
}

function renderBubble(m, isSent) {
    let contentHtml = '';
    const type = (m.type || 'text').toLowerCase();

    if (type === 'image') {
        // contenu = chemin relatif ex: ../../uploads/media/image_xxx.jpg
        const src = esc(m.contenu);
        contentHtml = `<img class="bubble-img" src="${src}" alt="Photo" onclick="openImgModal('${src}')">`;
    } else if (type === 'audio') {
        // contenu = chemin relatif ex: ../../uploads/media/vocal_xxx.webm
        voiceDataMap.set(m.id, m.contenu); // stocke le chemin URL
        const waveHtml = generateWaveBars(18);
        contentHtml = `
        <div class="voice-player">
          <button class="voice-play-btn" onclick="playVoice(this, ${m.id})" title="Écouter">
            <i class="bi bi-play-fill"></i>
          </button>
          <div class="voice-waveform">${waveHtml}</div>
          <span class="voice-duration">🎤</span>
        </div>`;
    } else {
        contentHtml = esc(m.contenu);
    }

    const canEdit = isSent && type === 'text';

    return `
    <div class="bubble-wrap ${isSent?'sent':'recv'}">
      ${!isSent?`<div class="bubble-sender">${esc(m.nom_expediteur||'?')}</div>`:''}
      <div class="bubble ${isSent?'sent':'recv'}" id="bubble-${m.id}">
        ${contentHtml}
        <div class="bub-acts">
          ${canEdit ? `<button class="bab" title="Modifier" onclick="openEditMsg(${m.id},${JSON.stringify(m.contenu)})"><i class="bi bi-pencil"></i></button>` : ''}
          ${type === 'text' ? `<button class="bab" title="Traduire" onclick="translateMsg(${m.id}, ${JSON.stringify(m.contenu)})"><i class="bi bi-translate"></i></button>` : ''}
          <button class="bab" title="Supprimer" onclick="delMsg(${m.id})"><i class="bi bi-trash"></i></button>
        </div>
        <div class="bubble-time">${formatTime(m.date_envoi)}</div>
        <div class="bubble-translation" id="tr-${m.id}" style="display:none"></div>
      </div>
    </div>`;
}

/** Génère des barres de forme d'onde aléatoires */
function generateWaveBars(count) {
    const bars = [];
    for (let i = 0; i < count; i++) {
        const h = 6 + Math.floor(Math.random() * 14);
        bars.push(`<span class="wave-bar" style="height:${h}px"></span>`);
    }
    return bars.join('');
}

/* ════════════════════════════════════════════════════════════════
   ENVOI DE MESSAGE (enrichi — gère texte + image)
════════════════════════════════════════════════════════════════ */
async function sendMsg() {
    if (!currentDid) { toast('Sélectionnez une conversation', 'error'); return; }

    const field = document.getElementById('msg-input');

    // ── Envoi image via upload.php ──
    if (pendingImgFile) {
        const path = await uploadFile(pendingImgFile, 'image');
        if (!path) return;
        await sendRawContent(path, 'image');
        removeImgPreview();
        if (!field.value.trim()) return;
    }

    // ── Envoi message texte ──
    if (!field.value.trim()) return;
    if (!validateMessageForm(field)) return;

    const ok = await sendRawContent(field.value.trim(), 'text');
    if (ok) {
        field.value = '';
        document.getElementById('char-count').textContent = '0/1000';
        field.classList.remove('is-valid','is-invalid');
        clearAISuggestions();
    }
}

async function sendRawContent(contenu, type = 'text') {
    const r = await apiPost('create_message', {
        discussion_id:   currentDid,
        expediteur_id:   currentUid,
        expediteur_type: 'user',
        contenu:         contenu,
        type:            type,
    });
    if (r.success) {
        await loadMessages(currentDid, false);
        loadDiscs();
        return true;
    } else {
        toast(r.message || 'Erreur envoi', 'error');
        return false;
    }
}

/* ════════════════════════════════════════════════════════════════
   UPLOAD FICHIER (image ou audio) via upload.php
   Retourne le chemin relatif ou null si erreur
════════════════════════════════════════════════════════════════ */
const UPLOAD_URL = '../../Controller/upload.php';

async function uploadFile(fileOrBlob, type = 'image') {
    const form = new FormData();
    form.append('type', type);
    // Blob audio : nommer le fichier avec la bonne extension
    if (fileOrBlob instanceof Blob && !(fileOrBlob instanceof File)) {
        const ext = fileOrBlob.type.includes('ogg') ? 'ogg'
                  : fileOrBlob.type.includes('mp4') ? 'mp4'
                  : 'webm';
        form.append('file', fileOrBlob, `vocal_${Date.now()}.${ext}`);
    } else {
        form.append('file', fileOrBlob);
    }
    try {
        const res  = await fetch(UPLOAD_URL, { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) return data.path; // ex: ../../uploads/media/image_xxx.jpg
        toast('Upload échoué : ' + (data.message || 'erreur'), 'error');
        return null;
    } catch (e) {
        toast('Erreur réseau upload', 'error');
        return null;
    }
}
function toggleVoiceRecording() {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
        stopAndSendVoice();
    } else {
        startRecording();
    }
}

async function startRecording() {
    if (!currentDid) { toast('Sélectionnez une conversation', 'error'); return; }
    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        audioChunks = [];
        recSeconds  = 0;

        // Choisir le meilleur format supporté par le navigateur
        const mimeTypes = ['audio/webm;codecs=opus', 'audio/webm', 'audio/ogg;codecs=opus', 'audio/mp4'];
        const supportedMime = mimeTypes.find(t => MediaRecorder.isTypeSupported(t)) || '';

        mediaRecorder = new MediaRecorder(stream, supportedMime ? { mimeType: supportedMime } : {});
        mediaRecorder.ondataavailable = e => { if (e.data.size > 0) audioChunks.push(e.data); };
        mediaRecorder.start(100);

        document.getElementById('recording-indicator').classList.add('show');
        document.getElementById('voice-btn').classList.add('active');

        recTimerInterval = setInterval(() => {
            recSeconds++;
            const m = Math.floor(recSeconds/60);
            const s = recSeconds % 60;
            document.getElementById('rec-time').textContent = `${m}:${s.toString().padStart(2,'0')}`;
            if (recSeconds >= 120) stopAndSendVoice(); // max 2 min
        }, 1000);

    } catch (err) {
        toast('Microphone non accessible : ' + err.message, 'error');
    }
}

function cancelRecording() {
    if (mediaRecorder) {
        mediaRecorder.stream.getTracks().forEach(t => t.stop());
        mediaRecorder = null;
    }
    clearInterval(recTimerInterval);
    audioChunks = [];
    document.getElementById('recording-indicator').classList.remove('show');
    document.getElementById('voice-btn').classList.remove('active');
}

async function stopAndSendVoice() {
    if (!mediaRecorder || mediaRecorder.state !== 'recording') return;
    clearInterval(recTimerInterval);

    await new Promise(resolve => {
        mediaRecorder.onstop = resolve;
        mediaRecorder.stop();
        mediaRecorder.stream.getTracks().forEach(t => t.stop());
    });

    document.getElementById('recording-indicator').classList.remove('show');
    document.getElementById('voice-btn').classList.remove('active');

    const mimeType = mediaRecorder.mimeType || 'audio/webm';
    const blob = new Blob(audioChunks, { type: mimeType });
    audioChunks = [];
    mediaRecorder = null;

    if (blob.size > 15 * 1024 * 1024) {
        toast('Vocal trop long (max 15 Mo).', 'error');
        return;
    }

    toast('Envoi du vocal…', 'info');
    const path = await uploadFile(blob, 'audio');
    if (!path) return;
    await sendRawContent(path, 'audio');
    toast('Message vocal envoyé', 'success');
}

function playVoice(btn, msgId) {
    const src = voiceDataMap.get(msgId);
    if (!src) { toast('Audio introuvable', 'error'); return; }

    const icon = btn.querySelector('i');
    // Si déjà en lecture, on met en pause
    if (btn._audio && !btn._audio.paused) {
        btn._audio.pause();
        icon.className = 'bi bi-play-fill';
        return;
    }

    icon.className = 'bi bi-pause-fill';
    btn.disabled = true;

    const audio = new Audio(src);
    btn._audio = audio;

    const resetBtn = () => {
        icon.className = 'bi bi-play-fill';
        btn.disabled = false;
        btn._audio = null;
    };

    audio.onended = resetBtn;
    audio.onerror = () => {
        resetBtn();
        toast('Erreur lecture audio — fichier introuvable ou format non supporté', 'error');
    };

    audio.play()
        .then(() => { btn.disabled = false; })
        .catch(() => { resetBtn(); toast('Lecture audio impossible', 'error'); });
}

/* ════════════════════════════════════════════════════════════════
   IMAGES
════════════════════════════════════════════════════════════════ */

function handleImageSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { toast('Image trop grande (max 5 Mo)', 'error'); return; }
    if (!file.type.startsWith('image/')) { toast('Fichier non supporté', 'error'); return; }

    pendingImgFile = file;
    // Aperçu local sans lire tout le fichier en base64
    const previewUrl = URL.createObjectURL(file);
    document.getElementById('img-preview').src = previewUrl;
    document.getElementById('img-preview-wrap').classList.add('show');
    event.target.value = '';
}

function removeImgPreview() {
    pendingImgFile = null;
    const preview = document.getElementById('img-preview');
    if (preview.src.startsWith('blob:')) URL.revokeObjectURL(preview.src);
    preview.src = '';
    document.getElementById('img-preview-wrap').classList.remove('show');
}

function openImgModal(src) {
    document.getElementById('img-modal-src').src = src;
    document.getElementById('img-modal').classList.add('show');
}

/* ════════════════════════════════════════════════════════════════
   SUGGESTIONS IA (Gemini)
════════════════════════════════════════════════════════════════ */
function toggleAISuggestions() {
    aiEnabled = !aiEnabled;
    const btn = document.getElementById('ai-toggle-btn');
    btn.classList.toggle('active', aiEnabled);
    if (!aiEnabled) clearAISuggestions();
    toast(aiEnabled ? 'Suggestions IA activées' : 'Suggestions IA désactivées', 'info');
}

function onMsgInput(field) {
    // Compteur (préserve la logique de validation.js)
    const len = (field.value || '').length;
    const counter = document.getElementById('char-count');
    if (counter) {
        counter.textContent = `${len}/1000`;
        counter.style.color = len > 1000 ? 'var(--red)' : '';
    }
    if (field.classList.contains('is-invalid') || field.classList.contains('is-valid')) {
        RULES.contenu(field);
    }

    // Suggestions IA avec debounce
    if (!aiEnabled) return;
    clearTimeout(aiDebounce);
    const val = field.value.trim();
    if (val.length < 2) { clearAISuggestions(); return; }
    aiDebounce = setTimeout(() => fetchAISuggestions(val), 700);
}

async function fetchAISuggestions(text) {
    if (!GEMINI_API_KEY || GEMINI_API_KEY === 'AIzaSyCb1fI_TuuRm4caWPZoEL4aE-OK2rdoOkw') {
        // Mode démo sans clé API : suggestions statiques contextuelles
        const demos = getStaticSuggestions(text);
        renderAISuggestions(demos);
        return;
    }

    const prompt = `Tu es un assistant spécialisé dans l'automobile et les garages.
Un client commence à écrire ce message : "${text}"
Génère exactement 3 suggestions courtes de complétion (max 8 mots chacune), naturelles et spécialisées automobile.
Réponds UNIQUEMENT avec un JSON valide : {"suggestions": ["...", "...", "..."]}
Pas d'explication, pas de markdown.`;

    try {
        const res = await fetch(GEMINI_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contents: [{ parts: [{ text: prompt }] }],
                generationConfig: { maxOutputTokens: 120, temperature: 0.7 }
            })
        });
        const data = await res.json();
        const raw = data.candidates?.[0]?.content?.parts?.[0]?.text || '';
        const clean = raw.replace(/```json|```/g, '').trim();
        const parsed = JSON.parse(clean);
        renderAISuggestions(parsed.suggestions || []);
    } catch (e) {
        renderAISuggestions(getStaticSuggestions(text));
    }
}

/** Suggestions statiques de secours basées sur des mots-clés */
function getStaticSuggestions(text) {
    const t = text.toLowerCase();
    const suggestions = {
        moteur:    ['le moteur fait un bruit anormal', 'le moteur chauffe rapidement', 'le moteur ne démarre plus'],
        voiture:   ['la voiture ne démarre plus', 'la voiture fait un bruit', 'la voiture consomme trop'],
        frein:     ['les freins font du bruit', 'les freins sont moins efficaces', 'la pédale de frein est molle'],
        bruit:     ['bruit au niveau du moteur', 'bruit quand je freine', 'bruit en tournant le volant'],
        batterie:  ['la batterie semble déchargée', 'la batterie ne se recharge plus', 'voyant batterie allumé'],
        voyant:    ['voyant moteur allumé', 'voyant huile allumé', 'voyant check engine allumé'],
        huile:     ['niveau d\'huile bas', 'fuite d\'huile sous la voiture', 'huile noire à changer'],
        pneu:      ['pneu dégonflé rapidement', 'pneu usé de manière inégale', 'crevaison lente'],
        rendez:    ['prendre un rendez-vous urgent', 'rendez-vous pour révision', 'disponibilité cette semaine'],
        révision:  ['révision des 30000 km due', 'révision complète nécessaire', 'contrôle technique prochain'],
    };
    for (const [key, vals] of Object.entries(suggestions)) {
        if (t.includes(key)) return vals;
    }
    // Défaut
    return ['ma voiture ne démarre plus', 'je voudrais prendre rendez-vous', 'j\'ai un problème de frein'];
}

function renderAISuggestions(suggestions) {
    if (!suggestions || !suggestions.length) { clearAISuggestions(); return; }
    const area  = document.getElementById('ai-suggestions-area');
    const chips = document.getElementById('ai-chips');
    area.style.display = '';
    chips.innerHTML = suggestions.map(s => `
        <button class="ai-chip" onclick="applySuggestion('${s.replace(/'/g,"\\'")}')">
            <i class="bi bi-arrow-right-circle"></i>${esc(s)}
        </button>`).join('');
}

function applySuggestion(text) {
    const field = document.getElementById('msg-input');
    field.value = text;
    field.focus();
    // Déclencher compteur et validation
    onMsgInput(field);
    clearAISuggestions();
}

function clearAISuggestions() {
    document.getElementById('ai-suggestions-area').style.display = 'none';
    document.getElementById('ai-chips').innerHTML = '';
}

/* ════════════════════════════════════════════════════════════════
   TRADUCTION (Gemini)
════════════════════════════════════════════════════════════════ */
function toggleTranslateBar() {
    const bar = document.getElementById('translate-bar');
    translateActive = !bar.classList.contains('show');
    bar.classList.toggle('show', translateActive);
    if (!translateActive) {
        // Cacher toutes les traductions
        document.querySelectorAll('[id^="tr-"]').forEach(el => { el.style.display = 'none'; el.textContent = ''; });
    }
}

function setTranslateLang(lang) {
    translateLang = lang;
    ['fr','ar','en'].forEach(l => {
        document.getElementById('tr-'+l).classList.toggle('active', l === lang);
    });
    if (lang === 'fr') {
        // Cacher toutes les traductions en français (langue source par défaut)
        document.querySelectorAll('[id^="tr-"]').forEach(el => { el.style.display = 'none'; el.textContent = ''; });
        return;
    }
    // Retraduire tous les messages visibles
    translateAllVisible();
}

async function translateAllVisible() {
    const area = document.getElementById('msgs-area');
    const bubbles = area.querySelectorAll('.bubble');
    for (const bubble of bubbles) {
        const id = bubble.id?.replace('bubble-', '');
        if (!id) continue;
        const textEl = bubble.querySelector('.bubble-translation') || bubble;
        // Récupérer le texte brut du message (premier nœud texte ou contenu)
        const rawText = getRawBubbleText(bubble);
        if (!rawText || rawText.startsWith('[IMG]') || rawText.startsWith('[VOICE]')) continue;
        const key = `${id}_${translateLang}`;
        if (translationsCache[key]) {
            showTranslation(id, translationsCache[key]);
        } else {
            const translated = await translateText(rawText, translateLang);
            if (translated) {
                translationsCache[key] = translated;
                showTranslation(id, translated);
            }
        }
    }
}

function getRawBubbleText(bubble) {
    // Récupère le texte du bubble sans les éléments bub-acts et bubble-time
    const clone = bubble.cloneNode(true);
    clone.querySelectorAll('.bub-acts,.bubble-time,.bubble-translation').forEach(el => el.remove());
    const img = clone.querySelector('.bubble-img');
    if (img) return '[IMG]';
    const voice = clone.querySelector('.voice-player');
    if (voice) return '[VOICE]';
    return clone.textContent.trim();
}

async function translateMsg(msgId, originalText) {
    if (!originalText || originalText.startsWith('[IMG]') || originalText.startsWith('[VOICE]')) {
        toast('Ce type de message ne peut pas être traduit', 'info');
        return;
    }
    const lang = translateLang !== 'fr' ? translateLang : 'ar'; // défaut arabe si pas de langue choisie
    const key  = `${msgId}_${lang}`;

    if (translationsCache[key]) {
        const el = document.getElementById('tr-'+msgId);
        if (el) { el.style.display = el.style.display === 'none' ? '' : 'none'; }
        return;
    }

    toast('Traduction en cours…', 'info');
    const translated = await translateText(originalText, lang);
    if (translated) {
        translationsCache[key] = translated;
        showTranslation(msgId, translated);
    }
}

function showTranslation(msgId, text) {
    const el = document.getElementById('tr-'+msgId);
    if (!el) return;
    el.textContent = text;
    el.style.display = '';
}

async function translateText(text, targetLang) {
    const langNames = { fr: 'français', ar: 'arabe', en: 'anglais' };
    const target = langNames[targetLang] || targetLang;

    if (!GEMINI_API_KEY || GEMINI_API_KEY === 'AIzaSyCb1fI_TuuRm4caWPZoEL4aE-OK2rdoOkw') {
        // Simuler une traduction simple en mode démo
        return `[Traduction ${target} indisponible — configurez votre clé Gemini]`;
    }

    const prompt = `Traduis ce texte en ${target}. Réponds UNIQUEMENT avec la traduction, sans guillemets ni explication :\n"${text}"`;

    try {
        const res = await fetch(GEMINI_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contents: [{ parts: [{ text: prompt }] }],
                generationConfig: { maxOutputTokens: 300, temperature: 0.3 }
            })
        });
        const data = await res.json();
        return data.candidates?.[0]?.content?.parts?.[0]?.text?.trim() || null;
    } catch (e) {
        toast('Erreur de traduction', 'error');
        return null;
    }
}

/* ════════════════════════════════════════════════════════════════
   POLLING TEMPS RÉEL
════════════════════════════════════════════════════════════════ */
function startPolling() {
    if (pollingInterval) return;
    pollingInterval = setInterval(async () => {
        if (currentDid) await loadMessages(currentDid, true);
    }, 5000); // toutes les 5 secondes
}

function stopPolling() {
    clearInterval(pollingInterval);
    pollingInterval = null;
}

/* ════════════════════════════════════════════════════════════════
   MODIFIER / SUPPRIMER MESSAGE (identique à l'original)
════════════════════════════════════════════════════════════════ */
function openEditMsg(id, contenu) {
    document.getElementById('em-id').value      = id;
    document.getElementById('em-contenu').value = contenu;
    openModal('modal-edit-msg');
}
async function saveEditMsg() {
    const id    = document.getElementById('em-id').value;
    const field = document.getElementById('em-contenu');
    if (!RULES.contenu(field)) return;
    const r = await apiPost('update_message', { id, contenu: field.value.trim() });
    if (r.success) {
        toast(r.message, 'success');
        closeModal('modal-edit-msg');
        if (currentDid) loadMessages(currentDid);
    } else toast(r.message||'Erreur', 'error');
}

function delMsg(id) {
    confirmDel(`Supprimer ce message #${id} ?`, async () => {
        const r = await apiPost('delete_message', { id });
        if (r.success) {
            toast(r.message,'success');
            closeModal('modal-del');
            if (currentDid) loadMessages(currentDid);
            loadDiscs();
        } else toast(r.message||'Erreur','error');
    });
}

/* ════════════════════════════════════════════════════════════════
   MODAL DISCUSSION (identique à l'original)
════════════════════════════════════════════════════════════════ */
function openNewDiscModal() {
    clearAllErrors(document.getElementById('modal-disc'));
    document.getElementById('md-id').value    = '';
    document.getElementById('md-objet').value = '';
    document.getElementById('md-title').textContent = 'Nouvelle Discussion';
    document.getElementById('md-btn').textContent   = 'Créer';
    populateGarageSelect('md-garage');
    document.getElementById('md-uid').value = currentUid;
    openModal('modal-disc');
}

async function editDiscModal(id) {
    const disc = allDiscs.find(d => d.id==id);
    if (!disc) return;
    clearAllErrors(document.getElementById('modal-disc'));
    document.getElementById('md-id').value    = disc.id;
    document.getElementById('md-uid').value   = disc.user_id;
    document.getElementById('md-objet').value = disc.objet;
    document.getElementById('md-title').textContent = 'Modifier la Discussion';
    document.getElementById('md-btn').textContent   = 'Enregistrer';
    populateGarageSelect('md-garage');
    await new Promise(r=>setTimeout(r,10));
    document.getElementById('md-garage').value = disc.garage_id;
    openModal('modal-disc');
}

function editCurrentDisc() { if (currentDid) editDiscModal(currentDid); }

async function saveDisc() {
    const garageField = document.getElementById('md-garage');
    const objetField  = document.getElementById('md-objet');
    if (!validateDiscussionForm({ objet: objetField, garage: garageField })) return;

    const uid = document.getElementById('md-uid').value || currentUid;
    if (!uid) { toast('Aucun utilisateur sélectionné', 'error'); return; }

    const id     = document.getElementById('md-id').value;
    const action = id ? 'update_discussion' : 'create_discussion';
    const data   = { user_id: uid, garage_id: garageField.value, objet: objetField.value.trim() };
    if (id) data.id = id;

    const r = await apiPost(action, data);
    if (r.success) {
        toast(r.message, 'success');
        closeModal('modal-disc');
        await loadDiscs();
        if (!id && r.data) openConv(r.data.id);
    } else toast(r.message||'Erreur','error');
}

function delDisc(id) {
    confirmDel(`Supprimer la discussion #${id} et tous ses messages ?`, async () => {
        const r = await apiPost('delete_discussion', { id });
        if (r.success) {
            toast(r.message,'success');
            closeModal('modal-del');
            stopPolling();
            if (currentDid==id) {
                currentDid=null;
                document.getElementById('chat-empty').style.display='';
                document.getElementById('chat-active').classList.remove('show');
            }
            loadDiscs();
        } else toast(r.message||'Erreur','error');
    });
}

function delCurrentDisc(){ if(currentDid) delDisc(currentDid); }

function confirmDel(text, cb) {
    document.getElementById('del-text').textContent = text;
    document.getElementById('del-confirm').onclick  = cb;
    openModal('modal-del');
}

/* ════════════════════════════════════════════════════════════════
   ONGLETS (identique à l'original)
════════════════════════════════════════════════════════════════ */
function switchTab(tab) {
    document.getElementById('view-chat').style.display = tab==='chat' ? '' : 'none';
    document.getElementById('view-grid').style.display = tab==='grid' ? '' : 'none';
    document.getElementById('ptab-chat').classList.toggle('active', tab==='chat');
    document.getElementById('ptab-grid').classList.toggle('active', tab==='grid');
}

/* ════════════════════════════════════════════════════════════════
   HELPERS MODAL & UTILS (identique à l'original + ajouts)
════════════════════════════════════════════════════════════════ */
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function esc(s){ if(!s)return''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function formatTime(dt){ if(!dt)return''; const d=new Date(dt); return d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'}); }
function shortDate(dt){
    if(!dt)return'';
    const d=new Date(dt); if(isNaN(d))return'';
    const diff=Date.now()-d;
    if(diff<60000)return'À l\'instant';
    if(diff<3600000)return Math.floor(diff/60000)+'min';
    if(diff<86400000)return Math.floor(diff/3600000)+'h';
    return d.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit'});
}

function isAtBottom(el) {
    return el.scrollHeight - el.scrollTop - el.clientHeight < 60;
}
function scrollToBottom() {
    const area = document.getElementById('msgs-area');
    area.scrollTop = area.scrollHeight;
    document.getElementById('new-msg-notif').classList.remove('show');
}
</script>
</body>
</html>