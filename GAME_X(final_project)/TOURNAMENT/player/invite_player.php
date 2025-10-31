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

// Fetch all teams created by the current player
$stmt = $conn->prepare("SELECT team_id, team_name FROM teams WHERE created_by = :account_id");
$stmt->execute(['account_id' => $account_id]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $team_id = $_POST["team_id"];
    $searched_username = trim($_POST["username"]);

    if (!empty($searched_username)) {
        // Step 1: Check if the username exists
        $player_stmt = $conn->prepare("
            SELECT account_id, username, role 
            FROM accounts 
            WHERE username = :username AND role = 'player' AND account_status = 'active'
        ");
        $player_stmt->execute(['username' => $searched_username]);
        $player = $player_stmt->fetch(PDO::FETCH_ASSOC);

        if (!$player) {
            $message = "Player not found or inactive.";
        } elseif ($player['account_id'] == $account_id) {
            $message = "You cannot invite yourself.";
        } else {
            $invited_player = $player['account_id'];

            // Step 2: Check if already in the team
            $check_member = $conn->prepare("
                SELECT * FROM team_members WHERE team_id = :team_id AND account_id = :account_id
            ");
            $check_member->execute([
                'team_id' => $team_id,
                'account_id' => $invited_player
            ]);

            if ($check_member->rowCount() > 0) {
                $message = "This player is already a team member.";
            } else {
                // Step 3: Check if an invitation already exists
                $check_invite = $conn->prepare("
                    SELECT * FROM team_invitations
                    WHERE team_id = :team_id AND invited_player = :player_id AND status = 'pending'
                ");
                $check_invite->execute([
                    'team_id' => $team_id,
                    'player_id' => $invited_player
                ]);

                if ($check_invite->rowCount() > 0) {
                    $message = "An invitation is already pending for this player.";
                } else {
                    // Step 4: Insert new invite
                    $insert_invite = $conn->prepare("
                        INSERT INTO team_invitations (team_id, invited_by, invited_player)
                        VALUES (:team_id, :invited_by, :invited_player)
                    ");
                    $insert_invite->execute([
                        'team_id' => $team_id,
                        'invited_by' => $account_id,
                        'invited_player' => $invited_player
                    ]);

                    $message = "Invitation sent successfully to {$searched_username}!";
                }
            }
        }
    } else {
        $message = "Please enter a valid username.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Invite Player</title>
<link rel="stylesheet" href="../assets/css/common.css?v=2">
<link rel="stylesheet" href="../assets/css/invite_player.css?v=2">
</head>
<body class="invite-page">
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
  <h2>Invite a Player</h2>

    <?php if ($message): ?>
      <p class="message"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST" class="invite-form">
        <label for="team_id">Select Your Team:</label>
        <select name="team_id" id="team_id" required>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <label for="username">Enter Player Username:</label>
        <input type="text" name="username" id="username" placeholder="e.g. AceShooter123" required>

        <button type="submit">Send Invite</button>
    </form>

    <a href="player_dashboard.php" class="back-link">← Back to Dashboard</a>

    <footer class="footer">
      <p>&copy; <?= date("Y"); ?> Game X Community. All rights reserved.</p>
    </footer>

  <script src="../assets/js/darkmode_toggle.js"></script>
  <script src="../assets/js/index.js"></script>
</body>
</html>


  <script src="../assets/js/darkmode_toggle.js"></script>
  <script src="../assets/js/index.js"></script>

