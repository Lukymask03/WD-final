<?php
session_start();
require_once "../backend/db.php";

// ==========================
// SECURITY & ACCESS CONTROL
// ==========================
if (!isset($_SESSION["account_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$account_id = $_SESSION["account_id"];
$role = $_SESSION["role"] ?? "";

if ($role !== "player") {
    echo "<p style='color:red; text-align:center;'>Access Denied: Only players can create teams.</p>";
    exit;
}

// ==========================
// INITIALIZE VARIABLES
// ==========================
$message = "";
$message_type = "";

// ==========================
// FETCH AVAILABLE PLAYERS
// ==========================
try {
    $stmt = $conn->query("
        SELECT a.account_id, 
               COALESCE(pp.gamer_tag, a.username) AS gamer_tag
        FROM accounts a
        LEFT JOIN player_profiles pp ON a.account_id = pp.account_id
        WHERE a.role = 'player' 
          AND a.account_status = 'active'
    ");
    $players = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Database error: " . htmlspecialchars($e->getMessage());
    $message_type = "error";
}

// ==========================
// HANDLE TEAM CREATION
// ==========================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $team_name = trim($_POST["team_name"]);
    $game_name = trim($_POST["game_name"]); // must exist in your form
    $selected_members = $_POST["members"] ?? [];

    if (empty($team_name) || empty($game_name)) {
        $message = "Please enter both team name and game name.";
        $message_type = "error";
    } else {
        try {
            // Check if player already has a team for this game
            $checkTeam = $conn->prepare("
                SELECT COUNT(*) FROM teams 
                WHERE created_by = :account_id AND game_name = :game_name
            ");
            $checkTeam->execute([
                'account_id' => $account_id,
                'game_name' => $game_name
            ]);
            $existingTeamCount = $checkTeam->fetchColumn();

            if ($existingTeamCount > 0) {
                $message = "You already created a team for '$game_name'. You can only have one team per game.";
                $message_type = "error";
            } else {
                // Check for duplicate team name globally
                $check = $conn->prepare("SELECT team_id FROM teams WHERE team_name = :team_name");
                $check->execute(['team_name' => $team_name]);

                if ($check->rowCount() > 0) {
                    $message = "Team name already exists. Please choose another.";
                    $message_type = "error";
                } else {
                    $conn->beginTransaction();

                    // Insert new team
                    $insertTeam = $conn->prepare("
                        INSERT INTO teams (team_name, game_name, created_by, created_at)
                        VALUES (:team_name, :game_name, :created_by, NOW())
                    ");
                    $insertTeam->execute([
                        'team_name' => $team_name,
                        'game_name' => $game_name,
                        'created_by' => $account_id
                    ]);
                    $team_id = $conn->lastInsertId();

                    // Add creator as member
                    $addMember = $conn->prepare("
                        INSERT INTO team_members (team_id, account_id)
                        VALUES (:team_id, :account_id)
                    ");
                    $addMember->execute(['team_id' => $team_id, 'account_id' => $account_id]);

                    // Add selected members (if any)
                    foreach ($selected_members as $member_id) {
                        if ($member_id != $account_id) {
                            $addMember->execute(['team_id' => $team_id, 'account_id' => $member_id]);
                        }
                    }

                    // Log the action
                    $log = $conn->prepare("
                        INSERT INTO audit_logs (account_id, action, details, created_at)
                        VALUES (:account_id, 'Create Team', :details, NOW())
                    ");
                    $log->execute([
                        'account_id' => $account_id,
                        'details' => "Team '$team_name' for game '$game_name' created (ID: $team_id)"
                    ]);

                    $conn->commit();
                    $message = "Team '$team_name' created successfully for '$game_name'!";
                    $message_type = "success";
                }
            }
        } catch (PDOException $e) {
            $conn->rollBack();
            $message = "Error creating team: " . htmlspecialchars($e->getMessage());
            $message_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Create Team - GameX</title>
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/create_team.css">
<style>
/* ===== Additional Styling ===== */
.container {
    max-width: 500px;
    margin: 70px auto;
    background-color: var(--bg-secondary);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: 0 0 15px rgba(255, 102, 0, 0.3);
    text-align: center;
}

h2 {
    color: var(--accent);
    margin-bottom: 20px;
}

form {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

input[type="text"],
select {
    padding: 10px;
    border-radius: 8px;
    border: 1px solid var(--accent);
    font-size: 1rem;
    background-color: var(--bg-primary);
    color: var(--text-primary);
}

button[type="submit"] {
    background-color: var(--accent);
    color: #fff;
    border: none;
    padding: 10px;
    font-size: 1rem;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.3s;
}

button[type="submit"]:hover {
    background-color: #ff6600;
}

.message, .success {
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 10px;
}

.message {
    background-color: rgba(255, 0, 0, 0.2);
    color: #ff4d4d;
}

.success {
    background-color: rgba(0, 255, 0, 0.2);
    color: #33cc33;
}

.back-btn {
    display: inline-block;
    margin-top: 15px;
    color: var(--accent);
    text-decoration: none;
    font-size: 0.9rem;
}

.back-btn:hover {
    text-decoration: underline;
}
</style>
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

<!-- ===== MAIN CONTENT ===== -->
<div class="container">
    <h2>Create a New Team</h2>

    <?php if ($message): ?>
        <p class="<?= $message_type ?>"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <form method="POST">
        <label>Team Name</label>
        <input type="text" name="team_name" placeholder="Enter team name" required>

        <label>Select Team Members <small>(Hold Ctrl or Cmd to select multiple)</small></label>
        <select name="members[]" multiple size="6">
            <?php foreach ($players as $p): ?>
                <?php if ($p['account_id'] != $account_id): ?>
                    <option value="<?= $p['account_id'] ?>">
                        <?= htmlspecialchars($p['gamer_tag']) ?>
                    </option>
                <?php endif; ?>
            <?php endforeach; ?>
        </select>

        <label for="game_name">Select Game</label>
        <select name="game_name" id="game_name" required>
        <option value="">-- Select a Game --</option>
        <option value="Valorant">Valorant</option>
        <option value="Call of Duty">Call of Duty</option>
        <option value="Dota 2">Dota 2</option>
        <option value="Mobile Legends">Mobile Legends</option>
       <option value="League of Legends">League of Legends</option>
</select>


        <button type="submit">Create Team</button>
    </form>

    <a href="player_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<!-- ===== FOOTER ===== -->
<footer class="footer">
  <p>&copy; <?= date("Y") ?> Game X Community. All rights reserved.</p>
</footer>
<script src="../assets/js/darkmode_toggle.js"></script>
<script src="../assets/js/index.js"></script>
</body>
</html>