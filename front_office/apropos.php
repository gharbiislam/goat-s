<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('db.php');

$result = $conn->query("SELECT cover_marque FROM Marque");
$images = $result->fetch_all(MYSQLI_ASSOC);
$conn->close();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - À propos</title>
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            overflow-x: hidden;
        }

        .blue_goats {
            color: #1A4083;
        }

        .green_goats {
            color: #8FC73E;
        }

        /* Banner Section */
        .banner {
            position: relative;
            background-image: url('../assets/img/banner/About.png');
            background-size: cover;
            background-position: center;
            width: 100%;
            height: 385px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .banner::before {
            content: "";
            position: absolute;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.65);
        }

        .banner h1 {
            position: relative;
            z-index: 1;
            font-size: 3.5rem;
            line-height: 1.2;
        }

        /* About Section */
        .detail { 
            font-size: 24px;
            line-height: 1.6;
        }

        /* Features Section */
        .feature-box {
            text-align: center;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            background-color: white;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .feature-box:hover {
            transform: translateY(-5px);
        }

        .feature-box i {
            display: inline-block;
            background-color: #1E4494;
            color: white;
            padding: 12px 16px;
            border-radius: 6px;
            box-shadow: 0 4px 4px rgba(0, 0, 0, 0.3);
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        /* Partners Carousel */
        .carousel-container {
            width: 100%;
            overflow: hidden;
            background: #f8f9fa;
            padding: 20px 0;
            position: relative;
        }

        .carousel-track {
            display: flex;
            gap: 20px;
            white-space: nowrap;
            width: max-content;
            animation: scroll 15s linear infinite;
        }

        .carousel-img {
            width: 290px;
            height: 75px;
            object-fit: cover;
            flex-shrink: 0;
            border-radius: 16px;
        }

        @keyframes scroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }

        /* Partner CTA Section */
        .partenaire {
            position: relative;
            background: url('../assets/img/banner/about2.png') no-repeat center;
            background-size: cover;
            width: 100%;
            height: auto;
            min-height: 260px;
            margin-top: 100px;
            margin-bottom: 50px;
            padding: 40px;
            border-radius: 8px;
            overflow: hidden;
        }
     /* Brands Carousel */
     .brands-carousel {
    width: 100%;
    overflow: hidden;
    position: relative;
    padding: 20px 0;
}

.brands-container {
    display: flex;
    gap: 15px;
    animation: scroll 12S linear infinite; /* Adjust duration for speed */
    white-space: nowrap;
    -ms-overflow-style: none;
    scrollbar-width: none;
}

.brands-container:hover {
    animation-play-state: paused; /* Pause animation on hover */
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
    width: 100%;
    height: 50px;
    border-radius: 16px;
    object-fit: contain;
}

@media (min-width: 768px) {
    .brand-item img {
        width:100%;
        height: 70px;
    }
}

@media (min-width: 992px) {
    .brand-item img {
        width: 100%;
        height: 80px;
    }
}

/* Animation for infinite scroll */
@keyframes scroll {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%); /* Move half the container width for seamless loop */
    }
}
        .partenaire::before {
            content: "";
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
        }

        .partenaire h3, .partenaire p, .partenaire button, .partenaire a {
            position: relative;
            z-index: 2;
        }
        .partenaire {
          font-weight: 500;
     
        }
.partenaire h3{
    line-height:1.8;
}
        .btn_green {
            width: 210px;
            height: 48px;
            background-color: #8FC73E;
            border-radius: 6px;
            border: none;
            color: white;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }

        .btn_green:hover {
            background-color: #7AB32E;
        }

        .btn_green a {
            color: white;
            text-decoration: none;
            display: block;
            width: 100%;
            height: 100%;
            line-height: 48px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
            border-radius: 50px !important;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #8FC73F;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive Styles */
        @media (max-width: 992px) {
            .banner h1 {
                font-size: 2.8rem; margin-top: 20px;
            }

            .detail {
                font-size: 20px;
            }

            .partenaire {
                margin-top: 70px;
            }
          
        }

        @media (max-width: 768px) {
            .banner {
                height: 300px; margin-top: 20px;
            }

            .banner h1 {
                font-size: 2.2rem;
            }

            .detail {
                font-size: 18px;
            }

            .feature-box {
                padding: 20px;
            }

            .partenaire {
                padding: 30px;
            }  
        }

        @media (max-width: 576px) {
            .banner {
                height: 200px;
            }

            .banner h1 {
                font-size: 1.8rem;
            }

            .detail {
                font-size: 16px;
            }

            .carousel-img {
                width: 150px;
                height: 50px;
            }

            .feature-box i {
                font-size: 1.2rem;
                padding: 8px 10px;
            }

            .partenaire {
                margin-top: 50px;
                padding: 20px;
            }
            .partenaire h3 {
    font-size: 1.5rem;
 
}


            .btn_green {
                width: 160px;
                height: 40px;
                font-size: small;
            }

            .btn_green a {
                line-height: 40px;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'?>

    <!-- Banner Section -->
    <div class="banner">
        <h1 class="text-uppercase text-center text-white fw-bolder">GOAT'S <br> EN QUELQUES MOTS</h1>
    </div>

    <!-- About Section -->
    <div class="container mt-5 mb-5">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-6 col-12 mb-4 mb-md-0">
                <h2 class="blue_goats fw-bold mb-3">À propos de GOAT'S</h2>
                <p class="detail text-black">GOAT'S est la première marketplace en Tunisie dédiée aux passionnés de sport et de vie saine. Que tu sois un bodybuilder, un athlète, un adepte du fitness ou simplement quelqu'un qui veut mener un mode de vie plus healthy.</p>
            </div> 
            <div class="col-lg-6 col-md-6 col-12 text-center">
                <img src="../assets/img/content/about.png" alt="About GOAT'S" class="img-fluid rounded">
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container my-5 py-3">
        <h2 class="green_goats text-center mb-4 fw-bold">Pourquoi choisir GOAT'S ?</h2>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="feature-box">
                    <i class="bi bi-check2"></i>
                    <p class="blue_goats mb-0">Un large choix de produits adaptés à tous les niveaux</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="feature-box">
                    <i class="bi bi-award"></i>
                    <p class="blue_goats mb-0">Des marques de confiance, sélectionnées pour leur qualité</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="feature-box">
                    <i class="bi bi-cart-check"></i>
                    <p class="blue_goats mb-0">Une expérience d'achat simple, rapide et sécurisée</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="feature-box">
                    <i class="bi bi-lightbulb"></i>
                    <p class="blue_goats mb-0">Des conseils et astuces pour t'aider à atteindre tes objectifs</p>
                </div>
            </div>
        </div>
    </div>

    <section class="pb-4">
    <h2 class="text-center mb-4 fw-bold blue_goats">Nous collaborons avec</h2>
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

    <!-- Partner CTA Section -->
    <div class="container">
        <div class="partenaire">
            <div class="row align-items-center">
                <div class="col-lg-6 col-md-6 col-12 mb-4 mb-md-0">
                    <h3 class="text-uppercase fw-bold green_goats px-md-2 ">GOAT'S EST LA MARKETPLACE IDÉALE POUR VOUS !</h3>
                </div>
                <div class="col-lg-6 col-md-6 col-12">
                    <p class="text-white mb-3 ">Vous êtes une boutique, une marque ou un fournisseur dans le domaine du sport, de la nutrition ou du lifestyle healthy ?</p>
                    <button class="btn_green">
                        <a href="demande.php">Devenir Partenaire</a>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'footer.php'?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const marqueCategory = document.querySelector('.category a[href="marque.php"]').parentElement;

    marqueCategory.addEventListener('mouseenter', function() {
        document.body.style.overflow = 'hidden';
    });

    marqueCategory.addEventListener('mouseleave', function() {
        document.body.style.overflow = '';
    });
});
      
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>