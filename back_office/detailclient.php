<?php
include('db.php');
include('navbar.php');

// Get client ID from URL or session - you need to implement this properly
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($client_id <= 0) {
    die("Invalid client ID");
}

// Fetch client data with error handling
$client_query = "SELECT * FROM Client WHERE id_client = $client_id";
$client_result = mysqli_query($conn, $client_query);

if (!$client_result) {
    die("Database error: " . mysqli_error($conn));
}

$client = mysqli_fetch_assoc($client_result);

if (!$client) {
    die("Client not found");
}

// Fetch orders for this client with error handling
$orders_query = "SELECT * FROM Commande WHERE id_client = $client_id ORDER BY date_commande DESC";
$orders_result = mysqli_query($conn, $orders_query);

if (!$orders_result) {
    die("Database error: " . mysqli_error($conn));
}

$total_orders = mysqli_num_rows($orders_result);
$total_amount = 0;
$orders_data = []; 

while($order = mysqli_fetch_assoc($orders_result)) {
    $total_amount += $order['total'];
    $orders_data[] = $order; // Store order data
}

// Set default values for missing fields
$client['telephone'] = $client['telephone'] ?? '';
$client['gouvernorat'] = $client['gouvernorat'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Client Profile</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .client-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .stat-card {
           
            border-radius: 8px;
            padding: 15px;
         
        }
        .stat-icon {
    font-size: 15px;
    margin-right: 15px;
    color: #1E4494;
    background-color: #8FC73E;
    height: 40px;
    width: 40px;
    border-radius: 10px;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
} .bg-lightgreen {
           color: #8FC73E !important;
            
        }

        /* Pour le statut "En traitement" */
        .bg-lightblue {
        color: #1E4494 !important;
            
        }

        /* Pour le statut "En attente" */
        .bg-lightorange {
          color: #ff9966 !important;
            
        }

        /* Pour le statut "Annulée" */
        .bg-lightred {
            color: red !important;
          
        }


.stat-icon i {
    font-size: 22px;
}
     .details-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }
        .stat-content{
            line-height: 5px;
        }
    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content">
                <div class="page-header">
                    <div class="page-title">
                        <h4>Profile Client</h4>
                        <h6>Détails du client</h6>
                    </div>
                </div>
                <div class="d-flex ">
                <div class="client-card col-4 mx-2">
                    <div class="text-center">
                    <h4 class="fw-bold"><?php echo htmlspecialchars($client['prenom_client'] . ' ' . $client['nom_client']); ?></h4>
                    <p class="text-secondary">ID Client #<?php echo htmlspecialchars($client['id_client']); ?></p>
                    </div>
                    <div class="d-flex mt-3">
                        <div class="stat-card d-flex align-items-center" >
                            <div class="stat-icon">
                            <i class="bi bi-cart "></i>
                            </div>
                            <div class="stat-content">
                                <p class="fw-bold"><?php echo $total_orders; ?></p>
                                <p>Commandes</p>
                            </div>
                        </div>
                        
                        <div class="stat-card d-flex align-items-center">
                            <div class="stat-icon">
                            <i class="bi bi-currency-dollar"></i>
                            </div>
                            <div class="stat-content ">
                                <p class="fw-bold"><?php echo number_format($total_amount, 2); ?> </p>
                                <p>Total</p>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div>
                    <p class="fw-bold">Nom: <span class="fw-normal text-secondary"> <?php echo htmlspecialchars($client['nom_client']); ?></span></p>
                    <p class="fw-bold">Prénom:  <span class="fw-normal text-secondary"><?php echo htmlspecialchars($client['prenom_client']); ?></span></p>
                    <p class="fw-bold">Mail: <span class="fw-normal text-secondary"><?php echo htmlspecialchars($client['mail_client']); ?></span></p>
                  
                    <?php if (!empty($client['gouvernorat'])): ?>
                        <h6>Gouvernorat: <?php echo htmlspecialchars($client['gouvernorat']); ?></h6>
                    <?php endif; ?>
                    </div>
                </div> 
                
                <div class="details-section  col-8">
                    
                    
                 
                    
                <div class="table-responsive">
                   <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>Commande</th>
                                    <th>Date</th>
                                    <th>État</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($orders_data) > 0): ?>
                                    <?php foreach ($orders_data as $order): ?>
                                        <tr>
                                            <td>#<?php echo htmlspecialchars($order['id_commande']); ?></td>
                                            <td><?php echo date('d-m-Y', strtotime($order['date_commande'])); ?></td>
                                          <td>
                                    <span class=" 
                                        <?= $order['statut'] == 'Livrée' ? 'bg-lightgreen' : 
                                           ($order['statut'] == 'En traitement' ? 'bg-lightblue' : 
                                           ($order['statut'] == 'En attente' ? 'bg-lightorange' : 'bg-lightred')); ?>">
                                        <?= htmlspecialchars($order['statut']); ?>
                                    </span>
                                </td>
                                            <td><?php echo number_format($order['total'], 2); ?> TDN</td>
                                            <td><i class="fas fa-ellipsis-h"></i></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center">Aucune commande trouvée</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div></div>
    </div>   <!-- Scripts -->
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




<!-- Optionnel : scroller automatiquement vers la ligne cliquée -->

</body>
</html>