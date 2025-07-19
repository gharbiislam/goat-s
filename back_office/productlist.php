<?php
include('db.php');

// Handle delete action if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id_to_delete = (int)$_GET['delete'];

    // Start transaction to ensure all deletions succeed
    mysqli_begin_transaction($conn);

    try {
        // Delete from related tables
        $delete_images = "DELETE FROM Images WHERE id_produit = $id_to_delete";
        $delete_alimentaire = "DELETE FROM Alimentaire WHERE id_produit = $id_to_delete";
        $delete_vetement = "DELETE FROM Vetement WHERE id_produit = $id_to_delete";
        $delete_accessoire = "DELETE FROM Accessoire WHERE id_produit = $id_to_delete";

        mysqli_query($conn, $delete_images);
        mysqli_query($conn, $delete_alimentaire);
        mysqli_query($conn, $delete_vetement);
        mysqli_query($conn, $delete_accessoire);

        // Delete the product
        $delete_query = "DELETE FROM Produits WHERE id_produit = $id_to_delete";
        mysqli_query($conn, $delete_query);

        // Commit transaction
        mysqli_commit($conn);

        $_SESSION['success_message'] = "Produit supprimé avec succès";
        header("Location: productlist.php");
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($conn);
        $_SESSION['error_message'] = "Erreur lors de la suppression : " . mysqli_error($conn);
        header("Location: productlist.php");
        exit();
    }
}

// Include navbar after deletion logic
include('navbar.php');

// Fetch products with joins
$query = "SELECT p.*, c.libelle_categorie, m.nom_marque 
          FROM Produits p
          JOIN Categorie c ON p.id_categorie = c.id_categorie
          JOIN Marque m ON p.id_marque = m.id_marque";
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
    <meta charset="utf-8">
    <title>Liste des produits</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <!-- Styles -->
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">
    <style>
        .badge-promo {
            background-color: #dc3545;
            color: white;
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 12px;
        }
        .price-old {
            text-decoration: line-through;
            color: #6c757d;
            font-size: 0.9em;
        }
        .price-new {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Liste des produits</h4>
                <h6>Gérer vos produits</h6>
            </div>
            <div class="page-btn">
                <a href="produit.php" class="btn btn-added">
                    <img src="../assets/img/icons/plus.svg" alt="img" class="me-1">Ajouter un produit
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

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>Nom du produit</th>
                                <th>Catégorie</th>
                                <th>Marque</th>
                                <th>Prix</th>
                                <th>Promotion</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['lib_produit']) ?></td>
                                        <td><?= htmlspecialchars($row['libelle_categorie']) ?></td>
                                        <td><?= htmlspecialchars($row['nom_marque']) ?></td>
                                        <td>
                                            <?php if ($row['en_promo'] == 1): ?>
                                                <span class="price-old"><?= number_format($row['prix_produit'], 2) ?> DT</span><br>
                                                <span class="price-new">
                                                    <?= number_format($row['prix_produit'] * (1 - $row['taux_reduction']/100), 2) ?> DT
                                                </span>
                                            <?php else: ?>
                                                <?= number_format($row['prix_produit'], 2) ?> DT
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($row['en_promo'] == 1): ?>
                                                <span class="badge-promo">
                                                    -<?= $row['taux_reduction'] ?>%
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">Non</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>  <a href="detail_produit.php?id=<?= htmlspecialchars($row['id_produit']); ?>">
                                        <img src="../assets/img/icons/eye1.svg" class="me-2" alt="img">
                                    </a>
                                            <a class="me-3" href="editproduct.php?id=<?= $row['id_produit'] ?>">
                                                <img src="../assets/img/icons/edit.svg" alt="Modifier">
                                            </a>
                                            <a class="delete-product" href="#" data-id="<?= $row['id_produit'] ?>" title="Supprimer">
                                                <img src="../assets/img/icons/delete.svg" alt="Supprimer">
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; 
                            } else {
                                echo '<tr><td colspan="6" class="text-center">Aucun produit trouvé</td></tr>';
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
        // Gestion du clic sur le bouton de suppression avec event delegation
        $(document).on('click', '.delete-product', function(e) {
            e.preventDefault();
            var productId = $(this).data('id');
            console.log('Delete button clicked for product ID:', productId); // Debug log

            if (!productId) {
                console.error('Product ID is undefined or missing');
                return;
            }

            Swal.fire({
                title: 'Confirmer la suppression',
                text: 'Voulez-vous vraiment supprimer ce produit?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Oui, supprimer !',
                cancelButtonText: 'Annuler',
                confirmButtonClass: 'btn btn-primary',
                cancelButtonClass: 'btn btn-danger ml-1',
                buttonsStyling: false
            }).then(function(result) {
                if (result.isConfirmed) {
                    console.log('Redirecting to delete URL:', 'productlist.php?delete=' + productId); // Debug log
                    window.location.href = 'productlist.php?delete=' + productId;
                }
            });
        });

        // Afficher le message de succès si présent
        <?php if ($show_success): ?>
            Swal.fire({
                title: 'Supprimé!',
                text: 'Le produit a été supprimé avec succès.',
                icon: 'success',
                confirmButtonClass: 'btn btn-primary',
                buttonsStyling: false
            });
        <?php endif; ?>

        // Initialize DataTables only if not already initialized
        
    });
</script>

</body>
</html>

<?php mysqli_close($conn); ?>
<?php ob_end_flush(); ?>