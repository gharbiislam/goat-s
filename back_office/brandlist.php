<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = intval($_POST['delete_id']);
    $stmt = $conn->prepare("DELETE FROM marque WHERE id_marque = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    $stmt->close();
    $conn->close(); 
    exit;
}

include('navbar.php');
$query = "SELECT * FROM marque";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Liste des marques</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/plugins/sweetalert/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Liste des marques</h4>
                    <h6>Gérer les marques</h6>
                </div>
                <div class="page-btn">
                    <a href="addbrand.php" class="btn btn-added">
                        <img src="../assets/img/icons/plus.svg" alt="img" class="me-1">Ajouter une nouvelle marque
                    </a>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Nom de la marque</th> 
                                    <th>Code</th>
                                    <th>Couverture de marque</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="brand-table-body">
                                <?php
                                while ($row = mysqli_fetch_assoc($result)) {
                                    echo "<tr id='brand-".$row['id_marque']."'>";
                                    echo "<td>" . htmlspecialchars($row['nom_marque']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['id_marque']) . "</td>";
                                    echo "<td><img src='" . htmlspecialchars($row['cover_marque']) . "' class='w-25'></td>";
                                    echo "<td>
                                            <a class='me-3' href='editbrand.php?id=" . $row['id_marque'] . "'>
                                                <img src='../assets/img/icons/edit.svg' alt='img'>
                                            </a>
                                            <a class='delete-brand' data-id='" . $row['id_marque'] . "' href='javascript:void(0);'>
                                                <img src='../assets/img/icons/delete.svg' alt='img'>
                                            </a>
                                          </td>";
                                    echo "</tr>";
                                }
                                mysqli_free_result($result);
                                $conn->close();
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
    $(document).ready(function () {
        $('.delete-brand').on('click', function () {
            var brandId = $(this).data('id');
            var row = $('#brand-' + brandId);

            Swal.fire({
                title: "Confirmer la suppression",
                text: "Voulez-vous vraiment supprimer cette marque?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Oui, supprimer",
                cancelButtonText: "Annuler"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: '',
                        data: { delete_id: brandId },
                        success: function (response) {
                            if (response.trim() === 'success') {
                                row.remove();
                                Swal.fire("Supprimé !", "La marque a été supprimée.", "success");
                            } else {
                                Swal.fire("Erreur", "La suppression a échoué.", "error");
                            }
                        },
                        error: function () {
                            Swal.fire("Erreur", "La requête a échoué.", "error");
                        }
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
