<?php
session_start();
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Vérifiez si le panier contient des produits
$panier = isset($_SESSION['panier']) ? $_SESSION['panier'] : [];
$total = 0;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panier - SliceIT</title>
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
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            width: 80%;
            max-width: 800px;
            background-color: #2b2b3f;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h1 {
            color: #FF4081;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 15px;
            text-align: left;
            background-color: #3f3f46;
        }

        table th {
            background-color: #FF4081;
            color: white;
        }

        .total {
            font-size: 24px;
            margin: 20px 0;
        }

        .payment-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .payment-button {
            padding: 15px 30px;
            background-color: #FF4081;
            color: white;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }

        .payment-button:hover {
            background-color: #e03568;
        }

        .empty-cart {
            font-size: 18px;
            margin: 20px 0;
        }
    </style>
</head>
<body>

    <div class="container">
        <h1>Votre Panier</h1>

        <!-- Affichage du contenu du panier -->
        <?php if (!empty($panier)): ?>
            <table>
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Prix</th>
                        <th>Quantité</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($panier as $produit): ?>
                        <?php
                        $totalProduit = $produit['prix'] * $produit['quantite'];
                        $total += $totalProduit;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($produit['nom']) ?></td>
                            <td><?= number_format($produit['prix'], 2) ?> €</td>
                            <td><?= $produit['quantite'] ?></td>
                            <td><?= number_format($totalProduit, 2) ?> €</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="total">Total: <?= number_format($total, 2) ?> €</div>

            <!-- Boutons de paiement -->
            <div class="payment-buttons">
                <form action="payment_paypal.php" method="POST">
                    <button type="submit" class="payment-button">Payer avec PayPal</button>
                </form>
                <form action="payment_stripe.php" method="POST">
                    <button type="submit" class="payment-button">Payer avec Stripe</button>
                </form>
            </div>

        <?php else: ?>
            <p class="empty-cart">Votre panier est vide.</p>
        <?php endif; ?>
    </div>

</body>
</html>
