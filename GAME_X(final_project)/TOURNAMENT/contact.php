<?php
session_start();
require_once "backend/db.php";

// Handle form submission
$successMessage = "";
$errorMessage = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);
  $email = trim($_POST['email']);
  $message = trim($_POST['message']);

  if (!empty($name) && !empty($email) && !empty($message)) {
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
      try {
        $stmt = $conn->prepare("
                    INSERT INTO messages (name, email, message)
                    VALUES (:name, :email, :message)
                ");
        $stmt->execute([
          'name' => $name,
          'email' => $email,
          'message' => $message
        ]);
        $successMessage = "Your message has been sent successfully!";
      } catch (PDOException $e) {
        $errorMessage = "Something went wrong while sending your message. Please try again later.";
      }
    } else {
      $errorMessage = "Please enter a valid email address.";
    }
  } else {
    $errorMessage = "Please fill in all required fields.";
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Contact Us</title>
  <link rel="stylesheet" href="assets/css/common.css">
  <link rel="stylesheet" href="assets/css/contact.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>

  <!-- ========== NAVBAR ========== -->
  <header class="navbar">
    <div class="logo">
      <a href="index.php" class="logo-link">
        <img src="assets/images/game_x_logo.png" alt="Game X Community" class="logo-img" />
        <h1><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h1>
      </a>
    </div>

    <nav class="nav-menu">
      <a href="index.php" class="nav-link"><i class="fas fa-home"></i> Home</a>
      <a href="tournament.php" class="nav-link"><i class="fas fa-trophy"></i> Tournaments</a>
      <a href="about.php" class="nav-link"><i class="fas fa-info-circle"></i> About</a>
      <a href="contact.php" class="nav-link active"><i class="fas fa-envelope"></i> Contact</a>
    </nav>

    <div class="nav-actions">
      <button id="darkModeToggle" class="darkmode-btn">
        <i class="fas fa-moon"></i>
        <span>Dark Mode</span>
      </button>
      <a href="auth/login.php" class="cta-btn">
        <i class="fas fa-user"></i> Login
      </a>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle">
      <i class="fas fa-bars"></i>
    </button>
  </header>

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
    <div class="container">
      <div class="footer-content">
        <div class="footer-section">
          <div class="footer-logo">
            <img src="assets/images/game_x_logo.png" alt="Game X" />
            <h3><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h3>
          </div>
          <p class="footer-description">
            The premier platform for esports tournaments and competitive gaming.
          </p>
          <div class="social-links">
            <a href="#" class="social-link"><i class="fab fa-discord"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
            <a href="#" class="social-link"><i class="fab fa-twitch"></i></a>
            <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
          </div>
        </div>

        <div class="footer-section">
          <h4>Quick Links</h4>
          <ul class="footer-links">
            <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li><a href="about.php"><i class="fas fa-info-circle"></i> About Us</a></li>
            <li><a href="tournament.php"><i class="fas fa-trophy"></i> Tournaments</a></li>
            <li><a href="contact.php"><i class="fas fa-envelope"></i> Contact</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h4>For Players</h4>
          <ul class="footer-links">
            <li><a href="auth/create_account.php"><i class="fas fa-user-plus"></i> Register</a></li>
            <li><a href="auth/login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
            <li><a href="#"><i class="fas fa-question-circle"></i> FAQ</a></li>
            <li><a href="#"><i class="fas fa-file-alt"></i> Rules</a></li>
          </ul>
        </div>

        <div class="footer-section">
          <h4>Support</h4>
          <ul class="footer-links">
            <li><a href="contact.php"><i class="fas fa-headset"></i> Help Center</a></li>
            <li><a href="#"><i class="fas fa-shield-alt"></i> Privacy Policy</a></li>
            <li><a href="#"><i class="fas fa-gavel"></i> Terms of Service</a></li>
            <li><a href="#"><i class="fas fa-bug"></i> Report Issue</a></li>
          </ul>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; <?php echo date('Y'); ?> Game X Community. All rights reserved.</p>
        <p class="footer-made-with">Made with <i class="fas fa-heart"></i> for gamers</p>
      </div>
    </div>
  </footer>

  <!-- Scroll to Top Button -->
  <button id="scrollToTop" class="scroll-to-top">
    <i class="fas fa-arrow-up"></i>
  </button>

  <!-- ===== SCRIPTS ===== -->
  <script src='assets/js/darkmode.js'></script>
  <script src='assets/js/index.js'></script>
  <script src='assets/js/index.js'></script>

</body>

</html>