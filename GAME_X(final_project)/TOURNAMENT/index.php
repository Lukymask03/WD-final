<?php
include 'backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Game X Community | Home</title>
  <link rel="stylesheet" href="assets/css/common.css" />
  <link rel="stylesheet" href="assets/css/index.css" />
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
    <a href="contact.php" class="nav-link">Contact</a>
  </nav>

  <div class="nav-actions">
    <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
    <a href="auth/login.php" class="cta-btn">Register / Login</a>
  </div>
</header>

<!-- ========== HERO ========== -->
<section class="hero">
  <div class="hero-left">
    <h1>Welcome to Game X Tournament Hub</h1>
    <p>Register your team, join epic battles, and track your progress in real-time brackets.</p>
    <a href="tournament.php" class="btn btn-primary">Browse Tournaments</a>
  </div>

  <aside class="hero-right">
    <div class="slideshow-container">
      <div class="slide fade"><img src="assets/images/ESPORTS_1.png" alt="Esports 1" /></div>
      <div class="slide fade"><img src="assets/images/ESPORTS_2.png" alt="Esports 2" /></div>
      <div class="slide fade"><img src="assets/images/ESPORTS_3.png" alt="Esports 3" /></div>

      <a class="prev" onclick="plusSlides(-1)">&#10094;</a>
      <a class="next" onclick="plusSlides(1)">&#10095;</a>
    </div>

    <div class="dots">
      <span class="dot" onclick="currentSlide(1)"></span>
      <span class="dot" onclick="currentSlide(2)"></span>
      <span class="dot" onclick="currentSlide(3)"></span>
    </div>
  </aside>
</section>

<!-- ========== LEADERBOARD ========== -->
<section class="leaderboard">
  <h2>Top Teams</h2>
  <ul>
    <li>Team Alpha – 12 Wins</li>
    <li>Shadow Hunters – 9 Wins</li>
    <li>Dragon Crest – 7 Wins</li>
  </ul>
</section>

<!-- ========== NEWS ========== -->
<section class="news">
  <h2>Latest News</h2>
  <article>
    <h3>[NEW] Valorant Summer Cup 2025 Announced!</h3>
    <p>Registrations are now open for the biggest summer tournament. Limited slots available — don’t miss it!</p>
  </article>
  <article>
    <h3>Site Update</h3>
    <p>We’ve improved the bracket system and fixed bugs reported by players. Thanks for your feedback!</p>
  </article>
</section>

<!-- ========== COMMUNITY CTA ========== -->
<section class="community-cta">
  <h2>Join the GAME X Community</h2>
  <p>Compete, chat, and connect with fellow gamers worldwide.</p>
  <a href="auth/register.php" class="btn btn-primary">Join Now</a>
</section>

<!-- ========== FOOTER ========== -->
<footer class="footer">
  <p>&copy; <?php echo date("Y"); ?> Game X Community. All rights reserved.</p>
</footer>

<script src="assets/js/index.js"></script>
<script src="assets/js/darkmode.js"></script>

</body>
</html>
