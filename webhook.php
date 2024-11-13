<?php
require 'vendor/autoload.php';  // Charge la bibliothèque Stripe

// Clé secrète Stripe
\Stripe\Stripe::setApiKey('sk_test_51QBLqtAoiPPvcG4DhDxFdc3pDLP03ivKlZGUDfgrTUDkBjcrOHFurfYtNTgF96PzLka2mDBb2mv382y6bhTWeFuT00WTAXT0Yl');  // Remplace par ta clé secrète

// Secret du webhook
$endpoint_secret = 'whsec_MVHvNujwMeEuDdmQXkoRPcypIsUiTtGk';  // Ton secret de webhook

// Connexion à la base de données MySQL
$pdo = new PDO('mysql:host=localhost;dbname=site_web', 'Fly', 'FNuhfzifjzf64');

// Récupérer la charge utile envoyée par Stripe
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];

try {
    // Vérifier que l'événement est authentique
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch (\UnexpectedValueException $e) {
    // Charge utile invalide
    http_response_code(400);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    // Signature invalide
    http_response_code(400);
    exit();
}

// Traiter l'événement Stripe
if ($event['type'] == 'checkout.session.completed') {
    $session = $event['data']['object'];  // Récupérer les détails de la session Checkout

    // Obtenir l'ID client Stripe et l'email du client
    $customerId = $session['customer'];  // ID Stripe du client (customer_id)
    $email = $session['customer_details']['email'];  // Email du client

    // Rechercher l'utilisateur par email dans la base de données
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Calculer la date de fin d'abonnement (par exemple, 30 jours après la date actuelle)
        $subscriptionEnd = date('Y-m-d H:i:s', strtotime('+30 days'));

        // Mettre à jour la base de données avec l'ID client Stripe et la date de fin d'abonnement
        $stmt = $pdo->prepare("UPDATE users SET stripe_customer_id = :stripe_customer_id, subscription_end = :subscription_end WHERE id = :id");
        $stmt->execute([
            'stripe_customer_id' => $customerId,
            'subscription_end' => $subscriptionEnd,
            'id' => $user['id'],
        ]);

        // Répondre à Stripe pour confirmer la réception de l'événement
        http_response_code(200);
    } else {
        // L'utilisateur n'a pas été trouvé dans la base de données
        http_response_code(404);
        echo json_encode(['error' => 'Utilisateur non trouvé']);
    }
} else {
    // Si l'événement reçu n'est pas celui qu'on attendait
    http_response_code(400);
    echo json_encode(['error' => 'Événement inattendu']);
}
