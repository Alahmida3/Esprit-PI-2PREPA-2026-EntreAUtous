<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <meta name="description" content="" />
        <meta name="author" content="" />
        <title>Pièces Auto - Vente de Pièces Automobiles</title>
        <!-- Favicon-->
        <link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
        <!-- Font Awesome icons (free version)-->
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <!-- Bootstrap CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <!-- Google fonts-->
        <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700" rel="stylesheet" type="text/css" />
        <link href="https://fonts.googleapis.com/css?family=Roboto+Slab:400,100,300,700" rel="stylesheet" type="text/css" />
        <!-- Core theme CSS (includes Bootstrap)-->
        <link href="css/styles.css" rel="stylesheet" />
        <style>
            body {
                padding-top: 100px;
            }
        </style>
    </head>
    
<body>
<nav class="navbar navbar-expand-lg navbar-dark fixed-top" id="mainNav" style="background: #000000;">
    <div class="container">
        <a class="navbar-brand text-warning" href="/integration/user/index.php">
            <i class="fas fa-cogs"></i> Pièces Auto
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarResponsive" aria-controls="navbarResponsive" aria-expanded="false" aria-label="Toggle navigation">
            Menu
            <i class="fas fa-bars ms-1"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarResponsive">
            <ul class="navbar-nav text-uppercase ms-auto py-4 py-lg-0">
                <li class="nav-item"><a class="nav-link text-warning" href="/integration/user/index.php">Produits</a></li>
               
                <li class="nav-item"><a class="nav-link text-warning" href="/integration/user/views/front/home.php">Accueil</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="/integration/user/index.php?action=team">Team</a></li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="/integration/user/index.php?action=cart">
                        <i class="fas fa-shopping-cart"></i> Panier
                        <?php 
                            if (!empty($_SESSION['cart']) && count($_SESSION['cart']) > 0):
                        ?>
                            <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Notification Alerts -->
<?php if (isset($_SESSION['cart_message'])): ?>
    <div class="container mt-4">
        <div class="alert alert-<?php echo $_SESSION['cart_success'] ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
            <i class="fas fa-<?php echo $_SESSION['cart_success'] ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($_SESSION['cart_message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    <?php 
        unset($_SESSION['cart_message']);
        unset($_SESSION['cart_success']);
    ?>
<?php endif; ?>

<!-- IA Diagnostic Section -->
<?php
// Pas de session IA nécessaire avec fetch — on garde juste la section HTML
?>
<section class="page-section" id="ia-diagnostic" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); padding: 60px 0;">
    <div class="container">
        <div class="text-center mb-5">
            <span style="background: rgba(99,102,241,0.2); color: #a78bfa; padding: 8px 20px; border-radius: 50px; font-size: 0.85rem; letter-spacing: 2px; text-transform: uppercase; font-weight: 600;">
                🤖 Intelligence Artificielle
            </span>
            <h2 class="section-heading text-uppercase mt-3" style="color: white;">Diagnostic Auto IA</h2>
            <h3 class="section-subheading" style="color: #a78bfa;">Décrivez votre problème, l'IA vous recommande les pièces</h3>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div style="background: rgba(255,255,255,0.05); border: 1px solid rgba(167,139,250,0.3); border-radius: 20px; padding: 35px; backdrop-filter: blur(10px);">

                    <!-- Zone erreur client -->
                    <div id="ia-client-error" style="display:none; background: rgba(239,68,68,0.15); border: 1px solid rgba(239,68,68,0.4); color: #fca5a5; border-radius: 12px; padding: 14px 18px; margin-bottom: 18px;">
                        <i class="fas fa-exclamation-triangle"></i> <span id="ia-client-error-msg"></span>
                    </div>

                    <!-- Input Zone -->
                    <div class="mb-4">
                        <label style="color: #e2e8f0; font-weight: 600; margin-bottom: 10px; display: block;">
                            <i class="fas fa-car-crash" style="color: #a78bfa;"></i> Décrivez votre problème automobile :
                        </label>
                        <textarea id="problemDescription" rows="4"
                            placeholder="Ex: Ma voiture fait un bruit bizarre au freinage, je sens une vibration quand je tourne à droite..."
                            maxlength="1000"
                            style="width:100%; background: rgba(255,255,255,0.08); border: 1px solid rgba(167,139,250,0.4); border-radius: 12px; color: white; padding: 15px; font-size: 0.95rem; resize: vertical; outline: none;"
                            onfocus="this.style.borderColor='#a78bfa'" onblur="this.style.borderColor='rgba(167,139,250,0.4)'"
                            oninput="updateCharCount()"></textarea>
                        <div style="display:flex; justify-content:space-between; margin-top:5px;">
                            <small style="color:#ef4444; font-size:0.8rem;" id="ia-field-error"></small>
                            <small style="color:#94a3b8; font-size:0.78rem;"><span id="charCount">0</span> / 1000</small>
                        </div>
                    </div>

                    <!-- Suggestions rapides -->
                    <div class="mb-4">
                        <p style="color: #94a3b8; font-size: 0.85rem; margin-bottom: 10px;">💡 Suggestions rapides :</p>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            <button type="button" class="suggestion-btn" onclick="setSuggestion(this)">Bruit au freinage</button>
                            <button type="button" class="suggestion-btn" onclick="setSuggestion(this)">Vibration au volant</button>
                            <button type="button" class="suggestion-btn" onclick="setSuggestion(this)">Moteur qui chauffe</button>
                            <button type="button" class="suggestion-btn" onclick="setSuggestion(this)">Voyant moteur allumé</button>
                            <button type="button" class="suggestion-btn" onclick="setSuggestion(this)">Fuite d'huile</button>
                            <button type="button" class="suggestion-btn" onclick="setSuggestion(this)">Démarrage difficile</button>
                        </div>
                    </div>

                    <!-- Bouton Analyser -->
                    <button id="analyzeBtn" onclick="analyzeWithAI()"
                        style="width:100%; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; border: none; padding: 15px; border-radius: 12px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: all 0.3s; letter-spacing: 1px;">
                        <i class="fas fa-robot"></i> &nbsp;ANALYSER AVEC L'IA
                    </button>

                    <!-- Loading animé -->
                    <div id="ia-loading" style="display:none; text-align:center; padding: 30px 0;">
                        <div style="display:inline-flex; gap: 8px; align-items: center;">
                            <div style="width:10px;height:10px;background:#a78bfa;border-radius:50%;animation:pulse 1.4s infinite ease-in-out both;"></div>
                            <div style="width:10px;height:10px;background:#a78bfa;border-radius:50%;animation:pulse 1.4s 0.2s infinite ease-in-out both;"></div>
                            <div style="width:10px;height:10px;background:#a78bfa;border-radius:50%;animation:pulse 1.4s 0.4s infinite ease-in-out both;"></div>
                        </div>
                        <p style="color: #a78bfa; margin-top: 12px; font-size: 0.9rem;">⏳ L'IA analyse votre problème, veuillez patienter...</p>
                    </div>

                    <!-- Zone résultat -->
                    <div id="ia-result" style="display:none; margin-top: 25px;">
                        <div style="border-top: 1px solid rgba(167,139,250,0.3); padding-top: 25px;">
                            <h5 style="color: #a78bfa; margin-bottom: 15px;"><i class="fas fa-stethoscope"></i> Diagnostic de l'IA :</h5>
                            <div id="ia-text" style="color: #e2e8f0; line-height: 1.8; background: rgba(99,102,241,0.1); border-radius: 12px; padding: 20px; margin-bottom: 20px; white-space: pre-wrap;"></div>

                            <h5 style="color: #10b981; margin-bottom: 15px;"><i class="fas fa-tools"></i> Pièces recommandées disponibles :</h5>
                            <div id="ia-pieces" class="row g-3"></div>

                            <div id="ia-no-pieces" style="display:none; color:#94a3b8; text-align:center; padding: 15px;">
                                <i class="fas fa-info-circle"></i> Aucune pièce correspondante trouvée dans notre stock actuellement.
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<style>
.suggestion-btn {
    background: rgba(99,102,241,0.15);
    color: #a78bfa;
    border: 1px solid rgba(167,139,250,0.35);
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 0.82rem;
    cursor: pointer;
    transition: all 0.2s;
}
.suggestion-btn:hover { background: rgba(99,102,241,0.35); color: white; }
#analyzeBtn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(99,102,241,0.4); }
#analyzeBtn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
@keyframes pulse {
    0%, 80%, 100% { transform: scale(0); opacity: 0.5; }
    40%           { transform: scale(1); opacity: 1;   }
}
.piece-ia-card {
    background: rgba(16,185,129,0.1);
    border: 1px solid rgba(16,185,129,0.3);
    border-radius: 12px; padding: 15px; color: white; transition: all 0.2s;
}
.piece-ia-card:hover { background: rgba(16,185,129,0.2); transform: translateY(-2px); }
</style>

<script>
// Stock injecté par PHP — utilisé côté client pour afficher les pièces recommandées
const stockPieces = <?php echo json_encode(array_values($pieces)); ?>;

function updateCharCount() {
    var val = document.getElementById('problemDescription').value;
    document.getElementById('charCount').textContent = val.length;
    if (val.length > 900) {
        document.getElementById('charCount').style.color = '#ef4444';
    } else {
        document.getElementById('charCount').style.color = '#94a3b8';
    }
}

function setSuggestion(btn) {
    document.getElementById('problemDescription').value = btn.textContent.trim();
    updateCharCount();
    document.querySelectorAll('.suggestion-btn').forEach(b => b.style.background = 'rgba(99,102,241,0.15)');
    btn.style.background = 'rgba(99,102,241,0.4)';
    // Cacher les erreurs
    document.getElementById('ia-client-error').style.display = 'none';
    document.getElementById('ia-field-error').textContent = '';
}

function showClientError(msg) {
    document.getElementById('ia-client-error-msg').textContent = msg;
    document.getElementById('ia-client-error').style.display = 'block';
    document.getElementById('ia-field-error').textContent = msg;
    document.getElementById('problemDescription').style.borderColor = '#ef4444';
}

function hideClientError() {
    document.getElementById('ia-client-error').style.display = 'none';
    document.getElementById('ia-field-error').textContent = '';
    document.getElementById('problemDescription').style.borderColor = 'rgba(167,139,250,0.4)';
}

async function analyzeWithAI() {
    const problem = document.getElementById('problemDescription').value.trim();

    // ── Validations côté client ──────────────────────────────────────────────
    hideClientError();
    if (!problem) {
        showClientError('Veuillez décrire votre problème avant de lancer le diagnostic.');
        document.getElementById('problemDescription').focus();
        return;
    }
    if (problem.length < 10) {
        showClientError('Description trop courte — minimum 10 caractères.');
        document.getElementById('problemDescription').focus();
        return;
    }
    if (problem.length > 1000) {
        showClientError('Description trop longue — maximum 1000 caractères.');
        return;
    }

    // ── UI : état loading ────────────────────────────────────────────────────
    document.getElementById('ia-loading').style.display = 'block';
    document.getElementById('ia-result').style.display  = 'none';
    document.getElementById('analyzeBtn').disabled = true;
    document.getElementById('analyzeBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> &nbsp;Analyse en cours...';

    const stockList = stockPieces.map(p =>
        `- ID:${p.id_piece} | ${p.nom_piece} | Ref:${p.reference} | Cat:${p.categorie} | Prix:${p.prix}€ | Stock:${p.quantite_stock}`
    ).join('\n');

    try {
        // ── Appel PHP via fetch (asynchrone — pas de rechargement de page) ──
        const response = await fetch("ai_diagnostic.php", {
            method:  "POST",
            headers: { "Content-Type": "application/json" },
            body:    JSON.stringify({ problem: problem, stock: stockList })
        });

        const data = await response.json();

        if (!data.success) {
            showClientError(data.error || 'Erreur inconnue du serveur.');
            document.getElementById('ia-loading').style.display = 'none';
            document.getElementById('analyzeBtn').disabled = false;
            document.getElementById('analyzeBtn').innerHTML = '<i class="fas fa-robot"></i> &nbsp;ANALYSER AVEC L\'IA';
            return;
        }

        const fullText = data.text;

        // ── Parsing diagnostic + IDs ─────────────────────────────────────────
        const lines        = fullText.split('\n');
        const piecesLine   = lines.find(l => l.startsWith('PIECES_IDS:'));
        const diagnosticText = lines.filter(l => !l.startsWith('PIECES_IDS:')).join('\n').trim();

        document.getElementById('ia-text').textContent = diagnosticText;

        // ── Affichage pièces recommandées ────────────────────────────────────
        const piecesContainer = document.getElementById('ia-pieces');
        piecesContainer.innerHTML = '';
        document.getElementById('ia-no-pieces').style.display = 'none';

        if (piecesLine) {
            const ids = piecesLine.replace('PIECES_IDS:', '').split(',').map(id => parseInt(id.trim()));
            const recommended = stockPieces.filter(p => ids.includes(parseInt(p.id_piece)) && p.quantite_stock > 0);

            if (recommended.length > 0) {
                recommended.forEach(p => {
                    piecesContainer.innerHTML += `
                        <div class="col-md-6">
                            <div class="piece-ia-card">
                                <div style="display:flex; justify-content:space-between; align-items:start;">
                                    <div>
                                        <strong style="font-size:0.95rem;">${p.nom_piece}</strong><br>
                                        <small style="color:#94a3b8;">Réf: ${p.reference} | ${p.categorie}</small>
                                    </div>
                                    <span style="color:#10b981; font-weight:700; font-size:1.1rem;">${parseFloat(p.prix).toFixed(2)}€</span>
                                </div>
                                <div style="margin-top:10px; display:flex; justify-content:space-between; align-items:center;">
                                    <span style="color:#94a3b8; font-size:0.8rem;"><i class="fas fa-boxes"></i> ${p.quantite_stock} en stock</span>
                                    <button onclick="ajouterAuPanier(${p.id_piece})"
                                        style="background:#10b981; color:white; border:none; padding:6px 14px; border-radius:8px; font-size:0.82rem; cursor:pointer;">
                                        <i class="fas fa-cart-plus"></i> Ajouter
                                    </button>
                                </div>
                            </div>
                        </div>`;
                });
            } else {
                document.getElementById('ia-no-pieces').style.display = 'block';
            }
        } else {
            document.getElementById('ia-no-pieces').style.display = 'block';
        }

        document.getElementById('ia-result').style.display = 'block';

    } catch (err) {
        showClientError('Erreur réseau. Vérifiez votre connexion et réessayez.');
    }

    // ── UI : reset ───────────────────────────────────────────────────────────
    document.getElementById('ia-loading').style.display = 'none';
    document.getElementById('analyzeBtn').disabled = false;
    document.getElementById('analyzeBtn').innerHTML = '<i class="fas fa-robot"></i> &nbsp;ANALYSER AVEC L\'IA';
}

function ajouterAuPanier(idPiece) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'index.php?action=add_to_cart';
    form.innerHTML = `<input type="hidden" name="id_piece" value="${idPiece}"><input type="hidden" name="quantite" value="1">`;
    document.body.appendChild(form);
    form.submit();
}
</script>
<!-- Portfolio Grid-->
<section class="page-section bg-light" id="portfolio">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-heading text-uppercase">Pieces</h2>
                    <h3 class="section-subheading text-muted">bienvenue chez nous.</h3>
                </div>

                <!-- Search & Filter Bar -->
                <div class="row mb-5">
                    <div class="col-md-8">
                        <form method="GET" class="input-group">
                            <input type="text" class="form-control" name="search" placeholder="Rechercher une pièce..." value="<?php echo htmlspecialchars($search); ?>">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i> Rechercher
                            </button>
                        </form>
                    </div>
                    <div class="col-md-4">
                        <form method="GET" class="input-group">
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="">Toutes les catégories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cat); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (!empty($search)): ?>
                                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <?php if (!empty($pieces)): ?>
                        <?php foreach ($pieces as $index => $piece): ?>
                            <div class="col-lg-4 col-sm-6 mb-4">
                                <!-- Portfolio item -->
                                <div class="portfolio-item">
                                    <a class="portfolio-link" data-bs-toggle="modal" href="#portfolioModal<?php echo $piece['id_piece']; ?>">
                                        <div class="portfolio-hover">
                                            <div class="portfolio-hover-content"><i class="fas fa-plus fa-3x"></i></div>
                                        </div>
                                        <?php 
                                            // Construire le chemin de l'image basé sur l'ID
                                            $imagePath = 'assets/img/portfolio/' . $piece['id_piece'] . '.jpg';
                                            // Si une image est spécifiée dans la base de données, l'utiliser
                                            if (!empty($piece['image'])) {
                                                $imagePath = trim($piece['image']);
                                            }
                                        ?>
                                        <img class="img-fluid" src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($piece['nom_piece']); ?>" style="object-fit: cover; height: 300px;" onerror="this.src='assets/img/default-image.svg'" />
                                    </a>
                                    <div class="portfolio-caption">
                                        <div class="portfolio-caption-heading"><?php echo htmlspecialchars($piece['nom_piece']); ?></div>
                                        <div class="portfolio-caption-subheading text-muted">Référence: <?php echo htmlspecialchars($piece['reference']); ?></div>
                                        <div class="portfolio-caption-subheading text-muted">Prix: <strong><?php echo number_format($piece['prix'], 2); ?> €</strong></div>
                                        <div class="portfolio-caption-subheading text-muted">
                                            Stock: 
                                            <?php if ($piece['quantite_stock'] > 0): ?>
                                                <span class="badge bg-success"><?php echo $piece['quantite_stock']; ?> unités</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Rupture de stock</span>
                                            <?php endif; ?>
                                        </div>
                                        <button class="btn btn-sm btn-primary mt-2" data-bs-toggle="modal" data-bs-target="#portfolioModal<?php echo $piece['id_piece']; ?>">
                                            <i class="fas fa-eye"></i> Voir détails
                                        </button>
                                    </div>
                                    
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <p class="text-center text-muted">Aucune pièce disponible pour le moment.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <!-- Modals for Pieces -->
        <?php if (!empty($pieces)): ?>
            <?php foreach ($pieces as $piece): ?>
                <div class="modal fade" id="portfolioModal<?php echo $piece['id_piece']; ?>" tabindex="-1" aria-labelledby="portfolioModal<?php echo $piece['id_piece']; ?>Label" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="portfolioModal<?php echo $piece['id_piece']; ?>Label">
                                    <?php echo htmlspecialchars($piece['nom_piece']); ?>
                                </h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php 
                                            $imageUrl = !empty($piece['image']) ? trim($piece['image']) : 'assets/img/default-image.svg';
                                        ?>
                                        <img class="img-fluid mb-3" src="<?php echo $imageUrl; ?>" alt="<?php echo htmlspecialchars($piece['nom_piece']); ?>" onerror="this.src='assets/img/default-image.svg'">
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless">
                                            <tr>
                                                <td><strong>Référence:</strong></td>
                                                <td><?php echo htmlspecialchars($piece['reference']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Prix:</strong></td>
                                                <td><span class="h5 text-primary"><?php echo number_format($piece['prix'], 2); ?> €</span></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Catégorie:</strong></td>
                                                <td><?php echo htmlspecialchars($piece['categorie']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Fournisseur:</strong></td>
                                                <td><?php echo htmlspecialchars($piece['fourniseur']); ?></td>
                                            </tr>
                                            <tr>
                                                <td><strong>Stock disponible:</strong></td>
                                                <td>
                                                    <?php if ($piece['quantite_stock'] > 0): ?>
                                                        <span class="badge bg-success"><?php echo $piece['quantite_stock']; ?> unités</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Rupture de stock</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td><strong>Ajouté le:</strong></td>
                                                <td><?php echo date('d/m/Y', strtotime($piece['date_ajout'])); ?></td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="mt-3">
                                    <h6>Description:</h6>
                                    <p><?php echo nl2br(htmlspecialchars($piece['description'])); ?></p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <?php if ($piece['quantite_stock'] > 0): ?>
                                    <form method="POST" action="index.php?action=add_to_cart" class="d-flex gap-2">
                                        <input type="hidden" name="id_piece" value="<?php echo $piece['id_piece']; ?>">
                                        <div class="input-group" style="width: 150px;">
                                            <button class="btn btn-outline-secondary" type="button" onclick="decreaseQuantity(this)">−</button>
                                            <input type="number" name="quantite" class="form-control text-center quantite-input" value="1" min="1" max="<?php echo $piece['quantite_stock']; ?>">
                                            <button class="btn btn-outline-secondary" type="button" onclick="increaseQuantity(this)">+</button>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn btn-danger" disabled>Rupture de stock</button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- Bootstrap Bundle JS -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            function increaseQuantity(btn) {
                const input = btn.parentElement.querySelector('.quantite-input');
                const max = parseInt(input.max);
                const current = parseInt(input.value) || 1;
                if (current < max) {
                    input.value = current + 1;
                }
            }

            function decreaseQuantity(btn) {
                const input = btn.parentElement.querySelector('.quantite-input');
                const current = parseInt(input.value) || 1;
                if (current > 1) {
                    input.value = current - 1;
                }
            }
        </script>
    </body>
</html>
