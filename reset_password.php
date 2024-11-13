<?php
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $new_password = password_hash($_POST['password'], PASSWORD_BCRYPT);

        // Connexion à la base de données
        $conn = new mysqli('localhost', 'root', '', 'site_web');

        if ($conn->connect_error) {
            die("Erreur de connexion à la base de données : " . $conn->connect_error);
        }

        // Vérifier le token et récupérer l'email
        $stmt = $conn->prepare("SELECT email FROM password_resets WHERE token = ?");
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $stmt->bind_result($email);
        $stmt->fetch();
        $stmt->close();

        if ($email) {
            // Mettre à jour le mot de passe de l'utilisateur
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $new_password, $email);
            $stmt->execute();
            $stmt->close();

            // Supprimer le token après réinitialisation
            $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();

            echo "Votre mot de passe a été réinitialisé avec succès.";
        } else {
            echo "Token invalide.";
        }

        $conn->close();
    }
} else {
    echo "Token manquant.";
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialiser le mot de passe</title>
</head>
<body>
    <h1>Réinitialiser votre mot de passe</h1>
    <form action="/mon_projet/authentification/reset_password.php?token=<?php echo $token; ?>" method="POST">
        <label for="password">Nouveau mot de passe :</label>
        <input type="password" name="password" required>
        <button type="submit">Réinitialiser le mot de passe</button>
    </form>
</body>
</html>
