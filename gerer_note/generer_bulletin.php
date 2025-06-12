<?php
session_start();
require_once '../config/db.php';
require '../phpmailer/src/PHPMailer.php';
require '../phpmailer/src/SMTP.php';
require '../phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['utilisateur_id']) || $_SESSION['type_utilisateur'] !== 'Administrateur') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_POST['etudiant_id'])) {
    echo "Erreur : identifiant de l'étudiant manquant.";
    exit;
}

$etudiant_id = intval($_POST['etudiant_id']);
$sequence = isset($_POST['sequence']) ? intval($_POST['sequence']) : null;

$sql_etudiant = "SELECT u.nom, u.prenom, u.email, i.id_classe
                 FROM utilisateur u 
                 JOIN etudiant e ON u.id = e.id 
                 JOIN inscription i ON i.id_etudiant = e.id
                 WHERE e.id = ?";
$stmt = $conn->prepare($sql_etudiant);
$stmt->bind_param("i", $etudiant_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$nom = $result['nom'];
$prenom = $result['prenom'];
$email = $result['email'];
$id_classe = $result['id_classe'];

$sql_notes = "SELECT m.nom AS matiere, m.coefficient, n.note, n.sequence, n.annee_scolaire
              FROM note n
              JOIN matiere m ON m.id = n.id_matiere
              WHERE n.id_etudiant = ?";
if ($sequence !== null) {
    $sql_notes .= " AND n.sequence = ?";
    $stmt_notes = $conn->prepare($sql_notes);
    $stmt_notes->bind_param("ii", $etudiant_id, $sequence);
} else {
    $stmt_notes = $conn->prepare($sql_notes);
    $stmt_notes->bind_param("i", $etudiant_id);
}
$stmt_notes->execute();
$notes = $stmt_notes->get_result()->fetch_all(MYSQLI_ASSOC);

$total_note = 0;
$total_coef = 0;
foreach ($notes as $n) {
    $total_note += $n['note'] * $n['coefficient'];
    $total_coef += $n['coefficient'];
}
$moyenne = $total_coef > 0 ? round($total_note / $total_coef, 2) : "N/A";

$rang = null;
$total_etudiants = 0;
if ($sequence !== null) {
    $sql_moyennes = "SELECT n.id_etudiant, SUM(n.note * m.coefficient)/SUM(m.coefficient) AS moyenne
                     FROM note n
                     JOIN matiere m ON m.id = n.id_matiere
                     WHERE n.sequence = ?
                     GROUP BY n.id_etudiant
                     ORDER BY moyenne DESC";
    $stmt_rang = $conn->prepare($sql_moyennes);
    $stmt_rang->bind_param("i", $sequence);
    $stmt_rang->execute();
    $resultats = $stmt_rang->get_result()->fetch_all(MYSQLI_ASSOC);
    $total_etudiants = count($resultats);

    foreach ($resultats as $index => $ligne) {
        if ($ligne['id_etudiant'] == $etudiant_id) {
            $rang = $index + 1;
            break;
        }
    }
}


$ai_message = "Aucun message personnalisé.";
if ($moyenne !== "N/A") {
    $prompt = "Agis comme un professeur bienveillant. Donne un conseil personnalisé à l’élève $prenom $nom qui a obtenu une moyenne de $moyenne à la séquence $sequence. Sois encourageant et motivant.";

    $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=AIzaSyCqLl6G7nzuh9iPIvOrmPdCJqrFw0y0vnk';

    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ]
    ];

    $ch = curl_init($api_endpoint);
    curl_setopt_array($ch, [
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($ch);

    if (!curl_errno($ch)) {
        $decoded = json_decode($response, true);
        if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
            $ai_message = nl2br(htmlspecialchars($decoded['candidates'][0]['content']['parts'][0]['text']));
        }
    }

    curl_close($ch);
}

// Génération du code bulletin
$code_bulletin = strtoupper(uniqid("BULLETIN_"));
if ($moyenne !== "N/A" && $sequence !== null && $rang !== null) {
    $annee_scolaire = $notes[0]['annee_scolaire'] ?? date('Y');
    $stmt_bulletin = $conn->prepare("INSERT INTO bulletin (id_etudiant, id_classe, annee_scolaire, sequence, moyenne, rang, code_bulletin) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt_bulletin->bind_param("iisidss", $etudiant_id, $id_classe, $annee_scolaire, $sequence, $moyenne, $rang, $code_bulletin);
    $stmt_bulletin->execute();
}


$contenu = "<h2>Bulletin de notes de $prenom $nom</h2>";
if ($sequence !== null) {
    $contenu .= "<p><strong>Séquence :</strong> $sequence</p>";
}
if ($rang !== null) {
    $contenu .= "<p><strong>Rang :</strong> $rang / $total_etudiants</p>";
}
$contenu .= "<table border='1' cellspacing='0' cellpadding='8'>
                <thead>
                    <tr style='background-color:#f0f0f0;'>
                        <th>Année scolaire</th>
                        <th>Séquence</th>
                        <th>Matière</th>
                        <th>Note</th>
                        <th>Coefficient</th>
                    </tr>
                </thead><tbody>";
foreach ($notes as $n) {
    $contenu .= "<tr>
                    <td>{$n['annee_scolaire']}</td>
                    <td>Séquence {$n['sequence']}</td>
                    <td>{$n['matiere']}</td>
                    <td>{$n['note']}</td>
                    <td>{$n['coefficient']}</td>
                </tr>";
}
$contenu .= "</tbody>
             <tfoot>
                 <tr>
                    <td colspan='4'><strong>Moyenne Générale</strong></td>
                    <td><strong>$moyenne</strong></td>
                 </tr>
             </tfoot>
             </table>";


$contenu .= "<h3 style='color:#1565c0;'>💡 Conseil personnalisé de ton professeur virtuel :</h3>
             <p style='font-style: italic; color: #444;'>$ai_message</p>";


$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'kammadamwil@gmail.com';
    $mail->Password = 'pgqb actn kcen cedb';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('kammadamwil@gmail.com', 'Administration Scolaire');
    $mail->addAddress($email, "$prenom $nom");

    $mail->isHTML(true);
    $mail->Subject = 'Bulletin de notes';
    $mail->Body = $contenu;

    $mail->send();
    echo "<p>Bulletin envoyé avec succès à <strong>$email</strong>.</p>";
    echo "<a href='notes.php'>← Retour</a>";
} catch (Exception $e) {
    echo "Erreur lors de l'envoi du mail : {$mail->ErrorInfo}";
}
?>