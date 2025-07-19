<?php
include('db.php');
session_start();

if (isset($_POST['signup'])) {
    $nom = mysqli_real_escape_string($conn, $_POST['nom_client']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom_client']);
    $mail = mysqli_real_escape_string($conn, $_POST['mail_client']);
    $password = mysqli_real_escape_string($conn, $_POST['mdp_client']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_mdp_client']);
    
    // Vérifier si les mots de passe correspondent
    if ($password !== $confirm_password) {
        $error = "Les mots de passe ne correspondent pas.";
    } else {
        // Hacher le mot de passe pour plus de sécurité
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Définir actif à 1 par défaut (seul l'admin peut le désactiver)
        $query = "INSERT INTO client (nom_client, prenom_client, mail_client, mdp_client, actif) 
                VALUES ('$nom', '$prenom', '$mail', '$hashed_password', 1)";
        $res = mysqli_query($conn, $query);

        if ($res) {
            $_SESSION['success'] = "Inscription réussie!";
            header("Location:client.php");
            exit();
        } else {
            $error = "Erreur: " . mysqli_error($conn);
        }
    }
}

// Configuration Google OAuth
$google_client_id = "355056512501-fhtho9prt85g18cd8r0vql5aof126php.apps.googleusercontent.com";
$google_redirect_uri = "http://localhost/dashboard/PFE/front_office/google_callback.php";

$google_auth_url = "https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query([

    'client_id' => $google_client_id,

    'redirect_uri' => $google_redirect_uri,

    'response_type' => 'code',

    'scope' => 'email profile',

    'access_type' => 'online',

    'prompt' => 'select_account'

]);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOATS-S'inscrire</title>
    <!-- Bootstrap CSS --><link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
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
            width: 650px; /* Légèrement agrandi */
            height: 560px; /* Légèrement agrandi */
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
            padding: 18px; /* Légèrement agrandi */
            height: 100%;
            overflow-y: auto;
        }
        
        .close-btn {
            position: absolute;
            top: 8px; /* Légèrement agrandi */
            right: 8px; /* Légèrement agrandi */
            font-size: 20px; /* Légèrement agrandi */
            color: #333;
            background: none;
            border: none;
            z-index: 1060;
            cursor: pointer;
        }
        
        h2 {
            font-size: 20px; /* Légèrement agrandi */
            margin-bottom: 10px !important; /* Légèrement agrandi */
        }
        
        .form-label {
            font-size: 12px; /* Légèrement agrandi */
            font-weight: 600;
            margin-bottom: 2px;
        }
        
        .form-control {
            border-width: 1px;
            border-color: #f0f0f0;
            border-style: solid;
            border-radius: 4px;
            padding: 5px 10px; /* Légèrement agrandi */
            height: 32px; /* Légèrement agrandi */
            font-size: 12px; /* Légèrement agrandi */
        }
        
        .form-control::placeholder {
            color: #999;
            font-size: 10px;
        }
        
        .mb-3 {
            margin-bottom: 8px !important; /* Légèrement agrandi */
        }
        
        .signup-btn {
            background-color: #8BC34A;
            border-color: #8BC34A;
            width: 100%;
            padding: 5px; /* Légèrement agrandi */
            font-weight: 600;
            margin-top: 5px;
            font-size: 13px; /* Légèrement agrandi */
            border-radius: 4px;
            height: 32px; /* Légèrement agrandi */
            line-height: 1;
        }
        
        .signup-btn:hover {
            background-color: #7CB342;
            border-color: #7CB342;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 10px 0; /* Légèrement agrandi */
            color: #888;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .divider span {
            padding: 0 10px; /* Légèrement agrandi */
            font-size: 12px; /* Légèrement agrandi */
        }
        
        .google-btn {
            background-color: white;
            border: 1px solid #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
            width: 100%;
            height: 32px; /* Légèrement agrandi */
            margin-bottom: 10px; /* Légèrement agrandi */
            font-size: 12px; /* Légèrement agrandi */
        }
        
        .google-btn:hover {
            background-color: #f8f9fa;
        }
        
        .google-btn img {
            margin-right: 8px; /* Légèrement agrandi */
            width: 16px; /* Légèrement agrandi */
            height: 16px; /* Légèrement agrandi */
        }
        
        .login-link {
            text-align: center;
            margin-top: 8px; /* Légèrement agrandi */
            font-size: 12px; /* Légèrement agrandi */
        }
        
        .login-link a {
            color: #8BC34A;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
        
        /* Ajustements pour les boutons Bootstrap */
        .btn {
            padding: 0.25rem 0.5rem;
        }
        .ysar 
        {
            padding: 20px;
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
                        <!-- Utilisation d'une balise img au lieu d'un background -->
                        <img src="../assets/img/signupcover.png" alt="GOATS">
                    </div>
                </div>
                <div class=" ysar col-md-6">
                    <div class="popup-right">
                        <h2>S'inscrire</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger py-1 px-2 mb-2" style="font-size: 11px; padding: 3px 6px !important;"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom</label>
                                <input type="text" class="form-control" id="nom" name="nom_client" placeholder="Entrer votre nom" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="prenom" class="form-label">Prénom</label>
                                <input type="text" class="form-control" id="prenom" name="prenom_client" placeholder="Entrer votre prénom" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="mail_client" placeholder="Entrer votre email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="mdp_client" placeholder="Entrer votre mot de passe" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirmer votre mot de passe</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_mdp_client" placeholder="Confirmer votre mot de passe" required>
                            </div>
                            
                            <button type="submit" name="signup" class="btn btn-primary signup-btn">S'inscrire</button>
                        </form>
                        
                        <div class="divider">
                            <span>ou</span>
                        </div>
                        
                        <a href="<?php echo $google_auth_url; ?>" class="google-btn">
                            <img src="../assets/img/google_icon.png" alt="Google">
                            Connecter avec google
                        </a>
                        
                        <div class="login-link">
                            Vous avez un compte? <a href="login.php">se connecter</a>
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
