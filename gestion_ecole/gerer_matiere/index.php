<?php
include("../config/db.php");
$classes = mysqli_query($conn, "SELECT * FROM classe");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Choisir une Classe</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Sélectionnez une classe</h2>
    <form action="matieres.php" method="get">
        <label for="classe_id">Classe :</label>
        <select name="classe_id" id="classe_id" required>
            <option value="">-- Choisir --</option>
            <?php while ($classe = mysqli_fetch_assoc($classes)): ?>
                <option value="<?= $classe['id'] ?>"><?= htmlspecialchars($classe['nom']) ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Voir les matières</button>
    </form>
        <p><a href="../utilisateurs/dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>
