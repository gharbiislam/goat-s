<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Montserrat', sans-serif;
            margin: 0;
            padding: 0;
        }
        .footer {
    display: flex;
    justify-content: space-between;
    padding-top: 60px;
    padding-bottom: 40px;
    flex-wrap: wrap;
    gap: 30px; /* espace uniforme entre les colonnes */
}

.bloc {
    flex: 1;
    min-width: 280px;
    padding: 0 15px; /* marges internes uniformes pour chaque colonne */
}

.bloc:last-child {
    padding-right: 0; /* retire un peu d’espace du bloc "Suivez-nous" */
    margin-right: 0;
}

.container {
    padding-left: 30px !important;
    padding-right: 30px !important;
}


        footer {
            background-color: #1E4494;
            color: white;
            font-family: 'Montserrat', sans-serif;
            font-weight: 500;
            font-size: 13px;
        }

        .footer {
            padding-top: 60px;
            /* padding-bottom: 20px; */
        }

        .bloc {
            width: 30%;
        }

        .bloc img {
            max-width: 200px;
            margin-bottom: 20px;
        }

        .bloc h5 {
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .footer_link {
            color: white;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            display: block;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .footer_link:hover {
            text-decoration: underline;
        }

        .footer-icon {
            margin-right: 25px;
            font-size: 14px;
        }

        .social-icons .footer-icon {
            font-size: 18px;
            /* margin-right: 20px; */
        }

        .bloc ul li {
            margin-bottom: 10px;
            font-size: 13px;
        }

        .paiment {
            height: 30px;
            width: auto;
            margin-right: 20px;
        }

        .green-line {
            height: 2px;
            background-color: #8FC73E;
            /* margin-top: 10px; */
            margin-bottom: 10px;
        }

        .copyright {
            /* padding: 20px 0; */
            text-align: center;
        }

        .about_text {
            font-size: 13px;
            font-weight: 500;
            margin: 0;
            padding: 20px 0px;
        }

        @media (max-width: 992px) {
            .bloc {
                width: 100%;
                margin-bottom: 30px;
            }

            .footer {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <footer>
        <div class="container">
            <div class="footer row justify-content-between">
                <!-- Colonne de gauche (logo et liens) -->
                <div class="bloc">
                    <img src="../assets/img/logo/logo2.png" alt="GOAT'S" class="w-50">
                    <ul class="list-unstyled">
                        <li><a href="apropos.php" class="footer_link">A propos de nous</a></li>
                        <li><a href="commande.php" class="footer_link">Suivre ma commande</a></li>
                        <li><a href="remboursement.php" class="footer_link">Satisfait ou remboursé</a></li>
                    </ul>
                </div>

                <!-- Colonne du milieu (service client) -->
                <div class="bloc">
                    <h5>SERVICE CLIENT</h5>
                    <p>
                        Vous pouvez nous joindre du mardi au<br>
                        samedi de 10h00 à 13h00 et de 14h30 à<br>
                        19h00
                    </p>
                    <p>
                        <a href="tel:+21621158657" style="color: white; text-decoration: none;">
                            <i class="bi bi-telephone footer-icon"></i> +216 21 158 657
                        </a>
                    </p>
                    <p>
                        <a href="mailto:goats@gmail.com" style="color: white; text-decoration: none;">
                            <i class="bi bi-envelope footer-icon"></i> goats@gmail.com
                        </a>
                    </p>
                </div>

                <!-- Colonne de droite (réseaux sociaux et paiement) -->
                <div class="bloc">
                    <div>
                        <h5>SUIVEZ-NOUS</h5>
                        <div class="d-flex align-items-center social-icons">
                            <a href="#" style="color: white; text-decoration: none;">
                                <i class="fa fa-facebook footer-icon"></i>
                            </a>
                            <a href="#" style="color: white; text-decoration: none;">
                                <i class="fa fa-instagram footer-icon"></i>
                            </a>
                            <a href="#" style="color: white; text-decoration: none;">
                                <i class="bi bi-tiktok footer-icon"></i>
                            </a>
                        </div>
                    </div>
                    <div class="mt-5">
                        <h5>PAIEMENT SÉCURISÉE AVEC</h5>
                        <div class="d-flex mt-3">
                            <img src="../assets/img/visa.png" alt="Visa" class="paiment">
                            <img src="../assets/img/mastercard.png" alt="Mastercard" class="paiment">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ligne verte -->
        <div class="green-line"></div>

        <!-- Copyright -->
        <div class="copyright">
            <div class="container">
                <p class="text-center about_text">© 2025 GOATS tout droit reservés</p>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    </footer>
</body>

</html>