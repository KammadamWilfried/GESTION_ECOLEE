<?php
session_start();
include("../config/db.php");

// Vérification admin
if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] != "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$query = "SELECT u.id, u.nom, u.prenom, u.email, u.telephone, e.specialite
          FROM utilisateur u
          JOIN enseignant e ON u.id = e.id
          WHERE u.type_utilisateur = 'Enseignant'
          ORDER BY u.nom";

$result = mysqli_query($conn, $query);
$enseignants = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $enseignants[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gérer les enseignants</title>
    <link rel="stylesheet" href="styles.css" />

</head>
<body>
<div class="container">
    <h2>Liste des enseignants</h2>
    <a href="ajouter.php" class="btn">Ajouter un enseignant</a>

    <table>
        <thead>
            <tr>
                <th>ID</th><th>Nom</th><th>Prénom</th><th>Email</th><th>Téléphone</th><th>Spécialité</th><th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($enseignants) > 0): ?>
                <?php foreach ($enseignants as $e): ?>
                    <tr>
                        <td><?= $e['id'] ?></td>
                        <td><?= htmlspecialchars($e['nom']) ?></td>
                        <td><?= htmlspecialchars($e['prenom']) ?></td>
                        <td><?= htmlspecialchars($e['email']) ?></td>
                        <td><?= htmlspecialchars($e['telephone']) ?></td>
                        <td><?= htmlspecialchars($e['specialite']) ?></td>
                        <td>
                            <a href="modifier.php?id=<?= $e['id'] ?>" class="modifier">Modifier</a>
                            <a href="supprimer.php?id=<?= $e['id'] ?>" class="supprimer" onclick="return confirm('Supprimer cet enseignant ?')">Supprimer</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7">Aucun enseignant trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    <p><a href="../utilisateurs/dashboard.php">Retour au tableau de bord</a></p>
</div>
</body>
</html>
