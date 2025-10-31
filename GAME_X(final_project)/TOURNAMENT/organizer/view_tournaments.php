<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";
require_once "../backend/helpers/log_activity.php";

checkAuth('organizer');

try {
    $stmt = $conn->prepare("SELECT tournament_id, title, start_date, end_date, status, max_teams, reg_deadline 
                        FROM tournaments 
                        WHERE organizer_id = ? 
                        ORDER BY start_date DESC");
    $stmt->execute([$_SESSION['account_id']]);
    $tournaments = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database fetch error: " . htmlspecialchars($e->getMessage()));
}

logActivity($_SESSION['account_id'], "View Tournaments", "Organizer viewed tournament list");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manage Tournaments | GameX</title>
  <link rel="stylesheet" href="../assets/css/common.css" />
  <style>
  :root {
    --bg-main: #FFFFFF;
    --bg-secondary: #F7F8FA;
    --text-main: #1A1A1A;
    --text-muted: #555555;
    --accent: #FF5E00;
    --accent-hover: #FF7B33;
    --border: #E0E0E0;
  }

  body {
    font-family: "Segoe UI", Arial, sans-serif;
    background: var(--bg-secondary);
    margin: 0;
    padding: 0;
    color: var(--text-main);
  }

  header.navbar {
    background: var(--bg-main);
    padding: 15px 30px;
    border-bottom: 2px solid var(--border);
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  header.navbar h2 {
    color: var(--accent);
    font-weight: 700;
  }

  nav a {
    margin: 0 12px;
    text-decoration: none;
    color: var(--text-main);
    font-weight: 600;
    transition: color 0.3s ease;
  }

  nav a:hover {
    color: var(--accent);
  }

  .container {
    max-width: 1100px;
    margin: 50px auto;
    background: var(--bg-main);
    border-radius: 14px;
    padding: 25px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
  }

  h1 {
    color: var(--accent);
    font-size: 1.8rem;
    border-bottom: 2px solid var(--accent);
    padding-bottom: 8px;
    margin-bottom: 25px;
  }

  .add-btn {
    display: inline-block;
    background: var(--accent);
    color: white;
    padding: 10px 16px;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 15px;
    transition: background 0.3s;
  }

  .add-btn:hover {
    background: var(--accent-hover);
  }

  .search-bar {
    margin-bottom: 15px;
    padding: 10px;
    width: 100%;
    border-radius: 6px;
    border: 1px solid var(--border);
    font-size: 14px;
  }

  table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
    background: var(--bg-main);
    border-radius: 8px;
    overflow: hidden;
  }

  th, td {
    padding: 14px 18px;
    border-bottom: 1px solid var(--border);
    text-align: left;
  }

  th {
    background: var(--accent);
    color: white;
    font-size: 15px;
  }

  tr:hover {
    background: #FFF3E6;
  }

  .action-btn {
    padding: 6px 12px;
    border-radius: 5px;
    font-weight: 600;
    text-decoration: none;
    color: white;
    transition: 0.2s ease-in-out;
  }

  .edit-btn { background: #2D89EF; }
  .delete-btn { background: #E74C3C; }
  .match-btn { background: #27AE60; }

  .edit-btn:hover { background: #4A9FFF; }
  .delete-btn:hover { background: #FF6B6B; }
  .match-btn:hover { background: #34C77B; }

  .empty-message {
    background: var(--bg-secondary);
    color: var(--text-muted);
    text-align: center;
    padding: 40px;
    border-radius: 10px;
    margin-top: 30px;
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


  <div class="container">
    <h1>Your Created Tournaments</h1>

    <?php if (count($tournaments) > 0): ?>
      <table>
        <thead>
          <tr>
            <th>Title</th>
            <th>Game</th>
            <th>Location</th>
            <th>Start</th>
            <th>End</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($tournaments as $t): ?>
          <tr>
            <td><?= htmlspecialchars($t['title']) ?></td>
            <td><?= htmlspecialchars($t['game']) ?></td>
            <td><?= htmlspecialchars($t['location']) ?></td>
            <td><?= htmlspecialchars($t['start_date']) ?></td>
            <td><?= htmlspecialchars($t['end_date']) ?></td>
            <td><?= htmlspecialchars($t['status']) ?></td>
            <td>
              <a href="edit_tournament.php?id=<?= $t['tournament_id'] ?>" class="btn edit-btn">Edit</a>
              <a href="../backend/delete_tournament.php?id=<?= $t['tournament_id'] ?>" class="btn delete-btn" onclick="return confirm('Delete this tournament?');">Delete</a>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No tournaments found. <a href="create_tournament.php" class="btn">Create one now.</a></p>
    <?php endif; ?>
  </div>
</body>
</html>
