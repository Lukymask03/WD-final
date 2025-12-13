<?php
session_start();
require_once __DIR__ . '/../backend/db.php';

// ================== AUTH CHECK ==================
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'player') {
    die("Unauthorized access.");
}

$account_id = $_SESSION['account_id'];

// ================== GET TEAM ID ==================
if (!isset($_GET['team_id'])) {
    die("Team ID missing.");
}

$team_id = (int) $_GET['team_id'];

// ================== VERIFY TEAM OWNER ==================
// ✅ FIXED COLUMN NAME HERE
$stmtTeam = $conn->prepare("
    SELECT *
    FROM teams
    WHERE team_id = ? AND created_by = ?
");
$stmtTeam->execute([$team_id, $account_id]);
$team = $stmtTeam->fetch(PDO::FETCH_ASSOC);

if (!$team) {
    die("You do not have permission to manage this team.");
}

// ================== GET TEAM MEMBERS ==================
$stmtMembers = $conn->prepare("
    SELECT u.username, tm.joined_at
    FROM team_members tm
    JOIN accounts u ON tm.account_id = u.account_id
    WHERE tm.team_id = ?
");
$stmtMembers->execute([$team_id]);
$members = $stmtMembers->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Team</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #0f172a;
            color: #fff;
            padding: 20px;
        }
        .card {
            background: #1e293b;
            padding: 20px;
            border-radius: 10px;
            max-width: 700px;
            margin: auto;
        }
        h2 {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 10px;
            border-bottom: 1px solid #334155;
        }
        th {
            text-align: left;
        }
        .btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 16px;
            background: #f97316;
            color: #fff;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Manage Team: <?= htmlspecialchars($team['team_name']) ?></h2>

    <p><strong>Created:</strong> <?= htmlspecialchars($team['created_at']) ?></p>

    <h3>Team Members</h3>

    <?php if (count($members) === 0): ?>
        <p>No members yet.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Username</th>
                <th>Joined At</th>
            </tr>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td><?= htmlspecialchars($member['username']) ?></td>
                    <td><?= htmlspecialchars($member['joined_at']) ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <a href="view_team.php?team_id=<?= $team_id ?>" class="btn">⬅ Back to Team</a>
</div>

</body>
</html>
