<?php
session_start();

// Inclure l'autoload de Composer pour utiliser Stripe
require_once __DIR__ . '/../vendor/autoload.php';

// Connexion à la base de données
$conn = new mysqli('localhost', 'Fly', 'FNuhfzifjzf64', 'site_web');

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Clé secrète Stripe
\Stripe\Stripe::setApiKey('sk_test_51QBLqtAoiPPvcG4DhDxFdc3pDLP03ivKlZGUDfgrTUDkBjcrOHFurfYtNTgF96PzLka2mDBb2mv382y6bhTWeFuT00WTAXT0Yl');  // Remplace par ta clé secrète Stripe

// Récupérer les données du formulaire
$username = $_POST['username'];
$email = $_POST['email'];
$password = $_POST['password'];
$confirm_password = $_POST['confirm_password'];

// Validation côté serveur : vérification du mot de passe
$uppercase = preg_match('@[A-Z]@', $password);
$number = preg_match('@[0-9]@', $password);
$specialChars = preg_match('@[^\w]@', $password);

if (!$uppercase || !$number || !$specialChars || strlen($password) < 8) {
    header("Location: /authentification/signup.php?error=weak_password");
    exit;
}

if ($password !== $confirm_password) {
    header("Location: /authentification/signup.php?error=password_mismatch");
    exit;
}

// Hacher le mot de passe avant de le stocker
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Vérifier si l'email existe déjà
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    header("Location: /authentification/signup.php?error=email_taken");
    exit;
} else {
    // Insérer l'utilisateur dans la base de données sans l'ID Stripe pour l'instant
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("sss", $username, $email, $hashed_password);
    
    if ($stmt->execute()) {
        // Récupérer l'ID de l'utilisateur dans la base de données
        $user_id = $conn->insert_id;

        // Créer un client Stripe pour cet utilisateur
        try {
            $customer = \Stripe\Customer::create([
                'email' => $email,
                'name' => $username,
            ]);

            // Récupérer l'ID du client Stripe
            $stripeCustomerId = $customer->id;

            // Mettre à jour l'utilisateur avec l'ID du client Stripe
            $stmt = $conn->prepare("UPDATE users SET stripe_customer_id = ? WHERE id = ?");
            $stmt->bind_param("si", $stripeCustomerId, $user_id);
            $stmt->execute();

            // Connexion de l'utilisateur
            $_SESSION['user_id'] = $user_id;
            $_SESSION['loggedin'] = true;

            // Rediriger vers la page d'accueil après l'inscription
            header("Location: /authentification/login.php");
            exit;
        } catch (Exception $e) {
            // Si la création du client Stripe échoue, supprimer l'utilisateur de la base de données
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();

            echo "Erreur lors de la création du compte Stripe : " . $e->getMessage();
            exit;
        }
    } else {
        echo "Erreur lors de l'inscription : " . $stmt->error;
    }
}

$stmt->close();
$conn->close();
