<?php
header('Content-Type: application/json');
include 'db.php';

$term = isset($_GET['term']) ? $conn->real_escape_string($_GET['term']) : '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

$suggestions = [];

$productQuery = "SELECT p.id_produit, p.lib_produit, i.image_path 
                FROM Produits p
                LEFT JOIN images i ON p.id_produit = i.id_produit
                WHERE p.lib_produit LIKE '%$term%'
                GROUP BY p.id_produit
                LIMIT 5";
$productResult = $conn->query($productQuery);

if ($productResult && $productResult !== false) {
    while ($row = $productResult->fetch_assoc()) {
        $imageUrl = !empty($row['image_path']) ? $row['image_path'] : '';
        $suggestions[] = [
            'id' => $row['id_produit'],
            'type' => 'product',
            'label' => $row['lib_produit'],
            'image' => '../'.$imageUrl,
            'url' => 'produit.php?id=' . $row['id_produit']
        ];
    }
}

$brandQuery = "SELECT id_marque, nom_marque, cover_marque FROM Marque 
              WHERE nom_marque LIKE '%$term%' 
              LIMIT 3";
$brandResult = $conn->query($brandQuery);

if ($brandResult && $brandResult !== false) {
    while ($row = $brandResult->fetch_assoc()) {
        $logoUrl = !empty($row['cover_marque']) ? $row['cover_marque'] : '';
        $suggestions[] = [
            'id' => $row['id_marque'],
            'type' => 'brand',
            'label' => $row['nom_marque'],
            'image' => $logoUrl,
            'url' => 'marque.php?id=' . $row['id_marque']
        ];
    }
}


$categoryQuery = "SELECT id_categorie, libelle_categorie FROM Categorie 
                 WHERE libelle_categorie LIKE '%$term%' 
                 LIMIT 3";
$categoryResult = $conn->query($categoryQuery);

if ($categoryResult && $categoryResult !== false) {
    while ($row = $categoryResult->fetch_assoc()) {
        $suggestions[] = [
            'id' => $row['id_categorie'],
            'type' => 'category',
            'label' => $row['libelle_categorie'],
            'image' => null,
            'url' => 'produitheader.php?subcategory_id=' . $row['id_categorie']
        ];
    }
}

echo json_encode($suggestions);
?>