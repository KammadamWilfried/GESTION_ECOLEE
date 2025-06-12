<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$query = "SELECT * FROM classe ORDER BY nom ASC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des classes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Liste des classes</h1>
    <p><a href="ajouter.php"class="btn">+ Ajouter une classe</a></p>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Niveau</th>
                <th>Capacité</th>
                <th>Nombre d'élèves</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($classe = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($classe['nom']) ?></td>
                    <td><?= htmlspecialchars($classe['niveau']) ?></td>
                    <td><?= (int)$classe['capacite'] ?></td>
                    <td><?= (int)$classe['nombre'] ?></td>
                    <td>
                        <a href="modifier.php?id=<?= $classe['id'] ?>"class="modifier">Modifier</a> |
                        <a href="supprimer.php?id=<?= $classe['id'] ?>"class="supprimer" onclick="return confirm('Supprimer cette classe ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <p><a href="../utilisateurs/dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>
