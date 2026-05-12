<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$sessionGarageId   = isset($_SESSION['garagiste_id']) ? (int)$_SESSION['garagiste_id'] : 0;
$sessionGarageName = trim((($_SESSION['prenom'] ?? '') . ' ' . ($_SESSION['nom'] ?? '')));
$sessionRole       = $_SESSION['role'] ?? '';
?>
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

/* SIDEBAR */
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
.sb-garage-picker{padding:14px 18px;border-top:1px solid rgba(255,255,255,.06)}
.sb-garage-label{font-size:10px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:rgba(255,255,255,.3);margin-bottom:7px}
.sb-garage-sel{width:100%;background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.12);border-radius:9px;padding:8px 11px;color:#fff;font-family:'Nunito',sans-serif;font-size:13px;font-weight:600;cursor:pointer;outline:none;transition:border .2s}
.sb-garage-sel:focus{border-color:var(--red)}
.sb-garage-sel option{background:#1a1a2e;color:#fff}
.garage-badge{display:flex;align-items:center;gap:9px;padding:10px 14px;background:rgba(233,69,96,.1);border-radius:9px;margin:0 18px 12px;border:1px solid rgba(233,69,96,.18)}
.garage-badge .gico{width:32px;height:32px;border-radius:8px;background:var(--red);display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;flex-shrink:0}
.garage-badge .gname{font-size:13px;font-weight:700;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.garage-badge .grole{font-size:10px;color:rgba(255,255,255,.45)}

/* MAIN */
.main{margin-left:var(--sb-w);flex:1;display:flex;flex-direction:column;min-width:0}
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
.no-garage-banner{background:linear-gradient(135deg,rgba(233,69,96,.08),rgba(233,69,96,.03));border:2px dashed rgba(233,69,96,.3);border-radius:var(--radius);padding:40px;text-align:center;margin:26px}
.no-garage-banner i{font-size:48px;color:rgba(233,69,96,.3);display:block;margin-bottom:14px}
.no-garage-banner h4{font-size:18px;font-weight:800;color:var(--txt);margin-bottom:8px}
.no-garage-banner p{color:var(--muted);font-size:14px}
.content{padding:26px}

/* STATS */
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

/* TABS */
.section-tabs{display:flex;gap:5px;background:var(--white);border-radius:var(--radius);padding:5px;box-shadow:var(--shadow);margin-bottom:18px;width:fit-content}
.stab{padding:8px 18px;border-radius:8px;border:none;background:transparent;font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;color:var(--muted);cursor:pointer;display:flex;align-items:center;gap:6px;transition:all .2s}
.stab.active{background:var(--red);color:#fff;box-shadow:0 4px 12px rgba(233,69,96,.32)}
.stab:hover:not(.active){background:var(--bg);color:var(--txt)}

/* CARD */
.card-box{background:var(--white);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden}
.cb-head{padding:16px 22px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap}
.cb-head h5{font-size:14.5px;font-weight:800;margin:0;display:flex;align-items:center;gap:7px}
.cb-head h5 i{color:var(--red)}

/* TABLE */
.tbl{width:100%;border-collapse:collapse}
.tbl thead th{padding:11px 14px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);background:var(--bg);border-bottom:1px solid var(--border);white-space:nowrap}
.tbl tbody tr{transition:background .15s}
.tbl tbody tr:hover{background:#fafbff}
.tbl tbody td{padding:13px 14px;font-size:13.5px;border-bottom:1px solid var(--border);vertical-align:middle}
.tbl tbody tr:last-child td{border-bottom:none}

/* BADGES */
.tag-subj{background:rgba(233,69,96,.08);color:var(--red);border:1px solid rgba(233,69,96,.2);border-radius:6px;padding:3px 9px;font-size:12px;font-weight:700}
.tag-user{display:inline-flex;align-items:center;gap:5px;background:var(--bg);border:1px solid var(--border);border-radius:18px;padding:2px 9px 2px 3px;font-size:12px;font-weight:600}
.tag-user .av{width:20px;height:20px;border-radius:50%;background:var(--success);color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center}
.tag-garage{display:inline-flex;align-items:center;gap:5px;background:rgba(23,162,184,.07);border:1px solid rgba(23,162,184,.2);border-radius:18px;padding:2px 9px 2px 3px;font-size:12px;font-weight:600;color:var(--info)}
.tag-garage .av{background:var(--info);width:20px;height:20px;border-radius:50%;color:#fff;font-size:9px;font-weight:700;display:flex;align-items:center;justify-content:center}
.nb-badge{background:var(--red);color:#fff;border-radius:9px;padding:2px 8px;font-size:11px;font-weight:700}
.date-txt{font-size:12px;color:var(--muted)}
.msg-prev{color:var(--muted);font-size:12.5px;max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}

/* TYPE BADGE */
.type-badge{display:inline-flex;align-items:center;gap:4px;border-radius:6px;padding:2px 8px;font-size:11px;font-weight:700}
.type-text{background:rgba(40,167,69,.1);color:var(--success)}
.type-image{background:rgba(23,162,184,.1);color:var(--info)}
.type-audio{background:rgba(255,193,7,.1);color:#c59b00}

/* ACTION BUTTONS */
.acts{display:flex;gap:5px}
.abtn{width:30px;height:30px;border-radius:7px;border:none;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:12px;transition:all .2s}
.abtn.v{background:rgba(23,162,184,.1);color:var(--info)}
.abtn.e{background:rgba(255,193,7,.1);color:#c59b00}
.abtn.d{background:rgba(233,69,96,.1);color:var(--red)}
.abtn.c{background:rgba(40,167,69,.1);color:var(--success)}
.abtn.ai{background:rgba(103,58,183,.1);color:#673ab7}
.abtn:hover{transform:scale(1.1)}

/* CHAT GARAGE */
.admin-chat{display:grid;grid-template-columns:270px 1fr;gap:18px;height:580px}
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

/* BUBBLES */
.bbl-wrap{display:flex;flex-direction:column}
.bbl-wrap.sent{align-items:flex-end}
.bbl-wrap.recv{align-items:flex-start}
.bbl-sender{font-size:11px;color:var(--muted);font-weight:700;margin-bottom:3px;padding:0 8px}
.bbl-hover{position:relative;max-width:65%}
.bbl{padding:9px 13px 7px;border-radius:14px;font-size:13.5px;line-height:1.5;word-break:break-word;width:100%}
.bbl.sent{background:var(--red);color:#fff;border-bottom-right-radius:4px}
.bbl.recv{background:var(--white);color:var(--txt);border-bottom-left-radius:4px;box-shadow:0 2px 6px rgba(0,0,0,.06)}
.bbl-meta{font-size:10px;margin-top:4px;opacity:.65;text-align:right}
.bbl-acts{position:absolute;top:-26px;left:0;display:flex;gap:3px;opacity:0;pointer-events:none;transition:opacity .15s ease .6s}
.bbl-hover:hover .bbl-acts{opacity:1;pointer-events:auto;transition:opacity .15s ease 0s}
.bba{width:22px;height:22px;border-radius:5px;border:none;background:#fff;box-shadow:0 1px 5px rgba(0,0,0,.12);cursor:pointer;font-size:10px;color:var(--muted);display:flex;align-items:center;justify-content:center}
.bba:hover{color:var(--red)}

/* MEDIA IN BUBBLES */
.bbl-img{max-width:200px;border-radius:8px;display:block;cursor:pointer;margin-bottom:3px}
.bbl-img:hover{opacity:.9}
.voice-player{display:flex;align-items:center;gap:8px;padding:4px 0;min-width:160px}
.voice-play-btn{width:28px;height:28px;border-radius:50%;border:none;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;transition:all .2s}
.bbl.sent  .voice-play-btn{background:rgba(255,255,255,.2);color:#fff}
.bbl.recv  .voice-play-btn{background:var(--red);color:#fff}
.voice-play-btn:hover{transform:scale(1.1)}
.voice-waveform{flex:1;height:20px;display:flex;align-items:center;gap:2px}
.wave-bar{width:3px;border-radius:2px;background:currentColor;opacity:.5;flex-shrink:0}

/* INPUT ZONE */
.ac-input-zone{border-top:1px solid var(--border);background:var(--white)}
.sender-hint{font-size:11px;color:var(--muted);padding:6px 16px 0;display:flex;align-items:center;gap:5px}
.sender-hint strong{color:var(--info)}
.ac-input{padding:10px 14px;display:flex;align-items:flex-end;gap:9px}
.ac-textarea{flex:1;border:1.5px solid var(--border);border-radius:20px;padding:8px 15px;font-size:13px;font-family:'Nunito',sans-serif;resize:none;min-height:40px;max-height:100px;outline:none;transition:border .2s}
.ac-textarea:focus{border-color:var(--red)}
.ac-textarea.is-invalid{border-color:var(--red)!important}
.send-btn{width:40px;height:40px;border-radius:50%;background:var(--red);border:none;color:#fff;font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center;box-shadow:0 4px 12px rgba(233,69,96,.3);transition:all .2s;flex-shrink:0}
.send-btn:hover{background:var(--red-dk);transform:scale(1.06)}

/* AI SUMMARY BOX */
.ai-summary-box{background:linear-gradient(135deg,rgba(103,58,183,.06),rgba(103,58,183,.02));border:1px solid rgba(103,58,183,.2);border-radius:10px;padding:14px 16px;margin:12px 16px;font-size:13px;display:none}
.ai-summary-box.show{display:block}
.ai-summary-title{font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#673ab7;margin-bottom:8px;display:flex;align-items:center;gap:6px}
.ai-summary-content{color:var(--txt);line-height:1.6}
.ai-summary-content ul{margin:4px 0 0 16px;padding:0}
.ai-summary-content li{margin-bottom:3px}
.ai-summary-loading{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:12px}

/* IMAGE MODAL */
.img-modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.88);z-index:9998;align-items:center;justify-content:center;cursor:zoom-out}
.img-modal-overlay.show{display:flex}
.img-modal-overlay img{max-width:92vw;max-height:88vh;border-radius:10px}

/* MODAL */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2000;align-items:center;justify-content:center}
.overlay.show{display:flex;animation:fadeIn .2s}
@keyframes fadeIn{from{opacity:0}to{opacity:1}}
.mbox{background:#fff;border-radius:16px;width:100%;max-width:500px;max-height:90vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:slideUp .25s}
.mbox.wide{max-width:700px}
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
.btn-ai{background:rgba(103,58,183,.1);color:#673ab7;border:1.5px solid rgba(103,58,183,.25);border-radius:9px;padding:9px 20px;font-family:'Nunito',sans-serif;font-size:13px;font-weight:700;cursor:pointer;transition:all .2s;display:inline-flex;align-items:center;gap:6px}
.btn-ai:hover{background:#673ab7;color:#fff}
.info-box{background:rgba(23,162,184,.07);border:1px solid rgba(23,162,184,.2);border-radius:9px;padding:10px 13px;font-size:12.5px;color:var(--info);display:flex;align-items:center;gap:8px;margin-bottom:16px}

/* TOAST */
#toast-container{position:fixed;bottom:22px;right:22px;z-index:9999;display:flex;flex-direction:column;gap:8px}
.toast-item{background:#fff;border-radius:11px;padding:12px 16px;box-shadow:0 8px 28px rgba(0,0,0,.14);display:flex;align-items:center;gap:9px;font-size:13px;font-weight:600;min-width:240px;border-left:4px solid;animation:slideIn .3s}
.toast-success{border-color:var(--success)}.toast-error{border-color:var(--red)}.toast-info{border-color:var(--info)}
.toast-icon{font-size:16px}
.toast-success .toast-icon{color:var(--success)}.toast-error .toast-icon{color:var(--red)}.toast-info .toast-icon{color:var(--info)}
.toast-hide{animation:fadeOut .4s forwards}
@keyframes slideIn{from{opacity:0;transform:translateX(36px)}to{opacity:1;transform:translateX(0)}}
@keyframes fadeOut{to{opacity:0;transform:translateX(36px)}}

/* MISC */
.loading-spinner{width:24px;height:24px;border:2.5px solid rgba(233,69,96,.2);border-top-color:var(--red);border-radius:50%;animation:spin .7s linear infinite;margin:28px auto;display:block}
@keyframes spin{to{transform:rotate(360deg)}}
.empty-state{text-align:center;padding:40px;color:var(--muted)}
.empty-state i{font-size:40px;opacity:.2;display:block;margin-bottom:10px}
.empty-state p{font-size:14px;font-weight:700}
.date-sep{text-align:center;font-size:11px;color:var(--muted);margin:4px 0;position:relative}
.date-sep::before,.date-sep::after{content:'';position:absolute;top:50%;width:32%;height:1px;background:var(--border)}
.date-sep::before{left:0}.date-sep::after{right:0}
@media(max-width:1100px){.stats-grid{grid-template-columns:repeat(2,1fr)}}
@media(max-width:860px){.admin-chat{grid-template-columns:1fr;height:auto}.ac-panel{height:250px}.ac-chat{height:420px}}
</style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sb-logo">
    <div class="ico"><i class="bi bi-shop"></i></div>
    <div class="txt">Auto<span>Service</span></div>
  </div>
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
  <div class="sb-garage-picker" style="margin-top:auto;<?= $sessionGarageId ? 'display:none;' : '' ?>">
    <div class="sb-garage-label"><i class="bi bi-shield-lock" style="margin-right:4px"></i> Connecté en tant que</div>
    <select class="sb-garage-sel" id="garage-session-sel" onchange="switchGarage(this.value)">
      <option value="">— Choisir un garage —</option>
    </select>
  </div>
</aside>

<!-- MAIN -->
<div class="main">
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
    <button class="btn-info" id="btn-contact-client" style="display:none;font-size:12px;padding:7px 14px" onclick="openContactClientModal()">
      <i class="bi bi-person-plus"></i> Contacter un client
    </button>
  </div>

  <div id="no-garage-banner" class="no-garage-banner">
    <i class="bi bi-shop"></i>
    <h4>Sélectionnez un garage</h4>
    <p>Choisissez un garage dans le menu à gauche pour accéder à sa messagerie.</p>
  </div>

  <div class="content" id="main-content" style="display:none">

    <!-- Stats -->
    <div class="stats-grid">
      <div class="stat-card"><div class="stat-ico r"><i class="bi bi-chat-square-dots"></i></div><div><div class="stat-val" id="s-disc">—</div><div class="stat-lbl">Mes Discussions</div></div></div>
      <div class="stat-card"><div class="stat-ico b"><i class="bi bi-envelope"></i></div><div><div class="stat-val" id="s-msg">—</div><div class="stat-lbl">Messages total</div></div></div>
      <div class="stat-card"><div class="stat-ico g"><i class="bi bi-people"></i></div><div><div class="stat-val" id="s-clients">—</div><div class="stat-lbl">Clients actifs</div></div></div>
      <div class="stat-card"><div class="stat-ico o"><i class="bi bi-calendar-check"></i></div><div><div class="stat-val" id="s-today">—</div><div class="stat-lbl">Aujourd'hui</div></div></div>
    </div>

    <!-- Tabs -->
    <div class="section-tabs">
      <button class="stab active" id="stab-disc"  onclick="showSection('discussions')"><i class="bi bi-chat-dots"></i> Discussions</button>
      <button class="stab"        id="stab-msg"   onclick="showSection('messages')"><i class="bi bi-envelope"></i> Messages</button>
      <button class="stab"        id="stab-chat"  onclick="showSection('chat')"><i class="bi bi-chat-left-text"></i> Vue Chat</button>
    </div>

    <!-- SECTION DISCUSSIONS -->
    <div id="sec-discussions">
      <div class="card-box">
        <div class="cb-head">
          <h5><i class="bi bi-chat-dots"></i> Discussions de mon garage</h5>
          <button class="btn-pri" onclick="openDiscModal()"><i class="bi bi-plus-lg"></i> Nouvelle Discussion</button>
        </div>
        <div style="overflow-x:auto">
          <table class="tbl">
            <thead><tr><th>#</th><th>Client</th><th>Objet</th><th>Messages</th><th>Dernier message</th><th>Créé le</th><th>Actions</th></tr></thead>
            <tbody id="tbody-disc"><tr><td colspan="7"><span class="loading-spinner"></span></td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SECTION MESSAGES -->
    <div id="sec-messages" style="display:none">
      <div class="card-box">
        <div class="cb-head">
          <h5><i class="bi bi-envelope"></i> Messages de mon garage</h5>
          <select id="filter-disc-msg" class="finput" style="width:220px;padding:7px 11px" onchange="filterMsgByDisc(this.value)">
            <option value="">— Toutes les discussions —</option>
          </select>
        </div>
        <div style="overflow-x:auto">
          <table class="tbl">
            <thead><tr><th>#</th><th>Expéditeur</th><th>Discussion</th><th>Contenu</th><th>Type</th><th>Date</th><th>Actions</th></tr></thead>
            <tbody id="tbody-msg"><tr><td colspan="7"><span class="loading-spinner"></span></td></tr></tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- SECTION CHAT GARAGE -->
    <div id="sec-chat" style="display:none">
      <div class="admin-chat">
        <!-- Liste conversations -->
        <div class="ac-panel">
          <div class="ac-ph">
            <span><i class="bi bi-chat-dots" style="color:var(--red)"></i> Conversations</span>
            <button class="btn-info" style="font-size:11px;padding:4px 10px;border-radius:7px" onclick="openContactClientModal()">
              <i class="bi bi-person-plus"></i> Nouveau
            </button>
          </div>
          <div class="ac-list" id="ac-list"><span class="loading-spinner"></span></div>
        </div>
        <!-- Chat -->
        <div class="ac-chat">
          <div class="ac-hd">
            <div class="ac-av" id="ac-av" style="width:36px;height:36px;font-size:13px">?</div>
            <div style="flex:1">
              <div class="ac-hd-title" id="ac-title">Sélectionnez une discussion</div>
              <div style="font-size:12px;color:var(--muted)" id="ac-sub"></div>
            </div>
            <div style="display:flex;gap:7px;align-items:center">
              <button class="abtn ai" id="btn-ai-summary" style="display:none" onclick="toggleAISummary()" title="Résumé IA"><i class="bi bi-stars"></i></button>
              <button class="abtn e"  id="btn-edit-disc"  style="display:none" onclick="editCurrentDisc()" title="Modifier"><i class="bi bi-pencil"></i></button>
              <button class="abtn d"  id="btn-del-disc"   style="display:none" onclick="delCurrentDisc()"  title="Supprimer"><i class="bi bi-trash"></i></button>
            </div>
          </div>
          <!-- Zone résumé IA -->
          <div class="ai-summary-box" id="ai-summary-box">
            <div class="ai-summary-title"><i class="bi bi-stars"></i> Résumé IA — Points clés</div>
            <div class="ai-summary-content" id="ai-summary-content">
              <div class="ai-summary-loading"><span class="loading-spinner" style="width:16px;height:16px;margin:0"></span> Analyse en cours…</div>
            </div>
          </div>
          <div class="ac-msgs" id="ac-msgs">
            <div class="empty-state"><i class="bi bi-chat-square"></i><p>Sélectionnez une conversation</p></div>
          </div>
          <div class="ac-input-zone">
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
    </div>

  </div><!-- /content -->
</div><!-- /main -->

<!-- TOAST -->
<div id="toast-container"></div>

<!-- IMAGE MODAL plein écran -->
<div class="img-modal-overlay" id="img-modal" onclick="closeImgModal()">
  <img id="img-modal-src" src="" alt="Image">
</div>

<!-- MODAL Discussion -->
<div class="overlay" id="modal-disc">
  <div class="mbox">
    <div class="mh">
      <h5><i class="bi bi-chat-dots"></i> <span id="md-title">Nouvelle Discussion</span></h5>
      <button class="mc" onclick="closeModal('modal-disc')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb">
      <input type="hidden" id="md-id">
      <div class="info-box"><i class="bi bi-shop"></i> Garage : <strong id="md-garage-info">—</strong></div>
      <div class="fg">
        <label class="flabel">Client *</label>
        <select class="finput" id="md-user"><option value="">— Sélectionner —</option></select>
        <span class="field-error" id="err-user"></span>
      </div>
      <div class="fg">
        <label class="flabel">Objet *</label>
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

<!-- MODAL Message (modifier) -->
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

<!-- MODAL Contacter un client -->
<div class="overlay" id="modal-contact-client">
  <div class="mbox">
    <div class="mh">
      <h5><i class="bi bi-person-plus"></i> Contacter un client</h5>
      <button class="mc" onclick="closeModal('modal-contact-client')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb">
      <div class="info-box"><i class="bi bi-shop"></i> Message envoyé par : <strong id="cc-garage-info">—</strong></div>
      <div class="fg">
        <label class="flabel">Client à contacter *</label>
        <select class="finput" id="cc-user"><option value="">— Sélectionner —</option></select>
        <span class="field-error" id="cc-err-user"></span>
      </div>
      <div class="fg">
        <label class="flabel">Objet *</label>
        <input type="text" class="finput" id="cc-objet" placeholder="Ex: Devis réparation…">
        <span class="field-error" id="cc-err-objet"></span>
      </div>
      <div class="fg">
        <label class="flabel">Premier message *</label>
        <textarea class="finput" id="cc-contenu" placeholder="Bonjour, nous souhaitons…" style="min-height:90px"></textarea>
        <span class="field-error" id="cc-err-contenu"></span>
      </div>
    </div>
    <div class="mf">
      <button class="btn-sec" onclick="closeModal('modal-contact-client')">Annuler</button>
      <button class="btn-pri" onclick="sendContactClient()"><i class="bi bi-send"></i> Envoyer</button>
    </div>
  </div>
</div>

<!-- MODAL Voir discussion -->
<div class="overlay" id="modal-view-disc">
  <div class="mbox wide">
    <div class="mh">
      <h5><i class="bi bi-eye"></i> Détail de la Discussion</h5>
      <button class="mc" onclick="closeModal('modal-view-disc')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb" id="view-disc-body"></div>
    <div class="mf">
      <button class="btn-sec" onclick="closeModal('modal-view-disc')">Fermer</button>
      <button class="btn-ai" onclick="openAISummaryModal()"><i class="bi bi-stars"></i> Résumé IA</button>
    </div>
  </div>
</div>

<!-- MODAL Résumé IA -->
<div class="overlay" id="modal-ai">
  <div class="mbox wide">
    <div class="mh">
      <h5><i class="bi bi-stars" style="color:#673ab7"></i> Résumé IA — Points clés</h5>
      <button class="mc" onclick="closeModal('modal-ai')"><i class="bi bi-x"></i></button>
    </div>
    <div class="mb" id="modal-ai-body">
      <div class="ai-summary-loading" style="justify-content:center;padding:20px">
        <span class="loading-spinner" style="width:20px;height:20px;margin:0"></span> Analyse en cours…
      </div>
    </div>
    <div class="mf"><button class="btn-sec" onclick="closeModal('modal-ai')">Fermer</button></div>
  </div>
</div>

<!-- MODAL Confirmation -->
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

<script>
'use strict';

/* ════════════════════════════════════════════════════════════
   CONFIG
════════════════════════════════════════════════════════════ */
const API_URL    = '../../controller/MessageController.php';
const UPLOAD_URL = '../../controller/upload.php';
// ⚠ Remplacez par votre clé Gemini
const GEMINI_KEY = 'CLE_API';
const GEMINI_URL = `https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=${GEMINI_KEY}`;

/* ════════════════════════════════════════════════════════════
   API HELPERS
════════════════════════════════════════════════════════════ */
async function apiGet(action, params = {}) {
    try {
        const url = new URL(API_URL, location.href);
        url.searchParams.set('action', action);
        Object.entries(params).forEach(([k, v]) => url.searchParams.set(k, v));
        const res = await fetch(url.toString());
        return await res.json();
    } catch (e) { return { success: false, message: 'Erreur réseau' }; }
}

async function apiPost(action, data = {}) {
    try {
        const body = new FormData();
        body.append('action', action);
        Object.entries(data).forEach(([k, v]) => body.append(k, v));
        const res = await fetch(API_URL, { method: 'POST', body });
        return await res.json();
    } catch (e) { return { success: false, message: 'Erreur réseau' }; }
}

async function uploadFile(fileOrBlob, type = 'image') {
    try {
        const form = new FormData();
        form.append('type', type);
        if (fileOrBlob instanceof Blob && !(fileOrBlob instanceof File)) {
            const ext = fileOrBlob.type.includes('ogg') ? 'ogg'
                      : fileOrBlob.type.includes('mp4') ? 'mp4' : 'webm';
            form.append('file', fileOrBlob, `vocal_${Date.now()}.${ext}`);
        } else {
            form.append('file', fileOrBlob);
        }
        const res  = await fetch(UPLOAD_URL, { method: 'POST', body: form });
        const data = await res.json();
        if (data.success) return data.path;
        toast('Upload échoué : ' + (data.message || 'erreur'), 'error');
        return null;
    } catch (e) { toast('Erreur réseau upload', 'error'); return null; }
}

/* ════════════════════════════════════════════════════════════
   VALIDATION (inline — évite la dépendance à validation.js)
════════════════════════════════════════════════════════════ */
const RULES = {
    _err(f, el, msg) { f.classList.add('is-invalid'); f.classList.remove('is-valid'); if (el) { el.textContent = msg; el.style.display = 'block'; } return false; },
    _ok(f, el)       { f.classList.remove('is-invalid'); f.classList.add('is-valid'); if (el) el.style.display = 'none'; return true; },
    objet(f, el)  { const v = (f.value||'').trim(); if (!v) return this._err(f,el,'Objet obligatoire.'); if (v.length<3) return this._err(f,el,'Min 3 caractères.'); return this._ok(f,el); },
    user(f, el)   { if (!f.value) return this._err(f,el,'Sélectionnez un client.'); return this._ok(f,el); },
    contenu(f, el){ const v = (f.value||'').trim(); if (!v) return this._err(f,el,'Message vide.'); if (v.length<2) return this._err(f,el,'Min 2 caractères.'); return this._ok(f,el); },
};

function clearAllErrors(container) {
    if (!container) return;
    container.querySelectorAll('.is-invalid,.is-valid').forEach(el => el.classList.remove('is-invalid','is-valid'));
    container.querySelectorAll('.field-error').forEach(el => { el.style.display = 'none'; el.textContent = ''; });
}

function validateMessageForm(field) { return RULES.contenu(field, null); }

/* ════════════════════════════════════════════════════════════
   TOAST
════════════════════════════════════════════════════════════ */
function toast(msg, type = 'info') {
    const icons = { success:'bi-check-circle-fill', error:'bi-x-circle-fill', info:'bi-info-circle-fill' };
    const t = document.createElement('div');
    t.className = `toast-item toast-${type === 'error' ? 'error' : type === 'success' ? 'success' : 'info'}`;
    t.innerHTML = `<i class="bi ${icons[type]||icons.info} toast-icon"></i><span>${msg}</span>`;
    document.getElementById('toast-container').appendChild(t);
    setTimeout(() => { t.classList.add('toast-hide'); setTimeout(() => t.remove(), 400); }, 3200);
}

/* ════════════════════════════════════════════════════════════
   ÉTAT GLOBAL
════════════════════════════════════════════════════════════ */
let allDiscs     = [];
let allMsgs      = [];
let allUsers     = [];
let allGarages   = [];
let currentDid   = null;
let activeGarage = null;
let aiSummaryVisible = false;
let _pollingInterval = null;
const sessionGarageId   = <?= json_encode($sessionGarageId) ?>;
const sessionGarageName = <?= json_encode($sessionGarageName) ?>;
const sessionRole       = <?= json_encode($sessionRole) ?>;
// Map msgId → URL audio (pour ne pas mettre l'URL dans l'HTML)
const voiceMap = new Map();

/* ════════════════════════════════════════════════════════════
   INIT
════════════════════════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', async () => {
    await loadGarages();
    if (!sessionGarageId) {
        await loadUsers();
    }

    // Validation live modals
    const bindings = [
        ['md-user',    'change', () => RULES.user(   document.getElementById('md-user'),    document.getElementById('err-user'))],
        ['md-objet',   'input',  () => RULES.objet(  document.getElementById('md-objet'),   document.getElementById('err-objet'))],
        ['md-objet',   'blur',   () => RULES.objet(  document.getElementById('md-objet'),   document.getElementById('err-objet'))],
        ['mm-contenu', 'input',  () => RULES.contenu(document.getElementById('mm-contenu'), document.getElementById('mm-err'))],
        ['mm-contenu', 'blur',   () => RULES.contenu(document.getElementById('mm-contenu'), document.getElementById('mm-err'))],
        ['cc-user',    'change', () => RULES.user(   document.getElementById('cc-user'),    document.getElementById('cc-err-user'))],
        ['cc-objet',   'input',  () => RULES.objet(  document.getElementById('cc-objet'),   document.getElementById('cc-err-objet'))],
        ['cc-objet',   'blur',   () => RULES.objet(  document.getElementById('cc-objet'),   document.getElementById('cc-err-objet'))],
        ['cc-contenu', 'input',  () => RULES.contenu(document.getElementById('cc-contenu'), document.getElementById('cc-err-contenu'))],
        ['cc-contenu', 'blur',   () => RULES.contenu(document.getElementById('cc-contenu'), document.getElementById('cc-err-contenu'))],
    ];
    bindings.forEach(([id, ev, fn]) => document.getElementById(id)?.addEventListener(ev, fn));

    document.querySelectorAll('.overlay').forEach(o =>
        o.addEventListener('click', e => { if (e.target === o) o.classList.remove('show'); })
    );
});

/* ════════════════════════════════════════════════════════════
   GARAGE SESSION
════════════════════════════════════════════════════════════ */
async function loadGarages() {
    const r = await apiGet('get_garages');
    if (!r.success) return;
    allGarages = r.data;
    const sel = document.getElementById('garage-session-sel');
    sel.innerHTML = '<option value="">— Choisir un garage —</option>';
    allGarages.forEach(g => sel.insertAdjacentHTML('beforeend',
        `<option value="${g.id}">${esc(g.nom_garages)}</option>`));

    if (sessionGarageId) {
        const current = allGarages.find(g => String(g.id) === String(sessionGarageId));
        if (current) {
            sel.value = String(sessionGarageId);
            await switchGarage(sessionGarageId);
        }
    } else if (sessionRole === 'admin') {
        // Pour l'admin, afficher toutes les discussions sans sélectionner un garage
        document.getElementById('garage-identity').style.display = 'none';
        document.getElementById('topbar-garage-tag').style.display = 'none';
        document.getElementById('btn-contact-client').style.display = '';
        document.getElementById('no-garage-banner').style.display = 'none';
        document.getElementById('main-content').style.display = '';
        document.getElementById('topbar-garage-tag').innerHTML = '<i class="bi bi-shop"></i> <span id="topbar-garage-name">Toutes les discussions</span>';
        await loadAll();
    }
}

async function switchGarage(garageId) {
  const displayName = sessionGarageName || activeGarage.nom_garages;
    stopPolling();
    if (!garageId) {
        activeGarage = null;
        document.getElementById('no-garage-banner').style.display = '';
        document.getElementById('main-content').style.display      = 'none';
        document.getElementById('garage-identity').style.display   = 'none';
        document.getElementById('topbar-garage-tag').style.display = 'none';
        document.getElementById('btn-contact-client').style.display = 'none';
        return;
    }
    activeGarage = allGarages.find(g => g.id == garageId);
    if (!activeGarage) return;
    document.getElementById('garage-identity').style.display      = '';
    document.getElementById('garage-identity-name').textContent = displayName;
    document.getElementById('topbar-garage-tag').style.display    = '';
    document.getElementById('topbar-garage-name').textContent   = sessionRole === 'admin' ? 'Admin' : (sessionGarageName || activeGarage.nom_garages);
    document.getElementById('btn-contact-client').style.display   = '';
    document.getElementById('no-garage-banner').style.display     = 'none';
    document.getElementById('main-content').style.display         = '';
    await loadAll();
    toast(`Connecté en tant que ${activeGarage.nom_garages}`, 'success');
}

/* ════════════════════════════════════════════════════════════
   CHARGEMENT DONNÉES
════════════════════════════════════════════════════════════ */
async function loadAll() {
    if (!activeGarage && sessionRole !== 'admin') return;
    await loadUsers();
    await Promise.all([loadDiscs(), loadGarageStats()]);
   
}

async function loadUsers() {
    const r = await apiGet('get_users');
    if (!r.success) return;
    allUsers = r.data;
    populateSel('md-user', allUsers, 'nom');
    populateSel('cc-user', allUsers, 'nom');
}

async function loadDiscs() {
    if (!activeGarage && sessionRole !== 'admin') return;
    const r = await apiGet('get_discussions');
    if (!r.success) return;
    allDiscs = sessionRole === 'admin' ? r.data : r.data.filter(d => d.garage_id == activeGarage.id);
    renderDiscTable(allDiscs);
    renderChatList(allDiscs);
    loadGarageStats();
    const fd = document.getElementById('filter-disc-msg');
    fd.innerHTML = '<option value="">— Toutes les discussions —</option>';
    allDiscs.forEach(d => fd.insertAdjacentHTML('beforeend',
        `<option value="${d.id}">#${d.id} — ${esc(d.objet)}</option>`));
    document.getElementById('sb-badge').textContent = allDiscs.length;
}

async function loadAllMsgs() {
    const r = await apiGet('get_all_messages');
    if (!r.success) return;
    const discIds = new Set(allDiscs.map(d => d.id));
    allMsgs = r.data.filter(m => discIds.has(parseInt(m.discussion_id)));
    renderMsgTable(allMsgs);
}

function loadGarageStats() {
    const today   = new Date().toLocaleDateString('fr-FR');
    const todayN  = allDiscs.filter(d => { const dt = new Date(d.created_at); return !isNaN(dt) && dt.toLocaleDateString('fr-FR') === today; }).length;
    const clients = new Set(allDiscs.map(d => d.user_id)).size;
    const msgs    = allDiscs.reduce((a, d) => a + (parseInt(d.nb_messages) || 0), 0);
    document.getElementById('s-disc').textContent    = allDiscs.length;
    document.getElementById('s-msg').textContent     = msgs;
    document.getElementById('s-clients').textContent = clients;
    document.getElementById('s-today').textContent   = todayN;
}

/* ════════════════════════════════════════════════════════════
   POLLING (rafraîchissement auto du chat)
════════════════════════════════════════════════════════════ */
function startPolling(discId) {
    stopPolling();
    _pollingInterval = setInterval(async () => {
        if (currentDid === discId) await loadChatMsgs(discId, true);
    }, 5000);
}
function stopPolling() {
    clearInterval(_pollingInterval);
    _pollingInterval = null;
}

/* ════════════════════════════════════════════════════════════
   RENDER TABLES
════════════════════════════════════════════════════════════ */
function renderDiscTable(list) {
    const tb = document.getElementById('tbody-disc');
    if (!list.length) {
        tb.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="bi bi-chat-square-dots"></i><p>Aucune discussion</p></div></td></tr>`;
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
        <button class="abtn v"  title="Voir"       onclick="viewDisc(${d.id})"><i class="bi bi-eye"></i></button>
        <button class="abtn ai" title="Résumé IA"  onclick="openAISummaryModal(${d.id})"><i class="bi bi-stars"></i></button>
        <button class="abtn e"  title="Modifier"   onclick="openDiscModal(${d.id})"><i class="bi bi-pencil"></i></button>
        <button class="abtn d"  title="Supprimer"  onclick="confDel('discussion',${d.id})"><i class="bi bi-trash"></i></button>
        <button class="abtn c"  title="Chat"       onclick="openChat(${d.id})"><i class="bi bi-chat"></i></button>
      </div></td>
    </tr>`).join('');
}

function renderMsgTable(list) {
    const tb = document.getElementById('tbody-msg');
    if (!list.length) {
        tb.innerHTML = `<tr><td colspan="7"><div class="empty-state"><i class="bi bi-envelope"></i><p>Aucun message</p></div></td></tr>`;
        return;
    }
    tb.innerHTML = list.map(m => {
        const type = (m.type || 'text').toLowerCase();
        const typeLabel = type === 'image' ? `<span class="type-badge type-image"><i class="bi bi-image"></i> Image</span>`
                        : type === 'audio' ? `<span class="type-badge type-audio"><i class="bi bi-mic"></i> Audio</span>`
                        : `<span class="type-badge type-text"><i class="bi bi-chat-text"></i> Texte</span>`;
        const preview = type === 'image' ? `<i class="bi bi-image" style="color:var(--info)"></i> [image]`
                      : type === 'audio' ? `<i class="bi bi-mic"   style="color:#c59b00"></i> [audio]`
                      : `<span class="msg-prev">${esc(m.contenu)}</span>`;
        return `
        <tr>
          <td><strong>#${m.id}</strong></td>
          <td><span class="${m.expediteur_type==='garage'?'tag-garage':'tag-user'}">
            <span class="av">${(m.nom_expediteur||'?')[0].toUpperCase()}</span>
            ${esc(m.nom_expediteur||'—')}
          </span></td>
          <td><span class="tag-subj" style="font-size:11px">${esc(m.sujet_discussion||'—')}</span></td>
          <td>${preview}</td>
          <td>${typeLabel}</td>
          <td class="date-txt">${fmt(m.date_envoi)}</td>
          <td><div class="acts">
            ${type === 'text' ? `<button class="abtn e" title="Modifier" onclick="openMsgModal(${m.id})"><i class="bi bi-pencil"></i></button>` : ''}
            <button class="abtn d" title="Supprimer" onclick="confDel('message',${m.id})"><i class="bi bi-trash"></i></button>
          </div></td>
        </tr>`;
    }).join('');
}

function filterMsgByDisc(did) {
    if (!did) { renderMsgTable(allMsgs); return; }
    renderMsgTable(allMsgs.filter(m => m.discussion_id == did));
}

/* ════════════════════════════════════════════════════════════
   CHAT GARAGE
════════════════════════════════════════════════════════════ */
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
    aiSummaryVisible = false;
    document.getElementById('ai-summary-box').classList.remove('show');
    showSection('chat');
    document.querySelectorAll('.ac-item').forEach(el => el.classList.remove('active'));
    const eli = document.getElementById('aci-' + id);
    if (eli) eli.classList.add('active');
    const disc = allDiscs.find(d => d.id == id);
    if (disc) {
        document.getElementById('ac-av').textContent    = (disc.nom_user||'?')[0].toUpperCase();
        document.getElementById('ac-title').textContent = disc.objet;
        document.getElementById('ac-sub').textContent   = `Client : ${disc.nom_user||'?'}`;
    }
    document.getElementById('btn-edit-disc').style.display   = '';
    document.getElementById('btn-del-disc').style.display    = '';
    document.getElementById('btn-ai-summary').style.display  = '';
    if (activeGarage) {
        document.getElementById('sender-hint').style.display      = '';
        document.getElementById('sender-hint-name').textContent   = activeGarage.nom_garages;
    }
    await loadChatMsgs(id);
    startPolling(id);
}

async function loadChatMsgs(discId, silent = false) {
    const area = document.getElementById('ac-msgs');
    if (!silent) area.innerHTML = '<span class="loading-spinner"></span>';

    const r = await apiGet('get_messages', { discussion_id: discId });
    if (!r.success || !r.data.length) {
        if (!silent) area.innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Aucun message</p></div>';
        return;
    }

    const wasAtBottom = area.scrollHeight - area.scrollTop - area.clientHeight < 60;
    let lastDate = '';
    area.innerHTML = r.data.map(m => {
        const isSent  = m.expediteur_type === 'garage' && m.expediteur_id == activeGarage?.id;
        const d       = new Date(m.date_envoi);
        const dateStr = isNaN(d) ? '' : d.toLocaleDateString('fr-FR');
        let sep = '';
        if (dateStr && dateStr !== lastDate) { lastDate = dateStr; sep = `<div class="date-sep">${dateStr}</div>`; }
        return sep + renderBubble(m, isSent);
    }).join('');

    if (wasAtBottom || !silent) area.scrollTop = area.scrollHeight;
}

function renderBubble(m, isSent) {
    const type = (m.type || 'text').toLowerCase();
    let content = '';

    if (type === 'image') {
        const src = esc(m.contenu);
        content = `<img class="bbl-img" src="${src}" alt="Image" onclick="openImgModal('${src}')">`;
    } else if (type === 'audio') {
        voiceMap.set(m.id, m.contenu);
        const bars = Array.from({length:14}, () => {
            const h = 6 + Math.floor(Math.random() * 12);
            return `<span class="wave-bar" style="height:${h}px"></span>`;
        }).join('');
        content = `
        <div class="voice-player">
          <button class="voice-play-btn" onclick="playVoice(this,${m.id})"><i class="bi bi-play-fill"></i></button>
          <div class="voice-waveform">${bars}</div>
          <span style="font-size:11px;opacity:.7">🎤</span>
        </div>`;
    } else {
        content = esc(m.contenu);
    }

    const canEdit = isSent && type === 'text';

    return `
    <div class="bbl-wrap ${isSent?'sent':'recv'}">
      ${!isSent ? `<div class="bbl-sender">${esc(m.nom_expediteur||'?')}</div>` : ''}
      <div class="bbl-hover">
        <div class="bbl-acts">
          ${canEdit ? `<button class="bba" title="Modifier" onclick="openMsgModal(${m.id})"><i class="bi bi-pencil"></i></button>` : ''}
          <button class="bba" title="Supprimer" onclick="confDel('message',${m.id})"><i class="bi bi-trash"></i></button>
        </div>
        <div class="bbl ${isSent?'sent':'recv'}" id="bbl-${m.id}">
          ${content}
          <div class="bbl-meta">${esc(m.nom_expediteur||'?')} · ${timeStr(m.date_envoi)}</div>
        </div>
      </div>
    </div>`;
}

/* ════════════════════════════════════════════════════════════
   LECTURE AUDIO
════════════════════════════════════════════════════════════ */
function playVoice(btn, msgId) {
    const src = voiceMap.get(msgId);
    if (!src) { toast('Audio introuvable', 'error'); return; }
    const icon = btn.querySelector('i');
    if (btn._audio && !btn._audio.paused) {
        btn._audio.pause();
        icon.className = 'bi bi-play-fill';
        return;
    }
    const audio = new Audio(src);
    btn._audio  = audio;
    icon.className = 'bi bi-pause-fill';
    const reset = () => { icon.className = 'bi bi-play-fill'; btn._audio = null; };
    audio.onended = reset;
    audio.onerror = () => { reset(); toast('Erreur lecture audio', 'error'); };
    audio.play().catch(() => { reset(); toast('Lecture impossible', 'error'); });
}

/* ════════════════════════════════════════════════════════════
   IMAGE MODAL
════════════════════════════════════════════════════════════ */
function openImgModal(src) {
    document.getElementById('img-modal-src').src = src;
    document.getElementById('img-modal').classList.add('show');
}
function closeImgModal() {
    document.getElementById('img-modal').classList.remove('show');
}

/* ════════════════════════════════════════════════════════════
   ENVOI MESSAGE GARAGE (texte uniquement depuis le back)
════════════════════════════════════════════════════════════ */
async function garageSend() {
    if (!currentDid)   { toast('Sélectionnez une conversation', 'error'); return; }
    if (!activeGarage) { toast('Aucun garage sélectionné', 'error'); return; }
    const inputEl = document.getElementById('ac-input');
    if (!validateMessageForm(inputEl)) return;

    const r = await apiPost('create_message', {
        discussion_id:   currentDid,
        expediteur_id:   activeGarage.id,
        expediteur_type: 'garage',
        contenu:         inputEl.value.trim(),
        type:            'text',
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

/* ════════════════════════════════════════════════════════════
   IA — RÉSUMÉ GEMINI
════════════════════════════════════════════════════════════ */
async function getAISummary(discId) {
    // Récupérer les messages de la discussion
    const r = await apiGet('get_messages', { discussion_id: discId });
    if (!r.success || !r.data.length) return null;

    // Ne garder que les messages texte pour le résumé
    const textMsgs = r.data.filter(m => (m.type||'text').toLowerCase() === 'text');
    if (!textMsgs.length) return null;

    const disc = allDiscs.find(d => d.id == discId);
    const conversation = textMsgs.map(m =>
        `[${m.expediteur_type === 'garage' ? 'Garage' : 'Client'} — ${m.nom_expediteur||'?'}] : ${m.contenu}`
    ).join('\n');

    const prompt = `Tu es un assistant spécialisé dans l'automobile et les garages.
Voici une conversation entre un garage et un client concernant : "${disc?.objet || 'sujet inconnu'}".

${conversation}

Résume cette conversation en 3 à 5 points clés/cruciaux, sous forme de liste à puces.
Identifie : la demande principale, les problèmes évoqués, les décisions prises, les actions à suivre.
Réponds UNIQUEMENT en JSON valide : {"points": ["point 1", "point 2", "point 3"]}
Pas d'explication, pas de markdown, juste le JSON.`;

    try {
        const res = await fetch(GEMINI_URL, {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contents: [{ parts: [{ text: prompt }] }],
                generationConfig: { maxOutputTokens: 300, temperature: 0.3 }
            })
        });
        const data  = await res.json();
        const raw   = data.candidates?.[0]?.content?.parts?.[0]?.text || '';
        const clean = raw.replace(/```json|```/g, '').trim();
        const parsed = JSON.parse(clean);
        return parsed.points || null;
    } catch (e) {
        console.error('Gemini error', e);
        return null;
    }
}

// Résumé IA dans la barre du chat (toggle)
async function toggleAISummary() {
    if (!currentDid) return;
    const box = document.getElementById('ai-summary-box');
    aiSummaryVisible = !aiSummaryVisible;
    if (!aiSummaryVisible) { box.classList.remove('show'); return; }

    box.classList.add('show');
    document.getElementById('ai-summary-content').innerHTML =
        `<div class="ai-summary-loading"><span class="loading-spinner" style="width:16px;height:16px;margin:0"></span> Analyse en cours…</div>`;

    const points = await getAISummary(currentDid);
    document.getElementById('ai-summary-content').innerHTML = points
        ? `<ul>${points.map(p => `<li>${esc(p)}</li>`).join('')}</ul>`
        : `<span style="color:var(--muted)">Impossible de générer un résumé (vérifiez votre clé API Gemini ou assurez-vous que la discussion contient des messages texte).</span>`;
}

// Résumé IA dans une modale (depuis tableau discussions ou bouton voir)
let _aiModalDiscId = null;
async function openAISummaryModal(discId = null) {
    _aiModalDiscId = discId || currentDid;
    if (!_aiModalDiscId) { toast('Aucune discussion sélectionnée', 'error'); return; }
    document.getElementById('modal-ai-body').innerHTML =
        `<div class="ai-summary-loading" style="justify-content:center;padding:20px">
          <span class="loading-spinner" style="width:20px;height:20px;margin:0"></span> Analyse en cours…
        </div>`;
    openModal('modal-ai');
    const disc   = allDiscs.find(d => d.id == _aiModalDiscId);
    const points = await getAISummary(_aiModalDiscId);
    document.getElementById('modal-ai-body').innerHTML = `
    <div style="margin-bottom:14px">
      <div class="flabel">Discussion</div>
      <span class="tag-subj">${esc(disc?.objet||'—')}</span>
      <span style="color:var(--muted);font-size:12px;margin-left:8px">Client : ${esc(disc?.nom_user||'—')}</span>
    </div>
    <div style="background:rgba(103,58,183,.05);border:1px solid rgba(103,58,183,.15);border-radius:9px;padding:16px">
      <div style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#673ab7;margin-bottom:10px;display:flex;align-items:center;gap:6px">
        <i class="bi bi-stars"></i> Points clés identifiés par l'IA
      </div>
      ${points
        ? `<ul style="margin:0 0 0 16px;padding:0;line-height:1.8">${points.map(p=>`<li>${esc(p)}</li>`).join('')}</ul>`
        : `<p style="color:var(--muted);font-size:13px">Résumé indisponible — vérifiez la clé API Gemini ou la présence de messages texte.</p>`}
    </div>`;
}

/* ════════════════════════════════════════════════════════════
   MODAL DISCUSSION
════════════════════════════════════════════════════════════ */
async function openDiscModal(id = null) {
    if (!activeGarage) { toast('Sélectionnez un garage d\'abord', 'error'); return; }
    clearAllErrors(document.getElementById('modal-disc'));
    document.getElementById('md-id').value    = '';
    document.getElementById('md-user').value  = '';
    document.getElementById('md-objet').value = '';
    document.getElementById('md-garage-info').textContent = activeGarage.nom_garages;
    document.getElementById('md-title').textContent = id ? 'Modifier' : 'Nouvelle Discussion';
    document.getElementById('md-btn').textContent   = id ? 'Enregistrer' : 'Créer';
    populateSel('md-user', allUsers, 'nom');
    if (id) {
        const r = await apiGet('get_discussion', { id });
        if (r.success) {
            document.getElementById('md-id').value    = r.data.id;
            document.getElementById('md-objet').value = r.data.objet;
            await new Promise(res => setTimeout(res, 10));
            document.getElementById('md-user').value  = r.data.user_id;
        }
    }
    openModal('modal-disc');
}

async function saveDisc() {
    const uf = document.getElementById('md-user');
    const of = document.getElementById('md-objet');
    if (!RULES.user(uf, document.getElementById('err-user'))) return;
    if (!RULES.objet(of, document.getElementById('err-objet'))) return;
    const id   = document.getElementById('md-id').value;
    const data = { user_id: uf.value, garage_id: activeGarage.id, objet: of.value.trim() };
    if (id) data.id = id;
    const r = await apiPost(id ? 'update_discussion' : 'create_discussion', data);
    if (r.success) { toast(r.message, 'success'); closeModal('modal-disc'); await loadDiscs(); }
    else toast(r.message || 'Erreur', 'error');
}

function editCurrentDisc() { if (currentDid) openDiscModal(currentDid); }

/* ════════════════════════════════════════════════════════════
   MODAL MESSAGE (modifier — texte uniquement)
════════════════════════════════════════════════════════════ */
async function openMsgModal(id) {
    clearAllErrors(document.getElementById('modal-msg'));
    document.getElementById('mm-id').value      = '';
    document.getElementById('mm-contenu').value = '';
    const r = await apiGet('get_message', { id });
    if (r.success) {
        const type = (r.data.type||'text').toLowerCase();
        if (type !== 'text') { toast('Seuls les messages texte peuvent être modifiés', 'info'); return; }
        document.getElementById('mm-id').value      = r.data.id;
        document.getElementById('mm-contenu').value = r.data.contenu;
    }
    openModal('modal-msg');
}

async function saveMsg() {
    const cf = document.getElementById('mm-contenu');
    if (!RULES.contenu(cf, document.getElementById('mm-err'))) return;
    const id = document.getElementById('mm-id').value;
    if (!id) return;
    const r = await apiPost('update_message', { id, contenu: cf.value.trim() });
    if (r.success) {
        toast(r.message, 'success');
        closeModal('modal-msg');
        await loadDiscs();
        await loadAllMsgs();
        if (currentDid) loadChatMsgs(currentDid);
    } else toast(r.message || 'Erreur', 'error');
}

/* ════════════════════════════════════════════════════════════
   MODAL CONTACTER UN CLIENT
════════════════════════════════════════════════════════════ */
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
    const uf = document.getElementById('cc-user');
    const of = document.getElementById('cc-objet');
    const cf = document.getElementById('cc-contenu');
    if (!RULES.user(uf, document.getElementById('cc-err-user'))) return;
    if (!RULES.objet(of, document.getElementById('cc-err-objet'))) return;
    if (!RULES.contenu(cf, document.getElementById('cc-err-contenu'))) return;

    const rDisc = await apiPost('create_discussion', {
        user_id: uf.value, garage_id: activeGarage.id, objet: of.value.trim()
    });
    if (!rDisc.success) { toast(rDisc.message || 'Erreur création discussion', 'error'); return; }

    const rMsg = await apiPost('create_message', {
        discussion_id:   rDisc.data.id,
        expediteur_id:   activeGarage.id,
        expediteur_type: 'garage',
        contenu:         cf.value.trim(),
        type:            'text',
    });
    if (!rMsg.success) { toast(rMsg.message || 'Erreur envoi message', 'error'); return; }

    toast(`Message envoyé à ${esc(allUsers.find(u => u.id == uf.value)?.nom || 'ce client')}`, 'success');
    closeModal('modal-contact-client');
    await loadDiscs();
    await openChat(rDisc.data.id);
    showSection('chat');
}

/* ════════════════════════════════════════════════════════════
   VUE DISCUSSION (détail modal)
════════════════════════════════════════════════════════════ */
async function viewDisc(id) {
    const rd = await apiGet('get_discussion', { id });
    const rm = await apiGet('get_messages',   { discussion_id: id });
    if (!rd.success) return;
    const d = rd.data, msgs = rm.success ? rm.data : [];
    _aiModalDiscId = id;

    document.getElementById('view-disc-body').innerHTML = `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px">
      <div><div class="flabel">Client</div><span class="tag-user"><span class="av">${(d.nom_user||'?')[0].toUpperCase()}</span>${esc(d.nom_user||'—')}</span></div>
      <div><div class="flabel">Garage</div><span class="tag-garage"><span class="av">${(d.nom_garage||'?')[0].toUpperCase()}</span>${esc(d.nom_garage||'—')}</span></div>
      <div><div class="flabel">Objet</div><span class="tag-subj">${esc(d.objet)}</span></div>
      <div><div class="flabel">Créé le</div><span class="date-txt">${fmt(d.created_at)}</span></div>
    </div>
    <div class="flabel" style="margin-bottom:10px">Messages (${msgs.length})</div>
    <div style="max-height:260px;overflow-y:auto;display:flex;flex-direction:column;gap:7px;background:var(--bg);border-radius:9px;padding:12px">
      ${msgs.length ? msgs.map(m => {
          const type = (m.type||'text').toLowerCase();
          const contentHtml = type === 'image'
              ? `<img src="${esc(m.contenu)}" style="max-width:120px;border-radius:6px;cursor:pointer" onclick="openImgModal('${esc(m.contenu)}')">`
              : type === 'audio'
              ? `<i class="bi bi-mic" style="color:#c59b00"></i> <em style="font-size:12px;color:var(--muted)">Message vocal</em>`
              : `<div style="font-size:13px;margin-top:3px">${esc(m.contenu)}</div>`;
          return `
          <div style="display:flex;gap:9px;align-items:flex-start">
            <div class="ac-av" style="width:28px;height:28px;font-size:11px;flex-shrink:0;background:${m.expediteur_type==='garage'?'linear-gradient(135deg,var(--info),#0d5466)':'linear-gradient(135deg,var(--success),#1a4a2e)'}">${(m.nom_expediteur||'?')[0].toUpperCase()}</div>
            <div>
              <div style="font-size:12px;font-weight:700">${esc(m.nom_expediteur||'?')}
                <span style="color:var(--muted);font-weight:400;font-size:11px"> · ${fmt(m.date_envoi)}</span>
                <span style="background:${m.expediteur_type==='garage'?'rgba(23,162,184,.1)':'rgba(40,167,69,.1)'};color:${m.expediteur_type==='garage'?'var(--info)':'var(--success)'};border-radius:5px;padding:1px 6px;font-size:10px;margin-left:4px">${m.expediteur_type==='garage'?'Garage':'Client'}</span>
              </div>
              ${contentHtml}
            </div>
          </div>`;
      }).join('') : '<div class="empty-state" style="padding:20px"><i class="bi bi-chat-square" style="font-size:26px"></i><p>Aucun message</p></div>'}
    </div>`;
    openModal('modal-view-disc');
}

/* ════════════════════════════════════════════════════════════
   SUPPRESSION
════════════════════════════════════════════════════════════ */
function confDel(type, id) {
    const texts = {
        discussion: `Supprimer la discussion #${id} et tous ses messages ?`,
        message:    `Supprimer ce message #${id} ?`,
    };
    document.getElementById('conf-text').textContent = texts[type];
    document.getElementById('conf-btn').onclick = async () => {
        const r = await apiPost(type === 'discussion' ? 'delete_discussion' : 'delete_message', { id });
        if (r.success) {
            toast(r.message, 'success');
            closeModal('modal-confirm');
            if (type === 'discussion' && currentDid == id) {
                currentDid = null;
                stopPolling();
                document.getElementById('ac-msgs').innerHTML = '<div class="empty-state"><i class="bi bi-chat-square"></i><p>Sélectionnez une conversation</p></div>';
                document.getElementById('btn-edit-disc').style.display   = 'none';
                document.getElementById('btn-del-disc').style.display    = 'none';
                document.getElementById('btn-ai-summary').style.display  = 'none';
                document.getElementById('sender-hint').style.display     = 'none';
                document.getElementById('ai-summary-box').classList.remove('show');
            }
            await loadDiscs();
            await loadAllMsgs();
        } else toast(r.message || 'Erreur', 'error');
    };
    openModal('modal-confirm');
}

/* ════════════════════════════════════════════════════════════
   RECHERCHE
════════════════════════════════════════════════════════════ */
let _searchTimer = null;
function handleSearch(q) {
    clearTimeout(_searchTimer);
    _searchTimer = setTimeout(() => {
        const ql = q.toLowerCase().trim();
        if (!ql) { renderDiscTable(allDiscs); renderMsgTable(allMsgs); return; }
        const sec = document.getElementById('sec-discussions').style.display !== 'none' ? 'disc' : 'msg';
        if (sec === 'disc') {
            renderDiscTable(allDiscs.filter(d =>
                (d.objet||'').toLowerCase().includes(ql) || (d.nom_user||'').toLowerCase().includes(ql)
            ));
        } else {
            renderMsgTable(allMsgs.filter(m =>
                (m.contenu||'').toLowerCase().includes(ql) || (m.nom_expediteur||'').toLowerCase().includes(ql)
            ));
        }
    }, 350);
}

/* ════════════════════════════════════════════════════════════
   NAVIGATION SECTIONS
════════════════════════════════════════════════════════════ */
function showSection(s) {
    ['discussions','messages','chat'].forEach(n => {
        document.getElementById('sec-'  + n).style.display = n === s ? '' : 'none';
        const tabId = { discussions:'disc', messages:'msg', chat:'chat' }[n];
        document.getElementById('stab-' + tabId).classList.toggle('active', n === s);
    });
    if (s === 'chat')     renderChatList(allDiscs);
    if (s === 'messages') loadAllMsgs();
}

/* ════════════════════════════════════════════════════════════
   HELPERS
════════════════════════════════════════════════════════════ */
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

function esc(s) {
    if (!s) return '';
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function fmt(dt) {
    if (!dt) return '—';
    const d = new Date(dt);
    if (isNaN(d)) return dt;
    return d.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric'})
        + ' ' + d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
}
function timeStr(dt) {
    if (!dt) return '';
    const d = new Date(dt);
    return isNaN(d) ? '' : d.toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
}
</script>
</body>
</html>