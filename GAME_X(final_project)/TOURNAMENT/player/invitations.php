<?php
// ============================================
// invitations.php
// Player-side view of received team invitations
// ============================================

// ✅ Connect to backend
require_once(__DIR__ . '/../backend/config.php');

// ✅ Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ✅ Check login and role
if (!isset($_SESSION['account_id']) || $_SESSION['role'] !== 'player') {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION['account_id'];

// ================================
// Handle Accept / Reject Actions
// ================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invitation_id = $_POST['invitation_id'];
    $action = $_POST['action'];

    if ($action === 'accept') {
        // ✅ 1. Update invitation status
        $update = $conn->prepare("UPDATE team_invitations SET status = 'accepted' WHERE invitation_id = :id");
        $update->bindParam(":id", $invitation_id);
        $update->execute();

        // ✅ 2. Add player to team_members (if not already)
        $teamQuery = $conn->prepare("SELECT team_id FROM team_invitations WHERE invitation_id = :id");
        $teamQuery->bindParam(":id", $invitation_id);
        $teamQuery->execute();
        $team_id = $teamQuery->fetchColumn();

        if ($team_id) {
            $check = $conn->prepare("SELECT * FROM team_members WHERE team_id = :team_id AND account_id = :account_id");
            $check->bindParam(":team_id", $team_id);
            $check->bindParam(":account_id", $account_id);
            $check->execute();

            if ($check->rowCount() === 0) {
                $insert = $conn->prepare("INSERT INTO team_members (team_id, account_id) VALUES (:team_id, :account_id)");
                $insert->bindParam(":team_id", $team_id);
                $insert->bindParam(":account_id", $account_id);
                $insert->execute();
            }
        }

        $message = "✅ Invitation accepted successfully!";
    } elseif ($action === 'reject') {
        // ✅ Reject invitation
        $update = $conn->prepare("UPDATE team_invitations SET status = 'rejected' WHERE invitation_id = :id");
        $update->bindParam(":id", $invitation_id);
        $update->execute();
        $message = "❌ Invitation rejected.";
    }
}

// ================================
// Fetch All Invitations
// ================================
$query = "
    SELECT 
        ti.invitation_id, 
        ti.status, 
        ti.created_at, 
        t.team_name, 
        a.username AS inviter_name
    FROM team_invitations ti
    JOIN teams t ON ti.team_id = t.team_id
    JOIN accounts a ON ti.invited_by = a.account_id
    WHERE ti.invited_player = :account_id
    ORDER BY ti.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bindParam(":account_id", $account_id);
$stmt->execute();
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>My Invitations</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/invitations.css">
</head>
<body>

    <!-- ========== NAVBAR ========== -->
<header class="navbar">
  <div class="logo">
    <a href="index.php" class="logo-link">
      <img src="../assets/images/game_x_logo.png" alt="Game X Community" class="logo-img" />
      <h1><span class="highlight-orange">GAME</span><span class="highlight-red"> X</span></h1>
    </a>
  </div>

  <nav>
      <a href="player_dashboard.php">Dashboard</a>
      <a href="register_tournament.php">Register Tournament</a>
      <a href="create_team.php">Create Team</a>
      <a href="invite_player.php">Invite Player</a>
      <a href="invitations.php">Invitations</a>
      <a href="respond_invite.php">Respond to Invitations</a>
      <a href="my_registrations.php">My Tournaments</a>
      <a href="player_contact.php">Support</a>
    </nav>

  <div class="nav-actions">
    <button id="darkModeToggle" class="darkmode-btn">Dark Mode</button>
    <a href="../auth/logout.php" class="cta-btn">Logout</a>
  </div>
</header>

    <div class="container">
        <h2>My Invitations</h2>
        <?php if (!empty($message)) echo "<p class='msg'>$message</p>"; ?>

        <?php if (count($invitations) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Team</th>
                    <th>Invited By</th>
                    <th>Status</th>
                    <th>Invited On</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invitations as $invite): ?>
                <tr>
                    <td><?= htmlspecialchars($invite['team_name']) ?></td>
                    <td><?= htmlspecialchars($invite['inviter_name']) ?></td>
                    <td class="<?= htmlspecialchars($invite['status']) ?>"><?= ucfirst($invite['status']) ?></td>
                    <td><?= htmlspecialchars($invite['created_at']) ?></td>
                    <td class="actions">
                        <?php if ($invite['status'] === 'pending'): ?>
                            <form method="POST">
                                <input type="hidden" name="invitation_id" value="<?= $invite['invitation_id'] ?>">
                                <button type="submit" name="action" value="accept" class="btn accept">Accept</button>
                                <button type="submit" name="action" value="reject" class="btn reject">Reject</button>
                            </form>
                        <?php else: ?>
                            <em>No actions</em>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p style="text-align:center;">No invitations found.</p>
        <?php endif; ?>
    </div>    
  <script src="../assets/js/darkmode_toggle.js"></script>
  <script src="../assets/js/index.js"></script>
</body>
</html>
