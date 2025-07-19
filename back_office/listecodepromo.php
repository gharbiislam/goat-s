<?php
session_start();
include('db.php');

// Handle delete action if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];

    // Check if promo code is used (e.g., in orders, if applicable)
    // Adjust table name based on your schema (e.g., 'commande' for orders)
    $check_query = "SELECT COUNT(*) as count FROM commande WHERE id_codepromo = $id_to_delete";
    $check_result = mysqli_query($conn, $check_query);
    $has_usage = false;

    if ($check_result) {
        $row = mysqli_fetch_assoc($check_result);
        if ($row['count'] > 0) {
            $has_usage = true;
        }
    } else {
        $_SESSION['error_message'] = "Error checking promo code usage: " . mysqli_error($conn);
        header("Location: listecodepromo.php");
        exit();
    }

    if (!$has_usage) {
        // Safe to delete
        $delete_query = "DELETE FROM Code_Promo WHERE id_codepromo = $id_to_delete";
        if (mysqli_query($conn, $delete_query)) {
            $_SESSION['success_message'] = "Code promo supprimé avec succès";
            header("Location: listecodepromo.php");
            exit();
        } else {
            $_SESSION['error_message'] = "Erreur lors de la suppression : " . mysqli_error($conn);
            header("Location: listecodepromo.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Cannot delete this promo code because it is used in orders.";
        header("Location: listecodepromo.php");
        exit();
    }
}

// Include navbar after deletion logic
include('navbar.php');

// Récupération de la liste des codes promo
$sql = "SELECT * FROM Code_Promo ORDER BY date_expiration";
$result = mysqli_query($conn, $sql);

// Vérifier si la requête s'est bien exécutée
if (!$result) {
    $error_message = "Erreur lors de l'exécution de la requête : " . mysqli_error($conn);
}

// Check for success or error messages
$show_success = isset($_SESSION['success_message']) && $_SESSION['success_message'];
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : (isset($error_message) ? $error_message : null);
if ($show_success) {
    unset($_SESSION['success_message']);
}
if ($error_message) {
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="Liste des codes promo">
    <meta name="keywords" content="admin, promo, réduction, dashboard">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <title>Liste des codes promo</title>

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
                    <h4>Liste des codes promo</h4>
                    <h6>Gérer vos codes de réduction</h6>
                </div>
                <div class="page-btn">
                    <a href="codepromo.php" class="btn btn-added">
                        <i class="fa fa-plus"></i> Ajouter un code promo
                    </a>
                </div>
            </div>

            <!-- Messages de notification -->
            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <!-- Tableau des codes promo -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Code</th>
                                    <th>Réduction (%)</th>
                                    <th>Date de lancement</th>
                                    <th>Date d'expiration</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result && $result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        $today = date('Y-m-d');
                                        $status = '';
                                        $status_class = '';
                                        
                                        if ($today < $row['date_lancement']) {
                                            $status = 'À venir';
                                            $status_class = 'bg-warning';
                                        } elseif ($today > $row['date_expiration']) {
                                            $status = 'Expiré';
                                            $status_class = 'bg-danger';
                                        } else {
                                            $status = 'Actif';
                                            $status_class = 'bg-success';
                                        }
                                ?>
                                <tr>
                                    <td><?php echo $row['id_codepromo']; ?></td>
                                    <td><?php echo htmlspecialchars($row['code']); ?></td>
                                    <td><?php echo number_format($row['reduction'], 2); ?> %</td>
                                    <td><?php echo date('d/m/Y', strtotime($row['date_lancement'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($row['date_expiration'])); ?></td>
                                    <td><span class="badge <?php echo $status_class; ?>"><?php echo $status; ?></span></td>
                                    <td>
                                        <a class="me-3" href="editcodepromo.php?id=<?php echo $row['id_codepromo']; ?>">
                                            <img src="../assets/img/icons/edit.svg" alt="Edit">
                                        </a>
                                        <a class="delete-promo" href="#" data-id="<?php echo $row['id_codepromo']; ?>" title="Delete">
                                            <img src="../assets/img/icons/delete.svg" alt="Delete">
                                        </a>
                                    </td>
                                </tr>
                                <?php
                                    }
                                } else {
                                    echo '<tr><td colspan="7" class="text-center">Aucun code promo trouvé</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
  <!-- Scripts -->
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
    <script src="../assets/js/custom-datatables.js"></script>
    <script>
        $(document).ready(function() {
            // Gestion du clic sur le bouton de suppression
            $('.delete-promo').on('click', function(e) {
                e.preventDefault();
                var promoId = $(this).data('id');

                Swal.fire({
                          title: 'Confirmer la suppression',
  text: 'Voulez-vous vraiment supprimer ce code promo ?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = 'listecodepromo.php?delete=' + promoId;
                    }
                });
            });

            // Afficher le message de succès si présent
            <?php if ($show_success): ?>
                Swal.fire({
                    title: 'Supprimé!',
                    text: 'Le code promo a été supprimé avec succès.',
                    icon: 'success',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false
                });
            <?php endif; ?>

        
        });
    </script>
</body>
</html>
<?php
ob_end_flush();
?>