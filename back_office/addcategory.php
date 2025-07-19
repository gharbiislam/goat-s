<?php
include('navbar.php');
include('db.php');
$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['categorie_name']) && !empty(trim($_POST['categorie_name']))) {
        $categorie_name = mysqli_real_escape_string($conn, $_POST['categorie_name']);
        
        // Check si categorie deja exist
        $check_query = "SELECT id_categorie FROM categorie WHERE libelle_categorie = '$categorie_name'";
        $check_result = mysqli_query($conn, $check_query);
        
        if (mysqli_num_rows($check_result) > 0) {
            $message = "<div class='alert alert-danger'>Erreur : Une catégorie avec ce nom existe déjà !</div>";
        } else {
            $parent_id = null;
            if (isset($_POST['parent_category']) && $_POST['parent_category'] > 0) {
                $parent_id = (int)$_POST['parent_category'];
            }
            
            if ($parent_id) {
                $req = "INSERT INTO categorie (libelle_categorie, id_categorie_parent) VALUES ('$categorie_name', $parent_id)";
            } else {
                $req = "INSERT INTO categorie (libelle_categorie, id_categorie_parent) VALUES ('$categorie_name', NULL)"; //kn fmsh id_parent nkhilha null
            }
            
            $res = mysqli_query($conn, $req);
            
            if ($res) {
                $message = "<div class='alert alert-success'>Catégorie ajoutée avec succès ! Redirection en cours...</div>";
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'categorylist.php?success=1';
                    }, 1500); 
                </script>";// success msg b3ed navigate el category list
            } else {
                $message = "<div class='alert alert-danger'>Erreur : " . mysqli_error($conn) . "</div>";
            }
        }
    } else {
        $message = "<div class='alert alert-danger'>Erreur : Le nom de la catégorie est requis !</div>";
    }
}

$categories = [];
$query = "SELECT id_categorie, libelle_categorie FROM categorie ORDER BY libelle_categorie";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $categories[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, invoice, html5, responsive, Projects">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <title>Add Category</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Ajouter catégorie</h4>
                <h6>Crée une nouvelle catégorie</h6>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <form action="addcategory.php" method="post" id="Form">
                        <div class="col-lg-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Nom catégorie</label>
                                <input type="text" name="categorie_name" id="categorie_name" class="form-control">
                            </div>
                        </div>
                        
                        <div class="col-lg-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Catégorie parente (Laissez vide pour une catégorie principale)</label>
                                <select name="parent_category" id="parent_category" class="form-control select">
                                    <option value="0">Aucune (catégorie principale)</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?= $category['id_categorie'] ?>"><?= htmlspecialchars($category['libelle_categorie']) ?></option> 
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-lg-12">
                            <button type="submit" class="btn btn-submit me-2">Ajouter</button>
                            <a href="categorylist.php" class="btn btn-cancel">Annuler</a>
                        </div>
                    </form>
                </div>
                <?= $message; ?>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="../assets/js/feather.min.js"></script>
<script src="../assets/js/jquery.slimscroll.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script src="../assets/js/jquery_validate.js"></script>
<script>
    $(document).ready(function() {//get plugin pour un style facile 
        $('.select').select2();
    });
</script>

</body>
</html>