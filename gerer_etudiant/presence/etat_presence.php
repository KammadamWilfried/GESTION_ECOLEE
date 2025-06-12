<?php
session_start();
include("../../config/db.php");

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Etudiant") {
   $_SESSION["utilisateur_id"] = $row['id'];
    header("Location: ../../login.php");
    exit;
}

$id_etudiant = $_SESSION["utilisateur_id"];

$stmt = $conn->prepare("
    SELECT p.date, p.etat, m.nom AS matiere
    FROM presence p
    JOIN matiere m ON p.id_matiere = m.id
    WHERE p.id_etudiant = ?
    ORDER BY p.date DESC
");
$stmt->bind_param("i", $id_etudiant);
$stmt->execute();
$result = $stmt->get_result();
$presences = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes états de présence</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Mes états de présence</h2>

    <?php if (count($presences) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Matière</th>
                    <th>État</th>
                    <th>Remarque</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($presences as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['date']) ?></td>
                        <td><?= htmlspecialchars($p['matiere']) ?></td>
                        <td><?= htmlspecialchars($p['etat']) ?></td>
                        <td><?= htmlspecialchars($p['remarque'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Aucune présence enregistrée.</p>
    <?php endif; ?>

    <p><a href="../../utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
</body>
</html>