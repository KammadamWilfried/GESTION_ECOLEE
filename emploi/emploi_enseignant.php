<?php
session_start();
require_once '../config/db.php';

// Sécurité : vérifier que l'utilisateur est bien un enseignant
if (!isset($_SESSION['utilisateur_id']) || $_SESSION['type_utilisateur'] !== 'Enseignant') {
    header('Location: ../login.php');
    exit;
}

$enseignant_id = $_SESSION['utilisateur_id'];

// Récupérer toutes les matières de cet enseignant
$sql = "
    SELECT e.date, e.heure_debut, e.heure_fin, m.nom AS matiere, c.nom AS classe
    FROM emploi_temp e
    JOIN matiere m ON m.id = e.id_matiere
    JOIN classe c ON c.id = e.id_classe
    JOIN matiere_enseignant me ON me.matiere_id = m.id
    WHERE me.enseignant_id = ?
    ORDER BY e.date, e.heure_debut
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $enseignant_id);
$stmt->execute();
$emplois = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Emploi du temps - Enseignant</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f1f4f9;
            padding: 30px;
        }
        h2 {
            color: #003366;
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            background: white;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
        }
        th {
            background: #003366;
            color: white;
        }
        tr:hover td {
            background: #f0f8ff;
        }
        .no-data {
            text-align: center;
            margin-top: 40px;
            color: #555;
        }
        .btn-retour {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #003366;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>Mon Emploi du Temps (Toutes Matières)</h2>

<?php if (!empty($emplois)) : ?>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Matière</th>
                <th>Classe</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($emplois as $e) : ?>
                <tr>
                    <td><?= htmlspecialchars($e['date']) ?></td>
                    <td><?= htmlspecialchars($e['heure_debut']) ?></td>
                    <td><?= htmlspecialchars($e['heure_fin']) ?></td>
                    <td><?= htmlspecialchars($e['matiere']) ?></td>
                    <td><?= htmlspecialchars($e['classe']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <p class="no-data">Aucun emploi du temps disponible pour vos matières.</p>
<?php endif; ?>

<a class="btn-retour" href="../utilisateurs/dashboard.php">← Retour au tableau de bord</a>

</body>
</html>