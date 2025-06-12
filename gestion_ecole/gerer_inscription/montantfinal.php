<?php
session_start();
include("../config/db.php");

$res = mysqli_query($conn, "SELECT montantfinal FROM config_paiement ORDER BY id DESC LIMIT 1");
$config = mysqli_fetch_assoc($res);

$montantfinal = isset($config['montantfinal']) ? $config['montantfinal'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newMontant = (int)$_POST['montantfinal'];
    if ($newMontant > 0) {

        mysqli_query($conn, "INSERT INTO config_paiement (montantfinal) VALUES ($newMontant)");
        header("Location: montantfinal.php?success=1");
        exit;
    } else {
        $error = "Veuillez saisir un montant valide.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gestion du Montant Final</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Montant total de l'école </h2>

    <?php if (isset($_GET['success'])): ?>
        <p style="color:green;">Montant mis à jour avec succès.</p>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <p style="color:red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label for="montantfinal">Montant final (en FCFA) :</label>
        <input type="number" name="montantfinal" id="montantfinal" value="<?= htmlspecialchars($montantfinal) ?>" min="0" required>
        <button type="submit">Enregistrer</button>
    </form>

    <p><a href="inscription.php">Retour à l'inscription</a></p>
</body>
</html>
