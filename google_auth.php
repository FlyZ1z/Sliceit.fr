<?php
// Le token d'accès que tu as reçu après la connexion Google
$access_token = 'ya29.a0AcM612zI_1BIjedNjUeHTN6GaKvaqnfKW_7RP4UyCk85f2hRfHkb40SEPR8sy9tpyjUll21LGxuqgVvY55axQ0-aAP5URRjz0zhwic45K3-FloL3rJVOLEIHZI2I41N4lbrwmt-_vJWqXmqp7yKHMbSuFob-4kyrtQuKxX29aCgYKARoSARASFQHGX2MiRkOF-PWYMnHQnBhCGJLy3Q0175';

// Récupérer le displayName via l'API Google People
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://people.googleapis.com/v1/people/me?personFields=names,emailAddresses");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $access_token,
]);

$response = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($response, true);

if (isset($userInfo['names'][0]['displayName'])) {
    $displayName = $userInfo['names'][0]['displayName'];
} else {
    $displayName = 'Nom inconnu';
}

// Connexion à la base de données
$conn = new mysqli('localhost', 'root', '', 'site_web');

if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}

// Récupérer les informations de l'utilisateur Google
$email = $userInfo['emailAddresses'][0]['value'];
$google_id = $userInfo['resourceName']; // L'identifiant Google

// Vérifier si l'utilisateur existe déjà dans la base de données
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // L'utilisateur existe déjà, on le connecte
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['loggedin'] = true;
} else {
    // L'utilisateur n'existe pas, on l'inscrit
    $stmt = $conn->prepare("INSERT INTO users (username, email, google_id) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $displayName, $email, $google_id);

    if ($stmt->execute()) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['loggedin'] = true;
    } else {
        echo "Erreur lors de l'inscription : " . $stmt->error;
    }
}

$stmt->close();
$conn->close();

// Redirection vers la page d'accueil après la connexion/inscription
header("Location: index.php");
exit();
