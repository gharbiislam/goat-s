<footer class="footer-section">
  <div class="container">
    <div class="row footer-content">
      <!-- Colonne de gauche (logo et liens) -->
      <div class="col-md-4">
        <a href="index.php" class="footer-logo">
          <img src="../assets/img/logo/logo2.png" alt="GOAT'S">
        </a>
        <div class="footer-links">
          <ul class="list-unstyled">
            <li><a href="a-propos.php" class="footer-link">A propos de nous</a></li>
            <li><a href="commande.php" class="footer-link">Suivre ma commande</a></li>
            <li><a href="garantie.php" class="footer-link">Satisfait ou remboursé</a></li>
          </ul>
        </div>
      </div>

      <!-- Colonne du milieu (service client) -->
      <div class="col-md-4">
        <h3 class="footer-heading">SERVICE CLIENT</h3>
        <p class="service-hours">
          Vous pouvez nous joindre du mardi au<br>
          samedi de 10h00 à 13h00 et de 14h30 à<br>
          19h00
        </p>
        <p class="service-phone">
          <a href="tel:+21621158657">
            <i class="bi bi-telephone"></i> +216 21 158 657
          </a>
        </p>
        <p class="service-email">
          <a href="mailto:goats@gmail.com">
            <i class="bi bi-envelope"></i> goats@gmail.com
          </a>
        </p>
      </div>

      <!-- Colonne de droite (réseaux sociaux et paiement) -->
      <div class="col-md-4">
        <h3 class="footer-heading">SUIVEZ-NOUS</h3>
        <div class="social-icons">
          <a href="#" class="social-icon"><i class="bi bi-facebook"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-instagram"></i></a>
          <a href="#" class="social-icon"><i class="bi bi-tiktok"></i></a>
        </div>
        
        <div class="payment-section">
          <h3 class="footer-heading">PAIEMENT SÉCURISÉE AVEC</h3>
          <div class="payment-icons">
            <img src="../assets/img/payment/visa.png" alt="Visa" class="payment-icon">
            <img src="../assets/img/payment/mastercard.png" alt="Mastercard" class="payment-icon">
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
      <div class="row">
        <div class="col-12 text-center">
          <p>© 2025 GOATS tout droit reservés</p>
        </div>
      </div>
    </div>
  </div>
</footer>

<!-- Bootstrap CSS et JS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<!-- Police Montserrat -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600&display=swap" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- CSS personnalisé pour le footer -->
<style>
  .footer-section {
    background-color: #1a4289;
    color: white;
    font-family: 'Montserrat', sans-serif;
    font-weight: 500;
    font-size: 13px;
    padding-top: 60px;
    padding-bottom: 60px;
  }

  .container {
    max-width: 1320px;
    margin: 0 auto;
    width: 100%;
  }

  .footer-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start; /* Aligne les colonnes au même niveau en haut */
    gap: 20px; /* Espacement entre les colonnes */
  }

  .footer-content > div {
    flex: 1;
    padding: 0 20px; /* Padding égal dans chaque colonne */
  }

  .footer-logo img {
    max-width: 200px;
    margin-bottom: 20px;
  }

  .footer-links {
    margin-top: 20px;
  }

  .footer-link {
    color: white;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    display: block;
    margin-bottom: 10px;
    transition: all 0.3s;
  }

  .footer-link:hover {
    text-decoration: underline;
  }

  .footer-heading {
    font-size: 20px;
    font-weight: 600;
    margin-bottom: 20px;
  }

  .social-icons a {
    font-size: 20px;
    margin-right: 10px;
    color: white;
    transition: color 0.3s;
  }

  .social-icons a:hover {
    color: #ccc;
  }

  .payment-icons img {
    height: 30px;
    margin-right: 10px;
  }

  .green-line {
    height: 5px;
    background-color: #6fcf97;
  }

  .copyright {
    background-color: #133776;
    padding: 15px 0;
    font-size: 12px;
  }

  .text-center {
    text-align: center;
  }
</style>
