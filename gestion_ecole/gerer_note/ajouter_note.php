<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["utilisateur_id"]) || $_SESSION["type_utilisateur"] !== "Enseignant") {
    header("Location: ../login.php");
    exit;
}

$classe_id = $_GET["classe_id"];
$matiere_id = $_GET["matiere_id"];

$query_etudiants = "
    SELECT e.id, e.nom, e.prenom
    FROM etudiant e
    JOIN inscription i ON i.etudiant_id = e.id
    WHERE i.classe_id = $classe_id
";
$result_etudiants = mysqli_query($conn, $query_etudiants);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter des notes</title>
</head>
<body>
    <h2>Ajouter / Modifier des notes</h2>
    <form action="enregistrer_note.php" method="POST">
        <input type="hidden" name="classe_id" value="<?= $classe_id ?>">
        <input type="hidden" name="matiere_id" value="<?= $matiere_id ?>">

        <table border="1" cellpadding="8">
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Note</th>
            </tr>
            <?php while ($etudiant = mysqli_fetch_assoc($result_etudiants)): ?>
            <tr>
                <td><?= htmlspecialchars($etudiant["nom"]) ?></td>
                <td><?= htmlspecialchars($etudiant["prenom"]) ?></td>
                <td>
                    <input type="number" name="notes[<?= $etudiant['id'] ?>]" min="0" max="20" step="0.1" required>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

        <br>
        <button type="submit">Enregistrer les notes</button>
    </form>
    <p><a href="../utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
</body>
</html>
