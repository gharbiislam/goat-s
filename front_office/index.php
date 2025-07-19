<?php
include('db.php');
include('header.php');

function mapColorToCSS($color) {
    // Correction: Suppression des espaces avant la comparaison
    $color = trim($color);
    $colorMap = [
        'pink' => 'pink',
        'yellow' => 'yellow',
        'black' => 'black',
        'blue' => 'blue',
        'grey' => 'grey',
        'white' => 'white',
        'beige' => 'beige', // Correction: suppression de l'espace après "beige"
        'red' => 'red',
        'cornflowerblue'=> 'cornflowerblue',
        'olive'=> 'olive',
        'silver'=> 'silver',
        'whitesmoke'=> 'whitesmoke',
        'orange'=> 'orange',
        'lightpink'=> 'lightpink'
       
    ];
    return isset($colorMap[$color]) ? $colorMap[$color] : 'gray'; // verif de couleur si exist
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png" sizes="50px">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap' rel='stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
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
    background: #8ec63e
}

::-webkit-scrollbar-thumb:hover {
    background: #555
}

        :root {
            --primary: #1f4492;
            --success: #8ec63e;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        .section-title {
            color: var(--primary);
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .scroll-buttons {
            display: flex;
            gap: 10px;
        }

        .scroll-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #f5f5f5;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;

        }

        .scroll-btn:hover {
            background-color: var(--primary);
            color: white;
        }

        .scroll-btn i {
            font-size: 18px;
        }

        /* Product Cards */
        .promo-card {
            border: 1px solid #e0e0e0;
            width: 200px;
            max-height: 280px;
            margin-bottom: 1rem;
            flex: 0 0 auto;
            border-radius: 8px;
            overflow: hidden;
        }


        .promo-img {
            width: 199px;
            height: 200px;
            display: flex;
           
        }
        .promo-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            position: absolute;
            transition: opacity 0.3s ease;
        }
        .promo-img .primary-image {
            opacity: 1;
        }
        .promo-img .next-image {
            opacity: 0;
        }
        .promo-img:hover .primary-image {
            opacity: 0;
        }
        .promo-img:hover .next-image {
            opacity: 1;
        }
        /* Product Cards */
        .product-card {
            border: 1px solid #e0e0e0;
            width: 210px;
           height: 340px;
            margin-bottom: 1rem;
            flex: 0 0 auto;
            border-radius: 8px;
            overflow: hidden;
        }


     .product-img {
    width: 100%;
    height: 260px;
    position: relative;
    overflow: hidden;
}
.product-img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    transition: opacity 0.3s ease;
}
.product-img .primary-image {
    opacity: 1;
}
.product-img .next-image {
    opacity: 0;
}
.product-img:hover .primary-image {
    opacity: 0;
}
.product-img:hover .next-image {
    opacity: 1;
}

        /* Horizontal Scrolling Containers */
        .scroll-container {
            position: relative;
            width: 100%;
            overflow: hidden;
        }

        .promo-container {
            display: flex;
            gap: 50px;
            overflow-x: auto;
            padding-bottom: 20px;
            scroll-behavior: smooth;
            -ms-overflow-style: none;
            scrollbar-width: none;

        }

        .promo-container::-webkit-scrollbar
     {
            display: none;
        }

        /* Discount Badge */
        .discount-badge {
            width: 52px;
            height: 17.5px;
            font-size: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #8ec63e;
            color: #1f4492;
            z-index: 1;
        }

        /* Astuce Section */
        .astuce {
            background-color: #8ec63e;
            border-radius: 15px;
        }

        .ratio {
            width: 100%;
            height: 200px;
            border-radius: 15px;
            overflow: hidden;
        }

        


        /* Service Icons */
        .service-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: var(--primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }

        .service-icon i {
            font-size: 24px;
        }

        /* Utility Classes */
        .object-fit-cover {
            object-fit: cover;
        }

        .object-fit-contain {
            object-fit: contain;
        }

        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }

        .btn-success {
            background-color: var(--success);
            border-color: var(--success);
        }

        .text-primary {
            color: var(--primary) !important;
        }

        .bg-primary {
            background-color: var(--primary) !important;
        }

        .bg-success {
            background-color: var(--success) !important;
        }

        .btn-outline-success {
            color: var(--success);
            border-color: var(--success);
        }

        .btn-outline-success:hover {
            background-color: var(--success);
            color: white;
        }

        /* section packs */
        .pack {
            background-color: #8ec63e;
            display: flex;
            align-items: center;
            overflow: hidden;
        }

        .pack-title {
            font-weight: 700;
            color: #1f4492;
            margin-bottom: 20px;
        }

        .pack-image-wrapper {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pack img {
            max-width: 100%;
            height: auto;
            padding: 20px;
        }

        @media (min-width: 768px) {
            .pack img {
                width: 350px;
                padding: 30px;
            }
        }

        @media (max-width: 768px) {
            .pack img {
                width: 200px;
                padding-bottom: 30px;
            }
           
        }

        .service-title {
            color: #1f4492;
        }

        .pack-description {
            font-size: 16px;
            color: white;
            margin-bottom: 20px;
            max-width: 100%;
            line-height: 1.5;
        }

        @media (min-width: 768px) {
            .pack-description {
                font-size: 20px;
                max-width: 80%;
                margin-bottom: 30px;
            }
        }

        /* boutons */
        .btn_blue {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 160px;
            height: 48px;
            border-radius: 6px;
            background-color: #1f4492;
            color: white;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            padding: 0 16px;
            text-align: center;
        }

        .btn_blue:hover {
            background-color: #1f4492;
            color: white;
            transform: translateY(-2px);
        }

        /* couleur */
        .green_goats {
            color: #8ec63e;
        }
    
        .blue_goats {
            color: #1f4492;
        }

    

        /* Category Banners */
        .category-banner {
            width: 450px;
            height: 100%;
            min-height: 200px;
            border-radius: 8px;
            background-size: cover;
            background-position: center;
            padding: 20px;
            margin-bottom: 20px;
        }

        .nutrition-banner {
            background-image: url('../assets/img/cover/nutrition.png');
        }

        .accessoires-banner {
            background-image: url('../assets/img/cover/accessoire.png');
        }

        .vetement-banner {
            background-image: url('../assets/img/cover/vet.png');
        }

        .category-title {
            font-size: 1.5rem;
            font-weight: 1000;
            
          
        }

       

        /* meilleures ventes */
        .bestseller-section {
            padding: 0 15px;
            margin-top: 50px;
        }

        @media (min-width: 768px) {
            .bestseller-section {
                padding: 0 30px;
            }
            .pack-image-wrapper{
                max-width: 30px;
            }
        }

        @media (min-width: 992px) {
            .bestseller-section {
                padding: 0 50px;
            }
        }

        @media (min-width: 1200px) {
            .bestseller-section {
                padding: 0 110px;
            }
        }

        .bestseller-row {
            display: flex;
            flex-direction: column;
            margin-bottom: 30px;
        }

        @media (min-width: 768px) {
            .bestseller-row {
                flex-direction: row;
            }
        }

        .bestseller-products {
            display: flex;
            flex-wrap: nowrap;
            overflow-x: auto;
            gap: 60px;
            margin-bottom: 20px;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .bestseller-products::-webkit-scrollbar {
            display: none;
        }

        @media (min-width: 768px) {
            .bestseller-products {
                flex: 1;
                margin-bottom: 0;
                overflow-x: hidden;
                gap: 50px;
            }
        }
a{
    text-decoration: none;
}
        @media (max-width: 768px) {
            .bestseller-products {
                flex: 1;
                margin-bottom: 0;
                overflow-x: hidden;
                gap: 25px;
            }
        }

    

        .bestseller-img {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }

        /* Card Body */
        .card-body {
            line-height: 1.1;
            padding: 10px;
        }

        .card-text {
            font-weight: 600;
            margin-bottom: 5px;
        }

        /* Responsive espacement */
        .pack-section,
        .promo-section,
        .tip-section,
        .marque-section {
            padding: 0 15px;
        }

        @media (min-width: 768px) {

            .promo-section,
            .tip-section,
            .marque-section {
                padding: 0 30px;
            }
        }

        @media (min-width: 992px) {

            .promo-section,
            .tip-section,
            .marque-section {
                padding: 0 50px;
            }
        }

        @media (min-width: 1200px) {

            .promo-section{
                padding-left: 110px;
            }.pack-section,
            .tip-section,
            .marque-section {
                padding: 0 110px;
            }
        }

        /* disparaitre boutons lel ecran sghir */
        @media (max-width: 767.98px) {
            .scroll-buttons {
                display: none;
            }

            /*2 produits bestsellet f responsive mobile*/
            .bestseller-products .bestseller:nth-child(n+3) {
                display: none;
            }

            .promo-container {
                padding-right: 0;
            }
            .btn_blue{
                width: 80px;
                height: 32px;
            
                font-size: 12px;           

            }
        }
        .color-options {
    display: flex;
    gap: 8px;
    margin-top: 5px;
    margin-bottom: 5px;

}
.color-dot {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    cursor: pointer;
    border: 1px solid #ccc;
}
.color-dot.active {
    border: 2px solid #000;
}
        /* taille M nkhaliw ken 2 produits yodhhrou */
        @media (min-width: 768px) and (max-width: 991.98px) {
            .bestseller-products .bestseller:nth-child(n+3) {
                display: none;
            }
        }
        /* carousel marque */
.brands-carousel {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 20px 0;
}

.brands-container {
    display: flex;
    gap: 25px;
    animation: scroll 15S linear infinite; /* Adjust duration for speed */
    white-space: nowrap;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.brands-container:hover {
    animation-play-state: paused; /* pause animation on hover */
}

.brands-container::-webkit-scrollbar {
    display: none;
}

.brand-item {
    flex: 0 0 auto;
    border-radius: 16px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.brand-item img {
    width: 200px;
    height: 80px;
    border-radius: 16px;
    object-fit: contain;
}

@media (min-width: 768px) {
    .brand-item img {
        width: 250px;
        height: 100px;
    }
}

@media (min-width: 992px) {
    .brand-item img {
        width: 340px;
    }
}

/* animation scroll*/
@keyframes scroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%); /* déplacé vers la gauche de 50% de sa propre largeur */
    }
}.hero-banner {
    position: relative;
    text-align: left;
    color: white;
    width: 100%;
    overflow: hidden;
    min-height: 300px;
}

.hero-banner img {
    width: 100%;
    height: auto;
    min-height: 300px;
    object-fit: cover;
    display: block;
}

.banner-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: black;
    opacity: 0.35;
}

.banner-content {
    position: absolute;
    top: 50%;
    left: 5%;
    transform: translateY(-50%);
    max-width: 50%;
    z-index: 1;
    padding: 0 10px;
}

.banner-content h1 {
    font-size: clamp(1.5rem, 4vw, 2.5rem);
    font-weight: bold;
    margin-bottom: 22px;
    line-height: 1.2;
}

.banner-content p {
    font-size: clamp(0.9rem, 2.5vw, 1.2rem);
    margin-bottom: 22px;
    color: #fff;
    line-height: 1.4;
}

.btn {
    display: inline-block;
}

.btn-goats {
    background-color: #8FC73E !important;
    color: #334B11;
    padding: clamp(0.5rem, 2vw, 0.75rem) clamp(1rem, 3vw, 1.5rem);
    border-radius: 25px;
    text-decoration: none;
    font-weight: bold;
    transition: background-color 0.3s ease;
    border: none;
    font-size: clamp(0.8rem, 2vw, 1rem);
}

.btn-goats:hover {
    background-color: #7EB337;
    color: #334B11;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .banner-content {
        max-width: 60%;
        left: 5%;
    }
    
    .hero-banner img {
        min-height: 250px;
    }
}

@media (max-width: 768px) {
    .banner-content {
        max-width: 80%;
        left: 5%;
        top: 45%;
    }

    .banner-content h1 {
        margin-bottom: 15px;
    }

    .banner-content p {
        margin-bottom: 15px;
    }
    
    .hero-banner img {
        min-height: 200px;
    }
}

@media (max-width: 576px) {
    .banner-content {
        max-width: 90%;
        left: 5%;
        top: 40%;
    }

    .banner-content h1 {
        margin-bottom: 12px;
    }

    .banner-content p {
        margin-bottom: 12px;
    }
    
    .hero-banner {
        min-height: 250px;
    }
    
    .hero-banner img {
        min-height: 250px;
    }
}

@media (max-width: 480px) {
    .banner-content {
        max-width: 95%;
        left: 2.5%;
        top: 35%;
    }
    
    .hero-banner {
        min-height: 200px;
    }
    
    .hero-banner img {
        min-height: 200px;
    }
}
    </style>
</head>

<body>
    <main>
      <!-- Hero Banner -->
<!-- Hero Banner -->
<div class="hero-banner mt-3">
    <div class="banner-overlay"></div>
    <div class="banner-content">
        <h1>Un mode de vie.<br>Pas juste des produits.</h1>
        <p>GOAT'S, c'est le carburant de ta discipline, de ton style, de ta vibe.</p>
        <a href="categorie.php" class="btn btn-goats">Découvrir l'univers GOAT'S</a>    </div>
    <img src="../assets/img/cover/banner2.png" alt="GOAT'S Fitness" class="img-fluid w-100">
</div>

        <!-- Promo Section -->
        <section class="promo-section py-5">
            <div class="section-header">
                <h2 class="section-title mb-0">PROMO</h2>
                <div class="scroll-buttons">
                    <button class="scroll-btn scroll-left" aria-label="Scroll left">
                        <i class="bi bi-chevron-left"></i>
                    </button>
                    <button class="scroll-btn scroll-right" aria-label="Scroll right">
                        <i class="bi bi-chevron-right"></i>
                    </button>
                </div>
            </div>
            <?php
      // Modification de la requête pour récupérer le type de produit (alimentaire ou non)
      $query = "SELECT p.id_produit, p.lib_produit, p.prix_produit, p.en_promo, p.taux_reduction, p.id_marque, m.nom_marque, 
      GROUP_CONCAT(DISTINCT i.couleur) AS available_colors,
      GROUP_CONCAT(DISTINCT CONCAT(i.couleur, ':', i.image_path) SEPARATOR '|') AS color_images,
      CASE 
          WHEN EXISTS (SELECT 1 FROM alimentaire a WHERE a.id_produit = p.id_produit) THEN 'alimentaire'
          ELSE 'autre'
      END AS product_type,
      (SELECT MIN(prix_variant) FROM alimentaire WHERE id_produit = p.id_produit) AS min_prix_variant
      FROM produits p
      LEFT JOIN marque m ON p.id_marque = m.id_marque
      LEFT JOIN images i ON p.id_produit = i.id_produit AND i.ordre = 1
      WHERE p.en_promo = 1
      GROUP BY p.id_produit";
$result = mysqli_query($conn, $query);
       

            if (!$result) {
                echo "Erreur de requête : " . mysqli_error($conn);
            } else {
            ?>
     <div class="scroll-container">
    <div class="promo-container" id="promo-container">
        <?php while ($product = mysqli_fetch_assoc($result)) {
            $discountedPrice = $product['prix_produit'] * (1 - $product['taux_reduction'] / 100);
            
            // afficher prix variant lel aliment
            if ($product['product_type'] == 'alimentaire' && $product['prix_produit'] == 0 && !empty($product['min_prix_variant'])) {
                $discountedPrice = $product['min_prix_variant'] * (1 - $product['taux_reduction'] / 100);
                $product['prix_produit'] = $product['min_prix_variant'];
            }
            
            //separer les couelurs
            $colors = explode(',', $product['available_colors']);
            // kol couleur tekhou l img mteeha
            $color_images = [];
            foreach (explode('|', $product['color_images']) as $pair) {
                if ($pair) {
                    list($color, $image) = explode(':', $pair);
                    $color_images[$color] = $image;
                }
            }
            //definir couleur lowla w naffichiw l images mteha
            $default_color = $colors[0] ?? '';
            $primaryImage = '../' . ($color_images[$default_color] ?? 'assets/images/placeholder.jpg');
            // afficher 2eme image on hover
            $nextImageQuery = "SELECT image_path FROM images WHERE id_produit = {$product['id_produit']} AND couleur = '$default_color' AND ordre = 2 LIMIT 1";
            $nextImageResult = mysqli_query($conn, $nextImageQuery);
            $nextImage = '../' . (mysqli_num_rows($nextImageResult) > 0 ? mysqli_fetch_assoc($nextImageResult)['image_path'] : ($color_images[$default_color] ?? 'assets/images/placeholder.jpg'));
        ?>
            <div class="promo">
                <a href="produit.php?id=<?php echo $product['id_produit']; ?>" class="text-decoration-none">
                    <div class="card promo-card">
                        <div class="position-relative">
                            <?php if ($product['taux_reduction'] > 0) { ?>
                                <span class="discount-badge fw-bold">-<?php echo $product['taux_reduction']; ?>%</span>
                            <?php } ?>
                            <div class="promo-img">
                                <img class="primary-image" src="<?php echo $primaryImage; ?>" alt="<?php echo $product['lib_produit']; ?>">
                                <img class="next-image" src="<?php echo $nextImage; ?>" alt="<?php echo $product['lib_produit']; ?>">
                            </div>
                        </div>  </a>
                        <div class="card-body p-2">
                            <h5 class="card-title h6 mb-1"><?php echo $product['lib_produit']; ?></h5>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <?php if ($product['taux_reduction'] > 0) { ?>
                                    <small class="text-decoration-line-through"><?php echo number_format($product['prix_produit'], 2); ?> TND</small>
                                <?php } ?>
                                <strong class="green_goats"><?php echo number_format($discountedPrice, 2); ?> TND</strong>
                            </div>
                            <?php 
                            // afficher couleur si un produit alimentaire
                            if ($product['product_type'] != 'alimentaire' && !empty($colors[0])) { 
                            ?>
                            <div class="color-options">
                                <?php foreach ($colors as $color) {
                                    if ($color) { ?>
                                        <div class="color-dot <?php echo $color === $default_color ? 'active' : ''; ?>" 
                                             style="background-color: <?php echo mapColorToCSS($color); ?>" 
                                             data-color="<?php echo htmlspecialchars($color); ?>" 
                                             data-primary-image="<?php echo '../' . ($color_images[$color] ?? 'assets/images/placeholder.jpg'); ?>"
                                             data-next-image="<?php 
                                                $nextQuery = "SELECT image_path FROM images WHERE id_produit = {$product['id_produit']} AND couleur = '$color' AND ordre = 2 LIMIT 1";
                                                $nextResult = mysqli_query($conn, $nextQuery);
                                                echo '../' . (mysqli_num_rows($nextResult) > 0 ? mysqli_fetch_assoc($nextResult)['image_path'] : ($color_images[$color] ?? 'assets/images/placeholder.jpg'));
                                             ?>"></div>
                                <?php }
                                } ?>
                            </div>
                            <?php } ?>
                            <p class="card-text small service-title mb-0"><?php echo $product['nom_marque']; ?></p>
                        </div>
                    </div>
              
            </div>
        <?php } ?>
    </div>
</div>
            <?php
            }
            mysqli_free_result($result);
            ?>
        </section>

      <!-- Brands Section -->
<section class="pb-5">
    <div class="marque-section">
        <h2 class="section-title mb-0">MARQUES</h2>
    </div>
    <?php
    $query = "SELECT id_marque, nom_marque, cover_marque FROM marque ORDER BY id_marque";
    $result = mysqli_query($conn, $query);

    if (!$result) {
        echo "Erreur de requête : " . mysqli_error($conn);
    } else {
    ?>
        <div class="brands-carousel">
            <div class="brands-container">
                <?php while ($brand = mysqli_fetch_assoc($result)) {
                    $coverPath = $brand['cover_marque'] ? $brand['cover_marque'] : 'assets/images/placeholder-logo.jpg';
                    $coverPath = str_replace('', '', $coverPath);
                ?>
                    <div class="brand-item">
                        <a href="filtre.php?id=<?php echo $brand['id_marque']; ?>">
                            <img src="<?php echo $coverPath; ?>" alt="<?php echo $brand['nom_marque']; ?>" class="img-fluid">
                        </a>
                    </div>
                <?php } ?>
                <!-- Duplicate brand items for infinite scroll effect -->
                <?php
                mysqli_data_seek($result, 0); // Reset result pointer to start
                while ($brand = mysqli_fetch_assoc($result)) {
                    $coverPath = $brand['cover_marque'] ? $brand['cover_marque'] : 'assets/images/placeholder-logo.jpg';
                    $coverPath = str_replace('', '', $coverPath);
                ?>
                    <div class="brand-item">
                        <a href="filtre.php?id=<?php echo $brand['id_marque']; ?>">
                            <img src="<?php echo $coverPath; ?>" alt="<?php echo $brand['nom_marque']; ?>" class="img-fluid">
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php
    }
    mysqli_free_result($result);
    ?>
</section>

        <!-- Packs Section -->
        <section class="pack">
            <div class="pack-section py-4">
                <div class="row align-items-center">
                    <div class="col-md-7 pack-content">
                        <h1 class="pack-title">Nos packs</h1>
                        <p class="pack-description">
                            Découvrez nos packs complets, adaptés à tous les niveaux et objectifs : débutant, performance, économie... Tout ce dont vous avez besoin en un seul pack !
                        </p>
                        <a href="pack.php" class="btn_blue ">Découvrir</a>
                    </div>
                    <div class="col-md-5 pack-image-wrapper">
                        <img src="../assets/img/cover/pack.jpg" alt="Nos packs" class=" ">
                    </div>
                </div>
            </div>
        </section>

        <!-- section el bestsellet -->
        <section class="bestseller-section my-5">
            <h2 class="section-title mb-4">MEILLEURES VENTES</h2>

            <!-- vetements -->
            <div class="bestseller-row">
            <div class="bestseller-products">
    <?php $query_vetements = "SELECT 
                    p.id_produit, 
                    p.lib_produit, 
                    p.prix_produit, 
                    p.id_marque, 
                    m.nom_marque, 
                    GROUP_CONCAT(DISTINCT v.couleur) AS available_colors,
                    GROUP_CONCAT(DISTINCT CONCAT(i.couleur, ':', i.image_path) SEPARATOR '|') AS color_images,
                    SUM(lc.quantite) AS total_quantity_sold
                    FROM 
                    ligne_commande lc
                    JOIN 
                        produits p ON lc.id_produit = p.id_produit
                    JOIN 
                        vetement v ON p.id_produit = v.id_produit
                    JOIN
                        marque m ON p.id_marque = m.id_marque
                    LEFT JOIN images i ON p.id_produit = i.id_produit AND i.ordre = 1
                    GROUP BY 
                        p.id_produit
                    ORDER BY 
                        total_quantity_sold DESC
                    LIMIT 3";
    $result_vetements = mysqli_query($conn, $query_vetements);
    if (!$result_vetements) {
        echo "Erreur de requête (Vêtements) : " . mysqli_error($conn);
    } else {
        while ($product = mysqli_fetch_assoc($result_vetements)) {
            $colors = explode(',', $product['available_colors']);
            $color_images = [];
            foreach (explode('|', $product['color_images']) as $pair) {
                if ($pair) {
                    list($color, $image) = explode(':', $pair);
                    $color_images[$color] = $image;
                }
            }
            $default_color = $colors[0] ?? '';
            $primaryImage = '../' . ($color_images[$default_color] ?? 'assets/images/placeholder.jpg');
            $nextImageQuery = "SELECT image_path FROM images WHERE id_produit = {$product['id_produit']} AND couleur = '$default_color' AND ordre = 2 LIMIT 1";
            $nextImageResult = mysqli_query($conn, $nextImageQuery);
            $nextImage = '../' . (mysqli_num_rows($nextImageResult) > 0 ? mysqli_fetch_assoc($nextImageResult)['image_path'] : ($color_images[$default_color] ?? 'assets/images/placeholder.jpg'));
    ?>
            <div class="bestseller">
                <div class="card product-card">
                    <a href="produit.php?id=<?php echo $product['id_produit']; ?>" class="text-decoration-none">
                        <div class="position-relative">
                            <div class="product-img">
                                <img class="primary-image" src="<?php echo $primaryImage; ?>" alt="<?php echo $product['lib_produit']; ?>">
                                <img class="next-image" src="<?php echo $nextImage; ?>" alt="<?php echo $product['lib_produit']; ?>">
                            </div>
                        </div>
                        <div class="card-body p-2">
                            <h5 class="card-title h6 mb-1"><?php echo $product['lib_produit']; ?></h5>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="green_goats"><?php echo number_format($product['prix_produit'], 2); ?> TND</strong>
                            </div>  </a>
                            <div class="color-options">
                                <?php foreach ($colors as $color) {
                                    if ($color) { ?>
                                        <div class="color-dot <?php echo $color === $default_color ? 'active' : ''; ?>" 
                                             style="background-color: <?php echo mapColorToCSS($color); ?>" 
                                             data-color="<?php echo htmlspecialchars($color); ?>" 
                                             data-primary-image="<?php echo '../' . ($color_images[$color] ?? 'assets/images/placeholder.jpg'); ?>"
                                             data-next-image="<?php 
                                                $nextQuery = "SELECT image_path FROM images WHERE id_produit = {$product['id_produit']} AND couleur = '$color' AND ordre = 2 LIMIT 1";
                                                $nextResult = mysqli_query($conn, $nextQuery);
                                                echo '../' . (mysqli_num_rows($nextResult) > 0 ? mysqli_fetch_assoc($nextResult)['image_path'] : ($color_images[$color] ?? 'assets/images/placeholder.jpg'));
                                             ?>"></div>
                                <?php }
                                } ?>
                            </div>
                           
                        </div>
                  
                </div>
            </div>
    <?php
        }
        mysqli_free_result($result_vetements);
    }
    ?>
</div>
                <div class="bestseller-banner">
                    <div class="category-banner vetement-banner ">
                        <h1 class="category-title text-uppercase blue_goats ">Vêtements</h1>
                        <a href="categorie.php?id=2" class="category-link blue_goats">Voir Plus</a>
                    </div>
                </div>
            </div>

            <!-- nutrition -->
            <div class="bestseller-row">
          
          <div class="bestseller-products">
              <?php
              // récupérer prix_variant pour les produits alimentaires
              $query_nutrition = "SELECT 
                  p.id_produit,
                  p.lib_produit,
                  m.nom_marque AS brand_name,
                  p.prix_produit,
                  p.id_marque, 
                  m.nom_marque,
                  (SELECT image_path FROM images WHERE id_produit = p.id_produit ORDER BY ordre ASC LIMIT 1) as image_path,
                  MIN(a.prix_variant) as min_prix_variant,
                  SUM(lc.quantite) AS total_quantity_sold
              FROM 
                  ligne_commande lc
              JOIN 
                  produits p ON lc.id_produit = p.id_produit
              JOIN 
                  alimentaire a ON p.id_produit = a.id_produit
              JOIN
                  marque m ON p.id_marque = m.id_marque
              GROUP BY 
                  p.id_produit, p.lib_produit, m.nom_marque, p.prix_produit
              ORDER BY 
                  total_quantity_sold DESC
              LIMIT 3";
              $result_nutrition = mysqli_query($conn, $query_nutrition);

              if (!$result_nutrition) {
                  echo "Erreur de requête (Nutrition) : " . mysqli_error($conn);
              } else {
                  while ($product = mysqli_fetch_assoc($result_nutrition)) {
                      $imagePath = '../' . $product['image_path'] ? '../' . $product['image_path'] : '../assets/images/placeholder.jpg';
                      $imagePath = str_replace('../', '../', $imagePath);
                      
                      // Si le prix est 0, utiliser le prix_variant
                      $displayPrice = $product['prix_produit'];
                      if ($displayPrice == 0 && !empty($product['min_prix_variant'])) {
                          $displayPrice = $product['min_prix_variant'];
                      }
              ?>
                      <div class="bestseller">
                          <div class="card product-card"><a href="produit.php?id=<?php echo $product['id_produit']; ?>" class="text-decoration-none"  >
                              <div class="position-relative">
                                  <img src="<?php echo $imagePath; ?>" class="bestseller-img" alt="<?php echo $product['lib_produit']; ?>">
                              </div>
                              <div class="card-body p-2">
                                  <h5 class="card-title h6 mb-1"><?php echo $product['lib_produit']; ?></h5>
                                  <div class="d-flex justify-content-between align-items-center mb-1">
                                  <strong class="green_goats"><?php echo number_format($displayPrice, 2); ?> TND</strong>                                  </div>
                                  <p class="card-text small service-title mb-0"><?php echo $product['nom_marque']; ?></p>
                              </div></a>
                          </div>
                      </div>
              <?php
                  }
                  mysqli_free_result($result_nutrition);
              }
              ?>
          </div>
          <div class="bestseller-banner">
              <div class="category-banner nutrition-banner">
                  <h1 class="category-title text-uppercase blue_goats">Nutrition</h1>
                  <a href="categorie.php?id=1" class="category-link blue_goats ">Voir Plus</a>
              </div>
          </div>
      </div>

   
         <!-- accessoires -->
<div class="bestseller-row">
    <div class="bestseller-products">
        <?php
        $query_accessoires = "SELECT 
            p.id_produit, 
            p.lib_produit, 
            p.prix_produit, 
            p.id_marque, 
            m.nom_marque, 
            GROUP_CONCAT(DISTINCT a.couleur) AS available_colors,
            GROUP_CONCAT(DISTINCT CONCAT(i.couleur, ':', i.image_path) SEPARATOR '|') AS color_images,
            COALESCE(SUM(lc.quantite), 0) AS total_sales
            FROM 
            produits p
            LEFT JOIN 
            marque m ON p.id_marque = m.id_marque
            LEFT JOIN 
            ligne_commande lc ON p.id_produit = lc.id_produit
            LEFT JOIN 
            accessoire a ON p.id_produit = a.id_produit
            LEFT JOIN 
            images i ON p.id_produit = i.id_produit AND i.ordre = 1
            WHERE 
            p.id_categorie IN (SELECT id_categorie FROM categorie WHERE id_categorie = 3 OR id_categorie_parent = 3)
            GROUP BY 
            p.id_produit, p.lib_produit, p.prix_produit, p.id_marque, m.nom_marque
            ORDER BY 
            total_sales DESC 
            LIMIT 3";
        $result_accessoires = mysqli_query($conn, $query_accessoires);

        if (!$result_accessoires) {
            echo "Erreur de requête (Accessoires) : " . mysqli_error($conn);
        } else {
            if (mysqli_num_rows($result_accessoires) == 0) {
                echo '<p class="text-muted">Aucun produit disponible pour le moment.</p>';
            } else {
                while ($product = mysqli_fetch_assoc($result_accessoires)) {
                    $colors = explode(',', $product['available_colors']);
                    $color_images = [];
                    foreach (explode('|', $product['color_images']) as $pair) {
                        if ($pair) {
                            list($color, $image) = explode(':', $pair);
                            $color_images[$color] = $image;
                        }
                    }
                    $default_color = $colors[0] ?? '';
                    $primaryImage = '../' . ($color_images[$default_color] ?? 'assets/images/placeholder.jpg');
                    $nextImageQuery = "SELECT image_path FROM images WHERE id_produit = {$product['id_produit']} AND couleur = '$default_color' AND ordre = 2 LIMIT 1";
                    $nextImageResult = mysqli_query($conn, $nextImageQuery);
                    $nextImage = '../' . (mysqli_num_rows($nextImageResult) > 0 ? mysqli_fetch_assoc($nextImageResult)['image_path'] : ($color_images[$default_color] ?? 'assets/images/placeholder.jpg'));
        ?>
                    <div class="bestseller">
                        <div class="card product-card">
                            <a href="produit.php?id=<?php echo $product['id_produit']; ?>" class="text-decoration-none">
                                <div class="position-relative">
                                    <div class="product-img">
                                        <img class="primary-image" src="<?php echo $primaryImage; ?>" alt="<?php echo $product['lib_produit']; ?>">
                                        <img class="next-image" src="<?php echo $nextImage; ?>" alt="<?php echo $product['lib_produit']; ?>">
                                    </div>
                                </div>
                                <div class="card-body p-2">
                                    <h5 class="card-title h6 mb-1"><?php echo $product['lib_produit']; ?></h5>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <strong class="green_goats"><?php echo number_format($product['prix_produit'], 2); ?> TND</strong>
                                    </div>   </a>
                                    <div class="color-options">
                                        <?php foreach ($colors as $color) {
                                            if ($color) { ?>
                                                <div class="color-dot <?php echo $color === $default_color ? 'active' : ''; ?>" 
                                                     style="background-color: <?php echo mapColorToCSS($color); ?>" 
                                                     data-color="<?php echo htmlspecialchars($color); ?>" 
                                                     data-primary-image="<?php echo '../' . ($color_images[$color] ?? 'assets/images/placeholder.jpg'); ?>"
                                                     data-next-image="<?php 
                                                        $nextQuery = "SELECT image_path FROM images WHERE id_produit = {$product['id_produit']} AND couleur = '$color' AND ordre = 2 LIMIT 1";
                                                        $nextResult = mysqli_query($conn, $nextQuery);
                                                        echo '../' . (mysqli_num_rows($nextResult) > 0 ? mysqli_fetch_assoc($nextResult)['image_path'] : ($color_images[$color] ?? 'assets/images/placeholder.jpg'));
                                                     ?>"></div>
                                        <?php }
                                        } ?>
                                    </div>
                                    
                                </div>
                         
                        </div>
                    </div>
        <?php
                }
            }
            mysqli_free_result($result_accessoires);
        }
        ?>
    </div>
    <div class="bestseller-banner">
        <div class="category-banner accessoires-banner">
            <h1 class="category-title text-uppercase green_goat">Accessoires</h1>
            <a href="categorie.php?id=3" class="category-link green_goat">Voir Plus</a>
        </div>
    </div>
</div></section>

        <!-- astuce semaine -->
        <section class="tip-section py-5">
            <?php
            $query = "SELECT titre, descriptions, youtube_link FROM astuce_semaine ORDER BY titre DESC LIMIT 1";
            $result = mysqli_query($conn, $query);

            if (!$result) {
                echo "Erreur de requête : " . mysqli_error($conn);
            } else {
                $tip = mysqli_fetch_assoc($result);
                $title = $tip['titre'] ?? 'Entrainement Debutant';
                $description = $tip['descriptions'] ?? 'Un programme simple et progressif pour débuter sans pression, à la maison et sans matériel de salle.';
                $youtubeLink = $tip['youtube_link'] ?? '#';
            ?>
                <div class="astuce text-white p-4 rounded-3">
                    <div class="row g-0 align-items-center">
                        <div class="col-md-4 mb-3 mb-md-0">
                            <div class="ratio ratio-16x9 rounded-3 overflow-hidden">
                                <?php
                                $videoId = '';
                                if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\s*[^\/\n\s]+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $youtubeLink, $matches)) {
                                    $videoId = $matches[1];
                                }

                                if ($videoId) {
                                    echo '<iframe src="https://www.youtube.com/embed/' . $videoId . '?controls=0" title="YouTube video" allowfullscreen></iframe>';
                                } else {
                                    echo '<img src="assets/images/training-thumbnail.jpg" alt="Training" class="img-fluid h-100 object-fit-cover">';
                                }
                                ?>
                            </div>
                        </div>
                        <div class="col-md-8 px-lg-5">
                            <div class="ps-md-3">
                                <p class="my-2 fw-bold service-title">ASTUCE DE LA SEMAINE</p>
                                <h1 class="fw-bold mb-2"><?php echo $title; ?></h1>
                                <p class="pack-description mb-3"><?php echo $description; ?></p>
                                <a href="<?php echo $youtubeLink; ?>" class="btn_blue">Découvrir</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            }
            mysqli_free_result($result);
            ?>
        </section>
        <!-- services -->
        <section class="container py-5">
            <div class="row text-center g-4">
                <div class="col-6 col-md">
                    <div class="service-item">
                        <div class="service-icon mb-3">
                          <img src="../assets/img/icons/livraison.svg" alt="">
                        </div>
                        <h5 class="service-title small">CHOISISSEZ VOTRE DATE DE LIVRAISON</h5>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="service-item">
                        <div class="service-icon mb-3">
                           <img src="../assets/img/icons/card.svg" alt="">
                        </div>
                        <h5 class="service-title small">PAIEMENT EN LIGNE 100% SÉCURISÉ</h5>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="service-item">
                        <div class="service-icon mb-3">
                        <img src="../assets/img/icons/free.svg" alt="">
                        </div>
                        <h5 class="service-title small">LIVRAISON GRATUITE À PARTIR DE 300DT</h5>
                    </div>
                </div>

                <div class="col-6 col-md">
                    <div class="service-item">
                        <div class="service-icon mb-3">
                        <img src="../assets/img/icons/services.svg" alt="">
                        </div>
                        <h5 class="service-title small">SERVICE CLIENT 24/7</h5>
                    </div>
                </div>

                <div class="col-md">
                    <div class="service-item">
                        <div class="service-icon mb-3">
                        <img src="../assets/img/icons/verified-badge.svg" alt="">
                        </div>
                        <h5 class="service-title small">SATISFAIT OU REMBOURSÉ</h5>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- FOOTER -->
    <footer>
        <?php include('footer.php') ?>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Custom JS -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Brands carousel cloning for infinite scroll effect
            console.log("Initializing color dot event listeners...");
            document.querySelectorAll('.color-options').forEach(optionContainer => {
                const dots = optionContainer.querySelectorAll('.color-dot');
                // Find the parent card (promo-card or product-card) by traversing up
                const productCard = optionContainer.closest('.promo-card') || optionContainer.closest('.product-card');
                
                if (!productCard) {
                    console.error("No parent .promo-card or .product-card found for color-options:", optionContainer);
                    return;
                }

                const primaryImage = productCard.querySelector('.primary-image');
                const nextImage = productCard.querySelector('.next-image');

                if (!primaryImage || !nextImage) {
                    console.error("Missing .primary-image or .next-image in card:", productCard);
                    return;
                }

                dots.forEach(dot => {
                    dot.addEventListener('click', (e) => {
                       
                       //actvive l color selected et recuperer w affiche les image associées
                        e.stopPropagation();
                        e.preventDefault();
                        
                        console.log("Color dot clicked:", dot.dataset.color);
                        // remove active  selection m dots
                        dots.forEach(d => d.classList.remove('active'));
                        // Add active class to the clicked dot
                        dot.classList.add('active');
                        // Update primary and next images
                        const primarySrc = dot.getAttribute('data-primary-image');
                        const nextSrc = dot.getAttribute('data-next-image');
                        
                        console.log("Updating images - Primary:", primarySrc, "Next:", nextSrc);
                        
                        if (primarySrc && primaryImage) {
                            primaryImage.src = primarySrc;
                        } else {
                            console.warn("Invalid primary image source or element:", primarySrc, primaryImage);
                        }
                        if (nextSrc && nextImage) {
                            nextImage.src = nextSrc;
                        } else {
                            console.warn("Invalid next image source or element:", nextSrc, nextImage);
                        }
                    });
                });
            });
            
            const brandsContainer = document.querySelector(".brands-container");
            if (brandsContainer) {
                const brandItems = document.querySelectorAll(".brand-item");
                brandItems.forEach((item) => {
                    const clone = item.cloneNode(true);
                    brandsContainer.appendChild(clone);
                });
            }
            
            document.addEventListener('DOMContentLoaded', function() {
                const marqueCategory = document.querySelector('.category a[href="marque.php"]').parentElement;

                marqueCategory.addEventListener('mouseenter', function() {
                    document.body.style.overflow = 'hidden';
                });

                marqueCategory.addEventListener('mouseleave', function() {
                    document.body.style.overflow = '';
                });
            });
            
            // Navbar toggle functionality
            const navbarToggler = document.querySelector(".navbar-toggler");
            const navbarCollapse = document.querySelector(".navbar-collapse");
            if (navbarToggler && navbarCollapse) {
                navbarToggler.addEventListener("click", () => {
                    navbarCollapse.classList.toggle("show");
                });
            }

            // Promo section scroll buttons
            const scrollLeftBtn = document.querySelector('.scroll-left');
            const scrollRightBtn = document.querySelector('.scroll-right');
            const promoContainer = document.getElementById('promo-container');

            if (scrollLeftBtn && scrollRightBtn && promoContainer) {
                const scrollAmount = 300; // ajusté scroll bouton

                scrollLeftBtn.addEventListener('click', () => {
                    promoContainer.scrollBy({
                        left: -scrollAmount,
                        behavior: 'smooth'
                    });
                });

                scrollRightBtn.addEventListener('click', () => {
                    promoContainer.scrollBy({
                        left: scrollAmount,
                        behavior: 'smooth'
                    });
                });
            }
        });
    </script>
</body>

</html>
<?php
mysqli_close($conn);
?>