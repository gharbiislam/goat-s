<?php
include('db.php'); 
include('navbar.php');

$message = "";
$error = "";

if (isset($_POST['submit'])) {
    $nom_pack = trim(mysqli_real_escape_string($conn, $_POST['nom_pack']));
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $total_pack = floatval($_POST['total_pack']);
    $description =$_POST['description'];
    $produit =$_POST['produits_pack'];

    // la'jout d'image
    if (isset($_FILES['cover_pack']) && $_FILES['cover_pack']['error'] === 0) {
        $cover_pack = basename($_FILES['cover_pack']['name']);
        $upload_dir = __DIR__ . "/../assets/img/pack/";
        $upload_path = $upload_dir . $cover_pack;
        //si l'upload success inseree fel base
        if (move_uploaded_file($_FILES['cover_pack']['tmp_name'], $upload_path)) {
            $sql = "INSERT INTO Pack (nom_pack, date_debut, date_fin, total_pack, cover_pack,descriptions, produit) 
                    VALUES ('$nom_pack', '$date_debut', '$date_fin', '$total_pack', '$cover_pack','$description','$produit')";

            if ($conn->query($sql) === TRUE) {
                  $message = "<div class='alert alert-success'>pack ajoutée avec succès ! Redirection en cours...</div>";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'listpack.php?success=1';
                        }, 1500);
                    </script>";
              
            } else {
                $error = "Erreur lors de l'insertion : " . $conn->error;
            }
        } 
    } 
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Ajouter un pack">
    <meta name="keywords" content="admin, pack, promotion, dashboard">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <title>Ajouter un pack</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Ajouter un pack</h4>
                    <h6>Créer un nouveau pack promotionnel</h6>
                </div>
            </div>

            <div class="row"> <?= $message; ?>
                <form action="ajoutpack.php" method="post" enctype="multipart/form-data" id="Form">
                 
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Nom du pack</label>
                            <input type="text" name="nom_pack" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Produits</label>
                            <input type="text" name="produits_pack" class="form-control" required>
                        </div>
                    </div>
                        
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Date de début</label>
                            <input type="date" name="date_debut" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Date de fin</label>
                            <input type="date" name="date_fin" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Prix</label>
                            <input type="number" step="0.01" name="total_pack" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-12">
                                <div class="form-group">
                                    <label>Description *</label>
                                    <textarea name="description" class="form-control" required></textarea>
                                </div>
                            </div>
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Image de couverture</label>
                            <input type="file" name="cover_pack" accept="image/*" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <input type="submit" name="submit" value="Enregistrer" class="btn btn-submit me-2">
                        <a href="listpack.php" class="btn btn-cancel">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- scripts -->
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
