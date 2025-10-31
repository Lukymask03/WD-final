<?php
include 'backend/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Game X</title>
  <link rel="stylesheet" href="assets/css/common.css" />
  <link rel="stylesheet" href="assets/css/index.css" />
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
      <a href="index.php" class="nav-link active"><i class="fas fa-home"></i> Home</a>
      <a href="tournament.php" class="nav-link"><i class="fas fa-trophy"></i> Tournaments</a>
      <a href="about.php" class="nav-link"><i class="fas fa-info-circle"></i> About</a>
      <a href="contact.php" class="nav-link"><i class="fas fa-envelope"></i> Contact</a>
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

  <!-- ========== HERO ========== -->
  <section class="hero">
    <div class="hero-content">
      <div class="hero-left animate-slide-in-left">
        <div class="hero-badge">
          <i class="fas fa-fire"></i> LIVE NOW
        </div>
        <h1 class="hero-title">
          Welcome to <span class="gradient-text">Game X</span><br>
          Tournament Hub
        </h1>
        <p class="hero-description">
          Register your team, join epic battles, and track your progress in real-time brackets.
          Connect with gamers worldwide and compete for glory.
        </p>
        <div class="hero-buttons">
          <a href="tournament.php" class="btn btn-primary">
            <i class="fas fa-trophy"></i> Browse Tournaments
          </a>
          <a href="about.php" class="btn btn-secondary">
            <i class="fas fa-play-circle"></i> Learn More
          </a>
        </div>

        <div class="hero-stats">
          <div class="stat-item">
            <i class="fas fa-users"></i>
            <div class="stat-content">
              <span class="stat-number">10K+</span>
              <span class="stat-label">Active Players</span>
            </div>
          </div>
          <div class="stat-item">
            <i class="fas fa-gamepad"></i>
            <div class="stat-content">
              <span class="stat-number">500+</span>
              <span class="stat-label">Tournaments</span>
            </div>
          </div>
          <div class="stat-item">
            <i class="fas fa-medal"></i>
            <div class="stat-content">
              <span class="stat-number">$1M+</span>
              <span class="stat-label">Prize Pool</span>
            </div>
          </div>
        </div>
      </div>

      <aside class="hero-right animate-slide-in-right">
        <div class="slideshow-container">
          <div class="slide fade"><img src="assets/images/ESPORTS_1.png" alt="Esports 1" /></div>
          <div class="slide fade"><img src="assets/images/ESPORTS_2.png" alt="Esports 2" /></div>
          <div class="slide fade"><img src="assets/images/ESPORTS_3.png" alt="Esports 3" /></div>

          <a class="prev" onclick="plusSlides(-1)"><i class="fas fa-chevron-left"></i></a>
          <a class="next" onclick="plusSlides(1)"><i class="fas fa-chevron-right"></i></a>
        </div>

        <div class="dots">
          <span class="dot" onclick="currentSlide(1)"></span>
          <span class="dot" onclick="currentSlide(2)"></span>
          <span class="dot" onclick="currentSlide(3)"></span>
        </div>
      </aside>
    </div>
  </section>

  <!-- ========== FEATURES ========== -->
  <section class="features">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title">Why Choose Game X?</h2>
        <p class="section-subtitle">Everything you need to manage and participate in esports tournaments</p>
      </div>

      <div class="features-grid">
        <div class="feature-card animate-fade-in">
          <div class="feature-icon">
            <i class="fas fa-bolt"></i>
          </div>
          <h3>Real-Time Updates</h3>
          <p>Track your matches and brackets live with instant notifications and updates.</p>
        </div>

        <div class="feature-card animate-fade-in" style="animation-delay: 0.1s">
          <div class="feature-icon">
            <i class="fas fa-users-cog"></i>
          </div>
          <h3>Team Management</h3>
          <p>Create and manage your team, invite players, and coordinate strategies.</p>
        </div>

        <div class="feature-card animate-fade-in" style="animation-delay: 0.2s">
          <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
          </div>
          <h3>Statistics & Analytics</h3>
          <p>View detailed stats, match history, and performance analytics.</p>
        </div>

        <div class="feature-card animate-fade-in" style="animation-delay: 0.3s">
          <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
          </div>
          <h3>Secure Platform</h3>
          <p>Protected tournaments with anti-cheat measures and fair play policies.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== LEADERBOARD ========== -->
  <section class="leaderboard">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-crown"></i> Top Teams</h2>
        <p class="section-subtitle">The best teams competing in our tournaments</p>
      </div>

      <div class="leaderboard-list">
        <div class="leaderboard-item rank-1">
          <div class="rank-badge gold">
            <i class="fas fa-trophy"></i>
            <span>1</span>
          </div>
          <div class="team-info">
            <h3 class="team-name">Team Alpha</h3>
            <p class="team-stats"><i class="fas fa-star"></i> 12 Wins · 3 Tournaments Won</p>
          </div>
          <div class="team-score">
            <span class="score-number">2,450</span>
            <span class="score-label">Points</span>
          </div>
        </div>

        <div class="leaderboard-item rank-2">
          <div class="rank-badge silver">
            <i class="fas fa-medal"></i>
            <span>2</span>
          </div>
          <div class="team-info">
            <h3 class="team-name">Shadow Hunters</h3>
            <p class="team-stats"><i class="fas fa-star"></i> 9 Wins · 2 Tournaments Won</p>
          </div>
          <div class="team-score">
            <span class="score-number">2,180</span>
            <span class="score-label">Points</span>
          </div>
        </div>

        <div class="leaderboard-item rank-3">
          <div class="rank-badge bronze">
            <i class="fas fa-award"></i>
            <span>3</span>
          </div>
          <div class="team-info">
            <h3 class="team-name">Dragon Crest</h3>
            <p class="team-stats"><i class="fas fa-star"></i> 7 Wins · 1 Tournament Won</p>
          </div>
          <div class="team-score">
            <span class="score-number">1,920</span>
            <span class="score-label">Points</span>
          </div>
        </div>
      </div>

      <div class="leaderboard-cta">
        <a href="tournament.php" class="btn btn-outline">
          <i class="fas fa-list"></i> View Full Leaderboard
        </a>
      </div>
    </div>
  </section>

  <!-- ========== NEWS ========== -->
  <section class="news">
    <div class="container">
      <div class="section-header">
        <h2 class="section-title"><i class="fas fa-newspaper"></i> Latest News</h2>
        <p class="section-subtitle">Stay updated with the latest announcements and events</p>
      </div>

      <div class="news-grid">
        <article class="news-card featured">
          <div class="news-badge">
            <i class="fas fa-fire"></i> HOT
          </div>
          <div class="news-image">
            <img src="assets/images/ESPORTS_1.png" alt="Valorant Summer Cup" />
          </div>
          <div class="news-content">
            <div class="news-meta">
              <span class="news-date"><i class="far fa-calendar"></i> Oct 31, 2025</span>
              <span class="news-category"><i class="fas fa-tag"></i> Tournament</span>
            </div>
            <h3 class="news-title">Valorant Summer Cup 2025 Announced!</h3>
            <p class="news-excerpt">
              Registrations are now open for the biggest summer tournament.
              Limited slots available — don't miss it! $50,000 prize pool.
            </p>
            <a href="#" class="news-link">
              Read More <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </article>

        <article class="news-card">
          <div class="news-image">
            <img src="assets/images/ESPORTS_2.png" alt="Site Update" />
          </div>
          <div class="news-content">
            <div class="news-meta">
              <span class="news-date"><i class="far fa-calendar"></i> Oct 28, 2025</span>
              <span class="news-category"><i class="fas fa-tag"></i> Update</span>
            </div>
            <h3 class="news-title">Major Platform Update v2.5</h3>
            <p class="news-excerpt">
              We've improved the bracket system and fixed bugs reported by players.
              Thanks for your feedback!
            </p>
            <a href="#" class="news-link">
              Read More <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </article>

        <article class="news-card">
          <div class="news-image">
            <img src="assets/images/ESPORTS_3.png" alt="New Features" />
          </div>
          <div class="news-content">
            <div class="news-meta">
              <span class="news-date"><i class="far fa-calendar"></i> Oct 25, 2025</span>
              <span class="news-category"><i class="fas fa-tag"></i> Feature</span>
            </div>
            <h3 class="news-title">Introducing Team Voice Chat</h3>
            <p class="news-excerpt">
              Coordinate with your team better than ever with our new integrated
              voice chat feature during matches.
            </p>
            <a href="#" class="news-link">
              Read More <i class="fas fa-arrow-right"></i>
            </a>
          </div>
        </article>
      </div>
    </div>
  </section>

  <!-- ========== COMMUNITY CTA ========== -->
  <section class="community-cta">
    <div class="container">
      <div class="cta-content">
        <div class="cta-icon">
          <i class="fas fa-users"></i>
        </div>
        <h2 class="cta-title">Join the GAME X Community</h2>
        <p class="cta-description">
          Compete, chat, and connect with fellow gamers worldwide.
          Create your account today and start your esports journey!
        </p>
        <div class="cta-buttons">
          <a href="auth/create_account.php" class="btn btn-primary btn-large">
            <i class="fas fa-user-plus"></i> Create Account
          </a>
          <a href="tournament.php" class="btn btn-outline-light btn-large">
            <i class="fas fa-trophy"></i> Browse Tournaments
          </a>
        </div>

        <div class="cta-features">
          <div class="cta-feature-item">
            <i class="fas fa-check-circle"></i>
            <span>Free to Join</span>
          </div>
          <div class="cta-feature-item">
            <i class="fas fa-check-circle"></i>
            <span>No Hidden Fees</span>
          </div>
          <div class="cta-feature-item">
            <i class="fas fa-check-circle"></i>
            <span>Instant Access</span>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ========== FOOTER ========== -->
  <footer class="footer">
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
        <p>&copy; <?php echo date("Y"); ?> Game X Community. All rights reserved.</p>
        <p class="footer-made-with">Made with <i class="fas fa-heart"></i> for gamers</p>
      </div>
    </div>
  </footer>

  <!-- Scroll to Top Button -->
  <button id="scrollToTop" class="scroll-to-top">
    <i class="fas fa-arrow-up"></i>
  </button>

  <script src="assets/js/index.js"></script>
  <script src="assets/js/darkmode.js"></script>

</body>

</html>