<?php
// Ce fichier affiche la section "Complète ton look" avec les produits associés
// Il doit être inclus dans produit.php après avoir inclus recuperation-produits-associes.php

// Vérification que les variables nécessaires sont disponibles
if (!isset($related_products) || !isset($category_type) || !isset($conn)) {
return;
}

// Affichage de la section uniquement s'il y a des produits associés
if (!empty($related_products)):
?>
<!-- Section Complète ton look -->
<h2 class="section-title">
<?php 
// Changement du titre selon le type de produit
if ($category_type === 'vetement') {
    echo 'COMPLÈTE TON LOOK';
} elseif ($category_type === 'alimentaire') {
    echo 'COMPLÈTE TON REPAS';
} else {
    echo 'PRODUITS SIMILAIRES';
}
?>
</h2>
<div class="related-products">
<?php foreach ($related_products as $related): ?>
<div class="product-card">
    <a href="produit.php?id=<?php echo $related['id_produit']; ?>">
        <?php
        // Récupération de l'image principale du produit associé
        $sql_rel_img = "SELECT image_path FROM images WHERE id_produit = ? ORDER BY ordre ASC LIMIT 1";
        $stmt_rel_img = $conn->prepare($sql_rel_img);
        $stmt_rel_img->bind_param("i", $related['id_produit']);
        $stmt_rel_img->execute();
        $rel_img_result = $stmt_rel_img->get_result();
        $rel_img = $rel_img_result->fetch_assoc();
        ?>
        <img src="<?php echo !empty($rel_img) ? htmlspecialchars($rel_img['image_path']) : 'images/placeholder.jpg'; ?>" 
             alt="<?php echo htmlspecialchars($related['lib_produit']); ?>">
        <h3><?php echo htmlspecialchars($related['lib_produit']); ?></h3>
        <div class="price">
            <?php
            // Calcul du prix final (avec réduction si en promotion)
            $rel_final_price = $related['prix_produit'];
            if ($related['en_promo'] && $related['taux_reduction'] > 0) {
                $rel_final_price = $related['prix_produit'] * (1 - $related['taux_reduction'] / 100);
            }
            echo number_format($rel_final_price, 2) . ' TND';
            ?>
        </div>
    </a>
    <div class="wishlist-icon" onclick="addToWishlist(<?php echo $related['id_produit']; ?>)">
        <i class="far fa-heart"></i>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>