<?php
session_start();
require_once "../backend/db.php";

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us | GameX Tournament Hub</title>
  <link rel="stylesheet" href="../assets/css/common.css">
  <link rel="stylesheet" href="../assets/css/contact.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<?php require_once "../includes/player/player_navbar.php"; ?>

<!-- ===== CONTACT SECTION ===== -->
<section class="contact-section">
  <div class="contact-info">
    <h2>Get in Touch</h2>
    <p>We’d love to hear from you! Whether you’re a gamer, sponsor, or event organizer — reach out to us for any inquiries, collaborations, or feedback.</p>

    <div class="contact-details">
      <p><strong>Email:</strong> support@gamexhub.com</p>
      <p><strong>Phone:</strong> +63 912 345 6789</p>
      <p><strong>Location:</strong> Zamboanga City, Philippines</p>
    </div>

    <p style="margin-top:25px; color:#aaa;">Follow us on:</p>
    <div class="social-links">
      <a href="https://www.facebook.com/GameXTournamentHub" class="btn" target="_blank"><i class="fab fa-facebook"></i> Facebook</a>
      <a href="https://discord.gg/GameXCommunity" class="btn" target="_blank"><i class="fab fa-discord"></i> Discord</a>
      <a href="https://twitter.com/GameXHub" class="btn" target="_blank"><i class="fab fa-twitter"></i> Twitter</a>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer class='footer'>
  <p>&copy; <?php echo date('Y'); ?> Game X Community. All rights reserved.</p>
</footer>

<!-- ===== SCRIPTS ===== -->
<script src='../assets/js/darkmode.js'></script>
<script src='../assets/js/index.js'></script>

</body>
</html>
