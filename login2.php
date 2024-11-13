<?php 
require __DIR__ . '/../admin/config.php';

    // Construction de l'URL de redirection Google OAuth
    $redirect_url = "https://accounts.google.com/o/oauth2/v2/auth?scope=email%20profile&access_type=online&redirect_uri=" . urlencode('http://sliceit.fr/connect.php') . "&response_type=code&client_id=" . GOOGLE_ID;
?>  

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SliteIT - Connexion</title>

    <!-- Script de redirection automatique -->
    <script type="text/javascript">
        // Redirection vers l'URL Google OAuth dès que la page est chargée
        window.location.href = "<?= $redirect_url ?>";
    </script>
</head>  
<body>
    <h1>Redirection vers Google pour la connexion...</h1>
</body>
</html>
