    <?php
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    require 'vendor/autoload.php'; // Si vous utilisez Composer

    // Créer une nouvelle instance de PHPMailer
    $mail = new PHPMailer(true);

    if (isset($_POST['email'])) {
        try {
            // Configuration du serveur SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'sliceitsupp@gmail.com'; // Remplacez par votre email
            $mail->Password   = 'Y3,>Ao#T-Dy2F@Jo6nO5';    // Utilisez un mot de passe d'application Google
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Définir les informations de l'expéditeur et du destinataire
            $mail->setFrom('sliceitsupp@gmail.com', 'SliceIT');
            $mail->addAddress($_POST['email']); // Le destinataire

            // Contenu du mail
            $mail->isHTML(true);
            $mail->Subject = 'Mot de passe oublié';
            $password = uniqid();
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $mail->Body    = "Bonjour, voici votre nouveau mot de passe : $password";

            // Envoi du mail
            $mail->send();

            // Mise à jour du mot de passe dans la base de données
            $sql = "UPDATE users SET password = ? WHERE email = ?";
            $stmt = $db->prepare($sql);
            $stmt->execute([$hashedPassword, $_POST['email']]);

            echo 'Un nouveau mot de passe a été envoyé à votre adresse e-mail.';
        } catch (Exception $e) {
            echo "Erreur lors de l'envoi de l'email : {$mail->ErrorInfo}";
        }
    }
    ?>

    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    ?>

    <?php
    try {
        // Assure-toi d'avoir bien configuré ta connexion à la base de données
        $db = new PDO('mysql:host=localhost;dbname=site_web', 'root', '');
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
    ?>