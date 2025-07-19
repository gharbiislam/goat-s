<?php
include('db.php');
include('navbar.php');

// Fetch categories and brands
$categorie = mysqli_query($conn, "SELECT * FROM categorie");
$brand = mysqli_query($conn, "SELECT * FROM marque");
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Ajouter un produit</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <style>
        .btn-add-variant {
            margin-top: 10px;
        }
        .input-group {
            display: flex;
        }
        .input-group .size-type-select {
            flex: 0 0 120px;
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }
        .input-group .size-select {
            flex: 1;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-left: 0;
        }
        .variant-row {
            background-color: #f8f9fa;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
        }
        .gender-radio {
            display: inline-block;
            margin-right: 15px;
        }
        .gender-radio input[type="radio"] {
            margin-right: 5px;
        }
        .gender-container {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        .btn-remove-variant {
            background-color: #dc3545;
            color: white;
            border: none;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: 10px;
        }
        .color-images {
            border-top: 1px solid #ddd;
            padding-top: 10px;
            margin-top: 10px;
        }
        .color-images-label {
            font-weight: bold;
            margin-bottom: 5px;
        }
        #taux_reduction_group {
            display: none;
        }
    </style>
</head>

<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Ajouter un produit</h4>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <form action="addproduct.php" method="POST" enctype="multipart/form-data" id="Form">
                        <div class="row">
                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Nom du produit *</label>
                                    <input type="text" name="product_name" class="form-control" required>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Type *</label>
                                    <select name="type" id="type-select" class="form-select" required>
                                        <option value="" disabled selected>Choisir le type</option>
                                        <option value="vetement">Vêtement</option>
                                        <option value="accessoire">Accessoire</option>
                                        <option value="alimentaire">Alimentaire</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Catégorie *</label>
                                    <select name="Category" class="form-select" required>
                                        <?php while ($row = mysqli_fetch_assoc($categorie)): ?>
                                            <option value="<?= htmlspecialchars($row['libelle_categorie']); ?>">
                                                <?= htmlspecialchars($row['libelle_categorie']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Description *</label>
                                    <textarea name="description" class="form-control" required></textarea>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Marque *</label>
                                    <select name="brand" class="form-select" required>
                                        <?php 
                                        // Reset pointer for brands
                                        mysqli_data_seek($brand, 0);
                                        while ($row = mysqli_fetch_assoc($brand)): ?>
                                            <option value="<?= htmlspecialchars($row['nom_marque']); ?>">
                                                <?= htmlspecialchars($row['nom_marque']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>Prix (DT) *</label>
                                    <input type="number" name="price" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12">
                                <div class="form-group">
                                    <label>En promotion</label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="en_promo" name="en_promo" value="1">
                                        <label class="form-check-label" for="en_promo">Produit en promotion</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4 col-sm-6 col-12" id="taux_reduction_group">
                                <div class="form-group">
                                    <label>Taux de réduction (%)</label>
                                    <input type="number" name="taux_reduction" class="form-control" min="0" max="100" value="0">
                                </div>
                            </div>

                            <!-- Clothing specific fields -->
                            <div class="col-lg-12" id="vetement-fields" style="display: none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Détails Vêtement</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-lg-12 mb-3">
                                                <label>Genre *</label>
                                                <div class="gender-container">
                                                    <div class="gender-radio">
                                                        <input type="radio" id="homme" name="genre" value="homme" required>
                                                        <label for="homme" class="form-check-label">Homme</label>
                                                    </div>
                                                    <div class="gender-radio">
                                                        <input type="radio" id="femme" name="genre" value="femme">
                                                        <label for="femme" class="form-check-label">Femme</label>
                                                    </div>
                                                    <div class="gender-radio">
                                                        <input type="radio" id="unisexe" name="genre" value="unisexe">
                                                        <label for="unisexe" class="form-check-label">Unisexe</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div id="vetement-variants">
                                            <div class="variant-row">
                                                <div class="row">
                                                    <div class="col-lg-4 col-sm-4 col-12">
                                                        <div class="form-group">
                                                            <label>Taille *</label>
                                                            <div class="input-group">
                                                                <select class="form-select size-type-select" onchange="toggleSizeType(this)">
                                                                    <option value="standard">Vêtement (XS-XXL)</option>
                                                                    <option value="shoe">Chaussures (36-45)</option>
                                                                </select>
                                                                <select class="form-select size-select" name="taille_vetement[]" required>
                                                                    <option value="">Sélectionner</option>
                                                                    <option value="XS">XS</option>
                                                                    <option value="S">S</option>
                                                                    <option value="M">M</option>
                                                                    <option value="L">L</option>
                                                                    <option value="XL">XL</option>
                                                                    <option value="XXL">XXL</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 col-sm-4 col-12">
                                                        <div class="form-group">
                                                            <label>Couleur *</label>
                                                            <input type="text" name="couleur_vetement[]" class="form-control color-input" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-sm-3 col-12">
                                                        <div class="form-group">
                                                            <label>Quantité *</label>
                                                            <input type="number" name="quantity_vetement[]" class="form-control" min="0" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-1 col-sm-1 col-12 d-flex align-items-end justify-content-center mb-3">
                                                        <button type="button" class="btn-remove-variant" onclick="removeVariant(this)">×</button>
                                                    </div>
                                                    <div class="col-lg-12 color-images">
                                                        <div class="color-images-label">Images pour cette couleur:</div>
                                                        <input type="file" name="color_images_vetement_0[]" class="form-control" accept="image/*" multiple>
                                                        <small class="text-muted">Ces images seront associées à cette couleur spécifique</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm btn-add-variant" onclick="addVariant('vetement')">
                                            <i class="bi bi-plus"></i> Ajouter une taille/couleur
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Food specific fields -->
                            <div class="col-lg-12" id="alimentaire-fields" style="display: none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Détails Produit Alimentaire</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="alimentaire-variants">
                                            <div class="variant-row">
                                                <div class="row">
                                                    <div class="col-lg-3 col-sm-3 col-12">
                                                        <div class="form-group">
                                                            <label>Format *</label>
                                                            <input type="text" name="format[]" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-sm-3 col-12">
                                                        <div class="form-group">
                                                            <label>Saveur *</label>
                                                            <input type="text" name="saveur[]" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 col-sm-2 col-12">
                                                        <div class="form-group">
                                                            <label>Prix (DT) *</label>
                                                            <input type="number" name="price_alimentaire[]" class="form-control" step="0.01" min="0" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-2 col-sm-2 col-12">
                                                        <div class="form-group">
                                                            <label>Quantité *</label>
                                                            <input type="number" name="quantity_alimentaire[]" class="form-control" min="0" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-1 col-sm-1 col-12 d-flex align-items-end justify-content-center mb-3">
                                                        <button type="button" class="btn-remove-variant" onclick="removeVariant(this)">×</button>
                                                    </div>
                                                    <div class="col-lg-12 color-images">
                                                        <div class="color-images-label">Images pour cette saveur:</div>
                                                        <input type="file" name="color_images_alimentaire_0[]" class="form-control" accept="image/*" multiple>
                                                        <small class="text-muted">Ces images seront associées à cette saveur spécifique</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm btn-add-variant" onclick="addVariant('alimentaire')">
                                            <i class="bi bi-plus"></i> Ajouter un format/saveur
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Accessory specific fields -->
                            <div class="col-lg-12" id="accessoire-fields" style="display: none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0">Détails Accessoire</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="accessoire-variants">
                                            <div class="variant-row">
                                                <div class="row">
                                                    <div class="col-lg-4 col-sm-4 col-12">
                                                        <div class="form-group">
                                                            <label>Taille *</label>
                                                            <input type="text" name="taille_accessoire[]" class="form-control" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4 col-sm-4 col-12">
                                                        <div class="form-group">
                                                            <label>Couleur *</label>
                                                            <input type="text" name="couleur_accessoire[]" class="form-control color-input" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-3 col-sm-3 col-12">
                                                        <div class="form-group">
                                                            <label>Quantité *</label>
                                                            <input type="number" name="quantity_accessoire[]" class="form-control" min="0" required>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-1 col-sm-1 col-12 d-flex align-items-end justify-content-center mb-3">
                                                        <button type="button" class="btn-remove-variant" onclick="removeVariant(this)">×</button>
                                                    </div>
                                                    <div class="col-lg-12 color-images">
                                                        <div class="color-images-label">Images pour cette couleur:</div>
                                                        <input type="file" name="color_images_accessoire_0[]" class="form-control" accept="image/*" multiple>
                                                        <small class="text-muted">Ces images seront associées à cette couleur spécifique</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm btn-add-variant" onclick="addVariant('accessoire')">
                                            <i class="bi bi-plus"></i> Ajouter une taille/couleur
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12 mt-3">
                                <button type="submit" name="submit" class="btn btn-primary">Ajouter le produit</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script>
        let variantCounts = {
            'vetement': 1,
            'alimentaire': 1,
            'accessoire': 1
        };
        
        $(document).ready(function() {
            // Gestion de l'affichage du champ taux de réduction
            $('#en_promo').change(function() {
                if ($(this).is(':checked')) {
                    $('#taux_reduction_group').show();
                    $('input[name="taux_reduction"]').prop('required', true);
                } else {
                    $('#taux_reduction_group').hide();
                    $('input[name="taux_reduction"]').prop('required', false);
                    $('input[name="taux_reduction"]').val(0);
                }
            });
            
            $('#type-select').change(function() {
                var selectedType = $(this).val();
                
                // Hide all specific fields
                $('#vetement-fields').hide().find('input, select').prop('required', false);
                $('#alimentaire-fields').hide().find('input, select').prop('required', false);
                $('#accessoire-fields').hide().find('input, select').prop('required', false);
                
                // Show only the fields corresponding to the selected type
                if (selectedType === 'vetement') {
                    $('#vetement-fields').show().find('input:not([type=radio]):not([type=file]), select').prop('required', true);
                    $('#vetement-fields').find('input[type=radio][name=genre]').first().prop('required', true);
                } else if (selectedType === 'alimentaire') {
                    $('#alimentaire-fields').show().find('input:not([type=file]), select').prop('required', true);
                } else if (selectedType === 'accessoire') {
                    $('#accessoire-fields').show().find('input:not([type=file]), select').prop('required', true);
                }
            });
        });
        
        function toggleSizeType(select) {
            const sizeSelect = select.nextElementSibling;
            sizeSelect.innerHTML = '<option value="">Sélectionner</option>';
            
            if (select.value === 'standard') {
                // Add clothing sizes
                const clothingSizes = ['XS', 'S', 'M', 'L', 'XL', 'XXL'];
                clothingSizes.forEach(size => {
                    const option = document.createElement('option');
                    option.value = size;
                    option.textContent = size;
                    sizeSelect.appendChild(option);
                });
            } else {
                // Add shoe sizes
                for (let i = 36; i <= 45; i++) {
                    const option = document.createElement('option');
                    option.value = i;
                    option.textContent = i;
                    sizeSelect.appendChild(option);
                }
            }
        }
        
        function addVariant(type) {
            let container;
            let count = variantCounts[type];
            variantCounts[type]++;
            let html = '';
            
            if (type === 'vetement') {
                container = document.getElementById('vetement-variants');
                html = `
                    <div class="variant-row">
                        <div class="row">
                            <div class="col-lg-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>Taille *</label>
                                    <div class="input-group">
                                        <select class="form-select size-type-select" onchange="toggleSizeType(this)">
                                            <option value="standard">Vêtement (XS-XXL)</option>
                                            <option value="shoe">Chaussures (36-45)</option>
                                        </select>
                                        <select class="form-select size-select" name="taille_vetement[]" required>
                                            <option value="">Sélectionner</option>
                                            <option value="XS">XS</option>
                                            <option value="S">S</option>
                                            <option value="M">M</option>
                                            <option value="L">L</option>
                                            <option value="XL">XL</option>
                                            <option value="XXL">XXL</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>Couleur *</label>
                                    <input type="text" name="couleur_vetement[]" class="form-control color-input" required>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>Quantité *</label>
                                    <input type="number" name="quantity_vetement[]" class="form-control" min="0" required>
                                </div>
                            </div>
                            <div class="col-lg-1 col-sm-1 col-12 d-flex align-items-end justify-content-center mb-3">
                                <button type="button" class="btn-remove-variant" onclick="removeVariant(this)">×</button>
                            </div>
                            <div class="col-lg-12 color-images">
                                <div class="color-images-label">Images pour cette couleur:</div>
                                <input type="file" name="color_images_vetement_${count}[]" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Ces images seront associées à cette couleur spécifique</small>
                            </div>
                        </div>
                    </div>
                `;
            } else if (type === 'alimentaire') {
                container = document.getElementById('alimentaire-variants');
                html = `
                    <div class="variant-row">
                        <div class="row">
                            <div class="col-lg-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>Format *</label>
                                    <input type="text" name="format[]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>Saveur *</label>
                                    <input type="text" name="saveur[]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-2 col-12">
                                <div class="form-group">
                                    <label>Prix (DT) *</label>
                                    <input type="number" name="price_alimentaire[]" class="form-control" step="0.01" min="0" required>
                                </div>
                            </div>
                            <div class="col-lg-2 col-sm-2 col-12">
                                <div class="form-group">
                                    <label>Quantité *</label>
                                    <input type="number" name="quantity_alimentaire[]" class="form-control" min="0" required>
                                </div>
                            </div>
                            <div class="col-lg-1 col-sm-1 col-12 d-flex align-items-end justify-content-center mb-3">
                                <button type="button" class="btn-remove-variant" onclick="removeVariant(this)">×</button>
                            </div>
                            <div class="col-lg-12 color-images">
                                <div class="color-images-label">Images pour cette saveur:</div>
                                <input type="file" name="color_images_alimentaire_${count}[]" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Ces images seront associées à cette saveur spécifique</small>
                            </div>
                        </div>
                    </div>
                `;
            } else if (type === 'accessoire') {
                container = document.getElementById('accessoire-variants');
                html = `
                    <div class="variant-row">
                        <div class="row">
                            <div class="col-lg-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>Taille *</label>
                                    <input type="text" name="taille_accessoire[]" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>Couleur *</label>
                                    <input type="text" name="couleur_accessoire[]" class="form-control color-input" required>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>Quantité *</label>
                                    <input type="number" name="quantity_accessoire[]" class="form-control" min="0" required>
                                </div>
                            </div>
                            <div class="col-lg-1 col-sm-1 col-12 d-flex align-items-end justify-content-center mb-3">
                                <button type="button" class="btn-remove-variant" onclick="removeVariant(this)">×</button>
                            </div>
                            <div class="col-lg-12 color-images">
                                <div class="color-images-label">Images pour cette couleur:</div>
                                <input type="file" name="color_images_accessoire_${count}[]" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Ces images seront associées à cette couleur spécifique</small>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            if (container) {
                container.insertAdjacentHTML('beforeend', html);
            }
        }
        
        function removeVariant(button) {
            const variantRow = button.closest('.variant-row');
            const variantContainer = variantRow.parentElement;
            
            if (variantContainer.querySelectorAll('.variant-row').length > 1) {
                variantRow.remove();
            } else {
                alert('Vous devez avoir au moins une variante pour ce type de produit.');
            }
        }
    </script>

    
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="../assets/js/feather.min.js"></script>
<script src="../assets/js/jquery.slimscroll.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script src="../assets/js/jquery_validate.js"></script>
</body>
</html>