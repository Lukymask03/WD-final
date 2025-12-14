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

// ✅ 3. Determine tournament ID (GET only, not session - force selection each time)
$tournament_id = $_GET['tournament_id'] ?? null;

// If no tournament selected and there are tournaments available, show selection
if (!$tournament_id && !empty($tournaments)) {
    // Show tournament selection interface
    $showSelection = true;
} elseif (!$tournament_id && empty($tournaments)) {
    // No tournaments at all
    $noTournaments = true;
} else {
    // Save current tournament in session for persistence within the page
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Brackets - Game X<?= isset($tournament) ? ' - ' . htmlspecialchars($tournament['title']) : '' ?></title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Organizer CSS -->
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <style>
        .bracket-container {
            display: flex;
            gap: 30px;
            overflow-x: auto;
            padding: 20px 0;
        }

        .round {
            min-width: 300px;
            flex-shrink: 0;
        }

        .round h2 {
            color: var(--accent);
            margin-bottom: 20px;
            padding: 12px;
            background: linear-gradient(135deg, rgba(255, 94, 0, 0.1), rgba(255, 123, 51, 0.2));
            border-radius: 12px;
            text-align: center;
        }

        .match-card {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--border);
            transition: all 0.3s ease;
        }

        .match-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(255, 94, 0, 0.2);
            border-color: var(--accent);
        }

        .player {
            padding: 15px;
            margin: 8px 0;
            background: #f5f5f5;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .player.winner {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
        }

        .vs {
            text-align: center;
            color: #999;
            font-weight: 700;
            font-size: 14px;
            margin: 5px 0;
        }

        .winner-form {
            margin-top: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .winner-form button {
            padding: 10px;
            background: var(--gradient-primary);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .winner-form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 94, 0, 0.4);
        }

        .winner-label {
            text-align: center;
            color: #4CAF50;
            font-weight: 700;
            margin-top: 15px;
            padding: 10px;
            background: rgba(76, 175, 80, 0.1);
            border-radius: 8px;
        }

        .no-matches {
            text-align: center;
            padding: 60px 20px;
            color: #666;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <?php include '../includes/organizer/organizer_sidebar.php'; ?>

    <main class="org-main">
        <?php if (isset($noTournaments) && $noTournaments): ?>
            <!-- No tournaments available -->
            <section class="org-hero">
                <div class="org-hero-content">
                    <div class="org-hero-badge">
                        <i class="fas fa-project-diagram"></i>
                        Manage Brackets
                    </div>
                    <h1>Manage Tournament Brackets 🏆</h1>
                    <p>Create and manage tournament brackets for competitive matches</p>
                </div>
            </section>

            <div class="org-empty-state" style="margin-top: 40px;">
                <i class="fas fa-trophy"></i>
                <h3>No Tournaments Found</h3>
                <p>You haven't created any tournaments yet.</p>
                <a href="create_tournament.php" class="org-btn">
                    <i class="fas fa-plus-circle"></i> Create Your First Tournament
                </a>
            </div>

        <?php elseif (isset($showSelection) && $showSelection): ?>
            <!-- Tournament Selection -->
            <section class="org-hero">
                <div class="org-hero-content">
                    <div class="org-hero-badge">
                        <i class="fas fa-project-diagram"></i>
                        Manage Brackets
                    </div>
                    <h1>Select a Tournament 🏆</h1>
                    <p>Choose a tournament to manage its bracket structure</p>
                </div>
            </section>

            <div class="org-content-grid" style="margin-top: 40px;">
                <?php foreach ($tournaments as $t): ?>
                    <a href="manage_brackets.php?tournament_id=<?= htmlspecialchars($t['tournament_id']) ?>" class="org-card" style="text-decoration: none; color: inherit;">
                        <div class="org-card-header">
                            <div class="org-card-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h3 class="org-card-title"><?= htmlspecialchars($t['title']) ?></h3>
                        </div>
                        <div class="org-card-content">
                            <p><strong>Start:</strong> <?= date('M d, Y', strtotime($t['start_date'])) ?></p>
                            <p><strong>End:</strong> <?= date('M d, Y', strtotime($t['end_date'])) ?></p>
                            <p>
                                <?php
                                $badgeClass = 'org-badge-info';
                                if ($t['status'] == 'active') $badgeClass = 'org-badge-success';
                                elseif ($t['status'] == 'completed') $badgeClass = 'org-badge-warning';
                                ?>
                                <span class="org-badge <?= $badgeClass ?>">
                                    <?= htmlspecialchars($t['status']) ?>
                                </span>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- Bracket Management View -->
            <section class="org-hero">
                <div class="org-hero-content">
                    <div class="org-hero-badge">
                        <i class="fas fa-project-diagram"></i>
                        Bracket Management
                    </div>
                    <h1><?= htmlspecialchars($tournament['title']) ?> 🏆</h1>
                    <p>Manage matches and set winners for each round</p>
                </div>
            </section>

            <div style="margin-bottom: 30px;">
                <a href="manage_brackets.php" class="org-btn org-btn-secondary">
                    <i class="fas fa-arrow-left"></i> Switch Tournament
                </a>
            </div>

            <div class="org-card">
                <div class="bracket-container">
                    <?php if (!empty($rounds)): ?>
                        <?php foreach ($rounds as $round => $matches): ?>
                            <div class="round">
                                <h2><i class="fas fa-flag"></i> Round <?= htmlspecialchars($round) ?></h2>
                                <?php foreach ($matches as $match): ?>
                                    <div class="match-card">
                                        <div class="player <?= ($match['winner'] === $match['team1']) ? 'winner' : '' ?>">
                                            <?= htmlspecialchars($match['team1'] ?? 'TBD') ?>
                                        </div>
                                        <div class="vs">VS</div>
                                        <div class="player <?= ($match['winner'] === $match['team2']) ? 'winner' : '' ?>">
                                            <?= htmlspecialchars($match['team2'] ?? 'TBD') ?>
                                        </div>

                                        <?php if (empty($match['winner'])): ?>
                                            <form method="POST" action="update_match_result.php" class="winner-form">
                                                <input type="hidden" name="match_id" value="<?= htmlspecialchars($match['match_id']) ?>">
                                                <button type="submit" name="winner" value="<?= htmlspecialchars($match['team1']) ?>">
                                                    <i class="fas fa-crown"></i> Set Winner: <?= htmlspecialchars($match['team1'] ?? 'TBD') ?>
                                                </button>
                                                <button type="submit" name="winner" value="<?= htmlspecialchars($match['team2']) ?>">
                                                    <i class="fas fa-crown"></i> Set Winner: <?= htmlspecialchars($match['team2'] ?? 'TBD') ?>
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <p class="winner-label">
                                                <i class="fas fa-trophy"></i> Winner: <?= htmlspecialchars($match['winner']) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="org-empty-state" style="width: 100%;">
                            <i class="fas fa-project-diagram"></i>
                            <h3>No Matches Found</h3>
                            <p>No bracket matches have been created for this tournament yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>

</html>