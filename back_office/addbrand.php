<?php
include('db.php'); 
include('navbar.php');
//dossie les images des marques
$targetDir = __DIR__ . "/../assets/img/brand/";
$message = "";
$error = "";

if (isset($_POST['submit'])) {
    $brand_name = trim(mysqli_real_escape_string($conn, $_POST['brand_name']));
    $brand_cover = basename($_FILES['brand_cover']['name']);

    // Check si marque exist
    $check_sql = "SELECT id_marque FROM Marque WHERE nom_marque = '$brand_name'";
    $check_result = $conn->query($check_sql);
    
    if ($check_result->num_rows > 0) {
        $error = "<div class='alert alert-danger'>Cette marque existe déjà !</div>";
    } else {
        if (!empty($brand_cover)) {
            $targetFilePath = $targetDir . $brand_cover;

            // kzn dossier mouch mawjoud create it
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            // verification telechargment d'image
            if (move_uploaded_file($_FILES['brand_cover']['tmp_name'], $targetFilePath)) {
                $filePathEscaped = mysqli_real_escape_string($conn, "../assets/img/brand/" . $brand_cover);

                $sql = "INSERT INTO Marque (nom_marque, cover_marque) VALUES ('$brand_name', '$filePathEscaped')";

                if ($conn->query($sql) === TRUE) {
                    $message = "<div class='alert alert-success'>Marque ajoutée avec succès ! Redirection en cours...</div>";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'brandlist.php?success=1';
                        }, 1500);
                    </script>";
                  
                } else {
                    $error = "<div class='alert alert-danger'>Erreur lors de l'insertion : " . $conn->error . "</div>";
                }
            } else {
                $error = "<div class='alert alert-danger'>Erreur lors de l'upload du fichier.</div>";
            }
        } 
    }
}
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
    <title>Brand list</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Ajouter une marque</h4>
                    <h6>Créer une nouvelle marque</h6>
                </div>
            </div>

            <div id="message-container">
                <?php 
                if (!empty($message)) echo $message;
                if (!empty($error)) echo $error;
                ?>
            </div>

            <div class="row">
                <form action="addbrand.php" method="post" enctype="multipart/form-data" id="Form">
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Nom de la marque</label>
                            <input type="text" id="brand_name" name="brand_name" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label> Couverture de la marque</label>
                            <div class="image-upload">
                                <input type="file" id="brand_cover" name="brand_cover" class="form-control" required>
                                <div class="image-uploads">
                                    <i class="bi bi-cloud-upload upload-icon"></i>
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <input type="submit" name="submit" value="ajouter" class="btn btn-submit me-2">
                        <a href="brandlist.php" class="btn btn-cancel">Cancel</a>
                    </div>
                </form>
            </div>     <?= $message; ?>
        </div>
    </div>

    <!--scripts-->
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
        $('#Form').submit(function(e) {
            e.preventDefault();
            
            var formData = new FormData(this);
            
            $.ajax({
                url: 'addbrand.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#message-container').html($(response).find('#message-container').html());
                    
                    if ($(response).find('.alert-success').length) {
                        setTimeout(function() {
                            window.location.href = 'brandlist.php?success=1';
                        }, 1500);
                    }
                },
                error: function(xhr, status, error) {
                    $('#message-container').html('<div class="alert alert-danger">Erreur: ' + error + '</div>');
                }
            });
        });
    });
    </script>
</body>
</html>