<?php
include('db.php');
include('navbar.php');

$targetDir = __DIR__ . "/../assets/img/brand/";
$targetPathDb = "../assets/img/brand/";
$message="";

// Get brand ID
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch current brand data
$marque = null;
if ($id) {
    $result = $conn->query("SELECT * FROM Marque WHERE id_marque = $id");
    $marque = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom = trim(mysqli_real_escape_string($conn, $_POST['brand_name']));

    if (!empty($_FILES['brand_cover']['name'])) {
        $brand_cover = basename($_FILES['brand_cover']['name']);
        $targetFilePath = $targetDir . $brand_cover;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['brand_cover']['tmp_name'], $targetFilePath)) {
            $filePathEscaped = mysqli_real_escape_string($conn, $targetPathDb . $brand_cover);
            $sql = "UPDATE Marque SET nom_marque='$nom', cover_marque='$filePathEscaped' WHERE id_marque=$id";
        } else {
            echo "Erreur lors de l'upload du fichier.";
            exit;
        }
    } else {
        // Only update name
        $sql = "UPDATE Marque SET nom_marque='$nom' WHERE id_marque=$id";
    }

    if ($conn->query($sql) === TRUE) {
      $message = "<div class='alert alert-success'>Marque mise à jour avec succès ! Redirection en cours...</div>";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'brandlist.php?success=1';
                        }, 1500);
                    </script>";
    } else {
        echo "Erreur lors de la mise à jour : " . $conn->error;
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
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="assets/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        .brand-box {
            border: 1px solid #ccc;
            height: 100px;
            width: 360px;
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .brand-box img {
            width: 200px;
            height: auto;
            object-fit: contain;
        }

        .brand-box .brand-name {
            margin-left: 20px;
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Modifier la marque</h4>
                    <h6>Mettre à jour les informations de la marque</h6>
                </div>
            </div>

            <div class="row">
                <form action="" method="post" enctype="multipart/form-data" id="Form">
                    
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Nom de la marque</label>
                            <input type="text" id="brand_name" name="brand_name" class="form-control" value="<?= htmlspecialchars($marque['nom_marque'] ?? '') ?>">
                            </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Couverture de la marque</label>
                            <div class="image-upload">
                                <input type="file" id="brand_cover" name="brand_cover">
                                <div class="image-uploads">
                                    <i class="bi bi-cloud-upload upload-icon"></i>
                                    <h4>Drag and drop a file to upload</h4>
                                </div>
                            </div>
                        </div>
                    </div>
<div class="col-lg-12">
                        <div class="form-group">
                            
                        <div class="brand-box">
    
    <img src="<?= $marque && $marque['cover_marque'] ? $marque['cover_marque'] : 'https://via.placeholder.com/200x100?text=No+Image' ?>" alt="Brand Image">
    <div class="brand-name">
        <p>  <p><?= basename($marque['cover_marque'] ?? 'No Image') ?></p>
    </div><div class="close-icon " onclick="handleClose(this)">×</div>
</div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <input type="submit" name="submit" value="Enregistrer la modification" class="btn btn-submit me-2">
                        <a href="brandlist.php" class="btn btn-cancel">Annuler</a>
                    </div>
                </form>
            </div>     <?= $message; ?>
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
