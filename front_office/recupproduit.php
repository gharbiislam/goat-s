<?php
// Ce fichier récupère les produits associés de la même catégorie principale mais de sous-catégories différentes
// Il doit être inclus dans produit.php avant d'afficher la section "Complète ton look"

// Initialisation du tableau des produits associés
$related_products = [];
$category_type = isset($category_type) ? $category_type : "unknown";

// Vérification que l'ID du produit est disponible
if (!isset($product_id) || empty($product_id)) {
// Si l'ID du produit n'est pas disponible, on ne fait rien
return;
}

// Vérification que la connexion à la base de données est disponible
if (!isset($conn) || !$conn) {
// Si la connexion n'est pas disponible, on ne fait rien
return;
}

// Récupération des produits associés
if (!empty($product['id_categorie'])) {
// Récupération de l'ID de la catégorie parente
$parent_category_id = 0;
$sql_cat = "SELECT id_categorie_parent FROM categorie WHERE id_categorie = ?";
$stmt_cat = $conn->prepare($sql_cat);
if ($stmt_cat) {
    $stmt_cat->bind_param("i", $product['id_categorie']);
    $stmt_cat->execute();
    $cat_result = $stmt_cat->get_result();
    if ($cat_result->num_rows > 0) {
        $cat_data = $cat_result->fetch_assoc();
        $parent_category_id = $cat_data['id_categorie_parent'];
    }
    $stmt_cat->close();
}

if ($parent_category_id > 0) {
    // Récupération de toutes les sous-catégories de la même catégorie parente
    $sql_subcats = "SELECT id_categorie FROM categorie 
                    WHERE id_categorie_parent = ? AND id_categorie != ?";
    $stmt_subcats = $conn->prepare($sql_subcats);
    if ($stmt_subcats) {
        $stmt_subcats->bind_param("ii", $parent_category_id, $product['id_categorie']);
        $stmt_subcats->execute();
        $subcats_result = $stmt_subcats->get_result();
        
        $subcategory_ids = [];
        while ($row = $subcats_result->fetch_assoc()) {
            $subcategory_ids[] = $row['id_categorie'];
        }
        $stmt_subcats->close();
        
        if (!empty($subcategory_ids)) {
            // Création des placeholders pour la requête IN
            $placeholders = str_repeat('?,', count($subcategory_ids) - 1) . '?';
            
            // Récupération des produits des sous-catégories différentes
            $sql_related = "SELECT p.*, m.nom_marque 
                           FROM produits p 
                           LEFT JOIN marque m ON p.id_marque = m.id_marque 
                           WHERE p.id_categorie IN ($placeholders) 
                           AND p.id_produit != ? 
                           AND p.quantite_stock > 0 
                           ORDER BY RAND() 
                           LIMIT 3";
            
            $stmt_related = $conn->prepare($sql_related);
            if ($stmt_related) {
                $types = str_repeat('i', count($subcategory_ids)) . 'i';
                $params = array_merge($subcategory_ids, [$product_id]);
                $stmt_related->bind_param($types, ...$params);
                $stmt_related->execute();
                $related_result = $stmt_related->get_result();
                
                while ($row = $related_result->fetch_assoc()) {
                    $related_products[] = $row;
                }
                $stmt_related->close();
            }
        }
    }
}
}

// Détermination du type de catégorie si ce n'est pas déjà fait
if ($category_type === "unknown" && !empty($product['id_categorie'])) {
// Vérification si le produit est un vêtement
$sql_check_type = "SELECT c.libelle_categorie 
                  FROM categorie c 
                  JOIN categorie cp ON c.id_categorie_parent = cp.id_categorie 
                  WHERE c.id_categorie = ?";
$stmt_check_type = $conn->prepare($sql_check_type);
if ($stmt_check_type) {
    $stmt_check_type->bind_param("i", $product['id_categorie']);
    $stmt_check_type->execute();
    $check_type_result = $stmt_check_type->get_result();
    if ($check_type_result->num_rows > 0) {
        $check_type_data = $check_type_result->fetch_assoc();
        $parent_category = strtolower($check_type_data['libelle_categorie']);
        
        if (strpos($parent_category, 'vêtement') !== false || 
            strpos($parent_category, 'vetement') !== false || 
            strpos($parent_category, 'habit') !== false) {
            $category_type = "vetement";
        } elseif (strpos($parent_category, 'alimentaire') !== false || 
                 strpos($parent_category, 'nourriture') !== false || 
                 strpos($parent_category, 'repas') !== false) {
            $category_type = "alimentaire";
        } else {
            $category_type = "accessoire";
        }
    }
    $stmt_check_type->close();
}
}
?>