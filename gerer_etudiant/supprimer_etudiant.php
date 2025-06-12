<?php
session_start();
include("../config/db.php");

if (!isset($_GET['id'])) {
    header('Location: gestion_etudiants.php');
    exit;
}

$id = (int)$_GET['id'];


$conn->begin_transaction();

try {

    $stmt = $conn->prepare("DELETE FROM inscription WHERE id_etudiant = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM paiement WHERE id_etudiant = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM etudiant WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt = $conn->prepare("DELETE FROM utilisateur WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $conn->commit();
    header("Location: gestion_etudiants.php?message=suppression_reussie");
} catch (Exception $e) {
    $conn->rollback();
    die("Erreur lors de la suppression : " . $e->getMessage());
}
