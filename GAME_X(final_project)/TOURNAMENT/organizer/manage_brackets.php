<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";

// ✅ Use the same auth check as your dashboard
checkAuth('organizer');

// ✅ 2. Fetch all tournaments for this organizer
$stmt = $conn->prepare("
    SELECT tournament_id, title, status, start_date, end_date
    FROM tournaments
    WHERE organizer_id = (SELECT organizer_id FROM organizer_profiles WHERE account_id = ?)
    ORDER BY start_date DESC
");
$stmt->execute([$_SESSION['account_id']]);
$tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ 3. Determine tournament ID (GET or SESSION)
$tournament_id = $_GET['tournament_id'] ?? $_SESSION['current_tournament_id'] ?? null;

// If no tournament selected and there are tournaments available, show selection
if (!$tournament_id && !empty($tournaments)) {
    // Show tournament selection interface
    $showSelection = true;
} elseif (!$tournament_id && empty($tournaments)) {
    // No tournaments at all
    $noTournaments = true;
} else {
    // Save current tournament in session for persistence
    $_SESSION['current_tournament_id'] = $tournament_id;
    
    // ✅ 4. Fetch tournament details
    $stmt = $conn->prepare("
        SELECT t.tournament_id, t.title, t.status, t.start_date, t.end_date
        FROM tournaments t
        INNER JOIN organizer_profiles op ON t.organizer_id = op.organizer_id
        WHERE t.tournament_id = ? AND op.account_id = ?
    ");
    $stmt->execute([$tournament_id, $_SESSION['account_id']]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tournament) {
        die("Tournament not found or you don't have permission to access it.");
    }

    // ✅ 5. Fetch matches
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

    // ✅ 6. Group matches by round
    $rounds = [];
    foreach ($matches as $match) {
        $rounds[$match['round']][] = $match;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Brackets<?= isset($tournament) ? ' - ' . htmlspecialchars($tournament['title']) : '' ?></title>
    <link rel="stylesheet" href="../assets/css/manage_bracket.css">
    <style>
        .tournament-selector {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .tournament-selector h2 {
            color: #ff6600;
            margin-bottom: 20px;
        }
        .tournament-list {
            display: grid;
            gap: 15px;
        }
        .tournament-item {
            padding: 20px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .tournament-item:hover {
            border-color: #ff6600;
            background: #fff5f0;
            transform: translateY(-2px);
        }
        .tournament-item h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .tournament-item .details {
            color: #666;
            font-size: 14px;
        }
        .tournament-item .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
        }
        .status.active { background: #4CAF50; color: white; }
        .status.upcoming { background: #2196F3; color: white; }
        .status.completed { background: #9E9E9E; color: white; }
        .no-tournaments {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .no-tournaments a {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 24px;
            background: #ff6600;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .no-tournaments a:hover {
            background: #e65c00;
        }
    </style>
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
            <a href="view_tournaments.php">Manage Tournaments</a>
            <a href="manage_brackets.php">Manage Brackets</a>
        </nav>

        <div class="nav-actions">
            <a href="../auth/logout.php" class="btn">Logout</a>
        </div>
    </header>

    <!-- ==== MAIN CONTENT ==== -->
    <?php if (isset($noTournaments) && $noTournaments): ?>
        <!-- No tournaments available -->
        <div class="tournament-selector">
            <div class="no-tournaments">
                <h2>No Tournaments Found</h2>
                <p>You haven't created any tournaments yet.</p>
                <a href="create_tournament.php">Create Your First Tournament</a>
            </div>
        </div>

    <?php elseif (isset($showSelection) && $showSelection): ?>
        <!-- Tournament Selection -->
        <div class="tournament-selector">
            <h2>Select a Tournament to Manage Brackets</h2>
            <div class="tournament-list">
                <?php foreach ($tournaments as $t): ?>
                    <a href="manage_brackets.php?tournament_id=<?= htmlspecialchars($t['tournament_id']) ?>" class="tournament-item">
                        <h3><?= htmlspecialchars($t['title']) ?></h3>
                        <div class="details">
                            <p>Start: <?= htmlspecialchars($t['start_date']) ?> | End: <?= htmlspecialchars($t['end_date']) ?></p>
                        </div>
                        <span class="status <?= strtolower($t['status']) ?>">
                            <?= htmlspecialchars($t['status']) ?>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

    <?php else: ?>
        <!-- Bracket Management View -->
        <div class="container">
            <h1><?= htmlspecialchars($tournament['title']) ?> - Bracket Management</h1>
            
            <!-- Tournament Switcher -->
            <div style="margin-bottom: 20px;">
                <a href="manage_brackets.php" style="color: #ff6600; text-decoration: none;">
                    ← Switch Tournament
                </a>
            </div>

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
    <?php endif; ?>
</body>
</html>