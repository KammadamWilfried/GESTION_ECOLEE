<?php
session_start();
include("../config/db.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $id_matiere = $_POST['id_matiere'];

    $stmt = $conn->prepare("UPDATE emploi_temp SET date = ?, heure_debut = ?, heure_fin = ?, id_matiere = ? WHERE id = ?");
    $stmt->bind_param("ssiii", $date, $heure_debut, $heure_fin, $id_matiere, $id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Emploi du temps modifié avec succès.";
        header("Location: index.php");
        exit();
    } else {
        $_SESSION['error'] = "Erreur lors de la modification de l'emploi du temps : " . $stmt->error;
    }
}


$sql = "SELECT * FROM emploi_temp WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$emploi = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Modifier Emploi du Temps</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2>Modifier l'Emploi du Temps</h2>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <form action="" method="post">
        <label>Date :</label>
        <input type="date" name="date" value="<?= htmlspecialchars($emploi['date']) ?>" required>
        <label>Heure de début :</label>
        <input type="time" name="heure_debut" value="<?= htmlspecialchars($emploi['heure_debut']) ?>" required>
        <label>Heure de fin :</label>
        <input type="time" name="heure_fin" value="<?= htmlspecialchars($emploi['heure_fin']) ?>" required>
        <label>Matière :</label>
        <select name="id_matiere" required>

            <?php
            $matieres = mysqli_query($conn, "SELECT * FROM matiere");
            while ($matiere = mysqli_fetch_assoc($matieres)):
            ?>
                <option value="<?= $matiere['id'] ?>" <?= $matiere['id'] == $emploi['id_matiere'] ? 'selected' : '' ?>><?= htmlspecialchars($matiere['nom']) ?></option>
            <?php endwhile; ?>
        </select>
        <input type="submit" value="Modifier">
    </form>
        <p><a href="../utilisateurs/dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>