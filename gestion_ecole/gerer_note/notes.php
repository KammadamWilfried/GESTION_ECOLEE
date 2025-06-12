<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["utilisateur_id"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$sql = "SELECT e.id AS etudiant_id, u.nom, u.prenom 
        FROM etudiant e 
        JOIN utilisateur u ON u.id = e.id 
        ORDER BY u.nom";
$result = $conn->query($sql);
$etudiants = $result->fetch_all(MYSQLI_ASSOC);

$notes = [];
$etudiant_nom = "";
$sequence_filter = isset($_GET['sequence']) ? intval($_GET['sequence']) : null;
$rang = null;

if (isset($_GET['etudiant_id'])) {
    $etudiant_id = (int)$_GET['etudiant_id'];

    $sql_nom = "SELECT nom, prenom FROM utilisateur WHERE id = ?";
    $stmt_nom = $conn->prepare($sql_nom);
    $stmt_nom->bind_param("i", $etudiant_id);
    $stmt_nom->execute();
    $infos = $stmt_nom->get_result()->fetch_assoc();
    $etudiant_nom = $infos['nom'] . ' ' . $infos['prenom'];

    $sql_notes = "SELECT m.nom AS matiere, m.coefficient, n.note, n.sequence, n.annee_scolaire
                  FROM note n
                  JOIN matiere m ON m.id = n.id_matiere
                  WHERE n.id_etudiant = ?";
    if ($sequence_filter !== null) {
        $sql_notes .= " AND n.sequence = ?";
    }
    $sql_notes .= " ORDER BY n.annee_scolaire DESC, n.sequence ASC";

    $stmt = $conn->prepare($sql_notes);
    if ($sequence_filter !== null) {
        $stmt->bind_param("ii", $etudiant_id, $sequence_filter);
    } else {
        $stmt->bind_param("i", $etudiant_id);
    }
    $stmt->execute();
    $notes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Calcul du rang si une séquence est sélectionnée
    if ($sequence_filter !== null) {
        $sql_rang = "SELECT e.id AS etudiant_id, 
                            SUM(n.note * m.coefficient) / SUM(m.coefficient) AS moyenne
                     FROM note n
                     JOIN matiere m ON m.id = n.id_matiere
                     JOIN etudiant e ON e.id = n.id_etudiant
                     WHERE n.sequence = ?
                     GROUP BY e.id
                     ORDER BY moyenne DESC";

        $stmt_rang = $conn->prepare($sql_rang);
        $stmt_rang->bind_param("i", $sequence_filter);
        $stmt_rang->execute();
        $resultats = $stmt_rang->get_result()->fetch_all(MYSQLI_ASSOC);

        $position = 1;
        foreach ($resultats as $r) {
            if ($r['etudiant_id'] == $etudiant_id) {
                $rang = $position;
                break;
            }
            $position++;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notes des étudiants</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f9fc;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            max-width: 1100px;
            margin: auto;
            padding: 40px 20px;
        }

        h2, h3 {
            color: #0b3d91;
        }

        ul.student-list {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            padding: 0;
            list-style: none;
            margin-bottom: 30px;
        }

        ul.student-list li {
            background: #ffffff;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        ul.student-list li:hover {
            background: #e6f0ff;
            transform: translateY(-2px);
        }

        ul.student-list a {
            text-decoration: none;
            color: #0b3d91;
            font-weight: 500;
        }

        form.select-sequence {
            margin-bottom: 20px;
        }

        select {
            padding: 6px 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 15px;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #eee;
        }

        th {
            background: #0b3d91;
            color: #fff;
            text-transform: uppercase;
        }

        tr:hover td {
            background-color: #f1f9ff;
        }

        .btn {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background: #0b3d91;
            color: white;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #062b6d;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Liste des étudiants</h2>
        <ul class="student-list">
            <?php foreach ($etudiants as $e): ?>
                <li>
                    <a href="?etudiant_id=<?= $e['etudiant_id'] ?>">
                        <?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <?php if (!empty($etudiant_nom)): ?>
            <h3>Notes de <?= htmlspecialchars($etudiant_nom) ?></h3>

            <form class="select-sequence" method="get" action="">
                <input type="hidden" name="etudiant_id" value="<?= $_GET['etudiant_id'] ?>">
                <label for="sequence">Séquence :</label>
                <select name="sequence" id="sequence" onchange="this.form.submit()">
                    <option value="">-- Toutes --</option>
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <option value="<?= $i ?>" <?= ($sequence_filter === $i) ? 'selected' : '' ?>>Séquence <?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </form>

            <?php if (!empty($notes)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Année scolaire</th>
                            <th>Séquence</th>
                            <th>Matière</th>
                            <th>Note</th>
                            <th>Coefficient</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $total_note = 0;
                        $total_coef = 0;
                        foreach ($notes as $n):
                            $coef = $n['coefficient'];
                            $note = $n['note'];
                            $total_note += $note * $coef;
                            $total_coef += $coef;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($n['annee_scolaire']) ?></td>
                            <td>Séquence <?= htmlspecialchars($n['sequence']) ?></td>
                            <td><?= htmlspecialchars($n['matiere']) ?></td>
                            <td><?= htmlspecialchars($note) ?></td>
                            <td><?= htmlspecialchars($coef) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4"><strong>Moyenne pondérée</strong></td>
                            <td><strong><?= $total_coef > 0 ? round($total_note / $total_coef, 2) : 'N/A' ?></strong></td>
                        </tr>
                    </tfoot>
                </table>

                <?php if ($rang !== null): ?>
                    <p><strong>Rang de l’étudiant dans la séquence <?= $sequence_filter ?> :</strong> <?= $rang ?> / <?= count($resultats) ?></p>
                <?php endif; ?>

                <form method="post" action="generer_bulletin.php">
                    <input type="hidden" name="etudiant_id" value="<?= $_GET['etudiant_id'] ?>">
                    <?php if ($sequence_filter !== null): ?>
                        <input type="hidden" name="sequence" value="<?= $sequence_filter ?>">
                    <?php endif; ?>
                    <button type="submit" class="btn">Générer et envoyer le bulletin</button>
                </form>
            <?php else: ?>
                <p>Aucune note trouvée pour cette séquence.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <p><a href="../utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
</body>
</html>