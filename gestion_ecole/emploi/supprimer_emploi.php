<?php
session_start();
include("../config/db.php");

// Vérifie si un ID est passé
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_emploi = (int)$_GET['id'];

    // Récupérer la classe avant suppression pour rediriger correctement
    $stmt = $conn->prepare("SELECT id_classe FROM emploi_temp WHERE id = ?");
    $stmt->bind_param("i", $id_emploi);
    $stmt->execute();
    $result = $stmt->get_result();
    $emploi = $result->fetch_assoc();

    if ($emploi) {
        $classe_id = $emploi['id_classe'];

        // Suppression de l'emploi du temps
        $stmtDelete = $conn->prepare("DELETE FROM emploi_temp WHERE id = ?");
        $stmtDelete->bind_param("i", $id_emploi);
        if ($stmtDelete->execute()) {
            $_SESSION['message'] = "Emploi du temps supprimé avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la suppression.";
        }

        header("Location: index.php?classe_id=$classe_id");
        exit;
    } else {
        $_SESSION['error'] = "Emploi non trouvé.";
        header("Location: index.php");
        exit;
    }
} else {
    $_SESSION['error'] = "ID invalide.";
    header("Location: index.php");
    exit;
}
?>