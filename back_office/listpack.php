<?php
ob_start();
session_start();
include('db.php');

// Handle delete action if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id_pack = (int)$_GET['delete'];

    // Delete the pack
    $sql = "DELETE FROM Pack WHERE id_pack = $id_pack";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['success_message'] = "Pack supprimé avec succès";
        header("Location: listpack.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Erreur lors de la suppression : " . mysqli_error($conn);
        header("Location: listpack.php");
        exit();
    }
}

// Include navbar after deletion logic
include('navbar.php');

// Récupération des packs
$sql = "SELECT * FROM Pack ORDER BY date_debut DESC";
$result = mysqli_query($conn, $sql);

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
    <meta charset="utf-8">
    <title>Liste des Packs</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Liste des Packs</h4>
                    <h6>Gérer les packs disponibles</h6>
                </div>
                <a href="ajoutpack.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajouter un pack</a>
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
                    <?php if ($result && $result->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table datanew table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Date début</th>
                                    <th>Date fin</th>
                                    <th>Total (DT)</th>
                                    <th>Image</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id_pack'] ?></td>
                                    <td><?= htmlspecialchars($row['nom_pack']) ?></td>
                                    <td><?= htmlspecialchars($row['date_debut']) ?></td>
                                    <td><?= htmlspecialchars($row['date_fin']) ?></td>
                                    <td><?= number_format($row['total_pack'], 2) ?> DT</td>
                                    <td>
                                        <?php if (!empty($row['cover_pack'])): ?>
                                            <img src="../assets/img/pack/<?= htmlspecialchars($row['cover_pack']) ?>" alt="Cover" style="height: 40px;">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="editpack.php?id=<?= $row['id_pack'] ?>" class="me-3">
                                            <img src="../assets/img/icons/edit.svg" alt="Modifier">
                                        </a>
                                        <a href="#" class="delete-pack" data-id="<?= $row['id_pack'] ?>" title="Supprimer">
                                            <img src="../assets/img/icons/delete.svg" alt="Supprimer">
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <p>Aucun pack trouvé.</p>
                    <?php endif; ?>
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
            $('.delete-pack').on('click', function(e) {
                e.preventDefault();
                var packId = $(this).data('id');

                Swal.fire({
                    title: 'Confirmer la suppression',
                    text: 'Voulez-vous vraiment supprimer ce pack?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = 'listpack.php?delete=' + packId;
                    }
                });
            });

            // Afficher le message de succès si présent
            <?php if ($show_success): ?>
                Swal.fire({
                    title: 'Supprimé!',
                    text: 'Le pack a été supprimé avec succès.',
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