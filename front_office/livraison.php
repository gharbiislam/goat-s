<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "goats";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in
$user_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login']) && !$user_logged_in) {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id_client, mdp_client, nom_client, prenom_client FROM client WHERE mail_client = :email AND actif = 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['mdp_client'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id_client'];
        $_SESSION['user_name'] = $user['prenom_client'] . ' ' . $user['nom_client'];
        header("Location: livraison.php");
        exit;
    } else {
        $login_error = "Email ou mot de passe incorrect";
    }
}

// Get cart from cookie
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
$total = 0;
$shipping_fee = 8.00;
$discount = 0.00;
$discount_percentage = isset($_SESSION['discount_percentage']) ? $_SESSION['discount_percentage'] : 0;

if (isset($_SESSION['promo_code'])) {
    $promo_code = $_SESSION['promo_code'];
    $discount_percentage = $_SESSION['discount_percentage'];
}

foreach ($cart as $item) {
    $subtotal = $item['price'] * $item['quantity'];
    $total += $subtotal;
}

if ($user_logged_in) {
    $stmt = $conn->prepare("SELECT COUNT(*) as order_count FROM commande WHERE id_client = :user_id");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $order_count = $stmt->fetch(PDO::FETCH_ASSOC)['order_count'];

    if ($order_count == 0 || $total > 500) {
        $shipping_fee = 0.00;
    }
}

if ($discount_percentage > 0) {
    $discount = ($total * $discount_percentage) / 100;
}

$final_total = $total + $shipping_fee - $discount;

// Handle delivery form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_delivery']) && $user_logged_in) {
    $gouvernorat = filter_input(INPUT_POST, 'gouvernorat', FILTER_SANITIZE_STRING);
    $ville = filter_input(INPUT_POST, 'ville', FILTER_SANITIZE_STRING);
    $rue = filter_input(INPUT_POST, 'rue', FILTER_SANITIZE_STRING);
    $code_postal = filter_input(INPUT_POST, 'code_postal', FILTER_SANITIZE_STRING);
    $telephone = filter_input(INPUT_POST, 'telephone', FILTER_SANITIZE_STRING);
    $date_livraison = filter_input(INPUT_POST, 'date_livraison', FILTER_SANITIZE_STRING);
    $disponibilite = filter_input(INPUT_POST, 'disponibilite', FILTER_SANITIZE_STRING);
    $message = filter_input(INPUT_POST, 'message', FILTER_SANITIZE_STRING);

    $_SESSION['delivery_info'] = [
        'gouvernorat' => $gouvernorat,
        'ville' => $ville,
        'rue' => $rue,
        'code_postal' => $code_postal,
        'telephone' => $telephone,
        'date_livraison' => $date_livraison,
        'disponibilite' => $disponibilite,
        'message' => $message
    ];

    $_SESSION['final_total'] = $final_total;
    header("Location: paiement.php");
    exit;
}

// Handle promo code submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_promo'])) {
    $promo_code = trim($_POST['promo_code']);
    $today = date('Y-m-d');

    $stmt = $conn->prepare("SELECT reduction FROM code_promo WHERE code = :code AND date_lancement <= :today AND date_expiration >= :today");
    $stmt->execute(['code' => $promo_code, 'today' => $today]);
    $promo = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($promo) {
        $discount_percentage = floatval($promo['reduction']);
        $_SESSION['promo_code'] = $promo_code;
        $_SESSION['discount_percentage'] = $discount_percentage;
        unset($_SESSION['promo_error']);
    } else {
        $_SESSION['promo_error'] = "Code promo invalide ou expiré.";
        unset($_SESSION['promo_code']);
        unset($_SESSION['discount_percentage']);
    }
    header("Location: livraison.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - <?php echo $user_logged_in ? 'Livraison' : 'Se connecter'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
     <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <style>::-webkit-scrollbar {
    width: 5px;
    height: 5px;
    border-radius: 50px !important
}

::-webkit-scrollbar-track {
    background: #f1f1f1
}

::-webkit-scrollbar-thumb {
    background: #8FC73F
}

::-webkit-scrollbar-thumb:hover {
    background: #555
}
        body { font-family: 'Arial', sans-serif; }
        .livraison-title { 
            color: #1A3C6D; 
            font-weight: bold; 
            margin-bottom: 20px; 
        }
        .form-section, .login-form, .cart-summary { 
            border: 1px solid #E7E7E7; 
            border-radius: 5px; 
            padding: 20px; 
            margin-bottom: 20px; 
            margin-top: 0;
        }
        .section-title {  font-weight:600; margin-bottom: 20px; }
        .form-control { border: 1px solid #E7E7E7; border-radius: 5px; padding: 10px; }
        .form-label { font-weight: 500; margin-bottom: 8px; }
        
        .summary-item { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .summary-total { display: flex; justify-content: space-between; margin-top: 15px; font-weight: bold; }
        .promo-section { display: flex; margin: 15px 0; }
        .promo-section input { flex-grow: 1; border: 1px solid #E7E7E7; border-radius: 5px 0 0 5px; padding: 6px 12px; }
        .promo-section button { background-color: #8FC73F; color: white; border: none; border-radius: 0 5px 5px 0; padding: 6px 12px; }
        .checkout-btn, .login-btn { background-color: #8FC73F; color: white; border: none; padding: 12px; border-radius: 5px; width: 100%; font-weight: bold; margin-top: 20px; }
        .date-picker-container { position: relative; }
        .date-picker-container .calendar-icon { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #1A3C6D; pointer-events: none; }
        .discount-text { color: #8FC73F; font-size: 0.8rem; }
        .login-title { color: #1A3C6D; font-weight: bold; margin-bottom: 20px; }
        .new-account-link { text-align: right; margin-top: 15px; }
        .new-account-link a { color: #1A3C6D; text-decoration: none; }
        .error-message { color: #dc3545; font-size: 14px; margin-top: 5px; }
        .informations { border: 1px solid #E7E7E7; border-radius: 5px; padding: 20px; margin-top: 20px; font-size: 1.1rem; }
        .informations img { margin-right: 10px; }

      
        .row.align-top { 
            align-items: flex-start; 
        }
        .col-md-8, .col-md-4 { 
            padding-top: 0;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container my-5">
        <!-- Move the title outside the row for logged-in users -->
        <?php if ($user_logged_in): ?>
            <h4 class="livraison-title">Livraison</h4>
        <?php endif; ?>

        <div class="row align-top">
            <!-- Left Column: Login or Delivery Form -->
            <div class="col-md-8">
                <?php if ($user_logged_in): ?>
                    <!-- DELIVERY FORM FOR LOGGED IN USERS -->
                    <form method="post" action="">
                        <div class="form-section">
                            <h4 class="section-title">Adresse de livraison</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="gouvernorat" class="form-label">Gouvernorat</label>
                                    <input type="text" class="form-control" id="gouvernorat" name="gouvernorat" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="ville" class="form-label">Ville</label>
                                    <input type="text" class="form-control" id="ville" name="ville" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-8">
                                    <label for="rue" class="form-label">Rue</label>
                                    <input type="text" class="form-control" id="rue" name="rue" required>
                                </div>
                                <div class="col-md-4">
                                    <label for="code_postal" class="form-label">Code postal</label>
                                    <input type="text" class="form-control" id="code_postal" name="code_postal" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="telephone" class="form-label">Téléphone</label>
                                <input type="tel" class="form-control" id="telephone" name="telephone" required>
                            </div>
                        </div>
                        <div class="form-section">
                            <h4 class="section-title">Date livraison</h4>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="date_livraison" class="form-label">Date De Livraison</label>
                                    <div class="date-picker-container">
                                        <input type="text" class="form-control" id="date_livraison" name="date_livraison" placeholder="mm/jj/aaaa" required>
                                        <span class="calendar-icon"><i class="far fa-calendar-alt"></i></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="disponibilite" class="form-label">Votre Disponibilités</label>
                                    <input type="text" class="form-control" id="disponibilite" name="disponibilite" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Si vous voulez nous laisser un message à propos de votre commande, merci de bien vouloir le renseigner dans le champ ci-contre</label>
                                <textarea class="form-control" id="message" name="message" rows="3"></textarea>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-8 d-flex justify-content-center">
                                <button type="submit" name="submit_delivery" class="checkout-btn">Passer au paiement</button>
                            </div>
                        </div>
                    </form>
                <?php else: ?>
                    <!-- LOGIN FORM FOR GUESTS -->  <h4 class="login-title">Se connecter</h4>
                    <div class="login-form">
                      
                        <?php if (isset($login_error)): ?>
                            <div class="error-message"><?php echo $login_error; ?></div>
                        <?php endif; ?>
                        <form method="post" action="">
                            <div class="mb-4">
                                <label for="email" class="form-label">E-mail</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="new-account-link">
                                <a href="inscription.php">Nouveau compte ?</a>
                            </div>
                            <div class="row justify-content-center">
                                <div class="col-md-8 d-flex justify-content-center">
                                    <button type="submit" name="login" class="login-btn">Se connecter</button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Cart Summary and Information -->
            <div class="col-md-4">
                <div class="cart-summary">
                    <h4 class="section-title">Total du panier</h4>
                    <?php if ($discount_percentage > 0): ?>
                        <p class="discount-text">Réduction appliquée: <?php echo $discount_percentage; ?>%</p>
                    <?php endif; ?>
                    <?php if ($shipping_fee == 0 && $user_logged_in): ?>
                        <p class="discount-text">
                            Frais de livraison gratuits
                            <?php echo ($order_count == 0) ? '(Première commande)' : '(Commande > 500 DT)'; ?>
                        </p>
                    <?php endif; ?>
                    <div class="summary-item">
                        <span>Sous Total</span>
                        <span><?php echo number_format($total, 2); ?> DT</span>
                    </div>
                    <div class="summary-item">
                        <span>Frais de livraison</span>
                        <span><?php echo number_format($shipping_fee, 2); ?> DT</span>
                    </div>
                    <div class="promo-section">
                        <input type="text" placeholder="Avez-vous un code promo" id="promo_code" name="promo_code">
                        <button type="button" class="ms-2" onclick="validatePromo()">Valider</button>
                    </div>
                    <?php if (isset($_SESSION['promo_error'])): ?>
                        <div class="error-message"><?php echo $_SESSION['promo_error']; ?></div>
                    <?php endif; ?>
                    <div class="summary-total">
                        <span>Total</span>
                        <span><?php echo number_format($final_total, 2); ?> DT</span>
                    </div>
                </div>
                <div class="informations">
                    <div class="d-flex align-items-center">
                        <img src="../assets/img/icons/free_liv.svg" alt="">
                        <p>livraison gratuite dès 300TND & <br> sur votre 1ère commande</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <img src="../assets/img/icons/reduc.svg" alt="">
                        <p>10% de réduction tous les 1000 <br>points fidélité</p>
                    </div>
                    <div class="d-flex align-items-center">
                        <img src="../assets/img/icons/paiement_suc.svg" alt="">
                        <p>paiement 100% sécurisé -<br> uniquement en ligne</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
        flatpickr("#date_livraison", {
            dateFormat: "m/d/Y",
            minDate: "today",
            disableMobile: "true"
        });

        function validatePromo() {
            const promoCode = document.getElementById('promo_code').value.trim();
            if (!promoCode) {
                alert('Veuillez entrer un code promo');
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'livraison.php';
            const promoInput = document.createElement('input');
            promoInput.type = 'hidden';
            promoInput.name = 'promo_code';
            promoInput.value = promoCode;
            const submitInput = document.createElement('input');
            submitInput.type = 'hidden';
            submitInput.name = 'apply_promo';
            submitInput.value = '1';
            form.appendChild(promoInput);
            form.appendChild(submitInput);
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>