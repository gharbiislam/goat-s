<?php
session_start();
include('db.php');

// Initialize wishlist
$wishlist = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : (isset($_COOKIE['wishlist']) ? json_decode($_COOKIE['wishlist'], true) : []);

// Initialize cart
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
$cart_updated = false;

$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($product_id <= 0) {
    header("Location: index.php");
    exit;
}

// Check if product is in wishlist
$is_in_wishlist = in_array($product_id, $wishlist);

// Track viewed products
if (!isset($_SESSION['viewed_products'])) {
    $_SESSION['viewed_products'] = [];
}
$_SESSION['viewed_products'] = array_diff($_SESSION['viewed_products'], [$product_id]);
array_unshift($_SESSION['viewed_products'], $product_id);
$_SESSION['viewed_products'] = array_slice($_SESSION['viewed_products'], 0, 4);

// Get product details
$sql = "SELECT p.*, m.nom_marque, c.libelle_categorie 
        FROM produits p 
        LEFT JOIN marque m ON p.id_marque = m.id_marque 
        LEFT JOIN categorie c ON p.id_categorie = c.id_categorie 
        WHERE p.id_produit = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
if (mysqli_num_rows($result) == 0) {
    header("Location: index.php");
    exit;
}
$product = mysqli_fetch_assoc($result);

// Get prix_variant for alimentaire products if prix_produit is 0
if ($product['prix_produit'] == 0) {
    $sql_variant = "SELECT prix_variant FROM alimentaire WHERE id_produit = ? LIMIT 1";
    $stmt_variant = mysqli_prepare($conn, $sql_variant);
    mysqli_stmt_bind_param($stmt_variant, "i", $product_id);
    mysqli_stmt_execute($stmt_variant);
    $result_variant = mysqli_stmt_get_result($stmt_variant);
    if ($row_variant = mysqli_fetch_assoc($result_variant)) {
        $product['prix_produit'] = $row_variant['prix_variant'];
    }
}

// Determine product type and get gender for vetement
$category_type = "unknown";
$product_gender = null;
$tables = ['vetement' => ['couleur', 'taille', 'genre'], 'accessoire' => ['couleur', 'taille'], 'alimentaire' => ['format', 'saveur']];
foreach ($tables as $table => $fields) {
    $fields_str = implode(',', $fields);
    $sql = "SELECT $fields_str FROM $table WHERE id_produit = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        error_log("Query failed for $table: " . mysqli_error($conn));
        continue;
    }
    if (mysqli_num_rows($result) > 0) {
        $category_type = $table;
        if ($table === 'vetement') {
            $row = mysqli_fetch_assoc($result);
            $product_gender = $row['genre'] ?? null;
        }
        break;
    }
}

// Get complementary products
$comp_products = [];
$parent_cat_sql = "SELECT id_categorie_parent FROM categorie WHERE id_categorie = ?";
$parent_cat_stmt = mysqli_prepare($conn, $parent_cat_sql);
mysqli_stmt_bind_param($parent_cat_stmt, "i", $product['id_categorie']);
mysqli_stmt_execute($parent_cat_stmt);
$parent_cat_result = mysqli_stmt_get_result($parent_cat_stmt);
$parent_cat = mysqli_fetch_assoc($parent_cat_result)['id_categorie_parent'] ?? null;

if ($parent_cat) {
    // First, get all subcategories under the parent category
    $subcat_sql = "SELECT id_categorie FROM categorie WHERE id_categorie_parent = ?";
    $subcat_stmt = mysqli_prepare($conn, $subcat_sql);
    mysqli_stmt_bind_param($subcat_stmt, "i", $parent_cat);
    mysqli_stmt_execute($subcat_stmt);
    $subcat_result = mysqli_stmt_get_result($subcat_stmt);
    $subcategories = [];
    while ($row = mysqli_fetch_assoc($subcat_result)) {
        $subcategories[] = $row['id_categorie'];
    }

    // Remove current product's category from subcategories
    $subcategories = array_diff($subcategories, [$product['id_categorie']]);

    // Get one product from each subcategory until we have 3 products
    $selected_subcats = [];
    $comp_products = [];
    
    foreach ($subcategories as $subcat) {
        if (count($comp_products) >= 3) break;
        
        $comp_sql = "SELECT p.*, m.nom_marque, i.image_path 
                     FROM produits p 
                     LEFT JOIN marque m ON p.id_marque = m.id_marque 
                     LEFT JOIN images i ON p.id_produit = i.id_produit 
                     WHERE p.id_categorie = ? 
                     AND p.id_produit != ?";
        
        if ($category_type === 'vetement' && $product_gender) {
            $comp_sql .= " AND EXISTS (SELECT 1 FROM vetement v WHERE v.id_produit = p.id_produit AND v.genre = ?)";
        }
        
        $comp_sql .= " GROUP BY p.id_produit LIMIT 1";
        $comp_stmt = mysqli_prepare($conn, $comp_sql);
        
        if ($category_type === 'vetement' && $product_gender) {
            mysqli_stmt_bind_param($comp_stmt, "iis", $subcat, $product_id, $product_gender);
        } else {
            mysqli_stmt_bind_param($comp_stmt, "ii", $subcat, $product_id);
        }
        
        mysqli_stmt_execute($comp_stmt);
        $comp_result = mysqli_stmt_get_result($comp_stmt);
        
        if ($row = mysqli_fetch_assoc($comp_result)) {
            $comp_products[] = $row;
            $selected_subcats[] = $subcat;
        }
    }

    // If we still need more products, get them from any remaining subcategories
    if (count($comp_products) < 3) {
        $remaining_subcats = array_diff($subcategories, $selected_subcats);
        foreach ($remaining_subcats as $subcat) {
            if (count($comp_products) >= 3) break;
            
            $comp_sql = "SELECT p.*, m.nom_marque, i.image_path 
                         FROM produits p 
                         LEFT JOIN marque m ON p.id_marque = m.id_marque 
                         LEFT JOIN images i ON p.id_produit = i.id_produit 
                         WHERE p.id_categorie = ? 
                         AND p.id_produit != ?";
            
            if ($category_type === 'vetement' && $product_gender) {
                $comp_sql .= " AND EXISTS (SELECT 1 FROM vetement v WHERE v.id_produit = p.id_produit AND v.genre = ?)";
            }
            
            $comp_sql .= " GROUP BY p.id_produit LIMIT 1";
            $comp_stmt = mysqli_prepare($conn, $comp_sql);
            
            if ($category_type === 'vetement' && $product_gender) {
                mysqli_stmt_bind_param($comp_stmt, "iis", $subcat, $product_id, $product_gender);
            } else {
                mysqli_stmt_bind_param($comp_stmt, "ii", $subcat, $product_id);
            }
            
            mysqli_stmt_execute($comp_stmt);
            $comp_result = mysqli_stmt_get_result($comp_stmt);
            
            if ($row = mysqli_fetch_assoc($comp_result)) {
                $comp_products[] = $row;
            }
        }
    }
}

// Fetch all images for complementary products, grouped by color
$comp_product_images = [];
foreach ($comp_products as $comp_product) {
    if (!isset($comp_product['id_produit']) || empty($comp_product['id_produit'])) {
        continue;
    }
    $comp_id = $comp_product['id_produit'];
    $image_sql = "SELECT image_path, couleur FROM images WHERE id_produit = ? ORDER BY ordre ASC";
    $image_stmt = mysqli_prepare($conn, $image_sql);
    mysqli_stmt_bind_param($image_stmt, "i", $comp_id);
    mysqli_stmt_execute($image_stmt);
    $image_result = mysqli_stmt_get_result($image_stmt);
    $comp_product_images[$comp_id] = [];
    while ($row = mysqli_fetch_assoc($image_result)) {
        $color = $row['couleur'] ?? 'default';
        if (!isset($comp_product_images[$comp_id][$color])) {
            $comp_product_images[$comp_id][$color] = [];
        }
        $comp_product_images[$comp_id][$color][] = $row['image_path'];
    }
}

// Get product images
$sql_images = "SELECT image_path, couleur FROM images WHERE id_produit = ? ORDER BY ordre ASC";
$images_stmt = mysqli_prepare($conn, $sql_images);
mysqli_stmt_bind_param($images_stmt, "i", $product_id);
mysqli_stmt_execute($images_stmt);
$images_result = mysqli_stmt_get_result($images_stmt);
$images_by_color = [];
$images_by_flavor = [];
while ($row = mysqli_fetch_assoc($images_result)) {
    if ($category_type === 'alimentaire' && !empty($row['couleur'])) {
        $flavor = $row['couleur'];
        if (!isset($images_by_flavor[$flavor])) {
            $images_by_flavor[$flavor] = [];
        }
        $images_by_flavor[$flavor][] = $row;
    } else {
        $color = $row['couleur'] ?? 'default';
        if (!isset($images_by_color[$color])) {
            $images_by_color[$color] = [];
        }
        $images_by_color[$color][] = $row;
    }
}
$colors = array_keys($images_by_color);
$flavors = array_keys($images_by_flavor);
$first_color = $colors[0] ?? 'default';
$first_flavor = $flavors[0] ?? '';

// Get variants and build flavor-format mapping
$variants = [];
$flavor_format_map = [];
$format_flavor_map = [];
foreach ($tables as $table => $fields) {
    $fields_str = implode(',', $fields);
    $sql = "SELECT $fields_str FROM $table WHERE id_produit = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $variants[] = $row;
            if ($table === 'alimentaire') {
                $flavor = $row['saveur'];
                $format = $row['format'];
                if (!isset($flavor_format_map[$flavor])) {
                    $flavor_format_map[$flavor] = [];
                }
                if (!isset($format_flavor_map[$format])) {
                    $format_flavor_map[$format] = [];
                }
                $flavor_format_map[$flavor][] = $format;
                $flavor_format_map[$format][] = $flavor;
            }
        }
        break;
    }
}

// Get complementary product colors
$comp_product_colors = [];
foreach ($comp_products as $comp_product) {
    if (!isset($comp_product['id_produit']) || empty($comp_product['id_produit'])) {
        continue;
    }
    $comp_id = $comp_product['id_produit'];
    foreach (['vetement', 'accessoire'] as $table) {
        $sql = "SELECT couleur FROM $table WHERE id_produit = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $comp_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) > 0) {
            $colors = [];
            while ($row = mysqli_fetch_assoc($result)) {
                if ($row['couleur']) {
                    $colors[] = $row['couleur'];
                }
            }
            $comp_product_colors[$comp_id] = array_unique($colors);
            break;
        }
    }
}

// Get reviews
$sql_reviews = "SELECT * FROM avis WHERE id_produit = ?";
$reviews_stmt = mysqli_prepare($conn, $sql_reviews);
mysqli_stmt_bind_param($reviews_stmt, "i", $product_id);
mysqli_stmt_execute($reviews_stmt);
$reviews_result = mysqli_stmt_get_result($reviews_stmt);
$reviews = mysqli_fetch_all($reviews_result, MYSQLI_ASSOC);

// Calculate average rating
$avg_rating = count($reviews) > 0 ? array_sum(array_column($reviews, 'etoiles')) / count($reviews) : 0;

// Check if current user has already submitted a review
$user_review = null;
$has_submitted_review = false;
if (isset($_SESSION['id_client'])) {
    $client_id = $_SESSION['id_client'];
    $sql = "SELECT * FROM avis WHERE id_client = ? AND id_produit = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $client_id, $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    if (mysqli_num_rows($result) > 0) {
        $has_submitted_review = true;
        $user_review = mysqli_fetch_assoc($result);
    }
}

// Handle Review Deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review']) && isset($_SESSION['id_client'])) {
    $client_id = $_SESSION['id_client'];
    $sql = "DELETE FROM avis WHERE id_client = ? AND id_produit = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $client_id, $product_id);
    if (mysqli_stmt_execute($stmt)) {
        header("Location: produit.php?id=$product_id");
        exit;
    }
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review']) && isset($_SESSION['id_client'])) {
    $rating = intval($_POST['rating']);
    if ($rating >= 1 && $rating <= 5) {
        $client_id = $_SESSION['id_client'];
        $sql = "SELECT id_avis FROM avis WHERE id_client = ? AND id_produit = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $client_id, $product_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if (mysqli_num_rows($result) == 0) {
            $sql = "INSERT INTO avis (id_client, id_produit, etoiles, date_avis) VALUES (?, ?, ?, NOW())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "iii", $client_id, $product_id, $rating);
            mysqli_stmt_execute($stmt);
            header("Location: produit.php?id=$product_id");
            exit;
        }
    }
}

// Handle Add to Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_cart'])) {
    $quantity = max(1, intval($_POST['quantity']));
    $selected_flavor = $_POST['flavor'] ?? $first_flavor;
    $image_path = $images_by_flavor[$selected_flavor][0]['image_path'] ?? ($images_by_color[$first_color][0]['image_path'] ?? 'images/placeholder.jpg');
    $cart_item = [
        'id' => $product_id,
        'name' => $product['lib_produit'],
        'price' => $product['prix_produit'],
        'quantity' => $quantity,
        'image' => $image_path
    ];
    foreach (['color', 'size', 'format', 'flavor'] as $field) {
        $cart_item[$field] = $_POST[$field] ?? '';
    }

    $found = false;
    foreach ($cart as &$item) {
        if (
            $item['id'] == $product_id && $item['color'] == $cart_item['color'] && $item['size'] == $cart_item['size'] &&
            $item['format'] == $cart_item['format'] && $item['flavor'] == $cart_item['flavor']
        ) {
            $item['quantity'] += $quantity;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $cart[] = $cart_item;
    }
    setcookie('cart', json_encode($cart), time() + (30 * 24 * 60 * 60), "/");
    $cart_updated = true;
}

// Get reference number
$reference = $product['ref'] ?? 'REF' . $product_id;

// Determine section title based on product type
$section_title = "ACCESSOIRES COMPLÉMENTAIRES";
if ($category_type === 'vetement') {
    $section_title = "COMPLÈTE TON LOOK";
} elseif ($category_type === 'alimentaire') {
    $section_title = "LE COMBO PARFAIT";
}

// Format date function
function formatDate($date) {
    $timestamp = strtotime($date);
    return date('d/m/Y', $timestamp);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <title>GOAT'S - <?php echo htmlspecialchars($product['lib_produit']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="../assets/css/produit.css">
</head>
<body class="<?php echo $cart_updated ? 'cart-open' : ''; ?>">
    <?php include 'header.php'; ?>

    <!-- Cart Sidebar -->
    <div class="cart-sidebar <?php echo $cart_updated ? 'active' : ''; ?>" id="cart-sidebar">
        <div class="cart-header">
            <h3>Votre panier</h3>
            <span class="close-cart" onclick="closeCartSidebar()">×</span>
        </div>
        <div id="cart-items">
            <?php
            $total = 0;
            foreach ($cart as $index => $item):
                $total += $item['price'] * $item['quantity'];
            ?>
                <div class="cart-item" data-index="<?php echo $index; ?>">
                    <img src="../<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>">
                    <div class="cart-item-details">
                        <p><?php echo htmlspecialchars($item['name']); ?></p>
                        <p><?php echo number_format($item['price'], 2); ?> DT</p>
                        <?php if (!empty($item['color'])): ?>
                            <p>Couleur: <?php echo htmlspecialchars($item['color']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['size'])): ?>
                            <p>Taille: <?php echo htmlspecialchars($item['size']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['format'])): ?>
                            <p>Format: <?php echo htmlspecialchars($item['format']); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['flavor'])): ?>
                            <p>Saveur: <?php echo htmlspecialchars($item['flavor']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="cart-item-controls">
                        <button onclick="updateCartQuantity(<?php echo $index; ?>, -1)">-</button>
                        <span class="cart-item-quantity"><?php echo $item['quantity']; ?></span>
                        <button onclick="updateCartQuantity(<?php echo $index; ?>, 1)">+</button>
                    </div>
                    <button class="cart-item-remove" onclick="removeCartItem(<?php echo $index; ?>)">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="cart-total">
            Total: <span id="cart-total"><?php echo number_format($total, 2); ?></span> DT
        </div>
        <div class="cart-footer">
            <a href="panier.php" class="view-cart-btn">Voir le panier</a>
        </div>
    </div>

    <div class="product-container">
        <!-- Image Gallery -->
        <div class="image-gallery">
            <div class="thumbnails">
                <?php if ($category_type === 'alimentaire' && !empty($images_by_flavor[$first_flavor])): ?>
                    <?php foreach ($images_by_flavor[$first_flavor] as $index => $image): ?>
                        <img src="../<?php echo htmlspecialchars($image['image_path']); ?>"
                            class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>"
                            onclick="changeMainImage('../<?php echo htmlspecialchars($image['image_path']); ?>', this)">
                    <?php endforeach; ?>
                <?php elseif (!empty($images_by_color[$first_color])): ?>
                    <?php foreach ($images_by_color[$first_color] as $index => $image): ?>
                        <img src="../<?php echo htmlspecialchars($image['image_path']); ?>"
                            class="thumbnail <?php echo $index == 0 ? 'active' : ''; ?>"
                            onclick="changeMainImage('../<?php echo htmlspecialchars($image['image_path']); ?>', this)">
                    <?php endforeach; ?>
                <?php else: ?>
                    <img src="../images/placeholder.jpg" class="thumbnail active"
                        onclick="changeMainImage('../images/placeholder.jpg', this)">
                <?php endif; ?>
            </div>
            <div class="main-image-container">
                <img src="../<?php echo htmlspecialchars(($category_type === 'alimentaire' && !empty($images_by_flavor[$first_flavor])) ? $images_by_flavor[$first_flavor][0]['image_path'] : ($images_by_color[$first_color][0]['image_path'] ?? 'images/placeholder.jpg')); ?>"
                    class="main-image" id="main-image" alt="<?php echo htmlspecialchars($product['lib_produit']); ?>">
            </div>
        </div>

        <!-- Product Info -->
        <div class="product-info">
            <h1 class="product-title"><?php echo htmlspecialchars($product['lib_produit']); ?></h1>
            <div class="product-price">
                <?php echo number_format($product['prix_produit'], 2); ?> TND
            </div>
            <div class="product-ref">
                Réf: <?php echo htmlspecialchars($reference); ?>
            </div>

            <form method="POST" id="add-to-cart-form">
                <input type="hidden" name="add_to_cart" value="1">

                <!-- Color Options -->
                <?php if ($category_type === 'vetement' || $category_type === 'accessoire'): ?>
                    <?php
                    $colors = array_unique(array_column($variants, 'couleur'));
                    if (!empty($colors) && $colors[0]): ?>
                        <div class="color-section">
                            <div class="section-title">Couleur :</div>
                            <div class="color-options">
                                <?php foreach ($colors as $i => $color):
                                    $sql = "SELECT image_path FROM images WHERE id_produit = ? AND couleur = ? LIMIT 1";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    mysqli_stmt_bind_param($stmt, "is", $product_id, $color);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    $first_image = mysqli_fetch_assoc($result)['image_path'] ?? 'images/placeholder.jpg';
                                    $sql = "SELECT image_path FROM images WHERE id_produit = ? AND couleur = ? LIMIT 1,1";
                                    $stmt = mysqli_prepare($conn, $sql);
                                    mysqli_stmt_bind_param($stmt, "is", $product_id, $color);
                                    mysqli_stmt_execute($stmt);
                                    $result = mysqli_stmt_get_result($stmt);
                                    $next_image = mysqli_fetch_assoc($result)['image_path'] ?? $first_image;
                                ?>
                                    <div class="color-option <?php echo $i === 0 ? 'active' : ''; ?>"
                                        style="background-color: <?php echo htmlspecialchars($color); ?>"
                                        onclick="changeColor('../<?php echo htmlspecialchars($first_image); ?>', '../<?php echo htmlspecialchars($next_image); ?>', '<?php echo htmlspecialchars($color); ?>', this)">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="color" id="color-input" value="<?php echo htmlspecialchars($colors[0]); ?>">
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="color" id="color-input" value="">
                    <?php endif; ?>

                    <!-- Size Options -->
                    <?php
                    $sizes = array_unique(array_column($variants, 'taille'));
                    if (!empty($sizes) && $sizes[0]): ?>
                        <div class="size-section">
                            <div class="section-title">
                                <span>Taille:</span>
                              
                            </div>
                            <div class="size-options">
                                <?php
                                $all_sizes = [];
                                if ($category_type === 'vetement') {
                                    if ($product_gender === 'unisexe') {
                                        $all_sizes = $sizes;
                                    } elseif ($product['libelle_categorie'] === 'Chaussure') {
                                        if ($product_gender === 'homme') {
                                            $all_sizes = range(40, 45);
                                        } elseif ($product_gender === 'femme') {
                                            $all_sizes = range(36, 40);
                                        }
                                    } else {
                                        $all_sizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                                    }
                                } elseif ($category_type === 'accessoire') {
                                    $all_sizes = $sizes;
                                }

                                foreach ($all_sizes as $i => $size):
                                    $is_available = in_array($size, $sizes);
                                    if ($product_gender === 'unisexe' && !$is_available) continue;
                                    $size_class = $is_available ? ($i === 0 ? 'active' : '') : 'muted';
                                ?>
                                    <div class="size-option <?php echo $size_class; ?>"
                                        <?php if ($is_available): ?>
                                            onclick="selectSize('<?php echo htmlspecialchars($size); ?>', this)"
                                        <?php endif; ?>>
                                        <?php echo htmlspecialchars($size); ?>
                                    </div>
                                <?php endforeach; ?>  <?php if ($category_type === 'vetement'): ?>
                                    <a class="size-guide" onclick="showSizeGuide()"><img src="../assets/img/icons/eye1.svg" alt="" class="mx-2">Guide des tailles</a>
                                <?php endif; ?>
                            </div>
                            <input type="hidden" name="size" id="size-input" value="<?php echo htmlspecialchars($sizes[0] ?? ''); ?>">
                        </div>
                    <?php else: ?>
                        <input type="hidden" name="size" id="size-input" value="">
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Format Options for Alimentaire -->
                <?php if ($category_type === 'alimentaire'): ?>
                    <?php
                    $formats = array_unique(array_column($variants, 'format'));
                    if (!empty($formats) && $formats[0]): ?>
                        <div class="format-section">
                            <div class="section-title">Format :</div>
                            <?php if (count($formats) === 1): ?>
                                <p><?php echo htmlspecialchars($formats[0]); ?></p>
                                <input type="hidden" name="format" value="<?php echo htmlspecialchars($formats[0]); ?>">
                            <?php else: ?>
                                <select name="format" id="format-selector" class="form-select" onchange="updateOptions('format')">
                                    <?php foreach ($formats as $i => $format): ?>
                                        <option value="<?php echo htmlspecialchars($format); ?>" class="format-option <?php echo $i === 0 ? 'active' : ''; ?>">
                                            <?php echo htmlspecialchars($format); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Flavor Options for Alimentaire -->
                    <?php if (!empty($flavors) && $flavors[0]): ?>
                        <div class="flavor-section">
                            <div class="section-title">Saveur :</div>
                            <?php if (count($flavors) === 1): ?>
                                <p><?php echo htmlspecialchars($flavors[0]); ?></p>
                                <input type="hidden" name="flavor" value="<?php echo htmlspecialchars($flavors[0]); ?>">
                            <?php else: ?>
                                <select name="flavor" id="flavor-selector" class="form-select" onchange="updateOptions('flavor'); changeFlavor(this.value)">
                                    <?php foreach ($flavors as $i => $flavor): ?>
                                        <option value="<?php echo htmlspecialchars($flavor); ?>" class="flavor-option <?php echo $i === 0 ? 'active' : ''; ?>">
                                            <?php echo htmlspecialchars($flavor); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>

                <!-- Quantity Selector -->
                <div class="d-flex align-items-center mb-3">
                    <div class="quantity-selector me-3">
                        <button type="button" class="quantity-btn" onclick="decrementQuantity()">-</button>
                        <input type="text" id="quantity" name="quantity" class="quantity-input" value="1" readonly>
                        <button type="button" class="quantity-btn" onclick="incrementQuantity()">+</button>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="action-buttons">
                        <button type="submit" class="add-to-cart">
                            Ajouter au panier
                        </button>
                        <button type="button" class="wishlist" onclick="toggleWishlist(<?php echo $product_id; ?>)">
                            <i class="<?php echo $is_in_wishlist ? 'fas' : 'far'; ?> fa-heart"></i>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Tabs Section -->
            <div class="tabs">
                <div class="tab active" onclick="showTab('description')">Description</div>
                <div class="tab" onclick="showTab('avis')">Avis des clients</div>
            </div>

            <!-- Tab Contents -->
            <div id="description-tab" class="tab-content active">
                <div class="description-text" id="description-text">
                    <?php echo nl2br(htmlspecialchars($product['desc_produit'])); ?>
                </div>
                <div class="toggle-description" id="toggle-description">Voir plus</div>
            </div>

            <div id="avis-tab" class="tab-content">
               <div class="mb-3 globale">
    <h5>Note globale</h5>
    <div class="d-md-flex">
    <div class="mb-2 col-4 d-md-block d-flex align-items-center"> 
            <span class="fs-4 me-2 d-block"><?php echo number_format($avg_rating, 1); ?></span>
            <span class="d-md-block d-flex">
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <i class="fas fa-star <?php echo $i <= round($avg_rating) ? 'text-warning' : 'text-muted'; ?>"></i>
                <?php endfor; ?>
            </span>
            <span class="ms-2  d-md-block d-flex">(<?php echo count($reviews); ?> avis)</span>
        </div>
    <!-- Star Distribution -->
    <div class="star-distribution col-md-8">
        <?php
        $star_counts = array_fill(1, 5, 0);
        foreach ($reviews as $review) {
            $star_counts[$review['etoiles']]++;
        }
        for ($i = 5; $i >= 1; $i--):
        ?>
            <div class="d-flex align-items-center mb-1">
                <span class="me-2"><?php echo $i; ?> étoiles</span>
                <div class="progress flex-grow-1" style="height: 10px;">
                    <div class="progress-bar bg-warning" role="progressbar"
                        style="width: <?php echo count($reviews) > 0 ? ($star_counts[$i] / count($reviews)) * 100 : 0; ?>%"
                        aria-valuenow="<?php echo $star_counts[$i]; ?>" aria-valuemin="0"
                        aria-valuemax="<?php echo count($reviews); ?>"></div>
                </div>
                <span class="ms-2"><?php echo $star_counts[$i]; ?></span>
            </div>
        <?php endfor; ?>
    </div></div>
</div>

                <div class="mt-4 submission">
                    <h5>Votre avis</h5>
                    <?php if (isset($_SESSION['id_client'])): ?>
                        <?php if ($has_submitted_review): ?>
                            <!-- Display user's existing review with delete option -->
                            <div class="user-review-card">
                                <div class="user-review-header">
                                    <div class="d-flex align-items-center">
                                        <div class="user-review-stars me-3">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i class="fas fa-star <?php echo $i <= $user_review['etoiles'] ? 'text-warning' : 'text-muted'; ?>"></i>
                                            <?php endfor; ?>
                                        </div>
                                        <div class="user-review-date">
                                            <?php echo formatDate($user_review['date_avis']); ?>
                                        </div>
                                    </div>
                                    <form method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre avis?');">
                                        <button type="submit" name="delete_review" class="user-review-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>  
                                </div>
                                <div class="review-alert">
                                    Vous avez déjà soumis un avis pour ce produit.
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Review submission form -->
                            <form method="POST" >
                                <div class="mb-3">
                                  
                                    <div class="star-rating d-flex align-items-center">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <i class="fas fa-star text-muted" data-rating="<?php echo $i; ?>" onclick="setRating(<?php echo $i; ?>)"></i>
                                        <?php endfor; ?><button type="submit" name="submit_review" class="add-to-cart p-2 mx-3">Envoyer</button>
                                    </div>
                                    <input type="hidden" name="rating" id="rating" value="5">
                                </div>
                                
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center">
                        <p class="mb-3">Vous devez être connecté pour donner votre avis.</p>
                        <a href="login.php" class="login">Se connecter</a></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Complementary Products Section -->
    <?php if (!empty($comp_products)): ?>
    <div class="section-divider">
        <h2><?php echo $section_title; ?></h2>
    </div>
    <div class="complementary-products">
        <div class="complementary-grid">
            <?php foreach ($comp_products as $comp_product):
                $comp_id = $comp_product['id_produit'] ?? null;
                if (!$comp_id) continue;

                // Get product type for complementary product
                $comp_category_type = "unknown";
                foreach ($tables as $table => $fields) {
                    $fields_str = implode(',', $fields);
                    $sql = "SELECT $fields_str FROM $table WHERE id_produit = ?";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $comp_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if (mysqli_num_rows($result) > 0) {
                        $comp_category_type = $table;
                        break;
                    }
                }

                // Get price - if prix_produit is 0, get prix_variant for alimentaire
                $price = $comp_product['prix_produit'];
                if ($price == 0 && $comp_category_type === 'alimentaire') {
                    $sql = "SELECT prix_variant FROM alimentaire WHERE id_produit = ? LIMIT 1";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $comp_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    if ($row = mysqli_fetch_assoc($result)) {
                        $price = $row['prix_variant'];
                    }
                }

                // Get images based on product type
                if ($comp_category_type === 'alimentaire') {
                    // Get first flavor for alimentaire product
                    $sql = "SELECT DISTINCT saveur FROM alimentaire WHERE id_produit = ? ORDER BY saveur ASC LIMIT 1";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $comp_id);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $first_flavor = mysqli_fetch_assoc($result)['saveur'] ?? '';

                    // Get images for this flavor
                    $sql = "SELECT image_path FROM images WHERE id_produit = ? AND couleur = ? ORDER BY ordre ASC";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, "is", $comp_id, $first_flavor);
                    mysqli_stmt_execute($stmt);
                    $result = mysqli_stmt_get_result($stmt);
                    $images = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    
                    $primaryImage = $images[0]['image_path'] ?? 'images/placeholder.jpg';
                    $nextImage = $images[1]['image_path'] ?? $primaryImage;
                } else {
                    $default_color = $comp_product_colors[$comp_id][0] ?? 'default';
                    $primaryImage = $comp_product_images[$comp_id][$default_color][0] ?? 'images/placeholder.jpg';
                    $nextImage = $comp_product_images[$comp_id][$default_color][1] ?? $primaryImage;
                }

                $is_comp_in_wishlist = in_array($comp_id, $wishlist);
            ?>
                <div class="comp-product-card" data-product-id="<?php echo $comp_id; ?>">
                    <i class="<?php echo $is_comp_in_wishlist ? 'fas' : 'far'; ?> fa-heart wishlist-heart" onclick="toggleWishlist(<?php echo $comp_id; ?>)"></i>
                    <a href="produit.php?id=<?php echo $comp_id; ?>">
                        <div class="compl-img">
                            <img class="primary-image" src="../<?php echo htmlspecialchars($primaryImage); ?>" alt="<?php echo htmlspecialchars($comp_product['lib_produit']); ?>">
                            <img class="next-image" src="../<?php echo htmlspecialchars($nextImage); ?>" alt="<?php echo htmlspecialchars($comp_product['lib_produit']); ?>">
                        </div>
                        <div class="content">
                           
                           
                            <?php
                            if ($comp_category_type !== 'alimentaire' && isset($comp_product_colors[$comp_id]) && !empty($comp_product_colors[$comp_id])) {
                                echo '<div class="color-options">';
                                foreach ($comp_product_colors[$comp_id] as $color) {
                                    $color_images = $comp_product_images[$comp_id][$color] ?? [];
                                    $color_primary = $color_images[0] ?? $primaryImage;
                                    $color_next = $color_images[1] ?? $color_primary;
                                    echo '<div class="color-option" style="background-color: ' . htmlspecialchars($color) . '" data-image="../' . htmlspecialchars($color_primary) . '" data-next-image="../' . htmlspecialchars($color_next) . '" onclick="changeProductImage(this, ' . $comp_id . ')"></div>';
                                }
                                echo '</div>';
                            } else{
                           echo htmlspecialchars($comp_product['nom_marque']);

                            }
                            ?> 
                            <p><?php echo htmlspecialchars($comp_product['lib_produit']); ?></p>
<p class="fw-bold"><?php echo number_format($price, 2); ?> TND</p>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Size Guide Modal -->
    <div class="modal fade" id="size-guide-modal" tabindex="-1" aria-labelledby="sizeGuideModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content" id="size-guide-content">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Flavor-Format mappings
        const flavorFormatMap = <?php echo json_encode($flavor_format_map); ?>;
        const formatFlavorMap = <?php echo json_encode($format_flavor_map); ?>;
        const allFormats = <?php echo json_encode($formats ?? []); ?>;
        const allFlavors = <?php echo json_encode($flavors ?? []); ?>;
        const imagesByColor = <?php echo json_encode($images_by_color); ?>;
        const imagesByFlavor = <?php echo json_encode($images_by_flavor); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize description toggle
            const $text = document.getElementById('description-text');
            const $link = document.getElementById('toggle-description');
            
            function checkDescriptionHeight() {
                const lineHeight = parseFloat(window.getComputedStyle($text).lineHeight);
                const maxHeight = lineHeight * 3;
                
                if ($text.scrollHeight > maxHeight) {
                    $link.style.display = 'block';
                } else {
                    $link.style.display = 'none';
                }
            }

            checkDescriptionHeight();
            window.addEventListener('resize', checkDescriptionHeight);

            $link.addEventListener('click', function() {
                if ($text.classList.contains('expanded')) {
                    $text.classList.remove('expanded');
                    $link.textContent = 'Voir plus';
                } else {
                    $text.classList.add('expanded');
                    $link.textContent = 'Voir moins';
                }
            });

            // Initialize cart sidebar if it was updated
            if (<?php echo $cart_updated ? 'true' : 'false'; ?>) {
                showCartSidebar();
            }
            
            // Initialize rating stars
            const ratingStars = document.querySelectorAll('.star-rating i');
            ratingStars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = parseInt(this.dataset.rating);
                    highlightStars(rating);
                });
                
                star.addEventListener('mouseout', function() {
                    const currentRating = parseInt(document.getElementById('rating').value);
                    highlightStars(currentRating);
                });
            });
            
            // Set initial rating
            setRating(0);

            // Initialize format and flavor selectors
            if (document.getElementById('flavor-selector')) {
                const flavorSelector = document.getElementById('flavor-selector');
                const firstFlavor = flavorSelector.options[0].value;
                updateOptions('flavor'); // Initialize with default flavor
                changeFlavor(firstFlavor); // Initialize with first flavor
            }
        });

        function changeFlavor(flavor) {
            console.log('Changing flavor to:', flavor);
            const mainImage = document.getElementById('main-image');
            const flavorInput = document.getElementById('flavor-selector');
            const thumbnailContainer = document.querySelector('.thumbnails');
            
            if (!mainImage || !flavorInput || !thumbnailContainer) {
                console.error('Required elements not found');
                return;
            }
            
            flavorInput.value = flavor;
            
            const newThumbnails = imagesByFlavor[flavor] || [];
            thumbnailContainer.innerHTML = '';
            
            if (newThumbnails && newThumbnails.length > 0) {
                mainImage.src = '../' + newThumbnails[0].image_path;
                newThumbnails.forEach((img, index) => {
                    let thumb = document.createElement('img');
                    thumb.src = '../' + img.image_path;
                    thumb.className = 'thumbnail ' + (index === 0 ? 'active' : '');
                    thumb.onclick = function() {
                        changeMainImage('../' + img.image_path, this);
                    };
                    thumbnailContainer.appendChild(thumb);
                });
            } else {
                console.warn('No images found for flavor:', flavor);
                mainImage.src = '../images/placeholder.jpg';
                let thumb = document.createElement('img');
                thumb.src = '../images/placeholder.jpg';
                thumb.className = 'thumbnail active';
                thumb.onclick = function() {
                    changeMainImage('../images/placeholder.jpg', this);
                };
                thumbnailContainer.appendChild(thumb);
            }
        }

        function updateOptions(changedSelector) {
            const formatSelector = document.getElementById('format-selector');
            const flavorSelector = document.getElementById('flavor-selector');

            if (!formatSelector || !flavorSelector) return;

            const selectedFlavor = flavorSelector.value;
            const selectedFormat = formatSelector.value;

            if (changedSelector === 'flavor') {
                // Update format options based on selected flavor
                const availableFormats = flavorFormatMap[selectedFlavor] || [];
                Array.from(formatSelector.options).forEach(option => {
                    const format = option.value;
                    if (format && !availableFormats.includes(format)) {
                        option.classList.add('muted');
                        option.disabled = true;
                    } else {
                        option.classList.remove('muted');
                        option.disabled = false;
                    }
                });
                // Ensure selected format is valid for the current flavor
                if (!availableFormats.includes(selectedFormat) && availableFormats.length > 0) {
                    formatSelector.value = availableFormats[0];
                }
            } else if (changedSelector === 'format') {
                // Update flavor options based on selected format
                const availableFlavors = formatFlavorMap[selectedFormat] || [];
                Array.from(flavorSelector.options).forEach(option => {
                    const flavor = option.value;
                    if (flavor && !availableFlavors.includes(flavor)) {
                        option.classList.add('muted');
                        option.disabled = true;
                    } else {
                        option.classList.remove('muted');
                        option.disabled = false;
                    }
                });
                // Ensure selected flavor is valid for the current format
                if (!availableFlavors.includes(selectedFlavor) && availableFlavors.length > 0) {
                    flavorSelector.value = availableFlavors[0];
                    changeFlavor(availableFlavors[0]); // Update images for new flavor
                }
            }
        }

        function changeProductImage(element, productId) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            const card = document.querySelector(`.comp-product-card[data-product-id="${productId}"]`);
            const primaryImage = card.querySelector('.primary-image');
            const nextImage = card.querySelector('.next-image');
            const newImageSrc = element.getAttribute('data-image');
            const newNextImageSrc = element.getAttribute('data-next-image');
            
            primaryImage.src = newImageSrc;
            nextImage.src = newNextImageSrc;

            card.querySelectorAll('.color-option').forEach(option => {
                option.classList.remove('active');
            });

            element.classList.add('active');
        }

        function changeMainImage(src, element) {
            console.log('Changing main image to:', src);
            const mainImage = document.getElementById('main-image');
            if (mainImage) {
                mainImage.src = src;
                document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
                element.classList.add('active');
            } else {
                console.error('Main image element not found');
            }
        }

        function changeColor(primarySrc, nextSrc, color, element) {
            console.log('Changing color to:', color, 'Primary:', primarySrc, 'Next:', nextSrc);
            const mainImage = document.getElementById('main-image');
            const colorInput = document.getElementById('color-input');
            if (!mainImage || !colorInput) {
                console.error('Main image or color input not found');
                return;
            }
            
            mainImage.src = primarySrc;
            colorInput.value = color;
            
            document.querySelectorAll('.thumbnail').forEach(thumb => thumb.classList.remove('active'));
            document.querySelectorAll('.color-option').forEach(opt => opt.classList.remove('active'));
            element.classList.add('active');
            
            const newThumbnails = imagesByColor[color] || imagesByColor['<?php echo json_encode($first_color); ?>'];
            const thumbnailContainer = document.querySelector('.thumbnails');
            if (!thumbnailContainer) {
                console.error('Thumbnail container not found');
                return;
            }
            thumbnailContainer.innerHTML = '';
            
            if (newThumbnails && newThumbnails.length > 0) {
                newThumbnails.forEach((img, index) => {
                    let thumb = document.createElement('img');
                    thumb.src = '../' + img.image_path;
                    thumb.className = 'thumbnail ' + (index == 0 ? 'active' : '');
                    thumb.onclick = function() {
                        changeMainImage('../' + img.image_path, this);
                    };
                    thumbnailContainer.appendChild(thumb);
                });
            } else {
                console.warn('No thumbnails found for color:', color);
                let thumb = document.createElement('img');
                thumb.src = '../images/placeholder.jpg';
                thumb.className = 'thumbnail active';
                thumb.onclick = function() {
                    changeMainImage('../images/placeholder.jpg', this);
                };
                thumbnailContainer.appendChild(thumb);
            }
        }

        function selectSize(size, element) {
            console.log('Selected size:', size);
            const sizeInput = document.getElementById('size-input');
            if (!sizeInput) {
                console.error('Size input not found');
                return;
            }
            sizeInput.value = size;
            document.querySelectorAll('.size-option').forEach(opt => opt.classList.remove('active'));
            element.classList.add('active');
        }

        function incrementQuantity() {
            const input = document.getElementById('quantity');
            input.value = parseInt(input.value) + 1;
        }

        function decrementQuantity() {
            const input = document.getElementById('quantity');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        function toggleWishlist(productId) {
            fetch(`wishlist_handler.php?toggle_wishlist=${productId}&ajax=1`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const heartIcons = document.querySelectorAll(`.wishlist[onclick="toggleWishlist(${productId})"] i, .wishlist-heart[onclick="toggleWishlist(${productId})"]`);
                    
                    heartIcons.forEach(icon => {
                        if (data.in_wishlist) {
                            icon.classList.remove('far');
                            icon.classList.add('fas');
                        } else {
                            icon.classList.remove('fas');
                            icon.classList.add('far');
                        }
                    });
                    
                    const wishlistCount = document.querySelector('.wishlist-count');
                    if (wishlistCount) {
                        wishlistCount.textContent = data.wishlist_count || 0;
                    }
                }
            })
            .catch(error => {
                console.error('Error toggling wishlist:', error);
            });
        }

        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
            document.getElementById(tabId + '-tab').classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function showSizeGuide() {
            fetch('guide.php')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('size-guide-content').innerHTML = data;
                    const modal = new bootstrap.Modal(document.getElementById('size-guide-modal'));
                    modal.show();
                })
                .catch(error => {
                    console.error('Error loading size guide:', error);
                    alert('Erreur lors du chargement du guide des tailles.');
                });
        }

        function setRating(rating) {
            document.getElementById('rating').value = rating;
            highlightStars(rating);
        }

        function highlightStars(rating) {
            document.querySelectorAll('.star-rating i').forEach(star => {
                const starRating = parseInt(star.dataset.rating);
                if (starRating <= rating) {
                    star.classList.remove('text-muted');
                    star.classList.add('text-warning');
                } else {
                    star.classList.add('text-muted');
                    star.classList.remove('text-warning');
                }
            });
        }

        function showCartSidebar() {
            const sidebar = document.getElementById('cart-sidebar');
            sidebar.classList.add('active');
            document.body.classList.add('cart-open');
        }

        function closeCartSidebar() {
            const sidebar = document.getElementById('cart-sidebar');
            sidebar.classList.remove('active');
            document.body.classList.remove('cart-open');
        }

        function getCart() {
            const cartCookie = document.cookie.split('; ').find(row => row.startsWith('cart='));
            return cartCookie ? JSON.parse(decodeURIComponent(cartCookie.split('=')[1])) : [];
        }

        function setCart(cart) {
            document.cookie = `cart=${encodeURIComponent(JSON.stringify(cart))}; path=/; max-age=${30 * 24 * 60 * 60}`;
        }

        function updateCartDisplay() {
            const cart = getCart();
            const cartItemsContainer = document.getElementById('cart-items');
            const cartTotalElement = document.getElementById('cart-total');
            let total = 0;

            cartItemsContainer.innerHTML = '';

            cart.forEach((item, index) => {
                total += item.price * item.quantity;
                const itemElement = document.createElement('div');
                itemElement.className = 'cart-item';
                itemElement.dataset.index = index;
                
                let itemDetails = `
                    <img src="../${item.image}" alt="${item.name}">
                    <div class="cart-item-details">
                        <p>${item.name}</p>
                        <p>${item.price.toFixed(2)} DT</p>`;
                
                if (item.color) itemDetails += `<p>Couleur: ${item.color}</p>`;
                if (item.size) itemDetails += `<p>Taille: ${item.size}</p>`;
                if (item.format) itemDetails += `<p>Format: ${item.format}</p>`;
                if (item.flavor) itemDetails += `<p>Saveur: ${item.flavor}</p>`;
                
                itemDetails += `
                    </div>
                    <div class="cart-item-controls">
                        <button onclick="updateCartQuantity(${index}, -1)">-</button>
                        <span class="cart-item-quantity">${item.quantity}</span>
                        <button onclick="updateCartQuantity(${index}, 1)">+</button>
                    </div>
                    <button class="cart-item-remove" onclick="removeCartItem(${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                `;
                
                itemElement.innerHTML = itemDetails;
                cartItemsContainer.appendChild(itemElement);
            });

            cartTotalElement.textContent = total.toFixed(2);
            
            const headerCartCount = document.querySelector('.cart-count');
            if (headerCartCount) {
                const totalItems = cart.reduce((sum, item) => sum + item.quantity, 0);
                headerCartCount.textContent = totalItems;
            }
        }

        function updateCartQuantity(index, change) {
            let cart = getCart();
            if (cart[index]) {
                cart[index].quantity += change;
                if (cart[index].quantity <= 0) {
                    cart.splice(index, 1);
                }
                setCart(cart);
                updateCartDisplay();
            }
        }

        function removeCartItem(index) {
            let cart = getCart();
            if (cart[index]) {
                cart.splice(index, 1);
                setCart(cart);
                updateCartDisplay();
            }
        }

        document.getElementById('add-to-cart-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const quantity = parseInt(formData.get('quantity')) || 1;
            const selectedFlavor = formData.get('flavor') || '<?php echo addslashes($first_flavor); ?>';
            const imageSrc = selectedFlavor && imagesByFlavor[selectedFlavor] && imagesByFlavor[selectedFlavor][0] ? 
                imagesByFlavor[selectedFlavor][0].image_path : 
                (imagesByColor['<?php echo addslashes($first_color); ?>'] && imagesByColor['<?php echo addslashes($first_color); ?>'][0] ? 
                imagesByColor['<?php echo addslashes($first_color); ?>'][0].image_path : 
                'images/placeholder.jpg');
            const product = {
                id: <?php echo $product_id; ?>,
                name: "<?php echo addslashes($product['lib_produit']); ?>",
                price: <?php echo $product['prix_produit']; ?>,
                quantity: quantity,
                image: imageSrc,
                color: formData.get('color') || '',
                size: formData.get('size') || '',
                format: formData.get('format') || '',
                flavor: selectedFlavor
            };

            let cart = getCart();
            let found = false;
            for (let i = 0; i < cart.length; i++) {
                const item = cart[i];
                if (item.id === product.id && item.color === product.color && item.size === product.size &&
                    item.format === product.format && item.flavor === product.flavor) {
                    item.quantity += quantity;
                    found = true;
                    break;
                }
            }
            
            if (!found) {
                cart.push(product);
            }
            
            setCart(cart);
            updateCartDisplay();
            showCartSidebar();
        });
    </script>
</body>
</html>