<?php
session_start();
require_once "../backend/db.php";

if (!isset($_SESSION['account_id']) || $_SESSION['user_role'] !== 'organizer') {
    header("Location: ../auth/login.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, name FROM tournaments WHERE organizer_id = ?");
$stmt->execute([$_SESSION['account_id']]);
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Select Tournament</title>
    <link rel="stylesheet" href="../assets/css/common.css">
</head>
<body>
  <!-- ==== NAVIGATION BAR ==== -->
  <header class="navbar">
    <div class="logo-link">
      <img src="../assets/images/game_x_logo.png" alt="GameX Logo" class="logo-img" style="height: 40px; vertical-align: middle;">
      <h2>GameX Organizer</h2>
    </div>

<nav>
    <a href="organizer_dashboard.php">Dashboard</a>
    <a href="create_tournament.php">Create Tournament</a>
    <a href="view_tournaments.php">Manage Tournaments</a> <!-- correct link -->
    <a href="select_tournament.php">Manage Brackets</a> <!-- separate, safe -->
</nav>

          <div class="nav-actions">
           <a href="../auth/logout.php" class="btn">Logout</a>
        </div>
  </header>


    <main class="container">
        <h1>Select a Tournament to Manage Brackets</h1>
        <?php if ($tournaments): ?>
            <ul>
                <?php foreach ($tournaments as $t): ?>
                    <li>
                        <a href="manage_brackets.php?tournament_id=<?= $t['id'] ?>">
                            <?= htmlspecialchars($t['name']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>No tournaments found. <a href="create_tournament.php">Create one now.</a></p>
        <?php endif; ?>
    </main>
</body>
</html>
