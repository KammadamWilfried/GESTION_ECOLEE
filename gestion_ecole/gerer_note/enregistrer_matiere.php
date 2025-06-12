<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["utilisateur_id"]) || $_SESSION["type_utilisateur"] !== "Enseignant") {
    header("Location: ../login.php");
    exit;
}

$classe_id = $_POST["classe_id"];
$matiere_id = $_POST["matiere_id"];
$notes = $_POST["notes"];
$sequence = 'Séquence 1'; 

foreach ($notes as $etudiant_id => $note) {
    $etudiant_id = (int)$etudiant_id;
    $note = (float)$note;

    $check = mysqli_query($conn, "SELECT id FROM note WHERE etudiant_id=$etudiant_id AND matiere_id=$matiere_id AND sequence='$sequence'");
    
    if (mysqli_num_rows($check) > 0) {

        mysqli_query($conn, "UPDATE note SET note=$note WHERE etudiant_id=$etudiant_id AND matiere_id=$matiere_id AND sequence='$sequence'");
    } else {

        mysqli_query($conn, "INSERT INTO note (etudiant_id, matiere_id, note, sequence) VALUES ($etudiant_id, $matiere_id, $note, '$sequence')");
    }
}

header("Location: index.php");
exit;
