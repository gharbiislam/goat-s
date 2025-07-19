<?php
// Démarrer la bufferisation de sortie pour éviter les erreurs d'en-têtes
ob_start();

// Inclure les fichiers nécessaires
include('db.php'); 
include('navbar.php');

// Vérifier si l'ID est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: listecodepromo.php");
    exit();
}
 $message='';
// Sécuriser l'ID reçu
$id = intval($_GET['id']);

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit'])) {
    // Nettoyer et valider les données
    $code = trim(mysqli_real_escape_string($conn, $_POST['code']));
    $reduction = floatval($_POST['reduction']);
    $date_lancement = $_POST['date_lancement'];
    $date_expiration = $_POST['date_expiration'];

    // Validation des champs
  

    // Si pas d'erreurs, procéder à la mise à jour

        $sql = "UPDATE Code_Promo SET 
                code = ?, 
                reduction = ?, 
                date_lancement = ?, 
                date_expiration = ? 
                WHERE id_codepromo = ?";
        
        // Utilisation d'une requête préparée pour plus de sécurité
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdssi", $code, $reduction, $date_lancement, $date_expiration, $id);
        
        if ($stmt->execute()) {
            // Redirection après succès
           
             $message = "<div class='alert alert-success'> Code promo ajoutée avec succès ! Redirection en cours...</div>";
                    echo "<script>
                        setTimeout(function() {
                            window.location.href = 'listecodepromo.php?success=1';
                        }, 1500);
                    </script>";
        } else {
            $error_message = "Erreur lors de la mise à jour : " . $stmt->error;
        }
        
        $stmt->close();
    } 


// Récupération des données actuelles du code promo
$sql = "SELECT * FROM Code_Promo WHERE id_codepromo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    header("Location: listecodepromo.php?error=notfound");
    exit();
}

$code_promo = $result->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Modifier un code promo">
    <meta name="keywords" content="admin, promo, réduction, dashboard">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <title>Modifier un code promo</title>

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
                    <h4>Modifier un code promo</h4>
                    <h6>Mise à jour des informations du code promo</h6>
                </div>
            </div>

            <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="row">
    <div class="col-lg-12"><?= $message; ?>
        <form action="editcodepromo.php?id=<?php echo $id; ?>" method="post" id="Form">
            <div class="row">
                <div class="col-lg-6 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Code</label>
                        <input type="text" name="code" class="form-control" value="<?php echo htmlspecialchars($code_promo['code']); ?>" required>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Réduction (%)</label>
                        <input type="number" step="0.01" min="0.01" max="100" name="reduction" class="form-control" value="<?php echo $code_promo['reduction']; ?>" required>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Date de lancement</label>
                        <input type="date" name="date_lancement" class="form-control" value="<?php echo $code_promo['date_lancement']; ?>" required>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-6 col-12">
                    <div class="form-group">
                        <label>Date d'expiration</label>
                        <input type="date" name="date_expiration" class="form-control" value="<?php echo $code_promo['date_expiration']; ?>" required>
                    </div>
                </div>
                <div class="col-lg-12">
                    <input type="submit" name="submit" value="Mettre à jour" class="btn btn-submit me-2">
                    <a href="listecodepromo.php" class="btn btn-cancel">Annuler</a>
                </div>
            </div>
        </form>
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
    
    <!-- Script pour valider les dates -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.querySelector('form');
        const dateLancement = document.querySelector('input[name="date_lancement"]');
        const dateExpiration = document.querySelector('input[name="date_expiration"]');
        
        form.addEventListener('submit', function(e) {
            if (new Date(dateExpiration.value) < new Date(dateLancement.value)) {
                e.preventDefault();
                alert("La date d'expiration doit être postérieure à la date de lancement");
            }
        });
    });
    </script>
</body>
</html>
<?php ob_end_flush(); ?>