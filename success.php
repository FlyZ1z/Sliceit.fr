<?php
session_start();

if (!isset($_GET['session_id'])) {
    echo "Erreur : Aucun ID de session trouvé.";
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

// Clé secrète Stripe
\Stripe\Stripe::setApiKey('sk_test_51QBLqtAoiPPvcG4DhDxFdc3pDLP03ivKlZGUDfgrTUDkBjcrOHFurfYtNTgF96PzLka2mDBb2mv382y6bhTWeFuT00WTAXT0Yl');

// Récupérer l'ID de la session de paiement
$session_id = $_GET['session_id'];

try {
    // Récupérer les informations de la session de paiement
    $session = \Stripe\Checkout\Session::retrieve($session_id);
    $customer = \Stripe\Customer::retrieve($session->customer);
} catch (Exception $e) {
    echo "Erreur lors de la récupération de la session de paiement : " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement réussi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            color: #333;
            text-align: center;
            padding: 50px;
        }
        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: inline-block;
        }
        .success-message h1 {
            margin-bottom: 20px;
        }
        .success-message p {
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="success-message">
    <h1>Merci pour votre achat !</h1>
    <p>Paiement réussi pour <?php echo $customer->email; ?>.</p>
    <p>Votre commande est en cours de traitement.</p>
</div>

</body>
</html>
