<?php
include('db.php'); 
include('navbar.php');
 $message='';
if (isset($_POST['submit'])) {
    $code = trim(mysqli_real_escape_string($conn, $_POST['code']));
    $reduction = floatval($_POST['reduction']);
    $date_lancement = $_POST['date_lancement'];
    $date_expiration = $_POST['date_expiration'];

    if (!empty($code) && !empty($reduction) && !empty($date_lancement) && !empty($date_expiration)) {
        $sql = "INSERT INTO Code_Promo (code, reduction, date_lancement, date_expiration) 
                VALUES ('$code', '$reduction', '$date_lancement', '$date_expiration')";

        if ($conn->query($sql) === TRUE) {
          
             $message = "<div class='alert alert-success'> Code promo ajoutée avec succès ! Redirection en cours...</div>";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'listecodepromo.php?success=1';
                        }, 1500);
                    </script>";
        } else {
            echo "Erreur lors de l'insertion : " . $conn->error;
        }
    } else {
        echo "Veuillez remplir tous les champs.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Ajouter un code promo">
    <meta name="keywords" content="admin, promo, réduction, dashboard">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <title>Ajouter un code promo</title>

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
                    <h4>Ajouter un code promo</h4>
                    <h6>Créer un nouveau code de réduction</h6>
                </div>
            </div>

            <div class="row"><?= $message; ?>
                <form action="codepromo.php" method="post" id="Form">
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Code</label>
                            <input type="text" name="code" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Réduction (%)</label>
                            <input type="number" step="0.01" name="reduction" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Date de lancement</label>
                            <input type="date" name="date_lancement" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-6 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Date d'expiration</label>
                            <input type="date" name="date_expiration" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <input type="submit" name="submit" value="Enregistrer" class="btn btn-submit me-2">
                        <a href="commandelist.php" class="btn btn-cancel">Annuler</a>
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
