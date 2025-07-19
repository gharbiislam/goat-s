<?php
include('db.php');
session_start();

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize inputs
    $nom = $conn->real_escape_string($_POST['nom']);
    $email = $conn->real_escape_string($_POST['email']);
    $titre = $conn->real_escape_string($_POST['titre']);
    $sujet = $conn->real_escape_string($_POST['sujet']);
    $date = date('Y-m-d');

    // Prepare SQL statement
    $sql = "INSERT INTO demande (nom_demande, mail_demande, titre_demande, sujet_demande, date_demande) 
            VALUES ('$nom', '$email', '$titre', '$sujet', '$date')";

    // Execute query
    if ($conn->query($sql) === TRUE) {
        $_SESSION['form_submitted'] = true;
        header("Location: demande.php?success=1");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GOAT'S - Nous Contacter</title>
        <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Arial', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        ul {
            list-style: none;
        }

        .main-nav {
            background-color: #fff;
            border-top: 1px solid #eee;
        }

        .main-nav ul {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
        }

        .main-nav li {
            position: relative;
        }

        .main-nav a {
            color: #1e3a8a;
            font-weight: bold;
            padding: 10px 15px;
            display: inline-block;
        }

        main {
            flex-grow: 1;
        }

        .contact-background {
            background-image: url("../assets/img/cover/demande_cover.png");
            background-size: cover;
            background-position: center;
            min-height: 900px;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 50px 0;
            position: relative;
        }

        .contact-background::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #132346;
            opacity: 0.5;
            z-index: 1;
        }

        .contact-form-container {
            background-color: white;
            border-radius: 10px;
            padding: 30px;
            width: 100%;
            max-width: 715px;
            position: relative;
            z-index: 2;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
            margin-bottom: 20px;
        }

  .form-group input,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: none;
    border-bottom: 2px solid #AFABAB;
    font-size: 16px;
    outline: none; /* Optional: removes default browser focus outline */
}

.form-group input:focus,
.form-group textarea:focus {
    border-bottom: 3px solid #8FC73F;
}


        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn-submit {
            background-color: #8FC73F;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 19px;
            cursor: pointer;
            transition: background-color 0.3s;
            display: block;
            margin: 0 auto;
            width: 280px;
            font-weight: 700px;
        }

        .btn-submit:hover {
            background-color: #648B2C;
        }

        /* Success message styles */
        .alert-success {
            transition: all 0.5s ease;
            opacity: 1;
            max-height: 100px;
            overflow: hidden;
        }
        
        .alert-success.hide {
            opacity: 0;
            max-height: 0;
            padding: 0;
            margin: 0;
            border: 0;
        }
        .titre{
            font-weight: bold;
            color: #8FC73F;
            text-align: center;
        }
    </style>
</head>

<body>
    <?php include('header.php'); ?>
    
    <main>
        <div class="contact-background mt-4">
            <div class="contact-form-container">
                <h1 class="titre">Nous Contacter</h1>

                <?php if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_SESSION['form_submitted'])): ?>
                    <div id="success-message" class="alert alert-success w-75 mx-auto d-block">
                        Votre message a été envoyé avec succès!
                    </div>
                    <?php unset($_SESSION['form_submitted']); ?>
                <?php endif; ?>

                <form id="contactForm" action="demande.php" method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <input type="text" id="nom" name="nom" placeholder="Votre nom" required>
                        </div>
                        <div class="form-group">
                            <input type="email" id="email" name="email" placeholder="Votre mail" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <input type="text" id="titre" name="titre" placeholder="Titre du sujet" required>
                    </div>
                    <div class="form-group">
                        <textarea id="sujet" name="sujet" placeholder="Sujet" rows="6" required></textarea>
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn-submit">Envoyer message</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include('footer.php'); ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Form validation
            const contactForm = document.getElementById('contactForm');
            if (contactForm) {
                contactForm.addEventListener('submit', function(e) {
                    const nom = document.getElementById('nom').value.trim();
                    const email = document.getElementById('email').value.trim();
                    const titre = document.getElementById('titre').value.trim();
                    const sujet = document.getElementById('sujet').value.trim();

                    let isValid = true;
                    let errorMessage = '';

                    if (nom === '') {
                        isValid = false;
                        errorMessage += 'Le nom est requis.\n';
                    }

                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    if (!emailRegex.test(email)) {
                        isValid = false;
                        errorMessage += 'Veuillez entrer une adresse email valide.\n';
                    }

                    if (titre === '') {
                        isValid = false;
                        errorMessage += 'Le titre est requis.\n';
                    }

                    if (sujet === '') {
                        isValid = false;
                        errorMessage += 'Le message est requis.\n';
                    } else if (sujet.length < 10) {
                        isValid = false;
                        errorMessage += 'Le message doit contenir au moins 10 caractères.\n';
                    }

                    if (!isValid) {
                        e.preventDefault();
                        alert('Veuillez corriger les erreurs suivantes:\n' + errorMessage);
                    }
                });
            }

            // Success message handling
            const successMessage = document.getElementById('success-message');
            if (successMessage) {
                // Hide if page was reloaded
                if (performance.navigation.type === 1 || 
                    performance.getEntriesByType("navigation")[0].type === 'reload') {
                    successMessage.remove();
                } 
            }
        });
    </script>

    <script src="script.js"></script>
</body>
</html>