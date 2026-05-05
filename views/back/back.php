<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Messagerie — Espace Garage | AutoService</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
/* ═══════════════════════════════════════════════
   VARIABLES
═══════════════════════════════════════════════ */
:root{
  --sb-bg:#1a1a2e; --sb-acc:#e94560; --sb-txt:rgba(255,255,255,.65); --sb-w:255px;
  --red:#e94560; --red-dk:#c73652;
  --bg:#f4f6fc; --white:#fff; --border:#e0e4eb;
  --txt:#2d3748; --muted:#718096;
  --radius:12px; --shadow:0 2px 16px rgba(0,0,0,.06); --shadow-lg:0 8px 28px rgba(0,0,0,.11);
  --success:#28a745; --info:#17a2b8; --warn:#ffc107;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Nunito',sans-serif;background:var(--bg);color:var(--txt);display:flex;min-height:100vh}

/* ═══════════════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════════════ */
.sidebar{width:var(--sb-w);background:var(--sb-bg);min-height:100vh;position:fixed;top:0;left:0;z-index:1000;display:flex;flex-direction:column;transition:transform .3s}
.sb-logo{padding:22px 18px;border-bottom:1px solid rgba(255,255,255,.06);display:flex;align-items:center;gap:11px}
.sb-logo .ico{width:38px;height:38px;background:var(--red);border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:19px;color:#fff}
.sb-logo .txt{font-size:17px;font-weight:800;color:#fff}
.sb-logo .txt span{color:var(--red)}
.sb-section{padding:18px 0 6px}
.sb-section-title{padding:0 18px 7px;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:rgba(255,255,255,.22)}
.sb-item{display:flex;align-items:center;gap:11px;padding:9px 18px;color:var(--sb-txt);text-decoration:none;font-size:13.5px;font-weight:600;border:none;background:none;width:100%;text-align:left;cursor:pointer;transition:all .2s;border-left:3px solid transparent}
.sb-item:hover,.sb-item.active{background:rgba(233,69,96,.1);color:#fff;border-left-color:var(--red)}
.sb-item.active{color:var(--red)}
.sb-item .sico{width:30px;height:30px;background:rgba(255,255,255,.06);border-radius:7px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0}
.sb-item.active .sico{background:rgba(233,69,96,.18)}
.sb-badge{margin-left:auto;background:var(--red);color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px}

/* ── Garage picker (sidebar footer) ── */
.sb-garage-picker{padding:14px 18px;border-top:1px solid rgba(255,255,255,.06)}
.sb-garage-label{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:7px}
.sb-garage-sel{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:9px;padding:8px 11px;color:#fff;font-family:'Nunito',sans-serif;font-size:13px;font-weight:600;cursor:pointer;outline:none;transition:border .2s}
.sb-garage-sel:focus{border-color:var(--red)}
.sb-garage-sel option{background:#1a1a2e;color:#fff}

/* ── Garage identity badge ── */
.garage-badge{display:flex;align-items:center;gap:9px;padding:10px 14px;background:rgba(233,69,96,.1);border-radius:9px;margin:0 18px 12px;border:1px solid rgba(233,69,96,.18)}
.garage-badge .gico{width:32px;height:32px;border-radius:8px;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;flex-shrink:0}
.garage-badge .gname{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.garage-badge .grole{font-size:10px;color:rgba(255,255,255,.45)}

/* ═══════════════════════════════════════════════
   MAIN
═══════════════════════════════════════════════ */
.main{margin-left:var(--sb-w);flex:1;display:flex;flex-direction:column;min-width:0}

/* ─ Topbar ─ */
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:13px 26px;display:flex;align-items:center;gap:14px;position:sticky;top:0;z-index:100;box-shadow:var(--shadow)}
.topbar-title{font-size:17px;font-weight:800;flex:1;display:flex;align-items:center;gap:8px}
.topbar-title span{color:var(--red)}
.topbar-garage-tag{background:rgba(23,162,184,.1);color:var(--info);border:1px solid rgba(23,162,184,.2);border-radius:20px;padding:4px 12px;font-size:12px;font-weight:700;display:inline-flex;align-items:center;gap:5px}
.tb-search{position:relative}
.tb-search input{border:1.5px solid var(--border);border-radius:22px;padding:7px 14px 7px 36px;font-size:13px;font-family:'Nunito',sans-serif;width:220px;background:var(--bg);outline:none;transition:border .2s}
.tb-search input:focus{border-color:var(--red);background:#fff}
.tb-search i{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:14px}
.tb-btn{width:36px;height:36px;border-radius:50%;border:1.5px solid var(--border);background:#fff;display:flex;align-items:center;justify-content:center;cursor:pointer;color:var(--muted);font-size:15px;transition:all .2s;position:relative}
.tb-btn:hover{border-color:var(--red);color:var(--red)}
.tb-dot{position:absolute;top:5px;right:5px;width:8px;height:8px;background:var(--red);border-radius:50%;border:2px solid #fff}

/* ─ No garage banner ─ */
.no-garage-banner{background:linear-gradient(135deg,rgba(233,69,96,.08),rgba(233,69,96,.03));border:2px dashed rgba(233,69,96,.3);border-radius:var(--radius);padding:40px;text-align:center;margin:26px}
.no-garage-banner i{font-size:48px;color:rgba(233,69,96,.3);display:block;margin-bottom:14px}
.no-garage-banner h4{font-size:18px;font-weight:800;color:var(--txt);margin-bottom:8px}
.no-garage-banner p{color:var(--muted);font-size:14px}

/* ─ Content ─ */
.content{padding:26px}

/* ─ Stat cards ─ */
.stats-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:18px;margin-bottom:26px}
.stat-card{background:var(--white);border-radius:var(--radius);padding:20px;box-shadow:var(--shadow);display:flex;align-items:center;gap:14px;transition:box-shadow .2s}
.stat-card:hover{box-shadow:var(--shadow-lg)}
.stat-ico{width:50px;height:50px;border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:21px;flex-shrink:0}
.stat-ico.r{background:rgba(233,69,96,.11);color:var(--red)}
.stat-ico.b{background:rgba(23,162,184,.11);color:var(--info)}
.stat-ico.g{background:rgba(40,167,69,.11);color:var(--success)}
.stat-ico.o{background:rgba(255,193,7,.11);color:#c59b00}
.stat-val{font-size:26px;font-weight:800;color:var(--txt)}
.stat-lbl{font-size:12px;font-weight:600;color:var(--muted)}

/* ─ Tabs ─ */
.section-tabs{display:flex;gap:5px;background:var(--white);border-radius:var(--radius);padding:5px;box-shadow:var(--shadow);margin-bottom:18px;width:fit-content}
.stab{padding:8px 18px;border-radius:8px;border:none;background:transparent;font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;color:var(--muted);cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s}
.stab.active{background:var(--red);color:#fff;box-shadow:0 4px 12px rgba(233,69,96,.32)}
.stab:hover:not(.active){background:var(--bg);color:var(--txt)}

/* ─ Card box ─ */
.card-box{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.cb-head{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.cb-head h5{font-size:14.5px;font-weight:800;margin:0;display:flex;align-items:center;gap:7px}
.cb-head h5 i{color:var(--red)}

/* ─ Table ─ */
.tbl{width:100%;border-collapse:collapse}
.tbl thead th{padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);background:var(--bg);border-bottom:1px solid var(--border);white-space:nowrap}
.tbl tbody tr{transition:background .15s}
.tbl tbody tr:hover{background:#fafbff}
.tbl tbody td{padding:13px 14px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
.tbl tbody tr:last-child td{border-bottom:none}

/* ─ Badges ─ */
.tag-subj{background:rgba(233,69,96,.08);color:var(--red);border:1px solid rgba(233,69,96,.2);border-radius:6px;padding:3px 9px;font-size:12px;font-weight:700}
.tag-user{display:inline-flex;align-items:center;gap:5px;background:var(--bg);border:1px solid var(--border);border-radius:18px;padding:2px 9px 2px 3px;font-size:12px;font-weight:600}
.tag-user .av{width:20px;height:20px;border-radius:50%;background:var(--success);color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center}
.tag-garage{display:inline-flex;align-items:center;gap:5px;background:rgba(23,162,184,.07);border:1px solid rgba(23,162,184,.2);border-radius:18px;padding:2px 9px 2px 3px;font-size:12px;font-weight:600;color:var(--info)}
.tag-garage .av{background:var(--info);width:20px;height:20px;border-radius:50%;color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center}
.nb-badge{background:var(--red);color:#fff;border-radius:9px;padding:2px 8px;font-size:11px;font-weight:700}
.date-txt{font-size:12px;color:var(--muted)}
.msg-prev{color:var(--muted);font-size:12.5px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* ─ Action buttons ─ */
.acts{display:flex;gap:5px}
.abtn{width:30px;height:30px;border-radius:7px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s}
.abtn.v{background:rgba(23,162,184,.1);color:var(--info)}
.abtn.e{background:rgba(255,193,7,.1);color:#c59b00}
.abtn.d{background:rgba(233,69,96,.1);color:var(--red)}
.abtn.c{background:rgba(40,167,69,.1);color:var(--success)}
.abtn:hover{transform:scale(1.1)}

/* ─ Chat view garage ─ */
.admin-chat{display:grid;grid-template-columns:270px 1fr;gap:18px;height:550px}
.ac-panel{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);display:flex;flex-direction:column;overflow:hidden}
.ac-ph{padding:14px 16px;border-bottom:1px solid var(--border);font-size:13.5px;font-weight:800;display:flex;align-items:center;justify-content:space-between}
.ac-list{flex:1;overflow-y:auto}
.ac-item{padding:12px 14px;border-bottom:1px solid var(--border);cursor:pointer;display:flex;align-items:center;gap:10px;transition:background .15s}
.ac-item:hover{background:#fafbff}
.ac-item.active{background:rgba(233,69,96,.05);border-left:3px solid var(--red)}
.ac-av{width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,var(--success),#1a4a2e);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.ac-info{flex:1;min-width:0}
.ac-name{font-size:13px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ac-sub{font-size:11.5px;color:var(--muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ac-nb{background:var(--red);color:#fff;border-radius:9px;padding:1px 6px;font-size:10px;font-weight:700;flex-shrink:0}

.ac-chat{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);display:flex;flex-direction:column;overflow:hidden}
.ac-hd{padding:13px 18px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;background:var(--white)}
.ac-hd-title{font-size:14px;font-weight:800;flex:1}
.ac-msgs{flex:1;overflow-y:auto;padding:16px;background:#f8f9fc;display:flex;flex-direction:column;gap:9px}
.bbl{max-width:62%;padding:9px 13px 7px;border-radius:14px;font-size:13.5px;line-height:1.5;word-break:break-word;position:relative}
.bbl.sent{background:var(--red);color:#fff;align-self:flex-end;border-bottom-right-radius:4px}
.bbl.recv{background:var(--white);color:var(--txt);align-self:flex-start;border-bottom-left-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.06)}
.bbl-meta{font-size:10px;margin-top:4px;opacity:.65;text-align:right}
.bbl-sender{font-size:11px;color:var(--muted);font-weight:700;margin-bottom:3px}
.bbl-acts{position:absolute;top:-24px;display:none;gap:3px}
.bbl.sent  .bbl-acts{right:0}
.bbl.recv  .bbl-acts{left:0}
.bbl:hover .bbl-acts{display:flex}
.bba{width:22px;height:22px;border-radius:5px;border:none;background:#fff;box-shadow:0 1px 5px rgba(0,0,0,.12);cursor:pointer;font-size:10px;color:var(--muted);display:flex;align-items:center;justify-content:center}
.bba:hover{color:var(--red)}

.ac-input{padding:12px 16px;border-top:1px solid var(--border);background:var(--white);display:flex;align-items:flex-end;gap:9px}
.ac-textarea{flex:1;border:1.5px solid var(--border);border-radius:20px;padding:8px 15px;font-size:13px;font-family:'Nunito',sans-serif;resize:none;min-height:40px;max-height:100px;outline:none;transition:border .2s}
.ac-textarea:focus{border-color:var(--red)}
.ac-textarea.is-invalid{border-color:var(--red)!important}
.send-btn{width:40px;height:40px;border-radius:50%;background:var(--red);border:none;color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(233,69,96,.3);transition:all .2s;flex-shrink:0}
.send-btn:hover{background:var(--red-dk);transform:scale(1.06)}

/* ─ Sender hint in chat ─ */
.sender-hint{font-size:11px;color:var(--muted);padding:6px 16px 0;background:var(--white);display:flex;align-items:center;gap:5px}
.sender-hint strong{color:var(--info)}

/* ═══════════════════════════════════════════════
   MODAL
═══════════════════════════════════════════════ */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center}
.overlay.show{display:flex;animation:fadeIn .2s}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.mbox{background:#fff;border-radius:16px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:slideUp .25s}
.mbox.wide{max-width:680px}
@keyframes slideUp{from{opacity:0;transform:translateY(22px)}to{opacity:1;transform:translateY(0)}}
.mh{padding:20px 22px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between}
.mh h5{font-size:16px;font-weight:800;margin:0;display:flex;align-items:center;gap:8px}
.mh h5 i{color:var(--red)}
.mc{width:30px;height:30px;border-radius:7px;border:none;background:var(--bg);cursor:pointer;font-size:15px;color:var(--muted);display:flex;align-items:center;justify-content:center;transition:all .2s}
.mc:hover{background:rgba(233,69,96,.1);color:var(--red)}
.mb{padding:20px 22px}
.mf{padding:15px 22px;border-top:1px solid var(--border);display:flex;gap:9px;justify-content:flex-end}
.fg{margin-bottom:16px}
.flabel{display:block;font-size:11.5px;font-weight:700;color:var(--muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.finput{width:100%;border:1.5px solid var(--border);border-radius:9px;padding:9px 13px;font-size:13.5px;font-family:'Nunito',sans-serif;color:var(--txt);background:var(--bg);outline:none;transition:border .2s,box-shadow .2s}
.finput:focus{border-color:var(--red);box-shadow:0 0 0 3px rgba(233,69,96,.1);background:#fff}
.finput.is-invalid{border-color:var(--red)!important;background:#fff9f9}
.finput.is-valid{border-color:#28a745}
.field-error{display:none;font-size:11.5px;color:var(--red);font-weight:600;margin-top:4px}
textarea.finput{resize:vertical;min-height:85px}
.btn-pri{background:var(--red);color:#fff;border:none;border-radius:9px;padding:9px 20px;font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:6px;box-shadow:0 4px 12px rgba(233,69,96,.28);transition:all .2s}
.btn-pri:hover{background:var(--red-dk);transform:translateY(-1px)}
.btn-sec{background:var(--bg);color:var(--muted);border:1.5px solid var(--border);border-radius:9px;padding:9px 20px;font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s}
.btn-sec:hover{border-color:var(--red);color:var(--red)}
.btn-danger{background:rgba(233,69,96,.1);color:var(--red);border:1.5px solid rgba(233,69,96,.25);border-radius:9px;padding:9px 20px;font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s}
.btn-danger:hover{background:var(--red);color:#fff}
.btn-info{background:rgba(23,162,184,.1);color:var(--info);border:1.5px solid rgba(23,162,184,.25);border-radius:9px;padding:9px 20px;font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:6px}
.btn-info:hover{background:var(--info);color:#fff}

/* ─ Info box in modal ─ */
.info-box{background:rgba(23,162,184,.07);border:1px solid rgba(23,162,184,.2);border-radius:9px;padding:10px 13px;font-size:12.5px;color:var(--info);display:flex;align-items:center;gap:8px;margin-bottom:16px}

/* ─ Toast ─ */
#toast-container{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:8px}
.toast-item{background:#fff;border-radius:11px;padding:12px 16px;box-shadow:0 8px 28px rgba(0,0,0,.14);display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600;min-width:240px;border-left:4px solid;animation:slideIn .3s}
.toast-success{border-color:var(--success)}.toast-error{border-color:var(--red)}.toast-info{border-color:var(--info)}
.toast-icon{font-size:16px}
.toast-success .toast-icon{color:var(--success)}.toast-error .toast-icon{color:var(--red)}.toast-info .toast-icon{color:var(--info)}
.toast-hide{animation:fadeOut .4s forwards}
@keyframes slideIn{from{opacity:0;transform:translateX(36px)}to{opacity:1;transform:translateX(0)}}
@keyframes fadeOut{to{opacity:0;transform:translateX(36px)}}

/* ─ Misc ─ */
.loading-spinner{width:24px;height:24px;border:2.5px solid rgba(233,69,96,.2);border-top-color:var(--red);border-radius:50%;animation:spin .7s linear infinite;margin:28px auto;display:block}
@keyframes spin{to{transform:rotate(360deg)}}
.empty-state{text-align:center;padding:40px;color:var(--muted)}
.empty-state i{font-size:40px;opacity:.2;display:block;margin-bottom:10px}
.empty-state p{font-size:14px;font-weight:700}
.date-sep{text-align:center;font-size:11px;color:var(--muted);margin:4px 0;position:relative}
.date-sep::before,.date-sep::after{content:'';position:absolute;top:50%;width:32%;height:1px;background:var(--border)}
.date-sep::before{left:0}.date-sep::after{right:0}

@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:860px){.admin-chat{grid-template-columns:1fr;height:auto}.ac-panel{height:250px}.ac-chat{height:350px}}
</style>
</head>
<body>

<!-- ════ SIDEBAR ════ -->
<aside class="sidebar">
  <div class="sb-logo">
    <div class="ico"><i class="bi bi-shop"></i></div>
    <div class="txt">Auto<span>Service</span></div>
  </div>

  <!-- Garage identity badge (affiché quand un garage est sélectionné) -->
  <div id="garage-identity" style="display:none;padding:12px 18px 0">
    <div class="garage-badge">
      <div class="gico"><i class="bi bi-shop"></i></div>
      <div>
        <div class="gname" id="garage-identity-name">—</div>
        <div class="grole">Connecté en tant que garage</div>
      </div>
    </div>
  </div>

  <div class="sb-section">
    <div class="sb-section-title">Navigation</div>
    <a class="sb-item" href="#"><span class="sico"><i class="bi bi-speedometer2"></i></span> Dashboard</a>
    <button class="sb-item active" id="nav-msg" onclick="showSection('discussions')">
      <span class="sico"><i class="bi bi-chat-dots"></i></span> Messagerie
      <span class="sb-badge" id="sb-badge">0</span>
    </button>
  </div>
  <div class="sb-section">
    <div class="sb-section-title">Autres Modules</div>
    <a class="sb-item" href="#"><span class="sico"><i class="bi bi-people"></i></span> Clients</a>
    <a class="sb-item" href="#"><span class="sico"><i class="bi bi-car-front"></i></span> Véhicules</a>
    <a class="sb-item" href="#"><span class="sico"><i class="bi bi-tools"></i></span> Diagnostic</a>
    <a class="sb-item" href="#"><span class="sico"><i class="bi bi-cart3"></i></span> Vente</a>
  </div>

  <!-- Sélecteur de garage (simulation de session) -->
  <div class="sb-garage-picker" style="margin-top:auto">
    <div class="sb-garage-label"><i class="bi bi-shield-lock" style="margin-right:4px"></i> Connecté en tant que</div>
    <select class="sb-garage-sel" id="garage-session-sel" onchange="switchGarage(this.value)">
      <option value="">— Choisir un garage —</option>
    </select>
  </div>
</aside>

<!-- ════ MAIN ════ -->
<div class="main">

  <!-- Topbar -->
  <div class="topbar">
    <div class="topbar-title">
      Messagerie
      <span id="topbar-garage-tag" style="display:none" class="topbar-garage-tag"><i class="bi bi-shop"></i> <span id="topbar-garage-name">—</span></span>
    </div>
    <div class="tb-search">
      <i class="bi bi-search"></i>
      <input type="text" id="tb-search" placeholder="Rechercher…" onkeyup="handleSearch(this.value)">
    </div>
    <div class="tb-btn" onclick="loadAll()" title="Actualiser"><i class="bi bi-arrow-clockwise"></i></div>
    <!-- Bouton contacter un client -->
    <button class="btn-info" id="btn-contact-client" style="display:none;font-size:12px;padding:7px 14px" onclick="openContactClientModal()">
      <i class="bi bi-person-plus"></i> Contacter un client
    </button>
  </div>

  <!-- Bannière: aucun garage sélectionné -->
  <div id="no-garage-banner" class="no-garage-banner">
    <i class="bi bi-shop"></i>
    <h4>Sélectionnez un garage</h4>
    <p>Choisissez un garage dans le menu à gauche pour accéder à sa messagerie.</p>
  </div>

  <!-- Content (caché jusqu'à sélection d'un garage) -->
  <div class="content" id="main-content" style="display:none">

    <!-- Statistiques du garage -->
    <div class="stats-grid" id="stats-grid">
      <div class="stat-card"><div class="stat-ico r"><i class="bi bi-chat-square-dots"></i></div><div><div class="stat-val" id="s-disc">—</div><div class="stat-lbl">Mes Discussions</div></div></div>
      <div class="stat-card"><div class="stat-ico b"><i class="bi bi-envelope"></i></div><div><div class="stat-val" id="s-msg">—</div><div class="stat-lbl">Messages reçus</div></div></div>
      <div class="stat-card"><div class="stat-ico g"><i class="bi bi-people"></i></div><div><div class="stat-val" id="s-clients">—</div><div class="stat-lbl">Clients actifs</div></div></div>
      <div class="stat-card"><div class="stat-ico o"><i class="bi bi-calendar-check"></i></div><div><div class="stat-val" id="s-today">—</div><div class="stat-lbl">Aujourd'hui</div></div></div>
    </div>

    <!-- Tabs -->
    <div class="section-tabs">
      <button class="stab active" id="stab-disc"  onclick="showSection('discussions')"><i class="bi bi-chat-dots"></i> Discussions</button>
      <button class="stab"        id="stab-msg"   onclick="showSection('messages')"><i class="bi bi-envelope"></i> Messages</button>
      <button class="stab"        id="stab-chat"  onclick="showSection('chat')"><i class="bi bi-chat-left-text"></i> Vue Chat</button>
    </div>

    <!-- ════ SECTION: DISCUSSIONS ════ -->
    <div id="sec-discussions">
      <div class="card-box">
        <div class="cb-head">
          <h5><i class="bi bi-chat-dots"></i> Discussions de mon garage</h5>
          <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <button class="btn-pri" onclick="openDiscModal()"><i class="bi bi-plus-lg"></i> Nouvelle Discussion</button>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table class="tbl" id="tbl-disc">
            <thead><tr>
              <th>#</th><th>Client</th><th>Objet</th>
              <th>Messages</th><th>Dernier message</th><th>Créé le</th><th>Actions</th>
            </tr></thead>
            <tbody id="tbody-disc"><tr><td colspan="7"><span class="loading-spinner"></span></td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════ SECTION: MESSAGES ════ -->
    <div id="sec-messages" style="display:none">
      <div class="card-box">
        <div class="cb-head">
          <h5><i class="bi bi-envelope"></i> Messages de mon garage</h5>
          <div style="display:flex;gap:8px;align-items:center">
            <select id="filter-disc-msg" class="finput" style="width:220px;padding:7px 11px" onchange="filterMsgByDisc(this.value)">
              <option value="">— Toutes les discussions —</option>
            </select>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table class="tbl">
            <thead><tr><th>#</th><th>Expéditeur</th><th>Discussion</th><th>Contenu</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody id="tbody-msg"><tr><td colspan="7"><span class="loading-spinner"></span></td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- ════ SECTION: CHAT GARAGE ════ -->
    <div id="sec-chat" style="display:none">
      <div class="admin-chat">
        <div class="ac-panel">
          <div class="ac-ph">
            <span><i class="bi bi-chat-dots" style="color:var(--red)"></i> Conversations</span>
            <button class="btn-info" style="font-size:11px;padding:4px 10px;border-radius:7px" onclick="openContactClientModal()">
              <i class="bi bi-person-plus"></i> Nouveau
            </button>
          </div>
          <div class="ac-list" id="ac-list"><span class="loading-spinner"></span></div>
        </div>
        <div class="ac-chat">
          <div class="ac-hd">
            <div class="ac-av" id="ac-av" style="width:36px;height:36px">?</div>
            <div style="flex:1">
              <div class="ac-hd-title" id="ac-title">Sélectionnez une discussion</div>
              <div style="font-size:12px;color:var(--muted)" id="ac-sub"></div>
            </div>
            <div style="display:flex;gap:7px">
              <button class="abtn e" id="btn-edit-disc" style="display:none" onclick="editCurrentDisc()" title="Modifier"><i class="bi bi-pencil"></i></button>
              <button class="abtn d" id="btn-del-disc"  style="display:none" onclick="delCurrentDisc()"  title="Supprimer"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <div class="ac-msgs" id="ac-msgs">
            <div class="empty-state"><i class="bi bi-chat-square"></i><p>Sélectionnez une conversation</p></div>
          </div>
          <!-- Hint expéditeur (lecture seule, le garage actif) -->
          <div class="sender-hint" id="sender-hint" style="display:none">
            <i class="bi bi-send"></i> Envoyer en tant que <strong id="sender-hint-name">—</strong>
          </div>
          <div class="ac-input">
            <textarea class="ac-textarea" id="ac-input" placeholder="Répondre en tant que garage…"
              onkeydown="if(event.key==='Enter'&&!event.shiftKey){event.preventDefault();garageSend();}"></textarea>
            <button class="send-btn" onclick="garageSend()" title="Envoyer"><i class="bi bi-send"></i></button>
          </div>
        </div>
      </div>
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- ════ TOAST ════ -->
<div id="toast-container"></div>

<!-- ════ MODAL: Discussion (créer/modifier) ════ -->
<div class="overlay" id="modal-disc">
  <div class="mbox">
    <div class="mh">
      <h5><i class="bi bi-chat-dots"></i> <span id="md-title">Nouvelle Discussion</span></h5>
      <button class="mc" onclick="closeModal('modal-disc')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb">
      <input type="hidden" id="md-id">
      <!-- Garage pré-rempli (lecture seule) -->
      <div class="info-box">
        <i class="bi bi-shop"></i>
        Garage : <strong id="md-garage-info">—</strong>
      </div>
      <div class="fg">
        <label class="flabel">Client (Utilisateur) *</label>
        <select class="finput" id="md-user"><option value="">— Sélectionner —</option></select>
        <span class="field-error" id="err-user"></span>
      </div>
      <div class="fg">
        <label class="flabel">Objet / Sujet *</label>
        <input type="text" class="finput" id="md-objet" placeholder="Ex: Problème freins, Rendez-vous…">
        <span class="field-error" id="err-objet"></span>
      </div>
    </div>
    <div class="mf">
      <button class="btn-sec" onclick="closeModal('modal-disc')">Annuler</button>
      <button class="btn-pri" onclick="saveDisc()"><i class="bi bi-check-lg"></i> <span id="md-btn">Créer</span></button>
    </div>
  </div>
</div>

<!-- ════ MODAL: Message (modifier uniquement) ════ -->
<div class="overlay" id="modal-msg">
  <div class="mbox">
    <div class="mh">
      <h5><i class="bi bi-envelope"></i> Modifier le Message</h5>
      <button class="mc" onclick="closeModal('modal-msg')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb">
      <input type="hidden" id="mm-id">
      <div class="fg">
        <label class="flabel">Contenu *</label>
        <textarea class="finput" id="mm-contenu" placeholder="Saisir le message…"></textarea>
        <span class="field-error" id="mm-err"></span>
      </div>
    </div>
    <div class="mf">
      <button class="btn-sec" onclick="closeModal('modal-msg')">Annuler</button>
      <button class="btn-pri" onclick="saveMsg()"><i class="bi bi-check-lg"></i> Enregistrer</button>
    </div>
  </div>
</div>

<!-- ════ MODAL: Contacter un client ════ -->
<div class="overlay" id="modal-contact-client">
  <div class="mbox">
    <div class="mh">
      <h5><i class="bi bi-person-plus"></i> Contacter un client</h5>
      <button class="mc" onclick="closeModal('modal-contact-client')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb">
      <div class="info-box">
        <i class="bi bi-shop"></i>
        Message envoyé par : <strong id="cc-garage-info">—</strong>
      </div>
      <div class="fg">
        <label class="flabel">Client à contacter *</label>
        <select class="finput" id="cc-user"><option value="">— Sélectionner un client —</option></select>
        <span class="field-error" id="cc-err-user"></span>
      </div>
      <div class="fg">
        <label class="flabel">Objet de la discussion *</label>
        <input type="text" class="finput" id="cc-objet" placeholder="Ex: Devis réparation, Rappel entretien…">
        <span class="field-error" id="cc-err-objet"></span>
      </div>
      <div class="fg">
        <label class="flabel">Premier message *</label>
        <textarea class="finput" id="cc-contenu" placeholder="Bonjour, nous souhaitons vous informer…" style="min-height:90px"></textarea>
        <span class="field-error" id="cc-err-contenu"></span>
      </div>
      <p style="font-size:11.5px;color:var(--muted)">
        <i class="bi bi-info-circle"></i>
        Si une discussion existe déjà entre ce client et votre garage sur ce sujet, un nouveau fil sera créé.
      </p>
    </div>
    <div class="mf">
      <button class="btn-sec" onclick="closeModal('modal-contact-client')">Annuler</button>
      <button class="btn-pri" onclick="sendContactClient()"><i class="bi bi-send"></i> Envoyer le message</button>
    </div>
  </div>
</div>

<!-- ════ MODAL: Voir discussion ════ -->
<div class="overlay" id="modal-view-disc">
  <div class="mbox wide">
    <div class="mh">
      <h5><i class="bi bi-eye"></i> Détail de la Discussion</h5>
      <button class="mc" onclick="closeModal('modal-view-disc')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb" id="view-disc-body"></div>
    <div class="mf"><button class="btn-sec" onclick="closeModal('modal-view-disc')">Fermer</button></div>
  </div>
</div>

<!-- ════ MODAL: Confirmation ════ -->
<div class="overlay" id="modal-confirm">
  <div class="mbox" style="max-width:380px;text-align:center">
    <div class="mh" style="border:none;padding-bottom:0"><h5 style="opacity:0">_</h5><button class="mc" onclick="closeModal('modal-confirm')"><i class="bi bi-x"></i></button></div>
    <div class="mb">
      <i class="bi bi-exclamation-triangle-fill" style="font-size:44px;color:var(--red);display:block;margin-bottom:12px"></i>
      <h5 style="font-size:17px;font-weight:800;margin-bottom:8px">Confirmer la suppression</h5>
      <p id="conf-text" style="color:var(--muted);font-size:13px"></p>
    </div>
    <div class="mf" style="justify-content:center">
      <button class="btn-sec" onclick="closeModal('modal-confirm')">Annuler</button>
      <button class="btn-danger" id="conf-btn">Supprimer</button>
    </div>
  </div>
</div>

<!-- ════ SCRIPTS ════ -->
<script>const API_URL = '../../controller/MessageController.php';</script>
<script src="../../assets/js/api.js"></script>
<script src="../../assets/js/validation.js"></script>
<script>
'use strict';

// ── État global ───────────────────────────────────────────────
let allDiscs    = [];   // discussions du garage actif
let allMsgs     = [];   // messages du garage actif
let allUsers    = [];   // tous les clients
let allGarages  = [];   // tous les garages (pour le sélecteur)
let currentDid  = null; // discussion ouverte dans le chat
let activeGarage = null; // { id, nom_garages }  ← "session simulée"

/* ══════════════════════════════════════════════════════════════
   INIT
══════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', async () => {
    // Charger la liste des garages pour le sélecteur sidebar
    await loadGarages();
    await loadUsers();

    // Binding validation modal discussion
    const mdUser  = document.getElementById('md-user');
    const mdObjet = document.getElementById('md-objet');
    mdUser.addEventListener('change', () => RULES.user(mdUser, document.getElementById('err-user')));
    mdObjet.addEventListener('input', () => RULES.objet(mdObjet, document.getElementById('err-objet')));
    mdObjet.addEventListener('blur',  () => RULES.objet(mdObjet, document.getElementById('err-objet')));

    // Binding validation modal message
    const mmContenu = document.getElementById('mm-contenu');
    mmContenu.addEventListener('input', () => RULES.contenu(mmContenu, document.getElementById('mm-err')));
    mmContenu.addEventListener('blur',  () => RULES.contenu(mmContenu, document.getElementById('mm-err')));

    // Binding validation modal contacter client
    const ccUser    = document.getElementById('cc-user');
    const ccObjet   = document.getElementById('cc-objet');
    const ccContenu = document.getElementById('cc-contenu');
    ccUser.addEventListener('change', () => RULES.user(ccUser, document.getElementById('cc-err-user')));
    ccObjet.addEventListener('input', () => RULES.objet(ccObjet, document.getElementById('cc-err-objet')));
    ccObjet.addEventListener('blur',  () => RULES.objet(ccObjet, document.getElementById('cc-err-objet')));
    ccContenu.addEventListener('input', () => RULES.contenu(ccContenu, document.getElementById('cc-err-contenu')));
    ccContenu.addEventListener('blur',  () => RULES.contenu(ccContenu, document.getElementById('cc-err-contenu')));

    document.querySelectorAll('.overlay').forEach(o =>
        o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); })
    );
});

/* ══════════════════════════════════════════════════════════════
   SÉLECTION DU GARAGE (simulation session)
══════════════════════════════════════════════════════════════ */
async function loadGarages() {
    const r = await apiGet('get_garages');
    if (!r.success) return;
    allGarages = r.data;
    const sel = document.getElementById('garage-session-sel');
    sel.innerHTML = '<option value="">— Choisir un garage —</option>';
    allGarages.forEach(g => sel.insertAdjacentHTML('beforeend',
        `<option value="${g.id}">${esc(g.nom_garages)}</option>`));
}

async function switchGarage(garageId) {
    if (!garageId) {
        // Déconnexion
        activeGarage = null;
        document.getElementById('no-garage-banner').style.display = '';
        document.getElementById('main-content').style.display = 'none';
        document.getElementById('garage-identity').style.display = 'none';
        document.getElementById('topbar-garage-tag').style.display = 'none';
        document.getElementById('btn-contact-client').style.display = 'none';
        return;
    }
    activeGarage = allGarages.find(g => g.id == garageId);
    if (!activeGarage) return;

    // Mettre à jour l'UI de session
    document.getElementById('garage-identity').style.display = '';
    document.getElementById('garage-identity-name').textContent = activeGarage.nom_garages;
    document.getElementById('topbar-garage-tag').style.display = '';
    document.getElementById('topbar-garage-name').textContent = activeGarage.nom_garages;
    document.getElementById('btn-contact-client').style.display = '';
    document.getElementById('no-garage-banner').style.display = 'none';
    document.getElementById('main-content').style.display = '';

    // Charger les données du garage
    await loadAll();
    toast(`Connecté en tant que ${activeGarage.nom_garages}`, 'success');
}

/* ══════════════════════════════════════════════════════════════
   CHARGEMENT DES DONNÉES (filtré par garage actif)
══════════════════════════════════════════════════════════════ */
async function loadAll() {
    if (!activeGarage) return;
    await loadUsers();
    await Promise.all([loadDiscs(), loadGarageStats()]);
}

async function loadUsers() {
    const r = await apiGet('get_users');
    if (!r.success) return;
    allUsers = r.data;
    // Peupler les selects clients
    populateSel('md-user',  allUsers, 'nom');
    populateSel('cc-user',  allUsers, 'nom');
}

/* ── Stats spécifiques au garage ────────────────────────────── */
async function loadGarageStats() {
    // Calcul local depuis les données déjà chargées
    const today = new Date().toLocaleDateString('fr-FR');
    const discsToday = allDiscs.filter(d => {
        const dt = new Date(d.created_at);
        return isNaN(dt) ? false : dt.toLocaleDateString('fr-FR') === today;
    }).length;
    const clients = new Set(allDiscs.map(d => d.user_id)).size;
    const totalMsgs = allDiscs.reduce((acc, d) => acc + (parseInt(d.nb_messages) || 0), 0);

    document.getElementById('s-disc').textContent    = allDiscs.length;
    document.getElementById('s-msg').textContent     = totalMsgs;
    document.getElementById('s-clients').textContent = clients;
    document.getElementById('s-today').textContent   = discsToday;
    document.getElementById('sb-badge').textContent  = allDiscs.length;
}

/* ── Discussions filtrées par garage actif ──────────────────── */
async function loadDiscs() {
    if (!activeGarage) return;
    // Utiliser get_discussions et filtrer côté client par garage_id
    // (ou utiliser une action dédiée si disponible)
    const r = await apiGet('get_discussions');
    if (!r.success) return;
    // Filtrer uniquement les discussions du garage actif
    allDiscs = r.data.filter(d => d.garage_id == activeGarage.id);
    renderDiscTable(allDiscs);
    renderChatList(allDiscs);
    loadGarageStats();

    // Remplir filter disc dans onglet messages
    const fd = document.getElementById('filter-disc-msg');
    fd.innerHTML = '<option value="">— Toutes les discussions —</option>';
    allDiscs.forEach(d => fd.insertAdjacentHTML('beforeend',
        `<option value="${d.id}">#${d.id} — ${esc(d.objet)}</option>`));
}

/* ── Messages du garage (toutes ses discussions) ────────────── */
async function loadAllMsgs() {
    const r = await apiGet('get_all_messages');
    if (!r.success) return;
    // Filtrer les messages appartenant aux discussions de ce garage
    const discIds = new Set(allDiscs.map(d => d.id));
    allMsgs = r.data.filter(m => discIds.has(parseInt(m.discussion_id)));
    renderMsgTable(allMsgs);
}

/* ══════════════════════════════════════════════════════════════
   RENDER TABLES
══════════════════════════════════════════════════════════════ */
function renderDiscTable(list) {
    const tb = document.getElementById('tbody-disc');
    if (!list.length) {
        tb.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="bi bi-chat-square-dots"></i><p>Aucune discussion pour ce garage</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = list.map(d => `
    <tr>
      <td><strong>#${d.id}</strong></td>
      <td><span class="tag-user"><span class="av">${(d.nom_user||'?')[0].toUpperCase()}</span>${esc(d.nom_user||'—')}</span></td>
      <td><span class="tag-subj">${esc(d.objet)}</span></td>
      <td><span class="nb-badge">${d.nb_messages||0}</span></td>
      <td class="date-txt">${fmt(d.dernier_message)}</td>
      <td class="date-txt">${fmt(d.created_at)}</td>
      <td><div class="acts">
        <button class="abtn v" title="Voir"      onclick="viewDisc(${d.id})"><i class="bi bi-eye"></i></button>
        <button class="abtn e" title="Modifier"  onclick="openDiscModal(${d.id})"><i class="bi bi-pencil"></i></button>
        <button class="abtn d" title="Supprimer" onclick="confDel('discussion',${d.id})"><i class="bi bi-trash"></i></button>
        <button class="abtn c" title="Chat"      onclick="openChat(${d.id})"><i class="bi bi-chat"></i></button>
      </div></td>
    </tr>`).join('');
}

function renderMsgTable(list) {
    const tb = document.getElementById('tbody-msg');
    if (!list.length) {
        tb.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="bi bi-envelope"></i><p>Aucun message</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = list.map(m=>`
    <tr>
      <td><strong>#${m.id}</strong></td>
      <td>
        <span class="${m.expediteur_type==='garage'?'tag-garage':'tag-user'}">
          <span class="av">${(m.nom_expediteur||'?')[0].toUpperCase()}</span>
          ${esc(m.nom_expediteur||'—')}
        </span>
      </td>
      <td><span class="tag-subj" style="font-size:11px">${esc(m.sujet_discussion||'—')}</span></td>
      <td><span class="msg-prev">${esc(m.contenu)}</span></td>
      <td><span style="background:${m.expediteur_type==='garage'?'rgba(23,162,184,.1)':'rgba(40,167,69,.1)'};color:${m.expediteur_type==='garage'?'var(--info)':'var(--success)'};border-radius:5px;padding:2px 8px;font-size:11px;font-weight:700">${m.expediteur_type==='garage'?'Garage':'Client'}</span></td>
      <td class="date-txt">${fmt(m.date_envoi)}</td>
      <td><div class="acts">
        <button class="abtn e" title="Modifier"  onclick="openMsgModal(${m.id})"><i class="bi bi-pencil"></i></button>
        <button class="abtn d" title="Supprimer" onclick="confDel('message',${m.id})"><i class="bi bi-trash"></i></button>
      </div></td>
    </tr>`).join('');
}

function filterMsgByDisc(did) {
    if (!did) { renderMsgTable(allMsgs); return; }
    renderMsgTable(allMsgs.filter(m => m.discussion_id == did));
}

/* ══════════════════════════════════════════════════════════════
   MODAL DISCUSSION (créer / modifier)
══════════════════════════════════════════════════════════════ */
async function openDiscModal(id = null) {
    if (!activeGarage) { toast('Sélectionnez un garage d\'abord', 'error'); return; }
    clearAllErrors(document.getElementById('modal-disc'));
    document.getElementById('md-id').value    = '';
    document.getElementById('md-user').value  = '';
    document.getElementById('md-objet').value = '';
    document.getElementById('md-garage-info').textContent = activeGarage.nom_garages;
    document.getElementById('md-title').textContent = id ? 'Modifier la Discussion' : 'Nouvelle Discussion';
    document.getElementById('md-btn').textContent   = id ? 'Enregistrer' : 'Créer';
    populateSel('md-user', allUsers, 'nom');

    if (id) {
        const r = await apiGet('get_discussion', { id });
        if (r.success) {
            const d = r.data;
            document.getElementById('md-id').value    = d.id;
            document.getElementById('md-objet').value = d.objet;
            await new Promise(res => setTimeout(res, 10));
            document.getElementById('md-user').value  = d.user_id;
        }
    }
    openModal('modal-disc');
}

async function saveDisc() {
    const userField  = document.getElementById('md-user');
    const objetField = document.getElementById('md-objet');
    const okUser  = RULES.user(userField,  document.getElementById('err-user'));
    const okObjet = RULES.objet(objetField, document.getElementById('err-objet'));
    if (!okUser || !okObjet) return;

    const id = document.getElementById('md-id').value;
    const data = {
        user_id:   userField.value,
        garage_id: activeGarage.id,   // ← toujours le garage actif
        objet:     objetField.value.trim(),
    };
    if (id) data.id = id;

    const r = await apiPost(id ? 'update_discussion' : 'create_discussion', data);
    if (r.success) {
        toast(r.message, 'success');
        closeModal('modal-disc');
        await loadDiscs();
    } else toast(r.message || 'Erreur', 'error');
}

function editCurrentDisc() { if (currentDid) openDiscModal(currentDid); }

/* ══════════════════════════════════════════════════════════════
   MODAL MESSAGE (modifier uniquement — le garage ne crée pas
   de message hors chat, il répond via la vue chat)
══════════════════════════════════════════════════════════════ */
async function openMsgModal(id) {
    clearAllErrors(document.getElementById('modal-msg'));
    document.getElementById('mm-id').value      = '';
    document.getElementById('mm-contenu').value = '';
    const r = await apiGet('get_message', { id });
    if (r.success) {
        document.getElementById('mm-id').value      = r.data.id;
        document.getElementById('mm-contenu').value = r.data.contenu;
    }
    openModal('modal-msg');
}

async function saveMsg() {
    const contenu = document.getElementById('mm-contenu');
    if (!RULES.contenu(contenu, document.getElementById('mm-err'))) return;
    const id = document.getElementById('mm-id').value;
    if (!id) return;
    const r = await apiPost('update_message', { id, contenu: contenu.value.trim() });
    if (r.success) {
        toast(r.message, 'success');
        closeModal('modal-msg');
        await loadDiscs();
        await loadAllMsgs();
        if (currentDid) loadChatMsgs(currentDid);
    } else toast(r.message || 'Erreur', 'error');
}

/* ══════════════════════════════════════════════════════════════
   MODAL CONTACTER UN CLIENT (nouvelle fonctionnalité)
══════════════════════════════════════════════════════════════ */
function openContactClientModal() {
    if (!activeGarage) { toast('Sélectionnez un garage d\'abord', 'error'); return; }
    clearAllErrors(document.getElementById('modal-contact-client'));
    document.getElementById('cc-user').value    = '';
    document.getElementById('cc-objet').value   = '';
    document.getElementById('cc-contenu').value = '';
    document.getElementById('cc-garage-info').textContent = activeGarage.nom_garages;
    populateSel('cc-user', allUsers, 'nom');
    openModal('modal-contact-client');
}

async function sendContactClient() {
    const userField    = document.getElementById('cc-user');
    const objetField   = document.getElementById('cc-objet');
    const contenuField = document.getElementById('cc-contenu');

    const okUser    = RULES.user(userField,    document.getElementById('cc-err-user'));
    const okObjet   = RULES.objet(objetField,  document.getElementById('cc-err-objet'));
    const okContenu = RULES.contenu(contenuField, document.getElementById('cc-err-contenu'));
    if (!okUser || !okObjet || !okContenu) return;

    // 1. Créer la discussion (garage → client)
    const rDisc = await apiPost('create_discussion', {
        user_id:   userField.value,
        garage_id: activeGarage.id,
        objet:     objetField.value.trim(),
    });
    if (!rDisc.success) { toast(rDisc.message || 'Erreur création discussion', 'error'); return; }

    const newDiscId = rDisc.data.id;

    // 2. Envoyer le premier message en tant que garage
    const rMsg = await apiPost('create_message', {
        discussion_id:   newDiscId,
        expediteur_id:   activeGarage.id,
        expediteur_type: 'garage',
        contenu:         contenuField.value.trim(),
    });
    if (!rMsg.success) { toast(rMsg.message || 'Erreur envoi message', 'error'); return; }

    toast(`Message envoyé à ${esc(allUsers.find(u => u.id == userField.value)?.nom || 'ce client')}`, 'success');
    closeModal('modal-contact-client');
    await loadDiscs();
    // Ouvrir directement le chat sur la nouvelle discussion
    await openChat(newDiscId);
    showSection('chat');
}

/* ══════════════════════════════════════════════════════════════
   VUE DISCUSSION (détail)
══════════════════════════════════════════════════════════════ */
async function viewDisc(id) {
    const rd = await apiGet('get_discussion', { id });
    const rm = await apiGet('get_messages',   { discussion_id: id });
    if (!rd.success) return;
    const d = rd.data, msgs = rm.success ? rm.data : [];
    document.getElementById('view-disc-body').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
      <div><div class="flabel">Client</div><span class="tag-user"><span class="av">${(d.nom_user||'?')[0].toUpperCase()}</span>${esc(d.nom_user||'—')}</span></div>
      <div><div class="flabel">Garage</div><span class="tag-garage"><span class="av">${(d.nom_garage||'?')[0].toUpperCase()}</span>${esc(d.nom_garage||'—')}</span></div>
      <div><div class="flabel">Objet</div><span class="tag-subj">${esc(d.objet)}</span></div>
      <div><div class="flabel">Créé le</div><span class="date-txt">${fmt(d.created_at)}</span></div>
    </div>
    <div class="flabel" style="margin-bottom:10px">Messages (${msgs.length})</div>
    <div style="max-height:250px;overflow-y:auto;display:flex;flex-direction:column;gap:7px;background:var(--bg);border-radius:9px;padding:12px">
      ${msgs.length ? msgs.map(m=>`
        <div style="display:flex;gap:9px;align-items:flex-start">
          <div class="ac-av" style="width:28px;height:28px;font-size:11px;flex-shrink:0;background:${m.expediteur_type==='garage'?'linear-gradient(135deg,var(--info),#0d5466)':'linear-gradient(135deg,var(--success),#1a4a2e)'}">${(m.nom_expediteur||'?')[0].toUpperCase()}</div>
          <div>
            <div style="font-size:12px;font-weight:700">${esc(m.nom_expediteur||'?')}
              <span style="color:var(--muted);font-weight:400;font-size:11px"> · ${fmt(m.date_envoi)}</span>
              <span style="background:${m.expediteur_type==='garage'?'rgba(23,162,184,.1)':'rgba(40,167,69,.1)'};color:${m.expediteur_type==='garage'?'var(--info)':'var(--success)'};border-radius:5px;padding:1px 6px;font-size:10px;margin-left:4px">${m.expediteur_type==='garage'?'Garage':'Client'}</span>
            </div>
            <div style="font-size:13px;margin-top:3px">${esc(m.contenu)}</div>
          </div>
        </div>`).join('') :
        '<div class="empty-state" style="padding:20px"><i class="bi bi-chat-square" style="font-size:26px"></i><p>Aucun message</p></div>'}
    </div>`;
    openModal('modal-view-disc');
}

/* ══════════════════════════════════════════════════════════════
   CHAT GARAGE
══════════════════════════════════════════════════════════════ */
function renderChatList(list) {
    const cl = document.getElementById('ac-list');
    if (!list.length) {
        cl.innerHTML = '<div class="empty-state" style="padding:20px"><i class="bi bi-chat-square" style="font-size:28px"></i><p>Aucune conversation</p></div>';
        return;
    }
    cl.innerHTML = list.map(d => `
    <div class="ac-item ${currentDid == d.id ? 'active' : ''}" id="aci-${d.id}" onclick="openChat(${d.id})">
      <div class="ac-av">${(d.nom_user||'?')[0].toUpperCase()}</div>
      <div class="ac-info">
        <div class="ac-name">${esc(d.objet)}</div>
        <div class="ac-sub">Client : ${esc(d.nom_user||'?')}</div>
      </div>
      ${d.nb_messages > 0 ? `<div class="ac-nb">${d.nb_messages}</div>` : ''}
    </div>`).join('');
}

async function openChat(id) {
    currentDid = id;
    showSection('chat');
    document.querySelectorAll('.ac-item').forEach(el => el.classList.remove('active'));
    const eli = document.getElementById('aci-' + id);
    if (eli) eli.classList.add('active');

    const disc = allDiscs.find(d => d.id == id);
    if (disc) {
        document.getElementById('ac-av').textContent    = (disc.nom_user || '?')[0].toUpperCase();
        document.getElementById('ac-title').textContent = disc.objet;
        document.getElementById('ac-sub').textContent   = `Client : ${disc.nom_user || '?'}`;
    }
    document.getElementById('btn-edit-disc').style.display = '';
    document.getElementById('btn-del-disc').style.display  = '';

    // Afficher le hint "envoie en tant que garage"
    if (activeGarage) {
        document.getElementById('sender-hint').style.display = '';
        document.getElementById('sender-hint-name').textContent = activeGarage.nom_garages;
    }

    await loadChatMsgs(id);
}

async function loadChatMsgs(discId) {
    const area = document.getElementById('ac-msgs');
    area.innerHTML = '<span class="loading-spinner"></span>';
    const r = await apiGet('get_messages', { discussion_id: discId });
    if (!r.success || !r.data.length) {
        area.innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Aucun message</p></div>';
        return;
    }
    let lastDate = '';
    area.innerHTML = r.data.map(m => {
        // "sent" = envoyé par le garage actif
        const isSent = m.expediteur_type === 'garage' && m.expediteur_id == activeGarage?.id;
        const d = new Date(m.date_envoi);
        const dateStr = isNaN(d) ? '' : d.toLocaleDateString('fr-FR');
        let sep = '';
        if (dateStr && dateStr !== lastDate) { lastDate = dateStr; sep = `<div class="date-sep">${dateStr}</div>`; }
        return `${sep}
        <div style="display:flex;flex-direction:column;align-items:${isSent ? 'flex-end' : 'flex-start'}">
          ${!isSent ? `<div class="bbl-sender">${esc(m.nom_expediteur || '?')}</div>` : ''}
          <div class="bbl ${isSent ? 'sent' : 'recv'}">
            ${esc(m.contenu)}
            <div class="bbl-acts">
              <button class="bba" onclick="openMsgModal(${m.id})" title="Modifier"><i class="bi bi-pencil"></i></button>
              <button class="bba" onclick="confDel('message',${m.id})" title="Supprimer"><i class="bi bi-trash"></i></button>
            </div>
            <div class="bbl-meta">${esc(m.nom_expediteur || '?')} · ${timeStr(m.date_envoi)}</div>
          </div>
        </div>`;
    }).join('');
    area.scrollTop = area.scrollHeight;
}

/* ── Envoi d'un message en tant que garage ──────────────────── */
async function garageSend() {
    if (!currentDid)  { toast('Sélectionnez une conversation', 'error'); return; }
    if (!activeGarage){ toast('Aucun garage sélectionné', 'error'); return; }
    const inputEl = document.getElementById('ac-input');
    if (!validateMessageForm(inputEl)) return;

    const r = await apiPost('create_message', {
        discussion_id:   currentDid,
        expediteur_id:   activeGarage.id,   // ← l'ID du garage actif
        expediteur_type: 'garage',           // ← toujours garage
        contenu:         inputEl.value.trim(),
    });
    if (r.success) {
        inputEl.value = '';
        inputEl.classList.remove('is-valid', 'is-invalid');
        await loadChatMsgs(currentDid);
        await loadDiscs();
        await loadAllMsgs();
    } else toast(r.message || 'Erreur', 'error');
}

function delCurrentDisc() { if (currentDid) confDel('discussion', currentDid); }

/* ══════════════════════════════════════════════════════════════
   SUPPRESSION CONFIRMÉE
══════════════════════════════════════════════════════════════ */
function confDel(type, id) {
    const texts = {
        discussion: `Supprimer la discussion #${id} et tous ses messages ?`,
        message:    `Supprimer ce message #${id} ?`,
    };
    document.getElementById('conf-text').textContent = texts[type];
    document.getElementById('conf-btn').onclick = async () => {
        const action = type === 'discussion' ? 'delete_discussion' : 'delete_message';
        const r = await apiPost(action, { id });
        if (r.success) {
            toast(r.message, 'success');
            closeModal('modal-confirm');
            if (type === 'discussion' && currentDid == id) {
                currentDid = null;
                document.getElementById('ac-msgs').innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Sélectionnez une conversation</p></div>';
                document.getElementById('btn-edit-disc').style.display = 'none';
                document.getElementById('btn-del-disc').style.display  = 'none';
                document.getElementById('sender-hint').style.display   = 'none';
            }
            await loadDiscs();
            await loadAllMsgs();
        } else toast(r.message || 'Erreur', 'error');
    };
    openModal('modal-confirm');
}

/* ══════════════════════════════════════════════════════════════
   RECHERCHE
══════════════════════════════════════════════════════════════ */
let _searchTimer = null;
async function handleSearch(q) {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(async () => {
        if (!q.trim()) {
            renderDiscTable(allDiscs);
            renderMsgTable(allMsgs);
            return;
        }
        const ql = q.toLowerCase();
        // Recherche locale (déjà filtrée par garage)
        const sec = document.getElementById('sec-discussions').style.display !== 'none' ? 'disc' : 'msg';
        if (sec === 'disc') {
            renderDiscTable(allDiscs.filter(d =>
                (d.objet || '').toLowerCase().includes(ql) ||
                (d.nom_user || '').toLowerCase().includes(ql)
            ));
        } else {
            renderMsgTable(allMsgs.filter(m =>
                (m.contenu || '').toLowerCase().includes(ql) ||
                (m.nom_expediteur || '').toLowerCase().includes(ql)
            ));
        }
    }, 350);
}

/* ══════════════════════════════════════════════════════════════
   SECTION SWITCHING
══════════════════════════════════════════════════════════════ */
function showSection(s) {
    ['discussions', 'messages', 'chat'].forEach(n => {
        document.getElementById('sec-' + n).style.display = n === s ? '' : 'none';
        document.getElementById('stab-' + ({ discussions: 'disc', messages: 'msg', chat: 'chat' }[n] || n)).classList.toggle('active', n === s);
    });
    if (s === 'chat') renderChatList(allDiscs);
    if (s === 'messages') loadAllMsgs();
}

/* ══════════════════════════════════════════════════════════════
   HELPERS
══════════════════════════════════════════════════════════════ */
function populateSel(id, list, nameKey, placeholder = '— Sélectionner —') {
    const el = document.getElementById(id);
    if (!el) return;
    const cur = el.value;
    el.innerHTML = `<option value="">${placeholder}</option>`;
    list.forEach(item => el.insertAdjacentHTML('beforeend',
        `<option value="${item.id}">${esc(item[nameKey])}</option>`));
    if (cur) el.value = cur;
}

function openModal(id)  { document.getElementById(id).classList.add('show'); }
function closeModal(id) { document.getElementById(id).classList.remove('show'); }

function esc(s) { if (!s) return ''; return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function fmt(dt) { if (!dt) return '—'; const d = new Date(dt); if (isNaN(d)) return dt; return d.toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit', year: 'numeric' }) + ' ' + d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }); }
function timeStr(dt) { if (!dt) return ''; const d = new Date(dt); return isNaN(d) ? '' : d.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }); }
</script>
</body>
</html>