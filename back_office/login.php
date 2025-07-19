<?php
session_start();
include('db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $mdp = $_POST['motdepasse'];

    $query = "SELECT * FROM admin WHERE mail_admin='$email' AND mdp_admin='$mdp'";
    $result = mysqli_query($conn, $query);
    
    if (mysqli_num_rows($result) == 1) {
        $admin = mysqli_fetch_assoc($result);
        $_SESSION['id_admin'] = $admin['id_admin'];
        
        if (isset($_POST['stay_connected'])) {
            setcookie("admin_logged_in", $admin['id_admin'], time() + (86400 * 30), "/"); // expires in 30 days
        }
        
        header("Location:dashboard.php");
    } else {
        echo "<script>alert('Email ou mot de passe incorrect');</script>";
    }
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
    <title>Connexion admin</title>
    
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .background {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url(../assets/img/backgroundadmin.jpg);
    background-size: cover;
    background-position: center;
    z-index: -1;
}

.background::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5); /* Black overlay with opacity */
    z-index: 1;
}

        .login-container {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 10px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .login-container img {
            max-width: 150px;
            margin-bottom: 20px;
        }

        input[type="email"], input[type="password"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #8FC73F;
            border: none;
            border-radius: 5px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #8FC73F;
        }

        .forgot-password {
            margin-top: 10px;
            font-size: 14px;
        }

        .forgot-password a {
            color: #8FC73F;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }

        .stay-connected {
            margin-top: 15px;
            text-align: left;
        }

        .stay-connected input {
            width: auto;
        }
    </style>
</head>
<body>

<div class="background"></div> 

<div class="login-container">
    <img src="../assets/img/logo/logo.png" alt="Logo">
    <form method="POST" action="">
        <input type="email" name="email" placeholder="Email" required class="form-control">
        <input type="password" name="motdepasse" placeholder="Mot de passe" required class="form-control">
        
        <div class="stay-connected">
            <label for="stay_connected">
                <input type="checkbox" name="stay_connected" id="stay_connected"> Rester connecté
            </label>
        </div>
        
        <button type="submit" class="btn btn-submit my-2">Se connecter</button>
        
        <div class="forgot-password">
            <a href="#">Mot de passe oublié ?</a>
        </div>
    </form>
</div>

</body>
</html>
