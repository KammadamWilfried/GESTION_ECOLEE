<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION["type_utilisateur"]) || $_SESSION["type_utilisateur"] != "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = "";

if ($id <= 0) {
    die("ID invalide.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données postées
    $nom = mysqli_real_escape_string($conn, $_POST["nom"]);
    $prenom = mysqli_real_escape_string($conn, $_POST["prenom"]);
    $date_naissance = $_POST["date_naissance"];
    $genre = $_POST["genre"];
    $adresse = mysqli_real_escape_string($conn, $_POST["adresse"]);
    $telephone = mysqli_real_escape_string($conn, $_POST["telephone"]);
    $email = mysqli_real_escape_string($conn, $_POST["email"]);
    $specialite = mysqli_real_escape_string($conn, $_POST["specialite"]);

    // Update utilisateur
    $sql1 = "UPDATE utilisateur SET 
                nom='$nom',
                prenom='$prenom',
                date_naissance='$date_naissance',
                genre='$genre',
                adresse='$adresse',
                telephone='$telephone',
                email='$email'
             WHERE id=$id";

    if (mysqli_query($conn, $sql1)) {
        // Update enseignant
        $sql2 = "UPDATE enseignant SET specialite='$specialite' WHERE id=$id";
        if (mysqli_query($conn, $sql2)) {
            $message = "Modifications enregistrées avec succès.";
        } else {
            $message = "Erreur lors de la mise à jour de la spécialité : " . mysqli_error($conn);
        }
    } else {
        $message = "Erreur lors de la mise à jour de l'utilisateur : " . mysqli_error($conn);
    }
}

// Charger données existantes
$sql = "SELECT u.*, e.specialite FROM utilisateur u JOIN enseignant e ON u.id = e.id WHERE u.id = $id AND u.type_utilisateur='Enseignant'";
$res = mysqli_query($conn, $sql);
if (mysqli_num_rows($res) == 0) {
    die("Enseignant non trouvé.");
}
$enseignant = mysqli_fetch_assoc($res);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Modifier un enseignant</title>
   <link rel="stylesheet" href="styles.css" />

</head>
<body>
<div class="container">
    <h2>Modifier un enseignant</h2>

    <?php if ($message): ?>
        <p><?php echo $message; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Nom :</label><br>
        <input type="text" name="nom" value="<?= htmlspecialchars($enseignant['nom']) ?>" required><br><br>

        <label>Prénom :</label><br>
        <input type="text" name="prenom" value="<?= htmlspecialchars($enseignant['prenom']) ?>" required><br><br>

        <label>Date de naissance :</label><br>
        <input type="date" name="date_naissance" value="<?= $enseignant['date_naissance'] ?>"><br><br>

        <label>Genre :</label><br>
        <select name="genre">
            <option value="M" <?= $enseignant['genre'] == 'M' ? 'selected' : '' ?>>Masculin</option>
            <option value="F" <?= $enseignant['genre'] == 'F' ? 'selected' : '' ?>>Féminin</option>
        </select><br><br>

        <label>Adresse :</label><br>
        <textarea name="adresse"><?= htmlspecialchars($enseignant['adresse']) ?></textarea><br><br>

        <label>Téléphone :</label><br>
        <input type="text" name="telephone" value="<?= htmlspecialchars($enseignant['telephone']) ?>"><br><br>

        <label>Email :</label><br>
        <input type="email" name="email" value="<?= htmlspecialchars($enseignant['email']) ?>" required><br><br>

        <label>Spécialité :</label><br>
        <input type="text" name="specialite" value="<?= htmlspecialchars($enseignant['specialite']) ?>"><br><br>

        <button type="submit">Enregistrer</button>
    </form>
     <p><a href="index.php">Retour</a></p>
</div>
<p><a href="../utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
</body>
</html>
