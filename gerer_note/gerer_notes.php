<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["utilisateur_id"]) || $_SESSION["type_utilisateur"] !== "Enseignant") {
    header("Location: ../login.php");
    exit;
}

$enseignant_id = $_SESSION["utilisateur_id"];

if (!isset($_GET['classe_id']) || !isset($_GET['matiere_id'])) {
    echo "Erreur : Classe ou matière non spécifiée.";
    exit;
}

$classe_id = (int)$_GET['classe_id'];
$matiere_id = (int)$_GET['matiere_id'];
$sequence = isset($_GET['sequence']) ? intval($_GET['sequence']) : null;
$annee_scolaire = $_GET['annee_scolaire'] ?? null;

// Récupération des étudiants
$sql_etudiants = "SELECT e.id AS etudiant_id, u.nom, u.prenom
                  FROM etudiant e
                  INNER JOIN inscription i ON i.id_etudiant = e.id
                  INNER JOIN utilisateur u ON u.id = e.id
                  WHERE i.id_classe = ?
                  ORDER BY u.nom";
$stmt = $conn->prepare($sql_etudiants);
$stmt->bind_param("i", $classe_id);
$stmt->execute();
$result_etudiants = $stmt->get_result();
$etudiants = $result_etudiants->fetch_all(MYSQLI_ASSOC);

// Récupération des notes existantes
$notes_existantes = [];
if ($sequence !== null && $annee_scolaire) {
    $sql_notes = "SELECT id_etudiant, note FROM note 
                  WHERE id_classe = ? AND id_matiere = ? AND sequence = ? AND annee_scolaire = ?";
    $stmt = $conn->prepare($sql_notes);
    $stmt->bind_param("iiis", $classe_id, $matiere_id, $sequence, $annee_scolaire);
    $stmt->execute();
    $result_notes = $stmt->get_result();
    while ($row = $result_notes->fetch_assoc()) {
        $notes_existantes[$row['id_etudiant']] = $row['note'];
    }
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['notes'], $_POST['sequence'], $_POST['annee_scolaire'])) {
    $notes = $_POST['notes'];
    $sequence = intval($_POST['sequence']);
    $annee_scolaire = $_POST['annee_scolaire'];

    foreach ($notes as $etudiant_id => $note_valeur) {
        $note_valeur = floatval($note_valeur);

        // Vérifie si la note existe déjà
        $sql_check = "SELECT id FROM note WHERE id_etudiant=? AND id_matiere=? AND id_classe=? AND sequence=? AND annee_scolaire=?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("iiiis", $etudiant_id, $matiere_id, $classe_id, $sequence, $annee_scolaire);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $sql_update = "UPDATE note SET note=? WHERE id_etudiant=? AND id_matiere=? AND id_classe=? AND sequence=? AND annee_scolaire=?";
            $stmt_upd = $conn->prepare($sql_update);
            $stmt_upd->bind_param("diiiis", $note_valeur, $etudiant_id, $matiere_id, $classe_id, $sequence, $annee_scolaire);
            $stmt_upd->execute();
            $stmt_upd->close();
        } else {
            $sql_insert = "INSERT INTO note (id_etudiant, id_matiere, id_classe, sequence, note, annee_scolaire) 
                           VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_ins = $conn->prepare($sql_insert);
            $stmt_ins->bind_param("iiiids", $etudiant_id, $matiere_id, $classe_id, $sequence, $note_valeur, $annee_scolaire);
            $stmt_ins->execute();
            $stmt_ins->close();
        }
        $stmt_check->close();
    }

    header("Location: ?classe_id=$classe_id&matiere_id=$matiere_id&sequence=$sequence&annee_scolaire=" . urlencode($annee_scolaire));
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Gérer les notes</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Gérer les notes</h1>

    <form method="get" action="">
        <input type="hidden" name="classe_id" value="<?= $classe_id ?>">
        <input type="hidden" name="matiere_id" value="<?= $matiere_id ?>">
        <label for="sequence">Séquence :</label>
        <select name="sequence" id="sequence" required>
            <option value="">-- Choisir une séquence --</option>
            <?php for ($i = 1; $i <= 6; $i++): ?>
                <option value="<?= $i ?>" <?= ($sequence === $i) ? 'selected' : '' ?>>Séquence <?= $i ?></option>
            <?php endfor; ?>
        </select>

        <label for="annee_scolaire">Année scolaire :</label>
        <input type="text" name="annee_scolaire" id="annee_scolaire" placeholder="2024-2025" value="<?= htmlspecialchars($annee_scolaire ?? '') ?>" required>
        <button type="submit">Valider</button>
    </form>

    <?php if ($sequence !== null && $annee_scolaire): ?>
    <form method="post" action="">
        <input type="hidden" name="sequence" value="<?= $sequence ?>">
        <input type="hidden" name="annee_scolaire" value="<?= htmlspecialchars($annee_scolaire) ?>">
        <table>
            <thead>
                <tr>
                    <th>Élève</th>
                    <th>Note</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $etudiant): ?>
                    <tr>
                        <td><?= htmlspecialchars($etudiant['nom'] . ' ' . $etudiant['prenom']) ?></td>
                        <td>
                            <input type="number" step="0.01" min="0" max="20"
                                   name="notes[<?= $etudiant['etudiant_id'] ?>]"
                                   value="<?= isset($notes_existantes[$etudiant['etudiant_id']]) ? htmlspecialchars($notes_existantes[$etudiant['etudiant_id']]) : '' ?>">
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <br>
        <button type="submit">Enregistrer les notes</button>
    </form>
    <?php endif; ?>

    <a href="index.php">← Retour</a>
</body>
</html>