<?php
include('db.php');
include('navbar.php');

//select astuce info mel base
$result = $conn->query("SELECT * FROM astuce_semaine LIMIT 1");
$astuce = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titre = trim(mysqli_real_escape_string($conn, $_POST['titre']));
    $descriptions = trim(mysqli_real_escape_string($conn, $_POST['descriptions']));
    $youtube_link = trim(mysqli_real_escape_string($conn, $_POST['youtube_link']));

    // Update table astuce
    $sql = "UPDATE astuce_semaine SET titre='$titre', descriptions='$descriptions', youtube_link='$youtube_link' LIMIT 1";

    if ($conn->query($sql) === TRUE) {
        echo "✅ Astuce mise à jour avec succès!"; //msg succes
    } else {
        echo "Erreur lors de la mise à jour : " . $conn->error; //msg error en fama mockel
    }
}

// get id vd yt mel url w traj3 img
function getYoutubeThumbnail($url) {
    $videoId = null;
   
    if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
        $videoId = $matches[1]; 
    }
    return $videoId ? "https://img.youtube.com/vi/$videoId/hqdefault.jpg" : null;
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
        /* Styling for the YouTube thumbnail */
        .youtube-thumbnail {
            max-width: 200px;
            height: auto;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Mettre à jour l'astuce</h4>
                    <h6>Modifier l'astuce existante</h6>
                </div>
            </div>

            <div class="row">
                <form action="" method="post">
                    
                    <div class="col-lg-3 col-sm-6 col-12">
                        <div class="form-group">
                            <label>Titre</label>
                            <input type="text" id="titre" name="titre" class="form-control" value="<?= htmlspecialchars($astuce['titre'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Descriptions</label>
                            <textarea id="descriptions" name="descriptions" class="form-control" required><?= htmlspecialchars($astuce['descriptions'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-group">
                            <label>Youtube URL</label>
                            <input type="text" id="youtube_link" name="youtube_link" class="form-control" value="<?= htmlspecialchars($astuce['youtube_link'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <?php if (!empty($astuce['youtube_link'])): ?>
                            <?php $thumbnailUrl = getYoutubeThumbnail($astuce['youtube_link']); ?>
                            <?php if ($thumbnailUrl): ?>
                                <div class="form-group">
                                    <label>Thumbnail de YouTube</label>
                                    <img src="<?= $thumbnailUrl ?>" alt="YouTube Thumbnail" class="youtube-thumbnail">
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <div class="col-lg-12">
                        <input type="submit" name="submit" value="Mettre à jour" class="btn btn-submit me-2">
                        <a href="astuce_list.php" class="btn btn-cancel">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
