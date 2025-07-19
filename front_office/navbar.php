<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="User Dashboard">
    <meta name="keywords" content="dashboard, profile, user, account">
    <meta name="author" content="Admin">
    <meta name="robots" content="noindex, nofollow">
    <title>Sidebar</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="style.css">

    <style>
        /* Mobile first - icons only */
        .sidebars {
            position: sticky;
            top: 0;
            left: 0;
            width: 70px;
            background-color: #fff;
            border-right: 1px solid #e5e5e5;
            border-top: 1px solid #e5e5e5;
            min-height: 550px;
            height: 100%;
            border-radius: 6px;
     
            transition: width 0.3s ease;
        }
        
        .sidebars-menu {
            padding-bottom: 20px;
        }
        
        .sidebars-menu ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        
        .sidebars-menu li {
            margin-bottom: 5px;
            text-align: center;
        }
        
        .sidebars-menu a {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px 5px;
            color: #5B6E88;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .sidebars-menu i {
            margin-right: 0;
            width: 100%;
            text-align: center;
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .sidebars-menu span {
            display: none;
        }
        
        /* Active state styling */
        .sidebars-menu li.active a {
            background-color: #1E4494;
            color: white;
            font-weight: 600;
            margin: 10px 5px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        
        /* Desktop styles (md and up) */
        @media (min-width: 768px) {
            .sidebars {
                width: 250px;
                position: sticky;
                top: 0;
            }
            
            .sidebars-menu li {
                text-align: left;
            }
            
            .sidebars-menu a {
                flex-direction: row;
                padding: 10px 15px;
            }
            
            .sidebars-menu i {
                margin-right: 10px;
                width: 20px;
                margin-bottom: 0;
            }
            
            .sidebars-menu span {
                display: inline;
            }
            
            .sidebars-menu li.active a {
                margin: 10px;
            }
        }
    </style>
</head>
<body>

<div class="sidebars" id="sidebars">
    <div class="sidebars-inner">
        <div class="sidebars-menu">
            <ul class="">
                <li>
                    <a href="client.php"><i class="fa-regular fa-user"></i><span>Profile</span></a>
                </li>
                <li>
                    <a href="edit.php"><i class="fa-regular fa-user"></i><span>Mes informations</span></a>
                </li>
                <li>
                    <a href="commande.php"><i class="fa-solid fa-box"></i><span>Mes commandes</span></a>
                </li>
                <li>
                    <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i><span>Déconnexion</span></a>
                </li>
            </ul>
        </div>
    </div>
</div>

<script src="../assets/js/jquery-3.6.0.min.js"></script>
<script src="../assets/js/bootstrap.min.js"></script>

<script>
    $(document).ready(function() {
        // Set active state on click
        $('.sidebars-menu li').click(function() {
            $('.sidebars-menu li').removeClass('active');
            $(this).addClass('active');
        });

        // Optional: Set active state based on current page
        const currentPage = window.location.pathname.split('/').pop();
        $('.sidebars-menu li a').each(function() {
            if ($(this).attr('href') === currentPage) {
                $(this).parent().addClass('active');
            }
        });
    });
</script>
</body>
</html>