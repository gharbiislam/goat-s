<?php
session_start();
include 'db.php';

if (!isset($_SESSION['id_client'])) {
    header("Location: login.php");
    exit();
}

$id_client = $_SESSION['id_client'];
$result = mysqli_query($conn, "SELECT * FROM client WHERE id_client = '$id_client'");

if ($result) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "Error fetching user data.";
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nom = mysqli_real_escape_string($conn, $_POST['nom_client']);
    $prenom = mysqli_real_escape_string($conn, $_POST['prenom_client']);
    $email = mysqli_real_escape_string($conn, $_POST['mail_client']);
    
    // Handle password change only if old password is provided and correct
    if (!empty($_POST['old_password']) && !empty($_POST['new_password'])) {
        if (password_verify($_POST['old_password'], $user['mdp_client'])) {
            $mdp = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        } else {
            $error = "Old password is incorrect";
            $mdp = $user['mdp_client'];
        }
    } else {
        $mdp = $user['mdp_client'];
    }

    $update_query = "UPDATE client SET 
                    nom_client = '$nom', 
                    prenom_client = '$prenom', 
                    mail_client = '$email', 
                    mdp_client = '$mdp' 
                    WHERE id_client = '$id_client'";

    if (empty($error) && mysqli_query($conn, $update_query)) {
        $success = "Profile updated successfully!";
        $result = mysqli_query($conn, "SELECT * FROM client WHERE id_client = '$id_client'");
        $user = mysqli_fetch_assoc($result);
    } else if (empty($error)) {
        $error = "Error updating profile: " . mysqli_error($conn);
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
    <title> GOAT'S - modifer profil </title>
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="../assets/css/style.css">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <style>
        .goats_blue {
            color: #1E4494;
        }
        .bg-green {
            background-color: #1E4494;  
        }
        .error-message {
            color: red;
        }
        .success-message {
            color: green;
        }
      
        .form-group label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .form-control {
            border-radius: 5px;
        }
 button {
    border-radius: 6px !important;
    background-color: #8FC73F;
    color: white;
    padding: 8px 20px;
    font-weight: 600; border: none;
}

        button:hover {
            background-color: #648B2C;
           
        }
        .annuler {
            background-color: none;
            border:solid 2px #648B2C;
            color: #648B2C; padding: 8px 16px ; font-weight: 600;
           border-radius: 6px; text-decoration: none; margin-left: 10px;
        }
  .annuler:hover {
            background-color: #F3F9EB; border:solid 2px #648B2C;
            color: #648B2C; padding: 8px 16px ; 
        }
        /* Flexbox layout for sidebar and main content */
        .main-container {
            display: flex;
            flex-direction: row;
            min-height: calc(100vh - 56px); /* Adjust based on header height */
        }

        /* Sidebar styling */
        .sidebar-container {
            width: 70px; /* Match navbar width on small screens */
            flex-shrink: 0;
        }

        /* Main content styling */
        .content-container {
            flex-grow: 1;
            padding: 1rem;
        }

        /* Desktop styles (md and up) */
        @media (min-width: 768px) {
            .sidebar-container {
                width: 250px; /* Match navbar width on larger screens */
            }
            .content-container {
                padding: 1.5rem;
            }
            .page-title h2 {
                font-size: 1.75rem;
            }
        }

        /* Small screen adjustments */
        @media (max-width: 575.98px) {
            .content-container {
                padding: 0.5rem;
            }
            .page-title h2 {
                font-size: 1.25rem;
            }
            .card-body {
                padding: 0.75rem;
            }
            .form-group {
                margin-bottom: 0.75rem;
            }
            .btn {
                padding: 0.4rem 0.8rem;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container-fluid main-container mt-4">
        <!-- Sidebar -->
        <div class="sidebar-container mt-md-4 ">
            <?php include 'navbar.php'; ?>
        </div>

        <!-- Main Content -->
        <div class="content-container mx-lg-4">
            <div class="page-title goats_blue my-3">
                <h3 class="fw-bold">Vos informations personnelles</h3>
            </div>
            <div class="">
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger error-message"><?php echo $error; ?></div>
                    <?php endif; ?>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success success-message"><?php echo $success; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="row g-3">
                            <div class="col-12 col-sm-5">
                                <div class="form-group">
                                    <label for="nom_client">Nom</label>
                                    <input type="text" class="form-control" id="nom_client" name="nom_client" value="<?php echo htmlspecialchars($user['nom_client']); ?>" required>
                                </div>
                            </div>
                            <div class="col-12 col-sm-5">
                                <div class="form-group">
                                    <label for="prenom_client">Prénom</label>
                                    <input type="text" class="form-control" id="prenom_client" name="prenom_client" value="<?php echo htmlspecialchars($user['prenom_client']); ?>" required>
                                </div>
                            </div>
                            <div class="col-12 col-sm-10">
                                <div class="form-group">
                                    <label for="mail_client">Email</label>
                                    <input type="email" class="form-control" id="mail_client" name="mail_client" value="<?php echo htmlspecialchars($user['mail_client']); ?>" required>
                                </div>
                            </div>
                            <div class="col-12 col-sm-5">
                                <div class="form-group">
                                    <label for="old_password">Mot de passe actuel</label>
                                    <input type="password" class="form-control" id="old_password" name="old_password">
                                </div>
                            </div>
                            <div class="col-12 col-sm-5">
                                <div class="form-group">
                                    <label for="new_password">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Nouveau mot de passe">
                                    <small class="text-muted">Laissez vide pour conserver le mot de passe actuel</small>
                                </div>
                            </div>
                            <div class="col-12 mt-3">
                                <button type="submit" >Enregistrer</button>
                                <a href="profile.php" class="annuler">Annuler</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'; ?>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/index.js"></script>
    <script src="../assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="../assets/plugins/apexchart/chart-data.js"></script>
</body>
</html>