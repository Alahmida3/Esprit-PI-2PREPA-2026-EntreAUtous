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
    <title>Panier - Pièces Auto</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
    <link href="css/styles.css" rel="stylesheet" />
    <style>
        body {
            padding-top: 100px;
        }
        .cart-empty {
            text-align: center;
            padding: 60px 20px;
        }
        .cart-item {
            border-bottom: 1px solid #ddd;
            padding: 15px 0;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .price-total {
            font-size: 1.25rem;
            font-weight: bold;
            color: #28a745;
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
                <li class="nav-item"><a class="nav-link text-warning" href="/integration/user/index.php?action=about">About Us</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="/integration/user/index.php?action=team">Team</a></li>
                <li class="nav-item">
                    <a class="nav-link text-warning" href="/integration/user/index.php?action=cart">
                        <i class="fas fa-shopping-cart"></i> Panier
                        <?php 
                            $_SESSION['cart'] = $_SESSION['cart'] ?? [];
                            if (count($_SESSION['cart']) > 0):
                        ?>
                            <span class="badge bg-danger"><?php echo count($_SESSION['cart']); ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<section class="page-section" id="cart">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-heading text-uppercase">Mon Panier</h2>
            <h3 class="section-subheading text-muted">Vérifiez vos articles</h3>
        </div>

        <?php if (empty($_SESSION['cart'])): ?>
            <div class="cart-empty">
                <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                <h4>Votre panier est vide</h4>
                <p class="text-muted">Commencez par ajouter des articles à votre panier</p>
                <a href="/integration/user/index.php" class="btn btn-primary mt-3">
                    <i class="fas fa-arrow-left"></i> Continuer vos achats
                </a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Article</th>
                                        <th>Référence</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Sous-total</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $total = 0;
                                    foreach ($_SESSION['cart'] as $item): 
                                        $sousTotal = $item['prix'] * $item['quantite'];
                                        $total += $sousTotal;
                                    ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($item['nom_piece']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($item['reference']); ?></td>
                                            <td><?php echo number_format($item['prix'], 2); ?> €</td>
                                            <td>
                                                <form method="POST" action="/integration/user/index.php?action=update_cart" style="display: inline;">
                                                    <input type="hidden" name="id_piece" value="<?php echo $item['id_piece']; ?>">
                                                    <div class="input-group" style="width: 120px;">
                                                        <button class="btn btn-sm btn-outline-secondary" type="button" onclick="decreaseQty(this)">−</button>
                                                        <input type="number" name="quantite" class="form-control form-control-sm text-center qty-input" value="<?php echo $item['quantite']; ?>" min="1" onchange="this.form.submit()">
                                                        <button class="btn btn-sm btn-outline-secondary" type="button" onclick="increaseQty(this)">+</button>
                                                    </div>
                                                </form>
                                            </td>
                                            <td class="price-total"><?php echo number_format($sousTotal, 2); ?> €</td>
                                            <td>
                                                <form method="POST" action="/integration/user/index.php?action=remove_from_cart" style="display: inline;">
                                                    <input type="hidden" name="id_piece" value="<?php echo $item['id_piece']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card sticky-top" style="top: 120px;">
                        <div class="card-body">
                            <h5 class="card-title">Résumé</h5>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Articles:</span>
                                <strong><?php echo count($_SESSION['cart']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span>Total:</span>
                                <span class="price-total"><?php echo number_format($total, 2); ?> €</span>
                            </div>
                            <hr>
                            <form method="POST" action="/integration/user/index.php?action=checkout">
                                <button type="submit" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-lock"></i> Procéder au paiement
                                </button>
                            </form>
                            <a href="/integration/user/index.php" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="fas fa-arrow-left"></i> Continuer les achats
                            </a>
                            <form method="POST" action="/integration/user/index.php?action=clear_cart">
                                <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Êtes-vous sûr ?')">
                                    <i class="fas fa-trash"></i> Vider le panier
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function increaseQty(btn) {
        const input = btn.parentElement.querySelector('.qty-input');
        input.value = parseInt(input.value) + 1;
    }

    function decreaseQty(btn) {
        const input = btn.parentElement.querySelector('.qty-input');
        if (parseInt(input.value) > 1) {
            input.value = parseInt(input.value) - 1;
        }
    }
</script>
</body>
</html>
