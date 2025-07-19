
<?php
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


include('db.php');
session_start();

$message = '';
$alert_type = '';

if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($conn, $_POST['mail_client']);

    $query = "SELECT * FROM client WHERE mail_client = '$email' AND actif = 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $token = bin2hex(random_bytes(32));
        $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

        $update_query = "UPDATE client SET reset_token = '$token', reset_expires = '$expires' WHERE mail_client = '$email'";
        if (mysqli_query($conn, $update_query)) {
            $reset_link = "https://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;

            $mail = new PHPMailer(true);
            try {
                // Configuration SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'fekihines22@gmail.com';
                $mail->Password   = 'ldac aned sizv imcx'; 
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                // Destinataire
                $mail->setFrom('fekihines22@gmail.com', 'Inès Fekih');
                $mail->addAddress($email);

                // Contenu
                $mail->isHTML(true);
                $mail->Subject = 'Réinitialisation de votre mot de passe';
                $mail->Body = "Bonjour,<br><br>
                    Vous avez demandé la réinitialisation de votre mot de passe.<br>
                    Cliquez ici pour le faire : <a href='$reset_link'>$reset_link</a><br><br>
                    Ce lien expire dans 1 heure.<br><br>
                    Si vous n'avez pas demandé cette réinitialisation, ignorez ce mail.<br><br>
                    Merci.";

                $mail->send();
                $message = "Un email de réinitialisation a été envoyé à votre adresse email.";
                $alert_type = "success";
            } catch (Exception $e) {
                $message = "Erreur d'envoi de l'email : {$mail->ErrorInfo}";
                $alert_type = "danger";
            }
        } else {
            $message = "Une erreur est survenue. Veuillez réessayer plus tard.";
            $alert_type = "danger";
        }
    } else {
        $message = "Un email de réinitialisation a été envoyé à votre adresse email si elle existe dans notre système.";
        $alert_type = "success";
    }
}
?>

<!-- Partie HTML (formulaire) identique à ce que tu as déjà fourni -->



<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Mot de passe oublié</title>
    <!-- Bootstrap CSS --> <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
        
        .popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1050;
        }
        
        .popup-container {
            width: 650px;
            height: 480px;
            background-color: white;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        
        .popup-left {
            height: 100%;
            overflow: hidden;
        }
        
        .popup-left img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .popup-right {
            padding: 30px; /* Padding uniforme sur tous les côtés */
            height: 100%;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .close-btn {
            position: absolute;
            top: 8px;
            right: 8px;
            font-size: 20px;
            color: #333;
            background: none;
            border: none;
            z-index: 1060;
            cursor: pointer;
        }
        
        h2 {
            font-size: 20px;
            margin-bottom: 20px !important;
        }
        
        .form-label {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .form-control {
            border-width: 1px;
            border-color: #f0f0f0;
            border-style: solid;
            border-radius: 4px;
            padding: 5px 10px;
            height: 32px;
            font-size: 12px;
        }
        
        .form-control::placeholder {
            color: #999;
            font-size: 10px;
        }
        
        .mb-3 {
            margin-bottom: 15px !important;
        }
        
        .reset-btn {
            background-color: #8BC34A;
            border-color: #8BC34A;
            width: 100%;
            padding: 5px;
            font-weight: 600;
            margin-top: 10px;
            font-size: 13px;
            border-radius: 4px;
            height: 32px;
            line-height: 1;
        }
        
        .reset-btn:hover {
            background-color: #7CB342;
            border-color: #7CB342;
        }
        
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        
        .login-link a {
            color: #8BC34A;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        .description {
            font-size: 12px;
            color: #666;
            margin-bottom: 20px;
        }
        
        /* Ajustements pour les boutons Bootstrap */
        .btn {
            padding: 0.25rem 0.5rem;
        }
    </style>
</head>
<body>
    <div class="popup-overlay">
        <button class="close-btn" onclick="closePopup()">&times;</button>
        <div class="popup-container">
            <div class="row g-0 h-100">
                <div class="col-md-6">
                    <div class="popup-left">
                        <img src="../assets/img/cnxpic.png" alt="Reconnect with your goals">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="popup-right">
                        <h2>Mot de passe oublié</h2>
                        
                        <p class="description">Veuillez entrer votre adresse email pour recevoir un lien de réinitialisation de mot de passe.</p>
                        
                        <?php if (!empty($message)): ?>
                            <div class="alert alert-<?php echo $alert_type; ?> py-1 px-2 mb-3" style="font-size: 11px; padding: 3px 6px !important;"><?php echo $message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="mail_client" placeholder="Entrer votre email" required>
                            </div>
                            
                            <button type="submit" name="reset_password" class="btn btn-primary reset-btn">Réinitialiser le mot de passe</button>
                        </form>
                        
                        <div class="login-link">
                            <a href="login.php">Retour à la connexion</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function closePopup() {
            // Rediriger vers la page précédente ou fermer la popup
            window.history.back();
            // Ou si vous préférez simplement masquer la popup:
            // document.querySelector('.popup-overlay').style.display = 'none';
        }
    </script>
</body>
</html>