<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - SliceIT</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: #fff;
        }

        .signup-form {
            background-color: #2b2b3f;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
            width: 400px;
            text-align: center;
        }

        .signup-form h2 {
            margin-bottom: 20px;
            color: #FF4081;
            font-size: 28px;
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
            border-radius: 8px;
            border: none;
            background: #3f3f46;
            color: #fff;
            font-size: 16px;
        }

        .signup-form button {
            width: 100%;
            padding: 12px;
            background-color: #FF4081;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            margin-top: 10px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .signup-form button:hover {
            background-color: #e03568;
        }

        .signup-form .google-btn {
            background-color: #4285F4;
            margin-top: 20px;
            padding: 12px;
            border-radius: 8px;
            color: white;
            cursor: pointer;
            border: none;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .signup-form .google-btn:hover {
            background-color: #357ae8;
        }

        .error-message {
            color: #ff4081;
            font-size: 14px;
            margin-top: 10px;
        }

        .divider {
            margin: 20px 0;
            font-size: 14px;
            color: #999;
            position: relative;
        }

        .divider::before, .divider::after {
            content: "";
            height: 1px;
            width: 40%;
            background-color: #555;
            display: inline-block;
            position: absolute;
            top: 50%;
        }

        .divider::before {
            left: 0;
        }

        .divider::after {
            right: 0;
        }

        .password-requirements {
            text-align: left;
            margin-bottom: 10px;
        }

        .password-requirements p {
            margin: 5px 0;
            font-size: 14px;
        }

        .password-requirements p.valid {
            color: #28a745;
        }

        .password-requirements p.invalid {
            color: #ff4081;
        }
    </style>
</head>
<body>

<div class="signup-form">
    <h2>Créer un compte</h2>

    <!-- Message d'erreur si l'email est déjà utilisé -->
    <?php if (isset($_GET['error']) && $_GET['error'] == 'email_taken'): ?>
        <div class="error-message">
            L'adresse e-mail est déjà utilisée pour un autre compte.
        </div>
    <?php endif; ?>

    <form action="/authentification/signup_process.php" method="POST">
        <div class="input-group">
            <label for="username">Nom d'utilisateur</label>
            <input type="text" id="username" name="username" required>
        </div>
        <div class="input-group">
            <label for="email">Adresse e-mail</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="input-group">
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required oninput="validatePassword()">
        </div>
        <div class="input-group">
            <label for="confirm_password">Confirmer le mot de passe</label>
            <input type="password" id="confirm_password" name="confirm_password" required oninput="validatePasswordMatch()">
        </div>

        <!-- Exigences du mot de passe -->
        <div class="password-requirements">
            <p id="uppercase" class="invalid">Au moins 1 majuscule</p>
            <p id="number" class="invalid">Au moins 1 chiffre</p>
            <p id="special" class="invalid">Au moins 1 caractère spécial</p>
            <p id="match" class="invalid">Les mots de passe doivent correspondre</p>
        </div>

        <button type="submit" id="submit-btn" disabled>S'inscrire</button>
    </form>

    <div class="divider">ou</div>

    <!-- Bouton Inscription avec Google -->
    <a href="/authentification/login2.php">
        <button class="google-btn">S'inscrire avec Google</button>
    </a>
</div>

<script>
    function validatePassword() {
        const password = document.getElementById('password').value;

        // Vérification de la majuscule
        const hasUppercase = /[A-Z]/.test(password);
        document.getElementById('uppercase').classList.toggle('valid', hasUppercase);
        document.getElementById('uppercase').classList.toggle('invalid', !hasUppercase);

        // Vérification du chiffre
        const hasNumber = /[0-9]/.test(password);
        document.getElementById('number').classList.toggle('valid', hasNumber);
        document.getElementById('number').classList.toggle('invalid', !hasNumber);

        // Vérification du caractère spécial
        const hasSpecial = /[!@#\$%\^\&*\)\(+=._-]/.test(password);
        document.getElementById('special').classList.toggle('valid', hasSpecial);
        document.getElementById('special').classList.toggle('invalid', !hasSpecial);

        // Valider la correspondance des mots de passe
        validatePasswordMatch();

        // Activer le bouton si toutes les conditions sont remplies
        checkRequirements();
    }

    function validatePasswordMatch() {
        const password = document.getElementById('password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        const match = password === confirmPassword && password.length > 0;
        document.getElementById('match').classList.toggle('valid', match);
        document.getElementById('match').classList.toggle('invalid', !match);

        // Activer le bouton si toutes les conditions sont remplies
        checkRequirements();
    }

    function checkRequirements() {
        const allValid = document.querySelectorAll('.password-requirements .valid').length === 4;
        document.getElementById('submit-btn').disabled = !allValid;
    }
</script>

</body>
</html>
