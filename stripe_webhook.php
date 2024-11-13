<?php
require_once('vendor/autoload.php');
\Stripe\Stripe::setApiKey('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');  // Remplacez par votre clé secrète Stripe

// Lire le corps de la requête envoyée par Stripe
$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$endpoint_secret = 'whsec_FBTgziZ5ykm8I2PEDIwY9EEbXJW3h7n4';  // Remplacez par le secret du webhook de Stripe

// Vérifier que la requête provient bien de Stripe
try {
    $event = \Stripe\Webhook::constructEvent(
        $payload, $sig_header, $endpoint_secret
    );
} catch(\UnexpectedValueException $e) {
    // Mauvais payload
    http_response_code(400);
    exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
    // Signature invalide
    http_response_code(400);
    exit();
}

// Gérer différents types d'événements Stripe
if ($event->type == 'checkout.session.completed') {
    $session = $event->data->object;

    // Exemple de traitement après paiement réussi
    $user_id = $session->metadata->user_id;  // Utilisez les métadonnées pour lier l'utilisateur
    $product_id = $session->metadata->product_id;
    $payment_status = $session->payment_status;

    if ($payment_status == 'paid') {
        // Connexion à la base de données
        $conn = new mysqli('localhost', 'root', '', 'site_web');
        if ($conn->connect_error) {
            die("Erreur de connexion à la base de données : " . $conn->connect_error);
        }

        // Définir les dates d'abonnement
        $start_date = date('Y-m-d');
        $end_date = date('Y-m-d', strtotime('+30 days'));

        // Insérer l'abonnement dans la table
        $query = "INSERT INTO subscriptions (user_id, product_id, start_date, end_date, status) 
                  VALUES (?, ?, ?, ?, 'active')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiss", $user_id, $product_id, $start_date, $end_date);

        if ($stmt->execute()) {
            error_log("Abonnement activé avec succès pour l'utilisateur $user_id");
        } else {
            error_log("Erreur lors de l'activation de l'abonnement.");
        }

        $stmt->close();
        $conn->close();
    }
}

// Répondre à Stripe pour confirmer que nous avons reçu l'événement
http_response_code(200);
?>
