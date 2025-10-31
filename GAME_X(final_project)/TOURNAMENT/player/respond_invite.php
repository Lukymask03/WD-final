<?php
session_start();
require_once "../backend/db.php";

if (!isset($_SESSION["account_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION["account_id"];
$role = $_SESSION["role"] ?? "";

if ($role !== "player") {
    echo "<p style='color:red; text-align:center;'>Access Denied.</p>";
    exit;
}

$message = "";

// Handle Accept or Reject actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $invitation_id = $_POST["invite_id"]; // HTML form still sends 'invite_id'
    $action = $_POST["action"];

    if ($action === "accept") {
        // Fetch invitation details
        $stmt = $conn->prepare("
            SELECT * FROM team_invitations 
            WHERE invitation_id = :invitation_id 
              AND invited_player = :player_id
        ");
        $stmt->execute(['invitation_id' => $invitation_id, 'player_id' => $account_id]);
        $invite = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($invite) {
            // Add player to team_members
            $insert = $conn->prepare("
                INSERT INTO team_members (team_id, account_id)
                VALUES (:team_id, :account_id)
            ");
            $insert->execute([
                'team_id' => $invite['team_id'],
                'account_id' => $account_id
            ]);

            // Update invitation status
            $update = $conn->prepare("
                UPDATE team_invitations 
                SET status = 'accepted' 
                WHERE invitation_id = :invitation_id
            ");
            $update->execute(['invitation_id' => $invitation_id]);

            $message = "✅ You have successfully joined the team!";
        }
    } elseif ($action === "reject") {
        $update = $conn->prepare("
            UPDATE team_invitations 
            SET status = 'rejected' 
            WHERE invitation_id = :invitation_id
        ");
        $update->execute(['invitation_id' => $invitation_id]);

        $message = "❌ You have rejected the invitation.";
    }
}

// Fetch all pending invitations for this player
$stmt = $conn->prepare("
    SELECT 
        ti.invitation_id, 
        ti.team_id, 
        t.team_name, 
        pp.gamer_tag AS invited_by_name, 
        ti.status, 
        ti.created_at
    FROM team_invitations ti
    JOIN teams t ON ti.team_id = t.team_id
    JOIN accounts a ON ti.invited_by = a.account_id
    JOIN player_profiles pp ON a.account_id = pp.account_id
    WHERE ti.invited_player = :player_id AND ti.status = 'pending'
");
$stmt->execute(['player_id' => $account_id]);
$invitations = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Respond to Invitations</title>
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/respond_invite.css">
</head>
<body>

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
    <h2>Pending Team Invitations</h2>

    <?php if (!empty($message)): ?>
        <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if (count($invitations) > 0): ?>
        <table class="invite-table">
            <thead>
                <tr>
                    <th>Team Name</th>
                    <th>Invited By</th>
                    <th>Date Sent</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($invitations as $invite): ?>
                    <tr>
                        <td><?= htmlspecialchars($invite['team_name']) ?></td>
                        <td><?= htmlspecialchars($invite['invited_by_name']) ?></td>
                        <td><?= htmlspecialchars($invite['created_at']) ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="invite_id" value="<?= $invite['invitation_id'] ?>">
                                <button type="submit" name="action" value="accept" class="accept-btn">Accept</button>
                                <button type="submit" name="action" value="reject" class="reject-btn">Reject</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No pending invitations at the moment.</p>
    <?php endif; ?>

    <a href="player_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<footer class="footer">
  <p>&copy; <?= date("Y"); ?> Game X Community. All rights reserved.</p>
</footer>
<script src="../assets/js/darkmode_toggle.js"></script>
<script src="../assets/js/index.js"></script>
</body>
</html>
