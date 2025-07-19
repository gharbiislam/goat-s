<?php
include('db.php');
include('navbar.php');

// Vérification de l'ID de commande
if (!isset($_GET['order_id']) || !is_numeric($_GET['order_id'])) {
    die("ID de commande invalide.");
}

$order_id = intval($_GET['order_id']);

// Requête SQL pour récupérer les détails de la commande
$sql = "SELECT 
            c.*, 
            cmd.id_commande, cmd.date_commande, cmd.statut, cmd.total, cmd.date_livraison, cmd.ville, 
            cmd.rue, cmd.codePostal, cmd.telephone, cmd.commentaire, 
            p.lib_produit, p.desc_produit, p.prix_produit, 
            lc.quantite, lc.sous_total
        FROM 
            commande cmd
        JOIN 
            client c ON cmd.id_client = c.id_client
        JOIN 
            ligne_commande lc ON cmd.id_commande = lc.id_commande
        JOIN 
            produits p ON lc.id_produit = p.id_produit
        WHERE 
            cmd.id_commande = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Erreur de préparation de la requête: " . $conn->error);
}

$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Aucune commande trouvée avec cet ID.");
}

$order_data = $result->fetch_assoc();

// Calcul de la remise (20%)
$discount = $order_data['total'] * 0.20;
$total_with_discount = $order_data['total'] - $discount;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Détails de la commande #<?php echo $order_id; ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .bg-livree {
            background-color: #1E4494 !important;
            color: white;
        }
        .bg-encours {
            background-color: #8FC73E !important;
            color: white;
        }
        .bg-annulee {
            background-color: #DC3545 !important;
            color: white;
        }
        .btn {
            background-color: #8FC73E !important;
            color: white;
        }
        .product-placeholder {
            width: 50px;
            height: 50px;
            background-color: #e0e0e0;
            display: inline-block;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper"> 
        <div class="content"> 
            <div class="page-header">
                <div class="page-title">
                    <h3>Commande #<?php echo htmlspecialchars($order_data['id_commande']); ?></h3>
                </div>
                <div class="page-btn">
                    <a href="#" class="btn">
                        Supprimer
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-md-8">
                    <div class="card p-4">
                        <h2>Détails de la commande</h2>
                        <table class="table">
                            <thead class="table-light">
                                <tr>
                                    <th>Produit</th>
                                    <th>Prix</th>
                                    <th>Qte</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                mysqli_data_seek($result, 0);
                                while ($row = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>
                                        <div class="product-placeholder"></div>
                                        <strong><?= htmlspecialchars($row['lib_produit']) ?></strong><br>
                                    </td>
                                    <td><?= number_format($row['prix_produit'], 2) ?> TND</td>
                                    <td><?= $row['quantite'] ?></td>
                                    <td><?= number_format($row['sous_total'], 2) ?> TND</td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                        <div class="text-end mt-3">
                            <p>Sous-total : <strong><?= number_format($order_data['total'], 2) ?> TND</strong></p>
                            <p>Remise : <strong>20%</strong></p>
                            <p class="fw-bold fs-5">Total : <?= number_format($total_with_discount, 2) ?> TND</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card p-4 mb-3">
                        <h2>Détails du client</h2>
                        <p><strong><?= htmlspecialchars($order_data['prenom_client'] . ' ' . $order_data['nom_client']) ?></strong></p>
                        <p class="text-muted">ID Client #<?= htmlspecialchars($order_data['id_client']) ?></p>
                        <h6 class="mt-3">Informations de contact :</h6>
                        <p><i class="fas fa-envelope me-2"></i><?= htmlspecialchars($order_data['mail_client']) ?></p>
                        <p><i class="fas fa-phone me-2"></i><?= htmlspecialchars($order_data['telephone']) ?></p>
                        <p><i class="fas fa-comment me-2"></i><a href="#">Commentaires</a></p>
                    </div>

                    <div class="card p-4">
                        <h2>Adresse de livraison</h2>
                        <p><i class="fas fa-map-marker-alt me-2"></i><?= htmlspecialchars($order_data['rue']) ?></p>
                        <p><?= htmlspecialchars($order_data['codePostal'] . ' ' . $order_data['ville']) ?></p>
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
<?php
$stmt->close();
$conn->close();
?>