<?php
include("db.php");
include('navbar.php');

$query_client = "SELECT COUNT(*) AS total_client FROM client";  
$client = mysqli_query($conn, $query_client); 
$row_client = mysqli_fetch_assoc($client); 

$query_produit = "SELECT  COUNT(*) AS total_produit FROM produits";  
$produit = mysqli_query($conn, $query_produit); 
$row_produit = mysqli_fetch_assoc($produit); 

$query_cmd = "SELECT COUNT(id_commande) AS total_commande , SUM(total) AS Total FROM commande";  
$cmd = mysqli_query($conn, $query_cmd); 
$row_cmd = mysqli_fetch_assoc($cmd); 

$total_formatted = number_format($row_cmd['Total'], 2);

$query_recent = "SELECT p.*, i.image_path 
                FROM Produits p
                LEFT JOIN Images i ON p.id_produit = i.id_produit AND i.ordre = 1
                ORDER BY p.id_produit DESC 
                LIMIT 4";
$recent = mysqli_query($conn, $query_recent);

$query_chart = "SELECT date_commande, COUNT(*) as total_commandes 
                FROM commande 
                GROUP BY date_commande 
                ORDER BY date_commande ASC";
$result_chart = mysqli_query($conn, $query_chart);

// Préparer les données pour le graphique
$dates = [];
$commandes = [];

while ($row = mysqli_fetch_assoc($result_chart)) {
    $dates[] = $row['date_commande'];
    $commandes[] = $row['total_commandes'];
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
    <title>Astuce Update</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .stat-card-blue {
           background-color: white;
            color: #1E4494;
            border-radius:  10px;
            border:solid  #D9D9D9 1px ;
            padding: 15px;
            margin-bottom: 20px;
        }
        .stat-card-blue i{
            color: #1E4494;
            font-size: 1.5rem;     background:rgba(30, 68, 148, 0.2); 
            border-radius: 50px;
            padding: 10px;
        }
        .stat-card-green{
           background-color: white;
            color: #8FC73E;
            border-radius:  6px;
            border:solid  #D9D9D9 1px ;
            padding: 15px;
            margin-bottom: 20px;
        }
        .stat-card-green i{
            color: #8FC73E;
            font-size: 1.5rem;     background:rgba(143,200, 62, 0.2); 
            border-radius: 50px;
            padding: 10px;
        }
        .chart-container {
            background: white;
            padding: 20px;
            margin-bottom: 20px;
             border:solid  #D9D9D9 1px ;    
                     border-radius: 6px;

        }
        .recent-products {
            background: white;
            border-radius: 6px;
            padding: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border:solid  #D9D9D9 1px ;
        }
        .chart-placeholder {
            height: 300px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center; 
            color: #6c757d;
            border-radius: 6px;
        }
        .icon_blue{
            color: #1E4494;
            font-size: 2rem;     
        }

    </style>
</head>

<body>
    <div class="main-wrapper">
        <div class="page-wrapper">
            <div class="content container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="stat-card-blue">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="fw-bold"><?php echo htmlspecialchars($row_client['total_client']); ?></h3>
                                    <h5>Clients</h5>
                                </div>
                                <div>
                                <i class="bi bi-person "></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-green">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="fw-bold"><?php echo htmlspecialchars($row_produit['total_produit']); ?></h3>
                                    <h5>Produits</h5>
                                </div>
                                <div>
                                <i class="bi bi-box-seam"></i>                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-blue">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="fw-bold"><?php echo htmlspecialchars($row_cmd['total_commande']); ?></h3>
                                    <h5>Commandes</h5>
                                </div>
                                <div>
                                <i class="bi bi-cart"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card-green">
                            <div class="d-flex align-items-center justify-content-between">
                                <div>
                                    <h3 class="fw-bold"><?php echo $total_formatted; ?> </h3>
                                    <h5>Totales</h5>
                                </div>
                                <div>
                                <i class="bi bi-currency-dollar"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
  
                <div class="row">
                    <div class="col-md-8">
                        <div class="chart-container">
                            <h3>Statistiques des Ventes</h3>
                            <div id="chart-commande-par-jour" class="chart-placeholder"></div>

                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="recent-products">
                            <h3>Produits récemment ajoutés</h3>
                            <table class="table">
    <thead>
        <tr>
            <th>Image</th>
            <th>Produit</th>
            <th>Prix</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($recent)): ?>
        <tr>
            <td>
                <?php if(!empty($row['image_path'])): ?>
                    <img src="../<?php echo htmlspecialchars($row['image_path']); ?>" 
                         alt="<?php echo htmlspecialchars($row['lib_produit']); ?>" 
                         class="img-thumbnail" style="width: 50px; height: 50px; object-fit: cover;">
                <?php else: ?>
                    <div class="text-center">No image</div>
                <?php endif; ?>
            </td>
            <td><?php echo htmlspecialchars($row['lib_produit']); ?></td>
            <td><?php echo number_format($row['prix_produit'], 2); ?> TDN</td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        var options = {
            chart: {
                type: 'area',
                height: 350,
                zoom: { enabled: false },
                toolbar: { show: false }
            },
            series: [{
                name: "Commandes",
                data: <?php echo json_encode($commandes); ?>
            }],
            xaxis: {
                categories: <?php echo json_encode($dates); ?>,
                labels: {
                    rotate: -45,
                    style: {
                        fontSize: '12px',
                        colors: '#6c757d'
                    }
                },
                title: {
                    text: 'Date de commande'
                }
            },
            yaxis: {
                title: {
                    text: 'Nombre de commandes'
                },
                min: 0,
                forceNiceScale: true
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.4,
                    opacityTo: 0.1,
                    stops: [0, 90, 100]
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2,
                colors: ['#1E4494']
            },
            markers: {
                size: 0
            },
            grid: {
                strokeDashArray: 4
            },
            tooltip: {
                x: {
                    format: 'yyyy-MM-dd'
                }
            }
        };

        var chart = new ApexCharts(document.querySelector("#chart-commande-par-jour"), options);
        chart.render();
    });
</script>

  
        <!-- Scripts -->
        <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>