<?php

include('db.php'); // OK de garder ici

// ✅ 1. TRAITEMENT DU TOGGLE AVANT TOUT AFFICHAGE
if (isset($_GET['toggle'])) {
    $id = intval($_GET['toggle']);

    $query = "SELECT actif FROM client WHERE id_client = $id";
    $result = mysqli_query($conn, $query);

    if ($result && $row = mysqli_fetch_assoc($result)) {
        $new_status = $row['actif'] ? 0 : 1;
        $update_query = "UPDATE client SET actif = $new_status WHERE id_client = $id";
        mysqli_query($conn, $update_query);
    }

    // Redirection propre vers la même page avec ancre
    header("Location: clientlist.php#id-$id");
    exit;
}

// 2. Ensuite tu peux inclure du HTML
include('navbar.php'); // C'est ce fichier qui envoie du HTML



// Requête pour afficher la liste des clients
$query = "
    SELECT 
        c.id_client,
        c.nom_client,
        c.prenom_client,
        c.mail_client,
        c.actif,
        COUNT(co.id_commande) AS nombre_commandes,
        IFNULL(SUM(co.total), 0) AS total_depense
    FROM client c
    LEFT JOIN commande co ON c.id_client = co.id_client
    GROUP BY c.id_client
";

$result = mysqli_query($conn, $query);

if (!$result) {
    die("Erreur dans la requête : " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste des clients</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .action-icon {
            font-size: 16px;
            margin-right: 8px;
            cursor: pointer;
        }
    </style>
</head>

<body>
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Liste des clients</h4>
                <h6>Gérez vos clients</h6>
            </div>
            
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nom complet</th>
                            <th>Email</th>
                            <th>Commandes</th>
                            <th>Total dépensé</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr id="id-<?= $row['id_client'] ?>">
                                <td><?= $row['id_client'] ?></td>
                                <td><?= htmlspecialchars($row['nom_client'] . " " . $row['prenom_client']) ?></td>
                                <td><?= htmlspecialchars($row['mail_client']) ?></td>
                                <td><?= $row['nombre_commandes'] ?></td>
                                <td><?= number_format($row['total_depense'], 2) ?> TND</td>
                                <td>
                                    <?php if ($row['actif']) : ?>
                                        <span class="badge bg-success">Actif</span>
                                    <?php else : ?>
                                        <span class="badge bg-danger">Désactivé</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="detailclient.php?id=<?= $row['id_client'] ?>" title="Voir">
                                   <img src="../assets/img/icons/eye1.svg" class="me-2" alt="img">
                                    </a>
                                    <a href="?toggle=<?= $row['id_client'] ?>"
                                       onclick="return confirm('Voulez-vous vraiment <?= $row['actif'] ? 'désactiver' : 'activer' ?> ce client ?')"
                                       title="<?= $row['actif'] ? 'Désactiver' : 'Activer' ?>">
                                        <?php if ($row['actif']) : ?>
                                            <i class="bi bi-person-x text-danger action-icon fs-4	"></i>
                                        <?php else : ?>
                                            <i class="bi bi-person-check action-icon text-success fs-4"></i>

                                        <?php endif; ?>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Scripts -->
    <script src="../assets/js/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/feather.min.js"></script>
    <script src="../assets/js/jquery.slimscroll.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script src="../assets/js/index.js"></script>
    <script src="../assets/plugins/apexchart/apexcharts.min.js"></script>
    <script src="../assets/plugins/apexchart/chart-data.js"></script>
    <script src="../assets/plugins/select2/js/select2.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalert2.all.min.js"></script>
    <script src="../assets/js/jquery.dataTables.min.js"></script>
    <script src="../assets/js/dataTables.bootstrap4.min.js"></script>
    <script src="../assets/plugins/sweetalert/sweetalerts.min.js"></script>
    <script src="../assets/js/custom-datatables.js"></script>




<!-- Optionnel : scroller automatiquement vers la ligne cliquée -->
<script>
    document.addEventListener("DOMContentLoaded", () => {
        const hash = window.location.hash;
        if (hash && hash.startsWith("#id-")) {
            const row = document.querySelector(hash);
            if (row) {
                row.scrollIntoView({ behavior: "smooth", block: "center" });
                row.classList.add("table-success");
                setTimeout(() => row.classList.remove("table-success"), 2000);
            }
        }
    });
</script>
</body>
</html>

<?php
mysqli_close($conn);
?>
