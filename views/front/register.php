<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
        <title>Inscription - Garage Services</title>
        <link rel="icon" type="image/x-icon" href="../../assets/front/assets/favicon.ico" />
        <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
        <link href="../../assets/front/css/styles.css" rel="stylesheet" />
        
        <style>
            section#register {
                background-color: #212529;
                background-image: url("../../assets/front/assets/img/map-image.png");
                background-repeat: no-repeat;
                background-position: center;
                min-height: 100vh;
                padding: 100px 0;
            }
            .form-control { padding: 1.25rem; border-radius: 0.5rem; }
        </style>
    </head>
    <body id="page-top">
        <section class="page-section" id="register">
            <div class="container">
                <div class="text-center">
                    <h2 class="section-heading text-uppercase text-white">Inscription</h2>
                    <h3 class="section-subheading text-muted">Créez votre compte pour accéder à nos services.</h3>
                </div>

                <form id="registerForm" action="../../controller/UserController.php" method="POST" onsubmit="return validateForm()">
                    <input type="hidden" name="action" value="register">
                    
                    <div class="row align-items-stretch mb-5 justify-content-center">
                        <div class="col-md-6">
                            <div class="form-group mb-3">
                                <input class="form-control" name="prenom" type="text" placeholder="Votre Prénom *" required />
                            </div>
                            <div class="form-group mb-3">
                                <input class="form-control" name="nom" type="text" placeholder="Votre Nom *" required />
                            </div>
                            <div class="form-group mb-3">
                                <input class="form-control" name="email" id="email" type="email" placeholder="Votre Email *" required />
                            </div>
                            <div class="form-group mb-3">
                                <input class="form-control" name="telephone" type="tel" placeholder="Votre Téléphone *" required />
                            </div>
                            <div class="form-group mb-3">
                                <input class="form-control" name="mot_de_passe" id="password" type="password" placeholder="Mot de passe (min 6 caractères) *" required />
                            </div>
                            <div class="form-group mb-md-0">
                                <textarea class="form-control" name="adresse" placeholder="Votre Adresse *" required></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-primary btn-xl text-uppercase" type="submit">S'inscrire</button>
                        <div class="mt-3">
                            <a href="login.php" class="text-white">Déjà inscrit ? Connectez-vous ici.</a>
                        </div>
                    </div>
                </form>
            </div>
        </section>

        <script>
        function validateForm() {
            const password = document.getElementById('password').value;
            if (password.length < 6) {
                alert("Le mot de passe est trop court !");
                return false;
            }
            return true;
        }
        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="../../assets/front/js/scripts.js"></script>
    </body>
</html>