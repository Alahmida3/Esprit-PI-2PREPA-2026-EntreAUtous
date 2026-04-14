<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Register</title>

    <link href="/web/assets/front/css/styles.css" rel="stylesheet">
</head>

<body>

<header class="masthead">
    <div class="container text-center">

        <div class="bg-light p-5 rounded">

            <h2>Inscription</h2>

            <form action="/web/controller/register_process.php" method="POST">

                <!-- NOM -->
                <div class="mb-2">
                    <input class="form-control" type="text" name="nom"
                           placeholder="Nom" required minlength="2">
                </div>

                <!-- PRENOM -->
                <div class="mb-2">
                    <input class="form-control" type="text" name="prenom"
                           placeholder="Prénom" required minlength="2">
                </div>

                <!-- EMAIL -->
                <div class="mb-2">
                    <input class="form-control" type="email" name="email"
                           placeholder="Email" required>
                </div>

                <!-- TELEPHONE -->
                <div class="mb-2">
                    <input class="form-control" type="text"
                           name="telephone"
                           id="telephone"
                           placeholder="Téléphone (8 chiffres)"
                           maxlength="8"
                           required>

                    <small id="telError" style="color:red; display:none;">
                        ⚠ Le numéro doit contenir exactement 8 chiffres
                    </small>
                </div>

                <!-- ADRESSE -->
                <div class="mb-2">
                    <input class="form-control" type="text" name="adresse"
                           placeholder="Adresse">
                </div>

                <!-- PASSWORD -->
                <div class="mb-3">
                    <input class="form-control" type="password" name="password"
                           placeholder="Mot de passe"
                           minlength="6"
                           required>
                </div>

                <button class="btn btn-primary w-100">S'inscrire</button>

            </form>

            <br>
            <a href="login.php">Déjà un compte ?</a>

        </div>

    </div>
</header>

<!-- JS CONTROL TELEPHONE -->
<script>
const tel = document.getElementById("telephone");
const error = document.getElementById("telError");

// bloquer lettres
tel.addEventListener("input", function () {
    this.value = this.value.replace(/[^0-9]/g, '');
});

// validation visuelle
tel.addEventListener("blur", function () {
    if (this.value.length !== 8) {
        error.style.display = "block";
    } else {
        error.style.display = "none";
    }
});
</script>

</body>
</html>