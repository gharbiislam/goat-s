<?php
session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
  
    $product_name = mysqli_real_escape_string($conn, $_POST['product_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $category = mysqli_real_escape_string($conn, $_POST['Category']);
    $brand = mysqli_real_escape_string($conn, $_POST['brand']);

    // select el categorie
    $category_query = mysqli_query($conn, "SELECT id_categorie FROM categorie WHERE libelle_categorie = '$category'");
    if (!$category_query || mysqli_num_rows($category_query) == 0) {
        $_SESSION['error'] = "Catégorie invalide";
        header("Location: addproduct.php");
        exit();
    }
    $category_row = mysqli_fetch_assoc($category_query);
    $id_categorie = $category_row['id_categorie'];

    // select el marque
    $brand_query = mysqli_query($conn, "SELECT id_marque FROM marque WHERE nom_marque = '$brand'");
    if (!$brand_query || mysqli_num_rows($brand_query) == 0) {
        $_SESSION['error'] = "Marque invalide";
        header("Location: addproduct.php");
        exit();
    }
    $brand_row = mysqli_fetch_assoc($brand_query);
    $id_marque = $brand_row['id_marque'];

    mysqli_begin_transaction($conn);

    try {
        // Insertion produit fel base
        $insert_product = mysqli_query($conn, "INSERT INTO Produits (lib_produit, desc_produit, prix_produit, id_marque, id_categorie) 
                                            VALUES ('$product_name', '$description', $price, $id_marque, $id_categorie)");

        if (!$insert_product) {
            throw new Exception("Erreur lors de l'ajout du produit: " . mysqli_error($conn));
        }

        $product_id = mysqli_insert_id($conn);

        //create el dossier ken mouch maxjoud
        $upload_dir = '../assets/img/products/';
        $product_dir = $upload_dir . $product_id . '/';
        
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                throw new Exception("Erreur lors de la création du dossier d'images");
            }
        }
        
        if (!file_exists($product_dir)) {
            if (!mkdir($product_dir, 0777, true)) {
                throw new Exception("Erreur lors de la création du dossier du produit");
            }
        }
        function handleSaveurImages($conn, $files, $product_id, $saveur, $product_dir) {
            $order = 1;
            
            foreach ($files['tmp_name'] as $key => $tmp_name) {
                $image_info = getimagesize($tmp_name);
                if (!$image_info) {
                    continue; 
                }
                
                $image_name = basename($files['name'][$key]);
                $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
                $new_filename = 'saveur_' . $saveur . '_' . uniqid() . '.' . $image_ext;
                $image_path = $product_dir . $new_filename;
                
                if (move_uploaded_file($tmp_name, $image_path)) {
                    $relative_path = 'assets/img/products/' . $product_id . '/' . $new_filename;
                    $insert_saveur_image = mysqli_query($conn, "INSERT INTO Images (ordre, id_produit, couleur, image_path) 
                                                            VALUES ($order, $product_id, '$saveur', '$relative_path')");
                    
                    if (!$insert_saveur_image) {
                        throw new Exception("Erreur lors de l'ajout de l'image pour la saveur: " . mysqli_error($conn));
                    }
                    $order++;
                }
            }
        }
        //telechargement des images
        if (!empty($_FILES['product_images']['name'][0])) {
            $images = $_FILES['product_images'];
            $order = 1;
            
            foreach ($images['tmp_name'] as $key => $tmp_name) {
               
                $image_info = getimagesize($tmp_name);
                if (!$image_info) {
                    continue; // Skip  images kn mouch validee
                }
                
                $image_name = basename($images['name'][$key]);
                $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
                $new_filename = 'main_' . uniqid() . '.' . $image_ext;
                $image_path = $product_dir . $new_filename;
                
                if (move_uploaded_file($tmp_name, $image_path)) {
                    // Insertion images path
                    $relative_path = 'assets/img/products/' . $product_id . '/' . $new_filename;
                    $insert_image = mysqli_query($conn, "INSERT INTO Images (ordre, id_produit, couleur, image_path) 
                                                       VALUES ($order, $product_id, NULL, '$relative_path')");
                    
                    if (!$insert_image) {
                        throw new Exception("Erreur lors de l'ajout de l'image: " . mysqli_error($conn));
                    }
                    $order++;
                }
            }
        }

        // ken type vetement
        if ($type === 'vetement') {
            if (!isset($_POST['genre'])) {
                throw new Exception("Genre non spécifié pour le vêtement");
            }
            
            $genre = mysqli_real_escape_string($conn, $_POST['genre']);
            $tailles = $_POST['taille_vetement'] ?? [];
            $couleurs = $_POST['couleur_vetement'] ?? [];
            $quantities = $_POST['quantity_vetement'] ?? [];
            
            if (count($tailles) !== count($couleurs) || count($tailles) !== count($quantities)) {
                throw new Exception("Données de variantes de vêtement incomplètes");
            }
            
            for ($i = 0; $i < count($tailles); $i++) {
                $taille = mysqli_real_escape_string($conn, $tailles[$i]);
                $couleur = mysqli_real_escape_string($conn, $couleurs[$i]);
                $qty = intval($quantities[$i]);
                
                $insert_variant = mysqli_query($conn, "INSERT INTO Vetement (id_produit, taille, couleur, genre, stock_variant) 
                                                   VALUES ($product_id, '$taille', '$couleur', '$genre', $qty)");
                
                if (!$insert_variant) {
                    throw new Exception("Erreur lors de l'ajout de la variante de vêtement: " . mysqli_error($conn));
                }
                
                // add img par couleur
                $color_images_key = "color_images_vetement_$i";
                if (isset($_FILES[$color_images_key]) && !empty($_FILES[$color_images_key]['name'][0])) {
                    handleColorImages($conn, $_FILES[$color_images_key], $product_id, $couleur, $product_dir);
                }
            }
        } 
        // si type alimentaire
        elseif ($type === 'alimentaire') {
            $formats = $_POST['format'] ?? [];
            $saveurs = $_POST['saveur'] ?? [];
            $quantities = $_POST['quantity_alimentaire'] ?? [];
            $prices = $_POST['price_alimentaire'] ?? [];
            
            if (count($formats) !== count($saveurs) || count($formats) !== count($quantities) || count($formats) !== count($prices)) {
                throw new Exception("Données de variantes alimentaires incomplètes");
            }
            
            for ($i = 0; $i < count($formats); $i++) {
                $format = mysqli_real_escape_string($conn, $formats[$i]);
                $saveur = mysqli_real_escape_string($conn, $saveurs[$i]);
                $qty = intval($quantities[$i]);
                $variant_price = floatval($prices[$i]); // Prix spécifique au format
                
                $insert_variant = mysqli_query($conn, "INSERT INTO Alimentaire (id_produit, saveur, format, stock_variant, prix_variant) 
                                                   VALUES ($product_id, '$saveur', '$format', $qty, $variant_price)");
                
                if (!$insert_variant) {
                    throw new Exception("Erreur lors de l'ajout de la variante alimentaire: " . mysqli_error($conn));
                }
                
                //add img par saveur
                $saveur_images_key = "color_images_alimentaire_$i";
                if (isset($_FILES[$saveur_images_key]) && !empty($_FILES[$saveur_images_key]['name'][0])) {
                    handleSaveurImages($conn, $_FILES[$saveur_images_key], $product_id, $saveur, $product_dir);
                }
            }
        }
        //si type acc
        elseif ($type === 'accessoire') {
            $tailles = $_POST['taille_accessoire'] ?? [];
            $couleurs = $_POST['couleur_accessoire'] ?? [];
            $quantities = $_POST['quantity_accessoire'] ?? [];
            
            if (count($tailles) !== count($couleurs) || count($tailles) !== count($quantities)) {
                throw new Exception("Données de variantes d'accessoire incomplètes");
            }
            
            for ($i = 0; $i < count($tailles); $i++) {
                $taille = mysqli_real_escape_string($conn, $tailles[$i]);
                $couleur = mysqli_real_escape_string($conn, $couleurs[$i]);
                $qty = intval($quantities[$i]);
                
                $insert_variant = mysqli_query($conn, "INSERT INTO Accessoire (id_produit, taille, couleur, stock_variant) 
                                                   VALUES ($product_id, '$taille', '$couleur', $qty)");
                
                if (!$insert_variant) {
                    throw new Exception("Erreur lors de l'ajout de la variante d'accessoire: " . mysqli_error($conn));
                }
                
                // add img par couleur
                $color_images_key = "color_images_accessoire_$i";
                if (isset($_FILES[$color_images_key]) && !empty($_FILES[$color_images_key]['name'][0])) {
                    handleColorImages($conn, $_FILES[$color_images_key], $product_id, $couleur, $product_dir);
                }
            }
        }

       
        mysqli_commit($conn);
        
        // Success msg etredirection el page liste
        $_SESSION['message'] = "Produit ajouté avec succès!";
        header("Location: productlist.php");
        exit();
        
    } catch (Exception $e) {
       // annuler transaction si famma erreur
        mysqli_rollback($conn);
        
        $_SESSION['error'] = $e->getMessage();
        header("Location: addproduct.php");
        exit();
    }
} else {
    header("Location: addproduct.php");
    exit();
}

function handleColorImages($conn, $files, $product_id, $color, $product_dir) {
    $order = 1;
    
    foreach ($files['tmp_name'] as $key => $tmp_name) {
        // Validation image
        $image_info = getimagesize($tmp_name);
        if (!$image_info) {
            continue; // Skip si images pas validee
        }
        
        $image_name = basename($files['name'][$key]);
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $new_filename = 'color_' . $color . '_' . uniqid() . '.' . $image_ext;
        $image_path = $product_dir . $new_filename;
        
        if (move_uploaded_file($tmp_name, $image_path)) {
            // Insertion couleur d'img path 
            $relative_path = 'assets/img/products/' . $product_id . '/' . $new_filename;
            $insert_color_image = mysqli_query($conn, "INSERT INTO Images (ordre, id_produit, couleur, image_path) 
                                                    VALUES ($order, $product_id, '$color', '$relative_path')");
            
            if (!$insert_color_image) {
                throw new Exception("Erreur lors de l'ajout de l'image pour la couleur: " . mysqli_error($conn));
            }
            $order++;
        }
    }
}
?>