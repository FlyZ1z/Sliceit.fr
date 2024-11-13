<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Détails du produit
    $produit_id = $_POST['produit_id'];
    $produit_nom = $_POST['produit_nom'];
    $produit_prix = $_POST['produit_prix'];
    $produit_quantite = $_POST['produit_quantite'];

    // Initialiser le panier si non existant
    if (!isset($_SESSION['panier'])) {
        $_SESSION['panier'] = [];
    }

    // Si le produit existe déjà dans le panier, mettre à jour la quantité
    if (isset($_SESSION['panier'][$produit_id])) {
        $_SESSION['panier'][$produit_id]['quantite'] += $produit_quantite;
    } else {
        // Sinon, ajouter le nouveau produit dans le panier
        $_SESSION['panier'][$produit_id] = [
            'nom' => $produit_nom,
            'prix' => $produit_prix,
            'quantite' => $produit_quantite
        ];
    }

    // Redirection après ajout au panier
    header('Location: panier.php');
    exit();
}
?>
