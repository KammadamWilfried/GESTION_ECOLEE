<?php
session_start();
include("../config/db.php");

// Récupération des classes
$classes = mysqli_query($conn, "SELECT * FROM classe");

// Récupération des matières
$matieres = mysqli_query($conn, "SELECT * FROM matiere");

// Identifiant de la classe sélectionnée
$classe_id = isset($_GET['classe_id']) ? (int)$_GET['classe_id'] : 0;

// Insertion d’un emploi du temps
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_classe'])) {
    $id_classe = $_POST['id_classe'];
    $date = $_POST['date'];
    $heure_debut = $_POST['heure_debut'];
    $heure_fin = $_POST['heure_fin'];
    $id_matiere = $_POST['id_matiere'];

    $stmt = $conn->prepare("INSERT INTO emploi_temp (id_classe, id_matiere, date, heure_debut, heure_fin) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $id_classe, $id_matiere, $date, $heure_debut, $heure_fin);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Emploi du temps ajouté avec succès.";
    } else {
        $_SESSION['error'] = "Erreur lors de l'ajout.";
    }
    header("Location: index.php?classe_id=" . $id_classe);
    exit;
}

// Affichage des emplois du temps de la classe sélectionnée
$emplois = [];
if ($classe_id > 0) {
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
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion de l'Emploi du Temps</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 40px;
            background: #f4f6f9;
            color: #333;
        }

        h2, h3 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 20px;
        }

        form, table {
            max-width: 900px;
            margin: 0 auto;
        }

        select, input[type="date"], input[type="time"], button {
            padding: 10px;
            margin: 5px 0 15px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        select:focus, input:focus {
            outline: none;
            border-color: #2980b9;
        }

        button {
            background-color: #2980b9;
            color: white;
            font-weight: bold;
            border: none;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #1c5980;
            cursor: pointer;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        th, td {
            padding: 14px 16px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            padding: 12px;
            margin: 10px auto;
            border-left: 5px solid #28a745;
            max-width: 900px;
        }

        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            margin: 10px auto;
            border-left: 5px solid #dc3545;
            max-width: 900px;
        }

        .action-links a {
            color: #2980b9;
            text-decoration: none;
            margin-right: 10px;
        }

        .action-links a:hover {
            text-decoration: underline;
        }

        .back-link {
            text-align: center;
            margin-top: 40px;
        }

        .back-link a {
            text-decoration: none;
            color: #2c3e50;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media screen and (max-width: 600px) {
            input, select, button {
                font-size: 14px;
            }

            th, td {
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
    <h2>Gestion de l'Emploi du Temps</h2>

    <form method="get" action="">
        <label>Choisir une classe :</label>
        <select name="classe_id" onchange="this.form.submit()" required>
            <option value="">--Sélectionner une classe--</option>
            <?php while ($c = mysqli_fetch_assoc($classes)) : ?>
                <option value="<?= $c['id'] ?>" <?= ($c['id'] == $classe_id) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($c['nom']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert-success"><?= $_SESSION['message'] ?></div>
        <?php unset($_SESSION['message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert-danger"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if ($classe_id > 0): ?>
        <h3>Ajouter un cours à la classe <?= htmlspecialchars($classe_id) ?></h3>

        <form method="post">
            <input type="hidden" name="id_classe" value="<?= $classe_id ?>">
            <label>Date :</label>
            <input type="date" name="date" required>
            <label>Heure de début :</label>
            <input type="time" name="heure_debut" required>
            <label>Heure de fin :</label>
            <input type="time" name="heure_fin" required>
            <label>Matière :</label>
            <select name="id_matiere" required>
                <?php
                mysqli_data_seek($matieres, 0);
                while ($matiere = mysqli_fetch_assoc($matieres)) : ?>
                    <option value="<?= $matiere['id'] ?>"><?= htmlspecialchars($matiere['nom']) ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit">Ajouter à l'emploi du temps</button>
        </form>

        <?php if (count($emplois) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Heure de début</th>
                        <th>Heure de fin</th>
                        <th>Matière</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emplois as $emploi): ?>
                        <tr>
                            <td><?= htmlspecialchars($emploi['date']) ?></td>
                            <td><?= htmlspecialchars($emploi['heure_debut']) ?></td>
                            <td><?= htmlspecialchars($emploi['heure_fin']) ?></td>
                            <td><?= htmlspecialchars($emploi['matiere_nom']) ?></td>
                            <td class="action-links">
                                <a href="modifier_emploi.php?id=<?= $emploi['id'] ?>">Modifier</a>
                                <a href="supprimer_emploi.php?id=<?= $emploi['id'] ?>" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align:center;">Aucun emploi du temps défini pour cette classe.</p>
        <?php endif; ?>

        <form action="envoyer_email.php" method="get" style="text-align:center; margin-top: 25px;">
            <input type="hidden" name="classe_id" value="<?= $classe_id ?>">
            <button type="submit">📧 Envoyer l'emploi du temps par Email</button>
        </form>
    <?php endif; ?>

    <div class="back-link">
        <p><a href="../utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
    </div>
</body>
</html>