<?php
include('db.php');
include('navbar.php');

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($product_id <= 0) {
    die("Invalid product ID");
}

// Fetch product data with error handling
$product_query = "SELECT p.*, m.nom_marque, c.libelle_categorie 
                 FROM produits p 
                 LEFT JOIN marque m ON p.id_marque = m.id_marque 
                 LEFT JOIN categorie c ON p.id_categorie = c.id_categorie 
                 WHERE p.id_produit = $product_id";
$product_result = mysqli_query($conn, $product_query);

if (!$product_result) {
    die("Database error: " . mysqli_error($conn));
}

$product = mysqli_fetch_assoc($product_result);

if (!$product) {
    die("Product not found");
}

// Fetch variants (vetement table for this product)
$variants_query = "SELECT taille, couleur, stock_variant 
                  FROM vetement 
                  WHERE id_produit = $product_id";
$variants_result = mysqli_query($conn, $variants_query);

if (!$variants_result) {
    die("Database error: " . mysqli_error($conn));
}

$variants = [];
$colors = [];
while ($variant = mysqli_fetch_assoc($variants_result)) {
    $variants[] = $variant;
    $colors[] = $variant['couleur'];
}
$colors = array_unique($colors);
$default_color = !empty($colors) ? $colors[0] : '';

// Fetch images for the product, grouped by color
$images_query = "SELECT image_path, couleur 
                FROM images 
                WHERE id_produit = $product_id 
                ORDER BY couleur, ordre";
$images_result = mysqli_query($conn, $images_query);

if (!$images_result) {
    die("Database error: " . mysqli_error($conn));
}

$images_by_color = [];
while ($image = mysqli_fetch_assoc($images_result)) {
    $color = $image['couleur'] ?? 'default';
    $images_by_color[$color][] = $image['image_path'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Product Details</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
        }
        .product-container {
            max-width: 1200px;
            margin: 20px auto;
            display: flex;
            gap: 20px;
        }
        .details-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            flex: 2;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }.product-container > div {
    display: flex;
    flex-direction: column;
}

        .image-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            flex: 1;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
         height: 100%;
            position: relative;
        }
        .image-gallery {
            display: flex;
            overflow-x: auto;
            scroll-behavior: smooth;
            gap: 10px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .image-gallery::-webkit-scrollbar {
            display: none;
        }
        .image-gallery img {
            width: 60px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            flex-shrink: 0; 
            border: #D9D9D9 1px solid;
        }
        .main-image {
            max-width: 100%;
            max-height: 300px;
            object-fit: contain;
            margin-bottom: 20px;
        }
        .chevron {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0,0,0,0.5);
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 50%;
            font-size: 18px;
            z-index: 10;
        }
        .chevron-left {
            left: 10px;
        }
        .chevron-right {
            right: 10px;
        }
        h5 {
            font-weight: bold;
            margin-bottom: 10px;
        }
   
        .variant-option {
            padding: 0px 10px;
            border-radius: 5px;
            cursor: pointer;
        }
        .color-circle {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            border: 1px solid #dee2e6;
            cursor: pointer;
            margin-right: 5px;
        }
        .color-circle.selected {
            border: 2px solid #007bff;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }
        .info-grid p {
            margin: 0;
        }
        .delete-btn {
            background-color: #8FC73E;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        .detail {
            border: #E0E2E7 1px solid;
            padding: 8px 12px;
            border-radius: 8px;
            background-color: #F9F9FC;
        }
    </style>
</head>
<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    
                    <div class="page-title">
                        <h5>Produit #<?php echo htmlspecialchars($product['id_produit']); ?></h5>
                    </div>
                    <button class="delete-btn">Supprimer</button>
                </div>
                <div class="product-container">
                    <div class="details-section">
                        <h5>Nom du produit</h5>
                        <p class="detail"><?php echo htmlspecialchars($product['lib_produit']); ?></p>

                        <h5>Description</h5>
                        <p class="detail"><?php echo nl2br(htmlspecialchars($product['desc_produit'])); ?></p>

                        <h5>Coordinated Garments</h5>
                        <p class="detail">Combine it with the matching jacket, top or T-shirt.</p>

                        <div class="info-grid">
                            <div>
                                <p><strong>Marque</strong></p>
                                <p class="detail"><?php echo htmlspecialchars($product['nom_marque'] ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p><strong>Catégorie</strong></p>
                                <p class="detail"><?php echo htmlspecialchars($product['libelle_categorie']); ?></p>
                            </div>
                            <div>
                                <p><strong>Sous Catégorie</strong></p>
                                <p class="detail">N/A</p>
                            </div>
                            <div>
                                <p><strong>Prix</strong></p>
                                <p class="detail"><?php echo number_format($product['prix_produit'], 2); ?> TND</p>
                            </div>
                            <div>
                                <p><strong>Pourcentage</strong></p>
                                <p class="detail"><?php echo $product['en_promo'] ? $product['taux_reduction'] . '%' : 'N/A'; ?></p>
                            </div>
                            <div>
                                <p><strong>Qté</strong></p>
                                <p class="detail"><?php echo array_sum(array_column($variants, 'stock_variant')); ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="image-section">
                        <p><strong> Images</strong></p>
                        <div class="image-gallery" id="image-gallery">
                            <?php
                            if (!empty($images_by_color[$default_color])) {
                                foreach ($images_by_color[$default_color] as $image) {
                                    echo '<img src="../'.htmlspecialchars($image).'" alt="Product Image" data-color="'.htmlspecialchars($default_color).'">';
                                }
                            }
                            ?>
                        </div>
                        
                        <div class="variant-options">
                            <div class="d-flex ">
                                <p class="px-2"><strong>Couleur</strong></p>
                                <?php foreach ($colors as $color): ?>
                                    <span class="color-circle <?php echo $color === $default_color ? 'selected' : ''; ?>" style="background-color: <?php echo htmlspecialchars($color); ?>;" data-color="<?php echo htmlspecialchars($color); ?>"></span>
                                <?php endforeach; ?>
                            </div>
                            <div class="d-flex  ">
                                <p  class="px-2"><strong>Taille</strong></p>
                                <?php
                                $sizes = array_unique(array_column($variants, 'taille'));
                                foreach ($sizes as $size):
                                ?>
                                <span class="variant-option"><?php echo htmlspecialchars($size); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

 
    <!--scripts-->
      <!-- Scripts -->
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    
    <script>
        $(document).ready(function() {
            // Store images by color
            const imagesByColor = <?php echo json_encode($images_by_color); ?>;

            // Color selection
            $('.color-circle').click(function() {
                const selectedColor = $(this).data('color');
                
                // Update selected color styling
                $('.color-circle').removeClass('selected');
                $(this).addClass('selected');

                // Update main image and gallery
                const images = imagesByColor[selectedColor] || [];
                if (images.length > 0) {
                    $('#main-image').attr('src', '../' + images[0]);
                    const gallery = $('#image-gallery');
                    gallery.empty();
                    images.forEach(img => {
                        gallery.append(`<img src="../${img}" alt="Product Image" data-color="${selectedColor}">`);
                    });
                } else {
                    $('#main-image').attr('src', '../assets/images/placeholder.jpg');
                    $('#image-gallery').empty();
                }
            });

            // Gallery image click to update main image
            $('#image-gallery').on('click', 'img', function() {
                $('#main-image').attr('src', $(this).attr('src'));
            });

            // Chevron scroll
            $('#scroll-left').click(function() {
                $('#image-gallery').scrollLeft($('#image-gallery').scrollLeft() - 100);
            });

            $('#scroll-right').click(function() {
                $('#image-gallery').scrollLeft($('#image-gallery').scrollLeft() + 100);
            });

            // Show/hide chevrons based on scroll position
            function updateChevrons() {
                const gallery = $('#image-gallery');
                const scrollLeft = gallery.scrollLeft();
                const maxScroll = gallery[0].scrollWidth - gallery[0].clientWidth;

                $('#scroll-left').toggle(scrollLeft > 0);
                $('#scroll-right').toggle(scrollLeft < maxScroll - 1);
            }

            $('#image-gallery').on('scroll', updateChevrons);
            updateChevrons(); // Initial check
        });
    </script>
</body>
</html>