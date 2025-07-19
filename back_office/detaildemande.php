<?php
include('db.php');
include('navbar.php');

$id_demande = isset($_GET['id']) ? intval($_GET['id']) : 0;
$query = "SELECT * FROM demande WHERE id_demande = $id_demande";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);


if (isset($_GET['delete'])) {
    $id_to_delete = intval($_GET['delete']);

    $delete_query = "DELETE FROM demande WHERE id_demande = $id_to_delete";
    if (mysqli_query($conn, $delete_query)) {
        header("Location:demande.php");
        exit;
    } else {
        die("Erreur lors de la suppression : " . mysqli_error($conn));
    }
}
?>
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Détail Demande</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">

    <style>
        .card {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            padding: 20px;
            background: white;
        }
        .btn-delete {
            background-color: #8FC73E;
            color: white;
        }

    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content">

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold">Demande #<?= $row['id_demande'] ?></h4>
                    <form method="get" action="deletedemande.php" onsubmit="return confirm('Confirmer la suppression ?');">
                        <input type="hidden" name="id" value="<?= $row['id_demande'] ?>">
                        <a class='btn btn-delete' href='?delete=<?= $row['id_demande'] ?>' onclick="return confirm('Voulez-vous vraiment supprimer ce client ?')">
                                                Supprimer
                                            </a>
                    </form>
                </div>

                <div class="row">
                    <div class="col-md-8 mb-3">
                        <div class="card"> <h5 class="mb-3"><strong>Détails de la Demande</strong></h5>
                            <div class="d-flex justify-content-between align-items-center my-2 border-bottom py-1">
                                
                                <p> <strong> <?= htmlspecialchars($row['titre_demande']) ?></strong></p>
                                <?= date('M d, Y', strtotime($row['date_demande'])) ?>
                            </div>
                           
                            <p class="mt-2"><?= nl2br(htmlspecialchars($row['sujet_demande'])) ?></p>
                        </div>
                    </div>

                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <h5 class="mb-3"><strong>Détails du demandeur</strong></h5>
                            <p class="mb-1"><?= htmlspecialchars($row['nom_demande']) ?></p>
                            <p class="text-muted">Mail : <a class="text-muted text-decoration-none" href="mailto:<?= htmlspecialchars($row['mail_demande']) ?>"><?= htmlspecialchars($row['mail_demande']) ?></a></p>
                        </div>
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

</body>
</html>
