<?php
// views/back/dashboard_garagiste.php
session_set_cookie_params([
    'lifetime' => 60 * 60 * 24 * 7,
    'path'     => '/Esprit-PI-2PREPA-2026-EntreAUtous/',
    'secure'   => false,
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// ── Guard : seul un garagiste actif peut voir cette page ──
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'garagiste') {
    header("Location: ../front/login.php");
    exit();
}

require_once __DIR__ . '/../../models/db.php';

$prenom = htmlspecialchars($_SESSION['prenom'] ?? 'Garagiste');
$email  = htmlspecialchars($_SESSION['email']  ?? '');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Garagiste — EntreAutous</title>
  <link rel="stylesheet" href="/Esprit-PI-2PREPA-2026-EntreAUtous/assets/back/css/theme.css">
  <style>
    body { background: #f4f6fb; }
    .sidebar {
      width: 240px; min-height: 100vh; background: #1a1f36;
      display: flex; flex-direction: column; padding: 24px 0;
    }
    .sidebar-brand {
      color: #fff; font-size: 1.15rem; font-weight: 700;
      padding: 0 20px 24px; border-bottom: 1px solid rgba(255,255,255,.1);
      text-decoration: none;
    }
    .sidebar-nav { flex: 1; padding: 16px 0; }
    .sidebar-link {
      display: flex; align-items: center; gap: 10px;
      color: rgba(255,255,255,.65); padding: 10px 20px;
      text-decoration: none; font-size: .88rem; border-radius: 6px;
      transition: background .15s, color .15s;
    }
    .sidebar-link:hover, .sidebar-link.active {
      background: rgba(255,255,255,.1); color: #fff;
    }
    .sidebar-footer {
      padding: 16px 20px; border-top: 1px solid rgba(255,255,255,.1);
    }
    .garage-avatar {
      width: 36px; height: 36px; background: #0d6efd; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-weight: 700; font-size: .9rem;
    }
    .main-content { flex: 1; padding: 32px; }
    .stat-card {
      background: #fff; border-radius: 12px; padding: 20px 24px;
      box-shadow: 0 1px 6px rgba(0,0,0,.08);
    }
    .stat-card .label { font-size: .78rem; color: #6c757d; text-transform: uppercase; letter-spacing: .05em; }
    .stat-card .value { font-size: 1.9rem; font-weight: 700; color: #1a1f36; }
  </style>
</head>
<body class="d-flex">

<!-- ── Sidebar ─────────────────────────────────────────────── -->
<aside class="sidebar">
  <a class="sidebar-brand" href="dashboard_garagiste.php">🔧 EntreAutous</a>
  <nav class="sidebar-nav">
    <a class="sidebar-link active" href="dashboard_garagiste.php">
      🏠 Tableau de bord
    </a>
    <!-- Ajoutez ici les liens vers les modules du garagiste au fur et à mesure -->
    <!-- Exemple : rendez-vous, diagnostics, stock, etc. -->

    <a class="sidebar-link" href="/Esprit-PI-2PREPA-2026-EntreAUtous/views/back/back.php">
      💬 Messagerie
    </a>
  </nav>
  <div class="sidebar-footer d-flex align-items-center gap-2">
    <div class="garage-avatar"><?= strtoupper(substr($_SESSION['prenom'] ?? 'G', 0, 1)) ?></div>
    <div>
      <div style="color:#fff;font-size:.82rem;font-weight:600"><?= $prenom ?></div>
      <div style="color:rgba(255,255,255,.4);font-size:.72rem"><?= $email ?></div>
    </div>
  </div>
</aside>

<!-- ── Contenu principal ────────────────────────────────────── -->
<main class="main-content">

  <!-- Topbar -->
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="mb-0">Bonjour, <?= $prenom ?> 👋</h4>
      <p class="text-muted mb-0" style="font-size:.85rem">Espace garagiste — EntreAutous</p>
    </div>
    <a href="/Esprit-PI-2PREPA-2026-EntreAUtous/Controller/UserController.php?action=logout"
       class="btn btn-outline-danger btn-sm">
      Déconnexion
    </a>
  </div>

  <!-- Cartes de stats rapides (à brancher sur vos vraies données) -->
  <div class="row g-3 mb-4">
    <div class="col-md-3">
      <div class="stat-card">
        <div class="label">Rendez-vous aujourd'hui</div>
        <div class="value">—</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="label">Diagnostics en cours</div>
        <div class="value">—</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="label">Pièces en alerte stock</div>
        <div class="value">—</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="stat-card">
        <div class="label">Messages non lus</div>
        <div class="value">—</div>
      </div>
    </div>
  </div>

  <!-- Zone de travail principale (placeholder) -->
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
      <h6 class="mb-0">📋 Mes tâches du jour</h6>
    </div>
    <div class="card-body text-center text-muted py-5">
      <p>Votre espace de travail garagiste.<br>
         Les modules (RDV, diagnostics, stock…) seront affichés ici.</p>
    </div>
  </div>

</main>

</body>
</html>