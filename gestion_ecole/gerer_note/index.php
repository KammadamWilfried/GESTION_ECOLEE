<?php
session_start();
require_once '../config/db.php';

// Vérification de la session
if (!isset($_SESSION["utilisateur_id"]) || $_SESSION["type_utilisateur"] !== "Enseignant") {
    header("Location: ../login.php");
    exit;
}

$enseignant_id = $_SESSION["utilisateur_id"];
$message = "";

$query = "
SELECT DISTINCT c.id AS classe_id, c.nom AS classe_nom, m.id AS matiere_id, m.nom AS matiere_nom
FROM matiere_enseignant me
INNER JOIN matiere m ON me.matiere_id = m.id
INNER JOIN classe_matiere cm ON cm.id_matiere = m.id
INNER JOIN classe c ON cm.id_classe = c.id
WHERE me.enseignant_id = ?
ORDER BY c.nom, m.nom
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $enseignant_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Erreur SQL : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gérer les notes</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gérer les notes</h1>

    <?php if ($message): ?>
        <p style="color: red;"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <th>Classe</th>
                <th>Matière</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($row['classe_nom']) ?></td>
                    <td><?= htmlspecialchars($row['matiere_nom']) ?></td>
                    <td>
                        <a class="btn" href="gerer_notes.php?classe_id=<?= $row['classe_id'] ?>&matiere_id=<?= $row['matiere_id'] ?>">Voir / Modifier</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <p><a href="../utilisateurs/dashboard.php">Retour</a></p>
</body>
</html>