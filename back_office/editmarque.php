<?php
include('db.php');
include('index.php');
// Vérifiez si un ID est passé en paramètre dans l'URL
if (isset($_GET['id'])) {
    $id_marque = $_GET['id'];

    // Récupérer les informations de la marque depuis la base de données
    $query = "SELECT * FROM marque WHERE id_marque = '$id_marque'";
    $result = mysqli_query($conn, $query);
    $marque = mysqli_fetch_assoc($result);
} else {
    // Si l'ID n'est pas passé, rediriger ou afficher un message d'erreur
    echo "Marque introuvable.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Récupérer les données du formulaire
    $nom_marque = $_POST['nom_marque'];
    $cover_marque = $_FILES['cover_marque']['name'];

    // Vérifiez si une nouvelle image a été téléchargée
    if ($cover_marque != "") {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($cover_marque);
        move_uploaded_file($_FILES['cover_marque']['tmp_name'], $target_file);
    } else {
        $cover_marque = $marque['cover_marque']; // garder l'ancienne image si aucune nouvelle n'est téléchargée
    }

    // Mettre à jour les informations dans la base de données
    $update_query = "UPDATE marque SET nom_marque = '$nom_marque', cover_marque = '$cover_marque' WHERE id_marque = '$id_marque'";
    if (mysqli_query($conn, $update_query)) {
        echo "Marque mise à jour avec succès.";
    } else {
        echo "Erreur de mise à jour de la marque.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Modifier la marque</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <h2>Modifier la marque</h2>
        <form action="editmarque.php?id=<?php echo $marque['id_marque']; ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="nom_marque">Nom de la marque:</label>
                <input type="text" class="form-control" id="nom_marque" name="nom_marque" value="<?php echo $marque['nom_marque']; ?>" required>
            </div>
            <div class="form-group">
                <label for="cover_marque">Image de couverture:</label>
                <input type="file" class="form-control" id="cover_marque" name="cover_marque">
                <img src="../uploads/<?php echo $marque['cover_marque']; ?>" class="mt-2" width="150" alt="Cover Image">
            </div>
            <button type="submit" class="btn btn-primary mt-3">Sauvegarder les modifications</button>
        </form>
    </div>
</body>
</html>
