<?php
session_start();

// DB Connection
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

$user = [];

if (isset($_SESSION['id_client'])) {
    $id_client = $_SESSION['id_client'];

    $stmt = $pdo->prepare("SELECT prenom_client FROM client WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $user = ['prenom_client' => 'Utilisateur'];
    }
} else {
    header("Location: login.php");
    exit();
}

// Handle review deletion
if (isset($_POST['delete_avis']) && isset($_POST['id_avis'])) {
    $id_avis = intval($_POST['id_avis']);
    $stmt = $pdo->prepare("DELETE FROM avis WHERE id_avis = ? AND id_client = ?");
    $stmt->execute([$id_avis, $id_client]);
}

// Points de fidélité
try {
    $stmt = $pdo->prepare("SELECT SUM(total) as total_spent FROM commande WHERE id_client = ?");
    $stmt->execute([$id_client]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $points = $result['total_spent'] ? floor($result['total_spent']) : 0;
} catch (PDOException $e) {
    $points = 0;
}

// Initialize wishlist for packs
if (!isset($_SESSION['wishlist'])) {
    if (isset($_COOKIE['wishlist'])) {
        $_SESSION['wishlist'] = json_decode($_COOKIE['wishlist'], true);
    } else {
        $_SESSION['wishlist'] = [];
    }
}

// Initialize wishlist for products
if (!isset($_SESSION['wishlist_products'])) {
    if (isset($_COOKIE['wishlist_products'])) {
        $_SESSION['wishlist_products'] = json_decode($_COOKIE['wishlist_products'], true);
    } else {
        $_SESSION['wishlist_products'] = [];
    }
}

// Set cookies to persist wishlists
if (!empty($_SESSION['wishlist'])) {
    setcookie('wishlist', json_encode($_SESSION['wishlist']), time() + (90 * 24 * 60 * 60), '/');
} else {
    setcookie('wishlist', '', time() - 3600, '/');
}

if (!empty($_SESSION['wishlist_products'])) {
    setcookie('wishlist_products', json_encode($_SESSION['wishlist_products']), time() + (90 * 24 * 60 * 60), '/');
} else {
    setcookie('wishlist_products', '', time() - 3600, '/');
}

// Calculate total wishlist count
$wishlist_count = count($_SESSION['wishlist']) + count($_SESSION['wishlist_products']);

// Fetch wishlist packs
$wishlist_packs = [];
if (!empty($_SESSION['wishlist'])) {
    $wishlist_pack_ids = array_map('intval', $_SESSION['wishlist']);
    $placeholders = implode(',', array_fill(0, count($wishlist_pack_ids), '?'));
    $sql = "SELECT * FROM pack WHERE id_pack IN ($placeholders)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($wishlist_pack_ids));
    $wishlist_packs = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch wishlist products
$wishlist_products = [];
if (!empty($_SESSION['wishlist_products'])) {
    $wishlist_product_ids = array_map('intval', $_SESSION['wishlist_products']);
    $placeholders = implode(',', array_fill(0, count($wishlist_product_ids), '?'));
    $sql = "SELECT p.*, i.image_path 
            FROM produits p 
            LEFT JOIN images i ON p.id_produit = i.id_produit 
            WHERE p.id_produit IN ($placeholders) 
            GROUP BY p.id_produit";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($wishlist_product_ids));
    $wishlist_products = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch user reviews
$reviews = [];
$stmt = $pdo->prepare("
    SELECT a.*, p.lib_produit 
    FROM avis a 
    JOIN produits p ON a.id_produit = p.id_produit 
    WHERE a.id_client = ? 
    ORDER BY a.date_avis DESC
");
$stmt->execute([$id_client]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Produits récemment vus
$recentlyViewed = [];
if (!empty($_SESSION['viewed_products'])) {
    $placeholders = rtrim(str_repeat('?,', count($_SESSION['viewed_products'])), ',');
    $query = "SELECT * FROM produits WHERE id_produit IN ($placeholders) ORDER BY FIELD(id_produit, $placeholders)";
    $params = array_merge($_SESSION['viewed_products'], $_SESSION['viewed_products']);
    try {
        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        $recentlyViewed = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $recentlyViewed = [];
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title> GOAT'S - Mon Profil </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
            background-color: #f8f9fa;
        }

        .profile-content {
            background: white;
            border-radius: 10px;
            border: solid black 1px;
            padding: 1.5rem;
        }

        .feature-card {
            padding: 1rem;
            text-align: center;
            border-radius: 8px;
        }

        .feature-card img {
            width: 40px;
            height: 40px;
            margin-bottom: 0.5rem;
        }

        .feature-card .count {
            font-size: 1.5rem;
            font-weight: bold;
        }

        .product-card {
            position: relative;
            cursor: pointer;
            overflow: hidden;
            width: 65px;
            height: 65px;
        }

        .product-card img {
            width: 65px;
            height: 65px;
            object-fit: cover;
        }

        .product-card .overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .product-card:hover .overlay {
            opacity: 1;
        }

        .wishlist-count-badge {
            color: #637381;
            font-size: 0.9rem;
            margin-left: 0.5rem;
        }

        .reviews-section {
            display: none;
            margin-top: 1rem;
        }

        .reviews-section.show {
            display: block;
        }

        .review-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 0.75rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .review-card .stars {
            color: #f1c40f;
        }

        .review-card .delete-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
        }

        /* Flexbox layout for sidebar and main content */
        .main-container {
            display: flex;
            flex-direction: row;
            min-height: calc(100vh - 56px); /* Adjust based on header height */
        }

        /* Sidebar styling */
        .sidebar-container {
            width: 70px; /* Match navbar width on small screens */
            flex-shrink: 0;
        }

        /* Main content styling */
        .content-container {
            flex-grow: 1;
            padding: 1rem;
        }

        /* Desktop styles (md and up) */
        @media (min-width: 768px) {
            .sidebar-container {
                width: 250px; /* Match navbar width on larger screens */
            }
            .content-container {
                padding: 1.5rem;
            }
            .profile-content {
                padding: 1.5rem;
            }
            .feature-card img {
                width: 40px;
                height: 40px;
            }
            .feature-card .count {
                font-size: 1.5rem;
            }
            h1 {
                font-size: 2rem;
            }
            h5 {
                font-size: 1.25rem;
            }
        }

        /* Small screen adjustments */
        @media (max-width: 575.98px) {
            .content-container {
                padding: 0.5rem;
            }
            .profile-content {
                padding: 1rem;
            }
            .feature-card img {
                width: 30px;
                height: 30px;
            }
            .feature-card .count {
                font-size: 1.2rem;
            }
            .product-card img {
                width: 50px;
                height: 50px;
            }
            .product-card {
                width: 50px;
                height: 50px;
            }
            h1 {
                font-size: 1.5rem;
            }
            h5 {
                font-size: 1rem;
            }
            .wishlist, .recently-viewed {
                padding: 0.5rem;
            }
            .wishlist-count-badge {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>

    <div class="container-fluid main-container mt-4">
        <!-- Sidebar -->
        <div class="sidebar-container mt-lg-4">
            <?php include('navbar.php'); ?>
        </div>

        <!-- Main Content -->
        <div class="content-container ">
            <div class="row">
                <!-- Main Profile Content -->
                <div class="col-12 col-lg-8 mb-3">
                    <div class="profile-content">
                        <h4 class="fw-bold blue_goats">Salut, <?php echo htmlspecialchars($user['prenom_client']); ?></h4>
                        <h5 class="blue_goats">Bienvenue dans votre espace GOAT'S.</h5>

                        <div class="row mt-4">
                            <div class="col-12 col-sm-6 col-md-4 mb-3">
                                <div class="feature-card">
                                    <img src="../assets/img/icons/points.svg" alt="Points">
                                    <h5 class="blue_goats">Points</h5>
                                    <div class="count blue_goats"><?php echo $points; ?></div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 mb-3">
                                <div class="feature-card">
                                    <img src="../assets/img/icons/giftcard.svg" alt="Gift Card">
                                    <h5 class="blue_goats">Carte Cadeaux</h5>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-md-4 mb-3">
                                <div class="feature-card">
                                    <img src="../assets/img/icons/coupon.svg" alt="Coupon">
                                    <h5 class="blue_goats">Coupons</h5>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <a href="#" class="fw-bold text-decoration-none blue_goats toggle-reviews">Voir vos avis <i class="fa fa-chevron-down"></i></a>
                        </div>

                        <div class="reviews-section" id="reviews-section">
                            <?php if (empty($reviews)): ?>
                                <div class="alert alert-light text-center">Vous n'avez pas encore laissé d'avis.</div>
                            <?php else: ?>
                                <?php foreach ($reviews as $review): ?>
                                    <div class="review-card">
                                        <h6>
                                            <a href="produit.php?id=<?php echo $review['id_produit']; ?>" class="text-decoration-none">
                                                <?php echo htmlspecialchars($review['lib_produit']); ?>
                                            </a>
                                        </h6>
                                        <div class="stars">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $review['etoiles'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <p><small><?php echo date('d/m/Y H:i', strtotime($review['date_avis'])); ?></small></p>
                                        <form method="POST" class="delete-btn">
                                            <input type="hidden" name="id_avis" value="<?php echo $review['id_avis']; ?>">
                                            <button type="submit" name="delete_avis" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Wishlist and Recently Viewed -->
                <div class="col-12 col-lg-4">
                    <div class="wishlist px-3 py-2">
                        <h5 class="fw-bold d-flex justify-content-between align-items-center">
                            Wishlist
                            <a href="wishlist.php" class="text-decoration-none fw-normal">
                                <span class="wishlist-count-badge"><?php echo $wishlist_count . " Articles"; ?><i class="fa fa-chevron-right ms-2"></i></span>
                            </a>
                        </h5>
                        <?php if (empty($wishlist_packs) && empty($wishlist_products)): ?>
                            <div class="alert alert-light text-center">Votre wishlist est vide.</div>
                        <?php else: ?>
                            <div class="row row-cols-2 row-cols-md-3 g-2">
                                <!-- Display Packs -->
                                <?php foreach ($wishlist_packs as $pack): ?>
                                    <div class="col">
                                        <div class="product-card">
                                            <a href="detail_pack.php?id=<?php echo $pack['id_pack']; ?>" class="text-decoration-none">
                                                <img src="<?php echo '../assets/img/pack/'.htmlspecialchars($pack['cover_pack'] ?: 'images/placeholder.jpg'); ?>" 
                                                     alt="<?php echo htmlspecialchars($pack['nom_pack']); ?>" 
                                                     class="img-fluid">
                                                <div class="overlay"></div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                                <!-- Display Products -->
                                <?php foreach ($wishlist_products as $product): ?>
                                    <div class="col">
                                        <div class="product-card">
                                            <a href="produit.php?id=<?php echo $product['id_produit']; ?>" class="text-decoration-none">
                                                <img src="<?php echo '../' . htmlspecialchars($product['image_path'] ?: 'images/placeholder.jpg'); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['lib_produit']); ?>" 
                                                     class="img-fluid">
                                                <div class="overlay"></div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="recently-viewed mt-4 px-3 py-2">
                        <h5 class="fw-bold d-flex justify-content-between align-items-center">
                            Récemment vus
                            <a href="wishlist.php" class="text-decoration-none fw-normal">
                                <span class="wishlist-count-badge">Explorez<i class="fa fa-chevron-right ms-2"></i></span>
                            </a>
                        </h5>
                        <?php if (empty($recentlyViewed)): ?>
                            <div class="alert alert-light text-center">Vous n'avez pas encore consulté de produits.</div>
                        <?php else: ?>
                            <div class="row row-cols-2 row-cols-md-3 g-2">
                                <?php foreach ($recentlyViewed as $product): ?>
                                    <?php
                                    try {
                                        $imgStmt = $pdo->prepare("SELECT image_path FROM images WHERE id_produit = ? ORDER BY ordre ASC LIMIT 1");
                                        $imgStmt->execute([$product['id_produit']]);
                                        $imgPath = $imgStmt->fetchColumn() ?: 'images/placeholder.jpg';
                                    } catch (PDOException $e) {
                                        $imgPath = 'images/placeholder.jpg';
                                    }
                                    ?>
                                    <div class="col">
                                        <div class="product-card">
                                            <a href="produit.php?id=<?php echo $product['id_produit']; ?>" class="text-decoration-none">
                                                <img src="<?php echo '../' . htmlspecialchars($imgPath); ?>" 
                                                     alt="<?php echo htmlspecialchars($product['lib_produit']); ?>" 
                                                     class="img-fluid">
                                                <div class="overlay"></div>
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.toggle-reviews').click(function(e) {
                e.preventDefault();
                $('#reviews-section').toggleClass('show');
                $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
            });

            $('.delete-btn form').on('submit', function(e) {
                return confirm('Voulez-vous vraiment supprimer cet avis ?');
            });
        });
    </script>
</body>
</html