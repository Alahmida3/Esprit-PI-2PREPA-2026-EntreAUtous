<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Connexion - Garage Services</title>
    <link href="../../assets/front/css/styles.css" rel="stylesheet" />
    <style>
        body { background-color: #212529; color: white; padding-top: 100px; }
        .login-card { max-width: 400px; margin: auto; background: rgba(255,255,255,0.1); padding: 30px; border-radius: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <h2 class="text-center text-uppercase">Connexion</h2>
            <hr class="bg-primary">
            
            <?php if(isset($_GET['error'])): ?>
                <div class="alert alert-danger">Identifiants incorrects</div>
            <?php endif; ?>

            <form action="../../controller/UserController.php" method="POST">
                <input type="hidden" name="action" value="login">
                <div class="mb-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="admin@garage.com">
                </div>
                <div class="mb-3">
                    <label>Mot de passe</label>
                    <input type="password" name="mot_de_passe" class="form-control" required>
                </div>
                <input type="hidden" name="action" value="login"> <button type="submit" class="btn btn-primary w-100">Se connecter</button>
            </form>
            <div class="text-center mt-3">
                <a href="register.php" class="text-white-50">Pas encore de compte ?</a>
            </div>
        </div>
    </div>
</body>
</html>