<?php
session_start();
require_once "../backend/db.php";

// ===== ACCESS CONTROL =====
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
$message_type = "";

// ===== FETCH PLAYER TEAMS (created or joined) =====
$stmt = $conn->prepare("
    SELECT DISTINCT t.team_id, t.team_name
    FROM teams t
    LEFT JOIN team_members tm ON t.team_id = tm.team_id
    WHERE t.created_by = :acc1 OR tm.account_id = :acc2
");
$stmt->execute([
    'acc1' => $account_id,
    'acc2' => $account_id
]);
$teams = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ===== FETCH OPEN TOURNAMENTS =====
$stmt2 = $conn->prepare("
    SELECT tournament_id, title, reg_deadline 
    FROM tournaments 
    WHERE status = 'open' 
      AND reg_deadline >= NOW()
    ORDER BY reg_deadline ASC
");
$stmt2->execute();
$tournaments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// ===== HANDLE REGISTRATION =====
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $team_id = $_POST["team_id"] ?? null;
    $tournament_id = $_POST["tournament_id"] ?? null;

    if ($team_id && $tournament_id) {
        // Check if already registered
        $check = $conn->prepare("
            SELECT 1 FROM registrations 
            WHERE tournament_id = :tid 
              AND team_id = :team 
              AND account_id = :acc
        ");
        $check->execute([
            'tid' => $tournament_id,
            'team' => $team_id,
            'acc' => $account_id
        ]);

        if ($check->rowCount() > 0) {
            $message = "⚠️ This team is already registered for that tournament.";
            $message_type = "error";
        } else {
            try {
                $insert = $conn->prepare("
                    INSERT INTO registrations (tournament_id, team_id, account_id, registered_at)
                    VALUES (:tid, :team, :acc, :reg_time)
                ");
                $insert->execute([
                    'tid' => $tournament_id,
                    'team' => $team_id,
                    'acc' => $account_id,
                    'reg_time' => date("Y-m-d H:i:s")
                ]);

                $message = "✅ Team successfully registered for the tournament!";
                $message_type = "success";
            } catch (PDOException $e) {
                $message = "❌ Registration failed: " . htmlspecialchars($e->getMessage());
                $message_type = "error";
            }
        }
    } else {
        $message = "⚠️ Please select both a team and a tournament.";
        $message_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Register Team for Tournament</title>
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/register_tournament.css">
</head>
<body>

<!-- ===== NAVBAR ===== -->
<?php require_once "../includes/player/player_navbar.php"; ?>

<!-- ===== MAIN CONTAINER ===== -->
<div class="container">
    <h2>Register Your Team for a Tournament</h2>

    <?php if ($message): ?>
        <div class="alert <?= $message_type ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="register-form">
        <label for="tournament_id">Select Tournament:</label>
        <select name="tournament_id" id="tournament_id" required>
            <option value="">-- Choose a Tournament --</option>
            <?php foreach ($tournaments as $t): ?>
                <option value="<?= $t['tournament_id'] ?>">
                    <?= htmlspecialchars($t['title']) ?> (Deadline: <?= htmlspecialchars($t['reg_deadline']) ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <label for="team_id">Select Your Team:</label>
        <select name="team_id" id="team_id" required>
            <option value="">-- Choose a Team --</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit" class="btn">Register Team</button>
    </form>

    <a href="player_dashboard.php" class="back-btn">← Back to Dashboard</a>
</div>

<!-- ===== FOOTER ===== -->
<footer class="footer">
  <p>&copy; <?= date("Y"); ?> Game X Community. All rights reserved.</p>
</footer>

<!-- ===== SCRIPTS ===== -->
  <script src="../assets/js/darkmode_toggle.js"></script>
  <script src="../assets/js/index.js"></script>
</body>
</html>
