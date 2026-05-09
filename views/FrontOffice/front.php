<?php
require_once __DIR__ . '/../../models/db.php';
require_once __DIR__ . '/../../models/MonModule.php';
require_once __DIR__ . '/../../Controller/MonModuleController.php';

$garageServices = [];
$serviceSearchTerm = '';

try {
    
    $model = new MonModule($pdo);
    $controller = new MonModuleController($model);
    
    // Handle service search
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search_service'])) {
        $serviceSearchTerm = trim($_POST['search_service_input'] ?? '');
        if ($serviceSearchTerm !== '') {
            $garageServices = $controller->searchGaragesByServiceName($serviceSearchTerm);
        } else {
            $garageServices = $controller->getGarageServices();
        }
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_search'])) {
        $serviceSearchTerm = '';
        $garageServices = $controller->getGarageServices();
    } else {
        $garageServices = $controller->getGarageServices();
    }
} catch (PDOException $e) {
    $garageServices = [];
}
$maxServices = 0;
foreach ($garageServices as $g) {
    $count = count($g['services']);
    if ($count > $maxServices) {
        $maxServices = $count;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="Gestion de Garage - Entretien et Lavage de Véhicules" />
    <title>Garage Expert - Entretien & Lavage</title>
    <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
    <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
    <link href="assets/css/styles.css" rel="stylesheet" />
    <style>
        body {
            background: #f8fafc;
        }
        .masthead {
            min-height: 90vh;
            background: linear-gradient(180deg, rgba(10, 25, 47, 0.72) 0%, rgba(8, 18, 35, 0.72) 100%), url("assets/img/back1.jpg") no-repeat center/cover;
            color: #fff;
            display: flex;
            align-items: center;
            position: relative;
        }
        .masthead .container {
            position: relative;
            z-index: 1;
        }
        .masthead::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.28);
            z-index: 0;
        }
        .masthead-heading,
        .masthead-subheading,
        .masthead .btn {
            color: #fff;
        }
        #services {
            padding: 60px 0 80px;
        }
        .service-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            padding: 32px 24px;
            box-shadow: 0 20px 60px rgba(15, 23, 42, 0.14);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }
        .service-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 26px 80px rgba(15, 23, 42, 0.2);
        }
        .service-card h4 {
            color: #111827;
        }
        .service-card p {
            color: #4b5563;
        }
        .portfolio-section {
            background: url("assets/img/background.webp") no-repeat center/cover;
            background-size: cover;
            position: relative;
            padding: 80px 0 60px;
            color: #fff;
        }
        .portfolio-section::before {
            content: "";
            position: absolute;
            inset: 0;
            background: rgba(14, 23, 34, 0.72);
            z-index: 0;
        }
        .portfolio-section .container {
            position: relative;
            z-index: 1;
        }
        .portfolio-hero-panel {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 28px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.24);
            min-height: 280px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            margin-bottom: 32px;
        }
        .portfolio-hero-panel h3,
        .portfolio-hero-panel p {
            color: #f8fafc;
        }
        .portfolio-hero-panel p {
            max-width: 700px;
            margin: 0 auto;
            color: rgba(248, 250, 252, 0.82);
        }
        .portfolio-section .card {
            background: rgba(8, 18, 35, 0.62) !important;
            border: 1px solid rgba(255, 255, 255, 0.18) !important;
            box-shadow: 0 30px 80px rgba(8, 18, 35, 0.35) !important;
            backdrop-filter: blur(18px);
            color: #f8fafc;
        }
        .portfolio-section .table {
            background: transparent;
            color: #f8fafc;
        }
        .portfolio-section .table thead th {
            background: rgba(15, 23, 42, 0.76);
            color: #f8fafc;
            border-color: rgba(255, 255, 255, 0.12);
        }
        .portfolio-section .table tbody tr {
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .portfolio-section .table tbody td {
            color: #f8fafc;
            border-color: rgba(255, 255, 255, 0.08);
        }
        .portfolio-section .table-responsive {
            overflow: visible;
        }
        .portfolio-section .pagination-controls {
            padding-top: 8px;
        }
        .portfolio-section .section-heading,
        .portfolio-section .section-subheading,
        .portfolio-section .form-label {
            color: #f8fafc;
        }
        .portfolio-section .btn-secondary {
            background: rgba(255, 255, 255, 0.14);
            border-color: rgba(255, 255, 255, 0.2);
            color: #f8fafc;
        }
        .portfolio-section .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.22);
        }
        .portfolio-section .form-control {
            background: rgba(255,255,255,0.12);
            color: #f8fafc;
            border: 1px solid rgba(255,255,255,0.16);
        }
        .portfolio-section .form-control::placeholder {
            color: rgba(248, 250, 252, 0.68);
        }
    </style>
</head>
<body id="page-top">
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav">
        <div class="container">
            <a class="navbar-brand" href="#page-top">EntreAuTous</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
                Menu
                <i class="fas fa-bars ms-1"></i>
            </button>
            <div class="collapse navbar-collapse" id="navbarResponsive">
                <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                    <li class="nav-item"><a class="nav-link" href="#services">Services</a></li>
                    <li class="nav-item">
                         <a class="nav-link" href="/integration/user/views/front/home.php"
                          style="background:#f59e0b; color:white !important; border-radius:8px;
                          padding:8px 16px !important; font-weight:700;">
                           🏠 Accueil
                        </a> 
                    </li>
                    <li class="nav-item"><a class="nav-link" href="#portfolio">Véhicules</a></li>
                    <li class="nav-item"><a class="nav-link" href="#about">À propos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contact">Contact</a></li>
                </ul>
            </div>
        </div>
    </nav>
    
    <header class="masthead">
        <div class="container text-center">
            <div class="masthead-subheading">Bienvenue dans votre espace auto !</div>
            <div class="masthead-heading text-uppercase">Entretien, Réparation & Lavage</div>
            <p class="mt-4 mb-4" style="max-width: 680px; margin: 0 auto; color: rgba(255,255,255,0.87); font-size: 1.05rem;">Profitez d'un service professionnel avec une expérience client premium. Découvrez nos offres de réparation, lavage et entretien dans un seul espace.</p>
            <a class="btn btn-primary btn-xl text-uppercase" href="#services">Catalogue Services</a>
        </div>
    </header>

    <section class="page-section" id="services">
        <div class="container">
            <div class="text-center">
                <h2 class="section-heading text-uppercase">Nos Services</h2>
                <h3 class="section-subheading text-muted">Nous prenons soin de votre véhicule de A à Z.</h3>
            </div>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="service-card">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-tools fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Réparation Auto</h4>
                        <p>Diagnostic complet, mécanique générale et remplacement de pièces d'origine pour assurer la longévité de votre moteur.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-car-wash fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Lavage & Esthétique</h4>
                        <p>Nettoyage haute pression, aspiration intérieure et polissage de carrosserie pour un véhicule éclatant comme au premier jour.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="service-card">
                        <span class="fa-stack fa-4x">
                            <i class="fas fa-circle fa-stack-2x text-primary"></i>
                            <i class="fas fa-clipboard-check fa-stack-1x fa-inverse"></i>
                        </span>
                        <h4 class="my-3">Suivi d'Entretien</h4>
                        <p>Planification de vos vidanges et contrôles techniques. Consultez votre historique de maintenance en un clic.</p>
                    </div>
                </div>
            </div>
            <div class="text-center mt-5">
                <button class="btn btn-secondary btn-xl text-uppercase" id="catalogueBtn">CATALOGUE</button>
            </div>
        </div>
    </section>

    <section class="page-section portfolio-section" id="portfolio">
        <div class="container">
            <div class="text-center mb-4">
                <h2 class="section-heading text-uppercase">Liste des garages et services</h2>
                <p class="section-subheading text-muted">Une interface claire avec vos garages et services.</p>
            </div>
            <div class="row" id="garage-list-section" style="display:block; margin-top:0;">
                <div class="col-12">
                    <div class="card p-4 shadow-sm" style="background:#fff; color:#212529; border-radius:16px;">
                        <h3 class="section-heading text-uppercase">Liste des garages et services</h3>
                        <p class="section-subheading text-muted">Chaque garage affiche au moins deux services disponibles.</p>
                        
                        <!-- Search by Service Form -->
                        <form method="post" action="#portfolio" class="mb-4">
                            <div class="row g-3 align-items-end">
                                <div class="col-md-6">
                                    <label for="searchServiceInput" class="form-label" style="color: #212529; font-weight: 600;">Rechercher par Service</label>
                                    <input type="text" class="form-control" id="searchServiceInput" name="search_service_input" placeholder="Ex: Lavage, Réparation..." value="<?php echo htmlspecialchars($serviceSearchTerm, ENT_QUOTES, 'UTF-8'); ?>" />
                                </div>
                                <div class="col-md-6 d-flex gap-2">
                                    <button type="submit" name="search_service" class="btn btn-primary" style="flex: 1;">
                                        <i class="fas fa-search"></i> Rechercher
                                    </button>
                                    <?php if ($serviceSearchTerm !== ''): ?>
                                        <button type="submit" name="reset_search" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i> Réinitialiser
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </form>
                        
                        <div class="table-responsive mt-4">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nom Garage</th>
                                        <th>Adresse</th>
                                        <th>Heure ouverture</th>
                                        <th>Heure fermeture</th>
                                        <th>Services</th>
                                        <th>⭐ Favori</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($garageServices)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center">Aucun garage disponible.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($garageServices as $garage): ?>
                                            <tr>
                                                <td>
    <?php 
        echo htmlspecialchars($garage['nom_garage'], ENT_QUOTES, 'UTF-8'); 
        // Si ce garage a le nombre max de services, on affiche le badge
        if ($maxServices > 0 && count($garage['services']) === $maxServices) {
            echo ' <span title="Garage le plus actif" style="cursor:help;">🏆</span>';
        }
    ?>
</td>
                                                <td><?php echo htmlspecialchars($garage['adresse'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($garage['heure_ouv'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars($garage['heure_fer'], ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td><?php echo htmlspecialchars(implode(' / ', $garage['services']), ENT_QUOTES, 'UTF-8'); ?></td>
                                                <td class="text-center">
                                                    <button 
                                                       class="btn-favori" 
                                                       onclick="toggleFavori(this, '<?php echo htmlspecialchars($garage['nom_garage'], ENT_QUOTES, 'UTF-8'); ?>')"
                                                       style="background:none; border:none; font-size:22px; cursor:pointer;">
                                                      ⭐
                                                   </button>
                                               </td>
                                                     
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="pagination-controls" style="display: flex; align-items: center; justify-content: space-between; margin-top: 16px; gap: 12px;">
                            <button type="button" id="front-prev" class="btn btn-secondary" style="padding: 10px 18px;">Précédent</button>
                            <span id="front-page-info" style="color: #212529; font-weight: 600;">Page 1 / 1</span>
                            <button type="button" id="front-next" class="btn btn-secondary" style="padding: 10px 18px;">Suivant</button>
                        </div>
                        <div class="text-center mt-4">
                            <button class="btn btn-secondary btn-lg text-uppercase" id="backToServicesBtn">Retour aux Services</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <footer class="footer py-4">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-4 text-lg-start">Copyright &copy; Garage Expert 2024</div>
                <div class="col-lg-4 my-3 my-lg-0">
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a class="btn btn-dark btn-social mx-2" href="#!" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
                <div class="col-lg-4 text-lg-end">
                    <a class="link-dark text-decoration-none me-3" href="#!">Politique de confidentialité</a>
                    <a class="link-dark text-decoration-none" href="#!">Conditions d'utilisation</a>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/scripts.js"></script>

    <script>
// Charger les favoris depuis localStorage
function chargerFavoris() {
    const favoris = JSON.parse(localStorage.getItem('favoris_garages') || '[]');
    document.querySelectorAll('.btn-favori').forEach(btn => {
        const nom = btn.getAttribute('onclick').match(/'([^']+)'/)[1];
        if (favoris.includes(nom)) {
            btn.textContent = '⭐';
            btn.style.filter = 'none';
            btn.closest('tr').style.background = 'rgba(245,158,11,0.08)';
        } else {
            btn.textContent = '☆';
            btn.style.filter = 'grayscale(1)';
        }
    });
}

function toggleFavori(btn, nomGarage) {
    let favoris = JSON.parse(localStorage.getItem('favoris_garages') || '[]');
    
    if (favoris.includes(nomGarage)) {
        // Retirer des favoris
        favoris = favoris.filter(f => f !== nomGarage);
        btn.textContent = '☆';
        btn.style.filter = 'grayscale(1)';
        btn.closest('tr').style.background = '';
        afficherToast('Retiré des favoris');
    } else {
        // Ajouter aux favoris
        favoris.push(nomGarage);
        btn.textContent = '⭐';
        btn.style.filter = 'none';
        btn.closest('tr').style.background = 'rgba(245,158,11,0.08)';
        afficherToast('✅ Ajouté aux favoris !');
    }
    
    localStorage.setItem('favoris_garages', JSON.stringify(favoris));
}

function afficherToast(message) {
    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position:fixed; bottom:30px; left:50%; transform:translateX(-50%);
        background:#1e293b; color:white; padding:12px 25px;
        border-radius:10px; font-size:14px; z-index:9999;
        box-shadow:0 4px 15px rgba(0,0,0,0.3);
        animation: fadeIn 0.3s ease;
    `;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2500);
}

document.addEventListener('DOMContentLoaded', chargerFavoris);
</script> 

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const catalogueBtn = document.getElementById('catalogueBtn');
            const servicesSection = document.getElementById('services');
            const portfolioSection = document.getElementById('portfolio');

            catalogueBtn.addEventListener('click', function(event) {
                event.preventDefault();

                servicesSection.style.display = 'none';
                portfolioSection.style.display = 'block';
                catalogueBtn.style.display = 'none';
            });

            const backToServicesBtn = document.getElementById('backToServicesBtn');
            const afficherListeBtn = document.getElementById('afficherListeBtn');
            const garageListSection = document.getElementById('garage-list-section');

            if (backToServicesBtn) {
                backToServicesBtn.addEventListener('click', function(event) {
                    event.preventDefault();

                    servicesSection.style.display = 'block';
                    portfolioSection.style.display = 'none';
                    if (garageListSection) garageListSection.style.display = 'none';
                    catalogueBtn.style.display = 'inline-block';
                });
            }

            if (afficherListeBtn) {
                afficherListeBtn.addEventListener('click', function(event) {
                    event.preventDefault();

                    servicesSection.style.display = 'none';
                    portfolioSection.style.display = 'block';
                    if (garageListSection) garageListSection.style.display = 'block';
                    catalogueBtn.style.display = 'none';
                });
            }

            function setupFrontPagination(tableSelector, prevId, nextId, pageInfoId, rowsPerPage = 5) {
                const table = document.querySelector(tableSelector);
                const prevButton = document.getElementById(prevId);
                const nextButton = document.getElementById(nextId);
                const pageInfo = document.getElementById(pageInfoId);
                if (!table || !prevButton || !nextButton || !pageInfo) return;

                const rows = Array.from(table.querySelectorAll('tbody tr'));
                let currentPage = 1;
                const totalPages = Math.max(1, Math.ceil(rows.length / rowsPerPage));

                const refresh = () => {
                    const start = (currentPage - 1) * rowsPerPage;
                    const end = start + rowsPerPage;
                    rows.forEach((row, index) => {
                        row.style.display = index >= start && index < end ? '' : 'none';
                    });
                    pageInfo.textContent = `Page ${currentPage} / ${totalPages}`;
                    prevButton.disabled = currentPage <= 1;
                    nextButton.disabled = currentPage >= totalPages;
                };

                prevButton.addEventListener('click', function() {
                    if (currentPage > 1) {
                        currentPage -= 1;
                        refresh();
                    }
                });
                nextButton.addEventListener('click', function() {
                    if (currentPage < totalPages) {
                        currentPage += 1;
                        refresh();
                    }
                });

                refresh();
            }

            setupFrontPagination('#portfolio table', 'front-prev', 'front-next', 'front-page-info', 5);
        });
    </script>
    <!-- ========== CHATBOT GEMINI IA ========== -->
<div id="chatbot-container" style="
    position:fixed; bottom:30px; right:30px; z-index:9999;">

    <!-- Bouton flottant -->
    <div id="chat-toggle" onclick="toggleChat()" style="
        width:65px; height:65px; background:linear-gradient(135deg,#f59e0b,#ef4444);
        border-radius:50%; cursor:pointer; display:flex;
        align-items:center; justify-content:center;
        box-shadow:0 4px 20px rgba(245,158,11,0.5); font-size:30px;
        transition:transform 0.2s;"
        onmouseover="this.style.transform='scale(1.1)'"
        onmouseout="this.style.transform='scale(1)'">
        🤖
    </div>

    <!-- Fenêtre chat -->
    <div id="chat-window" style="
        display:none; width:360px;
        background:#0f172a; border-radius:20px;
        box-shadow:0 15px 50px rgba(0,0,0,0.5);
        flex-direction:column; overflow:hidden;
        position:absolute; bottom:80px; right:0;
        border:1px solid #1e293b;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,#f59e0b,#ef4444);
            padding:18px 20px; color:white; display:flex;
            align-items:center; gap:12px;">
            <span style="font-size:28px;">🤖</span>
            <div>
                <strong style="font-size:15px;">Assistant EntreAuTous</strong>
                <p style="margin:0; font-size:11px; opacity:0.9;">
                    Propulsé par Gemini AI ✨
                </p>
            </div>
        </div>

        <!-- Messages -->
        <div id="chat-messages" style="
            height:300px; overflow-y:auto; padding:15px;
            display:flex; flex-direction:column; gap:10px;">
            <div style="background:#1e293b; color:#e2e8f0;
                padding:12px 15px; border-radius:12px;
                font-size:13px; max-width:85%; line-height:1.5;">
                👋 Bonjour ! Je suis votre assistant auto.<br>
                Dites-moi quel service vous cherchez et je trouve <strong>le meilleur prix</strong> pour vous !
            </div>
        </div>

        <!-- Zone de saisie -->
        <div style="padding:12px; border-top:1px solid #1e293b;
            display:flex; gap:8px; background:#0f172a;">
            <input type="text" id="chat-input"
                placeholder="Ex: je cherche un lavage..."
                onkeypress="if(event.key==='Enter') envoyerMessage()"
                style="flex:1; padding:11px 15px; border-radius:10px;
                border:1px solid #334155; background:#1e293b;
                color:white; font-size:13px; outline:none;">
            <button onclick="envoyerMessage()" style="
                background:linear-gradient(135deg,#f59e0b,#ef4444);
                color:white; border:none; padding:11px 16px;
                border-radius:10px; cursor:pointer; font-size:18px;
                transition:opacity 0.2s;"
                onmouseover="this.style.opacity='0.85'"
                onmouseout="this.style.opacity='1'">
                ➤
            </button>
        </div>
    </div>
</div>

<script>
// Chemin vers chatbot.php — adapte si besoin
const CHATBOT_URL = '/integration/user/Controller/chat.php';
function toggleChat() {
    const win = document.getElementById('chat-window');
    if (win.style.display === 'none' || win.style.display === '') {
        win.style.display = 'flex';
        win.style.flexDirection = 'column';
    } else {
        win.style.display = 'none';
    }
}

function envoyerMessage() {
    const input = document.getElementById('chat-input');
    const messages = document.getElementById('chat-messages');
    const texte = input.value.trim();
    if (!texte) return;

    // Message du client (droite)
    messages.innerHTML += `
        <div style="background:linear-gradient(135deg,#f59e0b,#ef4444);
            color:white; padding:10px 14px; border-radius:12px;
            font-size:13px; max-width:80%; align-self:flex-end;
            margin-left:auto;">
            ${texte}
        </div>`;

    input.value = '';
    messages.scrollTop = messages.scrollHeight;

    // Animation "en train d'écrire"
    messages.innerHTML += `
        <div id="typing" style="background:#1e293b; color:#94a3b8;
            padding:10px 14px; border-radius:12px; font-size:13px; max-width:80%;">
            ✍️ En train de répondre...
        </div>`;
    messages.scrollTop = messages.scrollHeight;

    // Appel au chatbot
    fetch(CHATBOT_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: texte })
    })
    .then(r => r.json())
    .then(data => {
        const typing = document.getElementById('typing');
        if (typing) typing.remove();

        const texteIA = data.response || 'Je ne peux pas répondre maintenant.';

        messages.innerHTML += `
            <div style="background:#1e293b; color:#e2e8f0;
                padding:12px 15px; border-radius:12px;
                font-size:13px; max-width:85%; line-height:1.6;">
                🤖 ${texteIA.replace(/\n/g, '<br>')}
            </div>`;
        messages.scrollTop = messages.scrollHeight;
    })
    .catch(() => {
        const typing = document.getElementById('typing');
        if (typing) typing.remove();
        messages.innerHTML += `
            <div style="background:#ef4444; color:white;
                padding:10px 14px; border-radius:12px; font-size:13px;">
                ❌ Erreur de connexion au serveur
            </div>`;
    });
}
</script>
</body>
</html>