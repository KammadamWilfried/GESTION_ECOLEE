<?php
include("../config/db.php");

$classe_id = isset($_GET['classe_id']) ? (int) $_GET['classe_id'] : 0;
if ($classe_id <= 0) {
    header("Location: index.php");
    exit;
}

$message = "";

$matieres_res = mysqli_query($conn, "SELECT * FROM matiere");

$enseignants_res = mysqli_query($conn, "
    SELECT e.id, u.nom, u.prenom
    FROM enseignant e
    JOIN utilisateur u ON e.id = u.id
");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $matiere_id = (int) $_POST["matiere_id"];
    $enseignants = isset($_POST["enseignants"]) ? $_POST["enseignants"] : [];

    $check_res = mysqli_query($conn, "SELECT * FROM classe_matiere WHERE id_classe = $classe_id AND id_matiere = $matiere_id");
    if (mysqli_num_rows($check_res) === 0) {

        mysqli_query($conn, "INSERT INTO classe_matiere (id_classe, id_matiere) VALUES ($classe_id, $matiere_id)");

        mysqli_query($conn, "DELETE FROM matiere_enseignant WHERE matiere_id = $matiere_id");

        foreach ($enseignants as $ens_id) {
            $ens_id = (int) $ens_id;
            mysqli_query($conn, "INSERT INTO matiere_enseignant (matiere_id, enseignant_id) VALUES ($matiere_id, $ens_id)");
        }

        header("Location: matieres.php?classe_id=$classe_id");
        exit;
    } else {
        $message = "Cette matière est déjà associée à cette classe.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Associer une matière</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Associer une matière à la classe</h2>
    <?php if ($message): ?>
        <p style="color:red;"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="matiere_id">Matière :</label><br>
        <select name="matiere_id" required>
            <option value="">-- Choisir --</option>
            <?php while ($m = mysqli_fetch_assoc($matieres_res)) : ?>
                <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nom']) ?> (Coef. <?= $m['coefficient'] ?>)</option>
            <?php endwhile; ?>
        </select><br><br>

        <label>Enseignants :</label><br>
        <select name="enseignants[]" multiple required>
            <?php while ($e = mysqli_fetch_assoc($enseignants_res)) : ?>
                <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['prenom'] . " " . $e['nom']) ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <button type="submit">Associer</button>
        <a href="matieres.php?classe_id=<?= $classe_id ?>">Annuler</a>
    </form>
</body>
</html>
