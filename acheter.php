<?php
session_start();
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

// Connexion à la base de données
$conn = new mysqli('localhost', 'Fly', 'FNuhfzifjzf64', 'site_web');
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT stripe_customer_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($stripe_customer_id);
$stmt->fetch();
$stmt->close();

// Simulation d'un produit sélectionné
$product = [
    'id' => 1,
    'name' => 'Produit 1',
    'description' => 'Ceci est la description de Produit 1.',
    'price' => 3.99,
    'image' => '/mon_projet/assets/product1.jpg'
];

// Vérification si l'utilisateur a un stripe_customer_id
if (!$stripe_customer_id) {
    echo "Erreur : Aucun stripe_customer_id trouvé pour cet utilisateur.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acheter <?php echo $product['name']; ?> - SliceIT</title>
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
        }

        /* Navigation */
        nav {
            background: linear-gradient(135deg, #232323, #3f3f46);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            z-index: 1000;
        }

        .logo {
            font-size: 30px;
            font-weight: 700;
            color: #FF4081;
        }

        .nav-links a {
            color: #ffffff;
            padding: 12px 25px;
            border: 2px solid transparent;
            border-radius: 50px;
            text-decoration: none;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .nav-links a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-color: #FF4081;
            color: #FF4081;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        /* Main Content */
        .main-content {
            padding: 100px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #1e1e2e, #3f3f46);
        }

        .product-details {
            max-width: 50%;
        }

        .product-details h1 {
            font-size: 48px;
            color: #FF4081;
        }

        .product-details p {
            font-size: 18px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .product-details .price {
            font-size: 32px;
            color: #00bcd4;
            margin-bottom: 30px;
        }

        .product-details .payment-options {
            display: flex;
            gap: 20px;
        }

        .payment-options button {
            padding: 15px 30px;
            background: #FF4081;
            color: #fff;
            border-radius: 50px;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .payment-options button:hover {
            background: #e03568;
        }

        .product-image {
            max-width: 40%;
        }

        .product-image img {
            max-width: 100%;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }

        /* Footer */
        footer {
            background: #2b2b3f;
            padding: 20px;
            text-align: center;
            color: #fff;
            font-size: 16px;
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav>
    <div class="logo">
        <img src="/mon_projet/assets/logo1.png" alt="SliteIT Logo">
    </div>
    <div class="nav-links">
        <a href="/mon_projet/index.php">Accueil</a>
        <a href="produits.php">Produits</a>
        <a href="about.php">À propos</a>
        <a href="contact.php">Contact</a>
    </div>
</nav>

<!-- Main Content -->
<section class="main-content">
    <div class="product-details">
        <h1><?php echo $product['name']; ?></h1>
        <p><?php echo $product['description']; ?></p>
        <p class="price">Prix : €<?php echo number_format($product['price'], 2); ?></p>

        <!-- Options de paiement -->
        <div class="payment-options">
            <form action="/paiement/stripe_payment.php" method="POST">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <input type="hidden" name="product_price" value="<?php echo $product['price']; ?>">
                <input type="hidden" name="stripe_customer_id" value="<?php echo $stripe_customer_id; ?>">
                <button type="submit">Payer avec Stripe</button>
            </form>
        </div>
    </div>

    <div class="product-image">
        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
    </div>
</section>

<!-- Footer -->
<footer>
    © 2024 SliteIT. Tous droits réservés.
</footer>

</body>
</html>
