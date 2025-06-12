<?php
include("../config/db.php");

$classe_id = isset($_GET['classe_id']) ? (int) $_GET['classe_id'] : 0;
$matiere_id = isset($_GET['matiere_id']) ? (int) $_GET['matiere_id'] : 0;

if ($classe_id <= 0 || $matiere_id <= 0) {
    header("Location: index.php");
    exit;
}

$verif = mysqli_query($conn, "SELECT * FROM classe_matiere WHERE id_classe = $classe_id AND id_matiere = $matiere_id");
if (mysqli_num_rows($verif) === 0) {
    header("Location: matieres.php?classe_id=$classe_id");
    exit;
}

$enseignants_actuels = [];
$res_ens = mysqli_query($conn, "SELECT enseignant_id FROM matiere_enseignant WHERE matiere_id = $matiere_id");
while ($row = mysqli_fetch_assoc($res_ens)) {
    $enseignants_actuels[] = $row["enseignant_id"];
}

$enseignants_res = mysqli_query($conn, "
    SELECT e.id, u.nom, u.prenom
    FROM enseignant e
    JOIN utilisateur u ON e.id = u.id
");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $enseignants = isset($_POST["enseignants"]) ? $_POST["enseignants"] : [];

    mysqli_query($conn, "DELETE FROM matiere_enseignant WHERE matiere_id = $matiere_id");

    foreach ($enseignants as $ens_id) {
        $ens_id = (int)$ens_id;
        mysqli_query($conn, "INSERT INTO matiere_enseignant (matiere_id, enseignant_id) VALUES ($matiere_id, $ens_id)");
    }

    header("Location: matieres.php?classe_id=$classe_id");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modifier les enseignants</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Modifier les enseignants pour la matière</h2>
    <form method="POST">
        <label>Enseignants :</label><br>
        <select name="enseignants[]" multiple required>
            <?php while ($e = mysqli_fetch_assoc($enseignants_res)) : ?>
                <option value="<?= $e['id'] ?>" <?= in_array($e['id'], $enseignants_actuels) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($e['prenom'] . " " . $e['nom']) ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <button type="submit">Mettre à jour</button>
        <a href="matieres.php?classe_id=<?= $classe_id ?>">Annuler</a>
    </form>
</body>
</html>
