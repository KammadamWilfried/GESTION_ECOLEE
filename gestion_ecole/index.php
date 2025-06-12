<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Bienvenue - École Excellence</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f2f2f2;
            color: #333;
        }
        header {
            background: #003366;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .contenu {
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 10px;
        }
        .btn-connexion {
            display: inline-block;
            padding: 12px 25px;
            background: #003366;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-connexion:hover {
            background: #0055aa;
        }
        footer {
            background: #003366;
            color: white;
            text-align: center;
            padding: 15px;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        img.logo {
            width: 100px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<header>
    <h1>École Excellence</h1>
</header>

<div class="contenu">
    <img src="image/logo_ecole.png" alt="Logo École" class="logo">
    <h2>Bienvenue sur la plateforme de gestion scolaire</h2>
    <p>
        L’école Excellence est un établissement de référence dédié à la formation de qualité des jeunes talents.
        Grâce à une équipe pédagogique expérimentée, des salles modernes et un suivi personnalisé, nous offrons
        un cadre idéal pour la réussite scolaire.
    </p>
    <p>
        Vous êtes enseignant, étudiant ou membre de l’administration ? Cliquez ci-dessous pour accéder à votre espace personnel.
    </p>
    <a href="utilisateurs/login.php" class="btn-connexion">Se connecter</a>
</div>

<footer>
    &copy; 2025 - École Excellence. Tous droits réservés.
</footer>

</body>
</html>