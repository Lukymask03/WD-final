<?php
// ========================================
// MANAGE MATCHES - ORGANIZER
// ========================================

session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/auth_guard.php';

checkAuth('organizer');

// ===== Get organizer_id from organizer_profiles table =====
$stmtProfile = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
$stmtProfile->execute([$_SESSION['account_id']]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Organizer profile not found. Please complete your profile first.");
}

$organizer_id = $profile['organizer_id'];

// Fetch all tournaments by this organizer
try {
    $tournamentsStmt = $conn->prepare("
        SELECT tournament_id, title, game, status
        FROM tournaments
        WHERE organizer_id = ?
        ORDER BY start_date DESC
    ");
    $tournamentsStmt->execute([$organizer_id]);
    $tournaments = $tournamentsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

// Get selected tournament
$selected_tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : 0;
$matches = [];

if ($selected_tournament_id) {
    // Verify tournament belongs to this organizer
    $verifyStmt = $conn->prepare("SELECT tournament_id FROM tournaments WHERE tournament_id = ? AND organizer_id = ?");
    $verifyStmt->execute([$selected_tournament_id, $organizer_id]);

    if ($verifyStmt->fetch()) {
        // Fetch matches for this tournament
        try {
            $matchesStmt = $conn->prepare("
                SELECT 
                    m.*,
                    t1.team_name as team1_name,
                    t2.team_name as team2_name
                FROM matches m
                LEFT JOIN teams t1 ON m.team1_id = t1.team_id
                LEFT JOIN teams t2 ON m.team2_id = t2.team_id
                WHERE m.tournament_id = ?
                ORDER BY m.match_date ASC, m.match_time ASC
            ");
            $matchesStmt->execute([$selected_tournament_id]);
            $matches = $matchesStmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            $error = "Error fetching matches: " . htmlspecialchars($e->getMessage());
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Matches - Game X</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Modern Organizer CSS -->
    <link rel="stylesheet" href="../assets/css/organizer_modern.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ebf0 100%);
            min-height: 100vh;
        }

        main.content {
            margin-left: 280px;
            padding: 100px 50px 50px;
            min-height: 100vh;
            animation: fadeIn 0.4s ease-in;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* ====================== PAGE HEADER ====================== */
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
            color: white;
        }

        .page-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .page-header p {
            font-size: 16px;
            opacity: 0.95;
        }

        /* ====================== TOURNAMENT SELECTOR ====================== */
        .tournament-selector {
            background: white;
            padding: 30px;
            border-radius: 16px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .tournament-selector h2 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .selector-form {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .selector-form select {
            flex: 1;
            min-width: 300px;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .selector-form select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            white-space: nowrap;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-create {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }

        .btn-create:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }

        /* ====================== MATCHES CONTAINER ====================== */
        .matches-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .matches-header {
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .matches-header h2 {
            font-size: 20px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* ====================== MATCH CARD ====================== */
        .match-card {
            padding: 25px 30px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.3s;
        }

        .match-card:hover {
            background: linear-gradient(90deg, rgba(102, 126, 234, 0.03) 0%, transparent 100%);
        }

        .match-card:last-child {
            border-bottom: none;
        }

        .match-info {
            display: grid;
            grid-template-columns: 2fr 1fr 2fr 1.5fr 1fr;
            gap: 20px;
            align-items: center;
        }

        .team-section {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .team-badge {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }

        .team-name {
            font-weight: 600;
            color: #2c3e50;
            font-size: 16px;
        }

        .match-vs {
            text-align: center;
            font-weight: 700;
            color: #999;
            font-size: 18px;
        }

        .match-details {
            color: #666;
            font-size: 14px;
        }

        .match-details i {
            color: #667eea;
            margin-right: 5px;
        }

        .match-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }

        .status-scheduled {
            background: linear-gradient(135deg, #fff3cd 0%, #ffeeba 100%);
            color: #856404;
            border: 2px solid #ffc107;
        }

        .status-ongoing {
            background: linear-gradient(135deg, #d1ecf1 0%, #bee5eb 100%);
            color: #0c5460;
            border: 2px solid #17a2b8;
        }

        .status-completed {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border: 2px solid #28a745;
        }

        .status-cancelled {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border: 2px solid #dc3545;
        }

        .match-actions {
            display: flex;
            gap: 8px;
            justify-content: center;
        }

        .action-btn {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            text-decoration: none;
            color: white;
            transition: all 0.3s;
            font-size: 14px;
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .action-edit {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }

        .action-delete {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        }

        /* ====================== EMPTY STATE ====================== */
        .empty-state {
            text-align: center;
            padding: 80px 30px;
            color: #999;
        }

        .empty-state i {
            font-size: 80px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 25px;
        }

        /* ====================== RESPONSIVE ====================== */
        @media (max-width: 1200px) {
            .match-info {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .match-vs {
                display: none;
            }
        }

        @media (max-width: 768px) {
            main.content {
                margin-left: 0;
                padding: 90px 20px 40px;
            }

            .page-header {
                padding: 25px;
            }

            .page-header h1 {
                font-size: 24px;
            }

            .tournament-selector {
                padding: 20px;
            }

            .selector-form {
                flex-direction: column;
            }

            .selector-form select {
                min-width: 100%;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .match-card {
                padding: 20px;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include "../includes/organizer/organizer_header.php"; ?>
    <?php include "../includes/organizer/organizer_sidebar.php"; ?>

    <main class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fa-solid fa-calendar-check"></i> Manage Matches</h1>
            <p>Create, schedule, and manage matches for your tournaments</p>
        </div>

        <!-- Tournament Selector -->
        <div class="tournament-selector">
            <h2><i class="fa-solid fa-trophy"></i> Select Tournament</h2>
            <form method="GET" class="selector-form">
                <select name="tournament_id" id="tournament_id" required>
                    <option value="">-- Select a Tournament --</option>
                    <?php foreach ($tournaments as $t): ?>
                        <option value="<?= $t['tournament_id'] ?>" <?= $selected_tournament_id == $t['tournament_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($t['title']) ?> (<?= htmlspecialchars($t['game']) ?>) - <?= strtoupper($t['status']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary">
                    <i class="fa fa-search"></i> View Matches
                </button>
            </form>
        </div>

        <!-- Matches List -->
        <?php if ($selected_tournament_id): ?>
            <div class="matches-container">
                <div class="matches-header">
                    <h2><i class="fa-solid fa-list"></i> Matches (<?= count($matches) ?>)</h2>
                    <a href="create_match.php?tournament_id=<?= $selected_tournament_id ?>" class="btn btn-create">
                        <i class="fa fa-plus"></i> Create Match
                    </a>
                </div>

                <?php if ($matches): ?>
                    <?php foreach ($matches as $match): ?>
                        <div class="match-card">
                            <div class="match-info">
                                <!-- Team 1 -->
                                <div class="team-section">
                                    <div class="team-badge">
                                        <?= strtoupper(substr($match['team1_name'] ?? 'TBD', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="team-name"><?= htmlspecialchars($match['team1_name'] ?? 'TBD') ?></div>
                                        <?php if (isset($match['team1_score'])): ?>
                                            <small style="color: #999;">Score: <?= $match['team1_score'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- VS -->
                                <div class="match-vs">VS</div>

                                <!-- Team 2 -->
                                <div class="team-section">
                                    <div class="team-badge">
                                        <?= strtoupper(substr($match['team2_name'] ?? 'TBD', 0, 1)) ?>
                                    </div>
                                    <div>
                                        <div class="team-name"><?= htmlspecialchars($match['team2_name'] ?? 'TBD') ?></div>
                                        <?php if (isset($match['team2_score'])): ?>
                                            <small style="color: #999;">Score: <?= $match['team2_score'] ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Match Details -->
                                <div class="match-details">
                                    <?php if ($match['match_date']): ?>
                                        <div><i class="fa fa-calendar"></i> <?= date('M d, Y', strtotime($match['match_date'])) ?></div>
                                    <?php endif; ?>
                                    <?php if ($match['match_time']): ?>
                                        <div><i class="fa fa-clock"></i> <?= date('h:i A', strtotime($match['match_time'])) ?></div>
                                    <?php endif; ?>
                                    <?php if (isset($match['round'])): ?>
                                        <div><i class="fa fa-layer-group"></i> Round <?= $match['round'] ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Status & Actions -->
                                <div>
                                    <div class="match-status status-<?= $match['status'] ?? 'scheduled' ?>">
                                        <?= strtoupper($match['status'] ?? 'Scheduled') ?>
                                    </div>
                                    <div class="match-actions" style="margin-top: 10px;">
                                        <a href="edit_match.php?match_id=<?= $match['match_id'] ?>"
                                            class="action-btn action-edit"
                                            title="Edit Match">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="../backend/delete_match.php?match_id=<?= $match['match_id'] ?>"
                                            onclick="return confirm('Delete this match?');"
                                            class="action-btn action-delete"
                                            title="Delete Match">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-calendar-xmark"></i>
                        <h3>No Matches Yet</h3>
                        <p>No matches have been created for this tournament.</p>
                        <a href="create_match.php?tournament_id=<?= $selected_tournament_id ?>" class="btn btn-create">
                            <i class="fa fa-plus"></i> Create First Match
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>

    <script>
        // Auto-submit form when tournament is selected
        document.getElementById('tournament_id').addEventListener('change', function() {
            if (this.value) {
                this.form.submit();
            }
        });
    </script>

</body>

</html>