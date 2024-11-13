<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: /authentification/login.php');
    exit;
}

$message = '';
$activeSection = 'account-info';

// Connexion à la base de données
try {
    $db = new PDO('mysql:host=localhost;dbname=site_web', 'Fly', 'FNuhfzifjzf64');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Vérifier si l'utilisateur a un google_id dans la base de données
$stmt = $db->prepare("SELECT google_id FROM users WHERE email = ?");
$stmt->execute([$_SESSION['email'] ?? '']);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$isGoogleAccount = !empty($user['google_id']);

// Mettre à jour les informations si une demande de mise à jour est envoyée
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Mise à jour du mot de passe
    if (isset($_POST['update_password'])) {
        $newPassword = $_POST['new_password'];
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = ? WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$hashedPassword, $_SESSION['email']]);
        $message = "Votre mot de passe a été mis à jour avec succès.";
        $activeSection = 'security-settings';
    }

    // Mise à jour de l'adresse e-mail si ce n'est pas un compte Google
    if (!$isGoogleAccount && isset($_POST['update_email'])) {
        $newEmail = $_POST['new_email'];
        $sql = "UPDATE users SET email = ? WHERE email = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$newEmail, $_SESSION['email']]);
        $_SESSION['email'] = $newEmail;
        $message = "Votre adresse e-mail a été mise à jour avec succès.";
        $activeSection = 'security-settings';
    }
}

// Dossier où les fichiers ZIP sont stockés
$zipFolder = '/var/www/html/Sliceit/utilisateurs/fichier/';

// Fonction pour vérifier si un fichier est prêt
function isFileReady($filename) {
    return file_exists($filename) && filesize($filename) > 0;
}

// ID utilisateur depuis la session
$userId = $_SESSION['user_id'];

// Liste des fichiers ZIP de l'utilisateur
$userZipFiles = [];
foreach (glob($zipFolder . "{$userId}_*.zip") as $file) {
    $fileName = basename($file);
    $userZipFiles[] = [
        'name' => $fileName,
        'path' => "/Sliceit/utilisateurs/fichier/" . $fileName,
        'ready' => isFileReady($file)
    ];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - Gestion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #1e1e2e; color: #fff; }
        
        /* Sidebar */
        .sidebar { width: 220px; background-color: #232323; height: 100vh; position: fixed; top: 0; left: 0; padding: 20px; box-shadow: 2px 0 8px rgba(0, 0, 0, 0.3); }
        .sidebar h2 { color: #FF4081; text-align: center; font-size: 24px; margin-bottom: 30px; }
        .sidebar a { text-decoration: none; color: #a0aec0; padding: 15px 10px; display: block; font-size: 18px; margin-bottom: 10px; border-radius: 10px; transition: background-color 0.3s ease; }
        .sidebar a.active, .sidebar a:hover { background-color: #3f3f46; color: #FF4081; }

        /* Main Content */
        .main-content { margin-left: 250px; padding: 30px; width: calc(100% - 250px); background-color: #1e1e2e; min-height: 100vh; }
        h1 { font-size: 36px; color: #FF4081; margin-bottom: 20px; }
        .section { display: none; }
        .section.active { display: block; }
        .box { background-color: #282828; color: #fff; padding: 20px; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        
        .form-group label { color: #a0aec0; }
        .form-group input { width: 100%; padding: 10px; border-radius: 8px; border: none; margin-top: 10px; }
        .form-group button { background-color: #FF4081; color: #fff; padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; transition: background-color 0.3s ease; }
        .form-group button:hover { background-color: #e03568; }

        /* Loading and Success Icons */
        .loading-icon { border: 4px solid #f3f3f3; border-radius: 50%; border-top: 4px solid #FF4081; width: 20px; height: 20px; animation: spin 1s linear infinite; display: inline-block; margin-left: 10px; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .check-icon { color: #28a745; margin-left: 10px; font-size: 1.2em; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>Mon Compte</h2>
    <a href="javascript:void(0)" onclick="showSection('account-info')" class="active">Informations du compte</a>
    <a href="javascript:void(0)" onclick="showSection('security-settings')">Paramètres et sécurité</a>
    <a href="javascript:void(0)" onclick="showSection('products')">Produits</a>
    <a href="/authentification/logout.php" class="home-btn">Se déconnecter</a>
</div>

<div class="main-content">
    <!-- Section Informations du compte -->
    <div id="account-info" class="section active">
        <h1>Informations du compte</h1>
        <div class="box">
            <p><strong>Nom d'utilisateur :</strong> <?php echo $_SESSION['username']; ?></p>
            <p><strong>E-mail :</strong> <?php echo isset($_SESSION['email']) ? $_SESSION['email'] : 'Email non disponible'; ?></p>
        </div>
    </div>

    <!-- Section Paramètres et sécurité -->
    <div id="security-settings" class="section">
        <h1>Paramètres et sécurité</h1>
        <?php if ($message): ?>
            <div class="alert alert-success">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <div class="box">
            <form method="post">
                <div class="form-group">
                    <label for="new_password">Nouveau mot de passe</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                <button type="submit" name="update_password">Mettre à jour le mot de passe</button>
            </form>
        </div>

        <?php if (!$isGoogleAccount): ?>
            <div class="box">
                <form method="post">
                    <div class="form-group">
                        <label for="new_email">Nouvelle adresse e-mail</label>
                        <input type="email" id="new_email" name="new_email" value="<?php echo $_SESSION['email']; ?>">
                    </div>
                    <button type="submit" name="update_email">Mettre à jour l'adresse e-mail</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <!-- Section Produits -->
    <div id="products" class="section">
        <h1>Produits</h1>
        <div class="box">
            <h2>Découpages de vos vidéos</h2>
            <?php if (empty($userZipFiles)): ?>
                <p>Aucun fichier disponible pour le moment.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($userZipFiles as $zip): ?>
                        <li>
                            <span><?php echo htmlspecialchars($zip['name']); ?></span>
                            <?php if ($zip['ready']): ?>
                                <a href="<?php echo htmlspecialchars($zip['path']); ?>" class="btn btn-success" download>Télécharger</a>
                                <span class="check-icon">&#x2714;</span>
                            <?php else: ?>
                                <div class="loading-icon" title="En cours de traitement"></div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    function showSection(sectionId) {
        const sections = document.querySelectorAll('.section');
        sections.forEach(section => section.classList.remove('active'));
        document.getElementById(sectionId).classList.add('active');
    }
</script>

</body>
</html>
