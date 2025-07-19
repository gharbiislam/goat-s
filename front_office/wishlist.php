<?php
// Start session at the VERY TOP of the file
session_start();

// Database connection
$host = "localhost";
$dbname = "goats";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    die();
}

// Initialize wishlist - check both session and cookie
if (!isset($_SESSION['wishlist'])) {
    if (isset($_COOKIE['wishlist'])) {
        // If cookie exists, use it to initialize session
        $_SESSION['wishlist'] = json_decode($_COOKIE['wishlist'], true);
    } else {
        $_SESSION['wishlist'] = [];
    }
}

// Get wishlist items
$wishlist = $_SESSION['wishlist'];

// Set cookie to persist wishlist for 3 months (90 days)
if (!empty($wishlist)) {
    setcookie('wishlist', json_encode($wishlist), time() + (90 * 24 * 60 * 60), '/');
} else {
    // If wishlist is empty, remove the cookie
    setcookie('wishlist', '', time() - 3600, '/');
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title class="fw-bold ">GOAT'S - Ma Wishlist</title> <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    ::-webkit-scrollbar-track {
    background: #f1f1f1
}

::-webkit-scrollbar-thumb {
    background: #8FC73F
}

::-webkit-scrollbar-thumb:hover {
    background: #555
}
        .card {
            border-radius: 12px;
            overflow: hidden;
            max-height: 515px;
            width: 305px;
            position: relative;
        }
        
        .card-img-top {
            height: 402px;
            width: 303px;
            object-fit: cover;
        }
        
        .card-body {
            padding: 0.8rem;
        }
        
        .card-text {
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .card-text.fw-bold {
            color: #333;
        }
        
        .wishlist-heart {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 10px;
        }
        
        .wishlist-heart i {
            background-color: #ffffff;
            border-radius: 50%;
            padding: 10px;
            color: #1E4494;
            font-size: 20px;
        }
        
        .empty-wishlist {
            text-align: center;
            padding: 50px 0;
        }
        
        .pack-type-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            background-color: #8FC73E;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        /* Grid layout for responsiveness */
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* 4 cards per row */
            gap: 25px; /* Space between cards */
        }

        /* Responsive adjustments */
        @media (max-width: 1200px) {
            .wishlist-grid {
                grid-template-columns: repeat(3, 1fr); /* 3 cards per row on medium screens */
            }
        }

        @media (max-width: 768px) {
            .wishlist-grid {
                grid-template-columns: repeat(2, 1fr); /* 2 cards per row on small screens */
            }
        }

        @media (max-width: 576px) {
            .wishlist-grid {
                grid-template-columns: 1fr; /* 1 card per row on very small screens */
            }
           
        }
        h2{
color: #8FC73E !important;        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    <div class="container mt-4">
        <h2 class="mb-4 fw-bold"> Wishlist</h2>
        
        <?php if (empty($wishlist)): ?>
            <div class="empty-wishlist">
                <i class="far fa-heart fa-5x text-muted mb-3"></i>
                <h3 class="text-muted">Votre wishlist est vide</h3>
                <p class="text-muted">Ajoutez des packs à votre wishlist en cliquant sur les cœurs</p>
                <a href="pack.php" class="btn btn-primary mt-3">Voir les packs</a>
            </div>
        <?php else: ?>
            <div class="wishlist-grid" id="wishlistContainer">
                <?php 
                // Fetch pack details for wishlist items
                if (!empty($wishlist)) {
                    // Convert all wishlist IDs to integers for safety
                    $wishlistIds = array_map('intval', $wishlist);
                    
                    // Create placeholders for the IN clause
                    $placeholders = implode(',', array_fill(0, count($wishlistIds), '?'));
                    
                    // Prepare the SQL query
                    $sql = "SELECT * FROM pack WHERE id_pack IN ($placeholders)";
                    $stmt = $pdo->prepare($sql);
                    
                    // Execute with the wishlist IDs as positional parameters
                    $stmt->execute(array_values($wishlistIds));
                    $wishlistPacks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    foreach ($wishlistPacks as $pack): ?>
                        <div class="pack-card" 
                             data-id="<?php echo $pack['id_pack']; ?>"
                             data-type="<?php echo htmlspecialchars($pack['type']); ?>" 
                             data-price="<?php echo $pack['total_pack']; ?>">
                            <a href="detail_pack.php?id=<?php echo $pack['id_pack']; ?>" class="text-decoration-none text-dark">
                                <div class="card">
                                    <span class="pack-type-badge">
                                        <?php echo htmlspecialchars($pack['type']); ?>
                                    </span>
                                    
                                    <button class="btn btn-sm wishlist-heart position-absolute top-0 end-0" 
                                            data-pack-id="<?php echo $pack['id_pack']; ?>"
                                            type="button">
                                        <i class="fas fa-heart"></i>
                                    </button>
                                    
                                    <img src="<?php echo '../assets/img/pack/'.htmlspecialchars($pack['cover_pack']); ?>" 
                                         class="card-img-top" 
                                         alt="<?php echo htmlspecialchars($pack['nom_pack']); ?>">
                                    
                                    <div class="card-body">
                                        <h6 class="card-title fw-bold text-truncate mb-1"><?php echo htmlspecialchars($pack['nom_pack']); ?></h6>
                                        <p class="card-text small text-muted text-truncate"><?php echo htmlspecialchars($pack['produit']); ?></p>
                                        <p class="fw-bold"><?php echo $pack['total_pack']; ?> TND</p>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach;
                } ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Wishlist heart click handler
        $(document).on('click', '.wishlist-heart', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const heart = $(this);
            const packId = heart.data('pack-id');
            const packCard = heart.closest('.pack-card');
            
            $.get('packs.php?toggle_wishlist=' + packId + '&ajax=1', function(response) {
                try {
                    // Parse the response if it's a string
                    const data = typeof response === 'string' ? JSON.parse(response) : response;
                    
                    if (data.success) {
                        if (!data.in_wishlist) {
                            // Remove from wishlist view with animation
                            packCard.fadeOut(300, function() {
                                $(this).remove();
                                
                                // Check if wishlist is now empty
                                if ($('#wishlistContainer').children().length === 0) {
                                    location.reload(); // Reload to show empty state
                                }
                            });
                        }
                        
                        // Update wishlist count in header
                        $('.wishlist-count').text(data.wishlist_count || 0);
                    }
                } catch (e) {
                    console.error("Error processing response:", e);
                }
            });
        });
    });
    </script>
    <footer class="mt-5">
        <?php include('footer.php'); ?>
    </footer>
</body>
</html>