<?php
include("../config/db.php");

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM matiere WHERE id = $id");
}

header("Location: liste_matieres.php");
exit;
?>
