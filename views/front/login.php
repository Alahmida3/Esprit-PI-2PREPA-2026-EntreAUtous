<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>

    <!-- FRONT TEMPLATE CSS UNIQUEMENT -->
    <link href="/web/assets/front/css/styles.css" rel="stylesheet">
</head>

<body>

<!-- HEADER STYLE AGENCY -->
<header class="masthead">
    <div class="container text-center">

        <div class="bg-light p-5 rounded">

            <h2>Connexion</h2>

            <form action="/web/controller/login_process.php" method="POST">

                <div class="mb-3">
                    <input class="form-control" type="email" name="email" placeholder="Email" required>
                </div>

                <div class="mb-3">
                    <input class="form-control" type="password" name="password" placeholder="Mot de passe" required>
                </div>

                <button class="btn btn-primary w-100">Se connecter</button>

            </form>

            <br>
            <a href="register.php">Créer un compte</a>

        </div>

    </div>
</header>

</body>
</html>