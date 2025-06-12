<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = "";

if ($id <= 0) {
    header("Location: index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $niveau = mysqli_real_escape_string($conn, $_POST['niveau']);
    $capacite = (int) $_POST['capacite'];
    $nombre = (int) $_POST['nombre'];

    if ($nom && $capacite > 0 && $nombre >= 0) {
        $sql = "UPDATE classe SET nom='$nom', niveau='$niveau', capacite=$capacite, nombre=$nombre WHERE id=$id";
        if (mysqli_query($conn, $sql)) {
            header("Location: index.php");
            exit;
        } else {
            $message = "Erreur lors de la modification : " . mysqli_error($conn);
        }
    } else {
        $message = "Veuillez remplir tous les champs correctement.";
    }
} else {
    $result = mysqli_query($conn, "SELECT * FROM classe WHERE id=$id");
    if (mysqli_num_rows($result) == 1) {
        $classe = mysqli_fetch_assoc($result);
    } else {
        header("Location: index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier une classe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Modifier la classe</h1>

    <?php if ($message): ?>
        <p style="color: red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($classe['nom']) ?>" required><br><br>

        <label>Niveau :</label><br>
        <input type="text" name="niveau" value="<?= htmlspecialchars($classe['niveau']) ?>"><br><br>

        <label>Capacité :</label><br>
        <input type="number" name="capacite" min="1" value="<?= (int)$classe['capacite'] ?>" required><br><br>

        <label>Nombre d'élèves :</label><br>
        <input type="number" name="nombre" min="0" max="<?= (int)$classe['capacite'] ?>" value="<?= (int)$classe['nombre'] ?>" required><br><br>

        <button type="submit">Modifier</button>
    </form>

    <p><a href="index.php">Retour à la liste</a></p>
</body>
</html>
