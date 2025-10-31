<?php
session_start();
require_once "../backend/db.php";

// 🔍 Optional Debugging
// echo '<pre>'; print_r($_SESSION); echo '</pre>'; exit;

// ✅ 1. Session guard: organizer-only access
if (
    empty($_SESSION['account_id']) ||
    empty($_SESSION['user_role']) ||
    strtolower($_SESSION['user_role']) !== 'organizer'
) {
    header("Location: ../auth/login.php");
    exit;
}

// ✅ 2. Determine tournament ID (GET or SESSION)
$tournament_id = $_GET['tournament_id'] ?? $_SESSION['current_tournament_id'] ?? null;
if (!$tournament_id) {
    // Redirect to selection page if no tournament selected
    header("Location: select_tournament.php");
    exit;
}

// Save current tournament in session for persistence
$_SESSION['current_tournament_id'] = $tournament_id;

// ✅ 3. Fetch tournament details
$stmt = $conn->prepare("
    SELECT tournament_id, title, status, start_date, end_date
    FROM tournaments
    WHERE tournament_id = ?
");
$stmt->execute([$tournament_id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tournament) {
    die("Tournament not found.");
}

// ✅ 4. Fetch matches
try {
    $stmt = $conn->prepare("
        SELECT 
            m.match_id,
            m.round,
            t1.team_name AS team1,
            t2.team_name AS team2,
            w.team_name AS winner
        FROM matches m
        LEFT JOIN teams t1 ON m.team1_id = t1.team_id
        LEFT JOIN teams t2 ON m.team2_id = t2.team_id
        LEFT JOIN teams w ON m.winner_id = w.team_id
        WHERE m.tournament_id = ?
        ORDER BY m.round ASC
    ");
    $stmt->execute([$tournament_id]);
    $matches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $matches = [];
}

// ✅ 5. Group matches by round
$rounds = [];
foreach ($matches as $match) {
    $rounds[$match['round']][] = $match;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Brackets - <?= htmlspecialchars($tournament['title']) ?></title>
    <link rel="stylesheet" href="../assets/css/manage_bracket.css">
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


    <!-- ==== MAIN CONTENT ==== -->
    <div class="container">
        <h1><?= htmlspecialchars($tournament['title']) ?> - Bracket Management</h1>

        <div class="bracket-container">
            <?php if (!empty($rounds)): ?>
                <?php foreach ($rounds as $round => $matches): ?>
                    <div class="round">
                        <h2>Round <?= htmlspecialchars($round) ?></h2>
                        <?php foreach ($matches as $match): ?>
                            <div class="match-card">
                                <div class="player <?= ($match['winner'] === $match['team1']) ? 'winner' : '' ?>">
                                    <?= htmlspecialchars($match['team1'] ?? 'TBD') ?>
                                </div>
                                <div class="vs">vs</div>
                                <div class="player <?= ($match['winner'] === $match['team2']) ? 'winner' : '' ?>">
                                    <?= htmlspecialchars($match['team2'] ?? 'TBD') ?>
                                </div>

                                <?php if (empty($match['winner'])): ?>
                                    <form method="POST" action="update_match_result.php" class="winner-form">
                                        <input type="hidden" name="match_id" value="<?= htmlspecialchars($match['match_id']) ?>">
                                        <button type="submit" name="winner" value="<?= htmlspecialchars($match['team1']) ?>">
                                            Set Winner: <?= htmlspecialchars($match['team1'] ?? 'TBD') ?>
                                        </button>
                                        <button type="submit" name="winner" value="<?= htmlspecialchars($match['team2']) ?>">
                                            Set Winner: <?= htmlspecialchars($match['team2'] ?? 'TBD') ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <p class="winner-label">🏆 Winner: <?= htmlspecialchars($match['winner']) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="no-matches">No matches found for this tournament yet.</p>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
