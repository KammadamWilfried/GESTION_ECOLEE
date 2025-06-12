<?php
session_start();
require_once 'config/db.php';

if (!isset($_SESSION["utilisateur_id"]) || $_SESSION["type_utilisateur"] !== "Administrateur") {
    header("Location: ../login.php");
    exit;
}

$sql_etudiants = "SELECT COUNT(*) AS total FROM etudiant";
$result_etudiants = $conn->query($sql_etudiants);
$total_etudiants = $result_etudiants->fetch_assoc()['total'];

$sql_classes = "SELECT COUNT(*) AS total FROM classe";
$result_classes = $conn->query($sql_classes);
$total_classes = $result_classes->fetch_assoc()['total'];

$sql_enseignants = "SELECT COUNT(*) AS total FROM utilisateur WHERE type_utilisateur = 'Enseignant'";
$result_enseignants = $conn->query($sql_enseignants);
$total_enseignants = $result_enseignants->fetch_assoc()['total'];

$sql_admins = "SELECT COUNT(*) AS total FROM utilisateur WHERE type_utilisateur = 'Administrateur'";
$result_admins = $conn->query($sql_admins);
$total_admins = $result_admins->fetch_assoc()['total'];

$sql_moyennes = "SELECT m.nom AS matiere, AVG(n.note) AS moyenne
                 FROM note n
                 INNER JOIN matiere m ON n.id_matiere = m.id
                 GROUP BY m.nom";
$result_moyennes = $conn->query($sql_moyennes);
$moyennes = [];
while ($row = $result_moyennes->fetch_assoc()) {
    $moyennes[] = $row;
}

$sql_moyennes_classe = "SELECT c.nom AS classe, m.nom AS matiere, AVG(n.note) AS moyenne
                        FROM note n
                        INNER JOIN matiere m ON n.id_matiere = m.id
                        INNER JOIN inscription i ON n.id_etudiant = i.id_etudiant
                        INNER JOIN classe c ON i.id_classe = c.id
                        GROUP BY c.nom, m.nom";
$result_moyennes_classe = $conn->query($sql_moyennes_classe);
$moyennes_classe = [];
$all_matieres = [];

while ($row = $result_moyennes_classe->fetch_assoc()) {
    $moyennes_classe[$row['classe']][$row['matiere']] = $row['moyenne'];
    $all_matieres[] = $row['matiere'];
}
$unique_matieres = array_values(array_unique($all_matieres));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Statistiques</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 30px;
            background: #f4f6f9;
            color: #333;
        }

        h1, h2 {
            text-align: center;
            color: #1a237e;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            margin-bottom: 25px;
        }

        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-bottom: 30px;
            gap: 20px;
        }

        .card {
            flex: 1 1 200px;
            background: #ffffff;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
        }

        .card h3 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #1565c0;
        }

        .card p {
            font-size: 1.8rem;
            color: #2e7d32;
            margin: 0;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 14px 18px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
            font-size: 0.95rem;
        }

        th {
            background-color: #e3f2fd;
            color: #0d47a1;
            font-weight: 600;
        }

        td {
            background: #ffffff;
        }

        canvas {
            display: block;
            margin: 40px auto;
            background: #fff;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            max-width: 100%;
        }

        @media screen and (max-width: 768px) {
            .card-container {
                flex-direction: column;
                align-items: center;
            }

            .card {
                width: 90%;
            }

            table, canvas {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <h1>Statistiques de l'École</h1>

    <div class="card-container">
        <div class="card">
            <h3>Étudiants</h3>
            <p><?= htmlspecialchars($total_etudiants) ?></p>
        </div>
        <div class="card">
            <h3>Classes</h3>
            <p><?= htmlspecialchars($total_classes) ?></p>
        </div>
        <div class="card">
            <h3>Enseignants</h3>
            <p><?= htmlspecialchars($total_enseignants) ?></p>
        </div>
        <div class="card">
            <h3>Administrateurs</h3>
            <p><?= htmlspecialchars($total_admins) ?></p>
        </div>
    </div>

    <h2>Moyennes des Notes par Matière</h2>
    <canvas id="moyennesMatiereChart"></canvas>
    
    <h2>Moyennes des Notes par Classe et Matière</h2>
    <canvas id="moyennesClasseChart"></canvas>

    <h2>Moyennes Détail par Matière</h2>
    <table>
        <thead>
            <tr>
                <th>Matière</th>
                <th>Moyenne</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($moyennes as $moyenne): ?>
                <tr>
                    <td><?= htmlspecialchars($moyenne['matiere']) ?></td>
                    <td><?= htmlspecialchars(number_format($moyenne['moyenne'], 2)) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        const ctxMatiere = document.getElementById('moyennesMatiereChart').getContext('2d');
        const matieres = <?= json_encode(array_column($moyennes, 'matiere')) ?>;
        const moyennes = <?= json_encode(array_map('floatval', array_column($moyennes, 'moyenne'))) ?>;

        new Chart(ctxMatiere, {
            type: 'bar',
            data: {
                labels: matieres,
                datasets: [{
                    label: 'Moyenne des Notes',
                    data: moyennes,
                    backgroundColor: 'rgba(33, 150, 243, 0.5)',
                    borderColor: 'rgba(33, 150, 243, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 20
                    }
                }
            }
        });

        const ctxClasse = document.getElementById('moyennesClasseChart').getContext('2d');
        const classes = <?= json_encode(array_keys($moyennes_classe)) ?>;
        const moyennesClasse = <?= json_encode($moyennes_classe) ?>;
        const matieresClasse = <?= json_encode($unique_matieres) ?>;

        const dataClasse = {
            labels: classes,
            datasets: []
        };

        matieresClasse.forEach((matiere, index) => {
            const colors = [
                'rgba(255, 99, 132, 0.2)',
                'rgba(54, 162, 235, 0.2)',
                'rgba(255, 206, 86, 0.2)',
                'rgba(75, 192, 192, 0.2)',
                'rgba(153, 102, 255, 0.2)',
                'rgba(255, 159, 64, 0.2)'
            ];
            const borderColors = [
                'rgba(255, 99, 132, 1)',
                'rgba(54, 162, 235, 1)',
                'rgba(255, 206, 86, 1)',
                'rgba(75, 192, 192, 1)',
                'rgba(153, 102, 255, 1)',
                'rgba(255, 159, 64, 1)'
            ];

            dataClasse.datasets.push({
                label: matiere,
                data: classes.map(classe => moyennesClasse[classe][matiere] ?? 0),
                backgroundColor: colors[index % colors.length],
                borderColor: borderColors[index % borderColors.length],
                borderWidth: 1
            });
        });

        new Chart(ctxClasse, {
            type: 'bar',
            data: dataClasse,
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    x: { stacked: true },
                    y: {
                        beginAtZero: true,
                        stacked: true,
                        max: 20
                    }
                }
            }
        });
    </script>
     <p style="text-align:center;"><a href="utilisateurs/dashboard.php">← Retour au tableau de bord</a></p>
</body>
</html>