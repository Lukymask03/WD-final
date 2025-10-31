<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";
require_once "../backend/helpers/log_activity.php";

//Allow only organizers
checkAuth("organizer");

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tournament_name = trim($_POST["tournament_name"]);
    $game_title = trim($_POST["game_title"]);
    $max_players = trim($_POST["max_players"]);
    $start_date = trim($_POST["start_date"]);
    $description = trim($_POST["description"]);
    $organizer_id = $_SESSION["user_id"];

    // Validate required fields
    if (empty($tournament_name) || empty($game_title) || empty($max_players) || empty($start_date)) {
        $error = "Please fill in all required fields.";
    } else {
        try {
            $stmt = $conn->prepare("
                INSERT INTO tournaments (tournament_name, game_title, max_players, start_date, description, organizer_id, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$tournament_name, $game_title, $max_players, $start_date, $description, $organizer_id]);

            // Log organizer activity
            logActivity($organizer_id, "Create Tournament", "Created tournament: $tournament_name");

            $success = "Tournament created successfully!";
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
    <title>Create Tournament - GameX Organizer</title>
    <link rel="stylesheet" href="../assets/css/common.css">
    <link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
</head>
<body>
  <!-- ==== NAVIGATION BAR ==== -->
  <header class="navbar">
    <div class="logo-link">
      <img src="../assets/images/game_x_logo.png" alt="GameX Logo" class="logo-img" style="height: 40px; vertical-align: middle;">
      <h2>GameX Organizer</h2>
    </div>

<nav>
    <a href="organizer_dashboard.php">Dashboard</a>
    <a href="create_tournament.php">Create Tournament</a>
    <a href="view_tournaments.php">Manage Tournaments</a> <!-- correct link -->
    <a href="select_tournament.php">Manage Brackets</a> <!-- separate, safe -->
</nav>

          <div class="nav-actions">
           <a href="../auth/logout.php" class="btn">Logout</a>
        </div>
  </header>


    <!-- ==== MAIN CONTENT ==== -->

    <main class="content-area">
        <section class="form-section">
            <h2>Create New Tournament</h2>

            <?php if ($success): ?>
                <div class="success-msg"><?= htmlspecialchars($success) ?></div>
            <?php elseif ($error): ?>
                <div class="error-msg"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" class="tournament-form">
                <label for="tournament_name">Tournament Name *</label>
                <input type="text" name="tournament_name" id="tournament_name" required>

                <label for="game_title">Game Title *</label>
                <input type="text" name="game_title" id="game_title" required>

                <label for="max_players">Maximum Players *</label>
                <input type="number" name="max_players" id="max_players" required min="2">

                <label for="start_date">Start Date *</label>
                <input type="date" name="start_date" id="start_date" required>

                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4" placeholder="Optional tournament details..."></textarea>

                <button type="submit" class="btn">Create Tournament</button>
            </form>
        </section>
    </main>
    <!-- ==== FOOTER ==== -->
    <footer class="footer">
        <p>© <?= date("Y") ?> GameX Tournament System. All rights reserved.</p>
    </footer>

    
</body>
</html>
