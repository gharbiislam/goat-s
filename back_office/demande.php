<?php
ob_start();
include('db.php');

// Handle delete action if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];

    // Delete the demand (no usage check needed unless demands are linked to other tables)
    $delete_query = "DELETE FROM demande WHERE id_demande = $id_to_delete";
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['success_message'] = "Demande supprimée avec succès";
        header("Location: demande.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression : " . mysqli_error($conn);
        header("Location: demande.php");
        exit();
    }
}

// Include navbar after deletion logic
include('navbar.php');

// Fetch demands
$query = "SELECT * FROM demande ORDER BY date_demande DESC";
$result = mysqli_query($conn, $query);

// Check for query errors
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
    <meta charset="UTF-8">
    <title>Liste des demandes</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
</head>

<body>
    <style>
        .text-truncate {
            max-width: 150px; 
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            display: inline-block;
            vertical-align: middle;
        }
    </style>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Liste des demandes</h4>
                    <h6>Gérer les demandes reçues</h6>
                </div>
            </div>

            <!-- Messages de notification -->
            <?php if ($error_message): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Titre</th>
                                    <th>Sujet</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                if ($result && mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) : ?>
                                        <tr>
                                            <td><?= $row['id_demande'] ?></td>
                                            <td><?= htmlspecialchars($row['nom_demande']) ?></td>
                                            <td class="text-truncate"><?= htmlspecialchars($row['mail_demande']) ?></td>
                                            <td><?= htmlspecialchars($row['titre_demande']) ?></td>
                                            <td class="text-truncate"><?= htmlspecialchars($row['sujet_demande']) ?></td>
                                            <td><?= htmlspecialchars($row['date_demande']) ?></td>
                                            <td>
                                                <a class="me-3" href="detaildemande.php?id=<?= $row['id_demande'] ?>">
                                                    <img src="../assets/img/icons/eye1.svg" alt="View">
                                                </a>
                                                <a class="delete-demand" href="#" data-id="<?= $row['id_demande'] ?>" title="Delete">
                                                    <img src="../assets/img/icons/delete.svg" alt="Delete">
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; 
                                } else {
                                    echo '<tr><td colspan="7" class="text-center">Aucune demande trouvée</td></tr>';
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
            $('.delete-demand').on('click', function(e) {
                e.preventDefault();
                var demandId = $(this).data('id');

                Swal.fire({
                    title: 'Confirmer la suppression',
                    text: 'Voulez-vous vraiment supprimer cette demande?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = 'demande.php?delete=' + demandId;
                    }
                });
            });

            // Afficher le message de succès si présent
            <?php if ($show_success): ?>
                Swal.fire({
                    title: 'Supprimé!',
                    text: 'La demande a été supprimée avec succès.',
                    icon: 'success',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false
                });
            <?php endif; ?>

            // Initialize DataTables
           
        });
    </script>
</body>

</html>
<?php
ob_end_flush();
?>