<?php
include("../config/db.php");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = mysqli_real_escape_string($conn, $_POST["nom"]);
    $coefficient = (int)$_POST["coefficient"];

    $query = "INSERT INTO matiere (nom, coefficient) VALUES ('$nom', $coefficient)";
    if (mysqli_query($conn, $query)) {
        $message = "Matière ajoutée avec succès.";
    } else {
        $message = "Erreur lors de l'ajout.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Ajouter une Matière</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Ajouter une Matière</h2>
    <?php if ($message): ?><p style="color: green;"><?= $message ?></p><?php endif; ?>

    <form method="POST">
        <label>Nom :</label><br>
        <input type="text" name="nom" required><br><br>

        <label>Coefficient :</label><br>
        <input type="number" name="coefficient" required><br><br>

        <button type="submit">Ajouter</button>
        <a href="liste_matieres.php">Retour à la liste</a>
    </form>
</body>
</html>
