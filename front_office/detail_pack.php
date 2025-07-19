<?php
session_start();
include("db.php");

// Handle AJAX review actions
if (isset($_POST['ajax_action'])) {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => ''];

    if (!isset($_SESSION['id_client'])) {
        $response['message'] = 'Vous devez être connecté.';
        echo json_encode($response);
        exit();
    }

    $client_id = $_SESSION['id_client'];
    $pack_id = isset($_POST['pack_id']) ? intval($_POST['pack_id']) : 0;

    if ($pack_id <= 0) {
        $response['message'] = 'Pack ID invalide.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("SELECT id_avis, etoiles, date_avis FROM avis WHERE id_client = ? AND id_pack = ?");
    $stmt->bind_param("ii", $client_id, $pack_id);
    $stmt->execute();
    $user_review = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($_POST['ajax_action'] === 'submit_review' && !$user_review) {
        $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
        if ($rating >= 1 && $rating <= 5) {
            $current_date = date('Y-m-d H:i:s');
            $stmt = $conn->prepare("INSERT INTO avis (id_client, id_pack, etoiles, date_avis) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("iiis", $client_id, $pack_id, $rating, $current_date);
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['review'] = ['etoiles' => $rating, 'date_avis' => $current_date];
            } else {
                $response['message'] = 'Erreur lors de la soumission.';
            }
            $stmt->close();
        } else {
            $response['message'] = 'Note invalide.';
        }
    } elseif ($_POST['ajax_action'] === 'delete_review' && $user_review) {
        $stmt = $conn->prepare("DELETE FROM avis WHERE id_client = ? AND id_pack = ?");
        $stmt->bind_param("ii", $client_id, $pack_id);
        if ($stmt->execute()) {
            $response['success'] = true;
        } else {
            $response['message'] = 'Erreur lors de la suppression.';
        }
        $stmt->close();
    } else {
        $response['message'] = 'Action non autorisée.';
    }

    // Fetch updated review stats
    $stmt = $conn->prepare("SELECT etoiles FROM avis WHERE id_pack = ?");
    $stmt->bind_param("i", $pack_id);
    $stmt->execute();
    $reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $avg_rating = count($reviews) > 0 ? array_sum(array_column($reviews, 'etoiles')) / count($reviews) : 0;
    $star_counts = array_fill(1, 5, 0);
    foreach ($reviews as $review) {
        $star_counts[$review['etoiles']]++;
    }

    $response['stats'] = [
        'avg_rating' => number_format($avg_rating, 1),
        'total_reviews' => count($reviews),
        'star_counts' => $star_counts
    ];

    echo json_encode($response);
    exit();
}

// Initialize wishlist
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Get pack ID
$pack_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch pack details
$pack = null;
if ($pack_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM pack WHERE id_pack = ?");
    $stmt->bind_param("i", $pack_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $pack = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$pack) {
    header("Location: index.php");
    exit();
}

// Fetch reviews
$stmt = $conn->prepare("SELECT etoiles FROM avis WHERE id_pack = ?");
$stmt->bind_param("i", $pack_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$avg_rating = count($reviews) > 0 ? array_sum(array_column($reviews, 'etoiles')) / count($reviews) : 0;
$stmt->close();

// Fetch user's review
$user_review = null;
$has_reviewed = false;
if (isset($_SESSION['id_client'])) {
    $client_id = $_SESSION['id_client'];
    $stmt = $conn->prepare("SELECT etoiles, date_avis FROM avis WHERE id_client = ? AND id_pack = ?");
    $stmt->bind_param("ii", $client_id, $pack_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $user_review = $result->fetch_assoc();
        $has_reviewed = true;
    }
    $stmt->close();
}

// Handle wishlist toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_wishlist'])) {
    $pack_id = intval($_POST['pack_id']);
    if (in_array($pack_id, $_SESSION['wishlist'])) {
        $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$pack_id]);
    } else {
        $_SESSION['wishlist'][] = $pack_id;
    }
    setcookie('wishlist', !empty($_SESSION['wishlist']) ? json_encode($_SESSION['wishlist']) : '', time() + (90 * 24 * 60 * 60), '/');
    header("Location: detail_pack.php?id=" . $pack_id);
    exit();
}

// Handle add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $pack_id = intval($_POST['pack_id']);
    $quantity = intval($_POST['quantity']);
    $stmt = $conn->prepare("SELECT nom_pack, produit, total_pack, cover_pack FROM pack WHERE id_pack = ?");
    $stmt->bind_param("i", $pack_id);
    $stmt->execute();
    $pack = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($pack) {
        $cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
        $found = false;
        foreach ($cart as &$item) {
            if ($item['pack_id'] == $pack_id) {
                $item['quantity'] += $quantity;
                $found = true;
                break;
            }
        }
        if (!$found) {
            $cart[] = [
                'pack_id' => $pack_id,
                'nom_pack' => $pack['nom_pack'],
                'produit' => $pack['produit'],
                'total_pack' => $pack['total_pack'],
                'cover_pack' => $pack['cover_pack'],
                'quantity' => $quantity
            ];
        }
        setcookie('cart', json_encode($cart), time() + (30 * 24 * 60 * 60), '/');
    }
    header("Location: panier.php");
    exit();
}

// Fetch similar packs
$similar_packs = [];
if (!empty($pack['type'])) {
    $stmt = $conn->prepare("SELECT * FROM pack WHERE type = ? AND id_pack != ? LIMIT 3");
    $stmt->bind_param("si", $pack['type'], $pack_id);
    $stmt->execute();
    $similar_packs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - <?php echo htmlspecialchars($pack['nom_pack']); ?>  </title>
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        :root {
            --primary-color: #1e4494;
            --secondary-color: #8fc73f;
        }

        .btn-add-to-cart {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }

        .btn-add-to-cart:hover {
            background-color: #7db32f;
            border-color: #7db32f;
        }

        .product-image-container {
            background-color: #d9d9d9;
            border-radius: 12px;
            position: relative;
            height: 380px;
        }

        .wishlist-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .product-image {
            width: 100%;
            height: 350px;
            object-fit: contain;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border: none;
            background-color: white;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-input {
            width: 40px;
            text-align: center;
            border: none;
            font-size: 18px;
            margin: 0 10px;
        }

        .tab-buttons {
            border-bottom: 1px solid #d9d9d9;
            margin-bottom: 20px;
        }

        .tab-btn {
            background: none;
            border: none;
            padding: 10px 0;
            margin-right: 30px;
            font-weight: 500;
            position: relative;
        }

        .tab-btn.active {
            color: var(--secondary-color);
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: var(--secondary-color);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .section-title {
            display: flex;
            align-items: center;
            margin: 40px 0;
            text-align: center;
        }

        .section-title::before,
        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background-color: #d9d9d9;
        }

        .section-title h2 {
            margin: 0 20px;
            color: var(--primary-color);
            font-weight: bold;
            font-size: 1.75rem;
        }

        .similar-product {

            border-radius: 12px;
            border: 1px solid #d9d9d9;
            margin-bottom: 15px;
            position: relative;
            transition: transform 0.3s ease;
            max-width: 400px;
            max-height: 300px;
        }

        .similar-product-info {
            padding: 7px;

        }

        .similar-product img {
            width: 100%;
            height: 200px;
            object-fit: contain;
            background-color: #d9d9d9;
        }

        .similar-product-info h6 {
            margin-bottom: 2px;
            /* ou 0 si tu veux vraiment les coller */
            line-height: 1.2;
        }

        .similar-product-info p {
            margin-bottom: 2px;
            /* ou 0 si tu veux vraiment les coller */
            line-height: 1.2;
            font-size:14px;
            /* tu peux ajuster selon le besoin */
        }

        .description-text {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.5;
            max-height: 4.5em;
        }

        .description-text.expanded {
            -webkit-line-clamp: unset;
            max-height: none;
        }

        .review-stats {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .review-form-container {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }

        .star-rating-input {
            display: flex;
            gap: 5px;
            margin-bottom: 10px;
        }

        .star-rating-input i {
            font-size: 18px;
            color: #ddd;
            cursor: pointer;
        }

        .star-rating-input i.active,
        .star-rating-input i:hover {
            color: #ffc107;
        }

        .review-form .btn-primary {
            background-color: #8FC73F;
            border: none;
            padding: 10px 20px;
        }

        .review-form .btn-primary:hover {
            background-color: #7db336;
        }

        .star-distribution .progress {
            height: 8px;
            background-color: #e9ecef;
        }

        .star-distribution .progress-bar {
            background-color: #ffc107;
        }

        .star-rating-display i {
            color: #ffc107;
            font-size: 18px;
        }

        .text-success {
            color: #8FC73F !important;
        }

        .user-review {
            background-color: #ffffff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .user-review .delete-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 18px;
        }

        .user-review .delete-btn:hover {
            color: #c82333;
        }

        .toggle-description {
            display: none;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="container py-4">
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="product-image-container">

<img src="<?php echo !empty($pack['cover_pack']) ? '../assets/img/pack/' . htmlspecialchars($pack['cover_pack']) : 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($pack['nom_pack']); ?>" class="product-image">
                </div>
            </div>

            <div class="col-md-6">
                <h3 class="mb-2 text-black"><?php echo htmlspecialchars($pack['nom_pack']); ?></h3>
                <h2 class="mb-4 text-black fw-bold"><?php echo number_format($pack['total_pack'], 2); ?> TDN</h2>
                <p class="text-muted mb-4"><?php echo htmlspecialchars($pack['produit']); ?></p>

                <div class="d-flex align-items-center mb-4">
                    <div class="quantity-selector me-3">
                        <button class="quantity-btn" id="decrease-quantity"><i class="fas fa-minus"></i></button>
                        <input type="text" class="quantity-input" id="quantity" value="1" readonly>
                        <button class="quantity-btn" id="increase-quantity"><i class="fas fa-plus"></i></button>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="pack_id" value="<?php echo $pack_id; ?>">
                        <input type="hidden" name="quantity" id="cart-quantity" value="1">
                        <input type="hidden" name="add_to_cart" value="1">
                        <button type="submit" class="btn btn-add-to-cart px-4">Ajouter au panier</button>
                    </form>
                    <form method="POST" action="">
                        <input type="hidden" name="pack_id" value="<?php echo $pack_id; ?>">
                        <input type="hidden" name="toggle_wishlist" value="1">
                        <button type="submit" class="btn ms-3">
                            <i class="<?php echo in_array($pack_id, $_SESSION['wishlist']) ? 'fas' : 'far'; ?> fa-heart"></i>
                        </button>
                    </form>
                </div>

                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="description">Description</button>
                    <button class="tab-btn" data-tab="avis">Avis</button>
                </div>

                <div id="description" class="tab-content active">
                    <div class="description-text" id="description-text">
                        <?php echo htmlspecialchars($pack['descriptions'] ?? 'High-waist sports leggings with an elasticated waistband. Sculpts and shapes the legs. Tailored fit. Small back pocket inside the waistband and made of soft technical fabric.'); ?>
                    </div>
                    <p class="text-end text-muted small toggle-description" id="toggle-description">voir plus</p>
                </div>

                <div id="avis" class="tab-content">
                    <div class="review-stats">
                        <h4>Note globale</h4>
                        <div class="d-flex align-items-center mb-2">
                            <span class="fs-4 me-2"><?php echo number_format($avg_rating, 1); ?></span>
                            <div class="star-rating-display me-2">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?php echo $i <= round($avg_rating) ? '' : 'text-muted'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span>(<?php echo count($reviews); ?> avis)</span>
                        </div>
                        <div class="star-distribution">
                            <?php
                            $star_counts = array_fill(1, 5, 0);
                            foreach ($reviews as $review) {
                                $star_counts[$review['etoiles']]++;
                            }
                            for ($i = 5; $i >= 1; $i--):
                            ?>
                                <div class="d-flex align-items-center mb-1">
                                    <span class="me-2"><?php echo $i; ?> étoiles</span>
                                    <div class="progress flex-grow-1">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo count($reviews) > 0 ? ($star_counts[$i] / count($reviews)) * 100 : 0; ?>%" aria-valuenow="<?php echo $star_counts[$i]; ?>" aria-valuemin="0" aria-valuemax="<?php echo count($reviews); ?>"></div>
                                    </div>
                                    <span class="ms-2"><?php echo $star_counts[$i]; ?></span>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div id="user-review-container">
                        <?php if ($has_reviewed && $user_review): ?>
                            <div class="user-review">
                                <div class="stars">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <i class="fas fa-star <?php echo $i <= $user_review['etoiles'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ms-2 text-muted small"><?php echo date('d/m/Y', strtotime($user_review['date_avis'])); ?></span>
                                </div>
                                <button class="delete-btn" data-pack-id="<?php echo $pack_id; ?>" title="Supprimer l'avis"><i class="fas fa-trash-alt"></i></button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="review-form-container" id="review-form-container">
                        <h4>Donnez votre avis</h4>
                        <div id="review-message"></div>
                        <?php if (isset($_SESSION['id_client'])): ?>
                            <?php if ($has_reviewed): ?>
                                <p class="text-muted">Vous avez déjà soumis un avis pour ce pack.</p>
                            <?php else: ?>
                                <form class="review-form" id="review-form">
                                    <div class="star-rating-input">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star" data-rating="<?php echo $i; ?>"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <input type="hidden" name="rating" id="rating" value="0">
                                    <input type="hidden" name="pack_id" value="<?php echo $pack_id; ?>">
                                    <button type="submit" class="btn btn-primary">Envoyer</button>
                                </form>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="mb-2">Vous devez être connecté pour donner votre avis.</p>
                            <a href="login.php" class="text-success">Se connecter</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <div class="section-title">
                <h2>Pack Équivalent</h2>
            </div>
            <?php if (!empty($similar_packs)): ?>
                <div class="row">
                    <?php foreach ($similar_packs as $similar): ?>
                        <div class="col-md-4 mb-4">
                            <a href="detail_pack.php?id=<?php echo $similar['id_pack']; ?>" class="text-decoration-none text-dark">
                                <div class="similar-product">
                                    <form method="POST">
                                        <input type="hidden" name="pack_id" value="<?php echo $similar['id_pack']; ?>">
                                        <input type="hidden" name="toggle_wishlist" value="1">
                                        <button type="submit" class="wishlist-btn">
                                            <i class="<?php echo in_array($similar['id_pack'], $_SESSION['wishlist']) ? 'fas' : 'far'; ?> fa-heart"></i>
                                        </button>
                                    </form>
                                    <img src="<?php echo !empty($similar['cover_pack']) ? '../assets/img/pack/'.htmlspecialchars($similar['cover_pack']) : 'assets/images/placeholder.jpg'; ?>" alt="<?php echo htmlspecialchars($similar['nom_pack']); ?>" class="img-fluid">

                                    <div class="similar-product-info">
                                        <h6 class="similar-product-title fw-bold"><?php echo htmlspecialchars($similar['nom_pack']); ?></h6>
                                        <p class="similar-product-subtitle"><?php echo htmlspecialchars($similar['produit']); ?></p>
                                        <p class="similar-product-price fw-bold"><?php echo number_format($similar['total_pack'], 2); ?> TDN</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">Aucun pack équivalent trouvé pour le moment.</div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tab functionality
            $('.tab-btn').click(function() {
                $('.tab-btn').removeClass('active');
                $(this).addClass('active');
                $('.tab-content').removeClass('active');
                $('#' + $(this).data('tab')).addClass('active');
            });

            // Description toggle
            function checkDescriptionHeight() {
                const $text = $('#description-text');
                const lineHeight = parseFloat($text.css('line-height'));
                const maxHeight = lineHeight * 3;
                if ($text[0].scrollHeight > maxHeight) {
                    $('#toggle-description').show();
                } else {
                    $('#toggle-description').hide();
                }
            }
            checkDescriptionHeight();

            $('#toggle-description').click(function() {
                const $text = $('#description-text');
                const $link = $(this);
                if ($text.hasClass('expanded')) {
                    $text.removeClass('expanded');
                    $link.text('voir plus');
                } else {
                    $text.addClass('expanded');
                    $link.text('voir moins');
                }
            });

            // Quantity selector
            $('#increase-quantity').click(function() {
                let quantity = parseInt($('#quantity').val());
                $('#quantity').val(quantity + 1);
                $('#cart-quantity').val(quantity + 1);
            });
            $('#decrease-quantity').click(function() {
                let quantity = parseInt($('#quantity').val());
                if (quantity > 1) {
                    $('#quantity').val(quantity - 1);
                    $('#cart-quantity').val(quantity - 1);
                }
            });

            // Star rating
            function bindStarRating() {
                $('.star-rating-input .fa-star').off('click').on('click', function() {
                    const rating = $(this).data('rating');
                    $('#rating').val(rating);
                    $('.star-rating-input .fa-star').each(function() {
                        $(this).toggleClass('active text-warning', $(this).data('rating') <= rating)
                            .toggleClass('text-muted', $(this).data('rating') > rating);
                    });
                });
            }
            bindStarRating();

            // Review submission
            $(document).on('submit', '#review-form', function(e) {
                e.preventDefault();
                const formData = $(this).serializeArray();
                formData.push({
                    name: 'ajax_action',
                    value: 'submit_review'
                });

                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        $('#review-message').empty();
                        if (response.success) {
                            const reviewHtml = `
                        <div class="user-review">
                            <div class="stars">
                                ${'<i class="fas fa-star text-warning"></i>'.repeat(response.review.etoiles)}
                                ${'<i class="fas fa-star text-muted"></i>'.repeat(5 - response.review.etoiles)}
                                <span class="ms-2 text-muted small">${new Date(response.review.date_avis).toLocaleDateString('fr-FR')}</span>
                            </div>
                            <button class="delete-btn" data-pack-id="${formData.find(item => item.name === 'pack_id').value}" title="Supprimer l'avis"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    `;
                            $('#user-review-container').html(reviewHtml);
                            $('#review-form-container').html('<p class="text-muted">Vous avez déjà soumis un avis pour ce pack.</p>');
                            updateReviewStats(response.stats);
                        } else {
                            $('#review-message').html(`<div class="alert alert-danger">${response.message}</div>`);
                        }
                    },
                    error: function() {
                        $('#review-message').html('<div class="alert alert-danger">Erreur. Veuillez réessayer.</div>');
                    }
                });
            });

            // Review deletion
            $(document).on('click', '.delete-btn', function() {
                const packId = $(this).data('pack-id');
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: {
                        ajax_action: 'delete_review',
                        pack_id: packId
                    },
                    dataType: 'json',
                    success: function(response) {
                        $('#review-message').empty();
                        if (response.success) {
                            $('#user-review-container').empty();
                            const formHtml = `
                        <h4>Donnez votre avis</h4>
                        <div id="review-message"></div>
                        <form class="review-form" id="review-form">
                            <div class="star-rating-input">
                                ${Array.from({length: 5}, (_, i) => ` < i class = "fas fa-star"
                            data - rating = "${i + 1}" > < /i>`).join('')} <
                                /div> <
                                input type = "hidden"
                            name = "rating"
                            id = "rating"
                            value = "0" >
                                <
                                input type = "hidden"
                            name = "pack_id"
                            value = "${packId}" >
                                <
                                button type = "submit"
                            class = "btn btn-primary" > Envoyer < /button> <
                                /form>
                            `;
                    $('#review-form-container').html(formHtml);
                    bindStarRating(); // Rebind star rating event
                    updateReviewStats(response.stats);
                } else {
                    $('#review-message').html(` < div class = "alert alert-danger" > $ {
                                response.message
                            } < /div>`);
                        }
                    },
                    error: function() {
                        $('#review-message').html('<div class="alert alert-danger">Erreur. Veuillez réessayer.</div>');
                    }
                });
            });

            // Update review stats
            function updateReviewStats(stats) {
                $('.review-stats .fs-4').text(stats.avg_rating);
                $('.star-rating-display').html(
                    Array.from({
                        length: 5
                    }, (_, i) => `<i class="fas fa-star ${i < Math.round(stats.avg_rating) ? '' : 'text-muted'}"></i>`).join('')
                );
                $('.review-stats span:last').text(`(${stats.total_reviews} avis)`);
                $('.star-distribution').html(
                    Array.from({
                        length: 5
                    }, (_, i) => {
                        const stars = 5 - i;
                        const percentage = stats.total_reviews > 0 ? (stats.star_counts[stars] / stats.total_reviews) * 100 : 0;
                        return `
                    <div class="d-flex align-items-center mb-1">
                        <span class="me-2">${stars} étoiles</span>
                        <div class="progress flex-grow-1">
                            <div class="progress-bar" role="progressbar" style="width: ${percentage}%" aria-valuenow="${stats.star_counts[stars]}" aria-valuemin="0" aria-valuemax="${stats.total_reviews}"></div>
                        </div>
                        <span class="ms-2">${stats.star_counts[stars]}</span>
                    </div>
                `;
                    }).join('')
                );
            }
        });
    </script>

    <?php $conn->close(); ?>
</body>

</html>