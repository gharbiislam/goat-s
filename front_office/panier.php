<?php
session_start();

// Database connection (adjust credentials as needed)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "goats";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

// Get cart from cookie
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
error_log("Contenu du panier : " . print_r($cart, true)); // Journal pour débogage

$total = 0;
$shipping_fee = 8.00;
$discount = 0.00;
$discount_percentage = isset($_SESSION['discount_percentage']) ? $_SESSION['discount_percentage'] : 0;
$promo_code = isset($_SESSION['promo_code']) ? $_SESSION['promo_code'] : '';
$promo_error = isset($_SESSION['promo_error']) ? $_SESSION['promo_error'] : '';

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
    header("Location: panier.php");
    exit;
}

// Check if promo code is already applied
if (isset($_SESSION['promo_code'])) {
    $promo_code = $_SESSION['promo_code'];
    $discount_percentage = $_SESSION['discount_percentage'];
}

// Handle quantity update or item removal
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_quantity'])) {
        $index = $_POST['index'];
        $new_quantity = intval($_POST['quantity']);
        if ($new_quantity > 0) {
            $cart[$index]['quantity'] = $new_quantity;
        } else {
            unset($cart[$index]);
        }
        $cart = array_values($cart);
        setcookie('cart', json_encode($cart), time() + (30 * 24 * 60 * 60), "/");
    } elseif (isset($_POST['remove_item'])) {
        $index = $_POST['index'];
        unset($cart[$index]);
        $cart = array_values($cart);
        setcookie('cart', json_encode($cart), time() + (30 * 24 * 60 * 60), "/");
    }
    header("Location: panier.php");
    exit;
}

/// Calculate total and discount
foreach ($cart as $index => $item) {
    if (!isset($item['price']) || !isset($item['quantity']) || !is_numeric($item['price']) || !is_numeric($item['quantity'])) {
        error_log("Élément de panier invalide à l'index $index : " . print_r($item, true));
        unset($cart[$index]);
        continue;
    }
    $subtotal = floatval($item['price']) * intval($item['quantity']);
    $total += $subtotal;
}

// Réindexer le panier et mettre à jour le cookie
$cart = array_values($cart);
setcookie('cart', json_encode($cart), time() + (30 * 24 * 60 * 60), "/");

if ($discount_percentage > 0) {
    $discount = ($total * $discount_percentage) / 100;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Panier</title> <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
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
        body {
            font-family: 'Arial', sans-serif;
        }
        


        .cart-table {
             width: 100%;
            border-collapse: separate;
            margin-bottom: 20px;
            border-radius: 5px;    border-spacing: 0;
            border: 1px solid #E7E7E7;
        }

      
        .cart-table th {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #E7E7E7;
        }
  .cart-table tbody tr {
            background-color: #E8F3D8;
        }

    .cart-table td {
            padding: 15px;
            border-top: 10px solid white; 
            border-bottom: 10px solid white; 
        }


        .cart-item img {
            width: 65px;
            height: 100px;
            object-fit: cover;
         
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-btn {
         
background-color: transparent;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            padding: 5px 10px;
        }

        .quantity-input {
            width: 50px;
            text-align: center;
        background-color: transparent;
 border: none;
            border-radius: 5px;
            padding: 5px;
        }

        .cart-summary {
            background-color: none;
            padding: 20px;
            border-radius: 5px;
            text-align: left;
            border: 1px solid #E7E7E7; border-radius: 5px;
        }

        .cart-summary h4 {
              color: #1A3C6D; 
            font-weight: bold; 
            margin-bottom: 20px; 
        }

        small {
            color: #747171;
        }

        .checkout-btn {
            background-color: #8ec63e;
           
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
          
            font-weight: bold; margin-top: 20px;
        }  .checkout-btn a{
             color: white; text-decoration: none;
        }
 .checkout-btn:hover {
    background-color:  #8ec63e;
 }
 .checkout-btn:disabled {
    background-color: #cccccc; 
    cursor: not-allowed;
    opacity: 0.6; 
}
.checkout-btn:disabled a {
    color: #666666; 
    pointer-events: none; 
}
        .promo-section {
            margin-bottom: 15px;
        }

        .promo-section input {
            width: 70%;
            padding: 5px;
        
            border-radius: 5px 0 0 5px;border-color: #9EA2AE;
        }

        .promo-section button {
            width: 30%;
            padding: 5px;
            background-color: #8ec63e;
            color: white;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            font-weight: bold;
        }

        .promo-error {
            color: red;
            font-size: 12px;
            margin-top: 5px;
            text-align: left;
        }

       .title {
            color:  #1f4492;
            font-weight: bold;   
            margin-bottom: 20px; 
            margin-bottom: 20px; 

        }  .informations { border: 1px solid #E7E7E7; border-radius: 5px; padding: 20px; margin-top: 20px; font-size: 1.1rem; }
        .informations img { margin-right: 10px; }
    
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <!-- Cart Content -->
    <div class="container my-5">
        <h3 class="title">Panier</h3>
        <div class="row">
            <div class="col-md-8">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Détail produits</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Montant</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cart)): ?>
                            <tr>
                                <td colspan="5" class="text-center">Votre panier est vide.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cart as $index => $item): ?>
                                <tr>
                                    <td>
                                        <div class="cart-item d-flex align-items-center">
                                            <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                            <div class="ms-3">
                                                <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                                <?php if (!empty($item['format'])): ?>
                                                    <small>Format: <?php echo htmlspecialchars($item['format']); ?></small><br>
                                                <?php endif; ?>
                                                <?php if (!empty($item['flavor'])): ?>
                                                    <small>Saveur: <?php echo htmlspecialchars($item['flavor']); ?></small><br>
                                                <?php endif; ?>
                                                <?php if (!empty($item['size'])): ?>
                                                    <small>Taille: <?php echo htmlspecialchars($item['size']); ?></small><br>
                                                <?php endif; ?>
                                                <?php if (!empty($item['color'])): ?>
                                                    <small>Couleur: <?php echo htmlspecialchars($item['color']); ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo number_format($item['price'], 2); ?> DT</td>
                                    <td>
                                        <form method="post" class="quantity-selector">
                                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                                            <button type="button" class="quantity-btn" onclick="updateQuantity(this, -1)">-</button>
                                            <input type="number" name="quantity" class="quantity-input" value="<?php echo $item['quantity']; ?>" min="1">
                                            <button type="button" class="quantity-btn" onclick="updateQuantity(this, 1)">+</button>
                                            <button type="submit" name="update_quantity" style="display:none;"></button>
                                        </form>
                                    </td>
                                    <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> DT</td>
                                    <td>
                                        <form method="post">
                                            <input type="hidden" name="index" value="<?php echo $index; ?>">
                                            <button type="submit" name="remove_item" class="btn btn-link text-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4">
                <div>
                <div class="cart-summary">
                    <h5>Total du panier</h5>
                    <div class="d-flex justify-content-between">
                        <p>Sous Total</p>
                        <p><?php echo number_format($total, 2); ?> DT</p>
                    </div>
                    <div class="d-flex justify-content-between">
                        <p>Frais de livraison</p>
                        <p><?php echo number_format($shipping_fee, 2); ?> DT</p>
                    </div>
                    <div class="promo-section">
                        <form method="post" style="display: flex;">
                            <input type="text" name="promo_code" placeholder="Avez-vous un code promo" value="<?php echo htmlspecialchars($promo_code); ?>">
                            <button type="submit" name="apply_promo" class="mx-2">Valider</button>
                        </form>
                        <?php if (!empty($promo_error)): ?>
                            <p class="promo-error"><?php echo $promo_error; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php if ($discount_percentage > 0): ?>
                        <div class="d-flex justify-content-between">
                            <p>Réduction (<?php echo $discount_percentage; ?>%)</p>
                            <p>-<?php echo number_format($discount, 2); ?> DT</p>
                        </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                        <h5>Total</h5>
                        <h5><?php echo number_format($total + $shipping_fee - $discount, 2); ?> DT</h5> 
                      
                    </div>  
                </div><button class="col-12 checkout-btn" <?php echo empty($cart) ? 'disabled' : ''; ?>>
                 <a href="livraison.php" class="">Commander</a> </button> 
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
    <script>
        function updateQuantity(button, change) {
            const input = button.parentElement.querySelector('.quantity-input');
            let quantity = parseInt(input.value);
            quantity += change;
            if (quantity < 1) quantity = 1;
            input.value = quantity;
            button.parentElement.querySelector('button[type="submit"]').click();
        }
    </script>
</body>
</html>

<?php
unset($_SESSION['promo_error']);
?>