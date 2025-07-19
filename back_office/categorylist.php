<?php
// Start session at the top
session_start();
include('db.php');

// Handle delete action if requested
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Check if this category has subcategories
    $check_query = "SELECT COUNT(*) as count FROM categorie WHERE id_categorie_parent = $id";
    $check_result = mysqli_query($conn, $check_query);
    $has_children = false;

    if ($check_result) {
        $row = mysqli_fetch_assoc($check_result);
        if ($row['count'] > 0) {
            $has_children = true;
        }
    }

    if (!$has_children) {
        // Check if category is used in products
        $check_products = "SELECT COUNT(*) as count FROM produit WHERE id_categorie = $id";
        $products_result = mysqli_query($conn, $check_products);
        $has_products = false;

        if ($products_result) {
            $row = mysqli_fetch_assoc($products_result);
            if ($row['count'] > 0) {
                $has_products = true;
            }
        }

        if (!$has_products) {
            // Safe to delete
            $delete_query = "DELETE FROM categorie WHERE id_categorie = $id";
            if (mysqli_query($conn, $delete_query)) {
                $_SESSION['category_deleted'] = true;
                header("Location: categorylist.php");
                exit();
            } else {
                $_SESSION['error_message'] = "Unable to delete category: " . mysqli_error($conn);
                header("Location: categorylist.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Cannot delete this category because it is used in products.";
            header("Location: categorylist.php");
            exit();
        }
    } else {
        $_SESSION['error_message'] = "Cannot delete a category that has subcategories. Delete subcategories first.";
        header("Location: categorylist.php");
        exit();
    }
}

// Include navbar after deletion logic
include('navbar.php');

// Function to get subcategories of a given category
function getSubcategories($conn, $parent_id)
{
    $subcategories = [];
    $query = "SELECT id_categorie, libelle_categorie FROM categorie 
              WHERE id_categorie_parent = $parent_id ORDER BY libelle_categorie";
    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $subcategories[] = $row;
        }
    }

    return $subcategories;
}

// Get main categories (no parent)
$main_categories = [];
$query = "SELECT id_categorie, libelle_categorie FROM categorie 
          WHERE id_categorie_parent IS NULL ORDER BY libelle_categorie";
$result = mysqli_query($conn, $query);

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $main_categories[] = $row;
    }
}

// Check for success or error messages
$show_success = isset($_SESSION['category_deleted']) && $_SESSION['category_deleted'];
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : null;
if ($show_success) {
    unset($_SESSION['category_deleted']);
}
if ($error_message) {
    unset($_SESSION['error_message']);
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
    <title>Category List</title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Liste des catégories</h4>
                    <h6>Toutes les catégories et les sous-catégories</h6>
                </div>
                <div class="page-btn">
                    <a href="addcategory.php" class="btn btn-added"><img src="../assets/img/icons/plus.svg" alt="img" class="me-1">Ajouter nouvelle catégorie</a>
                </div>
            </div>

            <?php if ($error_message): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom catégorie</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($main_categories as $category): ?>
                                    <tr>
                                        <td><?= $category['id_categorie'] ?></td>
                                        <td class="fw-bold"><?= htmlspecialchars($category['libelle_categorie']) ?></td>
                                        <td><span class="">Catégorie</span></td>
                                        <td>
                                            <a class="me-3" href="editcategory.php?id=<?= $category['id_categorie'] ?>">
                                                <img src="../assets/img/icons/edit.svg" alt="Edit">
                                            </a>
                                            <a class="me-3 delete-category" href="#" data-id="<?= $category['id_categorie'] ?>" title="Delete">
                                                <img src="../assets/img/icons/delete.svg" alt="Delete">
                                            </a>
                                        </td>
                                    </tr>

                                    <?php
                                    // Get and display subcategories
                                    $subcategories = getSubcategories($conn, $category['id_categorie']);
                                    foreach ($subcategories as $subcategory):
                                    ?>
                                        <tr>
                                            <td><?= $subcategory['id_categorie'] ?></td>
                                            <td style="padding-left: 40px;">
                                                <i class="fas fa-level-down-alt me-2"></i>
                                                <?= htmlspecialchars($subcategory['libelle_categorie']) ?>
                                            </td>
                                            <td><span class="">Sous-catégorie</span></td>
                                            <td>
                                                <a class="me-3" href="editcategory.php?id=<?= $subcategory['id_categorie'] ?>">
                                                    <img src="../assets/img/icons/edit.svg" alt="Edit">
                                                </a>
                                                <a class="me-3 delete-category" href="#" data-id="<?= $subcategory['id_categorie'] ?>" title="Delete">
                                                    <img src="../assets/img/icons/delete.svg" alt="Delete">
                                    
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/index.js"></script>
    <script src="../assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="../assets/plugins/select2/js/select2.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="../assets/js/custom-datatables.js"></script>

    <script>
        $(document).ready(function() {
            // Gestion du clic sur le bouton de suppression
            $('.delete-category').on('click', function(e) {
                e.preventDefault();
                var categoryId = $(this).data('id');

                Swal.fire({
                     title: 'Confirmer la suppression',
  text: 'Voulez-vous vraiment supprimer cette catégorie ?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = 'categorylist.php?delete=' + categoryId;
                    }
                });
            });

            // Afficher le message de succès si présent
            <?php if ($show_success): ?>
                Swal.fire({
                    title: 'Supprimé!',
                    text: 'La catégorie a été supprimée avec succès.',
                    icon: 'success',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>