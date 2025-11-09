<?php
session_start();
require_once "../backend/db.php";

// Check if player is logged in
if (!isset($_SESSION['account_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
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
  
  <!-- LEFT SIDE: CONTACT INFO CARD -->
  <div class="contact-info">
    <h2>Get in Touch</h2>
    <p>We’d love to hear from you! Reach out for inquiries, collaborations, or feedback.</p>

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

  <!-- RIGHT SIDE: FEEDBACK CARD -->
  <div class="contact-form">
    <h2>Send Feedback</h2>
    <p style="color:#aaa; margin-bottom:15px;">We value your thoughts! Tell us what you think about your experience with GameX.</p>

    <?php if(isset($_GET['success'])): ?>
      <p style="color:green;">Your message has been sent successfully!</p>
    <?php elseif(isset($_GET['error'])): ?>
      <p style="color:red;">Please enter a message before sending.</p>
    <?php endif; ?>

    <form action="player_feedback_submit.php" method="POST">
      <textarea name="message" placeholder="Your Feedback..." required></textarea>
      <button type="submit">Send Feedback</button>
    </form>
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
