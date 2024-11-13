<?php
session_start();

// Vérifiez si l'utilisateur est connecté et est administrateur
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: /index.php");
    exit();
}



// Connexion à la base de données
$conn = new mysqli('localhost', 'Fly', 'FNuhfzifjzf64', 'site_web');
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer les visites
function getVisitsForWeek($conn) {
    $week_visits = [];
    $week_dates = [];
    
    $query = "SELECT visit_date, visit_count FROM daily_visits 
              WHERE visit_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
              ORDER BY visit_date ASC";
    $result = $conn->query($query);

    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $week_visits[] = $row['visit_count'];
            $week_dates[] = $row['visit_date'];
        }
    }

    return ['visits' => $week_visits, 'dates' => $week_dates];
}

// Récupérer les utilisateurs
function getAllUsers($conn) {
    $query = "SELECT username, email, password, created_at FROM users ORDER BY created_at DESC";
    $result = $conn->query($query);

    $users = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    return $users;
}
$week_data = getVisitsForWeek($conn);
$users = getAllUsers($conn);
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord Administrateur</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #1e1e2e;
            color: #fff;
        }

        /* Barre latérale */
        .sidebar {
            width: 220px;
            background-color: #232323;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            padding: 20px;
            box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3);
        }

        .sidebar h2 {
            color: #FF4081;
            text-align: center;
            font-size: 24px;
            margin-bottom: 30px;
        }

                /* Tableau des utilisateurs */
                table {
            width: 100%;
            border-collapse: collapse;
            background-color: #2b2b3f;
            margin-bottom: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            overflow: hidden;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
        }

        table th {
            background-color: #3f3f46;
            color: #FF4081;
        }

        table tr:nth-child(even) {
            background-color: #2b2b3f;
        }

        table tr:hover {
            background-color: #3f3f46;
        }

        /* Boutons de gestion */
        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .edit-btn, .delete-btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }

        .edit-btn {
            background-color: #FF4081;
        }

        .edit-btn:hover {
            background-color: #e03568;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .delete-btn:hover {
            background-color: #c82333;
        }

        /* Bouton retour */
        .back-btn {
            padding: 10px 20px;
            background-color: #FF4081;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            transition: background-color 0.3s ease;
            display: inline-block;
        }

        .back-btn:hover {
            background-color: #e03568;
        }

        .sidebar a {
            text-decoration: none;
            color: #a0aec0;
            padding: 15px 10px;
            display: flex;
            align-items: center;
            font-size: 18px;
            margin-bottom: 10px;
            border-radius: 10px;
            transition: background-color 0.3s ease;
        }

        .sidebar a.active,
        .sidebar a:hover {
            background-color: #3f3f46;
            color: #FF4081;
        }

        /* Contenu principal */
        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
            background-color: #1e1e2e;
            min-height: 100vh;
        }

        /* Titre de la section */
        h1 {
            font-size: 36px;
            color: #FF4081;
            margin-bottom: 20px;
        }

        /* Statistiques */
        .stats {
            display: flex;
            justify-content: space-around;
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-box {
            background-color: #282828;
            color: #fff;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease;
        }

        .stat-box:hover {
            transform: translateY(-10px);
        }

        .stat-box h2 {
            font-size: 40px;
            margin: 0;
        }

        .stat-box p {
            margin-top: 10px;
            font-size: 16px;
        }

        /* Graphique */
        .chart-container {
            background-color: #282828;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .chart-container canvas {
            width: 100%;
        }

        /* Boutons de période */
        .period-buttons {
            text-align: center;
            margin-bottom: 20px;
        }

        .period-buttons button {
            padding: 10px 20px;
            margin: 0 5px;
            border: none;
            background-color: #FF4081;
            color: white;
            cursor: pointer;
            border-radius: 50px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .period-buttons button:hover {
            background-color: #e03568;
        }

        /* Sections supplémentaires */
        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        .method-box, .sales-box, .user-box {
            background-color: #282828;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .method-box h3, .sales-box h3, .user-box h3 {
            color: #FF4081;
            margin-bottom: 15px;
        }

        .method-box p, .sales-box p, .user-box p {
            font-size: 18px;
        }

        /* Bouton Accueil */
        .home-btn {
            padding: 10px 20px;
            background-color: #FF4081;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-size: 16px;
            display: inline-block;
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }

        .home-btn:hover {
            background-color: #e03568;
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Panel</h2>
        <a href="javascript:void(0)" onclick="showSection('stats-section')" class="active"><i class="fas fa-chart-bar"></i> Statistiques</a>
        <a href="javascript:void(0)" onclick="showSection('payment-section')"><i class="fas fa-credit-card"></i> Moyens de paiement</a>
        <a href="javascript:void(0)" onclick="showSection('sales-section')"><i class="fas fa-shopping-cart"></i> Ventes</a>
        <a href="javascript:void(0)" onclick="showSection('user-management-section')"><i class="fas fa-users"></i> Gestion des utilisateurs</a>
        <a href="/index.php" class="home-btn">Retour à l'accueil</a>
    </div>

    <div class="main-content">
        <!-- Section Statistiques -->
        <div id="stats-section" class="section active">
            <h1>Statistiques du Site</h1>
            <div class="stats">
                <div class="stat-box">
                    <h2>150</h2>
                    <p>Utilisateurs enregistrés</p>
                </div>
                <div class="stat-box">
                    <h2>50</h2>
                    <p>Commandes effectuées</p>
                </div>
                <div class="stat-box">
                    <h2>12,000 €</h2>
                    <p>Revenus totaux</p>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="visitsChart"></canvas>
            </div>
        </div>

        <!-- Section Moyens de paiement -->
        <div id="payment-section" class="section">
            <h1>Gestion des Moyens de Paiement</h1>
            <div class="method-box">
                <h3>Configurer PayPal</h3>
                <p>Gérez les paiements via PayPal.</p>
                <a href="config_paypal.php" class="home-btn">Configurer</a>
            </div>
            <div class="method-box">
                <h3>Configurer Stripe</h3>
                <p>Gérez les paiements via Stripe.</p>
                <a href="config_stripe.php" class="home-btn">Configurer</a>
            </div>
        </div>

        <!-- Section Ventes -->
        <div id="sales-section" class="section">
            <h1>Statistiques des Ventes</h1>
            <div class="sales-box">
                <h3>Revenus mensuels</h3>
                <p>12 000 €</p>
            </div>
            <div class="sales-box">
                <h3>Ventes totales</h3>
                <p>50 ventes ce mois-ci</p>
            </div>
        </div>

        <!-- Section Gestion des utilisateurs -->
        <div id="user-management-section" class="section">
    <h1>Gestion des Utilisateurs</h1>

    <!-- Affichage des utilisateurs sous forme de tableau -->
    <table>
        <thead>
            <tr>
                <th>Pseudo</th>
                <th>Email</th>
                <th>Mot de passe</th>
                <th>Date de création</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>********</td> <!-- Masquer le mot de passe avec des étoiles -->
                    <td><?= htmlspecialchars($user['created_at']) ?></td>
                    <td class="action-buttons">
                        <button class="edit-btn">Modifier</button>
                        <button class="delete-btn">Supprimer</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

        <!-- Ajout de scripts supplémentaires pour les actions -->
        <script>
            // Placeholder pour les actions Modifier et Supprimer
            const editButtons = document.querySelectorAll('.edit-btn');
            const deleteButtons = document.querySelectorAll('.delete-btn');

            editButtons.forEach(button => {
                button.addEventListener('click', () => {
                    alert('Fonction de modification à venir.');
                });
            });

            deleteButtons.forEach(button => {
                button.addEventListener('click', () => {
                    if (confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?')) {
                        alert('Utilisateur supprimé.');
                        // Vous ajouterez ici le code pour supprimer l'utilisateur via une requête PHP
                    }
                });
            });
        </script>
    </div>
            <div class="user-box">
                <h3>Administrateurs</h3>
                <p>5 administrateurs</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const weekData = <?php echo json_encode($week_data['visits']); ?>;
        const weekLabels = <?php echo json_encode($week_data['dates']); ?>;

        const ctx = document.getElementById('visitsChart').getContext('2d');
        let visitsChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: weekLabels,
                datasets: [{
                    label: 'Visites du site',
                    data: weekData,
                    backgroundColor: 'rgba(255, 64, 129, 0.2)',
                    borderColor: '#FF4081',
                    borderWidth: 3,
                    tension: 0.4,
                    pointBackgroundColor: '#FF4081',
                    pointRadius: 5,
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                    }
                }
            }
        });

        function showSection(sectionId) {
            const sections = document.querySelectorAll('.section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            document.getElementById(sectionId).classList.add('active');
        }
    </script>

</body>
</html>
