<?php
session_start();
require_once "../backend/db.php";
require_once "../backend/helpers/auth_guard.php";
require_once "../backend/helpers/log_activity.php";

// Allow only organizers
checkAuth("organizer");

$success = false;
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tournament_name = trim($_POST["tournament_name"]);
    $game_title = trim($_POST["game_title"]);
    $max_players = trim($_POST["max_players"]);
    $start_date = trim($_POST["start_date"]);
    $end_date = trim($_POST["end_date"]);
    $reg_start_date = trim($_POST["reg_start_date"]);
    $reg_end_date = trim($_POST["reg_end_date"]);
    $num_teams = trim($_POST["num_teams"]);
    $description = trim($_POST["description"]);
    $organizer_id = $_SESSION["user_id"];

    // Validate required fields
    if (empty($tournament_name) || empty($game_title) || empty($max_players) || empty($start_date) || empty($reg_start_date) || empty($reg_end_date) || empty($num_teams)) {
        $error = "Please fill in all required fields.";
    } else {
        // Validate start and end date of registration
        if ($reg_start_date >= $reg_end_date) {
            $error = "End date of registration cannot be earlier than start date of registration.";
        } elseif ($reg_start_date < $start_date) {
            $error = "Start date of registration cannot be earlier than start date of tournament.";
        } elseif ($reg_end_date > $end_date) {
            $error = "End date of registration cannot be later than end date of tournament.";
        } else {
            try {
                $stmt = $conn->prepare("
                    INSERT INTO tournaments (tournament_name, game_title, max_players, start_date, end_date, reg_start_date, reg_end_date, num_teams, description, organizer_id, status)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')
                ");
                $stmt->execute([$tournament_name, $game_title, $max_players, $start_date, $end_date, $reg_start_date, $reg_end_date, $num_teams, $description, $organizer_id]);

                // Log organizer activity
                logActivity($organizer_id, "Create Tournament", "Created tournament: $tournament_name");

                $success = true;
            } catch (PDOException $e) {
                $error = "Database error: " . $e->getMessage();
            }
        }
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'error' => $error
    ]);

    // Redirect to the tournament page
    header('Location: tournament.php?id=' . $tournament_id);
    exit();
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

            <label for="reg_start_date">Start Date of Registration *</label>
            <input type="date" name="reg_start_date" id="reg_start_date" required>

            <label for="reg_end_date">End Date of Registration *</label>
            <input type="date" name="reg_end_date" id="reg_end_date" required>

            <label for="num_teams">Number of Teams to Register *</label>
            <input type="number" name="num_teams" id="num_teams" required min="1">

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
    <!-- ==== SCRIPTS ==== -->
    <script>
        const form = document.querySelector('.tournament-form');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            // Get form data
            const formData = new FormData(form);

            // Send form data to the server
            fetch('/create-tournament', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Handle the response from the server
                if (data.success) {
                    // Display success message
                    const successMessage = document.querySelector('.success-message');
                    successMessage.textContent = data.message;
                } else {
                    // Display error message
                    const errorMessage = document.querySelector('.error-message');
                    errorMessage.textContent = data.message;
                }
            })
            .catch(error => {
                // Handle any errors
                console.error(error);
            });
        });
    </script>
</body>
</html>
