<?php
session_start();


if (!isset($_SESSION['type_utilisateur']) || $_SESSION['type_utilisateur'] !== 'Etudiant') {
    header("Location: utilisateurs/login.php");
    exit();
}


define('GEMINI_API_KEY', 'AIzaSyCqLl6G7nzuh9iPIvOrmPdCJqrFw0y0vnk');

$ai_response = '';
$user_question = '';


if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['user_question'])) {
    $user_question = htmlspecialchars(trim($_POST['user_question']));

    
    $mots_cles = ['math', 'algèbre', 'géométrie', 'probabilité', 'physique', 'chimie', 'biologie', 'svt', 'science', 'histoire', 'guerre', 'philo', 'philosophie', 'grammaire', 'conjugaison', 'orthographe', 'anglais', 'français', 'économie', 'loi', 'ohm', 'courant', 'voltage', 'tension', 'révolution', 'empire', 'analyse', 'statistique', 'informatique', 'science', 'programmation'];

    
    $est_scolaire = false;
    foreach ($mots_cles as $mot) {
        if (stripos($user_question, $mot) !== false) {
            $est_scolaire = true;
            break;
        }
    }

    
    if ($est_scolaire) {
        $api_endpoint = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . GEMINI_API_KEY;

        $data = [
            'contents' => [
                [
                    'parts' => [
                        ['text' => "Tu es un professeur pédagogue. Réponds simplement à cette question scolaire : " . $user_question]
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
                $ai_response = nl2br(htmlspecialchars($decoded['candidates'][0]['content']['parts'][0]['text']));
            } else {
                $ai_response = "<div class='error'>L’IA n’a pas pu répondre. Réessaie plus tard.</div>";
            }
        } else {
            $ai_response = "<div class='error'>Erreur cURL : " . curl_error($ch) . "</div>";
        }

        curl_close($ch);
    } else {
        // Si la question n'est pas scolaire
        $ai_response = "<div class='error'>❌ Désolé, je ne réponds qu’aux questions liées à l’école. Essaie :<br>- Qu’est-ce que la loi d’Ohm ?<br>- Quelles sont les causes de la Première Guerre mondiale ?<br>- Comment faire une équation ?</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Assistant IA scolaire</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f4f6f8;
            padding: 30px;
        }
        .container {
            max-width: 800px;
            margin: auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #1565c0;
        }
        textarea {
            width: 100%;
            height: 120px;
            padding: 10px;
            font-size: 1rem;
            border: 1px solid #ccc;
            border-radius: 6px;
            margin-bottom: 15px;
        }
        button {
            background-color: #1976d2;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            display: block;
            width: 100%;
        }
        .response {
            margin-top: 20px;
            background: #e3f2fd;
            border-left: 5px solid #2196f3;
            padding: 15px;
            border-radius: 6px;
            color: #333;
        }
        .error {
            margin-top: 20px;
            background: #ffebee;
            border-left: 5px solid #e53935;
            padding: 15px;
            border-radius: 6px;
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pose ta question scolaire à l’IA</h2>
        <form method="POST">
            <textarea name="user_question" placeholder="Ex : Comment résoudre une équation ? ou Quelles sont les causes de la Seconde Guerre mondiale ?" required><?= htmlspecialchars($user_question) ?></textarea>
            <button type="submit">Envoyer à l'IA</button>
        </form>

        <?php if (!empty($ai_response)): ?>
            <div class="response"><?= $ai_response ?></div>
        <?php endif; ?>
    </div>
    <p style="text-align:center;"><a href="utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
</body>
</html>