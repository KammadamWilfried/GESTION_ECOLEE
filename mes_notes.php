<?php
session_start();
include("config/db.php");

// Vérifie si l'étudiant est connecté
if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Etudiant") {
    header("Location: login.php");
    exit;
}

$id_etudiant = $_SESSION["id_etudiant"];

// Requête SQL avec jointure sur la table matiere
$stmt = $conn->prepare("
    SELECT n.annee_scolaire,
           n.sequence AS sequence,
           n.type_examen,
           n.note,
           m.nom AS matiere
    FROM note n
    JOIN matiere m ON n.id_matiere = m.id
    WHERE n.id_etudiant = ?
    ORDER BY n.annee_scolaire DESC, sequence ASC, n.type_examen ASC
");
$stmt->bind_param("i", $id_etudiant);
$stmt->execute();
$result = $stmt->get_result();
$notes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mes Notes</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: #f9f9f9;
            padding: 40px;
        }
        h2 {
            text-align: center;
            color: #003366;
        }
        table {
            width: 90%;
            margin: 30px auto;
            background: white;
            border-collapse: collapse;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
        }
        th {
            background: #003366;
            color: white;
        }
        td {
            text-align: center;
        }
        .back {
            display: block;
            text-align: center;
            margin-top: 30px;
            text-decoration: none;
            color: #003366;
            font-weight: bold;
        }
        .back:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Mes Notes</h2>

<?php if (count($notes) > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Année Scolaire</th>
                <th>Séquence</th>
                <th>Type d'Examen</th>
                <th>Matière</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notes as $note): ?>
                <tr>
                    <td><?= htmlspecialchars($note['annee_scolaire'] ?? '') ?></td>
                    <td><?= htmlspecialchars($note['sequence'] ?? '') ?></td>
                    <td><?= htmlspecialchars($note['type_examen'] ?? '') ?></td>
                    <td><?= htmlspecialchars($note['matiere'] ?? '') ?></td>
                    <td><?= htmlspecialchars($note['note'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p style="text-align:center;">Aucune note enregistrée.</p>
<?php endif; ?>

<a href="utilisateurs/dashboard.php" class="back">← Retour au tableau de bord</a>

</body>
</html>