<?php
session_start();
require 'config/db.php'; 
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';
require 'phpmailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


define('GEMINI_API_KEY', 'AIzaSyCqLl6G7nzuh9iPIvOrmPdCJqrFw0y0vnk');

$ai_response = '';
$user_question = '';
$success_message = '';


$etudiants = [];
$res = $conn->query("SELECT u.email, u.nom, u.prenom FROM utilisateur u INNER JOIN etudiant e ON u.id = e.id");
while ($row = $res->fetch_assoc()) {
    $etudiants[] = $row;
}

// Traitement IA
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_question'])) {
    $user_question = htmlspecialchars(trim($_POST['user_question']));

    if (!empty($user_question)) {
       $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . GEMINI_API_KEY;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Agis comme un enseignant. Génère un quiz clair, structuré et pertinent : " . $user_question]
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
                $quiz_content = $decoded['candidates'][0]['content']['parts'][0]['text'];
                $ai_response = nl2br(htmlspecialchars($quiz_content));

                
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'kammadamwil@gmail.com';
                    $mail->Password = 'pgqb actn kcen cedb'; 
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;
                    $mail->setFrom('kammadamwil@gmail.com', 'Administration');

                    foreach ($etudiants as $etu) {
                        $mail->addAddress($etu['email'], $etu['prenom'] . ' ' . $etu['nom']);
                        $mail->isHTML(true);
                        $mail->Subject = "Quiz IA - Cours récent";
                        $mail->Body = "
                            <h3>Bonjour {$etu['prenom']} {$etu['nom']},</h3>
                            <p>Voici un quiz généré automatiquement par votre enseignant :</p>
                            <div style='background:#f9f9f9;padding:15px;border:1px solid #ccc;border-radius:5px;color:#333'>
                                " . nl2br(htmlspecialchars($quiz_content)) . "
                            </div>
                            <br><p>Bonne chance à vous !</p>
                        ";
                        $mail->send();
                        $mail->clearAddresses();
                    }

                    $success_message = "Le quiz a été généré et envoyé à tous les étudiants.";
                } catch (Exception $e) {
                    $ai_response .= "<div class='error'>Erreur email : {$mail->ErrorInfo}</div>";
                }
            } else {
                $ai_response = "Erreur dans la réponse de l'IA : " . htmlspecialchars($response);
            }
        } else {
            $ai_response = "Erreur cURL : " . curl_error($ch);
        }
        curl_close($ch);
    } else {
        $ai_response = "Veuillez saisir une consigne de quiz.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Assistant IA - Génération de Quiz</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: #fff;
            border-radius: 8px;
            max-width: 900px;
            margin: 40px auto;
            padding: 30px;
            box-shadow: 0 0 14px rgba(0,0,0,0.1);
        }
        h2 {
            color: #4a148c;
            text-align: center;
            margin-bottom: 20px;
        }
        textarea {
            width: 100%;
            min-height: 120px;
            padding: 12px;
            margin-top: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button {
            background: #6a1b9a;
            color: #fff;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 15px;
        }
        .response, .success, .error {
            margin-top: 25px;
            padding: 18px;
            border-radius: 6px;
            font-size: 0.95rem;
        }
        .response {
            background: #f3f3f3;
            border-left: 5px solid #4a148c;
        }
        .success {
            background: #e6ffe6;
            border-left: 5px solid #2e7d32;
            color: #2e7d32;
        }
        .error {
            background: #ffebee;
            border-left: 5px solid #c62828;
            color: #c62828;
        }
    </style>
</head>
<body>
<div class="container">
    <h2>🧠 Générateur de Quiz IA + Envoi aux Étudiants</h2>
    <form method="POST">
        <label for="user_question">Entrez votre sujet ou consigne :</label>
        <textarea name="user_question" id="user_question" required><?= htmlspecialchars($user_question) ?></textarea>
        <button type="submit">Générer & Envoyer le Quiz</button>
    </form>

    <?php if (!empty($ai_response)): ?>
        <div class="response"><?= $ai_response ?></div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success"><?= $success_message ?></div>
    <?php endif; ?>
</div>
<p><a href="utilisateurs/dashboard.php">Retour au tableau de bord</a></p>
</body>
</html>