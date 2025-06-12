<?php 
session_start();
include("../../config/db.php");

$classes = mysqli_query($conn, "SELECT * FROM classe");

$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 0;
$datepres = isset($_GET['date']) ? $_GET['date'] : '';
$idmatiereselect = isset($_GET['id_matiere']) ? (int)$_GET['id_matiere'] : 0;

$dates = [];
$matieres = [];
$etudiants = [];

// Récupération des dates de cours
if ($classe_id > 0) {
    $requete = mysqli_prepare($conn, "SELECT DISTINCT date FROM emploi_temp WHERE id_classe = ? ORDER BY date");
    mysqli_stmt_bind_param($requete, "i", $classe_id);
    mysqli_stmt_execute($requete);
    $result = mysqli_stmt_get_result($requete);
    $dates = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

// Récupération des matières du jour sélectionné
if (!empty($datepres) && $classe_id > 0) {
    $requete = mysqli_prepare($conn, "SELECT m.id AS id_matiere, m.nom 
        FROM emploi_temp et
        JOIN matiere m ON et.id_matiere = m.id
        WHERE et.id_classe = ? AND et.date = ?");
    mysqli_stmt_bind_param($requete, "is", $classe_id, $datepres);
    mysqli_stmt_execute($requete);
    $result = mysqli_stmt_get_result($requete);
    $matieres = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Récupération des étudiants et états de présence
    if ($idmatiereselect > 0) {
        $requete = mysqli_prepare($conn, "SELECT e.id AS etudiant_id, u.nom, u.prenom, p.etat 
            FROM etudiant e 
            JOIN utilisateur u ON e.id = u.id 
            LEFT JOIN presence p ON p.id_etudiant = e.id AND p.date = ? AND p.id_matiere = ?
            WHERE e.id IN (SELECT id_etudiant FROM inscription WHERE id_classe = ?)");
        mysqli_stmt_bind_param($requete, "sii", $datepres, $idmatiereselect, $classe_id);
        mysqli_stmt_execute($requete);
        $result = mysqli_stmt_get_result($requete);
        $etudiants = mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_etudiant = (int)$_POST['id_etudiant'];
    $etat = trim($_POST['etat']);
    $id_matiere = (int)$_POST['id_matiere'];
    $datepres = $_POST['date'];

    $valeurs_autorisees = ['Present', 'Absent', 'Justifier'];
    if (!in_array($etat, $valeurs_autorisees)) {
        $_SESSION['error'] = "Valeur d'état invalide.";
        header("Location: index.php?classe_id=$classe_id&date=" . urlencode($datepres) . "&id_matiere=$id_matiere");
        exit;
    }

    $verif = mysqli_prepare($conn, "SELECT id FROM presence WHERE id_etudiant = ? AND date = ? AND id_matiere = ?");
    mysqli_stmt_bind_param($verif, "isi", $id_etudiant, $datepres, $id_matiere);
    mysqli_stmt_execute($verif);
    $res_check = mysqli_stmt_get_result($verif);

    if (mysqli_num_rows($res_check) > 0) {
        // Modification
        $update = mysqli_prepare($conn, "UPDATE presence SET etat = ? WHERE id_etudiant = ? AND date = ? AND id_matiere = ?");
        mysqli_stmt_bind_param($update, "sisi", $etat, $id_etudiant, $datepres, $id_matiere);
        if (mysqli_stmt_execute($update)) {
            $_SESSION['message'] = "État modifié avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de la modification : " . mysqli_error($conn);
        }
    } else {
        // Insertion
        $requete = mysqli_prepare($conn, "INSERT INTO presence (id_etudiant, date, etat, id_matiere) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($requete, "issi", $id_etudiant, $datepres, $etat, $id_matiere);
        if (mysqli_stmt_execute($requete)) {
            $_SESSION['message'] = "Présence enregistrée avec succès.";
        } else {
            $_SESSION['error'] = "Erreur lors de l'enregistrement : " . mysqli_error($conn);
        }
    }

    header("Location: index.php?classe_id=$classe_id&date=" . urlencode($datepres) . "&id_matiere=$id_matiere");
    exit;
}

// Pour affichage
$nom_matiereselect = '';
$matieres_assoc = array_column($matieres, 'nom', 'id_matiere');
if (isset($matieres_assoc[$idmatiereselect])) {
    $nom_matiereselect = $matieres_assoc[$idmatiereselect];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Présences</title>
 <link rel="stylesheet" href="style.css">
</head>
<body>
<h2>Gestion des Présences</h2>

<form method="get" action="">
    <label>Choisir une classe :</label>
    <select name="classe_id" onchange="this.form.submit()">
        <option value="">--Sélectionner--</option>
        <?php while ($c = mysqli_fetch_assoc($classes)) : ?>
            <option value="<?= $c['id'] ?>" <?= ($c['id'] == $classe_id) ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nom']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <?php if ($classe_id > 0): ?>
        <label>Choisir une date :</label>
        <select name="date" onchange="this.form.submit()">
            <option value="">--Sélectionner--</option>
            <?php foreach ($dates as $date): ?>
                <option value="<?= $date['date'] ?>" <?= ($date['date'] == $datepres) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($date['date']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>

    <?php if ($datepres && count($matieres) > 0): ?>
        <label>Choisir une matière :</label>
        <select name="id_matiere" onchange="this.form.submit()">
            <option value="">--Sélectionner--</option>
            <?php foreach ($matieres as $matiere): ?>
                <option value="<?= $matiere['id_matiere'] ?>" <?= ($matiere['id_matiere'] == $idmatiereselect) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($matiere['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endif; ?>
</form>

<?php if (isset($_SESSION['message'])): ?>
    <div style="color:green;"><?= $_SESSION['message'] ?></div>
    <?php unset($_SESSION['message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div style="color:red;"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<?php if ($datepres && $idmatiereselect && count($etudiants) > 0): ?>
    <h3>Présences le <?= htmlspecialchars($datepres) ?> - Matière : <?= htmlspecialchars($nom_matiereselect) ?></h3>
<button onclick="window.print()" class="btn btn-primary">Imprimer</button>
    
<div class="printable">
    <table border="1" cellpadding="8" cellspacing="0">
        <thead>
        <tr>
            <th>Nom de l'Étudiant</th>
            <th>État</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($etudiants as $e): ?>
            <tr>
                <td><?= htmlspecialchars($e['nom'] . ' ' . $e['prenom']) ?></td>
                <td><?= isset($e['etat']) ? htmlspecialchars($e['etat']) : 'Non marqué' ?></td>
                <td>
                    <form method="post" action="">
                        <input type="hidden" name="id_etudiant" value="<?= $e['etudiant_id'] ?>">
                        <input type="hidden" name="id_matiere" value="<?= $idmatiereselect ?>">
                        <input type="hidden" name="date" value="<?= htmlspecialchars($datepres) ?>">
                        <select name="etat" required>
                           <option value="Present" <?= (isset($e['etat']) && $e['etat'] == 'Present') ? 'selected' : '' ?>>Present</option>
                           <option value="Absent" <?= (isset($e['etat']) && $e['etat'] == 'Absent') ? 'selected' : '' ?>>Absent</option>
                           <option value="Justifier" <?= (isset($e['etat']) && $e['etat'] == 'Justifier') ? 'selected' : '' ?>>Justifier</option>

                        </select>
                        <input type="submit" value="<?= isset($e['etat']) ? 'Modifier état' : 'Enregistrer' ?>">
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php elseif ($classe_id && $datepres): ?>
    <p>Veuillez sélectionner une matière pour afficher les présences.</p>
<?php endif; ?>

<a href="../../utilisateurs/dashboard.php">Retour au dashboard</a>
</body>
</html>
