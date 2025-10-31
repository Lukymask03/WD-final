<?php
session_start();
require_once "backend/db.php";

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
function renderValorantTournaments($conn, $isLoggedIn, $userRole) {
    $stmt = $conn->prepare("SELECT * FROM tournaments ORDER BY start_date DESC");
    $stmt->execute();
    $tournaments = array_filter($stmt->fetchAll(PDO::FETCH_ASSOC), function($t) {
        return stripos($t['title'], 'Valorant') !== false;
    });

    echo "<section class='tournament-section'>";
    echo "<h2 class='section-title'>All Valorant Tournaments</h2>";

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

            echo "
            <div class='tournament-card'>
                <img src='{$image}' alt='" . htmlspecialchars($t['title']) . "' class='tournament-img'>
                <div class='tournament-info'>
                    <h3>" . htmlspecialchars($t['title']) . "</h3>
                    <p>" . htmlspecialchars($t['description'] ?? '') . "</p>
                    <p><strong>Start:</strong> " . date('F d, Y', strtotime($t['start_date'])) . "</p>
                    <a href='$buttonLink' class='$buttonClass' $extraAttr>$buttonText</a>
                </div>
            </div>";
        }
        echo "</div>";
    } else {
        echo "<p class='no-tournaments'>No Valorant tournaments available yet.</p>";
    }
    echo "</section>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Valorant Tournaments | Game X</title>
  <link rel="stylesheet" href="assets/css/common.css">
  <link rel="stylesheet" href="assets/css/tournament.css">
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
  <nav>
    <a href='index.php' class='nav-link'>Home</a>
    <a href='about.php' class='nav-link'>About</a>
    <a href='tournament.php' class='nav-link active'>Tournaments</a>
    <a href='contact.php' class='nav-link'>Contact</a>
  </nav>
  <div class='nav-actions'>
    <?php if ($isLoggedIn): ?>
      <a href='auth/logout.php' class='cta-btn'>Logout</a>
    <?php else: ?>
      <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
      <a href='auth/login.php' class='cta-btn'>Register / Login</a>
    <?php endif; ?>
  </div>
</header>

<section class='hero'>
  <div class='hero-left'>
      <h1>Join & Compete in Valorant Esports Battles</h1>
      <p>
        Prove your skill in <strong>Valorant</strong> and forge your legend.<br>
        <strong>Only the best make it to the top.</strong>
      </p>
      <div class='hero-cta'>
        <?php if ($isLoggedIn && $userRole === 'player'): ?>
          <a href='register_tournament.php' class='join-tournament-btn'>Join Tournament</a>
        <?php elseif ($isLoggedIn && $userRole !== 'player'): ?>
          <a href='#' class='join-tournament-btn' onclick='alert("Only player accounts can join tournaments.")'>Join Tournament</a>
        <?php else: ?>
          <a href='#' class='join-tournament-btn' onclick='showLoginModal()'>Join Tournament</a>
        <?php endif; ?>
      </div>
  </div>
  <!-- LOGIN MODAL (Styled Like Login.css) -->
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

  <aside class='hero-right'>
    <div class='slideshow-container'>
      <div class='slide fade'><img src='assets/images/VALORANT.png' alt='Valorant'></div>
      <div class='slide fade'><img src='assets/images/VALORANT2.png' alt='Valorant'></div>
      <div class='slide fade'><img src='assets/images/VALORANT3.png' alt='Valorant'></div>
    </div>
  </aside>
</section>

<div class='page-container'>
  <?php renderValorantTournaments($conn, $isLoggedIn, $userRole); ?>
</div>

<footer class='footer'>
  <p>&copy; <?php echo date('Y'); ?> Game X Community. All rights reserved.</p>
</footer>

<script src="assets/js/tournament.js"></script>
<script src="assets/js/darkmode.js"></script>
</body>
</html>
