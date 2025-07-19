<?php
header('Content-Type: application/json');
include 'db.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$sql = "SELECT id_produit as id, nom_produit as name, marque as brand, image_produit as image 
        FROM Produits 
        WHERE nom_produit LIKE ? OR marque LIKE ? 
        LIMIT 10";
$stmt = $conn->prepare($sql);
$searchTerm = "%$query%";
$stmt->bind_param('ss', $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = [
        'id' => $row['id'],
        'name' => htmlspecialchars($row['name']),
        'brand' => htmlspecialchars($row['brand']),
        'image' => $row['image'] ?? '../assets/img/default-product.jpg'
    ];
}

echo json_encode($products);

$stmt->close();
$conn->close();
?>