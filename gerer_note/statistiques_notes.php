<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION["utilisateur_id"]) || $_SESSION["type_utilisateur"] !== "Enseignant") {
    header("Location: ../login.php");
    exit;
}

$enseignant_id = $_SESSION["utilisateur_id"];

$sql = "SELECT m.id, m.nom 
        FROM matiere m
        INNER JOIN matiere_enseignant me ON me.matiere_id = m.id
        WHERE me.enseignant_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $enseignant_id);
$stmt->execute();
$result_matieres = $stmt->get_result();
$matieres = $result_matieres ? $result_matieres->fetch_all(MYSQLI_ASSOC) : [];

$statistiques = [];
$matiere_nom = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['matiere_id'], $_POST['sequence'])) {
    $matiere_id = (int)$_POST['matiere_id'];
    $sequence = $_POST['sequence'];

    foreach ($matieres as $m) {
        if ($m['id'] == $matiere_id) {
            $matiere_nom = $m['nom'];
            break;
        }
    }

    $sql = "SELECT AVG(note) AS moyenne, MIN(note) AS min_note, MAX(note) AS max_note, COUNT(*) AS total
            FROM note
            WHERE id_matiere = ? AND sequence = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $matiere_id, $sequence);
    $stmt->execute();
    $result = $stmt->get_result();
    $statistiques = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>📊 Statistiques des notes</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #e3f2fd, #ffffff);
            padding: 40px;
            margin: 0;
        }
        h1 {
            text-align: center;
            font-size: 2.5em;
            color: #2c3e50;
            margin-bottom: 40px;
        }
        form {
            max-width: 800px;
            margin: auto;
            background: #ffffff;
            padding: 25px 30px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        }
        select, button {
            font-size: 1rem;
            padding: 12px;
            border-radius: 8px;
            border: 1px solid #ccc;
            outline: none;
        }
        select {
            width: 220px;
        }
        button {
            background-color: #2980b9;
            color: #fff;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #1c6ea4;
        }
        .content {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
        }
        h3 {
            text-align: center;
            color: #34495e;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        th, td {
            padding: 14px 20px;
            text-align: center;
            border-bottom: 1px solid #f0f0f0;
        }
        th {
            background-color: #2980b9;
            color: #fff;
            font-weight: 600;
        }
        td {
            background-color: #fafafa;
            color: #2c3e50;
        }
        .print-btn {
            display: block;
            margin: 0 auto 40px auto;
            padding: 12px 24px;
            font-size: 1rem;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: 0.3s;
        }
        .print-btn:hover {
            background-color: #219150;
        }
        canvas {
            display: block;
            margin: 30px auto 10px auto;
            max-width: 100%;
        }
        a {
            display: block;
            text-align: center;
            color: #2980b9;
            font-weight: bold;
            text-decoration: none;
            margin-top: 30px;
            transition: 0.3s;
        }
        a:hover {
            color: #1c6ea4;
            text-decoration: underline;
        }
        p {
            text-align: center;
            font-size: 1.1rem;
            color: #c0392b;
            margin-top: 30px;
        }
    </style>
</head>
<body>

<h1>📊 Statistiques de notes</h1>

<form method="post">
    <label>Matière :</label>
    <select name="matiere_id" required>
        <option value="">-- Choisir --</option>
        <?php foreach ($matieres as $matiere): ?>
            <option value="<?= $matiere['id'] ?>" <?= (isset($_POST['matiere_id']) && $_POST['matiere_id'] == $matiere['id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($matiere['nom']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Séquence :</label>
    <select name="sequence" required>
        <option value="">-- Choisir --</option>
        <?php for ($i = 1; $i <= 6; $i++): ?>
            <option value="Séquence <?= $i ?>" <?= (isset($_POST['sequence']) && $_POST['sequence'] == "Séquence $i") ? 'selected' : '' ?>>
                Séquence <?= $i ?>
            </option>
        <?php endfor; ?>
    </select>

    <button type="submit">Afficher</button>
</form>

<?php if (!empty($statistiques) && $statistiques['total'] > 0): ?>
<div class="content" id="print-section">
    <h3><?= htmlspecialchars($matiere_nom) ?> - <?= htmlspecialchars($_POST['sequence']) ?></h3>
    <table>
        <thead>
            <tr>
                <th>Moyenne</th>
                <th>Note Min</th>
                <th>Note Max</th>
                <th>Nombre de notes</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= number_format($statistiques['moyenne'], 2) ?></td>
                <td><?= number_format($statistiques['min_note'], 2) ?></td>
                <td><?= number_format($statistiques['max_note'], 2) ?></td>
                <td><?= $statistiques['total'] ?></td>
            </tr>
        </tbody>
    </table>
    <canvas id="statistiquesChart" width="500" height="300"></canvas>
</div>
<button class="print-btn" onclick="printStats()">🖨 Imprimer</button>
<?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <p>Aucune note trouvée pour cette matière et cette séquence.</p>
<?php endif; ?>

<a href="../utilisateurs/dashboard.php">⬅ Retour au tableau de bord</a>

<script>
<?php if (!empty($statistiques) && $statistiques['total'] > 0): ?>
    const ctx = document.getElementById('statistiquesChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Moyenne', 'Note Min', 'Note Max'],
            datasets: [{
                label: 'Valeurs',
                data: [
                    <?= number_format($statistiques['moyenne'], 2) ?>,
                    <?= number_format($statistiques['min_note'], 2) ?>,
                    <?= number_format($statistiques['max_note'], 2) ?>
                ],
                backgroundColor: ['#4CAF50', '#f44336', '#2196F3']
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
<?php endif; ?>

    function printStats() {
        const canvas = document.getElementById("statistiquesChart");
        const imgData = canvas.toDataURL("image/png");

        const section = document.getElementById("print-section").cloneNode(true);
        const img = document.createElement("img");
        img.src = imgData;
        img.style.maxWidth = "100%";
        section.querySelector("canvas").replaceWith(img);

        const printWindow = window.open('', '_blank');
        printWindow.document.write(`
            <html>
            <head><title>Impression Statistiques</title></head>
            <body>${section.innerHTML}</body>
            </html>
        `);
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
        printWindow.close();
    }
</script>

</body>
</html>