<?php 

session_start();
require_once __DIR__ . '/../backend/db.php';
require_once __DIR__ . '/../backend/helpers/auth_guard.php';

checkAuth('organizer');

// Get tournament ID
$tournament_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$tournament_id) {
    header("Location: view_tournaments.php");
    exit;
}

// ===== Get organizer_id from organizer_profiles table =====
$stmtProfile = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
$stmtProfile->execute([$_SESSION['account_id']]);
$profile = $stmtProfile->fetch(PDO::FETCH_ASSOC);

if (!$profile) {
    die("Organizer profile not found. Please complete your profile first.");
}

$organizer_id = $profile['organizer_id'];

try {
    // Fetch tournament details - only if it belongs to this organizer
    $stmt = $conn->prepare("
        SELECT t.*, a.username as organizer_username
        FROM tournaments t
        LEFT JOIN organizer_profiles op ON t.organizer_id = op.organizer_id
        LEFT JOIN accounts a ON op.account_id = a.account_id
        WHERE t.tournament_id = ? AND t.organizer_id = ?
    ");
    $stmt->execute([$tournament_id, $organizer_id]);
    $tournament = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tournament) {
        die("Tournament not found or you don't have permission to view it.");
    }

    // Count registered teams
    $teamStmt = $conn->prepare("SELECT COUNT(*) FROM registrations WHERE tournament_id = ? AND status = 'approved'");
    $teamStmt->execute([$tournament_id]);
    $registeredTeams = $teamStmt->fetchColumn();

    // Fetch registered teams
    $teamsStmt = $conn->prepare("
        SELECT r.*, t.team_name, a.username as leader_name
        FROM registrations r
        JOIN teams t ON r.team_id = t.team_id
        LEFT JOIN team_members tm ON t.team_id = tm.team_id AND tm.role = 'leader'
        LEFT JOIN accounts a ON tm.account_id = a.account_id
        WHERE r.tournament_id = ?
        ORDER BY r.registered_at DESC
    ");
    $teamsStmt->execute([$tournament_id]);
    $teams = $teamsStmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . htmlspecialchars($e->getMessage()));
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($tournament['title']) ?> | Tournament Details</title>

<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
<link rel="stylesheet" href="../assets/css/single_tournament.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
<!-- Navigation -->
<?php include "../includes/organizer/organizer_header.php"; ?>
<?php include "../includes/organizer/organizer_sidebar.php"; ?>

<main class="content">
    <!-- Back Button -->
    <a href="view_tournaments.php" class="back-btn">
        <i class="fa fa-arrow-left"></i> Back to Tournaments
    </a>

    <!-- Tournament Header -->
    <div class="tournament-header">
        <div class="tournament-title-section">
            <h1><?= htmlspecialchars($tournament['title']) ?></h1>
            <span class="status-badge-large status-<?= $tournament['status'] ?>">
                <?= strtoupper($tournament['status']) ?>
            </span>
            <div class="tournament-meta">
                <div class="meta-item">
                    <i class="fa-solid fa-gamepad"></i>
                    <span><?= htmlspecialchars($tournament['game']) ?></span>
                </div>
                <div class="meta-item">
                    <i class="fa-solid fa-user"></i>
                    <span>Organized by <?= htmlspecialchars($tournament['organizer_username']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Grid -->
    <div class="content-grid">
        <!-- Tournament Details -->
        <div class="card">
            <h2><i class="fa-solid fa-circle-info"></i> Tournament Details</h2>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-align-left"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Description</div>
                        <div class="info-value">
                            <?= $tournament['description'] ? htmlspecialchars($tournament['description']) : 'No description provided' ?>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-calendar-days"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Tournament Dates</div>
                        <div class="info-value">
                            <?= date('M d, Y', strtotime($tournament['start_date'])) ?>
                            <?php if ($tournament['end_date']): ?>
                                - <?= date('M d, Y', strtotime($tournament['end_date'])) ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Registration Period</div>
                        <div class="info-value">
                            <?= date('M d, Y', strtotime($tournament['reg_start_date'])) ?>
                            - <?= date('M d, Y', strtotime($tournament['reg_end_date'])) ?>
                        </div>
                    </div>
                </div>

                <div class="info-item">
                    <div class="info-icon">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <div class="info-content">
                        <div class="info-label">Team Capacity</div>
                        <div class="info-value">
                            <?= $registeredTeams ?> / <?= $tournament['max_teams'] ?> Teams Registered
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="edit_tournament.php?id=<?= $tournament_id ?>" class="btn btn-edit">
                    <i class="fa fa-edit"></i> Edit Tournament
                </a>
                <a href="../backend/delete_tournament.php?id=<?= $tournament_id ?>" 
                   onclick="return confirm('Are you sure you want to delete this tournament? This action cannot be undone.');"
                   class="btn btn-delete">
                    <i class="fa fa-trash"></i> Delete Tournament
                </a>
            </div>
        </div>

        <!-- Statistics Card -->
        <div class="stats-card">
            <h3>Registration Progress</h3>
            <div class="stats-number">
                <?= $registeredTeams ?>
            </div>
            <div class="stats-label">Teams Registered</div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?= ($tournament['max_teams'] > 0) ? ($registeredTeams / $tournament['max_teams'] * 100) : 0 ?>%"></div>
            </div>
            <div style="margin-top: 10px; font-size: 13px;">
                <?= max(0, $tournament['max_teams'] - $registeredTeams) ?> slots remaining
            </div>
        </div>

        <!-- Registered Teams -->
        <div class="card teams-section">
            <h2><i class="fa-solid fa-users"></i> Registered Teams (<?= count($teams) ?>)</h2>
            
            <?php if ($teams): ?>
                <table class="teams-table">
                    <thead>
                        <tr>
                            <th>Team</th>
                            <th>Leader</th>
                            <th>Registration Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($teams as $team): ?>
                            <tr>
                                <td>
                                    <div class="team-name">
                                        <div class="team-badge">
                                            <?= strtoupper(substr($team['team_name'], 0, 1)) ?>
                                        </div>
                                        <?= htmlspecialchars($team['team_name']) ?>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($team['leader_name'] ?? 'N/A') ?></td>
                                <td><?= date('M d, Y', strtotime($team['registered_at'])) ?></td>
                                <td>
                                    <span class="registration-status status-<?= $team['status'] ?>">
                                        <?= strtoupper($team['status']) ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-users-slash"></i>
                    <h3>No Teams Yet</h3>
                    <p>No teams have registered for this tournament yet.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

</main>

</body>
</html>