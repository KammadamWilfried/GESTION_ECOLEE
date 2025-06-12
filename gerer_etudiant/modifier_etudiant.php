<?php
session_start();
include("../config/db.php");

if (!isset($_GET['id'])) {
    header('Location: gestion_etudiants.php');
    exit;
}

$id = (int)$_GET['id'];
$sql = "
    SELECT u.*, e.matricule 
    FROM utilisateur u
    JOIN etudiant e ON u.id = e.id
    WHERE u.id = ?
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$etudiant = $result->fetch_assoc();

if (!$etudiant) {
    die("Étudiant non trouvé");
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $date_naissance = $_POST['date_naissance'];
    $genre = $_POST['genre'];
    $adresse = trim($_POST['adresse']);
    $telephone = trim($_POST['telephone']);
    $email = trim($_POST['email']);
    
    if (!$nom || !$prenom || !$email || !$date_naissance || !$genre) {
        $error = "Veuillez remplir tous les champs obligatoires.";
    } else {

        $checkEmail = $conn->prepare("SELECT id FROM utilisateur WHERE email = ? AND id != ?");
        $checkEmail->bind_param("si", $email, $id);
        $checkEmail->execute();
        $checkEmail->store_result();
        if ($checkEmail->num_rows > 0) {
            $error = "Cet email est déjà utilisé.";
        } else {

            $update = $conn->prepare("UPDATE utilisateur SET nom=?, prenom=?, date_naissance=?, genre=?, adresse=?, telephone=?, email=? WHERE id=?");
            $update->bind_param("sssssssi", $nom, $prenom, $date_naissance, $genre, $adresse, $telephone, $email, $id);
            if ($update->execute()) {
                $success = "Étudiant mis à jour avec succès.";
                header("Location: modifier_etudiant.php?id=$id&success=1");
                exit;
            } else {
                $error = "Erreur lors de la mise à jour.";
            }
        }
    }
}

if (isset($_GET['success'])) {
    $success = "Étudiant mis à jour avec succès.";
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Modifier Étudiant</title>
    <style>
        /* styles.css */

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: #f5f7fa;
    color: #333;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 40px 20px;
}

.container {
    background: white;
    padding: 30px 40px;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    max-width: 500px;
    width: 100%;
    display: grid;
    grid-template-columns: 1fr 1fr;
    grid-gap: 20px 30px;
}

.container h2 {
    grid-column: 1 / -1;
    text-align: center;
    margin-bottom: 25px;
    font-weight: 700;
    color: #222;
}

label {
    display: block;
    font-weight: 600;
    margin-bottom: 6px;
    color: #555;
}

input[type="text"],
input[type="email"],
input[type="date"],
select {
    width: 100%;
    padding: 8px 12px;
    border: 1.8px solid #ddd;
    border-radius: 6px;
    font-size: 15px;
    transition: border-color 0.3s ease;
}

input[type="text"]:focus,
input[type="email"]:focus,
input[type="date"]:focus,
select:focus {
    outline: none;
    border-color: #4a90e2;
    box-shadow: 0 0 5px rgba(74,144,226,0.5);
}

input[type="submit"] {
    grid-column: 1 / -1;
    background-color: #4a90e2;
    color: white;
    border: none;
    padding: 12px 0;
    font-size: 17px;
    font-weight: 700;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

input[type="submit"]:hover {
    background-color: #357ABD;
}

a {
    grid-column: 1 / -1;
    text-align: center;
    margin-top: 15px;
    display: block;
    color: #4a90e2;
    text-decoration: none;
    font-weight: 600;
}

a:hover {
    text-decoration: underline;
}

    </style>
</head>
<body>
    <h2>Modifier Étudiant (Matricule: <?= htmlspecialchars($etudiant['matricule']) ?>)</h2>

    <?php if ($error): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>Nom:</label>
        <input type="text" name="nom" value="<?= htmlspecialchars($etudiant['nom']) ?>" required>

        <label>Prénom:</label>
        <input type="text" name="prenom" value="<?= htmlspecialchars($etudiant['prenom']) ?>" required>

        <label>Date de naissance:</label>
        <input type="date" name="date_naissance" value="<?= htmlspecialchars($etudiant['date_naissance']) ?>" required>

        <label>Genre:</label>
        <select name="genre" required>
            <option value="M" <?= $etudiant['genre'] === 'M' ? 'selected' : '' ?>>Masculin</option>
            <option value="F" <?= $etudiant['genre'] === 'F' ? 'selected' : '' ?>>Féminin</option>
        </select>

        <label>Adresse:</label>
        <textarea name="adresse"><?= htmlspecialchars($etudiant['adresse']) ?></textarea>

        <label>Téléphone:</label>
        <input type="text" name="telephone" value="<?= htmlspecialchars($etudiant['telephone']) ?>">

        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($etudiant['email']) ?>" required>

        <button type="submit">Mettre à jour</button>
    </form>

    <p><a href="index.php">Retour à la gestion des étudiants</a></p>
</body>
</html>
