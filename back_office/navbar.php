<!--indexphp-->
<?php
session_start();
include 'db.php'; // update if your DB config file is named differently
if (!isset($_SESSION['id_admin'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit();
}
// Check if admin is logged in
$id_admin = $_SESSION['id_admin'] ?? null;

if ($id_admin) {
    $stmt = $conn->prepare("SELECT * FROM admin WHERE id_admin = ?");
    $stmt->bind_param("i", $id_admin);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    $admin_name = $admin['prenom_admin'] . ' ' . $admin['nom_admin'];
    $admin_img = !empty($admin['profile_img']) ? $admin['profile_img'] : 'default.jpg';
} else {
    // fallback values if not logged in
    $admin_name = "Admin";
    $admin_img = "default.jpg";
}
?>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="description" content="POS - Bootstrap Admin Template">
    <meta name="keywords" content="admin, estimates, bootstrap, business, corporate, creative, management, minimal, modern, html5, responsive">
    <meta name="author" content="Dreamguys - Bootstrap Admin Template">
    <meta name="robots" content="noindex, nofollow">
    <title>Navbar</title>
    <link rel="shortcut icon" type="image/x-icon" href="../assets/img/logo/logo-small.png">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/animate.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/plugins/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>

<div class="main-wrapper">
<div class="header">
<div class="header-left active">
<a href="dashboard.php" class="logo">
<img src="../assets/img/logo/logo.png" alt="">
</a>
<a href="dashboard.php" class="logo-small">
<img src="../assets/img/logo/logo-small.png" alt="">
</a>
<a id="toggle_btn" href="javascript:void(0);"></a>
</div>
<a id="mobile_btn" class="mobile_btn" href="#sidebar">
<span class="bar-icon">
<span></span>
<span></span>
<span></span>
</span>
</a>

<ul class="nav user-menu">

<li class="nav-item">
<div class="top-nav-search">
<a href="" class="responsive-search">
<i class="fa fa-search"></i>
</a>
<form action="#">
<div class="searchinputs">
<input type="text" placeholder="Search Here ...">
<div class="search-addon">
<span><img src="../assets/img/icons/closes.svg" alt="img"></span>
</div>
</div>
</form>
</div>
</li>
<li class="nav-item dropdown has-arrow main-drop">
<a href="" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
<span class="user-img"><img src="<?php echo htmlspecialchars($admin_img); ?>" alt="">
</span>
</a>
<div class="dropdown-menu menu-drop-user">
<div class="profilename">
<div class="profileset">
<span class="user-img"><img src="<?php echo htmlspecialchars($admin_img); ?>" alt="">
<span class="status online"></span></span>
<div class="profilesets">
<h6><?php echo htmlspecialchars($admin_name); ?></h6>
<h5>Admin</h5>
</div>
</div>
<hr class="m-0">
<a class="dropdown-item" href="profile.php"> <i class="me-2" data-feather="user"></i>Profile</a>
<hr class="m-0">
<a class="dropdown-item logout pb-0" href="logout.php"><img src="../assets/img/icons/log-out.svg" class="me-2" alt="img">Déconnexion</a>
</div>
</div>
</li>
</ul>


<div class="dropdown mobile-user-menu">
<a href="" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
<div class="dropdown-menu dropdown-menu-right">
<a class="dropdown-item" href="profile.php"> Profile</a>
<a class="dropdown-item" href="signin.html">Déconnexion</a>
</div>
</div>

</div>


<div class="sidebar" id="sidebar">
<div class="sidebar-inner slimscroll">
<div id="sidebar-menu" class="sidebar-menu">
<ul>
<li class="active">
<a href="dashboard.php"><img src="../assets/img/icons/dashboard.svg" alt="img"><span> Dashboard</span> </a>
</li>
<li class="submenu">
<a href=""><i class="bi bi-bag"></i>
<span> Produits</span> <span class="menu-arrow"></span></a>
<ul>
<li><a href="productlist.php">Liste de produits</a></li>
<li><a href="produit.php">Ajouter un produit</a></li>
<li><a href="categorylist.php">Liste de catégories </a></li>
<li><a href="addcategory.php">Ajouter une catégorie</a></li>
<li><a href="brandlist.php">Liste de marques</a></li>
<li><a href="addbrand.php">Ajouter une marque</a></li>
</ul>
</li>

<li class="submenu">
<a href=""><img src="../assets/img/icons/product.svg" alt="img"><span>Packs</span> <span class="menu-arrow"></a>
<ul>
<li><a href="ajoutpack.php">Ajouter un Pack</a></li>
<li><a href="listpack.php">Liste des Packs</a></li>
</ul>
</li>


<li class="submenu">
<a href=""><img src="../assets/img/icons/sales1.svg" alt="img"><span>Commande</span> <span class="menu-arrow"></span></a>
<ul>
<li><a href="commandelist.php">Liste de commande</a></li>
</ul>
</li>

<li class="submenu">
<a href="">  <i class="bi bi-tag text-muted"></i> <span>Code promo</span> <span class="menu-arrow"></a>
<ul>
<li><a href="codepromo.php">Ajouter code promo</a></li>
<li><a href="listecodepromo.php">Liste des codes promo</a></li>
</ul>
</li>



<li class="submenu">
<a href=""><img src="../assets/img/icons/purchase1.svg" alt="img"><span>Liste de demande</span> <span class="menu-arrow"></span></a>
<ul>
<li><a href="demande.php">Demande List</a></li>

</ul>
</li>



<li class="submenu">
<a href=""><img src="../assets/img/icons/users1.svg" alt="img"><span> Clients</span> <span class="menu-arrow"></span></a>
<ul>
<li><a href="clientlist.php">Liste de clients</a></li>

</ul>
</li>

<li class="submenu">
<a href="">
<i class="fa-regular fa-comments text-muted"></i>
<span> Liste d'avis</span> <span class="menu-arrow"></span></a>
<ul>
<li><a href="avis.php">Avis List</a></li>
</ul>    
</li>

<li class="submenu">
<a href=""><i class="fas fa-newspaper text-muted "></i>
<span> Actualités</span> <span class="menu-arrow"></span></a>
<ul>
<li><a href="actualite.php">Astuce</a></li>

</ul>
</li>

</ul>
</div>
</div>
</div>
<script src="../assets/js/script.js"></script>

</body>
</html>