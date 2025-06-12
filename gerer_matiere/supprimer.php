<?php
include("../config/db.php");

$classe_id = isset($_GET['classe_id']) ? (int) $_GET['classe_id'] : 0;
$matiere_id = isset($_GET['matiere_id']) ? (int) $_GET['matiere_id'] : 0;

if ($classe_id <= 0 || $matiere_id <= 0) {
    header("Location: index.php");
    exit;
}

$check = mysqli_query($conn, "SELECT * FROM classe_matiere WHERE id_classe = $classe_id AND id_matiere = $matiere_id");
if (mysqli_num_rows($check) === 0) {
    header("Location: matieres.php?classe_id=$classe_id");
    exit;
}

mysqli_query($conn, "DELETE FROM classe_matiere WHERE id_classe = $classe_id AND id_matiere = $matiere_id");


header("Location: matieres.php?classe_id=$classe_id");
exit;
?>
