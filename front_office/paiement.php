<?php
session_start();

// Validate ALL required session data FIRST
$required_session_vars = [
    'final_total', 
    'delivery_info', 
    'user_id',
    'delivery_info' => ['date_livraison', 'ville', 'rue', 'code_postal', 'telephone', 'message']
];

// Check main session variables
foreach ($required_session_vars as $key => $value) {
    if (is_array($value)) {
        if (!isset($_SESSION[$key]) || !is_array($_SESSION[$key])) {
            header("Location: livraison.php?error=missing_data");
            exit;
        }
        foreach ($value as $field) {
            if (!isset($_SESSION[$key][$field])) {
                header("Location: livraison.php?error=missing_delivery_info");
                exit;
            }
        }
    } else {
        if (!isset($_SESSION[$value])) {
            header("Location: livraison.php?error=missing_session");
            exit;
        }
    }
}

// Now safely access session variables
$final_total = $_SESSION['final_total'];
$delivery_info = $_SESSION['delivery_info'];
$user_id = $_SESSION['user_id'];

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

// Initialize variables
$payment_success = false;
$commande_id = null;
$error_message = '';
$show_payment_form = true;

// Process payment if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_number'])) {
    // Begin transaction
    $conn->beginTransaction();

    try {
        // Get cart from cookie
        $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
        
        if (empty($cart)) {
            throw new Exception("Votre panier est vide");
        }
        
        // Get promo code if applied
        $promo_id = null;
        if (isset($_SESSION['promo_code'])) {
            $stmt = $conn->prepare("SELECT id_codepromo FROM code_promo WHERE code = :code");
            $stmt->execute(['code' => $_SESSION['promo_code']]);
            $promo = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($promo) {
                $promo_id = $promo['id_codepromo'];
            }
        }
        
        // Insert into commande table
        $stmt = $conn->prepare("
            INSERT INTO commande (
                date_commande, 
                statut, 
                total, 
                date_livraison, 
                ville, 
                rue, 
                codePostal, 
                telephone, 
                commentaire, 
                id_client, 
                id_codepromo
            ) VALUES (
                NOW(), 
                'En traitement', 
                :total, 
                :date_livraison, 
                :ville, 
                :rue, 
                :codePostal, 
                :telephone, 
                :commentaire, 
                :id_client, 
                :id_codepromo
            )
        ");
        
        // Convert date format if needed
        $delivery_date = date('Y-m-d', strtotime($delivery_info['date_livraison']));
        
        $stmt->execute([
            'total' => $final_total,
            'date_livraison' => $delivery_date,
            'ville' => $delivery_info['ville'],
            'rue' => $delivery_info['rue'],
            'codePostal' => (int)$delivery_info['code_postal'],
            'telephone' => (int)$delivery_info['telephone'],
            'commentaire' => $delivery_info['message'],
            'id_client' => (int)$user_id,
            'id_codepromo' => $promo_id
        ]);
        
        // Get the last inserted commande ID
        $commande_id = $conn->lastInsertId();
        
        // Insert each item into ligne_commande table and collect product details
        $cart_items = [];
        foreach ($cart as $item) {
            $stmt = $conn->prepare("
                INSERT INTO ligne_commande (
                    id_commande, 
                    id_produit, 
                    quantite, 
                    sous_total
                ) VALUES (
                    :id_commande, 
                    :id_produit, 
                    :quantite, 
                    :sous_total
                )
                ON DUPLICATE KEY UPDATE 
                    quantite = quantite + VALUES(quantite),
                    sous_total = sous_total + VALUES(sous_total)
            ");
            
            $stmt->execute([
                'id_commande' => (int)$commande_id,
                'id_produit' => (int)$item['id'],
                'quantite' => (int)$item['quantity'],
                'sous_total' => (float)($item['price'] * $item['quantity'])
            ]);
            
            // Store item details for stock update
            $cart_items[] = [
                'id_produit' => (int)$item['id'],
                'quantite' => (int)$item['quantity'],
                'variant' => isset($item['variant']) ? $item['variant'] : null // Assuming variant details (e.g., saveur, taille, couleur) are in the cart
            ];
        }
        
        // Update stock for each item
        foreach ($cart_items as $item) {
            $id_produit = $item['id_produit'];
            $quantite = $item['quantite'];
            $variant = $item['variant'];

            // Check if product is in alimentaire
            $stmt = $conn->prepare("SELECT id_alimentaire, stock_variant FROM alimentaire WHERE id_produit = :id_produit");
            if ($variant && isset($variant['saveur'], $variant['format'])) {
                $stmt = $conn->prepare("
                    SELECT id_alimentaire, stock_variant 
                    FROM alimentaire 
                    WHERE id_produit = :id_produit 
                    AND saveur = :saveur 
                    AND format = :format
                ");
                $stmt->execute([
                    'id_produit' => $id_produit,
                    'saveur' => $variant['saveur'],
                    'format' => $variant['format']
                ]);
            } else {
                $stmt->execute(['id_produit' => $id_produit]);
            }
            $alimentaire = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($alimentaire) {
                if ($alimentaire['stock_variant'] < $quantite) {
                    throw new Exception("Stock insuffisant pour le produit alimentaire ID $id_produit");
                }
                $stmt = $conn->prepare("
                    UPDATE alimentaire 
                    SET stock_variant = stock_variant - :quantite 
                    WHERE id_alimentaire = :id_alimentaire
                ");
                $stmt->execute([
                    'quantite' => $quantite,
                    'id_alimentaire' => $alimentaire['id_alimentaire']
                ]);
                continue;
            }
            
            // Check if product is in vetement
            $stmt = $conn->prepare("SELECT id_vetement, stock_variant FROM vetement WHERE id_produit = :id_produit");
            if ($variant && isset($variant['taille'], $variant['couleur'], $variant['genre'])) {
                $stmt = $conn->prepare("
                    SELECT id_vetement, stock_variant 
                    FROM vetement 
                    WHERE id_produit = :id_produit 
                    AND taille = :taille 
                    AND couleur = :couleur 
                    AND genre = :genre
                ");
                $stmt->execute([
                    'id_produit' => $id_produit,
                    'taille' => $variant['taille'],
                    'couleur' => $variant['couleur'],
                    'genre' => $variant['genre']
                ]);
            } else {
                $stmt->execute(['id_produit' => $id_produit]);
            }
            $vetement = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($vetement) {
                if ($vetement['stock_variant'] < $quantite) {
                    throw new Exception("Stock insuffisant pour le produit vestimentaire ID $id_produit");
                }
                $stmt = $conn->prepare("
                    UPDATE vetement 
                    SET stock_variant = stock_variant - :quantite 
                    WHERE id_vetement = :id_vetement
                ");
                $stmt->execute([
                    'quantite' => $quantite,
                    'id_vetement' => $vetement['id_vetement']
                ]);
                continue;
            }
            
            // Check if product is in accessoire
            $stmt = $conn->prepare("SELECT id_accessoire, stock_variant FROM accessoire WHERE id_produit = :id_produit");
            if ($variant && isset($variant['taille'], $variant['couleur'])) {
                $stmt = $conn->prepare("
                    SELECT id_accessoire, stock_variant 
                    FROM accessoire 
                    WHERE id_produit = :id_produit 
                    AND taille = :taille 
                    AND couleur = :couleur
                ");
                $stmt->execute([
                    'id_produit' => $id_produit,
                    'taille' => $variant['taille'],
                    'couleur' => $variant['couleur']
                ]);
            } else {
                $stmt->execute(['id_produit' => $id_produit]);
            }
            $accessoire = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($accessoire) {
                if ($accessoire['stock_variant'] < $quantite) {
                    throw new Exception("Stock insuffisant pour le produit accessoire ID $id_produit");
                }
                $stmt = $conn->prepare("
                    UPDATE accessoire 
                    SET stock_variant = stock_variant - :quantite 
                    WHERE id_accessoire = :id_accessoire

                ");
                $stmt->execute([
                    'quantite' => $quantite,
                    'id_accessoire' => $accessoire['id_accessoire']
                ]);
                continue;
            }
            
            // If product not found in any variant table
            throw new Exception("Produit ID $id_produit non trouvé dans les tables de variantes");
        }
        
        // Commit transaction
        $conn->commit();
        
        // Clear cart and session data
        setcookie('cart', '', time() - 3600, "/");
        unset($_SESSION['final_total']);
        unset($_SESSION['delivery_info']);
        unset($_SESSION['promo_code']);
        unset($_SESSION['discount_percentage']);
        
        $payment_success = true;
        $show_payment_form = false;
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollBack();
        $error_message = "Erreur: " . $e->getMessage();
        error_log("Payment processing error: " . $e->getMessage());
        if ($e instanceof PDOException) {
            error_log("PDO Error Info: " . print_r($conn->errorInfo(), true));
        }
    }
}

// Format values for display
$amount = number_format($final_total, 3) . ' TND';
$time_remaining = "15 min. 00 sec.";
$order_number = "N°" . rand(100000, 999999) . "-" . date("YmdHis");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paiement - ClicToPay</title> <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .header {
            margin-bottom: 20px;
        }
        .header img {
            height: 40px;
            margin-right: 10px;
        }
        .header-text {
            font-size: 14px;
            color: #666;
        }
        .order-info {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .timer {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            font-size: 14px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .card-details {
            display: flex;
            gap: 10px;
        }
        .card-details .form-group {
            flex: 1;
        }
        .checkbox {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .checkbox input {
            margin-right: 5px;
        }
        .pay-button {
            background-color: #005ea6;
            color: #fff;
            padding: 10px;
            border: none;
            border-radius: 4px;
            width: 100%;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
        }
        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
            background-color: #F2F2F2; 
            padding: 20px;
        }
        .footer img {
            height: 20px;
            width: auto;
            margin-right: 5px;
        }
        .secure-text {
            display: flex;
            align-items: center;
        }
        .secure-text img {
            height: 16px;
            margin-right: 5px;
        }
        .success-message {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 90vh;
        text-align: center;
        padding: 20px;
        background-color: #f9f9f9;
        color: #333;
    }

    .title-success {
        color: #8FC73F;
        font-size: 1.8rem;
        font-weight: bold;
        margin-bottom: 0.3rem;
    }

    .message {
        color: #000;
        font-size: 1rem;
        margin-bottom: 2rem;
    }

    .checkmark-circle {
        width: 100px;
        height: 100px;
        background-color: #8FC73F;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 2rem 0;
    }

    .checkmark {
        width: 80px;
        height: 40px;
        border-left: 6px solid #1E4494;
        border-bottom: 6px solid #1E4494;
        transform: rotate(-45deg);
        margin-bottom: 40px; margin-left: 20px;
    }

    .thank-you {
        color: #000;
        font-size: 1rem;
        margin-bottom: 1rem;
    }

    .continue-link {
        color: #1E4494;
        text-decoration: none;
        font-size: 0.95rem;
    }

    .continue-link:hover {
        text-decoration: underline;
    }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            text-align: center;
        }
        .hidden {
            display: none;
        }
    </style>
</head>
<body>
    <div class="container">
      
        <?php if ($payment_success): ?>
            <div class="success-message">
    <h3 class="title-success">votre commande a été passée avec succès</h3>
    <p class="message">
        Votre commande est en cours de traitement.<br>
        Vous recevrez un email de confirmation avec les détails.
    </p>
    <div class="checkmark-circle">
        <div class="checkmark"></div>
    </div>
    <p class="thank-you">Merci pour votre achat sur GOAT'S</p>
    <a href="index.php" class="continue-link">Continuer sur GOAT'S</a>
</div>

        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>
  

        <div id="payment-form" class="<?php echo $show_payment_form ? '' : 'hidden'; ?>"><div class="header">
            <img src="../assets/img/logo/ClicToPay_logo.png" alt="ClicToPay Logo">
            <span class="header-text">by Monétique Tunisie</span>
        </div>
            <div class="order-info">
                Numéro de la commande <?php echo htmlspecialchars($order_number); ?>
            </div>
            <div class="timer">
                Il reste jusqu'à la fin de la session <span id="timer" class="fw-bold"><?php echo htmlspecialchars($time_remaining); ?></span>.
            </div>

            <form method="POST">
                <div class="form-group">
                    <label for="card_number">Numéro de la carte</label>
                    <input type="text" id="card_number" name="card_number" placeholder="" required>
                </div>

                <div class="card-details">
                    <div class="form-group">
                        <label for="month">Mois</label>
                        <select id="month" name="month" required>
                            <option value="">Mois</option>
                            <option value="01">01</option>
                            <option value="02">02</option>
                            <option value="03">03</option>
                            <option value="04">04</option>
                            <option value="05">05</option>
                            <option value="06">06</option>
                            <option value="07">07</option>
                            <option value="08">08</option>
                            <option value="09">09</option>
                            <option value="10">10</option>
                            <option value="11">11</option>
                            <option value="12">12</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="year">Année</label>
                        <select id="year" name="year" required>
                            <option value="">Année</option>
                            <option value="2025">2025</option>
                            <option value="2026">2026</option>
                            <option value="2027">2027</option>
                            <option value="2028">2028</option>
                            <option value="2029">2029</option>
                            <option value="2030">2030</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cvv">Code de sûreté</label>
                        <input type="text" id="cvv" name="cvv" placeholder="" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="cardholder_name">Le nom du détenteur</label>
                    <input type="text" id="cardholder_name" name="cardholder_name" placeholder="" required>
                </div>

                <div class="form-group">
                    <label for="email">Adresse e-mail</label>
                    <input type="email" id="email" name="email" placeholder="" required>
                </div>

                <div class="checkbox">
                    <input type="checkbox" id="save_email" name="save_email" checked>
                    <label for="save_email">Adresse e-mail</label>
                </div>

                <button type="submit" class="pay-button">Paiement <?php echo htmlspecialchars($amount); ?></button>
            </form>

            <div class="footer">
                <div class="secure-text">
                    <img src="../assets/img/icons/lock.svg" alt="Lock Icon">
                    Paiement sécurisé
                </div>
                <div>
                    <img src="../assets/img/logo/monetique.png" alt="Monétique Logo">
                    <img src="../assets/img/logo/visa.png" alt="Visa Logo">
                    <img src="../assets/img/logo/mastercard.png" alt="Mastercard Logo">
                    <img src="../assets/img/logo/Verified-By-Visa.png" alt="Verified by Visa Logo">
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set the countdown timer to 15 minutes (900 seconds)
        let timeLeft = 900;
        const timerDisplay = document.getElementById('timer');

        function updateTimer() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            timerDisplay.textContent = `${minutes.toString().padStart(2, '0')} min. ${seconds.toString().padStart(2, '0')} sec.`;

            timeLeft--;

            if (timeLeft < 0) {
                clearInterval(timerInterval);
                timerDisplay.textContent = "00 min. 00 sec.";
                document.querySelector('form').style.pointerEvents = 'none';
                alert("La session a expiré. Veuillez recommencer.");
            }
        }

        const timerInterval = setInterval(updateTimer, 1000);
        updateTimer();
    </script>
</body>
</html>