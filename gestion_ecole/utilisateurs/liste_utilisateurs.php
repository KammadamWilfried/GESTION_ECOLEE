<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: login.php");
    exit;
}

$sql = "SELECT * FROM utilisateur ORDER BY nom, prenom";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des utilisateurs</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        a { text-decoration: none; color: blue; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>

<h2>Liste des utilisateurs</h2>

<p><a href="ajouter_utilisateur.php">Ajouter un utilisateur</a></p>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Email</th>
            <th>Type</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($user = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?= htmlspecialchars($user['id']) ?></td>
                    <td><?= htmlspecialchars($user['nom']) ?></td>
                    <td><?= htmlspecialchars($user['prenom']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['type_utilisateur']) ?></td>
                    <td>
                        <a href="modifier_utilisateur.php?id=<?= $user['id'] ?>">Modifier</a> |
                        <a href="supprimer_utilisateur.php?id=<?= $user['id'] ?>" onclick="return confirm('Supprimer cet utilisateur ?');">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="6">Aucun utilisateur trouvé.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<p><a href="dashboard.php">Retour au tableau de bord</a></p>

</body>
</html>
