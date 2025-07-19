<?php
// search_results.php - Page to display search results
include 'db.php';

// Get the search query from URL
$query = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

// Prepare queries for products, brands, and categories
$productQuery = "SELECT p.*, m.nom_marque, i.image_path 
                FROM Produits p 
                LEFT JOIN Marque m ON p.id_marque = m.id_marque 
                LEFT JOIN images i ON p.id_produit = i.id_produit 
                WHERE p.lib_produit LIKE '%$query%' 
                   OR m.nom_marque LIKE '%$query%'
                GROUP BY p.id_produit";

$categoryProductsQuery = "SELECT p.*, m.nom_marque, i.image_path 
                         FROM Produits p 
                         LEFT JOIN Marque m ON p.id_marque = m.id_marque 
                         LEFT JOIN images i ON p.id_produit = i.id_produit 
                         LEFT JOIN Categorie c ON p.id_categorie = c.id_categorie 
                         WHERE c.libelle_categorie LIKE '%$query%'
                         GROUP BY p.id_produit";

// Execute queries and check for errors
$productResults = $conn->query($productQuery);
if ($productResults === false) {
    echo "Error in product query: " . $conn->error;
    $productResults = []; // Set empty array to avoid further errors
} 

$categoryResults = $conn->query($categoryProductsQuery);
if ($categoryResults === false) {
    echo "Error in category query: " . $conn->error;
    $categoryResults = []; // Set empty array to avoid further errors
}

// Combine results (avoiding duplicates)
$products = [];
$productIds = [];

// Products directly matching
if ($productResults && $productResults !== false) {
    while ($row = $productResults->fetch_assoc()) {
        if (!in_array($row['id_produit'], $productIds)) {
            $products[] = $row;
            $productIds[] = $row['id_produit'];
        }
    }
}

// Products from matching categories
if ($categoryResults && $categoryResults !== false) {
    while ($row = $categoryResults->fetch_assoc()) {
        if (!in_array($row['id_produit'], $productIds)) {
            $products[] = $row;
            $productIds[] = $row['id_produit'];
        }
    }
}

include 'header.php'; // Include your site header
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results: <?php echo htmlspecialchars($query); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container my-5">
        <h2>Search Results for "<?php echo htmlspecialchars($query); ?>"</h2>
        
        <?php if (count($products) > 0): ?>
            <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 mt-3">
                <?php foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card h-100 product-card">
                            <?php if (isset($product['image_path']) && !empty($product['image_path'])): ?>
                                <img src="<?php echo '../'.htmlspecialchars($product['image_path']); ?>" 
                                     class="card-img-top" 
                                     alt="<?php echo htmlspecialchars($product['lib_produit']); ?>">
                            <?php else: ?>
                                <div class="text-center pt-3">
                                    <i class="fa fa-image fa-3x text-muted"></i>
                                </div>
                            <?php endif; ?>
                            <div class="card-body">
                                <?php if (isset($product['nom_marque']) && !empty($product['nom_marque'])): ?>
                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($product['nom_marque']); ?></p>
                                <?php endif; ?>
                                <h5 class="card-title"><?php echo htmlspecialchars($product['lib_produit']); ?></h5>
                                <p class="card-text fw-bold text-primary">
                                    <?php echo number_format($product['prix_produit'], 2); ?>TND
                                </p>
                                <a href="produit.php?id=<?php echo $product['id_produit']; ?>" 
                                   class="btn btn-primary">View Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                No products found matching "<?php echo htmlspecialchars($query); ?>".
            </div>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; // Include your site footer ?>
</body>
</html>