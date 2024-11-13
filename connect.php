<?php
session_start();
print_r($_SESSION);
require('admin/config.php');

// Vérification de la présence du paramètre "code" dans l'URL
if (!isset($_GET['code'])) {
    die('Erreur : le paramètre "code" est manquant.');
}

// Récupération du code d'autorisation dans l'URL
$code = $_GET['code'];

// Paramètres pour échanger le code contre un token d'accès
$client_id = '229919859520-jegm98fpkh04cb5jo4mk7pc488f5fas3.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = 'http://sliceit.fr/connect.php';

// Construction des données de la requête POST pour obtenir le token d'accès
$data = [
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code',
];

// Initialisation de la requête cURL pour échanger le code contre le token
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oauth2.googleapis.com/token");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
]);

// Exécution de la requête et récupération de la réponse
$response = curl_exec($ch);
curl_close($ch);

// Décodage de la réponse JSON
$responseData = json_decode($response, true);

// Vérifier si le token d'accès a été récupéré
if (isset($responseData['access_token'])) {
    // Récupérer le token d'accès
    $access_token = $responseData['access_token'];

    // Récupération des informations utilisateur depuis Google
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://www.googleapis.com/oauth2/v1/userinfo?alt=json&access_token=" . $access_token);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $userInfo = curl_exec($ch);
    curl_close($ch);

    $userInfo = json_decode($userInfo, true);

    if (isset($userInfo['email'])) {
        $email = $userInfo['email'];
        $google_id = $userInfo['id'];

        // Connexion à la base de données
        $conn = new mysqli('localhost', 'Fly', 'FNuhfzifjzf64', 'site_web');

        if ($conn->connect_error) {
            die("Erreur de connexion à la base de données : " . $conn->connect_error);
        }

        // Vérifier si l'utilisateur existe dans la base de données
        $stmt = $conn->prepare("SELECT id, username, is_admin FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $username, $is_admin);
            $stmt->fetch();

            // Mettre à jour le google_id de l'utilisateur existant
            $updateStmt = $conn->prepare("UPDATE users SET google_id = ? WHERE email = ?");
            $updateStmt->bind_param("ss", $google_id, $email);
            $updateStmt->execute();
            $updateStmt->close();

            // Stocker les informations de l'utilisateur dans la session, y compris le statut admin
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['email'] = $email;
            $_SESSION['is_admin'] = $is_admin;  // Stocke le statut admin
            $_SESSION['loggedin'] = true;

            // Redirection après connexion réussie
            header("Location: index.php");
            exit();
        } else {
            echo "Erreur : Impossible de récupérer les informations utilisateur depuis Google.";
        }
        $stmt->close();
        $conn->close();
    }
} else {
    // Affichage de l'erreur en cas d'échec
    echo "Erreur lors de la récupération du token d'accès : " . $response;
}
?>
