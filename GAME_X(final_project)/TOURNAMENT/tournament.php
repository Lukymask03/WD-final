<?php
session_start();
include 'backend/db.php';

 

// Detect if user is logged in
$isLoggedIn = isset($_SESSION['account_id']);
$userRole = null;

if ($isLoggedIn) {
  $stmt = $conn->prepare("SELECT role FROM accounts WHERE account_id = :id");
  $stmt->execute(['id' => $_SESSION['account_id']]);
  $user = $stmt->fetch(PDO::FETCH_ASSOC);
  $userRole = $user ? $user['role'] : null;
}

// Function to render Valorant tournaments
function renderValorantTournaments($conn, $isLoggedIn, $userRole)
{
  $stmt = $conn->prepare("SELECT * FROM tournaments ORDER BY start_date DESC");
  $stmt->execute();
  $tournaments = array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), function ($t) {
    return stripos($t['title'], 'Valorant') !== false;
  });

  echo "<section class='tournament-section'>";
  echo "<h2 class='section-title'>All Tournaments</h2>";

  if (count($tournaments) > 0) {
    echo "<div class='tournament-grid'>";
    foreach ($tournaments as $t) {
      $image = isset($t['image']) && !empty($t['image'])
        ? "uploads/" . htmlspecialchars($t['image'])
        : "assets/images/VALORANT.png";

      if ($t['status'] === 'open') {
        $buttonText = 'Join Tournament';
        $buttonClass = 'btn join-btn';
        if ($isLoggedIn && $userRole === 'player') {
          $buttonLink = "register_tournament.php?id={$t['tournament_id']}";
          $extraAttr = "";
        } elseif ($isLoggedIn && $userRole !== 'player') {
          $buttonLink = "#";
          $extraAttr = "onclick='alert(\"Only player accounts can join tournaments.\")'";
        } else {
          $buttonLink = "#";
          $extraAttr = "onclick='showLoginModal()'";
        }
      } else {
        $buttonText = 'View Summary';
        $buttonLink = "tournament_summary.php?id={$t['tournament_id']}";
        $buttonClass = 'btn view-btn';
        $extraAttr = "";
      }

      $statusBadge = $t['status'] === 'open'
        ? "<span class='status-badge status-open'><i class='fas fa-circle'></i> Open</span>"
        : "<span class='status-badge status-closed'><i class='fas fa-lock'></i> Closed</span>";

      echo "
            <div class='tournament-card'>
                <div class='tournament-image-container'>
                    <img src='{$image}' alt='" . htmlspecialchars($t['title']) . "' class='tournament-img'>
                    <div class='tournament-overlay'>
                        {$statusBadge}
                    </div>
                </div>
                <div class='tournament-info'>
                    <div class='tournament-header'>
                        <h3 class='tournament-title'><i class='fas fa-trophy'></i> " . htmlspecialchars($t['title']) . "</h3>
                    </div>
                    <p class='tournament-description'>" . htmlspecialchars($t['description'] ?? 'Join this exciting tournament and compete with the best!') . "</p>
                    <div class='tournament-meta'>
                        <div class='meta-item'>
                            <i class='far fa-calendar-alt'></i>
                            <span>" . date('F d, Y', strtotime($t['start_date'])) . "</span>
                        </div>
                        <div class='meta-item'>
                            <i class='fas fa-users'></i>
                            <span>Teams: TBA</span>
                        </div>
                    </div>
                    <a href='$buttonLink' class='$buttonClass' $extraAttr>
                        <i class='fas fa-" . ($t['status'] === 'open' ? 'sign-in-alt' : 'eye') . "'></i>
                        $buttonText
                    </a>
                </div>
            </div>";
    }
    echo "</div>";
  } else {
    echo "<p class='no-tournaments'>No tournaments available yet.</p>";
  }
  echo "</section>";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Tournaments</title>
  <link rel="stylesheet" href="assets/css/common.css">
  <link rel="stylesheet" href="assets/css/tournament.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <script src="assets/js/tournament.js" defer></script>
</head>

<body>

  <header class='navbar'>
    <div class='logo'>
      <a href='index.php' class='logo-link'>
        <img src='assets/images/game_x_logo.png' alt='Game X Community' class='logo-img' />
        <h1><span class='highlight-orange'>GAME</span><span class='highlight-red'> X</span></h1>
      </a>
    </div>
    <nav class="nav-menu">
      <a href='index.php' class='nav-link'><i class="fas fa-home"></i> Home</a>
      <a href='tournament.php' class='nav-link active'><i class="fas fa-trophy"></i> Tournaments</a>
      <a href='about.php' class='nav-link'><i class="fas fa-info-circle"></i> About</a>
      <a href='contact.php' class='nav-link'><i class="fas fa-envelope"></i> Contact</a>
    </nav>
    <div class='nav-actions'>
      <?php if ($isLoggedIn): ?>
        <button id="darkModeToggle" class="darkmode-btn">
          <i class="fas fa-moon"></i>
          <span>Dark Mode</span>
        </button>
        <a href='auth/logout.php' class='cta-btn'><i class="fas fa-sign-out-alt"></i> Logout</a>
      <?php else: ?>
        <button id="darkModeToggle" class="darkmode-btn">
          <i class="fas fa-moon"></i>
          <span>Dark Mode</span>
        </button>
        <a href='auth/login.php' class='cta-btn'><i class="fas fa-user"></i> Login</a>
      <?php endif; ?>
    </div>

    <button class="mobile-menu-toggle" id="mobileMenuToggle">
      <i class="fas fa-bars"></i>
    </button>
  </header>

  <section class='hero'>
    <div class='hero-content'>
      <div class='hero-left'>
        <div class='hero-badge'>
          <i class="fas fa-fire"></i> LIVE TOURNAMENTS
        </div>
        <h1 class='hero-title'>
          Join & Compete in<br>
          <span class='gradient-text'>Esports</span> Battles
        </h1>
        <p class='hero-description'>
          Prove your skill in <strong id="gameName">Valorant</strong> and forge your legend.
          <strong>Only the best make it to the top.</strong>
        </p>

        <div class='hero-features'>
          <div class='feature-item'>
            <i class="fas fa-trophy"></i>
            <div class='feature-content'>
              <span class='feature-title'>Competitive</span>
              <span class='feature-desc'>High-stakes matches</span>
            </div>
          </div>
          <div class='feature-item'>
            <i class="fas fa-users"></i>
            <div class='feature-content'>
              <span class='feature-title'>Team-Based</span>
              <span class='feature-desc'>5v5 tournaments</span>
            </div>
          </div>
          <div class='feature-item'>
            <i class="fas fa-medal"></i>
            <div class='feature-content'>
              <span class='feature-title'>Prizes</span>
              <span class='feature-desc'>Win big rewards</span>
            </div>
          </div>
        </div>

        <div class='hero-cta'>
          <?php if ($isLoggedIn && $userRole === 'player'): ?>
            <a href='register_tournament.php' class='join-tournament-btn'>
              <i class="fas fa-sign-in-alt"></i>
              <span>Join Tournament</span>
            </a>
            <a href='#tournaments' class='browse-btn'>
              <i class="fas fa-search"></i>
              <span>Browse All</span>
            </a>
          <?php elseif ($isLoggedIn && $userRole !== 'player'): ?>
            <a href='#' class='join-tournament-btn' onclick='alert("Only player accounts can join tournaments.")'>
              <i class="fas fa-sign-in-alt"></i>
              <span>Join Tournament</span>
            </a>
            <a href='#tournaments' class='browse-btn'>
              <i class="fas fa-search"></i>
              <span>Browse All</span>
            </a>
          <?php else: ?>
            <a href='#' class='join-tournament-btn' onclick='showLoginModal()'>
              <i class="fas fa-sign-in-alt"></i>
              <span>Join Tournament</span>
            </a>
            <a href='#tournaments' class='browse-btn'>
              <i class="fas fa-search"></i>
              <span>Browse All</span>
            </a>
          <?php endif; ?>
        </div>
      </div>

      <aside class='hero-right'>
        <div class='slideshow-container'>
          <div class='slide fade'><img src='assets/images/VALORANT.png' alt='Valorant'></div>
          <div class='slide fade'><img src='assets/images/VALORANT2.png' alt='Valorant'></div>
          <div class='slide fade'><img src='assets/images/VALORANT3.png' alt='Valorant'></div>

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

  <!-- LOGIN MODAL -->
  <div id="loginModal" class="modal">
    <div class="modal-content styled-modal">
      <span class="close" onclick="closeLoginModal()">&times;</span>
      <h2>Members Only</h2>
      <p>You need to be a registered member to join tournaments.</p>
      <div class="modal-buttons">
        <a href="auth/create_account.php" class="modal-btn primary">Be a Member</a>
        <a href="auth/login.php" class="modal-btn accent">Login</a>
        <button onclick="closeLoginModal()" class="modal-btn neutral">Cancel</button>
      </div>
    </div>
  </div>

  <div class='page-container' id='tournaments'>
    <?php renderValorantTournaments($conn, $isLoggedIn, $userRole); ?>
  </div>

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
  <!---   SCRIPTS -->
  <script>
     document.getElementById("tournament-form").addEventListener("submit", function(event) {
     event.preventDefault();

      // Send form data to the server
       fetch("/create-tournament", {
         method: "POST",
          headers: {
             "Content-Type": "application/x-www-form-urlencoded"
          },
         body: new URLSearchParams(new FormData(event.target))
       })
      .then(response => response.json())
      .then(data => {
         if (data.success) {
             // Display success message
             const successMessage = document.querySelector('.success-message');
             successMessage.textContent = data.message;
            
             // Redirect to the tournament page
             setTimeout(() => {
                 window.location.href = "/tournament.php?id=" + data.tournament_id;
             }, 2000);
          } else {
             // Display error message
             const errorMessage = document.querySelector('.error-message');
             errorMessage.textContent = data.error;
          }
     })
       .catch(error => {
         // Handle any errors
         console.error(error);
       });
    });
  </script>
  <script src="assets/js/tournament.js"></script>
  <script src="assets/js/darkmode.js"></script>
  <script src="assets/js/index.js"></script>
</body>

</html>