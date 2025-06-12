<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $niveau = mysqli_real_escape_string($conn, $_POST['niveau']);
    $capacite = (int) $_POST['capacite'];
    $nombre = 0; // par défaut

    if ($nom && $capacite > 0) {
        $sql = "INSERT INTO classe (nom, niveau, capacite, nombre) VALUES ('$nom', '$niveau', $capacite, $nombre)";
        if (mysqli_query($conn, $sql)) {
            header("Location: index.php");
            exit;
        } else {
            $message = "Erreur lors de l'ajout : " . mysqli_error($conn);
        }
    } else {
        $message = "Veuillez remplir tous les champs correctement.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une classe</title>
     <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Ajouter une classe</h1>

    <?php if ($message): ?>
        <p style="color: red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nom :</label><br>
        <input type="text" name="nom" required><br><br>

        <label>Niveau :</label><br>
        <input type="text" name="niveau"><br><br>

        <label>Capacité :</label><br>
        <input type="number" name="capacite" min="1" required><br><br>

        <button type="submit">Ajouter</button>
    </form>

    <p><a href="index.php">Retour à la liste</a></p>
</body>
</html>
