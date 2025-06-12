<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "DELETE FROM utilisateur WHERE id = $id";

    if (mysqli_query($conn, $sql)) {
        header("Location: liste_utilisateurs.php?msg=utilisateur_supprime");
        exit;
    } else {
        echo "Erreur : " . mysqli_error($conn);
    }
} else {
    header("Location: liste_utilisateurs.php");
    exit;
}
