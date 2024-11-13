<?php
session_start();
$isLoggedIn = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;

$conn = new mysqli('localhost', 'Fly', 'FNuhfzifjzf64', 'site_web');
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

$today = date('Y-m-d');

// Vérifier si une entrée pour la date d'aujourd'hui existe déjà
$query_check_visits = "SELECT visit_count FROM daily_visits WHERE visit_date = ?";
$stmt_check = $conn->prepare($query_check_visits);
$stmt_check->bind_param("s", $today);
$stmt_check->execute();
$stmt_check->store_result();

// Si l'entrée existe, on incrémente le compteur
if ($stmt_check->num_rows > 0) {
    $stmt_check->bind_result($visit_count);
    $stmt_check->fetch();
    $visit_count++;

    // Mise à jour du compteur dans la base de données
    $query_update_visits = "UPDATE daily_visits SET visit_count = ? WHERE visit_date = ?";
    $stmt_update = $conn->prepare($query_update_visits);
    $stmt_update->bind_param("is", $visit_count, $today);
    $stmt_update->execute();
    $stmt_update->close();
} else {
    // Si aucune visite n'est enregistrée pour aujourd'hui, en créer une nouvelle
    $visit_count = 1;
    $query_insert_visits = "INSERT INTO daily_visits (visit_date, visit_count) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($query_insert_visits);
    $stmt_insert->bind_param("si", $today, $visit_count);
    $stmt_insert->execute();
    $stmt_insert->close();
}



$stmt_check->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SliceIT - Accueil</title>
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

        .nav-links {
            display: flex;
            gap: 30px;
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

        .nav-buttons {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        /* User account dropdown */
        .user-account {
            position: relative;
            cursor: pointer;
        }

        .user-account img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 2px solid #FF4081;
            transition: box-shadow 0.3s ease;
        }

        .user-account:hover img {
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.4);
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            top: 60px;
            right: 0;
            width: 200px;
            background: #2b2b3f;
            border-radius: 12px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            padding: 15px;
            text-align: left;
            z-index: 1000;
            opacity: 0;
            transform: translateY(-10px);
            transition: opacity 0.3s ease, transform 0.3s ease;
        }

        .dropdown-menu.show {
            display: block;
            opacity: 1;
            transform: translateY(0);
        }

        .dropdown-menu p {
            margin: 0 0 10px;
            font-weight: 600;
            color: #FF4081;
        }

        .dropdown-menu a {
            display: block;
            color: #fff;
            padding: 10px 0;
            text-decoration: none;
            transition: background-color 0.3s ease, transform 0.3s ease;
        }

        .dropdown-menu a:hover {
            background-color: #3f3f46;
        }

        /* Hero Section */
        .hero-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 100vh;
            padding: 100px 50px;
            background: linear-gradient(135deg, #1e1e2e, #3f3f46);
        }

        .hero-text {
            max-width: 50%;
        }

        .hero-text h1 {
            font-size: 64px;
            color: #FF4081;
            background: linear-gradient(135deg, #ff4081, #ff80ab);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            line-height: 1.2;
        }

        .hero-text h1 span {
            color: #00bcd4;
        }

        .hero-text p {
            font-size: 22px;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .hero-button {
            display: inline-block;
            padding: 15px 40px;
            background: #FF4081;
            color: #fff;
            border-radius: 50px;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .hero-button:hover {
            background: #e03568;
        }

        /* Products Section */
        .products-section {
            padding: 60px 50px;
            background: #2b2b3f;
        }

        .products-section h2 {
            font-size: 36px;
            color: #fff;
            text-align: center;
            margin-bottom: 40px;
        }

        .products {
            display: flex;
            justify-content: space-around;
            gap: 30px;
        }

        .product-item {
            background: #3f3f46;
            border-radius: 12px;
            padding: 20px;
            color: #fff;
            text-align: center;
            transition: transform 0.3s ease;
        }

        .product-item:hover {
            transform: translateY(-10px);
        }

        .product-item img {
            max-width: 100%;
            height: 200px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .product-item h3 {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .product-item p {
            font-size: 16px;
        }

        /* Info Section */
        .info-section {
            padding: 80px 50px;
            background: #1e1e2e;
        }

        .info-section h2 {
            font-size: 36px;
            color: #FF4081;
            text-align: center;
            margin-bottom: 40px;
        }

        .info-section p {
            font-size: 20px;
            color: #fff;
            max-width: 800px;
            margin: 0 auto;
            text-align: center;
            line-height: 1.6;
        }

        /* Footer */
        footer {
            background: #2b2b3f;
            padding: 20px;
            text-align: center;
            color: #fff;
            font-size: 16px;
        }

        .logo img {
        width: 200px;  /* Ajuste la taille de l'image */
        height: auto;  /* Conserve les proportions */
        }

        .cart-icon {
    font-size: 24px;
    color: #FF4081;
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.3s ease;
}

.cart-icon:hover {
    color: #ffffff;
}

.cart-icon i {
    font-size: 28px;
    padding: 10px;
}

/* Optionnel : ajouter un badge pour afficher le nombre d'articles dans le panier */
.cart-icon::after {
    content: "3"; /* Remplacez le chiffre par une variable PHP si nécessaire */
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #FF4081;
    color: white;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 12px;
}


.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background-color: #FF4081;
    color: white;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 12px;
}
    </style>
</head>
<body>

<!-- Navigation -->
<nav>
<div class="logo">
    <img src="/assets/logo1.png" alt="SliteIT Logo">
</div>
    <div class="nav-links">
        <a href="index.php">Accueil</a>
        <a href="produits.php">Produits</a>
        <a href="about.php">À propos</a>
        <a href="contact.php">Contact</a>
    </div>
    <div class="nav-buttons">


<?php
$cartItemCount = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<a href="/panier.php" class="cart-icon">
    <i class="fas fa-shopping-cart"></i>
    <?php if ($cartItemCount > 0): ?>
        <span class="cart-count"><?php echo $cartItemCount; ?></span>
    <?php endif; ?>
</a>
    </a>

        <?php if (!$isLoggedIn): ?>
            <div class="user-account">
                <img src="/assets/perso.png" alt="Avatar utilisateur">
                <div class="dropdown-menu">
                    <p>Vous n'êtes pas connecté.</p>
                    <a href="/authentification/login.php">Connexion</a>
                    <a href="/authentification/signup.php">Créer un compte</a>
                </div>
            </div>
        <?php else: ?>
            <div class="user-account">
                <img src="/assets/perso.png" alt="Avatar utilisateur">
                <div class="dropdown-menu">
                    <p>Bonjour, <?php echo $_SESSION['username']; ?>.</p>
                    <?php if ($_SESSION['is_admin']): ?>
                        <a href="/admin/admin_panel.php">Panel administrateur</a>
                    <?php endif; ?>
                    <a href="/utilisateurs/account.php">Mon compte</a>
                    <a href="/authentification/logout.php">Se déconnecter</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</nav>

<!-- Hero Section -->
<section class="hero-section">
    <div class="hero-text">
        <h1>Faites vos vidéos TikTok <span>en 2 clics</span></h1>
        <p>Notre produit vous permet de couper et monter vos vidéos TikTok en toute simplicité. Découvrez comment cela peut vous aider à gagner du temps et à améliorer vos performances.</p>
        <a href="https://sliceit.fr/paiement/acheter.php" class="hero-button">Acheter maintenant</a>
    </div>
</section>

<!-- Products Section -->
<section class="products-section">
    <h2>Nos Produits</h2>
    <div class="products">
        <div class="product-item">
            <img src="/assets/product1.jpg" alt="Produit 1">
            <h3>Produit 1</h3>
            <p>Description du produit 1.</p>
        </div>
        <div class="product-item">
            <img src="/assets/product2.jpg" alt="Produit 2">
            <h3>Produit 2</h3>
            <p>Description du produit 2.</p>
        </div>
        <div class="product-item">
            <img src="/assets/product3.jpg" alt="Produit 3">
            <h3>Produit 3</h3>
            <p>Description du produit 3.</p>
        </div>
    </div>
</section>

<!-- Info Section -->
<section class="info-section">
    <h2>Pourquoi choisir SliteIT ?</h2>
    <p>Avec notre technologie avancée et notre interface intuitive, SliteIT vous aide à transformer vos idées en vidéos percutantes. Que vous soyez un créateur expérimenté ou un débutant, notre produit est conçu pour répondre à tous vos besoins.</p>
</section>

<!-- Footer -->
<footer>
    © 2024 SliteIT. Tous droits réservés. 
</footer>

<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
<script>
    const userAccount = document.querySelector('.user-account');
    const dropdownMenu = document.querySelector('.dropdown-menu');
    let hideTimeout;

    userAccount.addEventListener('mouseenter', () => {
        clearTimeout(hideTimeout);
        dropdownMenu.classList.add('show');
    });

    userAccount.addEventListener('mouseleave', () => {
        hideTimeout = setTimeout(() => {
            dropdownMenu.classList.remove('show');
        }, 500);
    });

    dropdownMenu.addEventListener('mouseenter', () => {
        clearTimeout(hideTimeout);
    });

    dropdownMenu.addEventListener('mouseleave', () => {
        hideTimeout = setTimeout(() => {
            dropdownMenu.classList.remove('show');
        }, 500);
    });


    
</script>

</body>
</html>
