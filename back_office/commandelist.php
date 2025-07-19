<?php
ob_start(); // Start output buffering
include('db.php');
include('navbar.php');

// Ensure UTF-8 encoding for the database connection
$conn->set_charset("utf8");

// Traitement des actions POST (mise à jour du statut)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    if (isset($_POST['order_id']) && isset($_POST['new_status'])) {
        $order_id = $_POST['order_id'];
        $new_status = $_POST['new_status'];

        // Sanitize inputs to prevent SQL injection
        $order_id = $conn->real_escape_string($order_id);
        $new_status = $conn->real_escape_string($new_status);

        // Update the status in the database
        $update_query = "UPDATE commande SET statut = '$new_status' WHERE id_commande = '$order_id'";
        if ($conn->query($update_query)) {
            $_SESSION['message'] = "Statut mis à jour avec succès.";
        } else {
            $_SESSION['message'] = "Erreur lors de la mise à jour du statut.";
        }
    } else {
        $_SESSION['message'] = "Données de formulaire manquantes.";
    }

    // Redirect to refresh the page
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Fetch all orders
$query = "SELECT c.*, cl.nom_client, cl.prenom_client 
          FROM commande c
          JOIN client cl ON c.id_client = cl.id_client
          ORDER BY c.date_commande DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Gestion des commandes - GOATS</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap4.min.css">

    <style>
        /* Pour le statut "Livrée" */
        .bg-lightgreen {
            background-color: #8FC73E !important;
            color: white !important;
        }

        /* Pour le statut "En traitement" */
        .bg-lightblue {
            background-color: #1E4494 !important;
            color: white !important;
        }

        /* Pour le statut "En attente" */
        .bg-lightorange {
            background-color: #ff9966 !important;
            color: white !important;
        }

        /* Pour le statut "Annulée" */
        .bg-lightred {
            background-color: red !important;
            color: white !important;
        }

        /* Hover effect for the entire select box */
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #1E4494;
            color: white;
        }
    </style>
</head>
<body>

<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Gestion des commandes</h4>
                <h6>Afficher et gérer les commandes clients</h6>
            </div>
        </div>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>ID Commande</th>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?= htmlspecialchars($order['id_commande']); ?></td>
                                <td><?= date('d M Y', strtotime($order['date_commande'])); ?></td>
                                <td><?= htmlspecialchars($order['prenom_client'] . ' ' . $order['nom_client']); ?></td>
                                <td><?= number_format($order['total'], 2); ?> TND</td>
                                <td>
                                    <span class="badges 
                                        <?= $order['statut'] == 'Livrée' ? 'bg-lightgreen' : 
                                           ($order['statut'] == 'En traitement' ? 'bg-lightblue' : 
                                           ($order['statut'] == 'En attente' ? 'bg-lightorange' : 'bg-lightred')); ?>">
                                        <?= htmlspecialchars($order['statut']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="detail_commande.php?order_id=<?= htmlspecialchars($order['id_commande']); ?>">
                                        <img src="../assets/img/icons/eye1.svg" class="me-2" alt="img">
                                    </a>
                                    <a href="#" data-bs-toggle="modal" 
                                       data-bs-target="#editOrder<?= htmlspecialchars($order['id_commande']); ?>">
                                        <img src="../assets/img/icons/edit.svg" class="me-2" alt="img">
                                    </a>
                                </td>
                            </tr>

                            <!-- Modal de modification -->
                            <div class="modal fade" id="editOrder<?= htmlspecialchars($order['id_commande']); ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Modifier le statut</h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">×</span>
                                            </button>
                                        </div>
                                        <form method="POST">
                                            <div class="modal-body">
                                                <input type="hidden" name="order_id" value="<?= htmlspecialchars($order['id_commande']); ?>">
                                                <div class="form-group">
                                                    <label>Statut actuel</label>
                                                    <input type="text" class="form-control" value="<?= htmlspecialchars($order['statut']); ?>" readonly>
                                                </div>
                                                <div class="form-group">
                                                    <label>Nouveau statut</label>
                                                    <select name="new_status" class="form-control select">
                                                        <option value="En attente" <?= $order['statut'] == 'En attente' ? 'selected' : ''; ?>>En attente</option>
                                                        <option value="En traitement"  <?= $order['statut'] == 'En traitement' ? 'selected' : ''; ?>>En traitement</option>
                                                        <option value="Livrée" <?= $order['statut'] == 'Livrée' ? 'selected' : ''; ?>>Livrée</option>
                                                        <option value="Annulée" <?= $order['statut'] == 'Annulée' ? 'selected' : ''; ?>>Annulée</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" name="update_status" class="btn btn-submit">Mettre à jour</button>
                                                <button type="button" class="btn btn-cancel" data-bs-dismiss="modal">Annuler</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
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
        // Initialize Select2 for all select elements
        $('.select').select2();
    });
</script>

</body>
</html>
<?php
ob_end_flush(); // Flush the output buffer
?>