<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] != "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("ID invalide.");
}

$sql = "DELETE FROM utilisateur WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    header("Location: index.php");
    exit;
} else {
    echo "Erreur lors de la suppression : " . mysqli_error($conn);
}
