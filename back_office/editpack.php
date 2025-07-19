<?php
include('db.php');
include('navbar.php');

$id_pack = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
 $message='';
// Récupération des données du pack
$sql = "SELECT * FROM Pack WHERE id_pack = $id_pack";
$result = $conn->query($sql);
if ($result->num_rows !== 1) {
    die("Pack non trouvé.");
}
$pack = $result->fetch_assoc();

// Traitement du formulaire
if (isset($_POST['submit'])) {
    $nom_pack = trim(mysqli_real_escape_string($conn, $_POST['nom_pack']));
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $total_pack = floatval($_POST['total_pack']);
    $cover_pack = $pack['cover_pack']; // par défaut

    // Gestion de l'image si nouvelle image envoyée
    if (isset($_FILES['cover_pack']) && $_FILES['cover_pack']['error'] === 0) {
        $cover_pack = basename($_FILES['cover_pack']['name']);
        $upload_dir = 'uploads/';
        $upload_path = $upload_dir . $cover_pack;
        move_uploaded_file($_FILES['cover_pack']['tmp_name'], $upload_path);
    }

    $update_sql = "UPDATE Pack SET nom_pack='$nom_pack', date_debut='$date_debut', date_fin='$date_fin', total_pack='$total_pack', cover_pack='$cover_pack' 
                   WHERE id_pack=$id_pack";

    if ($conn->query($update_sql) === TRUE) {
      $message = "<div class='alert alert-success'>pack ajoutée avec succès ! Redirection en cours...</div>";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'listpack.php?success=1';
                        }, 1500);
                    </script>";
        $pack = array_merge($pack, $_POST, ['cover_pack' => $cover_pack]); // actualiser les valeurs affichées
    } else {
        $error = "Erreur lors de la mise à jour : " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier un pack</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Modifier un pack</h4>
                    <h6>Éditer les informations du pack</h6>
                </div>
                <a href="listpack.php" class="btn btn-secondary"><i class="bi bi-arrow-left"></i> Retour</a>
            </div>

            <div class="row"><?= $message; ?>
                <form action="editpack.php?id=<?= $id_pack ?>" method="post" enctype="multipart/form-data" id="Form">
          
                 

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Nom du pack</label>
                            <input type="text" name="nom_pack" value="<?= htmlspecialchars($pack['nom_pack']) ?>" class="form-control" >
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Date de début</label>
                            <input type="date" name="date_debut" value="<?= $pack['date_debut'] ?>" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Date de fin</label>
                            <input type="date" name="date_fin" value="<?= $pack['date_fin'] ?>" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Total du pack (€)</label>
                            <input type="number" name="total_pack" step="0.01" value="<?= $pack['total_pack'] ?>" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Image de couverture actuelle</label><br>
                            <?php if ($pack['cover_pack']): ?>
                                <img src="uploads/<?= $pack['cover_pack'] ?>" style="height: 50px;">
                            <?php else: ?>
                                <span>Pas d'image</span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Changer l’image de couverture</label>
                            <input type="file" name="cover_pack" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <input type="submit" name="submit" value="Mettre à jour" class="btn btn-success me-2">
                        <a href="listpack.php" class="btn btn-cancel">Annuler</a>
                    </div>
                </form>
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
    
</body>
</html>
