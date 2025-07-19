<?php
session_start();
include('db.php');
include('navbar.php');



// select les avis
$sql = "SELECT *
        FROM avis a
        JOIN client c ON a.id_client = c.id_client
        JOIN produits p ON a.id_produit = p.id_produit
        ORDER BY a.id_avis DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <title>Liste des Avis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Liste des Avis</h4>
                    <h6>Les avis laissés par les clients</h6>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table datanew">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Produit</th>
                                        <th>Note</th>
                                  
                                </thead>
                                <tbody>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id_avis'] ?></td>
                                            <td><?= htmlspecialchars($row['prenom_client'] . ' ' . $row['nom_client']) ?></td>
                                            <td><?= htmlspecialchars($row['lib_produit']) ?></td>
                                            <td>
                                                <?php
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $row['etoiles'] ? '<i class="bi bi-star-fill text-warning"></i>' : '<i class="bi bi-star text-muted"></i>';
                                                }
                                                ?>
                                            </td>
                                          
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">Aucun avis trouvé.</div>
                    <?php endif; ?>
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
    <script src="../assets/plugins/apexchart/chart-data.js"></script>
    <script src="../assets/plugins/select2/js/select2.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="../assets/js/jquery.dataTables.min.js"></script>
    <script src="../assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="../assets/js/custom-datatables.js"></script>

    <script>
        $(document).ready(function() {
            $('.delete-avis').on('click', function(e) {
                e.preventDefault();
                var avisId = $(this).data('id');

                Swal.fire({
                    title: 'Confirmation',
  text: 'Voulez-vous vraiment supprimer cet avis ?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Oui, supprimer !',
                    cancelButtonText: 'Annuler',
                    confirmButtonClass: 'btn btn-primary',
                    cancelButtonClass: 'btn btn-danger ml-1',
                    buttonsStyling: false
                }).then(function(result) {
                    if (result.isConfirmed) {
                        window.location.href = 'avis.php?delete=' + avisId;
                    }
                });
            });

            
            <?php if ($show_success): ?>
                Swal.fire({
                    title: 'Supprimé!',
                    text: 'L\'avis a été supprimé avec succès.',
                    icon: 'success',
                    confirmButtonClass: 'btn btn-primary',
                    buttonsStyling: false
                });
            <?php endif; ?>
        });
    </script>
</body>

</html>