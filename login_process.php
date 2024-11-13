<?php
session_start();
print_r($_SESSION);
// Connexion à la base de données
$conn = new mysqli('localhost', 'Fly', 'FNuhfzifjzf64', 'site_web');

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer les données du formulaire
$email = $_POST['email'];
$password = $_POST['password'];

// Vérifier si l'utilisateur existe
$stmt = $conn->prepare("SELECT id, username, password, email, is_admin FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($user_id, $username, $hashed_password, $email, $is_admin);
    $stmt->fetch();

    // Vérifier le mot de passe
    if (password_verify($password, $hashed_password)) {
        // Stocker les informations de l'utilisateur dans la session, y compris l'email et le statut admin
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        $_SESSION['is_admin'] = $is_admin;  // Stocke la valeur de is_admin dans la session
        $_SESSION['loggedin'] = true;

        // Redirection vers la page d'accueil après une connexion réussie
        header("Location: /index.php");
        exit;
    } else {
        // Mot de passe incorrect, redirection vers login.php avec un message d'erreur
        header("Location: /Autentification/login.php?error=wrong_password");
        exit;
    }
} else {
    // Utilisateur non trouvé, redirection vers login.php avec un message d'erreur
    header("Location: /Autentificationlogin.php?error=user_not_found");
    exit;
}

$stmt->close();
$conn->close();
?>
