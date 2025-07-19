<?php
include('db.php');
session_start();

$message = '';
$alert_type = '';

// Récupération du token depuis l'URL
$token = isset($_GET['token']) ? mysqli_real_escape_string($conn, $_GET['token']) : '';

if (empty($token)) {
    $message = "Lien invalide.";
    $alert_type = "danger";
} else {
    // Vérifie si le token existe et n'a pas expiré
    $query = "SELECT * FROM client WHERE reset_token = '$token' AND reset_expires > NOW() AND actif = 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);

        if (isset($_POST['new_password'])) {
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            if ($password !== $confirm_password) {
                $message = "Les mots de passe ne correspondent pas.";
                $alert_type = "danger";
            } elseif (strlen($password) < 6) {
                $message = "Le mot de passe doit contenir au moins 6 caractères.";
                $alert_type = "danger";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                $update_query = "UPDATE client 
                                 SET motdepasse = '$hashed_password', reset_token = NULL, reset_expires = NULL 
                                 WHERE reset_token = '$token'";
                if (mysqli_query($conn, $update_query)) {
                    $message = "Mot de passe réinitialisé avec succès. Vous pouvez maintenant vous connecter.";
                    $alert_type = "success";
                } else {
                    $message = "Erreur lors de la mise à jour du mot de passe.";
                    $alert_type = "danger";
                }
            }
        }
    } else {
        $message = "Le lien est invalide ou a expiré.";
        $alert_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réinitialisation du mot de passe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }
        .container {
            max-width: 500px;
            margin-top: 60px;
            padding: 20px;
            background-color: white;
            box-shadow: 0 0 8px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .btn-success {
            background-color: #8BC34A;
            border-color: #8BC34A;
        }
    </style>
</head>
<body>
<div class="container">
    <h4 class="mb-3">Réinitialiser votre mot de passe</h4>

    <?php if (!empty($message)): ?>
        <div class="alert alert-<?php echo $alert_type; ?>"><?php echo $message; ?></div>
    <?php endif; ?>

    <?php if (!isset($_POST['new_password']) && $alert_type !== 'danger'): ?>
    <form method="POST">
        <div class="mb-3">
            <label for="password" class="form-label">Nouveau mot de passe</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="6">
        </div>
        <div class="mb-3">
            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="6">
        </div>
        <button type="submit" name="new_password" class="btn btn-success">Réinitialiser</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>
