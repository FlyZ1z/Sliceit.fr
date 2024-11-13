<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Clé secrète Stripe
\Stripe\Stripe::setApiKey('sk_test_51QBLqtAoiPPvcG4DhDxFdc3pDLP03ivKlZGUDfgrTUDkBjcrOHFurfYtNTgF96PzLka2mDBb2mv382y6bhTWeFuT00WTAXT0Yl');  // Remplace par ta clé secrète Stripe

// Récupérer les données du formulaire
$product_id = $_POST['product_id'];
$product_price = $_POST['product_price'];
$stripe_customer_id = $_POST['stripe_customer_id'];

try {
    // Créer une session Stripe Checkout
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'customer' => $stripe_customer_id,  // Utiliser l'ID client Stripe récupéré
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => 'Produit 1',
                ],
                'unit_amount' => $product_price * 100,  // Prix en centimes (Stripe gère les montants en centimes)
            ],
            'quantity' => 1,
        ]],
        'mode' => 'payment',  // Peut aussi être 'subscription' pour des abonnements
        'success_url' => 'https://sliceit.fr/paiement/success.php?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'https://sliceit.fr/paiement/cancel.php',
    ]);

    // Rediriger vers la page de paiement Stripe
    header("Location: " . $session->url);
    exit();
} catch (Exception $e) {
    echo "Erreur lors de la création de la session de paiement : " . $e->getMessage();
}
