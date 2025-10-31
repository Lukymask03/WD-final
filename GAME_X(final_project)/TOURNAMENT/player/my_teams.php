<?php
session_start();
require_once "../backend/db.php";

// Security check
if (!isset($_SESSION["account_id"]) || $_SESSION["role"] !== "player") {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION["account_id"];

// Fetch teams where player is owner or member
$teams_query = "
    SELECT DISTINCT
        t.team_id,
        t.team_name,
        t.game_name,
        t.team_logo,
        t.introduction,
        t.max_members,
        t.created_at,
        a.username AS creator_name,
        (SELECT COUNT(*) FROM team_members WHERE team_id = t.team_id) AS member_count,
        CASE WHEN t.created_by = :account_id THEN 1 ELSE 0 END AS is_leader,
        tm.role AS member_role
    FROM teams t
    LEFT JOIN accounts a ON t.created_by = a.account_id
    INNER JOIN team_members tm ON t.team_id = tm.team_id
    WHERE tm.account_id = :account_id2
    ORDER BY is_leader DESC, t.created_at DESC
";

$teams_stmt = $conn->prepare($teams_query);
$teams_stmt->execute(['account_id' => $account_id, 'account_id2' => $account_id]);
$my_teams = $teams_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Teams - GameX</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/my-teams.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" />
</head>

<body>

    <?php require_once "../includes/player/player_navbar.php"; ?>

    <main class="teams-container">
        <div class="my-teams-header">
            <div class="header-left">
                <h1><i class="fas fa-users"></i> My Teams</h1>
                <p class="header-subtitle">Teams you own or are a member of</p>
            </div>
            <a href="teams.php" class="back-to-teams-btn">
                <i class="fas fa-arrow-left"></i>
                <span>Back to All Teams</span>
            </a>
        </div>

        <div class="my-teams-grid">
            <?php if (count($my_teams) > 0): ?>
                <?php foreach ($my_teams as $team): ?>
                    <div class="team-card member-card">
                        <?php if ($team['team_logo']): ?>
                            <div class="team-logo-header">
                                <img src="../<?= htmlspecialchars($team['team_logo']) ?>" alt="<?= htmlspecialchars($team['team_name']) ?>">
                            </div>
                        <?php else: ?>
                            <div class="team-logo-placeholder">
                                <i class="fas fa-users"></i>
                            </div>
                        <?php endif; ?>

                        <div class="team-card-header">
                            <h3><?= htmlspecialchars($team['team_name']) ?></h3>
                        </div>

                        <div class="team-info">
                            <p>
                                <i class="fas fa-gamepad"></i>
                                <?= htmlspecialchars($team['game_name']) ?>
                            </p>
                            <p>
                                <i class="fas fa-users"></i>
                                <strong><?= $team['member_count'] ?></strong> / <?= $team['max_members'] ?>
                            </p>
                            <p>
                                <i class="far fa-calendar"></i>
                                <?= date('M d, Y', strtotime($team['created_at'])) ?>
                            </p>
                        </div>

                        <div class="team-actions">
                            <a href="view_team.php?team_id=<?= $team['team_id'] ?>" class="btn-view">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-teams">
                    <div class="no-teams-icon">
                        <i class="fas fa-users-slash"></i>
                    </div>
                    <h3>No Teams Yet</h3>
                    <p>You haven't created or joined any teams yet.</p>
                    <a href="teams.php" class="btn-create-first">
                        <i class="fas fa-search"></i>
                        Browse Available Teams
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="footer">
        <p>&copy; <?= date("Y") ?> Game X Community. All rights reserved.</p>
    </footer>

    <script src="../assets/js/darkmode_toggle.js"></script>
    <script src="../assets/js/index.js"></script>

</body>

</html>