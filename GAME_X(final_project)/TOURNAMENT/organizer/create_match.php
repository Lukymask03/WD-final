<?php
// ========================================
// CREATE MATCH - ORGANIZER
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

// Get tournament ID
$tournament_id = isset($_GET['tournament_id']) ? intval($_GET['tournament_id']) : 0;

if (!$tournament_id) {
    header("Location: manage_matches.php");
    exit;
}

// Verify tournament belongs to this organizer
try {
    $tournamentStmt = $conn->prepare("SELECT * FROM tournaments WHERE tournament_id = ? AND organizer_id = ?");
    $tournamentStmt->execute([$tournament_id, $organizer_id]);
    $tournament = $tournamentStmt->fetch(PDO::FETCH_ASSOC);

    if (!$tournament) {
        die("Tournament not found or you don't have permission to create matches for it.");
    }

    // Fetch registered teams for this tournament
    $teamsStmt = $conn->prepare("
        SELECT t.team_id, t.team_name
        FROM registrations r
        JOIN teams t ON r.team_id = t.team_id
        WHERE r.tournament_id = ? AND r.status = 'approved'
        ORDER BY t.team_name ASC
    ");
    $teamsStmt->execute([$tournament_id]);
    $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

$success = "";
$error = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $team1_id = intval($_POST['team1_id'] ?? 0);
    $team2_id = intval($_POST['team2_id'] ?? 0);
    $match_date = trim($_POST['match_date'] ?? '');
    $match_time = trim($_POST['match_time'] ?? '');
    $round = intval($_POST['round'] ?? 1);
    $status = trim($_POST['status'] ?? 'scheduled');

    // Validation
    if (!$team1_id || !$team2_id) {
        $error = "Please select both teams.";
    } elseif ($team1_id === $team2_id) {
        $error = "Team 1 and Team 2 cannot be the same.";
    } elseif (!$match_date) {
        $error = "Please select a match date.";
    } elseif (!$match_time) {
        $error = "Please select a match time.";
    }

    // Insert into DB
    if (empty($error)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO matches
                (tournament_id, team1_id, team2_id, match_date, match_time, round, status)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            $stmt->execute([
                $tournament_id,
                $team1_id,
                $team2_id,
                $match_date,
                $match_time,
                $round,
                $status
            ]);

            $success = "Match created successfully!";

            // Clear form
            $team1_id = $team2_id = $round = 0;
            $match_date = $match_time = $status = '';
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Match | <?= htmlspecialchars($tournament['title']) ?></title>

    <link rel="stylesheet" href="../assets/css/organizer_modern.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

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
            padding: 15px 30px;
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

        /* ====================== BACK BUTTON ====================== */
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #666;
            text-decoration: none;
            margin-bottom: 25px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .back-btn:hover {
            color: #667eea;
            transform: translateX(-5px);
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
        }

        .page-header p {
            font-size: 16px;
            opacity: 0.95;
        }

        .tournament-info {
            display: flex;
            gap: 20px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .info-badge {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* ====================== FORM SECTION ====================== */
        .form-section {
            background: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 50px;
            border-radius: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        .form-section h2 {
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 3px solid #667eea;
        }

        /* ====================== MESSAGES ====================== */
        .message {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 2px solid #28a745;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 2px solid #dc3545;
        }

        /* ====================== FORM STYLING ====================== */
        .form-container {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: span 2;
        }

        label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
            font-size: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        label .required {
            color: #dc3545;
        }

        input,
        select {
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s;
            width: 100%;
            background: white;
        }

        input:focus,
        select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        select {
            cursor: pointer;
        }

        /* ====================== TEAM SELECTOR ====================== */
        .team-selector {
            position: relative;
        }

        .team-selector select {
            padding-left: 45px;
        }

        .team-icon {
            position: absolute;
            left: 15px;
            top: 42px;
            font-size: 18px;
            color: #667eea;
        }

        /* ====================== BUTTON ====================== */
        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .btn {
            padding: 15px 32px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            flex: 1;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        /* ====================== INFO BOX ====================== */
        .info-box {
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #2196f3;
            margin-bottom: 25px;
        }

        .info-box p {
            color: #0d47a1;
            font-size: 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin: 0;
        }

        .info-box i {
            margin-top: 2px;
            font-size: 16px;
        }

        /* ====================== NO TEAMS WARNING ====================== */
        .warning-box {
            background: #fff3cd;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #ffc107;
            margin-bottom: 25px;
        }

        .warning-box p {
            color: #856404;
            font-size: 14px;
            margin: 0;
        }

        /* ====================== RESPONSIVE ====================== */
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

            .form-section {
                padding: 25px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-group.full-width {
                grid-column: span 1;
            }

            .btn-container {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- Navigation -->
    <?php include "../includes/organizer/organizer_header.php"; ?>
    <?php include "../includes/organizer/organizer_sidebar.php"; ?>

    <main class="content">
        <!-- Back Button -->
        <a href="manage_matches.php?tournament_id=<?= $tournament_id ?>" class="back-btn">
            <i class="fa fa-arrow-left"></i> Back to Matches
        </a>

        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fa-solid fa-calendar-plus"></i> Create New Match</h1>
            <p>Schedule a new match for your tournament</p>
            <div class="tournament-info">
                <div class="info-badge">
                    <i class="fa-solid fa-trophy"></i>
                    <?= htmlspecialchars($tournament['title']) ?>
                </div>
                <div class="info-badge">
                    <i class="fa-solid fa-gamepad"></i>
                    <?= htmlspecialchars($tournament['game']) ?>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="form-section">
            <h2><i class="fa-solid fa-pen-to-square"></i> Match Details</h2>

            <!-- Info Box -->
            <div class="info-box">
                <p>
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Select two registered teams, set the match date and time, and choose the round/bracket level.</span>
                </p>
            </div>

            <?php if (count($teams) < 2): ?>
                <div class="warning-box">
                    <p>
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> You need at least 2 approved teams to create a match. Please approve more team registrations first.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Messages -->
            <?php if ($success): ?>
                <div class="message success">
                    <i class="fa fa-check-circle"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="message error">
                    <i class="fa fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Match Form -->
            <form method="POST" class="form-container">

                <div class="form-row">
                    <!-- Team 1 -->
                    <div class="form-group team-selector">
                        <label>
                            <span>Team 1</span>
                            <span class="required">*</span>
                        </label>
                        <i class="team-icon fa-solid fa-users"></i>
                        <select name="team1_id" required <?= count($teams) < 2 ? 'disabled' : '' ?>>
                            <option value="">-- Select Team 1 --</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?= $team['team_id'] ?>" <?= (isset($team1_id) && $team1_id == $team['team_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team['team_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Team 2 -->
                    <div class="form-group team-selector">
                        <label>
                            <span>Team 2</span>
                            <span class="required">*</span>
                        </label>
                        <i class="team-icon fa-solid fa-users"></i>
                        <select name="team2_id" required <?= count($teams) < 2 ? 'disabled' : '' ?>>
                            <option value="">-- Select Team 2 --</option>
                            <?php foreach ($teams as $team): ?>
                                <option value="<?= $team['team_id'] ?>" <?= (isset($team2_id) && $team2_id == $team['team_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($team['team_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <!-- Match Date -->
                    <div class="form-group">
                        <label>
                            <i class="fa-solid fa-calendar"></i>
                            <span>Match Date</span>
                            <span class="required">*</span>
                        </label>
                        <input type="date" name="match_date" required value="<?= htmlspecialchars($match_date ?? '') ?>"
                            min="<?= date('Y-m-d') ?>">
                    </div>

                    <!-- Match Time -->
                    <div class="form-group">
                        <label>
                            <i class="fa-solid fa-clock"></i>
                            <span>Match Time</span>
                            <span class="required">*</span>
                        </label>
                        <input type="time" name="match_time" required value="<?= htmlspecialchars($match_time ?? '') ?>">
                    </div>
                </div>

                <div class="form-row">
                    <!-- Round -->
                    <div class="form-group">
                        <label>
                            <i class="fa-solid fa-layer-group"></i>
                            <span>Round / Bracket</span>
                            <span class="required">*</span>
                        </label>
                        <input type="number" name="round" min="1" value="<?= htmlspecialchars($round ?? 1) ?>" required>
                    </div>

                    <!-- Status -->
                    <div class="form-group">
                        <label>
                            <i class="fa-solid fa-flag"></i>
                            <span>Status</span>
                            <span class="required">*</span>
                        </label>
                        <select name="status" required>
                            <option value="scheduled" <?= (isset($status) && $status === 'scheduled') ? 'selected' : '' ?>>Scheduled</option>
                            <option value="ongoing" <?= (isset($status) && $status === 'ongoing') ? 'selected' : '' ?>>Ongoing</option>
                            <option value="completed" <?= (isset($status) && $status === 'completed') ? 'selected' : '' ?>>Completed</option>
                            <option value="cancelled" <?= (isset($status) && $status === 'cancelled') ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="btn-container">
                    <button type="submit" class="btn btn-primary" <?= count($teams) < 2 ? 'disabled' : '' ?>>
                        <i class="fa fa-check"></i> Create Match
                    </button>
                    <a href="manage_matches.php?tournament_id=<?= $tournament_id ?>" class="btn btn-secondary">
                        <i class="fa fa-times"></i> Cancel
                    </a>
                </div>

            </form>
        </div>

    </main>

</body>

</html>