<?php
// Start session at the very top
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "localhost";
include 'db.php';

// Get the current category ID from the URL (if any)
$category_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Function to get categories and their hierarchy
function getCategories($parent_id = NULL) {
    global $conn;
    if ($parent_id === NULL) {
        $sql = "SELECT * FROM Categorie WHERE id_categorie_parent IS NULL";
        $stmt = $conn->prepare($sql);
    } else {
        $sql = "SELECT * FROM Categorie WHERE id_categorie_parent = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $parent_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    return $result;
}

// Function to get the parent category of a subcategory
function getParentCategory($category_id) {
    global $conn;
    $sql = "SELECT id_categorie_parent FROM Categorie WHERE id_categorie = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id_categorie_parent'];
    }
    return null;
}

// Determine the active category
$active_category_id = $category_id;
$parent_category_id = $category_id ? getParentCategory($category_id) : null;

// Check if user is logged in
$isLoggedIn = isset($_SESSION['id_client']);

// Calculate total number of items in the cart
$cart = isset($_COOKIE['cart']) ? json_decode($_COOKIE['cart'], true) : [];
$cart_item_count = 0;
foreach ($cart as $item) {
    $cart_item_count += $item['quantity'];
}

// Determine the current page for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .active-subcategory {
            color: #8FC73E; /* Highlight active subcategory with color */
        }
        .active-category {
            color: #8FC73E !important; /* Highlight active category with color */
        }
        .active-page {
         
            font-weight: 700 !important; /* Explicitly set font-weight for active page */
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background-color: white;
        }
        .subcategory-container:hover .sub-subcategory-list li a:hover,
        .subcategory-container:hover .subcategory-list li :hover {
            color: #8BC34A;
        }
        .category {
            display: inline-block;
            padding: 10px;
            cursor: pointer;
            position: relative;
            font-size: 18px;
            font-weight: 600;
            transition: all 0.3s;
        }
        .desktop-nav .category:not(.active-category):not(.active-page) {
            font-weight: 600; /* Ensure non-active items stay at default font weight */
        }
        .blue_goats {
            color: #1a478b;
        }
        .category i {
            transition: transform 0.3s;
        }
        .category:hover {
            background-color: white;
            border-bottom: none;
        }
        .category:hover::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 1px;
            background-color: white;
            z-index: 10;
        }
        .subcategory-container {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            background: white;
            border: 1px solid #ddd;
            padding: 20px;
            min-width: 200px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }
        .subcategory-row {
            display: flex;
            gap: 40px;
        }
        .subcategory-title {
            color: #8FC73E;
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 10px;
            white-space: nowrap;
        }
        .sub-subcategory-list, .subcategory-list {
            list-style: none;
            padding-left: 0;
            margin-top: 5px;
        }
        .sub-subcategory-list li, 
        .subcategory-list li {
            color: black;
            padding: 5px 0;
            font-weight: 400;
            font-size: 15px;
            white-space: nowrap;
        }
        .sub-subcategory-list li:hover, 
        .subcategory-list li:hover {
            color: #8FC73E;
            cursor: pointer;
        }
        .category:hover .subcategory-container {
            display: block;
        }
        .header-front {
            padding: 20px 100px;
            justify-content: center;
            gap: 40px;
        }
        .search-container {
            background-color: #EEF1F6;
            padding: 5px 10px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            width: 700px;
            height: 45px;
            transition: all 0.3s;
        }
        .search-input {
            border: none;
            background: transparent;
            outline: none;
            flex: 1;
            font-family: 'Montserrat', sans-serif;
        }
        .search-container:focus-within {
            border: 2px solid #1F4493;
        }
        .search-container i {
            color: #1F4493;
            margin-right: 8px;
        }
        .search-container {
            position: relative;
        }
        .autocomplete-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-height: 400px;
            overflow-y: auto;
            z-index: 1100;
            margin-top: 5px;
            display: none;
        }
        .autocomplete-section {
            color: #8FC73E;
            font-weight: 600;
            font-size: 14px;
            padding: 10px 15px 5px;
            border-top: 1px solid #eee;
        }
        .autocomplete-section:first-child {
            border-top: none;
        }
        .autocomplete-item {
            padding: 10px 15px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .autocomplete-item:hover,
        .autocomplete-item.selected {
            background-color: #f5f7fa;
        }
        .item-content {
            display: flex;
            align-items: center;
        }
        .item-image {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            overflow: hidden;
            flex-shrink: 0;
            margin-right: 12px;
            background-color: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .item-image img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .item-icon {
            width: 40px;
            height: 40px;
            border-radius: 4px;
            background-color: #EEF1F6;
            color: #1F4493;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-right: 12px;
        }
        .item-details {
            flex-grow: 1;
        }
        .item-text {
            font-weight: 500;
            font-size: 14px;
            color: #333;
            margin-bottom: 2px;
        }
        .item-type {
            font-size: 12px;
            color: #777;
        }
        .search-all {
            border-top: 1px solid #eee;
            background-color: #f8f9fa;
            font-weight: 500;
        }
        .search-all .item-text {
            color: #1F4493;
        }
        @media (max-width: 992px) {
            .autocomplete-results {
                position: fixed;
                top: 140px;
                left: 20px;
                right: 20px;
                max-height: 50vh;
            }
        }
        .icon-btn {
            color: #1E4493;
            background: none;
            border: none;
            padding: 10px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            cursor: pointer;
        }
        .icon-btn:hover {
            background-color: #EDF0F6;
        }
        .icon-btn img {
            width: 24px;
            height: 24px;
            object-fit: contain;
            color: #1a478b;
        }
        .icons-group {
            display: flex;
            gap: 10px;
            width: 120px;
        }
        .icons-group a {
            text-decoration: none;
        }
        .dropdown-menu {
            min-width: 150px;
        }
        .dropdown-item {
            padding: 8px 16px;
            font-family: 'Montserrat', sans-serif;
        }
        .category a {
            text-decoration: none;
            color: #1F4493;
            display: inline-block;
            width: 100%;
        }
        .logo img {
            width: 150px;
            transition: transform 0.3s;
        }
        .logo:hover img {
            transform: scale(1.05);
        }
        .mobile-menu-btn {
            display: none;
            cursor: pointer;
            font-size: 24px;
            color: #1F4493;
            padding: 0;
            margin: 0;
            width: 40px;
            height: 40px;
        }
        .mobile-nav {
            display: none;
        }
        .cart-btn {
            position: relative;
        }
        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: #8FC73E;
            color: white;
            font-size: 10px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @media (max-width: 1200px) {
            .header-front {
                padding: 20px 50px;
            }
            .search-container {
                width: 500px;
            }
        }
        @media (max-width: 992px) {
            .header-front {
                padding: 15px 20px;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                gap: 0;
            }
            .mobile-menu-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                order: 1;
                min-width: 40px;
            }
            .logo {
                order: 2;
                text-align: center;
                flex-grow: 1;
            }
            .logo img {
                max-width: 120px;
                margin: 0 auto;
            }
            .icons-group {
                order: 3;
                width: auto;
                justify-content: flex-end;
                min-width: 40px;
            }
            .search-container {
                order: 4;
                width: 100%;
                margin: 10px 0 0;
            }
            .desktop-nav {
                display: none;
            }
            .mobile-nav {
                display: block;
                width: 100%;
                margin-top: 10px;
            }
            .mobile-search-container {
                width: 100%;
                order: 4;
                margin-top: 10px;
            }
            .icon-btn:not(.cart-btn) {
                display: none;
            }
            .cart-btn {
                display: flex;
            }
            .mobile-dropdown {
                width: 100%;
                padding: 8px 15px;
                margin-bottom: 5px;
                font-weight: 600;
                color: #1F4493;
                background-color: #f8f9fa;
                border: none;
                text-align: left;
                position: relative;
            }
            .mobile-dropdown i {
                position: absolute;
                right: 15px;
                top: 50%;
                transform: translateY(-50%);
            }
            .mobile-subcategories {
                padding-left: 20px;
                list-style: none;
                margin-bottom: 10px;
            }
            .mobile-subcategories li {
                padding: 5px 0;
            }
            .mobile-nav-link {
                display: block;
                width: 100%;
                padding: 8px 15px;
                margin-bottom: 5px;
                font-weight: 600;
                color: #1F4493;
                background-color: #f8f9fa;
                border: none;
                text-align: left;
                text-decoration: none;
            }
            .mobile-nav-link:hover {
                background-color: #e9ecef;
                color: #1F4493;
            }
            .mobile-nav-link.active-page {
                color: #8FC73E !important;
                font-weight: 600 !important;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex flex-column">
        <div>
            <header class="header-front d-flex justify-content-between align-items-center flex-wrap">
                <!-- Mobile Menu Button (Left) -->
                <div class="mobile-menu-btn">
                    <i class="fas fa-bars"></i>
                </div>
                
                <!-- Logo (Center on mobile, left on desktop) -->
                <div class="logo">
                    <a href="index.php">
                        <img src="../assets/img/logo/logo.png" alt="Logo" class="">
                    </a>
                </div>

                <!-- Search (Center on desktop, bottom on mobile) -->
                <div class="d-flex justify-content-center search-container-wrapper">
                    <div class="search-container d-none d-lg-flex">
                        <i class="fa fa-search fa-lg"></i>
                        <input type="text" class="search-input" placeholder="Rechercher">
                    </div>
                </div>

                <!-- Icons (Right) -->
                <div class="icons-group">
                    <div class="dropdown d-none d-lg-block">
                        <?php if ($isLoggedIn): ?>
                            <!-- Logged-in user: Show dropdown with Profile and Déconnexion -->
                            <button class="icon-btn" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="far fa-user fa-lg"></i>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownUser">
                                <li><a class="dropdown-item" href="client.php">Profile</a></li>
                                <li><a class="dropdown-item" href="logout.php">Déconnexion</a></li>
                            </ul>
                        <?php else: ?>
                            <!-- Not logged in: Redirect to login.php on click -->
                            <a href="login.php" class="icon-btn">
                                <i class="far fa-user fa-lg"></i>
                            </a>
                        <?php endif; ?>
                    </div>

                    <!-- Wishlist: Redirect to login if not logged in -->
                    <a href="wishlist.php" class="icon-btn d-none d-lg-flex">
                        <i class="far fa-heart fa-lg"></i>
                    </a>

                    <!-- Cart Button (Always visible) -->
                    <a href="panier.php" class="icon-btn cart-btn">
                        <img src="../assets/img/icons/Cart2.svg" alt="panier">
                        <?php if ($cart_item_count > 0): ?>
                            <span class="cart-count"><?php echo $cart_item_count; ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </header>
            
            <!-- Mobile Search Bar (Bottom) -->
            <div class="container mobile-search-container d-block d-lg-none">
                <div class="search-container">
                    <i class="fa fa-search fa-lg"></i>
                    <input type="text" class="search-input" placeholder="Rechercher">
                </div>
            </div>
        </div>
        
        <!-- Desktop Navigation -->
        <div class="container desktop-nav">
            <nav class="d-flex justify-content-between align-items-center flex-wrap">
                <?php
                $categories = getCategories();
                while ($category = $categories->fetch_assoc()) {
                    $is_active = ($active_category_id == $category['id_categorie']);
                    echo '<div class="category blue_goats d-flex ' . ($is_active ? 'active-category' : '') . '">';
                    echo '<a href="categorie.php?id=' . $category['id_categorie'] . '">' . htmlspecialchars($category['libelle_categorie']) . '</a>';
                    echo '<i class="bi bi-chevron-down mx-2"></i>';

                    $subcategories = getCategories($category['id_categorie']);
                    if ($subcategories->num_rows > 0) {
                        $hasSubSubcategories = false;
                        $subcategories_data = [];
                        
                        while ($sub = $subcategories->fetch_assoc()) {
                            $sub_subcategories = getCategories($sub['id_categorie']);
                            $has_children = $sub_subcategories->num_rows > 0;
                            $hasSubSubcategories = $hasSubSubcategories || $has_children;
                            $subcategories_data[] = [
                                'data' => $sub,
                                'has_children' => $has_children,
                                'children' => $has_children ? $sub_subcategories->fetch_all(MYSQLI_ASSOC) : []
                            ];
                        }

                        echo '<div class="subcategory-container">';
                        if ($hasSubSubcategories) {
                            echo '<div class="subcategory-row">';
                            foreach ($subcategories_data as $sub) {
                                $is_sub_active = ($active_category_id == $sub['data']['id_categorie']);
                                echo '<div>';
                                echo '<div class="subcategory-title ' . ($is_sub_active ? 'active-subcategory' : '') . '">';
                                echo '<a href="categorie.php?id=' . $sub['data']['id_categorie'] . '">' . htmlspecialchars($sub['data']['libelle_categorie']) . '</a>';
                                echo '</div>';
                                
                                if ($sub['has_children']) {
                                    echo '<ul class="sub-subcategory-list">';
                                    foreach ($sub['children'] as $sub_sub) {
                                        $is_sub_sub_active = ($active_category_id == $sub_sub['id_categorie']);
                                        echo '<li class="' . ($is_sub_sub_active ? 'active-subcategory' : '') . '">';
                                        echo '<a href="categorie.php?id=' . $sub_sub['id_categorie'] . '">' . htmlspecialchars($sub_sub['libelle_categorie']) . '</a>';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                }
                                echo '</div>';
                            }
                            echo '</div>';
                        } else {
                            echo '<ul class="subcategory-list">';
                            foreach ($subcategories_data as $sub) {
                                $is_sub_active = ($active_category_id == $sub['data']['id_categorie']);
                                echo '<li class="' . ($is_sub_active ? 'active-subcategory' : '') . '">';
                                echo '<a href="categorie.php?id=' . $sub['data']['id_categorie'] . '">' . htmlspecialchars($sub['data']['libelle_categorie']) . '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                        echo '</div>';
                    }
                    echo '</div>';
                }
                ?>
                
                <div class="category blue_goats text-uppercase">
                    <a href="promo.php" class="<?php echo $current_page == 'promo.php' ? 'active-page' : ''; ?>">Promo</a>
                </div>
                <div class="category blue_goats text-uppercase">
                    <a href="pack.php" class="<?php echo $current_page == 'pack.php' ? 'active-page' : ''; ?>">Pack</a>
                </div>
                <div class="category blue_goats text-uppercase">
                    <a href="marque.php" class="<?php echo $current_page == 'marque.php' ? 'active-page' : ''; ?>">Marques</a>
                </div>
            </nav>
        </div>
        
        <!-- Mobile Navigation (Hidden by default) -->
        <div class="container mobile-nav d-none">
            <div id="mobileMenuAccordion">
                <?php
                $categories = getCategories();
                $index = 1;
                while ($category = $categories->fetch_assoc()) {
                    $is_active = ($active_category_id == $category['id_categorie']);
                    echo '<button class="mobile-dropdown ' . ($is_active ? 'active-category' : '') . '" data-bs-toggle="collapse" data-bs-target="#collapse' . $index . '">';
                    echo htmlspecialchars($category['libelle_categorie']);
                    echo '<i class="bi bi-chevron-down"></i>';
                    echo '</button>';
                    
                    echo '<div id="collapse' . $index . '" class="collapse" data-bs-parent="#mobileMenuAccordion">';
                    $subcategories = getCategories($category['id_categorie']);
                    if ($subcategories->num_rows > 0) {
                        echo '<ul class="mobile-subcategories">';
                        while ($sub = $subcategories->fetch_assoc()) {
                            $is_sub_active = ($active_category_id == $sub['id_categorie']);
                            echo '<li class="' . ($is_sub_active ? 'active-subcategory' : '') . '">';
                            echo '<a class="text-decoration-none blue_goats" href="categorie.php?id=' . $sub['id_categorie'] . '">' . htmlspecialchars($sub['libelle_categorie']) . '</a>';
                            $sub_subcategories = getCategories($sub['id_categorie']);
                            if ($sub_subcategories->num_rows > 0) {
                                echo '<ul class="mobile-subcategories">';
                                while ($sub_sub = $sub_subcategories->fetch_assoc()) {
                                    $is_sub_sub_active = ($active_category_id == $sub_sub['id_categorie']);
                                    echo '<li class="' . ($is_sub_sub_active ? 'active-subcategory' : '') . '">';
                                    echo '<a class="text-decoration-none blue_goats" href="categorie.php?id=' . $sub_sub['id_categorie'] . '">' . htmlspecialchars($sub_sub['libelle_categorie']) . '</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                            echo '</li>';
                        }
                        echo '</ul>';
                    }
                    echo '</div>';
                    $index++;
                }
                ?>
                
                <!-- Fixed direct links in mobile menu -->
                <a href="promo.php" class="mobile-nav-link text-uppercase <?php echo $current_page == 'promo.php' ? 'active-page' : ''; ?>">Promo</a>
                <a href="pack.php" class="mobile-nav-link text-uppercase <?php echo $current_page == 'pack.php' ? 'active-page' : ''; ?>">Pack</a>
                <a href="marque.php" class="mobile-nav-link text-uppercase <?php echo $current_page == 'marque.php' ? 'active-page' : ''; ?>">Marques</a>
                
                <!-- Additional links for mobile -->
                <div class="mt-3">
                    <?php if ($isLoggedIn): ?>
                        <a href="client.php" class="mobile-nav-link <?php echo $current_page == 'client.php' ? 'active-page' : ''; ?>">
                            <i class="far fa-user me-2"></i> Profile
                        </a>
                        <a href="logout.php" class="mobile-nav-link <?php echo $current_page == 'logout.php' ? 'active-page' : ''; ?>">
                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="mobile-nav-link <?php echo $current_page == 'login.php' ? 'active-page' : ''; ?>">
                            <i class="far fa-user me-2"></i> Connexion
                        </a>
                    <?php endif; ?>
                    <a href="<?php echo $isLoggedIn ? 'wishlist.php' : 'login.php'; ?>" class="mobile-nav-link <?php echo $current_page == 'wishlist.php' ? 'active-page' : ''; ?>">
                        <i class="far fa-heart me-2"></i> Wishlist
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const categories = document.querySelectorAll('.category');
            
            categories.forEach(category => {
                category.addEventListener('mouseenter', () => {
                    const icon = category.querySelector('i');
                    if (icon) {
                        icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
                    }
                });

                category.addEventListener('mouseleave', () => {
                    const icon = category.querySelector('i');
                    if (icon) {
                        icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
                    }
                });
            });
            
            // Toggle mobile menu
            const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
            const mobileNav = document.querySelector('.mobile-nav');
            
            mobileMenuBtn.addEventListener('click', function() {
                mobileNav.classList.toggle('d-none');
                const icon = mobileMenuBtn.querySelector('i');
                if (icon.classList.contains('fa-bars')) {
                    icon.classList.replace('fa-bars', 'fa-times');
                } else {
                    icon.classList.replace('fa-times', 'fa-bars');
                }
            });
            
            // Toggle dropdown icons in mobile menu
            const mobileDropdowns = document.querySelectorAll('.mobile-dropdown');
            mobileDropdowns.forEach(dropdown => {
                dropdown.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    icon.classList.toggle('bi-chevron-down');
                    icon.classList.toggle('bi-chevron-up');
                });
            });
            
            // Close dropdowns when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.category')) {
                    document.querySelectorAll('.subcategory-container').forEach(dropdown => {
                        dropdown.style.display = 'none';
                    });
                }
            });

            // Search autocomplete
            const searchInputs = document.querySelectorAll('.search-input');
            searchInputs.forEach(searchInput => {
                const autocompleteContainer = document.createElement('div');
                autocompleteContainer.className = 'autocomplete-results';
                searchInput.parentNode.appendChild(autocompleteContainer);
                
                const searchForm = document.createElement('div');
                searchForm.action = 'search_results.php';
                searchForm.method = 'get';
                searchInput.parentNode.appendChild(searchForm);
                
                searchForm.appendChild(searchInput.cloneNode(true));
                searchInput.parentNode.removeChild(searchInput);
                
                const newSearchInput = searchForm.querySelector('.search-input');
                newSearchInput.name = 'query';
                
                let debounceTimer;
                newSearchInput.addEventListener('input', function() {
                    const query = this.value.trim();
                    clearTimeout(debounceTimer);
                    if (query.length < 2) {
                        autocompleteContainer.innerHTML = '';
                        autocompleteContainer.style.display = 'none';
                        return;
                    }
                    debounceTimer = setTimeout(() => {
                        fetchSuggestions(query, autocompleteContainer);
                    }, 300);
                });
                
                newSearchInput.addEventListener('keydown', function(e) {
                    const items = autocompleteContainer.querySelectorAll('.autocomplete-item');
                    if (!items.length) return;
                    const current = autocompleteContainer.querySelector('.autocomplete-item.selected');
                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        if (!current) {
                            items[0].classList.add('selected');
                        } else {
                            const next = [...items].indexOf(current) + 1;
                            if (next < items.length) {
                                current.classList.remove('selected');
                                items[next].classList.add('selected');
                            }
                        }
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        if (current) {
                            const prev = [...items].indexOf(current) - 1;
                            if (prev >= 0) {
                                current.classList.remove('selected');
                                items[prev].classList.add('selected');
                            }
                        }
                    } else if (e.key === 'Enter' && current) {
                        e.preventDefault();
                        window.location.href = current.getAttribute('data-url');
                    }
                });
                
                document.addEventListener('click', function(e) {
                    if (!newSearchInput.contains(e.target) && !autocompleteContainer.contains(e.target)) {
                        autocompleteContainer.style.display = 'none';
                    }
                });
                
                newSearchInput.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !autocompleteContainer.querySelector('.autocomplete-item.selected')) {
                        searchForm.submit();
                    }
                });
            });
            
            function fetchSuggestions(query, container) {
                fetch(`search_suggestions.php?term=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        container.innerHTML = '';
                        if (data.length === 0) {
                            container.style.display = 'none';
                            return;
                        }
                        
                        const grouped = {
                            product: [],
                            brand: [],
                            category: []
                        };
                        
                        data.forEach(item => {
                            grouped[item.type].push(item);
                        });
                        
                        buildSection('Produits', grouped.product, container);
                        buildSection('Marques', grouped.brand, container);
                        buildSection('Catégories', grouped.category, container);
                        
                        // Add "See all results" option
                        if (data.length > 0) {
                            const allResultsItem = document.createElement('div');
                            allResultsItem.className = 'autocomplete-item search-all';
                            allResultsItem.setAttribute('data-url', `search_results.php?query=${encodeURIComponent(query)}`);
                            allResultsItem.innerHTML = `
                                <div class="item-content">
                                    <div class="item-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div class="item-details">
                                        <div class="item-text">Voir tous les résultats pour "${query}"</div>
                                    </div>
                                </div>
                            `;
                            container.appendChild(allResultsItem);
                            allResultsItem.addEventListener('click', () => {
                                window.location.href = allResultsItem.getAttribute('data-url');
                            });
                        }
                        
                        container.style.display = 'block';
                        
                        // Add click handlers for autocomplete items
                        container.querySelectorAll('.autocomplete-item').forEach(item => {
                            item.addEventListener('click', () => {
                                window.location.href = item.getAttribute('data-url');
                            });
                        });
                    })
                    .catch(error => {
                        console.error('Error fetching suggestions:', error);
                        container.style.display = 'none';
                    });
            }
            
            function buildSection(title, items, container) {
                if (items.length === 0) return;
                
                const section = document.createElement('div');
                section.className = 'autocomplete-section';
                section.textContent = title;
                container.appendChild(section);
                
                items.forEach(item => {
                    const div = document.createElement('div');
                    div.className = 'autocomplete-item';
                    let url;
                    if (item.type === 'product') {
                        url = `produit.php?id=${item.id}`;
                    } else if (item.type === 'brand') {
                        url = `marque.php?id=${item.id}`;
                    } else if (item.type === 'category') {
                        url = `categorie.php?id=${item.id}`;
                    }
                    div.setAttribute('data-url', url);
                    
                    let content = `
                        <div class="item-content">
                            ${item.image ? `
                                <div class="item-image">
                                    <img src="${item.image}" alt="${item.name}">
                                </div>
                            ` : `
                                <div class="item-icon">
                                    <i class="fas ${item.type === 'produit' ? 'fa-box' : item.type === 'brand' ? 'fa-tag' : 'fa-list'}"></i>
                                </div>
                            `}
                            <div class="item-details">
                                <div class="item-text">${item.name}</div>
                                <div class="item-type">${item.type.charAt(0).toUpperCase() + item.type.slice(1)}</div>
                            </div>
                        </div>
                    `;
                    div.innerHTML = content;
                    container.appendChild(div);
                });
            }
        });
    </script>
</body>
</html>
