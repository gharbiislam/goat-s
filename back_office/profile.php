<?php
session_start();
include('db.php');
include('navbar.php');

if (!isset($_SESSION['id_admin'])) {
    header("Location: login.php");
    exit();
}

$id_admin = $_SESSION['id_admin'];

// Traitement du formulaire pour les informations personnelles
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_profile'])) {
    $nom = mysqli_real_escape_string($conn, $_POST['nom']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $motdepasse = !empty($_POST['motdepasse']) ? mysqli_real_escape_string($conn, $_POST['motdepasse']) : null;

    $updateQuery = "UPDATE admin SET 
                    nom_admin = '$nom', 
                    prenom_admin = '$prenom', 
                    mail_admin = '$email'";

    if ($motdepasse) {
        $updateQuery .= ", mdp_admin = '$motdepasse'";
    }

    $updateQuery .= " WHERE id_admin = $id_admin";

    if (mysqli_query($conn, $updateQuery)) {
        $success = "Profil mis à jour avec succès.";
    } else {
        $erreur = "Erreur lors de la mise à jour du profil.";
    }
}

// Traitement pour l'upload d'image
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_image'])) {
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $allowed = array('jpg', 'jpeg', 'png', 'gif');
        $filename = $_FILES['profile_image']['name'];
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        
        if (in_array(strtolower($ext), $allowed)) {
            $new_filename = 'admin_' . $id_admin . '_' . time() . '.' . $ext;
            $upload_dir = '../uploads/profile/';
            
            // Créer le répertoire s'il n'existe pas
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $destination = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $destination)) {
                $update_img_query = "UPDATE admin SET image_admin = '$destination' WHERE id_admin = $id_admin";
                
                if (mysqli_query($conn, $update_img_query)) {
                    $success_img = "Photo de profil mise à jour avec succès.";
                } else {
                    $erreur_img = "Erreur lors de la mise à jour de la photo de profil dans la base de données.";
                }
            } else {
                $erreur_img = "Erreur lors de l'upload de l'image.";
            }
        } else {
            $erreur_img = "Type de fichier non autorisé. Seuls les fichiers JPG, JPEG, PNG et GIF sont acceptés.";
        }
    } else if ($_FILES['profile_image']['error'] != 4) {
        $erreur_img = "Erreur lors de l'upload de l'image. Code erreur: " . $_FILES['profile_image']['error'];
    }
}

// Récupérer les données mises à jour
$query = "SELECT * FROM admin WHERE id_admin = $id_admin";
$result = mysqli_query($conn, $query);
$data = mysqli_fetch_assoc($result);

// Get admin image if available
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
<meta name="description" content="POS - Bootstrap Admin Template">
<meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, html5, responsive">
<meta name="author" content="Dreamguys - Bootstrap Admin Template">
<meta name="robots" content="noindex, nofollow">
<title>Profile</title>
<link rel="stylesheet" href="../assets/css/bootstrap.min.css">
<link rel="stylesheet" href="../assets/css/animate.css">
<link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
<link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
</head>
<body>

<div class="main-wrapper">
<div class="page-wrapper">
<div class="content">
<div class="page-header">
<div class="page-title d-flex justify-content-between align-items-center w-100">
<div class="">
<h4>Profile</h4>
<h6>Modifier vos informations personnelles</h6>
</div>
<div>
    <?php if (isset($success)) echo "<div class='alert alert-success'>$success</div>"; ?>
    <?php if (isset($erreur)) echo "<div class='alert alert-danger'>$erreur</div>"; ?>
    <?php if (isset($success_img)) echo "<div class='alert alert-success'>$success_img</div>"; ?>
    <?php if (isset($erreur_img)) echo "<div class='alert alert-danger'>$erreur_img</div>"; ?>
</div>         
</div>
</div>

<div class="card">
<div class="card-body">
<div class="profile-set">
<div class="profile-head">
</div>
<div class="profile-top">
<div class="profile-content">
<div class="profile-contentimg"> <form method="POST" action="" enctype="multipart/form-data" id="image-form">
<img src="<?php echo htmlspecialchars($admin_img); ?>" alt="Photo de profil" id="profile-preview" >
<div class="profileupload">
   
        <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;" onchange="document.getElementById('image-form').submit();">
        <input type="hidden" name="upload_image" value="1">
    </form>
    <a href="javascript:void(0);" onclick="document.getElementById('profile_image').click();">
        <img src="../assets/img/icons/edit-set.svg" alt="Modifier la photo">
    </a>
</div>
</div>
<div>
<div class="profile-contentname">
<h2><?= htmlspecialchars($data['nom_admin']) ?> <?= htmlspecialchars($data['prenom_admin']) ?></h2>
<h4>Mettez à jour votre photo et vos informations personnelles.</h4>
</div>
</div>
</div>
</div>
</div>

<form method="POST" action="">
    <div class="row">
        <div class="col-lg-6 col-sm-12">
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" value="<?= htmlspecialchars($data['nom_admin']) ?>" class="form-control" required>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12">
            <div class="form-group">
                <label>Prénom</label>
                <input type="text" name="prenom" value="<?= htmlspecialchars($data['prenom_admin']) ?>" class="form-control" required>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12">
            <div class="form-group">
                <label>Adresse mail</label>
                <input type="email" name="email" value="<?= htmlspecialchars($data['mail_admin']) ?>" class="form-control" required>
            </div>
        </div>
        <div class="col-lg-6 col-sm-12">
            <div class="form-group">
                <label>Nouveau mot de passe (laisser vide si inchangé)</label>
                <div class="pass-group">
                    <input type="password" name="motdepasse" class="form-control">
                    <span class="fas toggle-password fa-eye-slash"></span>
                </div>
            </div>
        </div>
        <div class="col-12">
            <button type="submit" name="submit_profile" class="btn btn-primary">Enregistrer</button>
            <a href="index.php" class="btn btn-secondary">Annuler</a>
        </div>
    </div>
</form>

</div>
</div>

</div>
</div>
</div>

<!--scripts-->
<script src="../assets/js/jquery-3.6.0.min.js"></script>
<script src="../assets/js/feather.min.js"></script>
<script src="../assets/js/jquery.slimscroll.min.js"></script>
<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/script.js"></script>
<script src="../assets/js/index.js"></script>
<script src="../assets/plugins/apexchart/apexcharts.min.js"></script>
<script src="../assets/plugins/apexchart/chart-data.js"></script>
<script src="../assets/plugins/select2/js/select2.min.js"></script>
<script src="../assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
<script src="../assets/js/jquery.dataTables.min.js"></script>
<script src="../assets/js/dataTables.bootstrap4.min.js"></script>
<script src="../assets/plugins/sweetalert/sweetalerts.min.js"></script>

<script>
// JavaScript to preview selected image before upload
document.getElementById('profile_image').addEventListener('change', function(e) {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('profile-preview').src = e.target.result;
        }
        reader.readAsDataURL(file);
    }
});

// Toggle password visibility
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.querySelector('input[name="motdepasse"]');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        this.classList.remove('fa-eye-slash');
        this.classList.add('fa-eye');
    } else {
        passwordInput.type = 'password';
        this.classList.remove('fa-eye');
        this.classList.add('fa-eye-slash');
    }
});
</script>
</body>
</html>