<?php
// Database connection
include('db.php');

// Check if connection is valid
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Get the marque ID from URL
$marque_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get marque details
$sql_marque = "SELECT * FROM marque WHERE id_marque = ?";
$marque_stmt = $conn->prepare($sql_marque);
if ($marque_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$marque_stmt->bind_param("i", $marque_id);
$marque_stmt->execute();
$marque_result = $marque_stmt->get_result();
$marque = $marque_result->fetch_assoc();

// Get only categories that have products for this marque
$sql_categories = "SELECT c.id_categorie, c.libelle_categorie, c.id_categorie_parent, 
                   COUNT(p.id_produit) as product_count
                   FROM categorie c
                   JOIN produits p ON c.id_categorie = p.id_categorie
                   WHERE p.id_marque = ?
                   GROUP BY c.id_categorie";
$categories_stmt = $conn->prepare($sql_categories);
if ($categories_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$categories_stmt->bind_param("i", $marque_id);
$categories_stmt->execute();
$categories_result = $categories_stmt->get_result();

// Get products for the marque
$sql_products = "SELECT p.*, i.image_path, i.couleur 
                 FROM produits p 
                 LEFT JOIN images i ON p.id_produit = i.id_produit AND i.ordre = 1
                 WHERE p.id_marque = ?
                 ORDER BY p.id_produit ASC";
$products_stmt = $conn->prepare($sql_products);
if ($products_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$products_stmt->bind_param("i", $marque_id);
$products_stmt->execute();
$products_result = $products_stmt->get_result();

// Determine min and max price for the price filter
$sql_price_range = "SELECT MIN(p.prix_produit) as min_price, MAX(p.prix_produit) as max_price 
                    FROM produits p 
                    WHERE p.id_marque = ?";
$price_range_stmt = $conn->prepare($sql_price_range);
if ($price_range_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$price_range_stmt->bind_param("i", $marque_id);
$price_range_stmt->execute();
$price_range_result = $price_range_stmt->get_result();
$price_range = $price_range_result->fetch_assoc();
$min_price = $price_range['min_price'] ?? 0;
$max_price = $price_range['max_price'] ?? 1000;

// Get distinct colors for products (for color filter)
$sql_colors = "SELECT DISTINCT i.couleur 
               FROM images i 
               JOIN produits p ON i.id_produit = p.id_produit 
               WHERE p.id_marque = ? AND i.couleur IS NOT NULL";
$colors_stmt = $conn->prepare($sql_colors);
if ($colors_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$colors_stmt->bind_param("i", $marque_id);
$colors_stmt->execute();
$colors_result = $colors_stmt->get_result();

// Check if products have sizes, saveurs, formats
$sql_check_sizes = "SELECT COUNT(*) as has_sizes 
                    FROM vetement v
                    JOIN produits p ON v.id_produit = p.id_produit
                    WHERE p.id_marque = ?";
$check_sizes_stmt = $conn->prepare($sql_check_sizes);
if ($check_sizes_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$check_sizes_stmt->bind_param("i", $marque_id);
$check_sizes_stmt->execute();
$check_sizes_result = $check_sizes_stmt->get_result();
$has_sizes = ($check_sizes_result->fetch_assoc()['has_sizes'] > 0);

$sql_check_saveurs = "SELECT COUNT(*) as has_saveurs 
                      FROM alimentaire a
                      JOIN produits p ON a.id_produit = p.id_produit
                      WHERE p.id_marque = ?";
$check_saveurs_stmt = $conn->prepare($sql_check_saveurs);
if ($check_saveurs_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$check_saveurs_stmt->bind_param("i", $marque_id);
$check_saveurs_stmt->execute();
$check_saveurs_result = $check_saveurs_stmt->get_result();
$has_saveurs = ($check_saveurs_result->fetch_assoc()['has_saveurs'] > 0);

$sql_check_formats = "SELECT COUNT(*) as has_formats 
                      FROM alimentaire a
                      JOIN produits p ON a.id_produit = p.id_produit
                      WHERE p.id_marque = ?";
$check_formats_stmt = $conn->prepare($sql_check_formats);
if ($check_formats_stmt === false) {
    die("Prepare failed: " . $conn->error);
}
$check_formats_stmt->bind_param("i", $marque_id);
$check_formats_stmt->execute();
$check_formats_result = $check_formats_stmt->get_result();
$has_formats = ($check_formats_result->fetch_assoc()['has_formats'] > 0);

// Get distinct saveurs, sizes, formats if they exist
$saveurs = $has_saveurs ? $conn->query("SELECT DISTINCT a.saveur FROM alimentaire a JOIN produits p ON a.id_produit = p.id_produit WHERE p.id_marque = $marque_id")->fetch_all(MYSQLI_ASSOC) : [];
$sizes = $has_sizes ? $conn->query("SELECT DISTINCT v.taille FROM vetement v JOIN produits p ON v.id_produit = p.id_produit WHERE p.id_marque = $marque_id")->fetch_all(MYSQLI_ASSOC) : [];
$formats = $has_formats ? $conn->query("SELECT DISTINCT a.format FROM alimentaire a JOIN produits p ON a.id_produit = p.id_produit WHERE p.id_marque = $marque_id")->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Produits de <?php echo htmlspecialchars($marque['nom_marque'] ?? 'Marque'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="style.css">
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="../assets/css/style.css">

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
        .cover-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }

        .filter-section {
            position: sticky;
            top: 0;
            border: 1px solid #ddd;
           border-radius: 7px;
            overflow-y: auto;
            width: 300px;
        }

     

        .filter-section ul {
            list-style: none;
            padding-left: 0;
            margin-bottom: 20px;
        }

        .filter-section ul li {
            margin-bottom: 5px;
        }

        .product-card {
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
          
            background: #fff;
            position: relative;
            width: 305px;
            height: 515px;
        }

        .product-card img {
            width: 100%;
            height: 400px;
            object-fit: contain;
        }

        .product-card p {
            margin: 5px 0;
        }

        .category-title {
            color: #28a745;
            font-weight: bold;
            font-size: 24px;
        }

        .pagination {
            justify-content: center;
            margin-top: 20px;
        }

        .heart-icon {
            position: absolute;
            top: 10px;
            right: 10px;
            font-size: 20px;
            cursor: pointer;
            color: #ccc;
        }

        .heart-icon.active {
            color: red;
        }

        .price-display {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        .layout-icons {
            position: sticky;
            top: 10px;
            left: 10px;
            z-index: 1000;
        }

        .filter-toggle {
            cursor: pointer;
            font-size: 20px;
        }

        @media (max-width: 768px) {
            .filter-section {
                display: none;
                position: fixed;
                width: 250px;
                left: -250px;
                transition: left 0.3s;
            }

            .filter-section.active {
                left: 0;
                display: block;
            }

            .col-md-9 {
                width: 100%;
            }
        }

        .filter-header {
            font-weight: bold;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .filter-body {
            display: none;
            padding: 10px 0;
        }

        .filter-body.show {
            display: block;
        }
    </style>
</head>

<body>
    <!-- Header included via include('header.php') -->
<?php include('header.php');
?>
    <!-- Marque Cover Image -->
    <?php if ($marque && !empty($marque['cover_marque'])): ?>
        <div class="cover-image-container">
            <img src="<?php echo htmlspecialchars($marque['cover_marque']); ?>" alt="<?php echo htmlspecialchars($marque['nom_marque']); ?>" class="cover-image">
        </div>
    <?php endif; ?>

    <div class="my-5 d-flex">
        <div class=" mx-5">
            <!-- Filter Toggle Icon -->

            <div class="layout-icons mb-4"> <i class="fas fa-filter filter-toggle" id="filterToggle"></i>
                <i class="fas fa-th-large" id="gridView" title="2 products per row"></i>
                <i class="fas fa-th" id="defaultView" title="3 products per row"></i>
            </div>
            <!-- Filter Section -->
            <div class=" filter-section  p-3">
                <div class="filter-header" data-target="categoryFilterBody">
                    categorie
                    <i class="fa fa-chevron-up"></i>
                </div>
                <div id="categoryFilterBody" class="filter-body show">
                    <ul>
                        <?php while ($cat = $categories_result->fetch_assoc()): ?>
                            <li>
                                <input type="checkbox" id="cat-<?php echo $cat['id_categorie']; ?>" class="category-filter" data-id="<?php echo $cat['id_categorie']; ?>">
                                <label for="cat-<?php echo $cat['id_categorie']; ?>"><?php echo htmlspecialchars($cat['libelle_categorie']); ?> (<?php echo $cat['product_count']; ?>)</label>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>

                <div class="filter-header" data-target="priceFilterBody">
                    prix
                    <i class="fa fa-chevron-up"></i>
                </div>
                <div id="priceFilterBody" class="filter-body show">
                    <input type="range" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $max_price; ?>" class="form-range" id="priceRange">
                    <div class="price-display">
                        <span id="priceMin"><?php echo $min_price; ?></span>
                        <span>-</span>
                        <span id="priceMax"><?php echo $max_price; ?></span>
                        <span>TND</span>
                    </div>
                </div>

                <?php if ($colors_result->num_rows > 0): ?>
                    <div class="filter-header" data-target="colorFilterBody">
                        color
                        <i class="fa fa-chevron-up"></i>
                    </div>
                    <div id="colorFilterBody" class="filter-body show">
                        <ul>
                            <?php while ($color = $colors_result->fetch_assoc()): if (!empty($color['couleur'])): ?>
                                    <li>
                                        <input type="checkbox" id="color-<?php echo htmlspecialchars($color['couleur']); ?>" class="color-filter" data-color="<?php echo htmlspecialchars($color['couleur']); ?>">
                                        <label for="color-<?php echo htmlspecialchars($color['couleur']); ?>"><?php echo htmlspecialchars($color['couleur']); ?></label>
                                    </li>
                            <?php endif;
                            endwhile; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($has_sizes): ?>
                    <div class="filter-header" data-target="sizeFilterBody">
                        taille
                        <i class="fa fa-chevron-up"></i>
                    </div>
                    <div id="sizeFilterBody" class="filter-body show">
                        <ul>
                            <?php foreach ($sizes as $size): ?>
                                <li>
                                    <input type="checkbox" id="size-<?php echo htmlspecialchars($size['taille']); ?>" class="size-filter" data-size="<?php echo htmlspecialchars($size['taille']); ?>">
                                    <label for="size-<?php echo htmlspecialchars($size['taille']); ?>"><?php echo htmlspecialchars($size['taille']); ?></label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($has_saveurs): ?>
                    <div class="filter-header" data-target="saveurFilterBody">
                        saveur
                        <i class="fa fa-chevron-up"></i>
                    </div>
                    <div id="saveurFilterBody" class="filter-body show">
                        <ul>
                            <?php foreach ($saveurs as $saveur): ?>
                                <li>
                                    <input type="checkbox" id="saveur-<?php echo htmlspecialchars($saveur['saveur']); ?>" class="saveur-filter" data-saveur="<?php echo htmlspecialchars($saveur['saveur']); ?>">
                                    <label for="saveur-<?php echo htmlspecialchars($saveur['saveur']); ?>"><?php echo htmlspecialchars($saveur['saveur']); ?></label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <?php if ($has_formats): ?>
                    <div class="filter-header" data-target="formatFilterBody">
                        Format
                        <i class="fa fa-chevron-up"></i>
                    </div>
                    <div id="formatFilterBody" class="filter-body show">
                        <ul>
                            <?php foreach ($formats as $format): ?>
                                <li>
                                    <input type="checkbox" id="format-<?php echo htmlspecialchars($format['format']); ?>" class="format-filter" data-format="<?php echo htmlspecialchars($format['format']); ?>">
                                    <label for="format-<?php echo htmlspecialchars($format['format']); ?>"><?php echo htmlspecialchars($format['format']); ?></label>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </div></div>

            <!-- Products Section -->
            <div class="col-md-9">
                <h2 class="category-title mb-4"><?php echo htmlspecialchars($marque['nom_marque'] ?? 'Marque'); ?></h2>

                <div class="row" id="products-container">
                    <?php
                    $products = $products_result->fetch_all(MYSQLI_ASSOC);
                    $total_products = count($products);
                    $products_per_page = 15; // 5 rows x 3 columns
                    $total_pages = ceil($total_products / $products_per_page);
                    $current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    $start = ($current_page - 1) * $products_per_page;
                    $display_products = array_slice($products, $start, $products_per_page);

                    foreach ($display_products as $row) {
                        $cat_sql = "SELECT libelle_categorie FROM categorie WHERE id_categorie = ?";
                        $cat_stmt = $conn->prepare($cat_sql);
                        if ($cat_stmt === false) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $cat_stmt->bind_param("i", $row['id_categorie']);
                        $cat_stmt->execute();
                        $cat_result = $cat_stmt->get_result();
                        $category_name = $cat_result->fetch_assoc()['libelle_categorie'] ?? 'N/A';

                        $sizes_sql = "SELECT taille FROM vetement WHERE id_produit = ?";
                        $sizes_stmt = $conn->prepare($sizes_sql);
                        if ($sizes_stmt === false) {
                            die("Prepare failed: " . $conn->error);
                        }
                        $sizes_stmt->bind_param("i", $row['id_produit']);
                        $sizes_stmt->execute();
                        $sizes_result = $sizes_stmt->get_result();
                        $product_sizes = [];
                        while ($size_row = $sizes_result->fetch_assoc()) {
                            $product_sizes[] = $size_row['taille'];
                        }
                        $data_sizes = implode(',', $product_sizes);

                        $display_price = $row['prix_produit'];
                        if ($row['en_promo'] && $row['taux_reduction'] > 0) {
                            $display_price = $row['prix_produit'] * (1 - $row['taux_reduction'] / 100);
                        }
                    ?>
                        <div class="col-md-4 mb-4 product-item"
                            data-category="<?php echo $row['id_categorie']; ?>"
                            data-price="<?php echo $display_price; ?>"
                            data-color="<?php echo htmlspecialchars($row['couleur'] ?? ''); ?>"
                            data-sizes="<?php echo htmlspecialchars($data_sizes); ?>">
                            <div class="product-card">
                                <i class="fa-regular fa-heart heart-icon" data-product-id="<?php echo $row['id_produit']; ?>"></i>
                                <a href="product.php?id=<?php echo $row['id_produit']; ?>">
                                    <img src="<?php echo '../' . ($row['image_path'] ?? 'https://via.placeholder.com/150'); ?>" alt="<?php echo htmlspecialchars($row['lib_produit']); ?>">
                                </a>
                                <p><?php echo htmlspecialchars($row['lib_produit']); ?></p>
                                <?php if ($row['en_promo'] && $row['taux_reduction'] > 0): ?>
                                    <p><del><?php echo number_format($row['prix_produit'], 2); ?> TND</del> <span class="text-danger"><?php echo number_format($display_price, 2); ?> TND</span></p>
                                <?php else: ?>
                                    <p><?php echo number_format($display_price, 2); ?> TND</p>
                                <?php endif; ?>
                              
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                <a class="page-link" href="?id=<?php echo $marque_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            </div>
        </div>
    </div>

    <?php include('footer.php'); ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle filter section visibility
            $('#filterToggle').click(function() {
                const filterSection = $('.filter-section');
                if (window.innerWidth <= 768) {
                    // Mobile: Slide in/out
                    filterSection.toggleClass('active');
                } else {
                    // Desktop: Toggle visibility
                    filterSection.slideToggle(200, function() {
                        $(this).toggleClass('active');
                        // Ensure display is set correctly after animation
                        $(this).css('display', $(this).hasClass('active') ? 'block' : 'none');
                    });
                }
            });

            // Toggle filter sections with animation
            $('.filter-header').click(function() {
                const targetId = $(this).data('target');
                const filterBody = $('#' + targetId);
                const icon = $(this).find('i');

                // Toggle the show class with animation
                filterBody.slideToggle(200, function() {
                    $(this).toggleClass('show');
                });

                // Toggle the chevron icon
                icon.toggleClass('fa-chevron-up fa-chevron-down');
            });

            // Initialize filter sections based on current state
            $('.filter-body').each(function() {
                if ($(this).hasClass('show')) {
                    $(this).show();
                }
            });

            // Wishlist functionality
            const heartIcons = document.querySelectorAll('.heart-icon');
            heartIcons.forEach(icon => {
                const productId = icon.getAttribute('data-product-id');
                const wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
                if (wishlist.includes(parseInt(productId))) {
                    icon.classList.remove('fa-regular');
                    icon.classList.add('fa-solid');
                    icon.classList.add('active');
                }
                icon.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.classList.toggle('fa-regular');
                    this.classList.toggle('fa-solid');
                    this.classList.toggle('active');
                    let wishlist = JSON.parse(localStorage.getItem('wishlist') || '[]');
                    if (this.classList.contains('active')) {
                        if (!wishlist.includes(parseInt(productId))) wishlist.push(parseInt(productId));
                    } else {
                        wishlist = wishlist.filter(id => id !== parseInt(productId));
                    }
                    localStorage.setItem('wishlist', JSON.stringify(wishlist));
                });
            });

            // Price range filter
            const priceRange = document.getElementById('priceRange');
            const priceMax = document.getElementById('priceMax');
            priceRange.addEventListener('input', function() {
                priceMax.textContent = this.value;
                filterProducts();
            });

            // Filter checkboxes
            const filters = document.querySelectorAll('.category-filter, .color-filter, .size-filter, .saveur-filter, .format-filter');
            filters.forEach(filter => filter.addEventListener('change', filterProducts));

            // Layout toggle
            const gridView = document.getElementById('gridView');
            const defaultView = document.getElementById('defaultView');
            const products = document.querySelectorAll('.product-item');
            gridView.addEventListener('click', function() {
                products.forEach(p => p.classList.remove('col-md-4'));
                products.forEach(p => p.classList.add('col-md-6'));
            });
            defaultView.addEventListener('click', function() {
                products.forEach(p => p.classList.remove('col-md-6'));
                products.forEach(p => p.classList.add('col-md-4'));
            });

            // Filter products function
            function filterProducts() {
                const maxPrice = parseFloat(priceRange.value);
                const selectedCategories = Array.from(document.querySelectorAll('.category-filter:checked')).map(f => f.getAttribute('data-id'));
                const selectedColors = Array.from(document.querySelectorAll('.color-filter:checked')).map(f => f.getAttribute('data-color'));
                const selectedSizes = Array.from(document.querySelectorAll('.size-filter:checked')).map(f => f.getAttribute('data-size'));
                const selectedSaveurs = Array.from(document.querySelectorAll('.saveur-filter:checked')).map(f => f.getAttribute('data-saveur'));
                const selectedFormats = Array.from(document.querySelectorAll('.format-filter:checked')).map(f => f.getAttribute('data-format'));

                products.forEach(product => {
                    const productPrice = parseFloat(product.getAttribute('data-price'));
                    const productCategory = product.getAttribute('data-category');
                    const productColor = product.getAttribute('data-color') || '';
                    const productSizes = product.getAttribute('data-sizes').split(',').filter(s => s);
                    const categoryMatch = selectedCategories.length === 0 || selectedCategories.includes(productCategory);
                    const colorMatch = selectedColors.length === 0 || (productColor && selectedColors.includes(productColor));
                    const sizeMatch = selectedSizes.length === 0 || selectedSizes.some(s => productSizes.includes(s));
                    const priceMatch = productPrice <= maxPrice;
                    const saveurMatch = selectedSaveurs.length === 0;
                    const formatMatch = selectedFormats.length === 0;
                    if (categoryMatch && colorMatch && sizeMatch && priceMatch && saveurMatch && formatMatch) {
                        product.style.display = '';
                    } else {
                        product.style.display = 'none';
                    }
                });
            }
        });
    </script>
</body>

</html>