<?php
include("../config/db.php");

$result = mysqli_query($conn, "SELECT * FROM matiere");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des Matières</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Liste des Matières</h2>
    
    <a href="ajouter_matiere.php" class="btn">Ajouter une matière</a>
    <a href="index.php" class="btn" style="margin-left: 20px;">Gerer les matieres des salles de classe</a>

    <table border="1" cellpadding="5" cellspacing="0" style="margin-top: 15px;">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Coefficient</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                <tr>
                    <td><?= htmlspecialchars($row['nom']) ?></td>
                    <td><?= $row['coefficient'] ?></td>
                    <td>
                        <a href="modifier_matiere.php?id=<?= $row['id'] ?>"class="btn-edit">Modifier</a> |
                        <a href="supprimer_matiere.php?id=<?= $row['id'] ?>" class="btn-delete"onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <p><a href="../utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
</body>
</html>
