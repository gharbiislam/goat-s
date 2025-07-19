<?php
include('db.php');

include('navbar.php');
$message = "";

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: categorylist.php');
    exit();
}

$id = (int)$_GET['id'];

$query = "SELECT id_categorie, libelle_categorie, id_categorie_parent FROM categorie WHERE id_categorie = $id";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header('Location: categorylist.php');
    exit();
}

$category = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['categorie_name']) && !empty(trim($_POST['categorie_name']))) {
        $categorie_name = mysqli_real_escape_string($conn, $_POST['categorie_name']);
        
        $parent_id = null;
        if (isset($_POST['parent_category']) && $_POST['parent_category'] > 0) {
            if ($_POST['parent_category'] != $id) {
                $parent_id = (int)$_POST['parent_category'];
            } else {
                $message = "<div class='alert alert-danger'>Une catégorie ne peut pas être son propre parent !</div>";
                goto skip_update;
            }
        }
        
        if ($parent_id) {
            $req = "UPDATE categorie SET libelle_categorie = '$categorie_name', id_categorie_parent = $parent_id WHERE id_categorie = $id";
        } else {
            $req = "UPDATE categorie SET libelle_categorie = '$categorie_name', id_categorie_parent = NULL WHERE id_categorie = $id";
        }
        
        $res = mysqli_query($conn, $req);
        
        if ($res) {
            $message = "<div class='alert alert-success'>Catégorie mise à jour avec succès ! Redirection en cours...</div>";
            echo "<script>
                setTimeout(function() {
                    window.location.href = 'categorylist.php?success=1';
                }, 1500);
            </script>";
        } else {
            $message = "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
        }
    } 
}

skip_update:

$categories = [];
$query = "SELECT id_categorie, libelle_categorie FROM categorie WHERE id_categorie != $id ORDER BY libelle_categorie";
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
    <title>Edit Category</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Modifier la catégorie</h4>
                <h6>Mettre à jour les informations de la catégorie</h6>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body">
                <div class="row">
                  
                    <form action="editcategory.php?id=<?= $id ?>" method="post" id="Form">
                        <div class="col-lg-6 col-sm-6 col-12">
                            <div class="form-group">
                                <label>Nom catégorie
</label>
                                <input type="text" name="categorie_name" id="categorie_name" class="form-control" value="<?= htmlspecialchars($category['libelle_categorie']) ?>" required>
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
                            <button type="submit" class="btn btn-submit me-2">Enregistrer la modification</button>
                            <a href="categorylist.php" class="btn btn-cancel">Annuler</a>
                        </div>
                    </form>
                </div>
                <?= $message; ?>
                
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="../assets/js/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="../assets/js/feather.min.js"></script>
<script src="../assets/js/jquery.slimscroll.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script src="../assets/js/jquery_validate.js"></script>

<script>
    $(document).ready(function() {
        $('.select').select2();
    });
</script>
</body>
</html>