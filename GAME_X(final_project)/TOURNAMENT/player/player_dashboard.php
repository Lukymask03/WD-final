<?php
session_start();
// Correct file pathing — only go one level up (no “tournament” folder)
require_once __DIR__ . '/../backend/helpers/auth_guard.php';
require_once __DIR__ . '/../backend/db.php';

// Protect page so only players can access
checkAuth('player');

// Get player info from session
$username = $_SESSION['username'] ?? 'Player';

$tournaments = [];
$selectedTournament = $_GET['tournament_id'] ?? null;
$matches = [];

try {
  // Example query — adjust table names based on your schema
  $stmt = $conn->prepare("SELECT tournament_id, title, status, start_date 
                        FROM tournaments 
                        WHERE organizer_id = ?");
  $stmt->execute([$_SESSION['account_id']]);
  $tournaments = $stmt->fetchAll();

  // If a tournament is selected, fetch matches
  if ($selectedTournament) {
    $stmt2 = $conn->prepare("SELECT * FROM matches WHERE tournament_id = ?");
    $stmt2->execute([$selectedTournament]);
    $matches = $stmt2->fetchAll();
  }
} catch (PDOException $e) {
  die("Error fetching tournaments: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Player Dashboard | GameX</title>
  <link rel="stylesheet" href="../assets/css/common.css" />
  <link rel="stylesheet" href="../assets/css/player_dashboard.css" />
  <link rel="stylesheet" href="../assets/css/sweetalert2.min.css">
</head>

<body>
  <!-- ========== NAVBAR ========== -->
  <?php require_once "../includes/player/player_navbar.php"; ?>

  <!-- === MAIN CONTENT === -->
  <main class="dashboard-container">
    <!-- Welcome Section -->
    <section class="welcome-box">
      <h1>Welcome, <span class="highlight"><?= htmlspecialchars($username) ?></span></h1>
      <p>Your role: <strong>Player</strong></p>
      <div class="quick-actions">
        <a href="teams.php" class="cta-btn">+ Create Team</a>
        <a href="register_tournament.php" class="cta-btn">Register Tournament</a>
      </div>
    </section>

    <!-- My Tournaments -->
    <section class="tournaments-section">
      <h2>My Tournaments</h2>
      <?php if (!empty($tournaments)): ?>
        <ul class="tournament-list">
          <?php foreach ($tournaments as $t): ?>
            <li>
              <a href="?tournament_id=<?= (int)$t['id'] ?>" <?= ($t['id'] == $selectedTournament) ? 'class="selected"' : '' ?>>
                <?= htmlspecialchars($t['name']) ?>
              </a>
              - <small>Status: <span class="status"><?= htmlspecialchars($t['status']) ?></span></small>,
              <small>Starts: <?= htmlspecialchars($t['start_date']) ?></small>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p class="text-muted">You haven't joined any tournaments yet.</p>
      <?php endif; ?>
    </section>

    <!-- Brackets Viewer -->
    <section class="bracket-section">
      <h2> Tournament Brackets</h2>
      <?php if ($selectedTournament): ?>
        <div class="bracket-block">
          <h3>Bracket for
            <?= htmlspecialchars(
              array_column(
                array_filter($tournaments, fn($t) => $t['id'] == $selectedTournament),
                'name'
              )[0] ?? 'Selected Tournament'
            ) ?>
          </h3>
          <?php if (!empty($matches)): ?>
            <?php foreach ($matches as $m): ?>
              <div class="match-card">
                <p><strong><?= htmlspecialchars($m['round_name']) ?> - Match <?= htmlspecialchars($m['match_no']) ?></strong></p>
                <p><?= htmlspecialchars($m['team_a']) ?> vs <?= htmlspecialchars($m['team_b']) ?></p>
                <p>Winner: <span class="winner"><?= htmlspecialchars($m['winner'] ?? 'TBD') ?></span></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p class="text-muted">No matches yet for this tournament.</p>
          <?php endif; ?>
        </div>
      <?php else: ?>
        <p class="text-muted">Select a tournament to view its bracket.</p>
      <?php endif; ?>
    </section>
  </main>

  <!-- === FOOTER === -->
  <footer class="footer">
    <p>© 2025 GameX Tournament Platform. All rights reserved.</p>
  </footer>
</body>
<!-- SweetAlert2 JS -->
<script src="../assets/js/sweetalert2.all.min.js"></script>
<script>
  <?php if (isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
    Swal.fire({
      icon: 'success',
      title: 'Welcome Back! 🎮',
      html: '<p style="font-size: 1.1rem; margin-top: 10px;">Hello <strong><?= htmlspecialchars($username) ?></strong>! Ready to dominate the arena?</p>',
      timer: 3000,
      timerProgressBar: true,
      showConfirmButton: false,
      background: '#1a1a1a',
      color: '#fff',
      iconColor: '#ff6600',
      customClass: {
        popup: 'swal-dark-theme'
      }
    });
    <?php unset($_SESSION['login_success']); ?>
  <?php endif; ?>
</script>

<script src="../assets/js/darkmode_toggle.js"></script>
<script src="../assets/js/index.js"></script>

</html>