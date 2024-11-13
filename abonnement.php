<?php
require '../vendor/autoload.php';  // Stripe PHP library

// Clé secrète Stripe
\Stripe\Stripe::setApiKey('sk_test_51QBLqtAoiPPvcG4DhDxFdc3pDLP03ivKlZGUDfgrTUDkBjcrOHFurfYtNTgF96PzLka2mDBb2mv382y6bhTWeFuT00WTAXT0Yl');

// Connexion à la base de données MySQL
$pdo = new PDO('mysql:host=localhost;dbname=site_web', 'Fly', 'Bongo64340!?!');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];

    // Rechercher l'utilisateur par email
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
        exit();
    }

    try {
        // Créer une session Stripe Checkout
        $session = \Stripe\Checkout\Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price' => 'price_1234567890',  // Remplace par l'ID du prix de l'abonnement
                'quantity' => 1,
            ]],
            'mode' => 'subscription',
            'success_url' => 'https://sliceit.fr/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => 'https://sliceit.fr/cancel',
        ]);

        // Afficher l'URL de paiement à l'utilisateur
        echo json_encode(['url' => $session->url]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => $e->getMessage()]);
    }
}
