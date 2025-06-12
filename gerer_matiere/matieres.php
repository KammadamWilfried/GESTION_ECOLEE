<?php
include("../config/db.php");

$classe_id = isset($_GET["classe_id"]) ? (int) $_GET["classe_id"] : 0;
if ($classe_id <= 0) {
    header("Location: index.php");
    exit;
}

$classe_res = mysqli_query($conn, "SELECT nom FROM classe WHERE id = $classe_id");
$classe = mysqli_fetch_assoc($classe_res);

$matiere_query = "
    SELECT m.id AS matiere_id, m.nom AS matiere_nom, m.coefficient
    FROM classe_matiere cm
    JOIN matiere m ON cm.id_matiere = m.id
    WHERE cm.id_classe = $classe_id
";
$matieres_res = mysqli_query($conn, $matiere_query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Matières de la classe <?= htmlspecialchars($classe['nom']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Matières de la classe : <?= htmlspecialchars($classe['nom']) ?></h2>

    <a href="ajouter.php?classe_id=<?= $classe_id ?>" class="btn">Associer une matière</a>
    <a href="index.php" class="btn">Retour</a>

    <table>
        <thead>
            <tr>
                <th>Matière</th>
                <th>Coefficient</th>
                <th>Enseignants</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($m = mysqli_fetch_assoc($matieres_res)) : ?>
                <tr>
                    <td><?= htmlspecialchars($m["matiere_nom"]) ?></td>
                    <td><?= $m["coefficient"] ?></td>
                    <td>
                        <?php
                        $mid = $m["matiere_id"];
                        $ens_res = mysqli_query($conn, "
                            SELECT u.nom, u.prenom
                            FROM matiere_enseignant me
                            JOIN enseignant e ON me.enseignant_id = e.id
                            JOIN utilisateur u ON e.id = u.id
                            WHERE me.matiere_id = $mid
                        ");
                        $enseignants = [];
                        while ($ens = mysqli_fetch_assoc($ens_res)) {
                            $enseignants[] = $ens["prenom"] . " " . $ens["nom"];
                        }
                        echo $enseignants ? implode(", ", $enseignants) : "<i>Aucun</i>";
                        ?>
                    </td>
                    <td>
                        <a href="modifier.php?classe_id=<?= $classe_id ?>&matiere_id=<?= $m['matiere_id'] ?>"class="btn-edit">Modifier</a>
                        <a href="supprimer.php?classe_id=<?= $classe_id ?>&matiere_id=<?= $m['matiere_id'] ?>"class="btn-delete" onclick="return confirm('Confirmer la suppression ?')">Supprimer</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
