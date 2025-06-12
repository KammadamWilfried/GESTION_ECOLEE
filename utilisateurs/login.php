<?php
session_start();
include("../config/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $password = sha1($_POST["password"]);

    // Requête sécurisée
    $query = "SELECT * FROM utilisateur WHERE email = ? AND password = ?";
    $stmt = $conn->prepare($query);

    if ($stmt === false) {
        die("Erreur de préparation : " . mysqli_error($conn));
    }

    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $utilisateur = $result->fetch_assoc();

        $_SESSION["utilisateur_id"] = $utilisateur["id"];
        $_SESSION["nom"] = $utilisateur["nom"];
        $_SESSION["type_utilisateur"] = $utilisateur["type_utilisateur"];
        $_SESSION["id"] = $utilisateur["id"]; // Toujours utile

        $type = $utilisateur["type_utilisateur"];
        $utilisateur_id = $utilisateur["id"];

        if ($type === "Etudiant") {
            $res = mysqli_query($conn, "SELECT id FROM etudiant WHERE id = $utilisateur_id");
            if ($row = mysqli_fetch_assoc($res)) {
                $_SESSION["id_etudiant"] = $row["id"];
            }
            header("Location: dashboard.php");
            exit;

        } elseif ($type === "Enseignant") {
            $res = mysqli_query($conn, "SELECT id FROM enseignant WHERE id = $utilisateur_id");
            if ($row = mysqli_fetch_assoc($res)) {
                $_SESSION["id_enseignant"] = $row["id"];
            }
            header("Location: dashboard.php");
            exit;

        } elseif ($type === "Administrateur") {
            header("Location: dashboard.php");
            exit;
        }
    } else {
        $message = "Email ou mot de passe incorrect.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0; padding: 0; background: #f2f2f2;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            max-width: 400px; margin: 80px auto; padding: 40px;
            background: #ffffff; border-radius: 10px;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; color: #003366; }
        form { display: flex; flex-direction: column; }
        label { margin: 10px 0 5px; font-weight: bold; }
        input[type="email"], input[type="password"] {
            padding: 10px; border: 1px solid #ccc; border-radius: 5px;
        }
        button {
            margin-top: 20px; padding: 12px;
            background: #003366; color: white; font-weight: bold;
            border: none; border-radius: 5px; cursor: pointer;
            transition: 0.3s;
        }
        button:hover { background: #0055aa; }
        .message { color: red; text-align: center; margin-top: 15px; }
        @media (max-width: 500px) {
            .login-container { margin: 40px 20px; padding: 30px; }
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Connexion</h2>
    <?php if ($message): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST" action="">
        <label for="email">Email :</label>
        <input type="email" name="email" id="email" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="password" id="password" required>

        <button type="submit">Se connecter</button>
    </form>
</div>

</body>
</html>