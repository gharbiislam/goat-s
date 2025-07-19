<?php
include('db.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get view mode from URL, default to 3
$view_mode = isset($_GET['view']) ? intval($_GET['view']) : 3;
$filterSectionVisible = ($view_mode != 4);

function getCategoryProductCount($conn, $categoryId)
{
    $sql = "WITH RECURSIVE category_tree AS (
                SELECT id_categorie
                FROM categorie
                WHERE id_categorie = $categoryId
                UNION ALL
                SELECT c.id_categorie
                FROM categorie c
                INNER JOIN category_tree ct ON c.id_categorie_parent = ct.id_categorie
            )
            SELECT COUNT(DISTINCT p.id_produit) as count 
            FROM produits p
            WHERE p.id_categorie IN (SELECT id_categorie FROM category_tree) AND p.en_promo = 1";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['count'];
    }
    return 0;
}

// Function to get count of products for a brand
function getBrandProductCount($conn, $brandId)
{
    $sql = "SELECT COUNT(*) as count FROM produits WHERE id_marque = $brandId AND en_promo = 1";
    $result = $conn->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        return $row['count'];
    }
    return 0;
}

// Get all main categories with promoted products
$categoriesQuery = "SELECT id_categorie, libelle_categorie FROM categorie WHERE id_categorie_parent IS NULL";
$categoriesResult = $conn->query($categoriesQuery);
// Get all subcategories with promoted products
$subcategoriesQuery = "SELECT c.id_categorie, c.libelle_categorie, c.id_categorie_parent, 
                      COUNT(p.id_produit) as product_count 
                      FROM categorie c
                      LEFT JOIN produits p ON c.id_categorie = p.id_categorie AND p.en_promo = 1
                      WHERE c.id_categorie_parent IS NOT NULL
                      AND EXISTS (
                          SELECT 1 
                          FROM produits p2 
                          WHERE p2.id_categorie = c.id_categorie 
                          AND p2.en_promo = 1
                      )
                      GROUP BY c.id_categorie
                      ORDER BY product_count DESC";
$subcategoriesResult = $conn->query($subcategoriesQuery);

// Get all brands with promoted products
$brandsQuery = "SELECT m.id_marque, m.nom_marque, COUNT(p.id_produit) as product_count 
               FROM marque m
               LEFT JOIN produits p ON m.id_marque = p.id_marque AND p.en_promo = 1
               WHERE EXISTS (
                   SELECT 1 
                   FROM produits p2 
                   WHERE p2.id_marque = m.id_marque 
                   AND p2.en_promo = 1
               )
               GROUP BY m.id_marque
               ORDER BY product_count DESC";
$brandsResult = $conn->query($brandsQuery);

// Get all colors from vetement table for promoted products
$colorsQuery = "SELECT DISTINCT v.couleur 
                FROM vetement v
                JOIN produits p ON v.id_produit = p.id_produit
                WHERE p.en_promo = 1
                ORDER BY v.couleur";
$colorsResult = $conn->query($colorsQuery);

// Check for query errors
if ($colorsResult === false) {
    error_log("Colors query failed: " . $conn->error);
    $colorsResult = null;
}

// Get all flavors from alimentaire table for promoted products
$flavorsQuery = "SELECT DISTINCT a.saveur 
                 FROM alimentaire a
                 JOIN produits p ON a.id_produit = p.id_produit
                 WHERE p.en_promo = 1
                 ORDER BY a.saveur";
$flavorsResult = $conn->query($flavorsQuery);

// Get min and max prices for products on promotion
$priceRangeQuery = "SELECT MIN(prix_produit) as min_price, MAX(prix_produit) as max_price 
                    FROM produits 
                    WHERE en_promo = 1";
$priceRangeResult = $conn->query($priceRangeQuery);
$priceRange = $priceRangeResult->fetch_assoc();
$minPrice = floor($priceRange['min_price']);
$maxPrice = ceil($priceRange['max_price']);

// Pagination setup
$productsPerPage = 9;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

// Apply filters if they exist
$whereConditions = ["p.en_promo = 1"];
$filterParams = [];

// Track applied filters for display
$appliedFilters = [];
$filterCount = 0;

// Category filter (include subcategories and sub-subcategories)
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $categoryIds = implode(',', array_map('intval', $_GET['category']));
    $whereConditions[] = "p.id_categorie IN (
        WITH RECURSIVE category_tree AS (
            SELECT id_categorie
            FROM categorie
            WHERE id_categorie IN ($categoryIds)
            UNION ALL
            SELECT c.id_categorie
            FROM categorie c
            INNER JOIN category_tree ct ON c.id_categorie_parent = ct.id_categorie
        )
        SELECT id_categorie FROM category_tree
    )";
    // Fetch category names for display
    $categoryNamesQuery = "SELECT libelle_categorie FROM categorie WHERE id_categorie IN ($categoryIds)";
    $categoryNamesResult = $conn->query($categoryNamesQuery);
    while ($cat = $categoryNamesResult->fetch_assoc()) {
        $appliedFilters[] = $cat['libelle_categorie'];
    }
    $filterCount += count($_GET['category']);
}

// Subcategory filter
if (isset($_GET['subcategory']) && !empty($_GET['subcategory'])) {
    $subcategoryIds = implode(',', array_map('intval', $_GET['subcategory']));
    $whereConditions[] = "p.id_categorie IN ($subcategoryIds)";
    // Fetch subcategory names for display
    $subcategoryNamesQuery = "SELECT libelle_categorie FROM categorie WHERE id_categorie IN ($subcategoryIds)";
    $subcategoryNamesResult = $conn->query($subcategoryNamesQuery);
    while ($subcat = $subcategoryNamesResult->fetch_assoc()) {
        $appliedFilters[] = $subcat['libelle_categorie'];
    }
    $filterCount += count($_GET['subcategory']);
}

// Brand filter
if (isset($_GET['brand']) && !empty($_GET['brand'])) {
    $brandIds = implode(',', array_map('intval', $_GET['brand']));
    $whereConditions[] = "p.id_marque IN ($brandIds)";
    // Fetch brand names for display
    $brandNamesQuery = "SELECT nom_marque FROM marque WHERE id_marque IN ($brandIds)";
    $brandNamesResult = $conn->query($brandNamesQuery);
    while ($brand = $brandNamesResult->fetch_assoc()) {
        $appliedFilters[] = $brand['nom_marque'];
    }
    $filterCount += count($_GET['brand']);
}

// Price range filter
if (isset($_GET['min_price']) && isset($_GET['max_price'])) {
    $minPriceFilter = floatval($_GET['min_price']);
    $maxPriceFilter = floatval($_GET['max_price']);
    if ($minPriceFilter != $minPrice || $maxPriceFilter != $maxPrice) {
        $whereConditions[] = "p.prix_produit BETWEEN $minPriceFilter AND $maxPriceFilter";
        $appliedFilters[] = "Prix: $minPriceFilter - $maxPriceFilter TND";
        $filterCount++;
    }
}

// Color filter (joins with vetement table)
if (isset($_GET['color']) && !empty($_GET['color'])) {
    $colors = $_GET['color'];
    $colorConditions = [];
    foreach ($colors as $color) {
        $colorConditions[] = "v.couleur = '" . $conn->real_escape_string($color) . "'";
        $appliedFilters[] = ucfirst($color);
    }
    if (!empty($colorConditions)) {
        $whereConditions[] = "(EXISTS (SELECT 1 FROM vetement v WHERE v.id_produit = p.id_produit AND (" . implode(' OR ', $colorConditions) . ")))";
    }
    $filterCount += count($colors);
}

// Flavor filter (joins with alimentaire table)
if (isset($_GET['flavor']) && !empty($_GET['flavor'])) {
    $flavors = $_GET['flavor'];
    $flavorConditions = [];
    foreach ($flavors as $flavor) {
        $flavorConditions[] = "a.saveur = '" . $conn->real_escape_string($flavor) . "'";
        $appliedFilters[] = $flavor;
    }
    if (!empty($flavorConditions)) {
        $whereConditions[] = "(EXISTS (SELECT 1 FROM alimentaire a WHERE a.id_produit = p.id_produit AND (" . implode(' OR ', $flavorConditions) . ")))";
    }
    $filterCount += count($flavors);
}

// Build the WHERE clause
$whereClause = !empty($whereConditions) ? "WHERE " . implode(' AND ', $whereConditions) : "";

// Count total products with filters
$countQuery = "SELECT COUNT(DISTINCT p.id_produit) as total 
              FROM produits p
              LEFT JOIN marque m ON p.id_marque = m.id_marque
              LEFT JOIN vetement v ON p.id_produit = v.id_produit
              LEFT JOIN alimentaire a ON p.id_produit = a.id_produit
              $whereClause";
$countResult = $conn->query($countQuery);
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $productsPerPage);

// Get products with filters, pagination, and sorting
$productsQuery = "SELECT p.id_produit, p.lib_produit, p.prix_produit, p.taux_reduction, 
                 m.nom_marque,
                 (SELECT image_path FROM images WHERE id_produit = p.id_produit ORDER BY ordre ASC LIMIT 1) as primary_image
                 FROM produits p
                 LEFT JOIN marque m ON p.id_marque = m.id_marque
                 $whereClause
                 GROUP BY p.id_produit
                 ORDER BY p.en_promo DESC, p.id_produit DESC
                 LIMIT $offset, $productsPerPage";
$productsResult = $conn->query($productsQuery);

// Fetch all images for each product, respecting color filter if applied
$productImages = [];
$colorFilter = isset($_GET['color']) ? $_GET['color'] : [];
if ($productsResult->num_rows > 0) {
    while ($product = $productsResult->fetch_assoc()) {
        $imageQuery = "SELECT i.image_path, i.couleur 
                       FROM images i 
                       WHERE i.id_produit = " . $product['id_produit'];
        if (!empty($colorFilter)) {
            $colorConditions = array_map(function ($color) use ($conn) {
                return "'" . $conn->real_escape_string($color) . "'";
            }, $colorFilter);
            $imageQuery .= " AND (i.couleur IN (" . implode(',', $colorConditions) . ") OR i.couleur IS NULL)";
        }
        $imageQuery .= " ORDER BY i.ordre ASC";
        $imageResult = $conn->query($imageQuery);
        $images = [];
        while ($image = $imageResult->fetch_assoc()) {
            $images[] = [
                'path' => $image['image_path'],
                'color' => $image['couleur']
            ];
        }
        $productImages[$product['id_produit']] = $images;
    }
    $productsResult->data_seek(0);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Produits en Promotion</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Montserrat', sans-serif;
        }
::-webkit-scrollbar {
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
        .sidebar {
            width: 300px;
        }

        .promo-title {
            color: #8bc34a;
            font-weight: 600;
        }

        #filterForm {
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 7px;
        }

        .filter-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }

        .filter-title {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 15px;
            cursor: pointer;
        }

        .filter-title i {
            font-size: 0.8rem;
        }

        .form-check {
            margin-bottom: 8px;
        }

        .form-check-label {
            font-size: 0.9rem;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }

        .form-check-label span {
            color: var(--medium-gray);
            font-size: 0.8rem;
        }

        .show-more {
            color: #1E4494;
            display: block;
            margin-top: 5px;
            cursor: pointer;
        }

        .price-slider {
            padding: 0 10px;
        }

        .price-range {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 0.9rem;
            color: var(--medium-gray);
        }

        .color-options {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }

        .color-option {
            width: 25px;
            height: 25px;
            cursor: pointer;
            border: 1px solid #ddd;
            position: relative;
        }

        .color-option.selected {
            border: solid black 1px;
        }

        .product-card {
            border: 1px solid #eee;
            border-radius: 5px;
            overflow: hidden;
            position: relative;
            transition: transform 0.3s, box-shadow 0.3s;
            height: 515px;
            width: 300px;
            display: flex;
            flex-direction: column;
            margin-bottom: 20px;
            margin-left: 40px;
        }

        .discount-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #8bc34a;
            color: #1E4494;
            padding: 5px 15px;
            font-size: 0.8rem;
            font-weight: bold;
            z-index: 1;
        }

        .product-img {
            height: 415px;
            width: 300px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .product-img img.product-image {
            height: 415px;
            width: 300px;
            object-fit: cover;
            position: absolute;
            top: 0;
            left: 0;
            transition: opacity 0.3s ease-in-out;
        }

        .product-img img.primary-image {
            opacity: 1;
        }

        .product-img img.next-image {
            opacity: 0;
        }

        .product-img:hover img.primary-image {
            opacity: 0;
        }

        .product-img:hover img.next-image {
            opacity: 1;
        }

        .product-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.2rem;
            font-weight: 600;
        }

        .product-brand {
            font-weight: 500;
            color: #1E4494;
            margin-bottom: 10px;
        }

        .original-price {
            text-decoration: line-through;
            font-weight: 500;
            font-size: 1.1rem;
            margin-right: 10px;
        }

        .discounted-price,
        .current-price {
            color: #8bc34a;
            font-weight: bold;
            font-size: 1.2rem;
        }

        .stock-info {
            font-size: 0.8rem;
            color: var(--medium-gray);
        }

        .pagination {
            margin-top: 30px;
            justify-content: center;
        }

        .pagination .page-link {
            color: #1E4494;
            border-color: #ddd;
        }

        .pagination .page-item.active .page-link {
            background-color: #1E4494;
            border-color: #1E4494;
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .form-check.hidden {
            display: none;
        }

        .no-products {
            text-align: center;
            padding: 50px 0;
            font-size: 1.2rem;
            color: var(--medium-gray);
        }

        /* Styles from categorie.php for product header */
        .products-header {
          
            margin-bottom: 20px;
            padding: 10px 15px;
           
        }

        .view-options {
            display: flex;
            gap: 10px;
        }

        .view-option {
            cursor: pointer;
            border-radius: 3px;
            transition: all 0.2s ease;
            font-size: 18px;
            position: relative;
        }

        .view-option img {
            vertical-align: middle;
        }

        .products-grid {
            display: grid;
            gap: 20px;
            transition: all 0.3s ease;
        }

        .grid-4 {
            grid-template-columns: repeat(4, 1fr);
        }

        .grid-3 {
            grid-template-columns: repeat(3, 1fr);
        }

        .grid-2 {
            grid-template-columns: repeat(2, 1fr);
        }

        @media (max-width: 992px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
                .f_img{
                 width: 100%;
  height: auto;
  max-width: 25px;
            }
           
        }

        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            .f_img{
                 width: 100%;
  height: auto;
  max-width: 25px;
            }
           
          
        }

        /* Styles for applied filters */
       

        .filter-tag {
            display: inline-flex;
            align-items: center;
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            border-radius: 20px;
            padding: 5px 10px;
            margin-right: 10px;
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .filter-tag .close-btn {
            margin-left: 5px;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .reset-filters {
            color: #1E4494;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .reset-filters:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <?php include 'header.php'; ?>

    <div class="mt-5 mx-5">
        
        <div class="d-flex justify-content-between align-items-center col-5">
            <div class="view-options d-none d-lg-flex">
                <div class="view-option <?php echo $view_mode == 4 ? 'checked active' : ''; ?>" data-view="4" title="Hide Filters (4 per row)">
                    <img src="../assets/img/icons/<?php echo $view_mode == 4 ? 'grid_4.svg' : 'grid_4.svg'; ?>" alt="Grid 4" style="width: 24px; height: 24px;">
                </div>
                <div class="view-option <?php echo $view_mode == 2 ? 'checked active' : ''; ?>" data-view="2" title="2 per row">
                    <img src="../assets/img/icons/<?php echo $view_mode == 2 ? 'grid2_active.svg' : 'grid2.svg'; ?>" alt="Grid 2" style="width: 24px; height: 24px;">
                </div>
                <div class="view-option <?php echo $view_mode == 3 ? 'checked active' : ''; ?>" data-view="3" title="Default (3 per row)">
                    <img src="../assets/img/icons/<?php echo $view_mode == 3 ? 'grid3_active.svg' : 'grid3.svg'; ?>" alt="Grid 3" style="width: 24px; height: 24px;">
                </div>
               
            </div> <h3 class="promo-title mx-lg-5 px-lg-4">PROMO</h3>
        </div>
        <div class="row">
            <div class="col-12 d-lg-none mb-3">
                <button class="filter-toggle btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarFilters" aria-expanded="false" aria-controls="sidebarFilters">
                    Filtres <img src="../assets/img/icons/grid_4.svg" alt="" class="f_img">
                </button>
            </div>
            <div class="col-lg-3 sidebar" id="filterSection" style="display: <?php echo $filterSectionVisible ? 'block' : 'none'; ?>;">
                <div id="sidebarFilters" class="sidebar-filters collapse d-lg-block">
                    <form id="filterForm" action="promo.php" method="GET">
                        <input type="hidden" name="view" value="<?php echo $view_mode; ?>">
                        <div class="filter-section">
                            <div class="filter-title d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#categoriesCollapse" aria-expanded="true" aria-controls="categoriesCollapse">
                                <span>Categories</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="collapse show" id="categoriesCollapse">
                                <?php
                                if ($categoriesResult && $categoriesResult->num_rows > 0) {
                                    $counter = 0;
                                    while ($category = $categoriesResult->fetch_assoc()) {
                                        $count = getCategoryProductCount($conn, $category['id_categorie']);
                                        $checked = isset($_GET['category']) && in_array($category['id_categorie'], $_GET['category']) ? 'checked' : '';
                                        $hiddenClass = $counter >= 5 ? 'hidden' : '';
                                        echo '<div class="form-check ' . $hiddenClass . '">';
                                        echo '<input class="form-check-input filter-checkbox category-checkbox" type="checkbox" name="category[]" id="cat' . $category['id_categorie'] . '" value="' . $category['id_categorie'] . '" ' . $checked . ' data-category-id="' . $category['id_categorie'] . '">';
                                        echo '<label class="form-check-label d-flex" for="cat' . $category['id_categorie'] . '">';
                                        echo $category['libelle_categorie'] . ' <span>(' . $count . ')</span>';
                                        echo '</label>';
                                        echo '</div>';
                                        $counter++;
                                    }
                                    if ($counter > 5) {
                                        echo '<a class="show-more">Voir plus</a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="filter-section">
                            <div class="filter-title d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#sousCategories" aria-expanded="true" aria-controls="sousCategories">
                                <span>Sous Catégories</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="collapse show" id="sousCategories">
                                <?php
                                if ($subcategoriesResult && $subcategoriesResult->num_rows > 0) {
                                    $counter = 0;
                                    while ($subcategory = $subcategoriesResult->fetch_assoc()) {
                                        $checked = isset($_GET['subcategory']) && in_array($subcategory['id_categorie'], $_GET['subcategory']) ? 'checked' : '';
                                        $hiddenClass = $counter >= 5 ? 'hidden' : '';
                                        echo '<div class="form-check ' . $hiddenClass . '" data-parent-category="' . $subcategory['id_categorie_parent'] . '">';
                                        echo '<input class="form-check-input filter-checkbox" type="checkbox" name="subcategory[]" id="subcat' . $subcategory['id_categorie'] . '" value="' . $subcategory['id_categorie'] . '" ' . $checked . '>';
                                        echo '<label class="form-check-label" for="subcat' . $subcategory['id_categorie'] . '">';
                                        echo $subcategory['libelle_categorie'] . ' <span>(' . $subcategory['product_count'] . ')</span>';
                                        echo '</label>';
                                        echo '</div>';
                                        $counter++;
                                    }
                                    if ($counter > 5) {
                                        echo '<a class="show-more">Voir plus</a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="filter-section">
                            <div class="filter-title d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#prixCollapse" aria-expanded="true" aria-controls="prixCollapse">
                                <span>Prix</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="collapse show" id="prixCollapse">
                                <div class="price-slider">
                                    <input type="range" class="form-range" min="<?php echo $minPrice; ?>" max="<?php echo $maxPrice; ?>" step="10" id="priceRange" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : $maxPrice; ?>">
                                    <input type="hidden" name="min_price" id="minPrice" value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : $minPrice; ?>">
                                    <input type="hidden" name="max_price" id="maxPrice" value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : $maxPrice; ?>">
                                    <div class="price-range">
                                        <span id="priceMin"><?php echo $minPrice; ?> TND</span>
                                        <span id="priceMax"><?php echo $maxPrice; ?> TND</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="filter-section">
                            <div class="filter-title d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#marquesCollapse" aria-expanded="true" aria-controls="marquesCollapse">
                                <span>Marques</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="collapse show" id="marquesCollapse">
                                <?php
                                if ($brandsResult && $brandsResult->num_rows > 0) {
                                    $counter = 0;
                                    while ($brand = $brandsResult->fetch_assoc()) {
                                        if ($brand['product_count'] > 0) {
                                            $checked = isset($_GET['brand']) && in_array($brand['id_marque'], $_GET['brand']) ? 'checked' : '';
                                            $hiddenClass = $counter >= 5 ? 'hidden' : '';
                                            echo '<div class="form-check ' . $hiddenClass . '">';
                                            echo '<input class="form-check-input filter-checkbox" type="checkbox" name="brand[]" id="brand' . $brand['id_marque'] . '" value="' . $brand['id_marque'] . '" ' . $checked . '>';
                                            echo '<label class="form-check-label" for="brand' . $brand['id_marque'] . '">';
                                            echo $brand['nom_marque'] . ' <span>(' . $brand['product_count'] . ')</span>';
                                            echo '</label>';
                                            echo '</div>';
                                            $counter++;
                                        }
                                    }
                                    if ($counter > 5) {
                                        echo '<a class="show-more">Voir plus</a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        <div class="filter-section">
                            <div class="filter-title d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#couleurCollapse" aria-expanded="true" aria-controls="couleurCollapse">
                                <span>Couleur</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="collapse show" id="couleurCollapse">
                                <div class="color-options">
                                    <?php
                                    if ($colorsResult && $colorsResult->num_rows > 0) {
                                        while ($color = $colorsResult->fetch_assoc()) {
                                            if (!empty($color['couleur'])) {
                                                $colorValue = strtolower($color['couleur']);
                                                $selected = isset($_GET['color']) && in_array($colorValue, $_GET['color']) ? 'selected' : '';
                                                echo '<div class="color-option ' . $selected . '" style="background-color: ' . $colorValue . ';" data-color="' . $colorValue . '"></div>';
                                                echo '<input type="checkbox" class="filter-checkbox" name="color[]" value="' . $colorValue . '" ' . ($selected ? 'checked' : '') . ' style="display:none;" id="color_' . $colorValue . '">';
                                            }
                                        }
                                    } else {
                                        echo '<p>Aucune couleur disponible.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                        <div class="filter-section">
                            <div class="filter-title d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#saveurCollapse" aria-expanded="true" aria-controls="saveurCollapse">
                                <span>Saveur</span>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="collapse show" id="saveurCollapse">
                                <?php
                                if ($flavorsResult && $flavorsResult->num_rows > 0) {
                                    $counter = 0;
                                    while ($flavor = $flavorsResult->fetch_assoc()) {
                                        if (!empty($flavor['saveur'])) {
                                            $checked = isset($_GET['flavor']) && in_array($flavor['saveur'], $_GET['flavor']) ? 'checked' : '';
                                            $hiddenClass = $counter >= 5 ? 'hidden' : '';
                                            echo '<div class="form-check ' . $hiddenClass . '">';
                                            echo '<input class="form-check-input filter-checkbox" type="checkbox" name="flavor[]" id="flavor_' . $counter . '" value="' . $flavor['saveur'] . '" ' . $checked . '>';
                                            echo '<label class="form-check-label" for="flavor_' . $counter . '">';
                                            echo $flavor['saveur'];
                                            echo '</label>';
                                            echo '</div>';
                                            $counter++;
                                        }
                                    }
                                    if ($counter > 5) {
                                        echo '<a class="show-more">Voir plus</a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-9">
                <!-- Display applied filters -->
                <div class="applied-filters">
                    <?php
                    // Show other applied filters
                    foreach ($appliedFilters as $filter) {
                        echo '<span class="filter-tag">' . htmlspecialchars($filter) . ' <span class="close-btn">×</span></span>';
                    }
                    // Show reset link if there are additional filters
                    if ($filterCount > 0) {
                        echo '<a href="promo.php?view=' . $view_mode . '" class="reset-filters">Réinitialiser les filtres (' . $filterCount . ')</a>';
                    }
                    ?>
                </div>
                <div class="products-grid <?php echo 'grid-' . ($view_mode == 4 && !$filterSectionVisible ? '4' : $view_mode); ?>">
                    <?php
                    if ($productsResult && $productsResult->num_rows > 0) {
                        while ($product = $productsResult->fetch_assoc()) {
                            $discountedPrice = $product["prix_produit"] - ($product["prix_produit"] * $product["taux_reduction"] / 100);
                            $productId = $product['id_produit'];
                            $images = $productImages[$productId] ?? [];
                            $primaryImage = $images[0]['path'] ?? 'assets/img/placeholder.jpg';
                            $nextImage = isset($images[1]) ? $images[1]['path'] : $primaryImage;
                    ?>
                            <div>
                                <a href="produit.php?id=<?php echo $product['id_produit']; ?>" class="text-decoration-none">
                                    <div class="product-card text-black" data-images='<?php echo json_encode($productImages[$product["id_produit"]]); ?>'>
                                        <?php if ($product["taux_reduction"] > 0): ?>
                                            <span class="discount-badge">-<?php echo $product["taux_reduction"]; ?>%</span>
                                        <?php endif; ?>
                                        <div class="product-img">
                                            <img src="../<?php echo htmlspecialchars($primaryImage); ?>"
                                                alt="<?php echo htmlspecialchars($product["lib_produit"]); ?>"
                                                class="product-image primary-image">
                                            <img src="../<?php echo htmlspecialchars($nextImage); ?>"
                                                alt="<?php echo htmlspecialchars($product["lib_produit"]); ?>"
                                                class="product-image next-image">
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-title"><?php echo $product["lib_produit"]; ?></h3>
                                            <div class="product-price">
                                                <?php if ($product["taux_reduction"] > 0): ?>
                                                    <span class="original-price"><?php echo number_format($product["prix_produit"], 2); ?> TND</span>
                                                    <span class="discounted-price"><?php echo number_format($discountedPrice, 2); ?> TND</span>
                                                <?php else: ?>
                                                    <span class="current-price"><?php echo number_format($product["prix_produit"], 2); ?> TND</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="product-brand"><?php echo $product["nom_marque"]; ?></p>
                                        </div>
                                    </div>
                                </a>
                            </div>
                    <?php
                        }
                    } else {
                        echo '<div class="no-products">Aucun produit en promotion actuellement.</div>';
                    }
                    ?>
                </div>
                <?php if ($totalPages > 1): ?>
                    <nav aria-label="Page navigation">
                        <ul class="pagination">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&view=<?php echo $view_mode; ?><?php echo isset($_GET['category']) ? '&category[]=' . implode('&category[]=', $_GET['category']) : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory[]=' . implode('&subcategory[]=', $_GET['subcategory']) : ''; ?><?php echo isset($_GET['brand']) ? '&brand[]=' . implode('&brand[]=', $_GET['brand']) : ''; ?><?php echo isset($_GET['color']) ? '&color[]=' . implode('&color[]=', $_GET['color']) : ''; ?><?php echo isset($_GET['flavor']) ? '&flavor[]=' . implode('&flavor[]=', $_GET['flavor']) : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price=' . $_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price=' . $_GET['max_price'] : ''; ?>" aria-label="Previous">
                                        <span aria-hidden="true">«</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&view=<?php echo $view_mode; ?><?php echo isset($_GET['category']) ? '&category[]=' . implode('&category[]=', $_GET['category']) : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory[]=' . implode('&subcategory[]=', $_GET['subcategory']) : ''; ?><?php echo isset($_GET['brand']) ? '&brand[]=' . implode('&brand[]=', $_GET['brand']) : ''; ?><?php echo isset($_GET['color']) ? '&color[]=' . implode('&color[]=', $_GET['color']) : ''; ?><?php echo isset($_GET['flavor']) ? '&flavor[]=' . implode('&flavor[]=', $_GET['flavor']) : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price=' . $_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price=' . $_GET['max_price'] : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&view=<?php echo $view_mode; ?><?php echo isset($_GET['category']) ? '&category[]=' . implode('&category[]=', $_GET['category']) : ''; ?><?php echo isset($_GET['subcategory']) ? '&subcategory[]=' . implode('&subcategory[]=', $_GET['subcategory']) : ''; ?><?php echo isset($_GET['brand']) ? '&brand[]=' . implode('&brand[]=', $_GET['brand']) : ''; ?><?php echo isset($_GET['color']) ? '&color[]=' . implode('&color[]=', $_GET['color']) : ''; ?><?php echo isset($_GET['flavor']) ? '&flavor[]=' . implode('&flavor[]=', $_GET['flavor']) : ''; ?><?php echo isset($_GET['min_price']) ? '&min_price=' . $_GET['min_price'] : ''; ?><?php echo isset($_GET['max_price']) ? '&max_price=' . $_GET['max_price'] : ''; ?>" aria-label="Next">
                                        <span aria-hidden="true">»</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let filterSectionVisible = <?php echo json_encode($filterSectionVisible); ?>;

            // Initialize images for product cards
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                const productId = card.getAttribute('data-product-id');
                const primaryImage = card.querySelector('.primary-image');
                const nextImage = card.querySelector('.next-image');
                const images = JSON.parse(card.getAttribute('data-images') || '[]');
                const selectedColors = <?php echo json_encode($colorFilter); ?>.map(c => c.toLowerCase());

                let filteredImages = images;
                if (selectedColors.length > 0) {
                    filteredImages = images.filter(image =>
                        !image.color || selectedColors.includes(image.color.toLowerCase())
                    );
                }
                if (filteredImages.length === 0) {
                    filteredImages = images; // Fallback to all images
                }

                // Initialize primary and next images
                if (filteredImages.length > 0) {
                    primaryImage.src = '../' + filteredImages[0].path;
                    nextImage.src = '../' + (filteredImages[1] ? filteredImages[1].path : filteredImages[0].path);
                }
            });

            // Price range slider
            const priceRange = document.getElementById('priceRange');
            const priceMin = document.getElementById('priceMin');
            const priceMax = document.getElementById('priceMax');
            const minPriceInput = document.getElementById('minPrice');
            const maxPriceInput = document.getElementById('maxPrice');
            if (priceRange && priceMin && priceMax) {
                priceRange.addEventListener('input', function() {
                    priceMax.textContent = this.value + ' TND';
                    maxPriceInput.value = this.value;
                    document.getElementById('filterForm').submit();
                });
            }

            // Color options selection
            const colorOptions = document.querySelectorAll('.color-option');
            colorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    const colorValue = this.getAttribute('data-color');
                    const checkbox = document.getElementById('color_' + colorValue);
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        this.classList.toggle('selected');
                        document.getElementById('filterForm').submit();
                    }
                });
            });

            // Auto-submit form when any filter checkbox is changed
            const filterCheckboxes = document.querySelectorAll('.filter-checkbox');
            filterCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            // Hide subcategories of other categories when a category is clicked
            const categoryCheckboxes = document.querySelectorAll('.category-checkbox');
            categoryCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const selectedCategoryId = this.getAttribute('data-category-id');
                    const subcategoryItems = document.querySelectorAll('#sousCategories .form-check');

                    subcategoryItems.forEach(item => {
                        const parentCategoryId = item.getAttribute('data-parent-category');
                        if (this.checked && parentCategoryId !== selectedCategoryId) {
                            item.style.display = 'none';
                        } else {
                            item.style.display = 'block';
                        }
                    });

                    const categorySections = document.querySelectorAll('#categoriesCollapse .form-check');
                    categorySections.forEach(section => {
                        const checkbox = section.querySelector('input');
                        if (checkbox !== this && this.checked) {
                            checkbox.checked = false;
                        }
                    });

                    document.getElementById('filterForm').submit();
                });
            });

            // Voir plus links
            const showMoreLinks = document.querySelectorAll('.show-more');
            showMoreLinks.forEach(link => {
                link.addEventListener('click', function() {
                    const parentSection = this.parentElement;
                    const hiddenItems = parentSection.querySelectorAll('.form-check.hidden');
                    if (this.textContent === 'Voir plus') {
                        hiddenItems.forEach(item => {
                            item.classList.remove('hidden');
                        });
                        this.textContent = 'Show less';
                    } else {
                        hiddenItems.forEach(item => {
                            item.classList.add('hidden');
                        });
                        this.textContent = 'Voir plus';
                    }
                });
            });

            // View options handling
            document.querySelectorAll('.view-option').forEach(function(option) {
                option.addEventListener('click', function() {
                    const view = this.getAttribute('data-view');
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('view', view);

                    document.querySelectorAll('.view-option').forEach(opt => opt.classList.remove('checked', 'active'));
                    this.classList.add('checked', 'active');

                    if (view == 4) {
                        document.getElementById('filterSection').style.display = 'none';
                        filterSectionVisible = false;
                    } else {
                        document.getElementById('filterSection').style.display = 'block';
                        filterSectionVisible = true;
                    }
                    window.location.href = currentUrl.toString();
                });
            });

            // Initialize view option
            if (!filterSectionVisible) {
                document.getElementById('filterSection').style.display = 'none';
                document.querySelector('.view-option[data-view="4"]').classList.add('checked', 'active');
            } else {
                document.querySelector('.view-option[data-view="<?php echo $view_mode; ?>"]').classList.add('checked', 'active');
            }

            // Handle close buttons for filter tags
            const closeButtons = document.querySelectorAll('.filter-tag .close-btn');
            closeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const filterTag = this.parentElement;
                    const filterText = filterTag.childNodes[0].textContent.trim();
                    const form = document.getElementById('filterForm');

                    // Find and uncheck the corresponding filter
                    const allCheckboxes = form.querySelectorAll('input[type="checkbox"]');
                    allCheckboxes.forEach(checkbox => {
                        const label = checkbox.nextElementSibling;
                        if (label) {
                            const labelText = label.textContent.split('(')[0].trim();
                            if (labelText === filterText || checkbox.value === filterText.toLowerCase()) {
                                checkbox.checked = false;
                            }
                        }
                    });

                    // Reset price if the filter is a price range
                    if (filterText.includes('Prix:')) {
                        document.getElementById('minPrice').value = <?php echo $minPrice; ?>;
                        document.getElementById('maxPrice').value = <?php echo $maxPrice; ?>;
                        document.getElementById('priceRange').value = <?php echo $maxPrice; ?>;
                        document.getElementById('priceMax').textContent = <?php echo $maxPrice; ?> + ' TND';
                    }

                    form.submit();
                });
            });
        });
    </script>
</body>

</html>