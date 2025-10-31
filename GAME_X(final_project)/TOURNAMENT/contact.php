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
  <title>Contact Us | GameX Tournament Hub</title>
  <link rel="stylesheet" href="assets/css/common.css">
  <link rel="stylesheet" href="assets/css/contact.css">
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

  <nav>
    <a href="index.php" class="nav-link">Home</a>
    <a href="about.php" class="nav-link">About</a>
    <a href="tournament.php" class="nav-link">Tournaments</a>
    <a href="contact.php" class="nav-link active">Contact</a>
  </nav>

  <div class="nav-actions">
    <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
    <a href="auth/login.php" class="cta-btn">Register / Login</a>
  </div>
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
  <p>&copy; <?php echo date('Y'); ?> Game X Community. All rights reserved.</p>
</footer>

<!-- ===== SCRIPTS ===== -->
<script src='assets/js/darkmode.js'></script>
<script src='assets/js/index.js'></script>

</body>
</html>
