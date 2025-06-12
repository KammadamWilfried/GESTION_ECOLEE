<?php
session_start();
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include("../config/db.php");

if (!isset($_GET['classe_id'])) {
    $_SESSION['error'] = "Classe non spécifiée.";
    header("Location: index.php");
    exit;
}

$classe_id = (int)$_GET['classe_id'];

// Récupérer les étudiants avec leur email
$etudiants = [];
$stmt = $conn->prepare("
    SELECT u.email, u.nom, u.prenom
    FROM utilisateur u
    JOIN etudiant e ON u.id = e.id
    JOIN inscription i ON i.id_etudiant = e.id
    WHERE i.id_classe = ?
");
$stmt->bind_param("i", $classe_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $etudiants[] = $row;
}

// Récupérer l'emploi du temps
$emplois = [];
$stmt = $conn->prepare("
    SELECT e.*, m.nom AS matiere_nom 
    FROM emploi_temp e 
    JOIN matiere m ON e.id_matiere = m.id 
    WHERE e.id_classe = ? 
    ORDER BY e.date, e.heure_debut
");
$stmt->bind_param("i", $classe_id);
$stmt->execute();
$result = $stmt->get_result();
$emplois = $result->fetch_all(MYSQLI_ASSOC);

// Générer le tableau HTML
$contenu = "<h3>Emploi du Temps</h3><table border='1' cellpadding='8' cellspacing='0'>
<thead><tr><th>Date</th><th>Heure de début</th><th>Heure de fin</th><th>Matière</th></tr></thead><tbody>";
foreach ($emplois as $emploi) {
    $contenu .= "<tr>
        <td>" . htmlspecialchars($emploi['date']) . "</td>
        <td>" . htmlspecialchars($emploi['heure_debut']) . "</td>
        <td>" . htmlspecialchars($emploi['heure_fin']) . "</td>
        <td>" . htmlspecialchars($emploi['matiere_nom']) . "</td>
    </tr>";
}
$contenu .= "</tbody></table>";

// Configuration PHPMailer
$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com'; // SMTP de ton fournisseur
    $mail->SMTPAuth   = true;
    $mail->Username   = 'kammadamwil@gmail.com'; // 💡 ton email
    $mail->Password   = 'pgqb actn kcen cedb'; // 💡 mot de passe application
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;
    $mail->setFrom('kammadamwil@gmail.com', 'Administration');

    // Envoi à chaque étudiant
    foreach ($etudiants as $etu) {
        $mail->addAddress($etu['email'], $etu['prenom'] . ' ' . $etu['nom']);
        $mail->isHTML(true);
        $mail->Subject = "Emploi du Temps - Classe $classe_id";
        $mail->Body    = "Bonjour " . $etu['prenom'] . ",<br><br>Voici l'emploi du temps de ta classe :<br>" . $contenu;
        $mail->AltBody = "Consulte ton emploi du temps via ton compte.";
        $mail->send();
        $mail->clearAddresses(); // important pour ne pas empiler les destinataires
    }

    $_SESSION['message'] = "Emails envoyés à tous les étudiants.";
} catch (Exception $e) {
    $_SESSION['error'] = "Erreur lors de l'envoi : " . $mail->ErrorInfo;
}

header("Location: index.php?classe_id=$classe_id");
exit;