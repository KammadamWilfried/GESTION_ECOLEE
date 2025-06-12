<?php
include("../config/db.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = "";

if ($id <= 0) {
    header("Location: liste_matieres.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = mysqli_real_escape_string($conn, $_POST["nom"]);
    $coefficient = (int)$_POST["coefficient"];

    $query = "UPDATE matiere SET nom = '$nom', coefficient = $coefficient WHERE id = $id";
    if (mysqli_query($conn, $query)) {
        $message = "Matière modifiée avec succès.";
    } else {
        $message = "Erreur lors de la modification.";
    }
}

$result = mysqli_query($conn, "SELECT * FROM matiere WHERE id = $id");
$matiere = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modifier une Matière</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Modifier une Matière</h2>
    <?php if ($message): ?><p style="color: green;"><?= $message ?></p><?php endif; ?>

    <form method="POST">
        <label>Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($matiere['nom']) ?>" required><br><br>

        <label>Coefficient :</label><br>
        <input type="number" name="coefficient" value="<?= $matiere['coefficient'] ?>" required><br><br>

        <button type="submit">Modifier</button>
        <a href="liste_matieres.php">Retour à la liste</a>
    </form>
</body>
</html>
