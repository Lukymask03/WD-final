<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";
checkAuth('organizer');

// --- Get organizer_id from session ---
$account_id = $_SESSION['user_id'] ?? null;
$organizer_id = null;

if ($account_id) {
    $stmt = $conn->prepare("SELECT organizer_id FROM organizer_profiles WHERE account_id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $organizer = $result->fetch_assoc();
    $organizer_id = $organizer['organizer_id'] ?? null;
    $stmt->close();
}

// --- Fetch tournaments owned by this organizer ---
$tournament_result = null;
if ($organizer_id) {
    $stmt = $conn->prepare("SELECT tournament_id, title FROM tournaments WHERE organizer_id = ?");
    $stmt->bind_param("i", $organizer_id);
    $stmt->execute();
    $tournament_result = $stmt->get_result();
    $stmt->close();
}

// --- Selected tournament ---
$selected_tournament_id = $_GET['tournament_id'] ?? null;

// --- Handle add match ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_match'])) {
    $tournament_id = $_POST['tournament_id'];
    $team1_id = $_POST['team1'];
    $team2_id = $_POST['team2'];
    $round = trim($_POST['round']);
    $match_date = $_POST['match_date'];

    if ($team1_id && $team2_id && $team1_id != $team2_id) {
        $stmt = $conn->prepare("INSERT INTO matches (tournament_id, match_date, round) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $tournament_id, $match_date, $round);
        $stmt->execute();
        $match_id = $conn->insert_id;
        $stmt->close();

        // Add both teams to match_teams
        $insert = $conn->prepare("INSERT INTO match_teams (match_id, team_id, result) VALUES (?, ?, 'pending')");
        $insert->bind_param("ii", $match_id, $team1_id);
        $insert->execute();
        $insert->bind_param("ii", $match_id, $team2_id);
        $insert->execute();
        $insert->close();
    }

    header("Location: manage_matches.php?tournament_id=$tournament_id");
    exit;
}

// --- Handle delete match ---
if (isset($_GET['delete_match'])) {
    $match_id = $_GET['delete_match'];

    $stmt = $conn->prepare("DELETE FROM match_teams WHERE match_id = ?");
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM matches WHERE match_id = ?");
    $stmt->bind_param("i", $match_id);
    $stmt->execute();
    $stmt->close();

    header("Location: manage_matches.php?tournament_id=$selected_tournament_id");
    exit;
}

// --- Fetch matches ---
$matches = [];
if ($selected_tournament_id) {
    $sql = "
        SELECT 
            m.match_id,
            m.match_date,
            m.round,
            GROUP_CONCAT(t.team_name ORDER BY t.team_id SEPARATOR ' vs ') AS teams,
            GROUP_CONCAT(mt.result ORDER BY t.team_id SEPARATOR ' / ') AS results
        FROM matches m
        JOIN match_teams mt ON m.match_id = mt.match_id
        JOIN teams t ON mt.team_id = t.team_id
        WHERE m.tournament_id = ?
        GROUP BY m.match_id
        ORDER BY m.match_date ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_tournament_id);
    $stmt->execute();
    $matches = $stmt->get_result();
    $stmt->close();
}

// --- Fetch teams for selected tournament ---
$teams_list = [];
if ($selected_tournament_id) {
    $sql = "
        SELECT DISTINCT t.team_id, t.team_name
        FROM registrations r
        JOIN teams t ON r.team_id = t.team_id
        WHERE r.tournament_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_tournament_id);
    $stmt->execute();
    $teams = $stmt->get_result();
    while ($row = $teams->fetch_assoc()) {
        $teams_list[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Matches</title>
    <link rel="stylesheet" href="../assets/css/admin_dashboard.css">
</head>
<body>
<div class="dashboard-container">
    <h1>Manage Matches</h1>

    <!-- Tournament Selector -->
    <form method="GET" class="tournament-selector">
        <label for="tournament_id">Select Tournament:</label>
        <select name="tournament_id" id="tournament_id" onchange="this.form.submit()" required>
            <option value="">-- Select Tournament --</option>
            <?php if ($tournament_result): ?>
                <?php while ($t = $tournament_result->fetch_assoc()): ?>
                    <option value="<?= $t['tournament_id'] ?>" <?= ($selected_tournament_id == $t['tournament_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($t['title']) ?>
                    </option>
                <?php endwhile; ?>
            <?php endif; ?>
        </select>
    </form>

    <?php if ($selected_tournament_id): ?>
        <!-- Add Match -->
        <div class="card">
            <h2>Add New Match</h2>
            <form method="POST">
                <input type="hidden" name="tournament_id" value="<?= htmlspecialchars($selected_tournament_id) ?>">

                <div>
                    <label>Team 1:</label>
                    <select name="team1" required>
                        <option value="">Select Team</option>
                        <?php foreach ($teams_list as $team): ?>
                            <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Team 2:</label>
                    <select name="team2" required>
                        <option value="">Select Team</option>
                        <?php foreach ($teams_list as $team): ?>
                            <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label>Round:</label>
                    <input type="text" name="round" placeholder="e.g., Quarterfinal" required>
                </div>

                <div>
                    <label>Match Date:</label>
                    <input type="datetime-local" name="match_date" required>
                </div>

                <button type="submit" name="add_match">Add Match</button>
            </form>
        </div>

        <!-- Match List -->
        <div class="card">
            <h2>All Matches</h2>
            <table class="dashboard-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Teams</th>
                        <th>Round</th>
                        <th>Date</th>
                        <th>Results</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($matches && $matches->num_rows > 0): ?>
                        <?php while ($m = $matches->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['match_id']) ?></td>
                                <td><?= htmlspecialchars($m['teams']) ?></td>
                                <td><?= htmlspecialchars($m['round']) ?></td>
                                <td><?= htmlspecialchars($m['match_date']) ?></td>
                                <td><?= htmlspecialchars($m['results']) ?></td>
                                <td>
                                    <a href="edit_match.php?match_id=<?= $m['match_id'] ?>&tournament_id=<?= $selected_tournament_id ?>" class="btn-edit">Edit</a>
                                    <a href="manage_matches.php?delete_match=<?= $m['match_id'] ?>&tournament_id=<?= $selected_tournament_id ?>" onclick="return confirm('Are you sure?')" class="btn-delete">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6">No matches found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
