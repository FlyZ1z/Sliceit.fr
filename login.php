<?php
session_start();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SliceIT - Connexion</title>
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
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .login-container {
            background: #2b2b3f;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
            max-width: 400px;
            width: 100%;
            text-align: center;
        }

        h1 {
            font-size: 32px;
            margin-bottom: 20px;
            color: #FF4081;
            background: linear-gradient(135deg, #ff4081, #ff80ab);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .error-message {
            color: #ff4081;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            font-size: 16px;
            color: #fff;
            margin-bottom: 5px;
            display: block;
        }

        .input-group input {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            border: none;
            border-radius: 8px;
            background: #3f3f46;
            color: #fff;
        }

        button {
            padding: 12px;
            background-color: #FF4081;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            cursor: pointer;
            margin-top: 20px;
            width: 100%;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #e03568;
        }

        .google-signup {
            margin-top: 20px;
        }

        .google-signup p {
            color: #999;
            font-size: 14px;
        }

        .google-signup a {
            display: inline-block;
            padding: 10px 20px;
            background-color: #4285F4;
            color: white;
            border-radius: 50px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }

        .google-signup a:hover {
            background-color: #357ae8;
        }

        .signup-link {
            margin-top: 20px;
            font-size: 14px;
        }

        .signup-link a {
            color: #FF4081;
            text-decoration: none;
        }

        .signup-link a:hover {
            text-decoration: underline;
        }

    </style>
</head>
<body>

<div class="login-container">
    <h1>Connexion</h1>

    <!-- Vérification des erreurs et affichage -->
    <?php if (isset($_GET['error'])): ?>
        <div class="error-message">
            <?php if ($_GET['error'] == 'wrong_password'): ?>
                Mot de passe incorrect.
            <?php elseif ($_GET['error'] == 'user_not_found'): ?>
                Utilisateur introuvable.
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form action="/authentification/login_process.php" method="POST">
        <div class="input-group">
            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="input-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required>
        </div>

        <button type="submit">Se connecter</button>
    </form>

    <!-- Option de connexion avec Google -->
    <div class="google-signup">
        <p>Ou</p>
        <a href="/authentification/login2.php">
            <img src="/assets/google-logo.png" alt="Google logo" /> Se connecter avec Google
        </a>
    </div>

    <div class="signup-link">
        Pas encore inscrit ? <a href="/authentification/signup.php">Créer un compte</a>
    </div>
</div>

</body>
</html>
