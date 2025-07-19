<?php
include 'db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_client'])) {
    header("Location: login.php");
    exit();
}

$id_client = $_SESSION['id_client'];

// Get all orders for the current client
$query = "SELECT c.*, 
          COUNT(lc.id_produit) as nombre_produits 
          FROM commande c 
          LEFT JOIN ligne_commande lc ON c.id_commande = lc.id_commande 
          WHERE c.id_client = ? 
          GROUP BY c.id_commande 
          ORDER BY c.date_commande DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $id_client);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$commandes = mysqli_fetch_all($result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Mes Commandes </title>
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>::-webkit-scrollbar {
    width: 5px;
    height: 5px;
    border-radius: 50px !important
}

::-webkit-scrollbar-track {
    background: #f1f1f1
}

::-webkit-scrollbar-thumb {
    background: #8FC73F
}

::-webkit-scrollbar-thumb:hover {
    background: #555
}
        .goats_blue { color: #1E4494; }
        .green_goats { color: #8FC73E; }
        .main-container { display: flex; flex-direction: row; min-height: calc(100vh - 56px); }
        .sidebar-container { width: 70px; flex-shrink: 0; }
        .content-container { flex-grow: 1; padding: 1rem; }
        .card-header { background-color: #f8f9fa; padding: 0.75rem; display: flex; justify-content: space-between; align-items: center; }
        .card-header h5 { margin: 0; font-size: 1rem; }
        .table { margin-bottom: 0; border: solid #E7E7E7 1px; }
        .table th, .table td { border: none; padding: 0.5rem; vertical-align: middle; }
        .table th { font-weight: normal; }
        .table tbody tr td { background-color: #E8F3D8 !important; border-bottom: solid white 10px; border-top: 10px solid white; }
        .badge { font-size: 0.9rem; padding: 0.25rem 0.5rem; }
        .img-thumbnail { max-width: 65px; max-height: 100px; object-fit: cover; margin-right: 0.5rem; }
        .text-muted { font-size: 0.8rem; }
        .toggle-details { padding: 0; }
        span { font-weight: 600; }
        /* Styles pour les statuts */
        .statut-en-attente {
            color: #FFD700; /* Texte noir pour lisibilité */
          
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .statut-en-traitement {
            color: #1A3C6D; /* Texte blanc pour lisibilité */
         
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .statut-annuler {
          
            color: #FF0000; /* Texte blanc pour lisibilité */
           
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        .statut-livree {
            color:#8EC63E; /* Texte noir pour lisibilité */
           
            padding: 0.25rem 0.5rem;
            border-radius: 4px;
        }
        @media (min-width: 768px) {
            .sidebar-container { width: 250px; }
            .content-container { padding: 1.5rem; }
            .card-header h5 { font-size: 1.25rem; }
            .table th, .table td { font-size: 1rem; padding: 0.75rem; }
            .img-thumbnail { width: 80px; height: 80px; }
        }
        @media (max-width: 575.98px) {
            .content-container { padding: 0.5rem; }
            .card-header h5 { font-size: 0.9rem; }
            .table { font-size: 0.85rem; }
            .img-thumbnail { width: 50px; height: 50px; }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container-fluid main-container mt-4">
        <div class="sidebar-container mt-md-4"><?php include 'navbar.php'; ?></div>
        <div class="content-container">
            <h3 class="mb-4 fw-bold goats_blue">Historique de vos commandes</h3>

            <?php if (empty($commandes)): ?>
                <div class="alert alert-info">Vous n'avez pas encore passé de commande.</div>
            <?php else: ?>
                <?php foreach ($commandes as $commande): ?>
                    <div class="card mb-4" >
                        <div class="card-header p-4">
                            <h5 class="goats_blue">
                                Commande #<?php echo $commande['id_commande']; ?> - <?php echo date('Y-m-d H:i:s', strtotime($commande['date_commande'])); ?> 
                                <span>- Statut : </span>
                                <?php
                                switch ($commande['statut']) {
                                    case 'En attente':
                                        echo '<span class="statut-en-attente">En attente</span>';
                                        break;
                                    case 'En traitement':
                                        echo '<span class="statut-en-traitement">En traitement</span>';
                                        break;
                                    case 'annuler':
                                        echo '<span class="statut-annuler">Annuler</span>';
                                        break;
                                    case 'Livrée':
                                        echo '<span class="statut-livree">Livrée</span>';
                                        break;
                                    default:
                                        echo '<span class="badge bg-secondary">' . htmlspecialchars($commande['statut']) . '</span>';
                                        break;
                                }
                                ?>
                            </h5>
                            <button class="btn btn-link toggle-details" data-bs-toggle="collapse" data-bs-target="#details-<?php echo $commande['id_commande']; ?>">
                                <i class="fas fa-chevron-down"></i>
                            </button>
                        </div>
                        <div id="details-<?php echo $commande['id_commande']; ?>" class="collapse">
                            <div class="card-body ">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Détails produits</th>
                                                <th class="text-center">Prix unitaire</th>
                                                <th class="text-center">Quantité</th>
                                                <th class="text-end">Montant</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $query_details = "SELECT lc.*, p.lib_produit, p.prix_produit, 
                                            (SELECT image_path FROM images WHERE id_produit = p.id_produit LIMIT 1) as image 
                                            FROM ligne_commande lc 
                                            JOIN produits p ON lc.id_produit = p.id_produit 
                                            WHERE lc.id_commande = ?";
                                            $stmt_details = mysqli_prepare($conn, $query_details);
                                            mysqli_stmt_bind_param($stmt_details, "i", $commande['id_commande']);
                                            mysqli_stmt_execute($stmt_details);
                                            $result_details = mysqli_stmt_get_result($stmt_details);
                                            $details = mysqli_fetch_all($result_details, MYSQLI_ASSOC);
                                            $total = 0;
                                            foreach ($details as $detail):
                                                $total += $detail['sous_total'];
                                            ?>
                                                <tr>
                                                    <td>
                                                        <div class="d-flex align-items-center">
                                                            <?php if (!empty($detail['image'])): ?>
                                                                <img src="<?php echo '../' . $detail['image']; ?>" alt="<?php echo $detail['lib_produit']; ?>" class="img-thumbnail">
                                                            <?php else: ?>
                                                                <div class="bg-light" style="width: 60px; height: 60px; display: flex; align-items: center; justify-content: center;">
                                                                    <span class="">No image</span>
                                                                </div>
                                                            <?php endif; ?>
                                                            <div>
                                                                <h6 class="mb-0 text-dark"><?php echo $detail['lib_produit']; ?></h6>
                                                                <?php
                                                                $query_variant = "SELECT * FROM alimentaire WHERE id_produit = ?";
                                                                $stmt_variant = mysqli_prepare($conn, $query_variant);
                                                                mysqli_stmt_bind_param($stmt_variant, "i", $detail['id_produit']);
                                                                mysqli_stmt_execute($stmt_variant);
                                                                $result_variant = mysqli_stmt_get_result($stmt_variant);
                                                                $variant = mysqli_fetch_assoc($result_variant);
                                                                if ($variant) {
                                                                    echo '<small class="text-muted">Format: ' . $variant['format'] . '<br>Saveur: ' . $variant['saveur'] . '</small>';
                                                                } else {
                                                                    $query_variant = "SELECT * FROM vetement WHERE id_produit = ?";
                                                                    $stmt_variant = mysqli_prepare($conn, $query_variant);
                                                                    mysqli_stmt_bind_param($stmt_variant, "i", $detail['id_produit']);
                                                                    mysqli_stmt_execute($stmt_variant);
                                                                    $result_variant = mysqli_stmt_get_result($stmt_variant);
                                                                    $variant = mysqli_fetch_assoc($result_variant);
                                                                    if ($variant) {
                                                                        echo '<small class="text-muted">Taille: ' . $variant['taille'] . '<br>Couleur: ' . $variant['couleur'] . '</small>';
                                                                    }
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="text-center text-dark"><?php echo number_format($detail['sous_total'] / $detail['quantite'], 2); ?> TND</td>
                                                    <td class="text-center text-dark"><?php echo $detail['quantite']; ?></td>
                                                    <td class="text-end text-dark"><?php echo number_format($detail['sous_total'], 2); ?> TND</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold">Total :</td>
                                                <td class="text-end fw-bold"><?php echo number_format($commande['total'], 2); ?> TND</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-details').forEach(button => {
                const icon = button.querySelector('i');
                const collapse = document.querySelector(button.getAttribute('data-bs-target'));

                // Événement lorsque le collapse s'ouvre
                collapse.addEventListener('show.bs.collapse', function() {
                    icon.classList.remove('fa-chevron-down');
                    icon.classList.add('fa-chevron-up');
                });

                // Événement lorsque le collapse se ferme
                collapse.addEventListener('hide.bs.collapse', function() {
                    icon.classList.remove('fa-chevron-up');
                    icon.classList.add('fa-chevron-down');
                });
            });
        });
    </script>
</body>
</html>