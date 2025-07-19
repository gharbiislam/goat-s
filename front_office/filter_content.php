<!-- Categories Filter -->
<h6 data-bs-toggle="collapse" data-bs-target="#categoryFilter">
    Catégorie <i class="fa fa-chevron-down"></i>
</h6>
<div id="categoryFilter" class="filter-content">
    <ul>
        <?php
        if ($categories_result && $categories_result->num_rows > 0) {
            $categories_result->data_seek(0);
            while ($cat = $categories_result->fetch_assoc()) {
                echo '<li>
                    <input type="checkbox" id="cat-' . $cat['id_categorie'] . '" class="category-filter" data-category="' . $cat['id_categorie'] . '"> 
                    <label for="cat-' . $cat['id_categorie'] . '">' . $cat['libelle_categorie'] . ' (' . $cat['product_count'] . ')</label>
                </li>';
            }
        } else {
            echo '<li>Aucune catégorie disponible</li>';
        }
        ?>
    </ul>
</div>

<!-- Price Filter -->
<h6 data-bs-toggle="collapse" data-bs-target="#priceFilter">
    Prix <i class="fa fa-chevron-down"></i>
</h6>
<div id="priceFilter" class="filter-content">
    <input type="range" min="<?php echo $min_price; ?>" max="<?php echo $max_price; ?>" value="<?php echo $max_price; ?>" class="form-range" id="priceRange">
    <div class="price-display">
        <span id="priceMin"><?php echo $min_price; ?></span>
        <span>-</span>
        <span id="priceMax"><?php echo $max_price; ?></span>
        <span>TND</span>
    </div>
</div>

<!-- Color Filter -->
<?php if ($has_colors): ?>
<h6 data-bs-toggle="collapse" data-bs-target="#colorFilter">
    Couleur <i class="fa fa-chevron-down"></i>
</h6>
<div id="colorFilter" class="filter-content">
    <ul>
        <?php
        $colors_result->data_seek(0);
        while ($color = $colors_result->fetch_assoc()) {
            if (!empty($color['color'])) {
                echo '<li>
                    <input type="checkbox" id="color-' . htmlspecialchars($color['color']) . '" class="color-filter" data-color="' . htmlspecialchars($color['color']) . '"> 
                    <label for="color-' . htmlspecialchars($color['color']) . '">' . htmlspecialchars($color['color']) . '</label>
                </li>';
            }
        }
        ?>
    </ul>
</div>
<?php endif; ?>

<!-- Size Filter -->
<?php if ($has_sizes): ?>
<h6 data-bs-toggle="collapse" data-bs-target="#sizeFilter">
    Taille <i class="fa fa-chevron-down"></i>
</h6>
<div id="sizeFilter" class="filter-content">
    <ul>
        <?php
        $sizes_result->data_seek(0);
        while ($size = $sizes_result->fetch_assoc()) {
            if (!empty($size['size'])) {
                echo '<li>
                    <input type="checkbox" id="size-' . htmlspecialchars($size['size']) . '" class="size-filter" data-size="' . htmlspecialchars($size['size']) . '"> 
                    <label for="size-' . htmlspecialchars($size['size']) . '">' . htmlspecialchars($size['size']) . '</label>
                </li>';
            }
        }
        ?>
    </ul>
</div>
<?php endif; ?>

<!-- Genre Filter (for clothing) -->
<?php if ($has_genres): ?>
<h6 data-bs-toggle="collapse" data-bs-target="#genreFilter">
    Genre <i class="fa fa-chevron-down"></i>
</h6>
<div id="genreFilter" class="filter-content">
    <ul>
        <?php
        $genres_result->data_seek(0);
        while ($genre = $genres_result->fetch_assoc()) {
            if (!empty($genre['genre'])) {
                echo '<li>
                    <input type="checkbox" id="genre-' . htmlspecialchars($genre['genre']) . '" class="genre-filter" data-genre="' . htmlspecialchars($genre['genre']) . '"> 
                    <label for="genre-' . htmlspecialchars($genre['genre']) . '">' . htmlspecialchars($genre['genre']) . '</label>
                </li>';
            }
        }
        ?>
    </ul>
</div>
<?php endif; ?>

<!-- Saveur Filter -->
<?php if ($has_saveurs): ?>
<h6 data-bs-toggle="collapse" data-bs-target="#saveurFilter">
    Saveur <i class="fa fa-chevron-down"></i>
</h6>
<div id="saveurFilter" class="filter-content">
    <ul>
        <?php
        $saveurs_result->data_seek(0);
        while ($saveur = $saveurs_result->fetch_assoc()) {
            if (!empty($saveur['saveur'])) {
                echo '<li>
                    <input type="checkbox" id="saveur-' . htmlspecialchars($saveur['saveur']) . '" class="saveur-filter" data-saveur="' . htmlspecialchars($saveur['saveur']) . '"> 
                    <label for="saveur-' . htmlspecialchars($saveur['saveur']) . '">' . htmlspecialchars($saveur['saveur']) . '</label>
                </li>';
            }
        }
        ?>
    </ul>
</div>
<?php endif; ?>

<!-- Format Filter -->
<?php if ($has_formats): ?>
<h6 data-bs-toggle="collapse" data-bs-target="#formatFilter">
    Format <i class="fa fa-chevron-down"></i>
</h6>
<div id="formatFilter" class="filter-content">
    <ul>
        <?php
        $formats_result->data_seek(0);
        while ($format = $formats_result->fetch_assoc()) {
            if (!empty($format['format'])) {
                echo '<li>
                    <input type="checkbox" id="format-' . htmlspecialchars($format['format']) . '" class="format-filter" data-format="' . htmlspecialchars($format['format']) . '"> 
                    <label for="format-' . htmlspecialchars($format['format']) . '">' . htmlspecialchars($format['format']) . '</label>
                </li>';
            }
        }
        ?>
    </ul>
</div>
<?php endif; ?>

<!-- Clear Filters -->
<div class="mt-4">
    <button id="clearFilters" class="btn btn-outline-secondary btn-sm w-100">Effacer les filtres</button>
</div>

<script>
document.getElementById('clearFilters')?.addEventListener('click', function() {
    // Clear all checkboxes
    document.querySelectorAll('.filter-section input[type="checkbox"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    
    // Reset price range
    const priceRange = document.getElementById('priceRange');
    if (priceRange) {
        priceRange.value = priceRange.max;
        document.getElementById('priceMax').textContent = priceRange.max;
    }
    
    // Trigger filter update
    filterProducts();
});
</script>