<?php
// Database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
include 'db.php';

// Get category ID from URL
$category_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get category information
$category_query = "SELECT c1.id_categorie, c1.libelle_categorie, c1.id_categorie_parent, 
                  c2.libelle_categorie as parent_name
                  FROM categorie c1
                  LEFT JOIN categorie c2 ON c1.id_categorie_parent = c2.id_categorie
                  WHERE c1.id_categorie = $category_id";
$category_result = mysqli_query($conn, $category_query);

$category = null;
$category_name = "Tous les produits";
$main_category_id = 0;
$show_banner = false;

if ($category_id > 0) {
    if (!$category_result) {
        die("Erreur de requête: " . mysqli_error($conn));
    }

    $category = mysqli_fetch_assoc($category_result);

    if (!$category) {
        header('Location: index.php');
        exit();
    }

    $category_name = htmlspecialchars($category['libelle_categorie']);
    $main_category_id = $category_id;
    
    if ($category['id_categorie_parent'] != NULL) {
        $parent_id = $category['id_categorie_parent'];
        while ($parent_id != NULL) {
            $parent_query = "SELECT id_categorie, libelle_categorie, id_categorie_parent 
                           FROM categorie WHERE id_categorie = $parent_id";
            $parent_result = mysqli_query($conn, $parent_query);
            $parent = mysqli_fetch_assoc($parent_result);
            if (!$parent) break;
            $main_category_id = $parent['id_categorie'];
            $parent_id = $parent['id_categorie_parent'];
        }
    }

    // Show banner only for main categories
    $show_banner = in_array($main_category_id, [1, 2, 3]);
    $banner = "default-banner.jpg";
    if ($main_category_id == 1) {
        $banner = "../assets/img/cover/alim.png";
    } elseif ($main_category_id == 2) {
        $banner = "../assets/img/cover/vete.png";
    } elseif ($main_category_id == 3) {
        $banner = "../assets/img/cover/acc.png";
    }
}

// Modified subcategory query to include subcategories and their sub-subcategories
$subcategories_query = "SELECT id_categorie, libelle_categorie, id_categorie_parent 
                       FROM categorie 
                       WHERE id_categorie_parent = $category_id 
                       OR id_categorie_parent IN (
                           SELECT id_categorie 
                           FROM categorie 
                           WHERE id_categorie_parent = $category_id
                       )";
$subcategories_result = mysqli_query($conn, $subcategories_query);
$subcategories = [];
while ($row = mysqli_fetch_assoc($subcategories_result)) {
    $subcategories[] = $row;
}

function buildCategoryTree($conn, $category_id) {
    $categories = [$category_id];
    $queue = [$category_id];
    
    while (!empty($queue)) {
        $current = array_shift($queue);
        $query = "SELECT id_categorie FROM categorie WHERE id_categorie_parent = $current";
        $result = mysqli_query($conn, $query);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $categories[] = $row['id_categorie'];
            $queue[] = $row['id_categorie'];
        }
    }
    
    return $categories;
}

$category_tree = $category_id > 0 ? buildCategoryTree($conn, $category_id) : [];
$category_ids = $category_id > 0 ? implode(',', $category_tree) : '';

// Ensure subcategory_filter is always an array
$brand_filter = isset($_GET['brand']) && is_array($_GET['brand']) ? $_GET['brand'] : [];
$color_filter = isset($_GET['color']) && is_array($_GET['color']) ? $_GET['color'] : [];
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : 0;
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : 10000;
$flavor_filter = isset($_GET['flavor']) && is_array($_GET['flavor']) ? $_GET['flavor'] : [];
$subcategory_filter = isset($_GET['subcategory']) && is_array($_GET['subcategory']) ? array_map('intval', $_GET['subcategory']) : (isset($_GET['subcategory']) ? [intval($_GET['subcategory'])] : []);

// Modified products query based on category_id
$products_query = "SELECT DISTINCT p.id_produit, p.lib_produit, p.desc_produit, p.prix_produit, 
                  p.en_promo, p.taux_reduction, m.nom_marque, c.libelle_categorie,
                  (SELECT image_path FROM images WHERE id_produit = p.id_produit ORDER BY ordre ASC LIMIT 1) as main_image";
if ($main_category_id == 1) {
    $products_query .= ", a.saveur, a.prix_variant";
} elseif ($main_category_id == 2) {
    $products_query .= ", v.couleur";
} elseif ($main_category_id == 3) {
    $products_query .= ", acc.couleur";
}

$products_query .= " FROM produits p
                  LEFT JOIN marque m ON p.id_marque = m.id_marque
                  LEFT JOIN categorie c ON p.id_categorie = c.id_categorie";

if ($main_category_id == 1) {
    $products_query .= " LEFT JOIN alimentaire a ON p.id_produit = a.id_produit";
} elseif ($main_category_id == 2) {
    $products_query .= " LEFT JOIN vetement v ON p.id_produit = v.id_produit";
} elseif ($main_category_id == 3) {
    $products_query .= " LEFT JOIN accessoire acc ON p.id_produit = acc.id_produit";
}

if ($category_id > 0) {
    $products_query .= " WHERE p.id_categorie IN ($category_ids)";
}

if (!empty($brand_filter)) {
    $brands = implode("','", array_map(function($brand) use ($conn) {
        return mysqli_real_escape_string($conn, $brand);
    }, $brand_filter));
    $products_query .= $category_id > 0 ? " AND" : " WHERE";
    $products_query .= " m.nom_marque IN ('$brands')";
}

if (!empty($color_filter) && in_array($main_category_id, [2, 3])) {
    $colors = implode("','", array_map(function($color) use ($conn) {
        return mysqli_real_escape_string($conn, $color);
    }, $color_filter));
    $products_query .= $category_id > 0 || !empty($brand_filter) ? " AND" : " WHERE";
    if ($main_category_id == 2) {
        $products_query .= " v.couleur IN ('$colors')";
    } elseif ($main_category_id == 3) {
        $products_query .= " acc.couleur IN ('$colors')";
    }
}

$products_query .= (!empty($brand_filter) || !empty($color_filter) || $category_id > 0) ? " AND" : " WHERE";
$products_query .= " ((p.prix_produit BETWEEN $price_min AND $price_max)";
if ($main_category_id == 1) {
    $products_query .= " OR (a.prix_variant BETWEEN $price_min AND $price_max)";
}
$products_query .= ")";

if (!empty($flavor_filter) && $main_category_id == 1) {
    $flavors = implode("','", array_map(function($flavor) use ($conn) {
        return mysqli_real_escape_string($conn, $flavor);
    }, $flavor_filter));
    $products_query .= " AND a.saveur IN ('$flavors')";
}

if (!empty($subcategory_filter)) {
    $subcats = implode(',', array_map('intval', $subcategory_filter));
    $products_query .= " AND p.id_categorie IN ($subcats)";
}

$products_per_page = 20;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $products_per_page;

$products_query .= " ORDER BY p.lib_produit ASC LIMIT $products_per_page OFFSET $offset";
$products_result = mysqli_query($conn, $products_query);

// Modified count query
$count_query = "SELECT COUNT(DISTINCT p.id_produit) as total 
                FROM produits p
                LEFT JOIN marque m ON p.id_marque = m.id_marque";
if ($main_category_id == 1) {
    $count_query .= " LEFT JOIN alimentaire a ON p.id_produit = a.id_produit";
} elseif ($main_category_id == 2) {
    $count_query .= " LEFT JOIN vetement v ON p.id_produit = v.id_produit";
} elseif ($main_category_id == 3) {
    $count_query .= " LEFT JOIN accessoire acc ON p.id_produit = acc.id_produit";
}

if ($category_id > 0) {
    $count_query .= " WHERE p.id_categorie IN ($category_ids)";
}

if (!empty($brand_filter)) {
    $brands = implode("','", array_map(function($brand) use ($conn) {
        return mysqli_real_escape_string($conn, $brand);
    }, $brand_filter));
    $count_query .= $category_id > 0 ? " AND" : " WHERE";
    $count_query .= " m.nom_marque IN ('$brands')";
}

if (!empty($color_filter) && in_array($main_category_id, [2, 3])) {
    $colors = implode("','", array_map(function($color) use ($conn) {
        return mysqli_real_escape_string($conn, $color);
    }, $color_filter));
    $count_query .= $category_id > 0 || !empty($brand_filter) ? " AND" : " WHERE";
    if ($main_category_id == 2) {
        $count_query .= " v.couleur IN ('$colors')";
    } elseif ($main_category_id == 3) {
        $count_query .= " acc.couleur IN ('$colors')";
    }
}

$count_query .= (!empty($brand_filter) || !empty($color_filter) || $category_id > 0) ? " AND" : " WHERE";
$count_query .= " ((p.prix_produit BETWEEN $price_min AND $price_max)";
if ($main_category_id == 1) {
    $count_query .= " OR (a.prix_variant BETWEEN $price_min AND $price_max)";
}
$count_query .= ")";

if (!empty($flavor_filter) && $main_category_id == 1) {
    $flavors = implode("','", array_map(function($flavor) use ($conn) {
        return mysqli_real_escape_string($conn, $flavor);
    }, $flavor_filter));
    $count_query .= " AND a.saveur IN ('$flavors')";
}

if (!empty($subcategory_filter)) {
    $subcats = implode(',', array_map('intval', $subcategory_filter));
    $count_query .= " AND p.id_categorie IN ($subcats)";
}

$count_result = mysqli_query($conn, $count_query);
$total_products = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_products / $products_per_page);

$brands_query = "SELECT DISTINCT m.id_marque, m.nom_marque, COUNT(p.id_produit) as count
                FROM marque m
                JOIN produits p ON m.id_marque = p.id_marque";
$brands_query .= $category_id > 0 ? " WHERE p.id_categorie IN ($category_ids)" : "";
$brands_query .= " GROUP BY m.id_marque ORDER BY m.nom_marque";
$brands_result = mysqli_query($conn, $brands_query);

$colors_query = "";
if ($main_category_id == 2) {
    $colors_query = "SELECT DISTINCT v.couleur, COUNT(*) as count 
                    FROM vetement v 
                    JOIN produits p ON v.id_produit = p.id_produit
                    WHERE p.id_categorie IN ($category_ids) AND v.couleur IS NOT NULL
                    GROUP BY v.couleur
                    ORDER BY v.couleur";
} elseif ($main_category_id == 3) {
    $colors_query = "SELECT DISTINCT acc.couleur, COUNT(*) as count 
                    FROM accessoire acc
                    JOIN produits p ON acc.id_produit = p.id_produit
                    WHERE p.id_categorie IN ($category_ids) AND acc.couleur IS NOT NULL
                    GROUP BY acc.couleur
                    ORDER BY acc.couleur";
}
$colors_result = $colors_query ? mysqli_query($conn, $colors_query) : false;

$flavors_query = "";
if ($main_category_id == 1) {
    $flavors_query = "SELECT DISTINCT a.saveur, COUNT(*) as count
                     FROM alimentaire a
                     JOIN produits p ON a.id_produit = p.id_produit
                     WHERE p.id_categorie IN ($category_ids) AND a.saveur IS NOT NULL
                     GROUP BY a.saveur
                     ORDER BY a.saveur";
}
$flavors_result = $flavors_query ? mysqli_query($conn, $flavors_query) : false;

$price_query = "SELECT MIN(price) as min_price, MAX(price) as max_price FROM (
                SELECT p.prix_produit as price FROM produits p";
if ($category_id > 0) {
    $price_query .= " WHERE p.id_categorie IN ($category_ids)";
}
if ($main_category_id == 1) {
    $price_query .= " UNION ALL
                     SELECT a.prix_variant as price FROM alimentaire a
                     JOIN produits p ON a.id_produit = p.id_produit";
    $price_query .= $category_id > 0 ? " WHERE p.id_categorie IN ($category_ids) AND a.prix_variant > 0" : " WHERE a.prix_variant > 0";
}
$price_query .= ") as prices";
$price_result = mysqli_query($conn, $price_query);
$price_range = mysqli_fetch_assoc($price_result);
$min_price = $price_range['min_price'] ?? 0;
$max_price = $price_range['max_price'] ?? 1000;

// Get view mode from URL, default to 3
$view_mode = isset($_GET['view']) ? intval($_GET['view']) : 3;
$filterSectionVisible = ($view_mode != 4);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - <?php echo $category_name; ?></title>
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        
        body {
            background-color: #f5f5f5;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .banner {
            width: 100vw;
            height: 370px;
            overflow: hidden;
            margin-bottom: 20px;
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
        }
        
        .banner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .category-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #333;
        }
        
        .content-wrapper {
            display: flex;
            gap: 20px;
        }
        
        .filter-section {
            width: 250px;
            background: white;
            padding: 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .filter-header {
            display: none;
        }
        
        .filter-content {
            margin-bottom: 20px;
        }
        
        .filter-group {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        
        .filter-group h4 {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            cursor: pointer;
            font-size: 14px;
            color: #333;
        }
        
        .filter-group h4 i {
            transition: transform 0.3s ease;
        }
        
        .filter-group h4.collapsed i {
            transform: rotate(-90deg);
        }
        
        .filter-options {
            max-height: 200px;
            overflow-y: auto;
        }
        
        .filter-option {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .filter-option input[type="checkbox"] {
            margin-right: 8px;
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #ddd;
            border-radius: 4px;
            outline: none;
            cursor: pointer;
            position: relative;
        }
        
        .filter-option input[type="checkbox"]:checked::before {
            content: '\2713';
            position: absolute;
            width: 12px;
            height: 12px;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #0066cc;
            font-size: 12px;
        }
        
        .filter-option label {
            font-size: 13px;
            color: #666;
            display: flex;
            justify-content: space-between;
            width: 100%;
        }
        
        .price-slider {
            margin: 10px 0;
        }
        
        .price-inputs {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }
        
        .price-inputs input {
            width: 45%;
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 3px;
        }
        
        .products-section {
            flex: 1;
        }
        
        .products-header {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-bottom: 20px;
            background: white;
            padding: 10px 15px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .view-options {
            display: flex;
            gap: 10px;
        }
        
        .view-option {
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 3px;
            transition: all 0.2s ease;
            font-size: 18px;
            position: relative;
        }
        
        .view-option.checked::after {
            content: '\2713';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: #0066cc;
            font-size: 16px;
        }
        
        .view-option:hover, .view-option.active {
            background-color: #f0f0f0;
            color: #0066cc;
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
        
        .product-card {
            background: white;
            border-radius: 17px;
            overflow: hidden;
            border: #D9D9D9 solid 1px;
            position: relative;
            max-height: 515px;
            max-width: 305px;
        }
        
        .product-image {
            position: relative;
            max-height: 400px;
            overflow: hidden;
        }
        
        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .product-card:hover .product-image img.main-image {
            opacity: 0;
        }
        
        .product-card:hover .product-image img.hover-image {
            opacity: 1;
        }
        
        .product-image img.hover-image {
            position: absolute;
            top: 0;
            left: 0;
            opacity: 0;
        }
        
        .wishlist-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: white;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 2;
        }
        
        .product-card:hover .wishlist-btn {
            opacity: 1;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-brand {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .product-name {
            font-size: 14px;
            color: #333;
            margin-bottom: 10px;
            font-weight: bold;
        }
        
        .product-price {
            font-size: 16px;
            color: #333;
            font-weight: bold;
        }
        
        .product-price .original-price {
            text-decoration: line-through;
            color: #999;
            margin-right: 5px;
            font-size: 14px;
        }
        
        .color-options {
            display: flex;
            gap: 5px;
            margin-top: 10px;
        }
        
        .color-option {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            cursor: pointer;
            border: 1px solid #ddd;
        }
        
        .out-of-stock {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }
        
        .out-of-stock span {
            background: #ff3b30;
            color: white;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 30px;
            gap: 5px;
        }
        
        .pagination a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 35px;
            height: 35px;
            background: white;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            transition: all 0.2s ease;
        }
        
        .pagination a:hover, .pagination a.active {
            background: #0066cc;
            color: white;
        }
        
        .filter-toggle {
            display: flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            font-size: 14px;
            color: #333;
            margin-bottom: 15px;
        }
        
        .filter-toggle i {
            margin-right: 5px;
        }
        
        @media (max-width: 992px) {
            .content-wrapper {
                flex-direction: column;
            }
            
            .filter-section {
                width: 100%;
                margin-bottom: 20px;
            }
            
            .filter-content {
                display: none;
            }
            
            .filter-content.show {
                display: block;
            }
            
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .product-image {
                height: 250px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <div class="container">
        <?php if ($show_banner): ?>
        <div class="banner">
            <img src="<?php echo $banner; ?>" alt="<?php echo $category_name; ?>">
        </div>
        <?php endif; ?>
        
        <h1 class="category-title"><?php echo $category_name; ?></h1>
        <div class="products-header">
            <div class="view-options">
                <div class="view-option <?php echo $view_mode == 4 ? 'checked active' : ''; ?>" data-view="4" title="Hide Filters (4 per row)">
                    <i class="fas fa-filter" style="transform: rotate(90deg);"></i>
                </div>
                <div class="view-option <?php echo $view_mode == 2 ? 'checked active' : ''; ?>" data-view="2" title="2 per row">
                    <i class="fas fa-grip-horizontal"></i>
                </div>
                <div class="view-option <?php echo $view_mode == 3 ? 'checked active' : ''; ?>" data-view="3" title="Default (3 per row)">
                    <i class="fas fa-th-large"></i>
                </div>
            </div>
        </div>
        <div class="content-wrapper">
            <div class="filter-section" id="filterSection" style="display: <?php echo $filterSectionVisible ? 'block' : 'none'; ?>;">
                <div class="filter-toggle" id="filterToggle">
                    <i class="fas fa-filter"></i> Filtres
                </div>
                <div class="filter-content" id="filterContent">
                    <form id="filterForm" action="" method="GET">
                        <input type="hidden" name="id" value="<?php echo $category_id; ?>">
                        <input type="hidden" name="page" value="1">
                        <input type="hidden" name="view" value="<?php echo $view_mode; ?>">
                        
                        <?php if (!empty($subcategories)): ?>
                        <div class="filter-group">
                            <h4>Sous-catégories <i class="fas fa-chevron-down"></i></h4>
                            <div class="filter-options">
                                <?php
                                $subcategory_tree = [];
                                foreach ($subcategories as $subcat) {
                                    $parent_id = $subcat['id_categorie_parent'] == $category_id ? 0 : $subcat['id_categorie_parent'];
                                    $subcategory_tree[$parent_id][] = $subcat;
                                }
                                
                                if (!empty($subcategory_tree[0])) {
                                    foreach ($subcategory_tree[0] as $subcat): ?>
                                        <div class="filter-option">
                                            <input type="checkbox" name="subcategory[]" id="subcat-<?php echo $subcat['id_categorie']; ?>" 
                                                   value="<?php echo $subcat['id_categorie']; ?>"
                                                   <?php echo in_array($subcat['id_categorie'], $subcategory_filter) ? 'checked' : ''; ?>
                                                   onchange="submitFilterForm()">
                                            <label for="subcat-<?php echo $subcat['id_categorie']; ?>">
                                                <?php echo htmlspecialchars($subcat['libelle_categorie']); ?>
                                            </label>
                                        </div>
                                        <?php if (!empty($subcategory_tree[$subcat['id_categorie']])): ?>
                                            <?php foreach ($subcategory_tree[$subcat['id_categorie']] as $subsubcat): ?>
                                                <div class="filter-option" style="margin-left: 20px;">
                                                    <input type="checkbox" name="subcategory[]" id="subcat-<?php echo $subsubcat['id_categorie']; ?>" 
                                                           value="<?php echo $subsubcat['id_categorie']; ?>"
                                                           <?php echo in_array($subsubcat['id_categorie'], $subcategory_filter) ? 'checked' : ''; ?>
                                                           onchange="submitFilterForm()">
                                                    <label for="subcat-<?php echo $subsubcat['id_categorie']; ?>">
                                                        <?php echo htmlspecialchars($subsubcat['libelle_categorie']); ?>
                                                    </label>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    <?php endforeach;
                                } else {
                                    echo '<div>Aucune sous-catégorie disponible</div>';
                                } ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="filter-group">
                            <h4>Marque <i class="fas fa-chevron-down"></i></h4>
                            <div class="filter-options">
                                <?php while ($brand = mysqli_fetch_assoc($brands_result)): ?>
                                <div class="filter-option">
                                    <input type="checkbox" name="brand[]" id="brand-<?php echo $brand['id_marque']; ?>" 
                                           value="<?php echo htmlspecialchars($brand['nom_marque']); ?>"
                                           <?php echo in_array($brand['nom_marque'], $brand_filter) ? 'checked' : ''; ?>
                                           onchange="submitFilterForm()">
                                    <label for="brand-<?php echo $brand['id_marque']; ?>">
                                        <?php echo htmlspecialchars($brand['nom_marque']); ?>
                                    </label>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        
                        <div class="filter-group">
                            <h4>Prix <i class="fas fa-chevron-down"></i></h4>
                            <div class="filter-options">
                                <div class="price-slider">
                                    <input type="range" id="price-range" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" 
                                           value="<?php echo $price_max; ?>" step="10" onchange="submitFilterForm()">
                                </div>
                                <div class="price-inputs">
                                    <input type="number" name="price_min" id="price-min" value="<?php echo $price_min; ?>" min="0" onchange="submitFilterForm()">
                                    <input type="number" name="price_max" id="price-max" value="<?php echo $price_max; ?>" min="0" onchange="submitFilterForm()">
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($main_category_id == 2 || $main_category_id == 3): ?>
                        <?php if ($colors_result && mysqli_num_rows($colors_result) > 0): ?>
                        <div class="filter-group">
                            <h4>Couleur <i class="fas fa-chevron-down"></i></h4>
                            <div class="filter-options">
                                <?php mysqli_data_seek($colors_result, 0); ?>
                                <?php while ($color = mysqli_fetch_assoc($colors_result)): ?>
                                <div class="filter-option">
                                    <input type="checkbox" name="color[]" id="color-<?php echo htmlspecialchars($color['couleur']); ?>" 
                                           value="<?php echo htmlspecialchars($color['couleur']); ?>"
                                           <?php echo in_array($color['couleur'], $color_filter) ? 'checked' : ''; ?>
                                           onchange="submitFilterForm()"
                                           style="background-color: <?php echo htmlspecialchars($color['couleur']); ?>;">
                                    <label for="color-<?php echo htmlspecialchars($color['couleur']); ?>"></label>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <?php if ($main_category_id == 1): ?>
                        <?php if ($flavors_result && mysqli_num_rows($flavors_result) > 0): ?>
                        <div class="filter-group">
                            <h4>Saveur <i class="fas fa-chevron-down"></i></h4>
                            <div class="filter-options">
                                <?php while ($flavor = mysqli_fetch_assoc($flavors_result)): ?>
                                <div class="filter-option">
                                    <input type="checkbox" name="flavor[]" id="flavor-<?php echo htmlspecialchars($flavor['saveur']); ?>" 
                                           value="<?php echo htmlspecialchars($flavor['saveur']); ?>"
                                           <?php echo in_array($flavor['saveur'], $flavor_filter) ? 'checked' : ''; ?>
                                           onchange="submitFilterForm()">
                                    <label for="flavor-<?php echo htmlspecialchars($flavor['saveur']); ?>">
                                        <?php echo htmlspecialchars(ucfirst($flavor['saveur'])); ?>
                                    </label>
                                </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <?php endif; ?>
                        
                        <button type="button" id="resetFilters" style="width: 100%; padding: 10px; background: #f5f5f5; color: #333; border: 1px solid #ddd; border-radius: 5px; cursor: pointer; margin-top: 10px;">Réinitialiser</button>
                    </form>
                </div>
            </div>
            
            <div class="products-section">
                <div class="products-grid <?php echo 'grid-' . ($view_mode == 4 && !$filterSectionVisible ? '4' : $view_mode); ?>">
                    <?php 
                    if (mysqli_num_rows($products_result) > 0) {
                        while ($product = mysqli_fetch_assoc($products_result)) {
                            $second_image_query = "SELECT image_path FROM images WHERE id_produit = {$product['id_produit']} ORDER BY ordre ASC LIMIT 1, 1";
                            $second_image_result = mysqli_query($conn, $second_image_query);
                            $second_image = mysqli_fetch_assoc($second_image_result);
                            $hover_image = $second_image ? $second_image['image_path'] : $product['main_image'];
                            
                            $stock_query = "";
                            $is_out_of_stock = false;
                            if ($main_category_id == 1) {
                                $stock_query = "SELECT SUM(stock_variant) as total_stock FROM alimentaire WHERE id_produit = {$product['id_produit']}";
                            } elseif ($main_category_id == 2) {
                                $stock_query = "SELECT SUM(stock_variant) as total_stock FROM vetement WHERE id_produit = {$product['id_produit']}";
                            } elseif ($main_category_id == 3) {
                                $stock_query = "SELECT SUM(stock_variant) as total_stock FROM accessoire WHERE id_produit = {$product['id_produit']}";
                            }
                            
                            if (!empty($stock_query)) {
                                $stock_result = mysqli_query($conn, $stock_query);
                                $stock_data = mysqli_fetch_assoc($stock_result);
                                $is_out_of_stock = ($stock_data['total_stock'] <= 0);
                            }
                            
                            $colors = [];
                            if ($main_category_id == 2) {
                                $colors_query = "SELECT DISTINCT couleur FROM vetement WHERE id_produit = {$product['id_produit']} AND stock_variant > 0";
                                $colors_result = mysqli_query($conn, $colors_query);
                                while ($color = mysqli_fetch_assoc($colors_result)) {
                                    $colors[] = $color['couleur'];
                                }
                            } elseif ($main_category_id == 3) {
                                $colors_query = "SELECT DISTINCT couleur FROM accessoire WHERE id_produit = {$product['id_produit']} AND stock_variant > 0";
                                $colors_result = mysqli_query($conn, $colors_query);
                                while ($color = mysqli_fetch_assoc($colors_result)) {
                                    $colors[] = $color['couleur'];
                                }
                            }
                            
                            $final_price = $product['prix_produit'];
                            if ($product['en_promo'] && $product['taux_reduction'] > 0) {
                                $final_price = $product['prix_produit'] * (1 - $product['taux_reduction'] / 100);
                            }
                            
                            $variant_price = 0;
                            if ($main_category_id == 1) {
                                if ($final_price == 0) {
                                    $variant_price_query = "SELECT MIN(prix_variant) as min_price FROM alimentaire WHERE id_produit = {$product['id_produit']} AND prix_variant > 0";
                                    $variant_price_result = mysqli_query($conn, $variant_price_query);
                                    $variant_price_data = mysqli_fetch_assoc($variant_price_result);
                                    $variant_price = $variant_price_data['min_price'] ?? 0;
                                }
                            }
                            
                            if ($final_price == 0 && $variant_price > 0) {
                                $final_price = $variant_price;
                            }
                    ?>
                    <div class="product-card">
                        <a href="produit.php?id=<?php echo $product['id_produit']; ?>">
                            <div class="product-image">
                                <img src="<?php echo '../'.$product['main_image']; ?>" alt="<?php echo htmlspecialchars($product['lib_produit']); ?>" class="main-image">
                                <img src="<?php echo '../'.$hover_image; ?>" alt="<?php echo htmlspecialchars($product['lib_produit']); ?>" class="hover-image">
                                <?php if ($is_out_of_stock): ?>
                                <div class="out-of-stock">
                                    <span>Rupture de stock</span>
                                </div>
                                <?php endif; ?>
                            </div>
                        </a>
                        <div class="wishlist-btn">
                            <i class="far fa-heart"></i>
                        </div>
                        <div class="product-info">
                            <div class="product-brand"><?php echo htmlspecialchars($product['nom_marque']); ?></div>
                            <h3 class="product-name"><?php echo htmlspecialchars($product['lib_produit']); ?></h3>
                            <div class="product-price">
                                <?php if ($product['en_promo'] && $product['taux_reduction'] > 0): ?>
                                <span class="original-price"><?php echo number_format($product['prix_produit'], 2); ?> TND</span>
                                <?php endif; ?>
                                <span><?php echo number_format($final_price, 2); ?> TND</span>
                            </div>
                            <?php if (!empty($colors)): ?>
                            <div class="color-options">
                                <?php foreach ($colors as $color): ?>
                                <div class="color-option" style="background-color: <?php echo $color; ?>;" data-color="<?php echo $color; ?>"></div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php
                        }
                    } else {
                        echo '<div style="grid-column: 1/-1; text-align: center; padding: 50px 0;">Aucun produit trouvé</div>';
                    }
                    ?>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page - 1; ?>&<?php echo http_build_query(array_filter($_GET, function($key) {
                            return !in_array($key, ['page', 'id']);
                        }, ARRAY_FILTER_USE_KEY)); ?>"><</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?id=<?php echo $category_id; ?>&page=<?php echo $i; ?>&<?php echo http_build_query(array_filter($_GET, function($key) {
                            return !in_array($key, ['page', 'id']);
                        }, ARRAY_FILTER_USE_KEY)); ?>" class="<?php echo $i == $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                        <a href="?id=<?php echo $category_id; ?>&page=<?php echo $page + 1; ?>&<?php echo http_build_query(array_filter($_GET, function($key) {
                            return !in_array($key, ['page', 'id']);
                        }, ARRAY_FILTER_USE_KEY)); ?>">></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>
    
    <script>
        function submitFilterForm() {
            document.getElementById('filterForm').submit();
        }

        let filterSectionVisible = <?php echo json_encode($filterSectionVisible); ?>;

        document.getElementById('filterToggle').addEventListener('click', function() {
            document.getElementById('filterContent').classList.toggle('show');
        });
        
        document.querySelectorAll('.filter-group h4').forEach(function(header) {
            header.addEventListener('click', function() {
                this.classList.toggle('collapsed');
                const options = this.nextElementSibling;
                options.style.display = options.style.display === 'none' ? 'block' : 'none';
            });
        });
        
        document.getElementById('resetFilters').addEventListener('click', function() {
            const currentUrl = new URL(window.location.href);
            const categoryId = currentUrl.searchParams.get('id');
            let newUrl = '?id=' + categoryId;
            window.location.href = newUrl;
        });
        
        document.querySelectorAll('.view-option').forEach(function(option) {
            option.addEventListener('click', function() {
                const view = this.getAttribute('data-view');
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('view', view);
                
                document.querySelectorAll('.view-option').forEach(opt => opt.classList.remove('checked'));
                this.classList.add('checked');

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
        
        document.querySelectorAll('.color-option').forEach(function(colorOption) {
            colorOption.addEventListener('click', function() {
                const color = this.getAttribute('data-color');
                const productCard = this.closest('.product-card');
                const productId = productCard.querySelector('a').href.split('id=')[1];
                
                fetch('get-color-image.php?product_id=' + productId + '&color=' + color)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.image_path) {
                            productCard.querySelector('.main-image').src = data.image_path;
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });

        if (!filterSectionVisible) {
            document.getElementById('filterSection').style.display = 'none';
            document.querySelector('.view-option[data-view="4"]').classList.add('checked');
        } else {
            document.querySelector('.view-option[data-view="<?php echo $view_mode; ?>"]').classList.add('checked');
        }
    </script>
</body>
</html>