<?php
// Database connection
include('db.php');
include('header.php');
// Select all marques
$sql = "SELECT * FROM marque";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S Marques</title>
     <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
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
        .marque-card {
            position: relative;
            transition: transform 0.3s ease;
            cursor: pointer;
        }
        
        .marque-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            opacity: 0;
           
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }
        .marque-card:hover .overlay {
            opacity: 1;
        }
        .overlay-text {
            color:black;
            font-size: 14px;
            text-transform: uppercase;
            background-color: white;
            padding: 15px; width: 700px;
            text-align: center;
           
        }
        .green_goats{
color: #8FC73E;
    }
    </style>
</head>
<body>
 

    <div class="container my-5">
        <h2 class="text-center my-4 pt-4 green_goats fw-bold text-uppercase">Nos Marques Partenaires</h2>
        <div class="row">
            <?php
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo '
                    <div class="col-md-6 mb-4">
                        <div class="marque-card" onclick="window.location.href=\'filtre.php?id=' . $row['id_marque'] . '\'">
                            <img src="' . $row['cover_marque'] . '" alt="' . $row['nom_marque'] . '">
                            <div class="overlay">
                                <span class="overlay-text">découvrir plus <i class="bi bi-arrow-up-right px-2"></i></span>
                            </div>
                        </div>
                    </div>';
                }
            } else {
                echo '<p class="text-center">Aucune marque trouvée.</p>';
            }
            $conn->close();
            ?>
        </div>
    </div>

    <?php include('footer.php');?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>