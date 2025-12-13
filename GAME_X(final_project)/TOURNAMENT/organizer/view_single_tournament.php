<?php 
// ========================================
// VIEW SINGLE TOURNAMENT - ORGANIZER
// ========================================

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
        padding: 100px 50px 50px;
        min-height: 100vh;
        animation: fadeIn 0.4s ease-in;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
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
        color: #ff6600;
        transform: translateX(-5px);
    }

    /* ====================== TOURNAMENT HEADER ====================== */
    .tournament-header {
        background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
        padding: 40px;
        border-radius: 20px;
        margin-bottom: 30px;
        box-shadow: 0 10px 30px rgba(255, 102, 0, 0.3);
        color: white;
        position: relative;
        overflow: hidden;
    }

    .tournament-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .tournament-title-section {
        position: relative;
        z-index: 1;
    }

    .tournament-title-section h1 {
        font-size: 36px;
        margin-bottom: 15px;
        font-weight: 700;
    }

    .tournament-meta {
        display: flex;
        gap: 30px;
        flex-wrap: wrap;
        margin-top: 20px;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(255, 255, 255, 0.2);
        padding: 10px 20px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
    }

    .meta-item i {
        font-size: 20px;
    }

    /* ====================== STATUS BADGE ====================== */
    .status-badge-large {
        display: inline-block;
        padding: 10px 25px;
        border-radius: 25px;
        font-size: 14px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .status-open {
        background: #28a745;
        color: white;
    }

    .status-completed {
        background: #17a2b8;
        color: white;
    }

    .status-cancelled {
        background: #dc3545;
        color: white;
    }

    /* ====================== CONTENT GRID ====================== */
    .content-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
        margin-bottom: 30px;
    }

    .card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
    }

    .card h2 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-size: 22px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 3px solid #ff6600;
    }

    /* ====================== INFO ITEMS ====================== */
    .info-grid {
        display: grid;
        gap: 20px;
    }

    .info-item {
        display: flex;
        align-items: flex-start;
        gap: 15px;
    }

    .info-icon {
        width: 45px;
        height: 45px;
        background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }

    .info-content {
        flex: 1;
    }

    .info-label {
        font-size: 13px;
        color: #999;
        text-transform: uppercase;
        font-weight: 600;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .info-value {
        font-size: 16px;
        color: #2c3e50;
        font-weight: 600;
    }

    /* ====================== STATS CARDS ====================== */
    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 25px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }

    .stats-card h3 {
        font-size: 14px;
        opacity: 0.9;
        margin-bottom: 10px;
    }

    .stats-card .stats-number {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 5px;
    }

    .stats-card .stats-label {
        font-size: 12px;
        opacity: 0.8;
    }

    .progress-bar {
        background: rgba(255, 255, 255, 0.3);
        height: 8px;
        border-radius: 10px;
        overflow: hidden;
        margin-top: 15px;
    }

    .progress-fill {
        background: white;
        height: 100%;
        border-radius: 10px;
        transition: width 0.5s ease;
    }

    /* ====================== TEAMS TABLE ====================== */
    .teams-section {
        grid-column: 1 / -1;
    }

    .teams-table {
        width: 100%;
        border-collapse: collapse;
    }

    .teams-table thead {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
    }

    .teams-table th {
        padding: 15px;
        text-align: left;
        font-weight: 600;
        font-size: 14px;
    }

    .teams-table td {
        padding: 15px;
        border-bottom: 1px solid #f0f0f0;
    }

    .teams-table tbody tr:hover {
        background: #f9f9f9;
    }

    .team-name {
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .team-badge {
        width: 40px;
        height: 40px;
        background: linear-gradient(135deg, #ff6600 0%, #ff8533 100%);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
    }

    .registration-status {
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .status-approved {
        background: #d4edda;
        color: #155724;
    }

    .status-pending {
        background: #fff3cd;
        color: #856404;
    }

    .status-rejected {
        background: #f8d7da;
        color: #721c24;
    }

    /* ====================== ACTION BUTTONS ====================== */
    .action-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        flex-wrap: wrap;
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
    }

    .btn-edit {
        background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
    }

    .btn-delete {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .btn-delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
    }

    /* ====================== EMPTY STATE ====================== */
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #999;
    }

    .empty-state i {
        font-size: 60px;
        color: #ddd;
        margin-bottom: 15px;
    }

    .empty-state h3 {
        font-size: 20px;
        color: #666;
        margin-bottom: 8px;
    }

    /* ====================== RESPONSIVE ====================== */
    @media (max-width: 1200px) {
        .content-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        main.content {
            margin-left: 0;
            padding: 90px 20px 40px;
        }

        .tournament-header {
            padding: 25px;
        }

        .tournament-title-section h1 {
            font-size: 24px;
        }

        .tournament-meta {
            flex-direction: column;
            gap: 10px;
        }

        .card {
            padding: 20px;
        }

        .teams-table {
            font-size: 14px;
        }

        .teams-table th,
        .teams-table td {
            padding: 10px;
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