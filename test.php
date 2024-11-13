<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /mon_projet/authentification/login.php');
    exit();
}

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'site_web');
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Vérifier si l'utilisateur a un abonnement valide
$query = "SELECT subscription_expiration FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($subscription_expiration);
$stmt->fetch();
$stmt->close();

$current_date = date('Y-m-d');

if ($subscription_expiration < $current_date) {
    echo "Votre abonnement a expiré. Veuillez renouveler votre abonnement pour accéder à ce service.";
    exit();
}

// Le reste de votre page protégée (test.php)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Découpeur de Vidéos YouTube</title>

    <style>
        /* Global styles */
        body {
            margin: 0;
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #FF1493, #000000); /* Rose and black gradient */
            color: #fff;
            text-align: center;
        }

        /* Main container */
        .main-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Central content container */
        .container {
            background-color: rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            width: 50%;
            max-width: 600px;
        }

        /* Logo styling */
        .logo img {
            width: 300px;
            margin-bottom: 20px;
        }

        /* Title styling */
        h1 {
            font-size: 28px;
            margin-bottom: 20px;
        }

        /* Input fields */
        input[type="text"] {
            padding: 15px;
            width: 80%;
            margin-bottom: 20px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
        }

        /* Checkbox container */
        .checkbox-container {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .checkbox-container input[type="checkbox"] {
            width: 18px;
            height: 18px;
            margin-right: 10px;
        }

        /* Buttons */
        button, .download-button {
            padding: 15px 30px;
            font-size: 16px;
            background-color: #FF1493; /* Rose color */
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover, .download-button:hover {
            background-color: #e60073; /* Darker rose */
        }

        /* Progress bar */
        .progress-bar {
            width: 80%;
            height: 30px;
            background-color: rgba(255, 255, 255, 0.3);
            border-radius: 15px;
            overflow: hidden;
            margin: 20px auto;
        }

        .progress {
            height: 100%;
            width: 0%;
            background-color: #00ff6a; /* Green */
            transition: width 0.2s ease;
        }

        #progress-text {
            font-size: 18px;
            margin-top: 10px;
        }

        /* Flag for language switch */
        .language-switch {
            position: fixed;
            top: 10px;
            right: 20px;
            z-index: 1001;
        }

        .language-switch img {
            width: 40px;
            height: auto;
            cursor: pointer;
            transition: transform 0.2s ease-in-out;
        }

        .language-switch img:hover {
            transform: scale(1.1); /* Slight zoom effect */
        }

        /* Side panels for options */
        .side-panel, .side-panel2 {
            display: none;
            width: 300px;
            height: 100vh;
            background-color: #1e1e1e;
            color: white;
            padding: 20px;
            position: fixed;
            top: 0;
            z-index: 1000;
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .side-panel.active, .side-panel2.active {
            display: block;
            transform: translateX(0);
        }

        .side-panel {
            left: 0;
            border-radius: 0 10px 10px 0;
            transform: translateX(-100%);
        }

        .side-panel2 {
            right: 0;
            border-radius: 10px 0 0 10px;
            transform: translateX(100%);
        }

        .panel-close-btn {
            background-color: #FF1493;
            border: none;
            padding: 10px;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }
    </style>

</head>
<body>

    <!-- Language Switch Flags -->
    <div class="language-switch">
        <img id="french-flag" src="image/fr.png" alt="Français" onclick="switchLanguage('en')" style="display: inline;">
        <img id="us-flag" src="image/us.png" alt="English" onclick="switchLanguage('fr')" style="display: none;">
    </div>

    <div class="main-container">
        <div class="container">
            <!-- Logo -->
            <div class="logo">
                <img src="assets/logo1.png" alt="Logo du site">
            </div>

            <!-- Main Title -->
            <h1 data-key="title">Découpez votre vidéo en plusieurs parties</h1>

            <!-- YouTube URL Input -->
            <input type="text" id="youtube-url" placeholder="Entrez l'URL de la vidéo YouTube" data-key="youtube-placeholder">

            <!-- Duration Input -->
            <input type="text" id="part-duration" placeholder="Durée de chaque partie (en secondes)" data-key="duration-placeholder">

            <!-- Checkbox for Subtitle -->
            <div class="checkbox-container">
                <input type="checkbox" id="apply-watermark" onclick="toggleSidePanel()">
                <label for="apply-watermark" data-key="apply-watermark-label">Appliquer un sous-titre</label>
            </div>

            <!-- Checkbox for Video Settings -->
            <div class="checkbox-container">
                <input type="checkbox" id="apply-watermark2" onclick="toggleSidePanel2()">
                <label for="apply-watermark2" data-key="video-settings-label">Paramètres vidéos</label>
            </div>

            <!-- Left Side Panel -->
            <div id="side-panel" class="side-panel">
                <h2 data-key="subtitle-panel-title">Options des sous-titres</h2>
                <div class="subtitle-option">
                    <label for="subtitle-color" data-key="subtitle-color-label">Choisissez la couleur des sous-titres :</label>
                    <input type="color" id="subtitle-color" value="#ffff00">
                </div>
                <button class="panel-close-btn" onclick="hidePanel('side-panel', 'show-panel-btn-left')" data-key="close-panel-button">Fermer</button>
            </div>

            <!-- Right Side Panel -->
            <div id="side-panel2" class="side-panel2">
                <h3 data-key="video-panel-title">Paramètres vidéos</h3>
                <div class="input-group">
                    <label for="start-time" data-key="start-time-label">Début de l'enregistrement (format mm:ss ou mm.ss)</label>
                    <input type="text" id="start-time" placeholder="Ex : 3:30 ou 3.30">
                </div>
                <div class="input-group">
                    <label for="end-time" data-key="end-time-label">Fin de l'enregistrement (format mm:ss ou mm.ss)</label>
                    <input type="text" id="end-time" placeholder="Ex : 5:00 ou 5.00">
                </div>
                <button class="panel-close-btn" onclick="hidePanel('side-panel2', 'show-panel-btn-right')" data-key="close-panel-button">Fermer</button>
            </div>

            <!-- Action Buttons -->
            <div class="action-container">
                <button id="submit-button" onclick="sendVideo()" data-key="submit-button">Découper la vidéo</button>
                <a id="download-button" href="#" style="display: none;" class="download-button" data-key="download-button">Télécharger les vidéos découpées</a>

                <!-- Progress Bar -->
                <div class="progress-bar">
                    <div class="progress" id="progress"></div>
                </div>
                <p id="progress-text" data-key="progress-text">En attente...</p>
            </div>
        </div>
    </div>

    <script>
        // Include the JavaScript from the file
        <?php include 'script.js'; ?>
    </script>
</body>
</html>
