<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Satisfait ou remboursé </title> <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>::-webkit-scrollbar-track {
    background: #f1f1f1
}

::-webkit-scrollbar-thumb {
    background: #8ec63e
}

::-webkit-scrollbar-thumb:hover {
    background: #555
}

        body {
            background-color: #F9F9F9;
        }
        
        .green_goats {
            color: #8BC34A;
        }
        
        .blue_goats {
            color: #1A4083;
        }
        
        .accordion-item {
            background-color: white;
            border: none;
            border-radius: 5px;
            margin-bottom: 30px;
      
        }
        
        .accordion-button {
            background-color: white !important;
            box-shadow: none !important;
            border-radius: 8px !important;
     
        }
        
        .accordion-button::after {
            display: none; /* Hide default chevron */
        }
        
        .plus-icon, .minus-icon {
            font-size: 2rem;
            color: #8BC34A;
            font-weight: bold;
       
            -webkit-text-stroke: 0.5px;
       
        }
        
        .minus-icon {
            display: none;
        }
        
        .accordion-button:not(.collapsed) .plus-icon {
            display: none;
        }
        
        .accordion-button:not(.collapsed) .minus-icon {
            display: block;
        }
        
        .accordion-header button {
            font-weight: bold;
        border: none;
            padding: 1.5rem 1rem;
        }
        .desc{
            font-weight: 800px;
        }
    </style>
</head>

<body>
    <?php include 'header.php'?>

    <div class="container my-5">
        <h2 class="green_goats text-uppercase fw-bold text-center my-5 pt-5">Satisfait ou remboursé</h2>
        <h5 class=" text-black my-4">
            Nous voulons que vous soyez entièrement satisfait de votre achat. Si ce n'est pas le cas, voici nos conditions de retour pour chaque catégorie de produits.
    </h5>
        
        <div class="accordion" id="accordionRetour">
            <!-- Vêtements de sport -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        <span class="blue_goats">Vêtements de sport</span>
                        <span class="ms-auto">
                            <i class="bi bi-plus-lg plus-icon"></i>
                            <i class="bi bi-dash-lg minus-icon"></i>
                        </span>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" data-bs-parent="#accordionRetour">
                    <div class="accordion-body">
                        <ul class="list-unstyled">
                            <li class="mb-3"><span class="fw-bold">Délai de retour : </span>de 2 à 3 Jours</li>
                            <li class="mb-3"><span class="fw-bold">Conditions : </span>Les vêtements doivent être non portés avec étiquettes intactes</li>
                            <li class="mb-3">
                                <span class="fw-bold">Produits non retournables : </span>
                                <div>Articles personnalisés, sous-vêtements et maillots de bain pour des raisons d'hygiène</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Deuxième catégorie -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <span class="blue_goats">Nutrition sportive</span>
                        <span class="ms-auto">
                            <i class="bi bi-plus-lg plus-icon"></i>
                            <i class="bi bi-dash-lg minus-icon"></i>
                        </span>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionRetour">
                    <div class="accordion-body">
                        <ul class="list-unstyled">
                            <li class="mb-3"><span class="fw-bold">Délai de retour : </span>de 7 à 14 Jours</li>
                            <li class="mb-3"><span class="fw-bold">Conditions : </span>Produits non ouverts et dans leur emballage d'origine</li>
                            <li class="mb-3">
                                <span class="fw-bold">Produits non retournables : </span>
                                <div>Produits ouverts, endommagés ou dont la date de péremption est dépassée</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Troisième catégorie -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        <span class="blue_goats">Accessoires</span>
                        <span class="ms-auto">
                            <i class="bi bi-plus-lg plus-icon"></i>
                            <i class="bi bi-dash-lg minus-icon"></i>
                        </span>
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionRetour">
                    <div class="accordion-body">
                        <ul class="list-unstyled">
                            <li class="mb-3"><span class="fw-bold">Délai de retour : </span>de 5 à 10 Jours</li>
                            <li class="mb-3"><span class="fw-bold">Conditions : </span>Accessoires non utilisés dans leur emballage d'origine</li>
                            <li class="mb-3">
                                <span class="fw-bold">Produits non retournables : </span>
                                <div>Accessoires électroniques déballés, articles d'hygiène personnelle et équipements de protection</div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <?php include 'footer.php'?>   
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>