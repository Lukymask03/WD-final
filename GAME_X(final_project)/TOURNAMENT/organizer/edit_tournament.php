<?php
session_start();
require_once "../backend/db.php";
require_once "../auth/auth_guard.php";

// ✅ Organizer access only
checkAuth("organizer");

$organizer_id = $_SESSION['user_id'];

// ✅ Ensure 'id' is passed
if (!isset($_GET['id'])) {
    header("Location: view_tournaments.php");
    exit;
}

$tournament_id = $_GET['id'];

//  Fetch tournament belonging to this organizer
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE tournament_id = ? AND organizer_id = ?");
$stmt->execute([$tournament_id, $organizer_id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

//  If tournament not found
if (!$tournament) {
    die("Tournament not found or access denied.");
}

//  Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['tournament_name']);
    $game = trim($_POST['game_name']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tournaments 
        SET tournament_name=?, game_name=?, start_date=?, end_date=?, status=? 
        WHERE tournament_id=? AND organizer_id=?");
    $stmt->execute([$name, $game, $start, $end, $status, $tournament_id, $organizer_id]);

    header("Location: view_tournaments.php?updated=1");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Edit Tournament</title>
  <link rel="stylesheet" href="../assets/css/common.css">
  <link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
  <script src="../assets/js/darkmode.js" defer></script>

  <style>
      body {
          font-family: 'Poppins', sans-serif;
      }
      form {
          max-width: 600px;
          margin: 60px auto;
          background: var(--bg-secondary);
          padding: 25px;
          border-radius: 15px;
          box-shadow: 0 0 15px rgba(0,0,0,0.2);
      }
      h2 {
          text-align: center;
          color: var(--text-main);
      }
      label {
          display: block;
          margin-top: 15px;
          font-weight: 600;
      }
      input, select {
          width: 100%;
          padding: 10px;
          margin-top: 5px;
          border: 1px solid var(--border);
          border-radius: 8px;
          background: var(--bg-main);
          color: var(--text-main);
      }
      button {
          display: block;
          width: 100%;
          margin-top: 25px;
          padding: 10px;
          background: #3498db;
          border: none;
          border-radius: 8px;
          color: white;
          font-weight: 600;
          cursor: pointer;
          transition: 0.3s;
      }
      button:hover {
          background: #2980b9;
      }
  </style>
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

  <!--  Main Content -->
  <form method="POST">
      <h2>Edit Tournament</h2>

      <label for="tournament_name">Tournament Name</label>
      <input type="text" name="tournament_name" value="<?= htmlspecialchars($tournament['tournament_name']) ?>" required>

      <label for="game_name">Game Name</label>
      <input type="text" name="game_name" value="<?= htmlspecialchars($tournament['game_name']) ?>" required>

      <label for="start_date">Start Date</label>
      <input type="date" name="start_date" value="<?= htmlspecialchars($tournament['start_date']) ?>" required>

      <label for="end_date">End Date</label>
      <input type="date" name="end_date" value="<?= htmlspecialchars($tournament['end_date']) ?>" required>

      <label for="status">Status</label>
      <select name="status">
          <option value="Upcoming" <?= $tournament['status'] === 'Upcoming' ? 'selected' : '' ?>>Upcoming</option>
          <option value="Ongoing" <?= $tournament['status'] === 'Ongoing' ? 'selected' : '' ?>>Ongoing</option>
          <option value="Completed" <?= $tournament['status'] === 'Completed' ? 'selected' : '' ?>>Completed</option>
      </select>

      <button type="submit">Update Tournament</button>
  </form>
</body>
</html>
