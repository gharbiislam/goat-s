<?php

include('db.php');

session_start();





$google_client_id = "355056512501-fhtho9prt85g18cd8r0vql5aof126php.apps.googleusercontent.com";

$google_client_secret = "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";

$google_redirect_uri = "http://localhost/dashboard/PFE/front_office/google_callback.php";





if (isset($_GET['code'])) {

    $code = $_GET['code'];



   

    $post_data = [

        'code' => $code,

        'client_id' => $google_client_id,

        'client_secret' => $google_client_secret,

        'redirect_uri' => $google_redirect_uri,

        'grant_type' => 'authorization_code'

    ];



    $ch = curl_init("https://oauth2.googleapis.com/token");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));

    curl_setopt($ch, CURLOPT_POST, true);

    $response = curl_exec($ch);

    curl_close($ch);



    $token_data = json_decode($response, true);



    if (isset($token_data['access_token'])) {

        $access_token = $token_data['access_token'];



       

        $ch = curl_init("https://www.googleapis.com/oauth2/v2/userinfo");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $access_token"]);

        $user_info_response = curl_exec($ch);

        curl_close($ch);



        $user_info = json_decode($user_info_response, true);



        if (isset($user_info['email'])) {

            $email = mysqli_real_escape_string($conn, $user_info['email']);

            $check_query = "SELECT * FROM client WHERE mail_client = '$email'";

            $check_result = mysqli_query($conn, $check_query);



            if (mysqli_num_rows($check_result) > 0) {

                $user = mysqli_fetch_assoc($check_result);

                $_SESSION['client_id'] = $user['id_client'];

                $_SESSION['client_nom'] = $user['nom_client'];

                $_SESSION['client_prenom'] = $user['prenom_client'];

                $_SESSION['client_email'] = $user['mail_client'];

            } else {

                $nom = isset($user_info['family_name']) ? mysqli_real_escape_string($conn, $user_info['family_name']) : '';

                $prenom = isset($user_info['given_name']) ? mysqli_real_escape_string($conn, $user_info['given_name']) : '';

                $random_password = bin2hex(random_bytes(8));

                $hashed_password = password_hash($random_password, PASSWORD_DEFAULT);



                $insert_query = "INSERT INTO client (nom_client, prenom_client, mail_client, mdp_client, actif) 

                                 VALUES ('$nom', '$prenom', '$email', '$hashed_password', 1)";

                $insert_result = mysqli_query($conn, $insert_query);



                if ($insert_result) {

                    $_SESSION['client_id'] = mysqli_insert_id($conn);

                    $_SESSION['client_nom'] = $nom;

                    $_SESSION['client_prenom'] = $prenom;

                    $_SESSION['client_email'] = $email;

                } else {

                    $_SESSION['error'] = "Erreur lors de la création du compte: " . mysqli_error($conn);

                    header("Location: signup.php");

                    exit();

                }

            }



            header("Location: client.php");

            exit();

        } else {

            $_SESSION['error'] = "Impossible de récupérer les informations utilisateur.";

            header("Location: signup.php");

            exit();

        }

    } else {

        $_SESSION['error'] = "Erreur lors de l'obtention du jeton.";

        header("Location: signup.php");

        exit();

    }

} else {

    header("Location: signup.php");

    exit();

}