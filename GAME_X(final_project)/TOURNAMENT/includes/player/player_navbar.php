<?php
// Ensure session is started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in as player
$isPlayer = isset($_SESSION['role']) && $_SESSION['role'] === 'player';
?>

<header class="navbar">
  <div class="logo">
    <a href="<?= $isPlayer ? 'player_dashboard.php' : '../index.php' ?>" class="logo-link">
      <img src="<?= $isPlayer ? '../assets/images/game_x_logo.png' : 'assets/images/game_x_logo.png' ?>" alt="Game X Community" class="logo-img" />
      <h1><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h1>
    </a>
  </div>

  <nav>
    <a href="player_dashboard.php">Dashboard</a>
    <a href="register_tournament.php">Register Tournament</a>
    <a href="teams.php">Teams</a>
    <a href="invite_player.php">Invite Player</a>
    <a href="my_registrations.php">My Tournaments</a>
    <a href="player_contact.php">Support</a>
  </nav>

  <div class="nav-actions">
    <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
    <a href="../auth/logout.php" class="cta-btn">Logout</a>
  </div>
</header>