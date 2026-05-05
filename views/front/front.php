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
   BARRE UTILISATEUR — avec switch simulé
═══════════════════════════════════════════════ */
.user-bar{background:var(--white);border-radius:var(--radius);padding:14px 20px;box-shadow:var(--shadow);margin-bottom:22px;display:flex;align-items:center;gap:14px;flex-wrap:wrap}
.user-bar label{font-size:13px;font-weight:700;color:var(--muted);white-space:nowrap}

/* Simulation badge */
.sim-badge{display:inline-flex;align-items:center;gap:6px;background:rgba(255,193,7,.1);border:1px solid rgba(255,193,7,.3);border-radius:7px;padding:3px 10px;font-size:11px;font-weight:700;color:#a07c00}
.sim-badge i{font-size:12px}

/* User select pill stylé */
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
.messenger{display:grid;grid-template-columns:300px 1fr;gap:18px;height:580px}

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
.bub-acts{position:absolute;top:-26px;display:none;gap:4px}
.bubble.sent  .bub-acts{right:0}
.bubble.recv  .bub-acts{left:0}
.bubble:hover .bub-acts{display:flex}
.bab{width:22px;height:22px;border-radius:5px;border:none;background:#fff;box-shadow:0 1px 5px rgba(0,0,0,.12);cursor:pointer;font-size:10px;color:var(--muted);display:flex;align-items:center;justify-content:center}
.bab:hover{color:var(--red)}

/* ── Input zone ── */
.input-zone{padding:12px 18px;border-top:1px solid var(--border);background:var(--white)}
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

/* Responsive */
@media(max-width:860px){
  .messenger{grid-template-columns:1fr;height:auto}
  .conv-panel{height:280px}
  .chat-win{height:380px}
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
      <a href="#">Accueil</a>
      <a href="#">Garages</a>
      <a href="#">Boutique</a>
      <a href="#" class="active">Messagerie</a>
      <a href="#">Mon Compte</a>
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

  <!--
    BARRE UTILISATEUR — Simulation de connexion client
    ⚠ En production : remplacer le <select> par les valeurs de session PHP :
      currentUid = <?= $_SESSION['user_id'] ?>;
      et afficher juste le nom (pill statique).
  -->
  <div class="user-bar">
    <label><i class="bi bi-person-circle" style="color:var(--red)"></i> Connecté en tant que :</label>

    <!-- Select de simulation — remplacer par session en prod -->
    <div class="user-select-wrap">
      <div class="pill-av" id="pill-av">?</div>
      <select class="user-select" id="user-switcher" onchange="switchUser(this.value)">
        <option value="">— Chargement… —</option>
      </select>
    </div>

    <!-- Badge simulation -->
    <span class="sim-badge"><i class="bi bi-flask"></i> Mode simulation</span>

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
              <div class="hd-sub"><span class="online-dot"></span> <span id="hd-sub">—</span></div>
            </div>
            <div class="hd-actions">
              <button class="ic-btn edit" title="Modifier la discussion" onclick="editCurrentDisc()"><i class="bi bi-pencil"></i></button>
              <button class="ic-btn del"  title="Supprimer la discussion" onclick="delCurrentDisc()"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <!-- Messages -->
          <div class="msgs-area" id="msgs-area"></div>
          <!-- Input -->
          <div class="input-zone">
            <div class="input-row">
              <div class="input-wrap">
                <textarea class="msg-input" id="msg-input" rows="1"
                  placeholder="Écrire un message…"
                  onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();sendMsg();}"></textarea>
                <div class="char-count" id="char-count">0/1000</div>
              </div>
              <button class="send-btn" onclick="sendMsg()" title="Envoyer"><i class="bi bi-send"></i></button>
            </div>
            <div style="position:relative">
              <span class="field-error" id="msg-error" style="display:none"></span>
            </div>
          </div>
        </div>
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
<script>const API_URL = '../../controller/MessageController.php';</script>
<script src="../../assets/js/api.js"></script>
<script src="../../assets/js/validation.js"></script>
<script>
'use strict';

let allGarages = [];
let allDiscs   = [];
let allUsers   = [];   // liste des users pour le switcher
let currentUid = null;
let currentDid = null;

/* ══════════════════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', async () => {
    // Charger la liste des users pour le switcher de simulation
    await loadUsers();
    await loadGarages();
    // loadDiscs() est appelé par switchUser() au premier changement
    // Si un user est déjà sélectionné, charger ses discussions
    if (currentUid) await loadDiscs();

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
});

/* ── Chargement users (switcher simulation) ─────────────── */
async function loadUsers() {
    const r = await apiGet('get_users');
    if (!r.success) { toast('Impossible de charger les utilisateurs', 'error'); return; }
    allUsers = r.data;

    const sel = document.getElementById('user-switcher');
    sel.innerHTML = '<option value="">— Choisir un client —</option>';
    allUsers.forEach(u =>
        sel.insertAdjacentHTML('beforeend',
            `<option value="${u.id}">${esc(u.nom)}</option>`)
    );

    // Pré-sélectionner le premier utilisateur
    if (allUsers.length) {
        sel.value = allUsers[0].id;
        await switchUser(allUsers[0].id, false); // false = ne pas recharger les discussions encore
    }
}

/* ── Changer d'utilisateur simulé ───────────────────────── */
async function switchUser(uid, reloadDiscs = true) {
    if (!uid) return;
    currentUid = parseInt(uid);
    currentDid = null;

    // Mettre à jour l'avatar
    const user = allUsers.find(u => u.id == uid);
    if (user) {
        document.getElementById('pill-av').textContent = user.nom[0].toUpperCase();
    }

    // Vider le chat actif
    document.getElementById('chat-empty').style.display  = '';
    document.getElementById('chat-active').classList.remove('show');

    if (reloadDiscs) {
        await loadDiscs();
        toast(`Connecté en tant que ${user ? user.nom : 'utilisateur #'+uid}`, 'info');
    }
    // Mettre à jour l'uid dans la modal
    document.getElementById('md-uid').value = currentUid;
}

/* ── Chargements données ────────────────────────────────── */
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
        sel.insertAdjacentHTML('beforeend',
            `<option value="${g.id}">${esc(g.nom_garages)}</option>`)
    );
}

async function loadDiscs() {
    if (!currentUid) return;
    const r = await apiGet('get_discussions_user', { user_id: currentUid });
    allDiscs = r.success ? r.data : [];
    renderConvList(allDiscs);
    renderDiscGrid(allDiscs);
}

/* ── Rendu liste conversations ──────────────────────────── */
function renderConvList(list) {
    const cl = document.getElementById('conv-list');
    if (!list.length) {
        cl.innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Aucune conversation</p></div>';
        return;
    }
    cl.innerHTML = list.map(d => {
        const name    = d.nom_garage || '—';
        const preview = d.dernier_contenu ? esc(d.dernier_contenu).substring(0,40)+'…' : 'Aucun message';
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

function filterConvs(q) {
    const q2 = q.toLowerCase();
    const filtered = allDiscs.filter(d =>
        d.objet.toLowerCase().includes(q2) ||
        (d.nom_garage||'').toLowerCase().includes(q2)
    );
    renderConvList(filtered);
}

/* ── Rendu grille discussions ───────────────────────────── */
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

/* ── Ouvrir une conversation ────────────────────────────── */
async function openConv(id) {
    currentDid = id;
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
}

function openConvSwitch(id) { switchTab('chat'); openConv(id); }

/* ── Charger messages ───────────────────────────────────── */
async function loadMessages(discId) {
    const area = document.getElementById('msgs-area');
    area.innerHTML = '<div class="loading-spinner"></div>';
    const r = await apiGet('get_messages', { discussion_id: discId });
    if (!r.success || !r.data.length) {
        area.innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Aucun message — soyez le premier !</p></div>';
        return;
    }
    let lastDate = '';
    area.innerHTML = r.data.map(m => {
        // Un message est "envoyé" (droite) si c'est le client actuel
        const isSent  = m.expediteur_type === 'user' && m.expediteur_id == currentUid;
        const msgDate = m.date_envoi ? new Date(m.date_envoi).toLocaleDateString('fr-FR') : '';
        let sep = '';
        if (msgDate && msgDate !== lastDate) { lastDate = msgDate; sep = `<div class="date-sep">${msgDate}</div>`; }
        return `${sep}
        <div class="bubble-wrap ${isSent?'sent':'recv'}">
          ${!isSent?`<div class="bubble-sender">${esc(m.nom_expediteur||'?')}</div>`:''}
          <div class="bubble ${isSent?'sent':'recv'}">
            ${esc(m.contenu)}
            <div class="bub-acts">
              <button class="bab" title="Modifier" onclick="openEditMsg(${m.id},${JSON.stringify(m.contenu)})"><i class="bi bi-pencil"></i></button>
              <button class="bab" title="Supprimer" onclick="delMsg(${m.id})"><i class="bi bi-trash"></i></button>
            </div>
            <div class="bubble-time">${formatTime(m.date_envoi)}</div>
          </div>
        </div>`;
    }).join('');
    area.scrollTop = area.scrollHeight;
}

/* ── Envoyer message ────────────────────────────────────── */
async function sendMsg() {
    if (!currentDid) { toast('Sélectionnez une conversation', 'error'); return; }
    if (!currentUid) { toast('Choisissez un utilisateur d\'abord', 'error'); return; }

    const field = document.getElementById('msg-input');
    if (!validateMessageForm(field)) return;

    const r = await apiPost('create_message', {
        discussion_id:   currentDid,
        expediteur_id:   currentUid,
        expediteur_type: 'user',
        contenu:         field.value.trim(),
    });
    if (r.success) {
        field.value = '';
        document.getElementById('char-count').textContent = '0/1000';
        field.classList.remove('is-valid','is-invalid');
        await loadMessages(currentDid);
        loadDiscs();
    } else {
        toast(r.message || 'Erreur envoi', 'error');
    }
}

/* ── Modifier message ───────────────────────────────────── */
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

/* ── Supprimer message ──────────────────────────────────── */
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

/* ── Modal Discussion ───────────────────────────────────── */
function openNewDiscModal() {
    if (!currentUid) { toast('Choisissez un utilisateur d\'abord', 'error'); return; }
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

/* ── Supprimer discussion ───────────────────────────────── */
function delDisc(id) {
    confirmDel(`Supprimer la discussion #${id} et tous ses messages ?`, async () => {
        const r = await apiPost('delete_discussion', { id });
        if (r.success) {
            toast(r.message,'success');
            closeModal('modal-del');
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

/* ── Confirmation suppression ───────────────────────────── */
function confirmDel(text, cb) {
    document.getElementById('del-text').textContent = text;
    document.getElementById('del-confirm').onclick  = cb;
    openModal('modal-del');
}

/* ── Onglets ────────────────────────────────────────────── */
function switchTab(tab) {
    document.getElementById('view-chat').style.display = tab==='chat' ? '' : 'none';
    document.getElementById('view-grid').style.display = tab==='grid' ? '' : 'none';
    document.getElementById('ptab-chat').classList.toggle('active', tab==='chat');
    document.getElementById('ptab-grid').classList.toggle('active', tab==='grid');
}

/* ── Modal helpers ──────────────────────────────────────── */
function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

/* ── Utils ──────────────────────────────────────────────── */
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
</script>
</body>
</html>