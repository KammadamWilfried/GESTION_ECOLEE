<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['type_utilisateur'] !== 'Etudiant') {
    header('Location: ../login.php');
    exit;
}

$etudiant_id = $_SESSION['utilisateur_id'];

// Récupération des bulletins
$sql = "SELECT b.*, c.nom AS classe_nom 
        FROM bulletin b
        JOIN inscription i ON i.id_etudiant = b.id_etudiant AND i.id_classe = b.id_classe
        JOIN classe c ON c.id = b.id_classe
        WHERE b.id_etudiant = ?
        ORDER BY b.annee_scolaire DESC, b.sequence ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$bulletins = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Bulletin</title>
    <style>
        body {
            font-family: Arial;
            background: #f5f5f5;
            padding: 30px;
        }
        h2 {
            color: #003366;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #003366;
            color: white;
        }
        .btn-retour {
            margin-top: 20px;
            display: inline-block;
            text-decoration: none;
            background: #003366;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <h2>Mes Bulletins</h2>

    <?php if (!empty($bulletins)) : ?>
        <table>
            <thead>
                <tr>
                    <th>Année scolaire</th>
                    <th>Classe</th>
                    <th>Séquence</th>
                    <th>Moyenne</th>
                    <th>Rang</th>
                    <th>Code Bulletin</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bulletins as $b) : ?>
                    <tr>
                        <td><?= htmlspecialchars($b['annee_scolaire']) ?></td>
                        <td><?= htmlspecialchars($b['classe_nom']) ?></td>
                        <td>Séquence <?= htmlspecialchars($b['sequence']) ?></td>
                        <td><?= htmlspecialchars($b['moyenne']) ?></td>
                        <td><?= htmlspecialchars($b['rang']) ?></td>
                        <td><?= htmlspecialchars($b['code_bulletin']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else : ?>
        <p>Vous n’avez encore aucun bulletin enregistré.</p>
    <?php endif; ?>

    <a href="utilisateurs/dashboard.php" class="btn-retour">← Retour au tableau de bord</a>
</body>
</html>