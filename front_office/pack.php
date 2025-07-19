<?php
// Database connection
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
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize wishlist if not exists
if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = [];
}

// Handle wishlist toggle requests
if (isset($_GET['toggle_wishlist'])) {
    $packId = $_GET['toggle_wishlist'];

    if (in_array($packId, $_SESSION['wishlist'])) {
        // Remove from wishlist
        $_SESSION['wishlist'] = array_diff($_SESSION['wishlist'], [$packId]);
    } else {
        // Add to wishlist
        $_SESSION['wishlist'][] = $packId;
    }

    // Return JSON response for AJAX requests
    if (isset($_GET['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'in_wishlist' => in_array($packId, $_SESSION['wishlist'])
        ]);
        exit();
    }

    // For non-AJAX, redirect back
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}


// Define filter images and labels
$types_images = [
    'Prise de masse' => 'img/cover/prissedemasse.png',
    'Perte de poids' => 'img/cover/pertedepoids.png',
    'Bac sport' => 'img/cover/bacsport.png',
    'Fitness' => 'img/cover/fitness.png',
    'Débutant' => 'img/cover/débutant.png'
];

// Fetch all packs initially
$packs_query = $pdo->query("SELECT * FROM pack");
$packs = $packs_query->fetchAll(PDO::FETCH_ASSOC);

// Count packs by type
$pack_counts = [];
foreach ($types_images as $type => $image) {
    $pack_counts[$type] = count(array_filter($packs, function ($pack) use ($type) {
        return $pack['type'] == $type;
    }));
}

// Determine the current filter type from the query parameter
$type_filter = isset($_GET['type']) ? urldecode($_GET['type']) : 'Tous';
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Packs</title> <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
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
        /* Existing styles remain unchanged */
        .filter-links {
            display: flex;
            flex-wrap: wrap;
            gap: 30px;
            justify-content: center;
        }

        .filter-links a {
            text-align: center;
            text-decoration: none;
            color: #333;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .filter-links a img {
            width: 235px;
            height: 67px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
            transition: opacity 0.3s ease;
        }

        .wishlist-heart {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 10;
        color: #6B7280;
        background-color: white;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border: none;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        transition: all 0.2s ease;
    }

    .wishlist-heart:hover {
        transform: scale(1.1);
    }

    .wishlist-heart .fas {
        color: #1E4494;
    }
       /* .wishlist-count {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1E4494;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            z-index: 1000;
        }*/

        .filter-links a::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 67px;
            background-color: rgba(0, 0, 0, 0.4);
            border-radius: 5px;
            z-index: 1;
        }

        .filter-links a div {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            z-index: 2;
            width: 100%;
        }

        .filter-links a:hover img {
            opacity: 0.8;
        }

        .filter-links a.active img {
            border: 2px solid #007bff;
            opacity: 1;
        }

        .pack-card {
            display: flex;
            flex: row;
            justify-content: center;
            align-items: center;
        }

        .pack-card .card {
            width: 400px;
            height: 300px;
            border: 1px solid #D9D9D9;
            border-radius: 12px;
            overflow: hidden;
            position: relative;
            margin: 30px;
        }

        .pack-card .card-img-top {
            height: 200px;
            width: 100%;
            object-fit: contain;
            background-color: #D9D9D9;
        }

        .pack-card .card:hover .card-img-top {
            transform: scale(1.1);
        }

        .pack-card .card-body {
            padding: 10px;
        }

        .pack-card .card-titles {
            font-size: 16px;
            margin-bottom: 0.1rem;
        }

        .pack-card .card-text {
            font-size: 12px;
            margin-bottom: 0.1rem;
        }

        .pack-card .card-price {
            font-size: 16px;
            font-weight: bold;
        }

        .detail-pack-section {
            height: 80px;
            padding: 10px;
        }

        .cover-section {
            background-size: cover;
            background-position: center;
            height: 370px;
            width: 100%;
            position: relative;
            margin-bottom: 30px;
        }

        .cover-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .cover-section h2 {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
            font-size: 2.5rem;
            font-weight: bold;
            text-transform: uppercase;
            z-index: 1;
        }

        .filter-section {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
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

        .filter-option {
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .filter-option input {
            margin-right: 10px;
        }

        .price-inputs {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }

        .price-inputs input {
            width: 70px;
        }

        #packsContainer {
            display: flex;
            flex-wrap: wrap;
        }

        .main-content {
            margin-left: 90px;
            margin-right: 90px;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            max-width: 1430px;
            margin: 0 auto;
        }

        @media (max-width: 992px) {
            .pack-card {
                width: 50%;
            }

            .main-content {
                margin-left: 40px;
                margin-right: 40px;
            }
        }

        @media (max-width: 576px) {
            .pack-card {
                width: 100%;
            }

            .main-content {
                margin-left: 20px;
                margin-right: 20px;
            }
        }

        /* NEW CSS to prevent layout shift */
        footer {
            width: 100%;
            clear: both; /* Clear any floating elements from footer.php */
        }

        .main-content {
            box-sizing: border-box; /* Ensure padding/margins are included in width */
            margin-left: auto !important; /* Reinforce centering */
            margin-right: auto !important;
        }

        /* Ensure container does not inherit unwanted styles */
        .container {
            max-width: 1430px; /* Match main-content max-width */
            margin: 0 auto;
        }
    </style>
</head>

<body><?php include('header.php');?>

    <div class="main-content mt-4 row justify-content-center">
        <div class="row mt-3" id="filterImagesContainer">
            <div class="col-12">
                <div class="filter-links mb-4 d-flex flex-wrap">
                    <?php foreach ($types_images as $type => $image): ?>
                        <a href="#"
                            class="text-center <?php echo ($type_filter == $type) ? 'active' : ''; ?>"
                            data-type="<?php echo htmlspecialchars($type); ?>"
                            data-image="../assets/<?php echo htmlspecialchars($image); ?>">
                            <img src="../assets/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($type); ?>">
                            <div><?php echo htmlspecialchars($type); ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Cover Section (initially hidden) -->
        <div class="row">
            <div class="col-12">
                <div id="coverSection" class="cover-section mb-4" style="display: none;">
                    <h2 id="coverTitle"></h2>
                </div>
            </div>
        </div>

        <!-- Main Content with Sidebar and Products -->
        <div class="row">
            <!-- Left Filter Sidebar (Initially Hidden) -->
            <div class="col-md-3" id="filterSidebar" style="display: none;">
                <!-- Pack Type Filter -->
                <div class="filter-section mb-4">
                    <div class="filter-header" data-target="packFilterBody">
                        PACK
                        <i class="fa fa-chevron-up"></i>
                    </div>
                    <div class="filter-body show" id="packFilterBody">
                        <?php foreach ($types_images as $type => $image): ?>
                            <div class="filter-option d-flex justify-content-between">
                                <div>
                                    <input type="radio"
                                        id="filter-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $type))); ?>"
                                        name="pack-type"
                                        value="<?php echo htmlspecialchars($type); ?>"
                                        <?php echo ($type_filter == $type) ? 'checked' : ''; ?>>
                                    <label for="filter-<?php echo htmlspecialchars(strtolower(str_replace(' ', '-', $type))); ?>">
                                        <?php echo htmlspecialchars($type); ?>
                                    </label>
                                </div>
                                <div>
                                    <span class="pack-count">(<?php echo $pack_counts[$type]; ?>)</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Price Filter -->
                <div class="filter-section">
                    <div class="filter-header" data-target="priceFilterBody">
                        Prix
                        <i class="fa fa-chevron-up"></i>
                    </div>
                    <div class="filter-body show" id="priceFilterBody">
                        <input type="range" class="form-range" min="0" max="350" id="priceRange" value="350">
                        <div class="price-inputs">
                            <input type="number" id="minPrice" min="0" max="350" value="0" class="form-control">
                            <span>-</span>
                            <input type="number" id="maxPrice" min="0" max="350" value="350" class="form-control">
                            <span>TND</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content -->
            <div class="col-md-12" id="packsContentArea">
                <!-- Products Grid -->
                <div id="packsContainer">
                    <?php
                    $filtered_packs = $type_filter == 'Tous' ? $packs : array_filter($packs, function ($pack) use ($type_filter) {
                        return $pack['type'] == $type_filter;
                    });
                    foreach ($filtered_packs as $pack): ?>
                        <div class="pack-card"
                            data-id="<?php echo $pack['id_pack']; ?>"
                            data-type="<?php echo htmlspecialchars($pack['type']); ?>"
                            data-price="<?php echo $pack['total_pack']; ?>">
                            <a href="detail_pack.php?id=<?php echo $pack['id_pack']; ?>" class="text-decoration-none text-dark">
                                <div class="card">
                                    <!-- Wishlist heart button -->
                                    <button class="wishlist-heart"
                                        data-pack-id="<?php echo $pack['id_pack']; ?>"
                                        type="button">
                                        <i class="<?php echo in_array($pack['id_pack'], $_SESSION['wishlist']) ? 'fas' : 'far'; ?> fa-heart"></i>
                                    </button>

                                    <img src="<?php echo '../assets/img/pack/'.htmlspecialchars($pack['cover_pack']); ?>"
                                        class="card-img-top"
                                        alt="<?php echo htmlspecialchars($pack['nom_pack']); ?>">
                                    <div class="card-body">
                                        <p class="card-titles fw-bold"><?php echo htmlspecialchars($pack['nom_pack']); ?></p>
                                        <p class="card-text"><?php echo htmlspecialchars($pack['produit']); ?></p>
                                        <p class="card-price"><?php echo $pack['total_pack']; ?> TND</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Ensure footer is outside main-content to avoid nesting issues -->
    <?php include('footer.php'); ?>
    <!-- JavaScript -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            $(document).on('click', '.wishlist-heart', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const heart = $(this);
                const packId = heart.data('pack-id');

                $.get('?toggle_wishlist=' + packId + '&ajax=1', function(response) {
                    if (response.success) {
                        // Toggle heart icon
                        const icon = heart.find('i');
                        if (response.in_wishlist) {
                            icon.removeClass('far').addClass('fas');
                        } else {
                            icon.removeClass('fas').addClass('far');
                        }

                        // Update wishlist count
                        $('.wishlist-count').text(<?php echo count($_SESSION['wishlist']); ?>);

                        // Small animation
                        heart.css('transform', 'scale(1.3)');
                        setTimeout(() => {
                            heart.css('transform', 'scale(1)');
                        }, 300);
                    }
                });
            });

            // Initially adjust the main content width to full
            $('#packsContentArea').removeClass('col-md-9').addClass('col-md-12');

            // If a type is already filtered (via URL), trigger the click event
            const typeFilter = '<?php echo htmlspecialchars($type_filter); ?>';
            if (typeFilter !== 'Tous') {
                $(`.filter-links a[data-type="${typeFilter}"]`).click();
            }

            // Toggle filter sections with animation
            $('.filter-header').click(function() {
                const targetId = $(this).data('target');
                const filterBody = $('#' + targetId);
                const icon = $(this).find('i');

                filterBody.slideToggle(200, function() {
                    $(this).toggleClass('show');
                });

                icon.toggleClass('fa-chevron-up fa-chevron-down');
            });

            $('.filter-body').each(function() {
                if ($(this).hasClass('show')) {
                    $(this).show();
                }
            });

            $('#priceRange').on('input', function() {
                const price = $(this).val();
                $('#maxPrice').val(price);
                updatePriceFilter();
            });

            $('#minPrice, #maxPrice').on('input', function() {
                let min = parseInt($('#minPrice').val()) || 0;
                let max = parseInt($('#maxPrice').val()) || 350;

                if (min > max) {
                    if ($(this).attr('id') === 'minPrice') {
                        max = min;
                        $('#maxPrice').val(max);
                    } else {
                        min = max;
                        $('#minPrice').val(min);
                    }
                }

                $('#priceRange').val(max);
                updatePriceFilter();
            });

            $('.filter-links a').click(function(e) {
                e.preventDefault();
                $('.filter-links a').removeClass('active');
                $(this).addClass('active');

                const type = $(this).data('type');
                const coverImage = $(this).data('image');

                $('#filterImagesContainer').hide();
                $('#coverSection').css({
                    'background-image': `url(${coverImage})`,
                    'display': 'block'
                });
                $('#coverTitle').text(type);
                $('#filterSidebar').show();
                $('#packsContentArea').removeClass('col-md-12').addClass('col-md-9');
                $(`input[name="pack-type"][value="${type}"]`).prop('checked', true);
                updatePriceFilter();
            });

            $('input[name="pack-type"]').change(function() {
                const type = $(this).val();
                const coverImage = $(`.filter-links a[data-type="${type}"]`).data('image');
                $('#coverSection').css({
                    'background-image': `url(${coverImage})`,
                    'display': 'block'
                });
                $('#coverTitle').text(type);
                $('#filterImagesContainer').hide();
                $('.filter-links a').removeClass('active');
                $(`.filter-links a[data-type="${type}"]`).addClass('active');
                updatePriceFilter();
            });

            function updatePriceFilter() {
                const type = $('input[name="pack-type"]:checked').val() || $('.filter-links a.active').data('type') || '';
                const minPrice = parseInt($('#minPrice').val()) || 0;
                const maxPrice = parseInt($('#maxPrice').val()) || 350;

                filterPacks(type, minPrice, maxPrice);
            }

            function filterPacks(type, minPrice, maxPrice) {
                $('.pack-card').each(function() {
                    const packType = $(this).data('type');
                    const packPrice = parseFloat($(this).data('price'));

                    const typeMatch = !type || packType === type;
                    const priceMatch = packPrice >= minPrice && packPrice <= maxPrice;

                    if (typeMatch && priceMatch) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    </script>
</body>

</html>
