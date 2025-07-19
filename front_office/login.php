<?php
session_start();

$host = "localhost";
$dbname = "goats";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Check for existing cookie on page load
if (!isset($_SESSION['id_client']) && isset($_COOKIE['stay_connected'])) {
    $cookieData = json_decode($_COOKIE['stay_connected'], true);
    if ($cookieData && isset($cookieData['email']) && isset($cookieData['hash'])) {
        $stmt = $pdo->prepare("SELECT * FROM client WHERE mail_client = ? AND actif = 1");
        $stmt->execute([$cookieData['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($cookieData['hash'], $user['mdp_client'])) {
            $_SESSION['id_client'] = $user['id_client'];
            $_SESSION['prenom_client'] = $user['prenom_client'];
            $_SESSION['nom_client'] = $user['nom_client'];
            $_SESSION['mail_client'] = $user['mail_client'];
            header("Location: client.php");
            exit();
        } else {
            // Invalid cookie, clear it
            setcookie('stay_connected', '', time() - 3600, '/', '', true, true);
        }
    }
}

if (isset($_POST['login'])) {
    $mail = $_POST['mail_client'];
    $password = $_POST['mdp_client'];
    $stayConnected = isset($_POST['stay_connected']);
    
    $stmt = $pdo->prepare("SELECT * FROM client WHERE mail_client = ? AND actif = 1");
    $stmt->execute([$mail]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        // Verify password
        if (password_verify($password, $user['mdp_client'])) {
            $_SESSION['id_client'] = $user['id_client'];
            $_SESSION['prenom_client'] = $user['prenom_client'];
            $_SESSION['nom_client'] = $user['nom_client'];
            $_SESSION['mail_client'] = $user['mail_client'];
            
            // If "stay connected" is checked, set a cookie
            if ($stayConnected) {
                $cookieData = json_encode([
                    'email' => $mail,
                    'hash' => $password // Store plain password (less secure, but no DB changes)
                ]);
                // Set secure cookie (expires in 30 days)
                setcookie('stay_connected', $cookieData, time() + (30 * 24 * 60 * 60), '/', '', true, true);
            }
            
            header("Location: client.php");
            exit();
        } else {
            $error = "Mot de passe incorrect.";
        }
    } else {
        $error = "Email incorrect ou compte désactivé.";
    }
}

// Configuration Google OAuth
$google_client_id = "VOTRE_CLIENT_ID"; // À remplacer par votre ID client Google
$google_redirect_uri = "https://votre-site.com/google_callback.php"; // À remplacer par votre URL de redirection
$google_auth_url = "https://accounts.google.com/o/oauth2/auth?client_id=$google_client_id&redirect_uri=$google_redirect_uri&response_type=code&scope=email%20profile&prompt=select_account";
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Se connecter</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: #f8f9fa;
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
            border-radius: 10px;
            overflow: hidden;
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
        .green_goats {
            color: #8FC73E;
        }        
        .popup-right {
            padding: 30px 30px;
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
            margin-bottom: 15px;
            font-weight: bold;
            color: #172B4D;
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
            margin-bottom: 15px;
        }
        
        .login-btn {
            background-color: #8FC73E;
            color: white;
            width: 100%;
            padding: 5px;
            font-weight: 600;
            margin-top: 10px;
            font-size: 13px;
            border-radius: 4px;
            height: 32px;
            line-height: 1;
        }
        
        .login-btn:hover {
            background-color: #648B2C;
            color: white;
        }
        
        .divider {
            display: flex;
            align-items: center;
            margin: 20px 0;
            color: #888;
        }
        
        .divider::before,
        .divider::after {
            content: "";
            flex: 1;
            border-bottom: 1px solid #ddd;
        }
        
        .divider span {
            padding: 0 10px;
            font-size: 12px;
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
            height: 32px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        
        .google-btn:hover {
            background-color: #f8f9fa;
        }
        
        .google-btn img {
            margin-right: 8px;
            width: 16px;
            height: 16px;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 15px;
            font-size: 12px;
        }
        
        .signup-link a {
            color: #172B4D;
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .forgot-password {
            text-align: right;
            margin-top: 5px;
            font-size: 11px;
        }
        
        .forgot-password a {
            color: #172B4D;
            text-decoration: none;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .stay-connected {
            font-size: 12px;
            margin-top: 10px;
            display: flex;
            align-items: center;
        }
        
        .stay-connected input {
            margin-right: 5px;
        }
        
        .blue_goats {
            color: #172B4D;
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
                        <h2 class="blue_goats">Se connecter</h2>
                        
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger py-1 px-2 mb-3" style="font-size: 11px; padding: 3px 6px ;"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="mail_client" placeholder="Entrer votre email" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="mdp_client" placeholder="Entrer votre mot de passe" required>
                                <div class="forgot-password">
                                    <a href="forgot-password.php" class="blue_goats">Mot de passe oublié ?</a>
                                </div>
                            </div>
                            
                            <div class="stay-connected">
                                <input type="checkbox" id="stay_connected" name="stay_connected">
                                <label for="stay_connected">Rester connecté</label>
                            </div>
                            
                            <button type="submit" name="login" class="btn login-btn">Se connecter</button>
                        </form>
                        
                        <div class="divider">
                            <span>ou</span>
                        </div>
                        
                        <a href="<?php echo $google_auth_url; ?>" class="google-btn">
                            <img src="../assets/img/google_icon.png" alt="Google">
                            Connecter avec google
                        </a>
                        
                        <div class="signup-link">
                            Vous n'avez pas de compte? <a href="signup.php" class="green_goats">s'inscrire</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function closePopup() {
            window.history.back();
        }
    </script>
</body>
</html>