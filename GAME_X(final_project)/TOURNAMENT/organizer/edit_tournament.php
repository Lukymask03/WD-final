<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";
require_once "../backend/helpers/log_activity.php";

checkAuth('organizer');

// ✅ Use the correct session variable for organizer
$organizer_id = $_SESSION['account_id'] ?? $_SESSION['user_id'] ?? null;

if (!$organizer_id) {
    die("Organizer not logged in.");
}

if (!isset($_GET['id'])) {
    header("Location: view_tournaments.php");
    exit;
}

$tournament_id = (int)$_GET['id'];

// ===== Fetch tournament WITHOUT restricting to organizer =====
$stmt = $conn->prepare("SELECT * FROM tournaments WHERE tournament_id = ?");
$stmt->execute([$tournament_id]);
$tournament = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== Friendly message if tournament not found =====
if (!$tournament) {
    echo "<div class='container'>";
    echo "<h2>Tournament Not Found</h2>";
    echo "<p>The tournament you are trying to edit does not exist.</p>";
    echo "<a href='view_tournaments.php' class='btn'>Back to My Tournaments</a>";
    echo "</div>";
    exit;
}

// ===== Check if organizer owns this tournament =====
$ownTournament = ($tournament['organizer_id'] == $organizer_id);
$accessWarning = !$ownTournament
    ? "<p style='color:red; font-weight:bold;'>You are not the organizer of this tournament. Changes may not be saved under your account.</p>"
    : "";

// ===== Handle form submission only if organizer owns the tournament =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $ownTournament) {
    $title = trim($_POST['title']);
    $game = trim($_POST['game']);
    $description = trim($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $max_teams = (int)$_POST['max_teams'];
    $reg_start = $_POST['reg_start_date'];
    $reg_deadline = $_POST['reg_deadline'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("
        UPDATE tournaments SET
            title=?, game=?, description=?, start_date=?, end_date=?,
            max_teams=?, reg_start=?, reg_deadline=?, status=?
        WHERE tournament_id=? AND organizer_id=?
    ");
    $stmt->execute([
        $title, $game, $description, $start_date, $end_date,
        $max_teams, $reg_start, $reg_deadline, $status,
        $tournament_id, $organizer_id
    ]);

    logActivity($organizer_id, "Edit Tournament", "Updated tournament ID: $tournament_id");

    header("Location: view_tournaments.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Edit Tournament | GameX</title>
<link rel="stylesheet" href="../assets/css/common.css">
<link rel="stylesheet" href="../assets/css/organizer_dashboard.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
<style>
body { font-family: 'Poppins', sans-serif; }
form {
    max-width: 600px; margin: 60px auto; background: var(--bg-secondary);
    padding: 25px; border-radius: 15px; box-shadow: 0 0 15px rgba(0,0,0,0.2);
}
h2 { text-align: center; color: var(--text-main); }
label { display: block; margin-top: 15px; font-weight: 600; }
input, select, textarea { width: 100%; padding: 10px; margin-top: 5px;
    border: 1px solid var(--border); border-radius: 8px;
    background: var(--bg-main); color: var(--text-main); }
button { display: block; width: 100%; margin-top: 25px; padding: 10px;
    background: #3498db; border: none; border-radius: 8px; color: white;
    font-weight: 600; cursor: pointer; transition: 0.3s; }
button:hover { background: #2980b9; }
.access-warning {
    background-color: #ffe6e6;
    color: #c0392b;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 8px;
    font-weight: bold;
    text-align: center;
}

</style>
</head>
<body>
<!-- ==== SIDEBAR ==== -->
<?php include '../includes/organizer/organizer_sidebar.php'; ?>
<form method="POST">

   <h2>Edit Tournament</h2>

    <!-- Access warning inside the card -->
    <?php if (!$ownTournament): ?>
        <div class="access-warning">
            You are not the organizer of this tournament. Changes may not be saved under your account.
        </div>
    <?php endif; ?>

    <label>Title</label>
    <input type="text" name="title" value="<?= htmlspecialchars($tournament['title']) ?>" required>

    <label>Game</label>
    <input type="text" name="game" value="<?= htmlspecialchars($tournament['game']) ?>" required>

    <label>Description</label>
    <textarea name="description"><?= htmlspecialchars($tournament['description']) ?></textarea>

    <label>Start Date</label>
    <input type="date" name="start_date" value="<?= date('Y-m-d', strtotime($tournament['start_date'])) ?>" required>

    <label>End Date</label>
    <input type="date" name="end_date" value="<?= date('Y-m-d', strtotime($tournament['end_date'])) ?>">

    <label>Max Teams</label>
    <input type="number" name="max_teams" min="2" value="<?= $tournament['max_teams'] ?>">

    <label>Registration Start</label>
    <input type="date" name="reg_start_date" value="<?= date('Y-m-d', strtotime($tournament['reg_start'])) ?>">

    <label>Registration Deadline</label>
    <input type="date" name="reg_deadline" value="<?= date('Y-m-d', strtotime($tournament['reg_deadline'])) ?>">

    <label>Status</label>
    <select name="status">
        <option value="open" <?= $tournament['status']=='open'?'selected':'' ?>>Open</option>
        <option value="closed" <?= $tournament['status']=='closed'?'selected':'' ?>>Closed</option>
    </select>

    <?php if ($ownTournament): ?>
        <button type="submit">Update Tournament</button>
    <?php else: ?>
        <button type="button" disabled>Cannot Edit</button>
    <?php endif; ?>
</form>

</main>
</body>
</html>
